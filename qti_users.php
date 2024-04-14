<?php

session_start();
/**
 * @var string $formAddUser
 * @var CHtml $oH
 * @var array $L
 * @var CDatabase $oDB
 */
require 'bin/init.php';

$oH->selfurl = 'qti_users.php';
if ( !SUser::canAccess('show_memberlist') ) $oH->voidPage('user-lock.svg',11,true); //...

// CHANGE USER INTERFACE
if ( isset($_GET['view'])) $_SESSION[QT]['viewmode'] = substr($_GET['view'],0,1);

// INITIALISE
$fg = 'all';
$po = 'name';
$pd = 'asc';
$sqlStart = 0; // starting limit for subset of rows {0|page*ipp}
$pn = 1; // page number (start at 1)
if ( isset($_GET['fg']) ) $fg = substr($_GET['fg'],0,7); // protection against injection (widest is "A|B|C|D")
if ( isset($_GET['page']) )  { $pn = (int)$_GET['page']; $sqlStart = ($pn-1)*$_SESSION[QT]['items_per_page']; }
if ( isset($_GET['order']) ) $po = strip_tags(substr($_GET['order'],0,15)); // protection against injection
if ( isset($_GET['dir']) ) $pd = strtolower(substr($_GET['dir'],0,4));
$oH->selfname = L('Memberlist');

// MAP MODULE
$useMap=false;
if ( qtModule('gmap') )
{
  include translate(APP.'m_gmap.php');
  include 'qtim_gmap_lib.php';
  if ( gmapCan('U') ) $useMap=true;
  if ( $useMap ) $oH->links[]='<link rel="stylesheet" type="text/css" href="qtim_gmap.css"/>';
  if ( isset($_GET['hidemap']) ) $_SESSION[QT]['m_gmap_hidelist']=true;
  if ( isset($_GET['showmap']) ) $_SESSION[QT]['m_gmap_hidelist']=false;
  if ( !isset($_SESSION[QT]['m_gmap_hidelist']) ) $_SESSION[QT]['m_gmap_hidelist']=false;
  if ( !isset($_SESSION[QT]['m_gmap_symbols']) ) $_SESSION[QT]['m_gmap_symbols']='0';
  $arrSymbolByRole = ( empty($_SESSION[QT]['m_gmap_symbols']) ? array() : qtExplode($_SESSION[QT]['m_gmap_symbols']) );
}

// Query by lettre

$arrGroup = array_filter(explode('|',$fg)); // filter to remove empty
if ( count($arrGroup)==1 ) {
  switch($fg) {
  Case 'all': $sqlWhere = ''; Break;
  Case '0':   $sqlWhere = ' AND '.sqlFirstChar('name','~'); Break;
  Default:    $sqlWhere = ' AND '.sqlFirstChar('name','u',strlen($fg)).'="'.strtoupper($fg).'"'; Break;
  }
} else {
  $arr = [];
  foreach($arrGroup as $str) $arr[] = sqlFirstChar('name','u').'="'.strtoupper($str).'"';
  $sqlWhere = ' AND ('.implode(' OR ',$arr).')';
}

// COUNT
$intTotal = $oDB->count( TABUSER." WHERE id>0" );
$intCount = $fg=='all' ? $intTotal : $oDB->count( TABUSER." WHERE id>0".$sqlWhere);

// Defines FORM $formAddUser and handles POST
if ( SUser::isStaff() ) include APP.'_inc_adduser.php';

// ------
// HTML BEGIN
// ------
include 'qti_inc_hd.php';

// ------
// Title and top 5
// ------
echo '<div id="ct-title" class="fix-sp top">
<div><h2>'.$oH->selfname.'</h2>
<p>'.( $fg=='all' ? $intTotal.' '.L('Members') : $intCount.' / '.$intTotal.' '.L('Members') );
if ( SUser::canAccess('show_calendar') )echo ' &middot; <a href="'.url('qti_calendar.php').'" style="white-space:nowrap">'.L('Birthdays_calendar').'</a>';
if ( !empty($formAddUser) ) echo ' &middot; <span style="white-space:nowrap">'.SUser::getStamp(SUser::role(), 'class=stamp08').' <a id="tgl-ctrl" href="javascript:void(0)" class="tgl-ctrl'.(isset($_POST['title']) ? ' expanded' : '').'" onclick="qtToggle(`participants`,`block`,``);qtToggle();">'.L('User_add').qtSVG('angle-down','','',true).qtSVG('angle-up','','',true).'</a></span>';
echo '</p>
</div>
';

echo '<div id="participants"'.(isset($_POST['title']) ? ' style="display:none"' : '').' class="strongbox">
<p class="title">'.L('Top_participants').'</p>
<table>
';
// Top 5 participants
$strState = 'name, id, numpost FROM TABUSER WHERE id>0';
$oDB->query( sqlLimit($strState, 'numpost DESC', 0, $_SESSION[QT]['viewmode']==='C' ? 2 : 5) );
while($row = $oDB->getRow()) {
  echo '<tr><td><a href="'.url('qti_user.php').'?id='.$row['id'].'">'.$row['name'].'</a></td><td class="right">'.qtK((int)$row['numpost']).'</td></tr>';
}
echo '</table>
</div>';

// Form Add User

if ( !empty($formAddUser) ) echo $formAddUser;

echo '</div>
';
// ------
// Button line and paging
// ------
// -- build paging --
$strPaging = makePager( url('qti_users.php?fg='.$fg.'&po='.$po.'&pd='.$pd), $intCount, (int)$_SESSION[QT]['items_per_page'], $pn );
if ( !empty($strPaging) ) $strPaging = $L['Page'].$strPaging;
if ( $intCount<$intTotal ) $strPaging = L('user',$intCount).' '.L('from').' '.$intTotal.(empty($strPaging) ? '' : ' | '.$strPaging);

// -- Display button line (if more that tpp users) and paging --
if ( $intCount>$_SESSION[QT]['items_per_page'] || $fg!=='all' ) echo htmlLettres(url($oH->selfurl),$fg,L('All'),'lettres',L('Username_starting').' ', $intTotal>300 ? 1 : ($intTotal>2*$_SESSION[QT]['items_per_page'] ? 2 : 3)).PHP_EOL;

if ( !empty($strPaging) ) echo '<p class="paging">'.$strPaging.'</p>'.PHP_EOL;

// end if no result
if ( $intCount==0)
{
  echo '<p>'.L('None').'</p><br>';
  include 'qti_inc_ft.php';
  exit;
}

// ------
// Memberlist
// ------
$bCompact = FALSE;
if ( empty(qtExplodeGet($_SESSION[QT]['formatpicture'], 'mime')) ||  $_SESSION[QT]['viewmode']=='C' ) $bCompact = true;

// Table definition
$t = new TabTable('id=t1|class=t-user',$intCount);
$t->thead();
$t->tbody();
$t->activecol = 'user'.$po;
$t->activelink = '<a href="'.$oH->selfurl.'?fg='.$fg.'&po='.$po.'&pd='.($pd=='asc' ? 'desc' : 'asc').'">%s</a> '.qtSVG('caret-'.($pd==='asc' ? 'down' : 'up'));
// TH
if ( !$bCompact )
$t->arrTh['userphoto']  = new TabHead(qtSVG('camera'));
$t->arrTh['username'] = new TabHead(L('Username'), '', '<a href="'.$oH->selfurl.'?fg='.$fg.'&po=name&pd=asc">%s</a>');
$t->arrTh['userrole'] = new TabHead(L('Role'), '', '<a href="'.$oH->selfurl.'?fg='.$fg.'&po=role&pd=asc">%s</a>');
$t->arrTh['usercontact'] = new TabHead(L('Contact'));
$t->arrTh['userlocation'] = new TabHead(L('Location'), '', '<a href="'.$oH->selfurl.'?fg='.$fg.'&po=location&pd=asc">%s</a>');
$t->arrTh['usernumpost'] = new TabHead(qtSVG('comments'), 'class=ellipsis', '<a href="'.$oH->selfurl.'?fg='.$fg.'&po=numpost&pd=desc">%s</a>');
if ( SUser::isStaff() )
$t->arrTh['userpriv'] = new TabHead(qtSVG('info'), 'title='.L('Privacy'));
foreach(array_keys($t->arrTh) as $key)
$t->arrTh[$key]->append('class','c-'.$key);
// TD
$t->cloneThTd();

// === TABLE START DISPLAY ===

echo $t->start();
echo $t->thead->start();
echo $t->getTHrow();
echo $t->thead->end();
echo $t->tbody->start();

$oDB->query( sqlLimit('* FROM TABUSER WHERE id>0'.$sqlWhere, $po.' '.strtoupper($pd), $sqlStart,$_SESSION[QT]['items_per_page'],$intCount) );

$intWhile=0;
while($row=$oDB->getRow())
{
	// privacy control for map and location field
	if ( !SUser::canSeePrivate((int)$row['privacy'],(int)$row['id']) ) { $row['y']=null; $row['x']=null; }

	// prepare row
  if ( !$bCompact )
  $t->arrTd['userphoto']->content = '<div class="magnifier center">'.SUser::getPicture((int)$row['id'], 'data-magnify=0|onclick=this.dataset.magnify=this.dataset.magnify==1?0:1;', '').'</div>';
  $t->arrTd['username']->content = '<a href="'.url('qti_user.php').'?id='.$row['id'].'">'.qtTrunc($row['name'],24).'</a>';
  $t->arrTd['userrole']->content = L('Role_'.strtoupper($row['role']));
  $t->arrTd['usercontact']->content = renderUserMailSymbol($row).' '.renderUserWwwSymbol($row);
  $t->arrTd['userlocation']->content = empty($row['location']) ? ' ' : $row['location'];
  $t->arrTd['usernumpost']->content = qtK((int)$row['numpost']);
  if ( isset($t->arrTh['userpriv']) )
  $t->arrTd['userpriv']->content = renderUserPrivSymbol($row);

	//show row
	echo $t->getTDrow('class=t-user hover');

	// map settings
	if ( $useMap && !gmapEmpty($row['x']) && !gmapEmpty($row['y']) )
	{
		$y = (float)$row['y']; $x = (float)$row['x'];
		$strPname = $row['name'];
		$strPinfo = $row['name'].'<br><a class="gmap" href="'.url('qti_user.php').'?id='.$row['id'].'">Open profile &raquo;</a>';
    $strPinfo = SUser::getPicture((int)$row['id'], 'class=markerprofileimage', '').$strPinfo;
		$oMapPoint = new CMapPoint($y,$x,$strPname,$strPinfo);
		if ( !empty($arrSymbolByRole[$row['role']]) ) $oMapPoint->marker = $arrSymbolByRole[$row['role']];
		$arrExtData[(int)$row['id']] = $oMapPoint;
	}

	$intWhile++;
	//odbcbreak
	if ( $intWhile>=$_SESSION[QT]['items_per_page'] ) break;
}

// === TABLE END DISPLAY ===

echo $t->tbody->end();
echo $t->end();

// -- Display paging --

if ( !empty($strPaging) )
{
echo '<p class="paging">'.$strPaging.'</p>'.PHP_EOL;
}

//show table caption
if ( SUser::role()!=='U' ) echo '<p class="disabled right small">Only staff members see privacy settings</p>';

// MAP MODULE, Show map

if ( $useMap )
{
  echo '<!-- Map module -->'.PHP_EOL;
  if ( count($arrExtData)===0 ) {
    echo '<div class="gmap_disabled">'.L('Gmap.No_coordinates').'</div>';
    $useMap=false;
  } else {
    //select zoomto (maximum 20 items in the list)
    $str = '';
    if ( count($arrExtData)>1 ) {
      $str = '<p class="gmap commands" style="margin:0 0 4px 0"><a class="gmap" href="javascript:void(0)" onclick="zoomToFullExtend(); return false;">'.L('Gmap.zoomtoall').'</a> | '.L('Show').' <select class="gmap" id="zoomto" name="zoomto" size="1" onchange="gmapPan(this.value);">';
      $str .= '<option class="small_gmap" value="'.$_SESSION[QT]['m_gmap_gcenter'].'"> </option>';
      $i=0;
      foreach($arrExtData as $oMapPoint) {
        $str .= '<option class="small_gmap" value="'.$oMapPoint->y.','.$oMapPoint->x.'">'.$oMapPoint->title.'</option>';
        $i++; if ( $i>20 ) break;
      }
      $str .= '</select></p>'.PHP_EOL;
    }
    echo '<div class="gmap">'.PHP_EOL;
    echo ($_SESSION[QT]['m_gmap_hidelist'] ? '' : $str.PHP_EOL.'<div id="map_canvas"></div>'.PHP_EOL);
    echo '<p class="gmap" style="margin:4px 0 0 0">'.sprintf( L('Gmap.items'), strtolower( L('User',count($arrExtData))), strtolower(L('User',$intCount)) ).'</p>'.PHP_EOL;
    echo '</div>'.PHP_EOL;
    // Show/Hide
    if ( $_SESSION[QT]['m_gmap_hidelist'] ) {
      echo '<div class="canvashandler"><a class="canvashandler" href="'.url($oH->selfurl).'?showmap">'.qtSVG('chevron-down').' '.L('Gmap.Show_map').'</a></div>'.PHP_EOL;
    } else {
      echo '<div class="canvashandler"><a class="canvashandler" href="'.url($oH->selfurl).'?hidemap">'.qtSVG('chevron-up').' '.L('Gmap.Hide_map').'</a></div>'.PHP_EOL;
    }
  }
  echo '<!-- Map module end -->'.PHP_EOL;
}

// ------
// HTML END
// ------
// MAP MODULE

if ( $useMap && !$_SESSION[QT]['m_gmap_hidelist'] )
{
  /**
   * @var array $gmap_markers
   * @var array $gmap_events
   * @var array $gmap_functions
   */
  $gmap_symbol = false;
  if ( !empty($_SESSION[QT]['m_gmap_gsymbol']) )
  {
    $arr = explode(' ',$_SESSION[QT]['m_gmap_gsymbol']);
    $gmap_symbol=$arr[0];
  }

  // check new map center
  $y = floatval(QTgety($_SESSION[QT]['m_gmap_gcenter']));
  $x = floatval(QTgetx($_SESSION[QT]['m_gmap_gcenter']));

  // center on the first item
  foreach($arrExtData as $oMapPoint)
  {
    if ( !empty($oMapPoint->y) && !empty($oMapPoint->x) )
    {
    $y=$oMapPoint->y;
    $x=$oMapPoint->x;
    break;
    }
  }
  // update center
  $_SESSION[QT]['m_gmap_gcenter'] = $y.','.$x;

  $gmap_markers = [];
  $gmap_events = [];
  $gmap_functions = [];
  foreach($arrExtData as $oMapPoint)
  {
    if ( !empty($oMapPoint->y) && !empty($oMapPoint->x) )
    {
      $user_symbol = $gmap_symbol; // required to reset symbol on each user
      if ( !empty($oMapPoint->marker) ) $user_symbol = $oMapPoint->marker;
      $gmap_markers[] = gmapMarker($oMapPoint->y.','.$oMapPoint->x, false, $user_symbol, $oMapPoint->title,$oMapPoint->info);
    }
  }
  $gmap_functions[] = '
  function zoomToFullExtend()
  {
    if ( markers.length<2 ) return;
    var bounds = new google.maps.LatLngBounds();
    for (var i=markers.length-1; i>=0; i--) bounds.extend(markers[i].position);
    gmap.fitBounds(bounds);
  }
  function showLocation(address)
  {
    if ( gmapInfoBox ) gmapInfoBox.close();
    gmapCoder.geocode( { "address": address}, function(results, status) {
      if ( status==google.maps.GeocoderStatus.OK) {
        gmap.setCenter(results[0].geometry.location);
        if ( marker ) {
          marker.position = results[0].geometry.location;
        } else {
          marker = new google.maps.marker.AdvancedMarkerElement({map: gmap, position: results[0].geometry.location, draggable: true, title: "Move to define the default map center"});
        }
      } else {
        alert("Geocode was not successful for the following reason: " + status);
      }
    });
  }

  ';
  include 'qtim_gmap_load.php';
}

include 'qti_inc_ft.php';