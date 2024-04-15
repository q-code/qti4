<?php

/**
* PHP versions 5
*
* LICENSE: This source file is subject to version 3.0 of the PHP license
* that is available through the world-wide-web at the following URI:
* http://www.php.net/license. If you did not receive a copy of
* the PHP License and are unable to obtain it through the web, please
* send a note to license@php.net so we can mail you a copy immediately.
*
* @package    QuickTicket
* @author     Philippe Vandenberghe <info@qt-cute.org>
* @copyright  20013 The PHP Group
* @version    4.0 build:20240210
*/

session_start();
/**
 * @var CHtml $oH
 * @var CDatabase $oDB
 * @var array $L
 */
require 'bin/init.php';
include translate('lg_adm.php');
if ( SUser::role()!=='A' ) die(L('E_13'));
if ( empty($_SESSION[QT]['m_gmap_gkey']) ) die('Missing google map api key. First go to the Map administration page.');

include translate('qtim_gmap.php');
include translate('qtim_gmap_adm.php');
include 'qtim_gmap_lib.php';

// INITIALISE
$oH->selfurl = 'qtim_gmap_adm_sections.php';
$oH->selfname = 'Gmap';
$oH->selfparent = L('Module');
$oH->exiturl = 'qtim_gmap_adm.php';
$oH->exitname = $oH->selfname;
$arrSections = CSection::getSections('A'); // sql

// Read png/svg in directory
$files = [];
foreach(glob(APP.'m_gmap/*.*g') as $file) {
  $file = substr($file,10);
  if ( strpos($file,'_shadow') ) continue;
  $name = ucfirst(str_replace('_',' ',qtDropExt($file)));
  $files[$file] = empty($name) ? L('Gmap.Default') : $name;
}

// ------
// SUBMITTED for changes
// ------
if ( isset($_POST['ok']) ) try {

  // save setting files
  $file = 'qtim_gmap/config_gmap.json';
  $arrData = [];
  $arrData['U'] = array('enabled'=>(isset($_POST['sec_U']) ? 1 : 0));
  if ( $arrData['U']['enabled']==1 && isset($_POST['list_U']) ) $arrData['U']['list'] = (int)$_POST['list_U'];
  if ( $arrData['U']['enabled']==1 && isset($_POST['mark_U']) ) $arrData['U']['icon'] = $_POST['mark_U'];
  $arrData['S'] = array('enabled'=>(isset($_POST['sec_S']) ? 1 : 0));
  if ( $arrData['S']['enabled']==1 && isset($_POST['list_S']) ) $arrData['S']['list'] = (int)$_POST['list_S'];
  if ( $arrData['S']['enabled']==1 && isset($_POST['mark_S']) ) $arrData['S']['icon'] = $_POST['mark_S'];
  foreach(array_keys($arrSections) as $id) {
    $arrData[$id] = array('enabled'=>(isset($_POST['sec_'.$id]) ? 1 : 0));
    if ( $arrData[$id]['enabled']==1 && isset($_POST['list_'.$id]) ) $arrData[$id]['list'] = (int)$_POST['list_'.$id];
    if ( $arrData[$id]['enabled']==1 && isset($_POST['mark_'.$id]) ) $arrData[$id]['icon'] = $_POST['mark_'.$id];
  }
  if ( !is_writable($file) ) throw new Exception("Impossible to write into the file [$file].");
  if ( !$handle = fopen($file, 'w')) throw new Exception("Impossible to open the file [$file].");
  if ( fwrite($handle, json_encode($arrData))===FALSE ) { fclose($handle); throw new Exception("Impossible to write into the file [$file]."); }
  fclose($handle);
  // exit
  $_SESSION[QT.'splash'] = L('S_save');

} catch (Exception $e) {

  // Splash short message and send error to ...inc_hd.php
  $_SESSION[QT.'splash'] = 'E|'.L('E_failed');
  $oH->error = $e->getMessage();

}

// ------
// HTML BEGIN
// ------
// prepare section settings
$json = file_exists('qtim_gmap/config_gmap.json') ? file_get_contents('qtim_gmap/config_gmap.json') :  '{}';

$arrConfig = json_decode($json,true); // decode as an array
$arrSections = SMem::get('_Sections');

$oH->scripts[] = 'function mapsection(section){
  if ( document.getElementById("sec_"+section).checked) {
    document.getElementById("mark_"+section).style.visibility="visible";
    document.getElementById("list_"+section).style.visibility="visible";
  } else {
    document.getElementById("mark_"+section).style.visibility="hidden";
    document.getElementById("list_"+section).style.visibility="hidden";
  }
}';

// DISPLAY

include APP.'_adm_inc_hd.php';

echo '<form class="formsafe" method="post" action="'.$oH->self().'">
<h2 class="config">'.L('Section+').'</h2>
<div class="pan">
<p>'.L('Gmap.Allowed').'</p>
<table class="subtable">
<tr>
<th style="width:40px"></th>
<th>'.L('Section+').'</th>
<th>'.L('Gmap.symbols').'</th>
<th>'.L('Gmap.Main_list').'</th>
</tr>
';

foreach(array_keys($arrSections) as $id) {

if ( !isset($arrConfig[$id]['enabled']) ) $arrConfig[$id]['enabled'] = 0;
if ( !isset($arrConfig[$id]['list']) ) $arrConfig[$id]['list'] = 0;

echo '<tr class="hover">
<td><input type="checkbox" id="sec_'.$id.'" name="sec_'.$id.'"'.($arrConfig[$id]['enabled']==0 ? '' : ' checked').' style="vertical-align: middle" onclick="mapsection(`'.$id.'`)"/></td>
<td><label for="sec_'.$id.'">'.(isset($arrSections[$id]['title']) ? $arrSections[$id]['title'] : '[section '.$id.']').'</label></td>
<td>
<select class="small" id="mark_'.$id.'" name="mark_'.$id.'" size="1" style="'.($arrConfig[$id]['enabled']==0 ? 'visibility:hidden' : '').'">
'.qtTags($files,(isset($arrConfig[$id]['icon']) ? $arrConfig[$id]['icon'] : '')).'
</select>
</td>
<td>
<select class="small" id="list_'.$id.'" name="list_'.$id.'" size="1" style="'.($arrConfig[$id]['enabled']==0 ? 'visibility:hidden' : '').'">
'.qtTags(L('Gmap.List.*'),$arrConfig[$id]['list']).'
</select>
</td>
</tr>
';
}

echo '
<tr>
<th></th>
<th>'.L('Page').'</th>
<th>'.L('Gmap.symbols').'</th>
<th>'.L('Gmap.Main_list').'</th>
</tr>
';

$id = 'S';
if ( !isset($arrConfig[$id]['enabled']) ) $arrConfig[$id]['enabled'] = 0;
if ( !isset($arrConfig[$id]['list']) ) $arrConfig[$id]['list'] = 0;
echo '<tr class="hover">
<td><input type="checkbox" id="sec_'.$id.'" name="sec_'.$id.'"'.($arrConfig[$id]['enabled']==0 ? '' : ' checked').' style="vertical-align: middle" onclick="mapsection(`'.$id.'`)"/></td>
<td><label for="sec_'.$id.'">'.L('Search_result').'</label></td>
<td>
<select class="small" id="mark_'.$id.'" name="mark_'.$id.'" size="1" style="'.($arrConfig[$id]['enabled']==0 ? 'visibility:hidden' : '').'">
<option value="S">'.L('Gmap.From_section').'</option>
'.qtTags($files,(isset($arrConfig[$id]['icon']) ? $arrConfig[$id]['icon'] : null)).'
</select>
</td>
<td>
<select class="small" id="list_'.$id.'" name="list_'.$id.'" size="1" style="'.($arrConfig[$id]['enabled']==0 ? 'visibility:hidden' : '').'">
'.qtTags(array_slice(L('Gmap.List.*'),1),$arrConfig[$id]['list']).'
</select>
</td>
</tr>
';

$id = 'U';
if ( !isset($arrConfig[$id]['enabled']) ) $arrConfig[$id]['enabled'] = 0;
if ( !isset($arrConfig[$id]['list']) ) $arrConfig[$id]['list'] = 0;
echo '<tr class="hover">
<td><input type="checkbox" id="sec_'.$id.'" name="sec_'.$id.'"'.($arrConfig[$id]['enabled']==0 ? '' : ' checked').' style="vertical-align: middle" onclick="mapsection(`'.$id.'`)"/></td>
<td><label for="sec_'.$id.'">'.L('Memberlist').'</label></td>
<td>
<select class="small" id="mark_'.$id.'" name="mark_'.$id.'" size="1" style="'.($arrConfig[$id]['enabled']==0 ? 'visibility:hidden' : '').'">
'.qtTags($files,(isset($arrConfig[$id]['icon']) ? $arrConfig[$id]['icon'] : null)).'
</select>
</td>
<td>
<select class="small" id="list_'.$id.'" name="list_'.$id.'" size="1" style="'.($arrConfig[$id]['enabled']==0 ? 'visibility:hidden' : '').'">
'.qtTags(L('Gmap.List.*'), $arrConfig[$id]['list']).'
</select>
</td>
</tr>
</table>
';

echo '<p class="submit">
<button type="button" name="cancel" value="cancel" onclick="window.location=`'.$oH->exit().'`;">'.L('Cancel').'</button> <button type="submit" name="ok" value="save">'.L('Save').'</button>
</p>
</div>
</form>
';

// show table symbols
echo '<br>
<h2 class="config">'.L('Gmap.symbols').'</h2>
<div class="flex-sp t-conf" style="padding:1rem">
';
foreach ($files as $file=>$name)
echo '<p class="center"><img alt="i" class="marker" src="qtim_gmap/'.$file.'"/><br><small>'.$name.'</small></p>';
echo '</div>
';

// HTML END

include APP.'_adm_inc_ft.php';