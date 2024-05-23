<?php

/* ============
 * map_lib.php
 * ------
 * version: 4.0 build:20240210
 * This is a module library
 * ------
 * Class CMapPoint
 * gmapCan gmapHasKey gmapApi gmapEmpty gmapEmptycoord
 * gmapMarker gmapMarkerMapTypeId gmapMarkerPin
 * QTgetx QTgety QTgetz QTstr2yx QTdd2dms
 * ============ */

class CMapPoint
{
  public $y = 4.352;
  public $x = 50.847;
  public $title = '';  // marker tips
  public $info = '';   // html to display on click
  public $marker = ''; // default marker
  function __construct($y, $x, string $title='', string $info='', string $marker='')
  {
    if ( isset($y) && isset($x) ) {
      $this->y = (float)$y;
      $this->x = (float)$x;
    } else {
      if ( isset($_SESSION[QT]['m_gmap_gcenter']) ) {
        $this->y = (float)QTgety($_SESSION[QT]['m_gmap_gcenter']);
        $this->x = (float)QTgetx($_SESSION[QT]['m_gmap_gcenter']);
      }
    }
    $this->title = empty($title) ? '' : $title;
    $this->info = empty($info) ? '' : $info;
    $this->marker = empty($marker) || $marker==='default' ? '' : $marker;
  }
}
function getMapSectionsSettings(string $alt_json='{}')
{
  // Returns an array of settings with keys {[int]sectionid|'U'|'S'} and options keys {enabled,list,icon}
  // Returns [] in case of empty json or format error
  $json = file_exists(APP.'m_gmap/config_gmap.json') ? file_get_contents(APP.'m_gmap/config_gmap.json') : $alt_json;
  $arr = json_decode($json, true);
  if ( json_last_error()!==JSON_ERROR_NONE) { echo '<p class="error">Unable to read map configurations from '.APP.'m_gmap/config_gmap.json</p>'; return []; }
  return $arr;
}
function getMapSectionSettings($section, bool $generateDefault=false, string $json='')
{
  // Returns one setting [array] from the $json settings
  if ( !is_int($section) && $section!=='U' && $section!=='S' ) die('getMapSectionSettings: invalid argument #1');
  $arr = getMapSectionsSettings($json);
  if ( !empty($arr[$section]) ) return $arr[$section];
  // Error or missing
  if ( $generateDefault ) return ['section'=>$section,'enabled'=>0,'list'=>1];
  return [];
}

// ------
class CCanvas
{
	private $canvas = ''; // default is <div id="map_canvas"></div>
  private $header = '';
  private $footer = '';
  private static function idclass($id='',$class='') { return (empty($id) ? '' : ' id="'.$id.'"').(empty($class) ? '' : ' class="'.$class.'"'); }

  public function __construct($id='map_canvas',$class='')  { $this->canvas = '<div'.CCanvas::idclass($id,$class).'></div>'.PHP_EOL; }

  public function Render($handler=false, $id='', $class='gmap')
	{
	  if ( isset($_SESSION[QT]['m_gmap_hidelist']) && $_SESSION[QT]['m_gmap_hidelist'] ) {
      $this->canvas=''; // in case of no display canvas is '', header/footer/handler can be displayed
      $class.=' hidden';
    }
    $str = '<div'.CCanvas::idclass($id,$class).'>'.PHP_EOL;
		$str .= $this->header.PHP_EOL.$this->canvas.PHP_EOL.$this->footer.PHP_EOL;
		$str .= '</div>'.PHP_EOL;
		// Show/Hide control
		if ( $handler ) {
      global $oH;
			if ( $_SESSION[QT]['m_gmap_hidelist'] ) {
      $str .= '<div id="canvashandler" class="canvashandler"><a class="canvashandler" href="'.url($oH->php).qtURI('hidemap').'&showmap">'.qtSVG('caret-down').' '.L('Gmap.Show_map').'</a></div>'.PHP_EOL;
			} else {
      $str .= '<div id="canvashandler" class="canvashandler"><a class="canvashandler" href="'.url($oH->php).qtURI('showmap').'&hidemap">'.qtSVG('caret-up').' '.L('Gmap.Hide_map').'</a></div>'.PHP_EOL;
			}
		}

		return $str;
	}

  public function Header($arrExtData=array(),$arrEditCommands=array(),$id='',$class='header')
	{
	  // In case of $arrExtData (no EditCommands)
	  if ( is_array($arrExtData) && count($arrExtData)>1 ) {
      $this->header .= '<p'.CCanvas::idclass($id,$class).' style="margin:0 0 4px 0"><a class="gmap" href="javascript:void(0)" onclick="zoomToFullExtend(); return false;">'.L('Gmap.zoomtoall').'</a> | '.L('Show').' <select class="gmap" id="zoomto" name="zoomto" size="1" onchange="gmapPan(this.value);">';
      $this->header .= '<option value="'.$_SESSION[QT]['m_gmap_gcenter'].'"> </option>';
      $i=0;
      foreach($arrExtData as $oMapPoint) {
        if ( is_a($oMapPoint,'CMapPoint') ) $this->header .= '<option value="'.$oMapPoint->y.','.$oMapPoint->x.'">'.$oMapPoint->title.'</option>';
        ++$i; if ( $i>20 ) break;
      }
      $this->header .= '</select></p>';
	  }

    // Commands
	  if ( is_array($arrEditCommands) && count($arrEditCommands)>0 ) {
			$this->header .= '<p'.(empty($id) ? '' : ' id="'.$id.'"').(empty($class) ? '' : ' class="'.$class.'"').'>';
			foreach($arrEditCommands as $str) {
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
       $str = L('Gmap.addrlatlng').' <input type="text" size="24" id="find" name="find" class="small" value="'.$_SESSION[QT]['m_gmap_gfind'].'" title="'.L('map_H_addrlatlng').'" onkeypress="if ((event.key!==undefined && event.key==`Enter`) || (event.keyCode!==undefined && event.keyCode==13)) showLocation(this.value,null);"/><span id="btn-geocode" title="'.L('Search').'" onclick="showLocation(document.getElementById(`find`).value,null);">'.qtSVG('search').'</span>';
     }
     if ( !empty($str) ) {
       $this->footer .= '<p'.CCanvas::idclass($id,$class).'>'.$str.'</p>';
     }
	}

}
function gmapCan($id=null, string $role='')
{
  if ( !gmapHasKey() || $id===-1 ) return false;
  if ( !isset($id) ) die('gmapCan: arg #1 must be a section ref');
  // Evaluate
  $gmapSectionSettings = getMapSectionSettings($id); // id [int]|'U'|'S'
  if ( empty($gmapSectionSettings['enabled']) ) return false;
  if ( !empty($role) ) {
    if ( empty($gmapSectionSettings['list']) ) return false;
    if ( $gmapSectionSettings['list']==='M' ) $gmapSectionSettings['list'] = 2; // compatibility with version 2.x
    if ( $gmapSectionSettings['list']===2 && $role==='V' ) return false;
    if ( $gmapSectionSettings['list']===2 && $role==='U' ) return false;
  }
  return true;
}
function gmapHasKey()
{
  return !empty($_SESSION[QT]['m_gmap_gkey']);
}
function gmapApi(string $key='',string $addLibrary='')
{
  if ( empty($key) ) return '';
  return '(g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})({ key: "'.$key.'", v: "weekly"});'.PHP_EOL.$addLibrary.PHP_EOL.'gmapInitialize();';
}
function gmapOption(string $key='mt', string $alt='')
{
  return qtExplodeGet($_SESSION[QT]['m_gmap_options'], $key, $alt);
}
function gmapEmpty($i)
{
  // Returns true when $i is empty or a value starting with '0.000000'
  if ( empty($i) ) return true;
  if ( !is_string($i) && !is_float($i) && !is_int($i) ) die('gmapEmpty: Invalid argument #1');
  if ( substr((string)$i,0,8)==='0.000000' ) return true;
  return false;
}
function gmapEmptycoord($a)
{
  // Returns true when $a has empty coordinates in both Y and X.
  // $a can be a CMapPoint or CTopic object or a string Y,X. ex: "51.75,4.12"
  // Note: returns true if $a is not correctly formatted or when properties x or y are missing.
  // Note: Z coordinate is NOT evaluated. ex: gmapEmptycoord("0,0,125") returns true.

  if ( is_a($a,'CMapPoint') || is_a($a,'CTopic') )
  {
    if ( !property_exists($a,'y') ) return true;
    if ( !property_exists($a,'x') ) return true;
    if ( gmapEmpty($a->y) && gmapEmpty($a->x) ) return true;
    return false;
  }
  if ( is_string($a) )
  {
    if ( gmapEmpty(QTgety($a,true)) && gmapEmpty(QTgetx($a,true)) ) return true;
    return false;
  }
  die('gmapEmptycoord: invalid argument #1');
}
function gmapMarker(string $centerLatLng='', bool $draggable=false, string $marker='', string $title='', string $info='')
{
  if ( $centerLatLng==='' || $centerLatLng==='0,0' ) return 'marker = null;';
  return gmapMarkerPin($marker).PHP_EOL.'marker = new google.maps.marker.AdvancedMarkerElement({
  position: '.($centerLatLng==='map' ? 'gmap.getCenter()' : 'new google.maps.LatLng('.$centerLatLng.')').',
  map: gmap,
  gmpDraggable: '.($draggable ? 'true' : 'false').',
  content: gmapPin,
  title: "'.$title.'"
  });'.PHP_EOL.'markers.push(marker);'.PHP_EOL.(empty($info) ? '' : 'gmapInfo(marker,`'.$info.'`);');
}
function gmapMarkerPin(string $marker='')
{
  if ( empty($marker) || $marker==='0.png' ) return 'gmapPin = null;';
  if ( file_exists(APP.'m_gmap/'.$marker) ) return 'gmapPin = document.createElement("img"); gmapPin.src = "'.APP.'m_gmap/'.$marker.'";';
  // svg in a glyph
  // if ( file_exists(APP.'m_gmap/'.$marker.'.svg') ) return 'gmapPin = document.createElement("img"); gmapPin.src = "'.APP.'m_gmap/'.$marker.'.svg"; gmapPin = new PinElement({glyph:gmapPin}); gmapPin = gmapPin.element;';
  return 'gmapPin = null;';
}
function gmapMarkerMapTypeId(string $maptype='')
{
  switch($maptype) {
    case 'S':
    case 'SATELLITE': return 'google.maps.MapTypeId.SATELLITE';
    case 'H':
    case 'HYBRID': return 'google.maps.MapTypeId.HYBRID';
    case 'P':
    case 'T':
    case 'TERRAIN': return 'google.maps.MapTypeId.TERRAIN';
  }
  return 'google.maps.MapTypeId.ROADMAP';
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