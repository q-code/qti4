<?php // v4.0 build:20240210

session_start();
/**
 * @var CHtml $oH
 * @var CDatabase $oDB
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

// check register initialized
foreach(['m_gmap_gkey','m_gmap_gcenter','m_gmap_gzoom','m_gmap_gfind','m_gmap_gsymbol','m_gmap_sections'] as $key) {
  if ( !isset($_SESSION[QT][$key]) ) $_SESSION[QT][$key] = '';
}

// m_gmap_options (mt=maptype[T|S|H|R] bg=background sc=scale fs=fullscreen mw=mousewhell sv=streetview gc=geocode)
if ( empty($_SESSION[QT]['m_gmap_options']) ) $_SESSION[QT]['m_gmap_options'] = 'mt=T;bg=1;sc=1;fs=1;mw=0;sv=0;gc=0';

// Sections
$arrSections = CSection::getSections('A');

// Read png in directory (shadow is obsolete)
$files = [];
foreach(glob('qtim_gmap/*.png') as $file) {
  $file = substr($file,10,-4);
  if ( strpos($file,'_shadow') ) continue;
  $files[$file] = ucfirst(str_replace('_',' ',$file));
}

// ------
// SUBMITTED
// ------
if ( isset($_POST['ok']) ) try {

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
    if ( isset($_POST['m_gmap_gsymbol']) ) $_SESSION[QT]['m_gmap_gsymbol'] = trim($_POST['m_gmap_gsymbol']);
    if ( empty($_SESSION[QT]['m_gmap_gsymbol']) || $_SESSION[QT]['m_gmap_gsymbol']=='default' ) $_SESSION[QT]['m_gmap_gsymbol']=''; // filename (without .png) or '' default symbol
    if ( isset($_POST['m_gmap_section']) ) $_SESSION[QT]['m_gmap_section'] = substr(trim($_POST['sections']),0,1);
    if ( isset($_POST['options']) ) $_SESSION[QT]['m_gmap_options'] = qtImplode($_POST['options'],';');

    // store configuration
    $oDB->updSetting( ['m_gmap_gcenter','m_gmap_gzoom','m_gmap_options','m_gmap_gfind','m_gmap_gsymbol','m_gmap_sections'] );
  }

} catch (Exception $e) {

  // Splash short message and send error to ...inc_hd.php
  $_SESSION[QT.'splash'] = 'E|'.L('E_failed');
  $oH->error = $e->getMessage();

}

// ------
// HTML BEGIN
// ------
// prepare section settings
$_SESSION[QT]['m_gmap'] = [];
if ( file_exists('qtim_gmap/config_db.php') ) require 'qtim_gmap/config_db.php';

  if ( !isset($_SESSION[QT]['m_gmap']['U']) ) $_SESSION[QT]['m_gmap']['U'] = [false];
  foreach(array_keys($arrSections) as $id)
  if ( !isset($_SESSION[QT]['m_gmap'][$id]) ) $_SESSION[QT]['m_gmap'][$id] = [false];
  if ( !isset($_SESSION[QT]['m_gmap']['S']) ) $_SESSION[QT]['m_gmap']['S'] = [false];

if ( $_SESSION[QT]['m_gmap_gzoom']==='' ) $_SESSION[QT]['m_gmap_gzoom']='7';


$oH->links[]='<link rel="stylesheet" type="text/css" href="qtim_gmap.css"/>';
$oH->scripts[] = 'var enterkeyPressed=false;
function ValidateForm(theForm,enterkeyPressed) {
  if ( enterkeyPressed) return false;
}
';

include APP.'_adm_inc_hd.php';

echo '
<form class="formsafe" method="post" action="'.url($oH->selfurl).'">
<h2 class="config">'.L('Gmap.Mapping_settings').'</h2>
<table class="t-conf">
<tr>
<th style="width:120px"><label for="m_gmap_gkey">Google API key</label></td>
<td colspan="2"><input type="text" id="m_gmap_gkey" name="m_gmap_gkey" size="70" maxlength="100" value="'.$_SESSION[QT]['m_gmap_gkey'].'"/></td>
</tr>
';

//------
if ( !empty($_SESSION[QT]['m_gmap_gkey']) ) {
//------

$current = empty($_SESSION[QT]['m_gmap_gsymbol']) ? 'default' : $_SESSION[QT]['m_gmap_gsymbol']; // current symbol
echo '<tr>
<th style="width:150px">'.L('Gmap.API_ctrl').'</th>
<td>
<input type="checkbox" id="cb-bg" value="1" name="options[bg]"'.(empty(gmapOption('bg')) ? '' : 'checked').'/> <label for="cb-bg">'.L('Gmap.Ctrl.Background').'</label>
&nbsp; <input type="checkbox" id="cb-sc" value="1" name="options[sc]"'.(empty(gmapOption('sc')) ? '' : 'checked').'/> <label for="cb-sc">'.L('Gmap.Ctrl.Scale').'</label>
&nbsp; <input type="checkbox" id="cb-fs" value="1" name="options[fs]"'.(empty(gmapOption('fs')) ? '' : 'checked').'/> <label for="cb-fs">'.L('Gmap.Ctrl.Fullscreen').'</label>
&nbsp; <input type="checkbox" id="cb-mw" value="1" name="options[mw]"'.(empty(gmapOption('mw')) ? '' : 'checked').'/> <label for="cb-mw">'.L('Gmap.Ctrl.Mousewheel').'</label>
</td>
</tr>
<th style="width:150px">'.L('Gmap.API_services').'</th>
<td><input type="checkbox" id="cb-sv" value="1" name="options[sv]"'.(empty(gmapOption('sv')) ? '' : 'checked').'/> <label for="cb-sv">'.L('Gmap.Ctrl.Streetview').'</label>
&nbsp; <input type="checkbox" id="cb-gc" value="1" name="options[gc]"'.(empty(gmapOption('gc')) ? '' : 'checked').'/> <label for="cb-gc">'.L('Gmap.Ctrl.Geocode').'</label></td>
</tr>
<tr>
<th style="width:150px">'.L('Gmap.Default_symbol').'</th>
<td style="display:flex;gap:1.5rem;align-items:flex-end">
<p><img id="dflt-marker" class="markerpicked" src="'.APP.'m_gmap/'.$current.'.png" alt="i" title="default"/></p>
<p class="markerpicker small">'.L('Gmap.Click_to_change').'<br>
';
foreach ($files as $file=>$name) {
  echo '<input type="radio" id="symbol_'.$file.'" data-preview="dflt-marker" data-src="'.APP.'m_gmap/'.$file.'.png" name="m_gmap_gsymbol" value="'.$file.'"'.($current===$file ? 'checked' : '').' onchange="document.getElementById(this.dataset.preview).src=this.dataset.src;" style="display:none"/><label for="symbol_'.$file.'"><img class="marker" title="'.$name.'" src="'.APP.'m_gmap/'.$file.'.png" alt="i" aria-checked="'.($current===$file ? 'true' : 'false').'"/></label>'.PHP_EOL;
}
echo '</p></td>
</tr>
<tr>
<th style="width:120px;">'.L('Section+').'</td>
<td colspan="2"><strong>
'.CountMapSections().'</strong>/'.count(SMem::get('_Sections')).
(IsMapSection('U') ? ' '.L('and').' '.L('Users') : '').
(IsMapSection('S') ? ' '.L('and').' '.L('Search_result') : '').
' &middot; <a href="'.url('qtim_gmap_adm_sections.php').'">'.L('Gmap.define_sections').'...</a>
</td>
</tr>
</table>
';

echo '<h2 class="config">'.L('Gmap.Mapping_config').'</h2>
<table class="t-conf">
<tr>
<th style="width:150px;">'.L('Gmap.Center').'</th>
<td style="width:310px;"><input type="text" id="m_gmap_gcenter" name="m_gmap_gcenter" size="26" maxlength="100" value="'.$_SESSION[QT]['m_gmap_gcenter'].'"/><small> '.L('Gmap.Latlng').'</span></td>
<td><small>'.L('Gmap.H_Center').'</span></td>
</tr>
<tr>
<th style="width:150px;">'.L('Gmap.Zoom').'</th>
<td>
<input type="text" id="m_gmap_gzoom" name="m_gmap_gzoom" size="2" maxlength="2" value="'.$_SESSION[QT]['m_gmap_gzoom'].'"/></td>
<td><small>'.L('Gmap.H_Zoom').'</span></td>
</tr>
<tr>
<th style="width:150px;">'.L('Gmap.Background').'</th>
<td><select id="maptype" name="maptype" size="1">'.qtTags(L('Gmap.Back.*'),gmapOption('mt')).'</select></td>
<td><small>'.L('Gmap.H_Background').'</span></td>
</tr>
<tr>
<th style="width:150px;">'.L('Gmap.Address_sample').'</th>
<td>
<input'.(!empty(gmapOption('gc')) ? '' : 'disabled').' type="text" id="m_gmap_gfind" name="m_gmap_gfind" size="20" maxlength="100" value="'.$_SESSION[QT]['m_gmap_gfind'].'"/></td>
<td><small>'.(!empty(gmapOption('gc')) ? L('Gmap.H_Address_sample') : L('Gmap.Ctrl.Geocode').' (off)').'</span></td>
</tr>
';

//------
}
//------

echo '</table>
<p style="text-align:center"><button type="submit" name="ok" value="save">'.L('Save').'</button></p>
</form>
';

if ( !empty($_SESSION[QT]['m_gmap_gkey']) )
{
  $oCanvas = new CCanvas();
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

  $gmap_markers = [];
  $gmap_events = [];
  $gmap_functions = [];

  $gmap_markers[] = gmapMarker($_SESSION[QT]['m_gmap_gcenter'],true,$gmap_symbol,L('Gmap.Default_center'),'',$gmap_shadow);
  $gmap_events[] = '
  markers[0].addListener("drag", ()=>{ document.getElementById("yx").value = gmapRound(markers[0].position.lat,10) + "," + gmapRound(markers[0].position.lng,10); });
	google.maps.event.addListener(markers[0], "dragend", function() { map.panTo(markers[0].position);	});';
  $gmap_functions[] = '
  function undoChanges()
  {
  	if ( infowindow) infowindow.close();
  	if ( markers[0]) markers[0].setPosition(mapOptions.center);
  	if ( mapOptions) map.panTo(mapOptions.center);
  	return null;
  }
  function showLocation(address,title)
  {
    if ( infowindow ) infowindow.close();
    geocoder.geocode( { "address": address}, function(results, status) {
      if ( status == google.maps.GeocoderStatus.OK)
      {
        map.setCenter(results[0].geometry.location);
        if ( markers[0] )
        {
          markers[0].setPosition(results[0].geometry.location);
        } else {
          markers[0] = new google.maps.marker.AdvancedMarkerElement({map: map, position: results[0].geometry.location, draggable: true, title: title});
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