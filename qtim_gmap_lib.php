<?php

/* ============
 * map_lib.php
 * ------------
 * version: 4.0 build:20230430
 * This is a module library
 * ------------
 * Class CMapPoint
 * gmapCan gmapHasKey gmapApi QTgempty QTgemptycoord
 * gmapMarker gmapMarkerMapTypeId gmapMarkerIcon
 * QTgetx QTgety QTgetz QTstr2yx QTdd2dms
 * ============ */

class CMapPoint
{
  public $y = 4.352;
  public $x = 50.847;
  public $title = ''; // marker tips
  public $info = '';  // html to display on click
  public $icon = false;
  function __construct($y,$x,$title='',$info='')
  {
    if ( isset($y) && isset($x) )
    {
      $this->y = $y;
      $this->x = $x;
    }
    else
    {
      if ( isset($_SESSION[QT]['m_gmap_gcenter']) )
      {
        $this->y = floatval(QTgety($_SESSION[QT]['m_gmap_gcenter']));
        $this->x = floatval(QTgetx($_SESSION[QT]['m_gmap_gcenter']));
      }
    }
    if ( !empty($title) ) $this->title = $title;
    if ( !empty($info) ) $this->info = $info;
  }
}

function getMapSectionsSettings($jMapSections='')
{
  // Returns an array of settings where the key is the section-id [int, 'U' or 'S'] and the value is the settings [object] (json decoded).
  // $json should be a string of all settings (in a json format).
  // When $json is empty, the function uses the session variable $_SESSION[QT]['m_gmap'].
  // When $reloadOnEmpty is true, the function reloads the settings from the config file when $json and the session variable are empty.
  // Returns an empty array() in case of wrong json content (i.e. no valid section identifier), or when the settings are empty.
  // TIPS - To force a reload from the config file, just use unset($_SESSION[QT]['m_gmap']) then getMapSectionsSettings()

  if ( !is_string($jMapSections) ) die('getMapSectionsSettings: invalid argument #1');
  if ( empty($jMapSections) && file_exists(APP.'m_gmap/config_gmap.php') ) include APP.'m_gmap/config_gmap.php';

  $o = json_decode($jMapSections);
  if ( json_last_error()!==JSON_ERROR_NONE) { echo '<p class="error">Unable to read map configurations from '.APP.'m_gmap/config_gmap.php</p>'; return false; }
  return $o;
}

function getMapSectionSettings($section,$generateDefault=false,$jMapSections='')
{
  // Returns one setting [object] from the $json settings [string]
  // Returns false when there is no settings (or wrong format)
  // Returns false when $section is not defined in the settigns OR a default setting when $generatDefault is true

  if ( !is_string($jMapSections) ) die('getMapSectionSettings: invalid argument #1');
  if ( !is_int($section) && $section!=='U' && $section!=='S' ) die('getMapSectionSettings: invalid argument #2');

  $o = getMapSectionsSettings($jMapSections);
  if ( empty($o) ) return false;
  if ( property_exists($o,$section) ) return $o->$section;
  if ( $generateDefault ) return (object)['section'=>$section,'enabled'=>0,'list'=>1];
  return false;
}

// ---------

class cCanvas
{
	private $canvas=''; // default is <div id="map_canvas"></div>
  private $header='';
  private $footer='';
  private static function idclass($id='',$class='') { return (empty($id) ? '' : ' id="'.$id.'"').(empty($class) ? '' : ' class="'.$class.'"'); }

  public function __construct($id='map_canvas',$class='')  { $this->canvas = '<div'.cCanvas::idclass($id,$class).'></div>'.PHP_EOL; }

  public function Render($handler=false,$id='',$class='gmap')
	{
	  if ( isset($_SESSION[QT]['m_gmap_hidelist']) && $_SESSION[QT]['m_gmap_hidelist'] )
    {
    $this->canvas=''; // in case of no display canvas is '', header/footer/handler can be displayed
    $class.=' hidden';
    }

    $str = '<div'.cCanvas::idclass($id,$class).'>'.PHP_EOL;
		$str .= $this->header.PHP_EOL.$this->canvas.PHP_EOL.$this->footer.PHP_EOL;
		$str .= '</div>'.PHP_EOL;

		// Show/Hide control

		if ( $handler )
		{
      global $oH;
			if ( $_SESSION[QT]['m_gmap_hidelist'] )
			{
      $str .= '<div id="canvashandler" class="canvashandler"><a class="canvashandler" href="'.url($oH->selfurl).'?'.qtURI('hidemap').'&showmap">'.getSVG('caret-down').' '.L('Gmap.Show_map').'</a></div>'.PHP_EOL;
			}
			else
			{
      $str .= '<div id="canvashandler" class="canvashandler"><a class="canvashandler" href="'.url($oH->selfurl).'?'.qtURI('showmap').'&hidemap">'.getSVG('caret-up').' '.L('Gmap.Hide_map').'</a></div>'.PHP_EOL;
			}
		}

		return $str;
	}

  public function Header($arrExtData=array(),$arrEditCommands=array(),$id='',$class='header')
	{
	  // In case of $arrExtData (no EditCommands)

	  if ( is_array($arrExtData) && count($arrExtData)>1 )
	  {
          $this->header .= '<p'.cCanvas::idclass($id,$class).' style="margin:0 0 4px 0"><a class="gmap" href="javascript:void(0)" onclick="zoomToFullExtend(); return false;">'.L('Gmap.zoomtoall').'</a> | '.L('Show').' <select class="gmap" id="zoomto" name="zoomto" size="1" onchange="gmapPan(this.value);">';
		      $this->header .= '<option value="'.$_SESSION[QT]['m_gmap_gcenter'].'"> </option>';
		      $i=0;
		      foreach($arrExtData as $oMapPoint)
		      {
		      if ( is_a($oMapPoint,'CMapPoint') ) $this->header .= '<option value="'.$oMapPoint->y.','.$oMapPoint->x.'">'.$oMapPoint->title.'</option>';
		      ++$i; if ( $i>20 ) break;
		      }
		      $this->header .= '</select></p>';
	  }

    // Commands

	  if ( is_array($arrEditCommands) && count($arrEditCommands)>0 )
	  {
			$this->header .= '<p'.(empty($id) ? '' : ' id="'.$id.'"').(empty($class) ? '' : ' class="'.$class.'"').'>';
			foreach($arrEditCommands as $str)
			{
				// default command codes
				if ( $str==='add' ) $str=' | <a href="javascript:void(0)" onclick="createMarker(); return false;" title="'.L('map_H_pntadd').'">'.L('Gmap.pntadd').'</a>';
				if ( $str==='del' ) $str=' | <a href="javascript:void(0)" onclick="deleteMarker(); return false;">'.L('Gmap.pntdelete').'</a>';
				// other values
				$this->header .= $str;
			}
			$this->header .= '</p>';
    }

	}

  public function Footer($str='find', $id='', $class='footer')
	{
     if ( $str==='find' ) {
       $str = L('Gmap.addrlatlng').' <input type="text" size="24" id="find" name="find" class="small" value="'.$_SESSION[QT]['m_gmap_gfind'].'" title="'.L('map_H_addrlatlng').'" onkeypress="qtKeypress(event,`btn-geocode`);"/><span id="btn-geocode" title="'.L('Search').'" onclick="showLocation(document.getElementById(`find`).value,null);">'.getSVG('search').'</span>';
     }
     if ( !empty($str) ) {
       $this->footer .= '<p'.cCanvas::idclass($id,$class).'>'.$str.'</p>';
     }
	}

}

// ---------

// gmapCan
// $strSection is 'U' users, 'S' search results, or [int] section id
// $strRole can be '' to skip section list check

function gmapCan($section=null,$strRole='')
{
  if ( !gmapHasKey() ) return false;

  // Check

  if ( !isset($section) ) die('gmapCan: arg #1 must be a section ref');
  if ( !is_string($strRole) ) die('gmapCan: arg #2 must be an string');
  if ( $section===-1 ) return false;

  // Evaluate

  $oSettings = getMapSectionSettings($section);
  if ( $oSettings===false || !property_exists($oSettings,'enabled') ) return false;
  if ( $oSettings->enabled!=1 ) return false;
  if ( !empty($strRole) )
  {
    if ( !property_exists($oSettings,'list') ) return false;
    if ( $oSettings->list==0 ) return false;
    if ( $oSettings->list==='M' ) $oSettings->list=2; // compatibility with version 2.x
    if ( $oSettings->list==2 && $strRole==='V' ) return false;
    if ( $oSettings->list==2 && $strRole==='U' ) return false;
  }
  return true;
}

function gmapHasKey()
{
  return !empty($_SESSION[QT]['m_gmap_gkey']);
}
function gmapApi($strKey='',$strAddLibrary='')
{
  if ( empty($strKey) ) return '';
  return '<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key='.$strKey.'&callback=gmapInitialize"></script>'.PHP_EOL.(empty($strAddLibrary) ? '' : $strAddLibrary);
}
function QTgempty($i)
{
  // Returns true when $i is empty or a value starting with '0.000000'
  if ( empty($i) ) return true;
  if ( !is_string($i) && !is_float($i) && !is_int($i) ) die('QTgempty: Invalid argument #1');
  if ( substr((string)$i,0,8)==='0.000000' ) return true;
  return false;
}
function QTgemptycoord($a)
{
  // Returns true when $a has empty coordinates in both Y and X.
  // $a can be a CMapPoint or CTopic object or a string Y,X. ex: "51.75,4.12"
  // Note: returns true if $a is not correctly formatted or when properties x or y are missing.
  // Note: Z coordinate is NOT evaluated. ex: QTgemptycoord("0,0,125") returns true.

  if ( is_a($a,'CMapPoint') || is_a($a,'CTopic') )
  {
    if ( !property_exists($a,'y') ) return true;
    if ( !property_exists($a,'x') ) return true;
    if ( QTgempty($a->y) && QTgempty($a->x) ) return true;
    return false;
  }
  if ( is_string($a) )
  {
    if ( QTgempty(QTgety($a,true)) && QTgempty(QTgetx($a,true)) ) return true;
    return false;
  }
  die('QTgemptycoord: invalid argument #1');
}
function gmapMarker($centerLatLng='',$draggable=false,$gsymbol=false,$title='',$info='')
{
  if ( $centerLatLng==='' || $centerLatLng==='0,0' ) return 'marker = null;';
  if ( $centerLatLng=='map' )
  {
    $centerLatLng = 'map.getCenter()';
  }
  else
  {
    $centerLatLng = 'new google.maps.LatLng('.$centerLatLng.')';
  }
  if ( $draggable=='1' || $draggable==='true' || $draggable===true )
  {
    $draggable='draggable:true, animation:google.maps.Animation.DROP,';
  }
  else
  {
    $draggable='draggable:false,';
  }
  return '	marker = new google.maps.Marker({
		position: '.$centerLatLng.',
		map: map,
		' . $draggable . gmapMarkerIcon($gsymbol) . '
		title: "'.$title.'"
		});
		markers.push(marker); '.PHP_EOL.(empty($info) ? '' : '	gmapInfo(marker,`'.$info.'`);');
}
function gmapMarkerIcon($gsymbol=false)
{
  // returns the google.maps.Marker.icon argument
  if ( empty($gsymbol) ) return ''; // no icon source means that the default symbol is used
  $str = '';
  // icons are 32x32 pixels and the anchor depends on the name: (10,32) for puhspin, (16,32) for point, center form others
  $arr = explode('_',$gsymbol);
  switch($arr[0])
  {
    case 'pushpin':
      $str = 'icon: new google.maps.MarkerImage("qtim_gmap/'.$gsymbol.'.png",new google.maps.Size(32,32),new google.maps.Point(0,0),new google.maps.Point(10,32)),';
      break;
    case 'point':
      $str = 'icon: new google.maps.MarkerImage("qtim_gmap/'.$gsymbol.'.png",new google.maps.Size(32,32),new google.maps.Point(0,0),new google.maps.Point(16,32)),';
      break;
    default:
      $str = 'icon: new google.maps.MarkerImage("qtim_gmap/'.$gsymbol.'.png",new google.maps.Size(32,32),new google.maps.Point(0,0),new google.maps.Point(16,16)),';
      break;
  }
  return $str;
}
function gmapMarkerMapTypeId($gbuttons)
{
  switch((string)$gbuttons)
  {
    case 'S':
    case 'SATELLITE': return 'google.maps.MapTypeId.SATELLITE'; break;
    case 'H':
    case 'HYBRID': return 'google.maps.MapTypeId.HYBRID'; break;
    case 'P':
    case 'T':
    case 'TERRAIN': return 'google.maps.MapTypeId.TERRAIN'; break;
    default: return 'google.maps.MapTypeId.ROADMAP';
  }
}
function QTgetx($str=null,$onerror=0.0)
{
  // checks
  if ( !is_string($str) ) { if ( isset($onerror) ) return $onerror; die('QTgetx: arg #1 must be a string'); }
  if ( !strpos($str,',') ) { { if ( isset($onerror) ) return $onerror; die('QTgetx: arg #1 must be a string with 2 values'); }}
  $arr = explode(',',$str);
  if ( count($arr)<2 ) { if ( isset($onerror) ) return $onerror; die('QTgetx: coordinate must include at least 2 values'); }
  $str = trim($arr[1]);
  if ( !is_numeric($str) ) { if ( isset($onerror) ) return $onerror; die('QTgetx: x-coordinate is not a float'); }
  return (float)$str;
}
function QTgety($str=null,$onerror=0.0)
{
  // checks
  if ( !is_string($str) ) { if ( isset($onerror) ) return $onerror; die('QTgety: arg #1 must be a string'); }
  if ( !strpos($str,',') ) { { if ( isset($onerror) ) return $onerror; die('QTgety: arg #1 must be a string with 2 values'); }}
  $arr = explode(',',$str);
  if ( count($arr)<2 ) { if ( isset($onerror) ) return $onerror; die('QTgety: coordinate must include at least 2 values'); }
  $str = trim($arr[0]);
  if ( !is_numeric($str) ) { if ( isset($onerror) ) return $onerror; die('QTgety: y-coordinate is not a float'); }
  return (float)$str;
}
function QTgetz($str=null,$onerror=0.0)
{
  // checks
  if ( !is_string($str) ) { if ( isset($onerror) ) return $onerror; die('QTgetz: arg #1 must be a string'); }
  if ( !strpos($str,',') ) { { if ( isset($onerror) ) return $onerror; die('QTgetz: arg #1 must be a string with at least 3 values'); }}
  $arr = explode(',',$str);
  if ( count($arr)<3 ) { if ( isset($onerror) ) return $onerror; die('QTgetz: coordinate must include at least 3 values'); }
  $str = trim($arr[2]);
  if ( !is_numeric($str) ) { if ( isset($onerror) ) return $onerror; die('QTgetz: z-coordinate is not a float'); }
  return (float)$str;
}
function QTstr2yx($str)
{
  // check

  if ( !is_string($str) ) die('QTstr2dd: arg #1 must be a string');
  $str = trim($str);
  $str = str_replace('+','',$str);
  $str = str_replace(';',',',$str);
  $arr = explode(',',$str);
  if ( count($arr)!=2 ) return false;

  // analyse each values

  foreach($arr as $intKey=>$str)
  {
    $str = trim(strtoupper($str));
    if ( substr($str,0,1)==='N' || substr($str,0,1)==='E' ) $str = substr($str,1);
    if ( substr($str,0,1)==='S' || substr($str,0,1)==='W' ) $str = '-'.substr($str,1);
    if ( substr($str,-1,1)==='N' || substr($str,-1,1)==='E' ) $str = trim(substr($str,0,-1));
    if ( substr($str,-1,1)==='S' || substr($str,-1,1)==='W' ) $str = '-'.trim(substr($str,0,-1));
    $str = str_replace('--','-',$str);

    // convert dms to dd
    if ( strpos($str,'D') || strpos($str,'?') || strpos($str,"'") || strpos($str,'"') || strpos($str,'?') )
    {
      $str = str_replace(array('SEC','S',"''",'??','"'),'/',$str);
      $str = str_replace(array('MIN','M',"'",'?'),'/',$str);
      $str = str_replace(array('DEG','D','?',':'),'/',$str);
      if ( substr($str,-1,1)==='/' ) $str = substr($str,0,-1);
      $arrValues = explode('/',$str);
      $intD = intval($arrValues[0]); if ( !qtIsBetween($intD,($intKey==0 ? -90 : -180),($intKey==0 ? 90 : 180)) ) return false;
      $intM = 0;
      $intS = 0;
      if ( isset($arrValues[1]) ) { $intM = intval($arrValues[1]); if ( !qtIsBetween($intM,0,59) ) return false; }
      if ( isset($arrValues[2]) ) { $intS = intval($arrValues[2]); if ( !qtIsBetween($intS,0,59) ) return false; }
      $str = $intD+($intM/60)+($intS/3600);
    }

    if ( !qtIsBetween(intval($str),($intKey==0 ? -90 : -180),($intKey==0 ? 90 : 180)) ) return false;
    $arr[$intKey]=$str;
  }

  // returns 2 dd in a string

  return $arr[0].','.$arr[1];
}
function QTdd2dms($dd,$intDec=0)
{
  $dms_d = intval($dd);
  $dd_m = abs($dd - $dms_d);
  $dms_m_float = 60 * $dd_m;
  $dms_m = intval($dms_m_float);
  $dd_s = abs($dms_m_float - $dms_m);
  $dms_s = 60 * $dd_s;
  return $dms_d.'&#176;'.$dms_m.'&#039;'.round($dms_s,$intDec).'&quot;';
}