<?php

/**
* PHP version 7
*
* LICENSE: This source file is subject to version 3.0 of the PHP license
* that is available through the world-wide-web at the following URI:
* http://www.php.net/license. If you did not receive a copy of
* the PHP License and are unable to obtain it through the web, please
* send a note to license@php.net so we can mail you a copy immediately.
*
* @package    QuickTicket
* @author     Philippe Vandenberghe <info@qt-cute.org>
* @copyright  2012 The PHP Group
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
include translate(APP.'m_rss.php');
if ( SUser::role()!=='A' ) die('Access denied');

// INITIALISE

$oH->name = $L['rss']['Admin'];
$parentname = L('Module');
$moduleversion = L('Version').' 4.0';
$error = '';

// ------
// SUBMITTED
// ------
if ( isset($_POST['ok']) )
{
  // check others
  if ( empty($error) )
  {
    $_SESSION[QT]['m_rss'] = $_POST['rss'];
    $_SESSION[QT]['m_rss_conf'] = $_POST['rssuser'].' '.$_POST['rssformat'].' '.$_POST['rsssize'];
  }

  // save value
  if ( empty($error) )
  {
    $oDB->exec( 'UPDATE TABSETTING SET setting="'.$_SESSION[QT]['m_rss'].'" WHERE param="m_rss"');
    $oDB->exec( 'UPDATE TABSETTING SET setting="'.$_SESSION[QT]['m_rss_conf'].'" WHERE param="m_rss_conf"');
    // exit
    SMem::set('settingsage',time());
    $_SESSION[QT.'splash'] = (empty($error) ? L('S_save') : 'E|'.$error);
  }
}

// ------
// HTML BEGIN
// ------
include APP.'_adm_inc_hd.php';

// read values
if ( !isset($_SESSION[QT]['m_rss_conf']) )
{
  $arr = $oDB->getSettings('param="m_rss_conf"',true);
  if ( empty($arr) ) die('<span class="error">Parameters not found. The module is probably not installed properly.</span><br><br><a href="qti_adm_index.php">&laquo;&nbsp;'.$L['Exit'].'</a>');
}
if ( !isset($_SESSION[QT]['m_rss']) )
{
  $arr = $oDB->getSettings('param="m_rss"',true);
  if ( empty($arr) ) die('<span class="error">Parameters not found. The module is probably not installed properly.</span><br><br><a href="qti_adm_index.php">&laquo;&nbsp;'.$L['Exit'].'</a>');
}

$arrConf = explode(' ',$_SESSION[QT]['m_rss_conf']);
$strUser = $arrConf[0];
$strForm = $arrConf[1];if ( $strForm==='2.0' || $strForm==='2' ) $strForm='rss2';
$strSize = $arrConf[2];

// FORM

echo '<form class="formsafe" method="post" action="'.$oH->php.'">
<h2 class="config">'.L('Status').'</h2>
<table class="t-conf">
<tr>
<th>'.L('Status').'</th>
<td class="flex-sp">
<p><span style="display:inline-block;width:16px;background-color:'.( $_SESSION[QT]['m_rss'] ? 'green' : 'red').';border-radius:3px">&nbsp;</span>&nbsp;'.L(($_SESSION[QT]['m_rss'] ? 'On' : 'Off').'_line').'</p>
<p>'.L('Change').'&nbsp;<select id="rss" name="rss">
<option value="1"'.($_SESSION[QT]['m_rss']==='1' ? ' selected' : '').'>'.L('On_line').'</option>
<option value="0"'.($_SESSION[QT]['m_rss']==='0' ? ' selected' : '').'>'.L('Off_line').'</option>
</select>
</p>
</td>
</tr>
</table>
';

echo '<h2 class="config">'.L('Settings').'</h2>
<table class="t-conf">
<tr>
<th style="width:200px"><label for="rssuser">'.$L['rss']['User'].'</label></th>
<td><select id="rssuser" name="rssuser">
<option value="V"'.($strUser=='V' ? ' selected' : '').'>'.$L['rss']['All_users'].'</option>
<option value="U"'.($strUser=='U' ? ' selected' : '').'>'.$L['rss']['Members_only'].'</option>
</select></td>
<td><small>'.$L['rss']['H_User'].'</span></td>
</tr>
<tr>
<th><label for="rssformat">'.$L['rss']['Format'].'</label></th>
<td><select id="rssformat" name="rssformat">
<option value="rss2"'.($strForm=='rss2' ? ' selected' : '').'>RSS 2.0</option>
<option value="atom"'.($strForm=='atom' ? ' selected' : '').'>Atom</option>
</select></td>
<td ><small>'.$L['rss']['H_Format'].'</span></td>
</tr>
<tr>
<th><label for="rsssize">'.$L['rss']['Size'].'</label></th>
<td ><select id="rsssize" name="rsssize">
<option value="1"'.($strSize=='1' ? ' selected' : '').'>1</option>
<option value="2"'.($strSize=='2' ? ' selected' : '').'>2</option>
<option value="3"'.($strSize=='3' ? ' selected' : '').'>3</option>
<option value="4"'.($strSize=='4' ? ' selected' : '').'>4</option>
<option value="5"'.($strSize=='5' ? ' selected' : '').'>5</option>
</select></td>
<td ><small>'.$L['rss']['H_Size'].'</span></td>
</tr>
</table>
';

echo '<p class="submit"><button type="submit" name="ok" value="ok">'.L('Save').'</button></p>
</form>
';

$strRssUrl = $_SESSION[QT]['site_url'].'/rss';
$arrRss = [];
foreach(array_keys($_SectionsTitle) as $s) {
  if ( file_exists('rss/qti_'.$strForm.'_'.$s.'.xml') ) $arrRss[$s]=$strRssUrl.'/qti_'.$strForm.'_'.$s.'.xml';
}

if ( count($arrRss)>0 ) {
  echo '<h2 class="config">'.L('Preview').'</h2>'.PHP_EOL;
  echo '<div class="scroll">'.PHP_EOL;
  echo '<table class="data_t">'.PHP_EOL;
  foreach($arrRss as $s=>$strRss) {
    echo '<tr class="data_t hover"><td >'.$_SectionsTitle[$s].'</td><td ><a class="small" href="'.$strRss.'" target="_blank">'.$strRss.'</a></td></tr>';
  }
  echo '</table></div>';
  echo '<p class="minor">'.qtSvg('info').' The feeds remain accessible when the module is off-line.</p>';
}

// HTML END

include APP.'_adm_inc_ft.php';