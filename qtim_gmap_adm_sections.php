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
* @version    4.0 build:20230205
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

include translate('qtim_gmap.php');
include translate('qtim_gmap_adm.php');
include 'qtim_gmap_lib.php';

// INITIALISE

$oH->selfurl = 'qtim_gmap_adm_sections.php';
$oH->selfname = 'Map';
$oH->selfparent = L('Module');
$oH->selfversion = L('Gmap.Version').' 4.0';
$oH->exiturl = 'qtim_gmap_adm.php';
$oH->exitname = $oH->selfname;

$arrSections = qtArrget(getSections('A'));

// Read png in directory

$intHandle = opendir('qtim_gmap');
$arrFiles = array();
while ( false!==($strFile = readdir($intHandle)) )
{
  if ( $strFile!='.' && $strFile!='..' ) {
  if ( substr($strFile,-4,4)==='.png' ) {
  if ( !strpos($strFile,'shadow') ) {
    $arrFiles[substr($strFile,0,-4)]=ucfirst(substr(str_replace('_',' ',$strFile),0,-4));
  }}}
}
closedir($intHandle);
asort($arrFiles);

// --------
// SUBMITTED for changes
// --------

if ( isset($_POST['ok']) && !empty($_SESSION[QT]['m_gmap_gkey']) )
{
  // save setting files
  $strFilename = 'qtim_gmap/config_gmap.php';

  $arrData = array();
  $arrData['U'] = array('section'=>'U','enabled'=>(isset($_POST['sec_U']) ? 1 : 0));
  if ( $arrData['U']['enabled']==1 && isset($_POST['list_U']) ) $arrData['U']['list']=(int)$_POST['list_U'];
  if ( $arrData['U']['enabled']==1 && isset($_POST['mark_U']) ) $arrData['U']['icon']=$_POST['mark_U'];
  $arrData['S'] = array('section'=>'S','enabled'=>(isset($_POST['sec_S']) ? 1 : 0));
  if ( $arrData['S']['enabled']==1 && isset($_POST['list_S']) ) $arrData['S']['list']=(int)$_POST['list_S'];
  if ( $arrData['S']['enabled']==1 && isset($_POST['mark_S']) ) $arrData['S']['icon']=$_POST['mark_S'];
  foreach(array_keys($arrSections) as $id)
  {
  $arrData[$id] = array('section'=>$id,'enabled'=>(isset($_POST['sec_'.$id]) ? 1 : 0));
  if ( $arrData[$id]['enabled']==1 && isset($_POST['list_'.$id]) ) $arrData[$id]['list']=(int)$_POST['list_'.$id];
  if ( $arrData[$id]['enabled']==1 && isset($_POST['mark_'.$id]) ) $arrData[$id]['icon']=$_POST['mark_'.$id];
  }
  $content = '<?php'.PHP_EOL;
  $content .= '$jMapSections = \''.PHP_EOL;
  $content .= json_encode($arrData).PHP_EOL;
  $content .= '\';';

  if ( !is_writable($strFilename)) $error="Impossible to write into the file [$strFilename].";
  if ( empty($error) )
  {
  if ( !$handle = fopen($strFilename, 'w')) $error="Impossible to open the file [$strFilename].";
  }
  if ( empty($error) )
  {
  if ( fwrite($handle, $content)===FALSE ) $error="Impossible to write into the file [$strFilename].";
  fclose($handle);
  }

  // exit
  $_SESSION[QT.'splash'] = (empty($error) ? L('S_save') : 'E|'.$error);
}

// --------
// HTML BEGIN
// --------

// prepare section settings

if ( file_exists('qtim_gmap/config_gmap.php') ) { include 'qtim_gmap/config_gmap.php'; } else { $jMapSections = '[{"section":0,"enabled":0}]'; }

$arrConfig = json_decode($jMapSections,true); // decode as an array
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

echo '<form method="post" action="'.$oH->selfurl.'">
<h2 class="config">'.L('Section+').'</h2>
<div class="pan">
<p>'.L('Gmap.Allowed').'</p>
<table class="subtable">
<tr>
<th style="width:25px;text-align:center">&nbsp;</th>
<th>'.$L['Sections'].'</th>
<th>'.L('Gmap.symbols').'</th>
<th>'.L('Gmap.Main_list').'</th>
</tr>
';

foreach(array_keys($arrSections) as $id)
{
if ( !isset($arrConfig[$id]['enabled']) ) $arrConfig[$id]['enabled']=0;
if ( !isset($arrConfig[$id]['list']) ) $arrConfig[$id]['list']=0;
echo '<tr class="hover">
<td style="background-color:#c3d9ff;width:25px;text-align:center"><input type="checkbox" id="sec_'.$id.'" name="sec_'.$id.'"'.($arrConfig[$id]['enabled']==0 ? '' : ' checked').' style="vertical-align: middle" onclick="mapsection(\''.$id.'\')"/></td>
<td><label for="sec_'.$id.'">'.(isset($arrSections[$id]['title']) ? $arrSections[$id]['title'] : '[section '.$id.']').'</label></td>
<td>
<select class="small" id="mark_'.$id.'" name="mark_'.$id.'" size="1" style="'.($arrConfig[$id]['enabled']==0 ? 'visibility:hidden' : '').'">
<option value="0">'.L('Gmap.Default').'</option>
<option value="-" disabled="disabled">&nbsp;</option>
'.asTags($arrFiles,(isset($arrConfig[$id]['icon']) ? $arrConfig[$id]['icon'] : null)).'
</select>
</td>
<td>
<select class="small" id="list_'.$id.'" name="list_'.$id.'" size="1" style="'.($arrConfig[$id]['enabled']==0 ? 'visibility:hidden' : '').'">
'.asTags(L('Gmap.List.*'),$arrConfig[$id]['list']).'
</select>
</td>
</tr>
';
}

$id='S';
if ( !isset($arrConfig[$id]['enabled']) ) $arrConfig[$id]['enabled']=0;
if ( !isset($arrConfig[$id]['list']) ) $arrConfig[$id]['list']=0;
echo '<tr class="hover">
<td style="background-color:#c3d9ff;border-top:solid 1px #c3d9ff;width:25px;text-align:center"><input type="checkbox" id="sec_'.$id.'" name="sec_'.$id.'"'.($arrConfig[$id]['enabled']==0 ? '' : ' checked').' style="vertical-align: middle" onclick="mapsection(\''.$id.'\')"/></td>
<td style="border-top:solid 1px #c3d9ff"><label for="sec_'.$id.'">'.L('Search_result').'</label></td>
<td style="border-top:solid 1px #c3d9ff">
<select class="small" id="mark_'.$id.'" name="mark_'.$id.'" size="1" style="'.($arrConfig[$id]['enabled']==0 ? 'visibility:hidden' : '').'">
<option value="S">'.L('Gmap.From_section').'</option>
<option value="0">'.L('Gmap.Default').'</option>
<option value="-" disabled="disabled">&nbsp;</option>
'.asTags($arrFiles,(isset($arrConfig[$id]['icon']) ? $arrConfig[$id]['icon'] : null)).'
</select>
</td>
<td style="border-top:solid 1px #c3d9ff">
<select class="small" id="list_'.$id.'" name="list_'.$id.'" size="1" style="'.($arrConfig[$id]['enabled']==0 ? 'visibility:hidden' : '').'">
'.asTags(L('Gmap.List.*'),$arrConfig[$id]['list']).'
</select>
</td>
</tr>
';

$id='U';
if ( !isset($arrConfig[$id]['enabled']) ) $arrConfig[$id]['enabled']=0;
if ( !isset($arrConfig[$id]['list']) ) $arrConfig[$id]['list']=0;
echo '<tr class="hover">
<td style="background-color:#c3d9ff;width:25px;text-align:center"><input type="checkbox" id="sec_'.$id.'" name="sec_'.$id.'"'.($arrConfig[$id]['enabled']==0 ? '' : ' checked').' style="vertical-align: middle" onclick="mapsection(\''.$id.'\')"/></td>
<td><label for="sec_'.$id.'">'.L('Users').'</label></td>
<td>
<select class="small" id="mark_'.$id.'" name="mark_'.$id.'" size="1" style="'.($arrConfig[$id]['enabled']==0 ? 'visibility:hidden' : '').'">
<option value="0">'.L('Gmap.Default').'</option>
<option value="-" disabled="disabled">&nbsp;</option>
'.asTags($arrFiles,(isset($arrConfig[$id]['icon']) ? $arrConfig[$id]['icon'] : null)).'
</select>
</td>
<td>
<select class="small" id="list_'.$id.'" name="list_'.$id.'" size="1" style="'.($arrConfig[$id]['enabled']==0 ? 'visibility:hidden' : '').'">
'.asTags(L('Gmap.List.*'), $arrConfig[$id]['list']).'
</select>
</td>
</tr>
</table>
';

echo '<p class="submit"><button type="submit" name="ok" value="save">'.L('Save').'</button></p>
</div>
</form>
';

// show table symbols

echo '<br>
<h2 class="config">'.L('Gmap.symbols').'</h2>
<table class="t-conf">
<tr>
<td class="center"><img alt="i" class="marker" src="bin/css/gmap_marker.png"/><br><span class="small">Default</span></td>
';
$i=0;
foreach ($arrFiles as $strFile=>$strName)
{
echo '<td class="center"><img alt="i" class="marker" src="qtim_gmap/'.$strFile.'.png"/><br><span class="small">'.$strName.'</span></td>
';
++$i;
if ( $i>=9 ) { echo '</tr><tr>'; $i=0; }
}
echo '</tr>
</table>
<p class="submit">'.getSVG('chevron-left').'<a href="'.$oH->exiturl.'" onclick="return qtFormSafe.exit(e0);">'.$oH->exitname.'</a></p>
';

// HTML END

include APP.'_adm_inc_ft.php';