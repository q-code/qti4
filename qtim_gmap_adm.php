<?php // v4.0 build:20230205

session_start();
/**
 * @var CHtml $oH
 * @var CDatabase $oDB
 * @var string $error
 * @var array $gmap_markers
 * @var array $gmap_events
 * @var array $gmap_functions
 */
require 'bin/init.php';
include translate('lg_adm.php');
if ( SUser::role()!=='A' ) die('Access denied');

include translate('qtim_gmap.php');
include translate('qtim_gmap_adm.php');
include 'qtim_gmap_lib.php';

function IsMapSection($id=0){
  $o = getMapSectionSettings($id);
  if ( empty($o) ) return false;
  if ( property_exists($o,'enabled') && $o->enabled>0 ) return true;
  return false;
}
function CountMapSections(){
  $i=0;
  foreach(array_keys(SMem::get('_Sections')) as $id) if ( IsMapSection($id) ) ++$i;
  return $i;
}

// INITIALISE

$oH->selfurl = 'qtim_gmap_adm.php';
$oH->selfname = 'Gmap';
$oH->selfparent = L('Module');
$oH->selfversion = L('Gmap.Version').' 4.0';
$oH->exiturl = $oH->selfurl;
$oH->exitname = $oH->selfname;

// read values
foreach(array('m_gmap_gkey','m_gmap_gcenter','m_gmap_gzoom','m_gmap_gfind','m_gmap_gbuttons') as $strValue)
{
  if ( !isset($_SESSION[QT][$strValue]) )
  {
  $arr = $oDB->getSettings('param="'.$strValue.'"',true);
  if ( empty($arr) ) die('<span class="error">Parameters not found. The module is probably not installed properly.</span><br><br><a href="qti_adm_index.php">&laquo;&nbsp;'.L('Exit').'</a>');
  }
}

$arrSections = qtArrget(getSections('A'));

// Read png in directory

$intHandle = opendir('qtim_gmap');
$arrFiles = array();
while ( false!==($strFile = readdir($intHandle)) )
{
  if ( $strFile!='.' && $strFile!='..' ) {
  if ( substr($strFile,-4,4)=='.png' ) {
  if ( !strpos($strFile,'shadow') ) {
    $arrFiles[substr($strFile,0,-4)]=ucfirst(substr(str_replace('_',' ',$strFile),0,-4));
  }}}
}
closedir($intHandle);
asort($arrFiles);

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{
  // save gkey
  if ( isset($_POST['m_gmap_gkey']) ) {
    $_SESSION[QT]['m_gmap_gkey'] = trim($_POST['m_gmap_gkey']); if ( strlen($_SESSION[QT]['m_gmap_gkey'])<8 ) $_SESSION[QT]['m_gmap_gkey'] = '';
    // store configuration
    $oDB->updSetting('m_gmap_gkey');
  }
  // save others if gkey
  if ( !empty($_SESSION[QT]['m_gmap_gkey']) ) {
    if ( isset($_POST['m_gmap_gcenter']) ) $_SESSION[QT]['m_gmap_gcenter'] = trim($_POST['m_gmap_gcenter']);
    if ( isset($_POST['m_gmap_gzoom']) )   $_SESSION[QT]['m_gmap_gzoom'] = trim($_POST['m_gmap_gzoom']);
    if ( isset($_POST['m_gmap_gfind']) )   $_SESSION[QT]['m_gmap_gfind'] = qtAttr($_POST['m_gmap_gfind']);
    if ( isset($_POST['m_gmap_gsymbol']) ) $_SESSION[QT]['m_gmap_gsymbol'] = trim($_POST['m_gmap_gsymbol']); if ( empty($_SESSION[QT]['m_gmap_gsymbol']) || $_SESSION[QT]['m_gmap_gsymbol']=='default' ) $_SESSION[QT]['m_gmap_gsymbol']='0'; // "iconname" (without extension) or '0' default symbol
    // m_gmap_gbuttons = maptype.streetview.background.scale.fullscreen.mousewheel.geocode
    if ( isset($_POST['maptype']) )        $_SESSION[QT]['m_gmap_gbuttons'][0] = substr($_POST['maptype'],0,1);
    if ( isset($_POST['streetview']) )     $_SESSION[QT]['m_gmap_gbuttons'][1] = '1';
    if ( isset($_POST['map']) )            $_SESSION[QT]['m_gmap_gbuttons'][2] = '1';
    if ( isset($_POST['scale']) )          $_SESSION[QT]['m_gmap_gbuttons'][3] = '1';
    if ( isset($_POST['fullscreen']) )     $_SESSION[QT]['m_gmap_gbuttons'][4] = '1';
    if ( isset($_POST['mousewheel']) )     $_SESSION[QT]['m_gmap_gbuttons'][5] = '1';
    if ( isset($_POST['geocode']) )        $_SESSION[QT]['m_gmap_gbuttons'][6] = '1';
    // store configuration
    $oDB->updSetting( ['m_gmap_gcenter','m_gmap_gzoom','m_gmap_gbuttons','m_gmap_gfind','m_gmap_gsymbol'] );
  }

  // exit
  $_SESSION[QT.'splash'] = (empty($error) ? L('S_save') : 'E|'.$error);
}

// --------
// HTML BEGIN
// --------

// prepare section settings

$_SESSION[QT]['m_gmap'] = array();
if ( file_exists('qtim_gmap/config_db.php') ) require 'qtim_gmap/config_db.php';

  if ( !isset($_SESSION[QT]['m_gmap']['U']) ) $_SESSION[QT]['m_gmap']['U'] = array(0=>false);
  foreach(array_keys($arrSections) as $intSecid)
  {
  if ( !isset($_SESSION[QT]['m_gmap'][$intSecid]) ) $_SESSION[QT]['m_gmap'][$intSecid] = array(0=>false);
  }
  if ( !isset($_SESSION[QT]['m_gmap']['S']) ) $_SESSION[QT]['m_gmap']['S'] = array(0=>false);

if ( $_SESSION[QT]['m_gmap_gzoom']==='' ) $_SESSION[QT]['m_gmap_gzoom']='7';


$oH->links[]='<link rel="stylesheet" type="text/css" href="qtim_gmap.css"/>';
$oH->scripts[] = 'function previewMarker(src) { document.getElementById("markerpicked").src = src; }';

include APP.'_adm_inc_hd.php';

echo '
<form method="post" action="'.Href($oH->selfurl).'">
<h2 class="config">'.L('Gmap.Mapping_settings').'</h2>
<table class="t-conf">
<tr>
<th style="width:120px"><label for="m_gmap_gkey">Google API key</label></td>
<td colspan="2"><input type="text" id="m_gmap_gkey" name="m_gmap_gkey" size="70" maxlength="100" value="'.$_SESSION[QT]['m_gmap_gkey'].'" onchange="qtFormSafe.not();"/></td>
</tr>
';

//-----------
if ( !empty($_SESSION[QT]['m_gmap_gkey']) ) {
//-----------


// current symbol
$arr=explode(' ',$_SESSION[QT]['m_gmap_gsymbol']);// read first icon (can be '0')
$strSymbol=$arr[0];

echo '<tr>
<th style="width:120px">'.L('Gmap.API_ctrl').'</td>
<td colspan="2">
<input type="checkbox" id="map" name="map"'.(substr($_SESSION[QT]['m_gmap_gbuttons'],2,1)==='1' ? ' checked' : '').' style="vertical-align: middle" onchange="qtFormSafe.not();"/><label for="map">'.L('Gmap.Ctrl.Background').'</label>
&nbsp;<input type="checkbox" id="fullscreen" name="fullscreen"'.(substr($_SESSION[QT]['m_gmap_gbuttons'],4,1)==='1' ? ' checked' : '').' style="vertical-align: middle" onchange="qtFormSafe.not();"/><label for="fullscreen">'.L('Gmap.Ctrl.Fullscreen').'</label>
&nbsp;<input type="checkbox" id="streetview" name="streetview"'.(substr($_SESSION[QT]['m_gmap_gbuttons'],1,1)==='1' ? ' checked' : '').' style="vertical-align: middle" onchange="qtFormSafe.not();"/><label for="streetview">'.L('Gmap.Ctrl.Streetview').'</label>
&nbsp;<input type="checkbox" id="scale" name="scale"'.(substr($_SESSION[QT]['m_gmap_gbuttons'],3,1)==='1' ? ' checked' : '').' style="vertical-align: middle" onchange="qtFormSafe.not();"/><label for="scale">'.L('Gmap.Ctrl.Scale').'</label>
&nbsp;<input type="checkbox" id="mousewheel" name="mousewheel"'.(substr($_SESSION[QT]['m_gmap_gbuttons'],5,1)==='1' ? ' checked' : '').' style="vertical-align: middle" onchange="qtFormSafe.not();"/><label for="mousewheel">'.L('Gmap.Ctrl.Mousewheel').'</label>
</td>
</tr>
<tr>
<th style="width:120px">'.L('Gmap.default_symbol').'</td>
<td style="width:70px;text-align:center"><img id="markerpicked" title="default" alt="i" src="'.($strSymbol==='0' ? 'bin/css/gmap_marker.png' : 'qtim_gmap/'.$strSymbol.'.png' ).'"/></td>
<td>
<p class="small" style="text-align:center">'.L('Gmap.Click_to_change').'</p>
<div class="markerpicker">
<input type="radio" id="symbol_0" data-src="bin/css/gmap_marker.png" name="m_gmap_gsymbol" value="0"'.(empty($_SESSION[QT]['m_gmap_gsymbol']) ? ' checked' : '').' onchange="previewMarker(this.dataset.src);qtFormSafe.not();"/><label for="symbol_0"><img class="marker'.($_SESSION[QT]['m_gmap_gsymbol']===$strFile ? ' checked' : '').'" title="default" src="bin/css/gmap_marker.png" alt="i"/></label>
';
foreach ($arrFiles as $strFile=>$strName)
{
echo '<input type="radio" id="symbol_'.$strFile.'" data-src="qtim_gmap/'.$strFile.'.png" name="m_gmap_gsymbol" value="'.$strFile.'"'.($strSymbol===$strFile ? ' checked' : '').' onchange="previewMarker(this.dataset.src);qtFormSafe.not();"/><label for="symbol_'.$strFile.'"><img class="marker'.($strSymbol===$strFile ? ' checked' : '').'" title="'.$strName.'" src="qtim_gmap/'.$strFile.'.png" alt="i"/></label>'.PHP_EOL;
}
echo '</div>
</td>
</tr>
<tr>
<th style="width:120px;">'.L('Section+').'</td>
<td colspan="2"><strong>
'.CountMapSections().'</strong>/'.count(SMem::get('_Sections')).
(IsMapSection('U') ? ' '.L('and').' '.L('Users') : '').
(IsMapSection('S') ? ' '.L('and').' '.L('Search_result') : '').
' &middot; <a href="'.Href('qtim_gmap_adm_sections.php').'">'.L('Gmap.define_sections').'...</a>
</td>
</tr>
</table>
';
echo '<h2 class="config">'.L('Gmap.Mapping_config').'</h2>
<table class="t-conf">
<tr>
<th style="width:120px;"><label for="m_gmap_gcenter">'.L('Gmap.Center').'</label></td>
<td style="width:280px;"><input type="text" id="m_gmap_gcenter" name="m_gmap_gcenter" size="28" maxlength="100" value="'.$_SESSION[QT]['m_gmap_gcenter'].'" onchange="qtFormSafe.not();"/><span class="small"> '.L('Gmap.Latlng').'</span></td>
<td><span class="small">'.L('Gmap.H_Center').'</span></td>
</tr>
<tr>
<th style="width:120px;"><label for="m_gmap_gzoom">'.L('Gmap.Zoom').'</label></td>
<td>
<input type="text" id="m_gmap_gzoom" name="m_gmap_gzoom" size="2" maxlength="2" value="'.$_SESSION[QT]['m_gmap_gzoom'].'" onchange="qtFormSafe.not();"/></td>
<td><span class="small">'.L('Gmap.H_Zoom').'</span></td>
</tr>
<tr>
<th style="width:120px;">'.L('Gmap.Background').'</td>
<td><select id="maptype" name="maptype" size="1" onchange="qtFormSafe.not();">'.asTags(L('Gmap.Back.*'),substr($_SESSION[QT]['m_gmap_gbuttons'],0,1)).'</select></td>
<td><span class="small">'.L('Gmap.H_Background').'</span></td>
</tr>
<tr>
<th style="width:120px;"><label for="m_gmap_gfind">'.L('Gmap.Address_sample').'</label></td>
<td>
<input type="text" id="m_gmap_gfind" name="m_gmap_gfind" size="20" maxlength="100" value="'.$_SESSION[QT]['m_gmap_gfind'].'" onchange="qtFormSafe.not();"/></td>
<td><span class="small">'.L('Gmap.H_Address_sample').'</span></td>
</tr>
';

//-----------
}
//-----------

echo '</table>
<p style="text-align:center"><button type="submit" name="ok" value="save">'.L('Save').'</button></p>
</form>
';

if ( !empty($_SESSION[QT]['m_gmap_gkey']) )
{
  $oCanvas = new cCanvas();
  $oCanvas->Header(L('Gmap.canmove').' | <a class="gmap" href="javascript:void(0)" onclick="undoChanges(); return false;"/>'.L('Gmap.undo').'</a>' );
  $oCanvas->Footer('find','','footer right');
  echo $oCanvas->Render();
}
else
{
  echo '<p class="disabled">'.L('Gmap.E_disabled').'</p>';
}

// HTML END

if ( !empty($_SESSION[QT]['m_gmap_gkey']) )
{
  $gmap_shadow = false;
  $gmap_symbol = false;
  if ( !empty($_SESSION[QT]['m_gmap_gsymbol']) )
  {
    $arr = explode(' ',$_SESSION[QT]['m_gmap_gsymbol']);
    $gmap_symbol=$arr[0];
    if ( isset($arr[1]) ) $gmap_shadow=$arr[1];
  }

  $gmap_markers = array();
  $gmap_events = array();
  $gmap_functions = array();

  $gmap_markers[] = gmapMarker($_SESSION[QT]['m_gmap_gcenter'],true,$gmap_symbol,L('Gmap.Default_center'),'',$gmap_shadow);
  $gmap_events[] = '
	google.maps.event.addListener(marker, "position_changed", function() {
		if (document.getElementById("m_gmap_gcenter")) {document.getElementById("m_gmap_gcenter").value = gmapRound(marker.getPosition().lat(),10) + "," + gmapRound(marker.getPosition().lng(),10);}
	});
	google.maps.event.addListener(marker, "dragend", function() {
		map.panTo(marker.getPosition());
	});';
  $gmap_functions[] = '
  function undoChanges()
  {
  	if (infowindow) infowindow.close();
  	if (markers[0]) markers[0].setPosition(mapOptions.center);
  	if (mapOptions) map.panTo(mapOptions.center);
  	return null;
  }
  function showLocation(address,title)
  {
    if ( infowindow ) infowindow.close();
    geocoder.geocode( { "address": address}, function(results, status) {
      if (status == google.maps.GeocoderStatus.OK)
      {
        map.setCenter(results[0].geometry.location);
        if ( markers[0] )
        {
          markers[0].setPosition(results[0].geometry.location);
        } else {
          markers[0] = new google.maps.Marker({map: map, position: results[0].geometry.location, draggable: true, animation: google.maps.Animation.DROP, title: title});
        }
        gmapYXfield("qti_gcenter",markers[0]);
      } else {
        alert("Geocode was not successful for the following reason: " + status);
      }
    });
  }
  ';
  include 'qtim_gmap_load.php';
}

include APP.'_adm_inc_ft.php';