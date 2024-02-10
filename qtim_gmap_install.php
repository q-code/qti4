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
* @copyright  2015 The PHP Group
* @version    4.0 build:20240210
*/

session_start();
/**
 * @var CHtml $oH
 * @var CDatabase $oDB
 */
require 'bin/init.php';
include translate('lg_adm.php');
if ( SUser::role()!=='A' ) die('Access denied');

// INITIALISE

define('MODULE', 'gmap');
$oH->selfurl = 'qtim_'.MODULE.'_install.php';
$oH->selfname = 'Install module: '.MODULE;

$bStep1 = true;
$bStepZ = true;

// STEP 1

$strFile = 'qtim_gmap_uninstall.php';
if ( !file_exists($strFile) ) $error='Missing file: '.$strFile.'<br />This module cannot be used.';
$strFile = 'qtim_gmap_adm.php';
if ( !file_exists($strFile) ) $error='Missing file: '.$strFile.'<br />This module cannot be used.';
$strFile = 'qtim_gmap/config_gmap.php';
if ( !file_exists($strFile) ) $error='Missing file: '.$strFile.'<br />This module cannot be used.';
if ( !empty($error) ) $bStep1 = false;

// STEP Z
if ( empty($error) )
{
  $oDB->exec( 'DELETE FROM TABSETTING WHERE param="module_gmap" OR param="m_gmap_gkey" OR param="m_gmap_gcenter" OR param="m_gmap_gzoom" OR param="m_gmap_gbuttons" OR param="m_gmap_gsymbol"');
  $oDB->exec( 'INSERT INTO TABSETTING (param,setting) VALUES ("module_gmap","Gmap")');
  $oDB->exec( 'INSERT INTO TABSETTING (param,setting) VALUES ("m_gmap_gkey","")');
  $oDB->exec( 'INSERT INTO TABSETTING (param,setting) VALUES ("m_gmap_gcenter","50.8468142558,4.35238838196")');
  $oDB->exec( 'INSERT INTO TABSETTING (param,setting) VALUES ("m_gmap_gzoom","10")');
  $oDB->exec( 'INSERT INTO TABSETTING (param,setting) VALUES ("m_gmap_gbuttons","P10100")');
  $oDB->exec( 'INSERT INTO TABSETTING (param,setting) VALUES ("m_gmap_gfind","Brussels, Belgium")');
  $oDB->exec( 'INSERT INTO TABSETTING (param,setting) VALUES ("m_gmap_gsymbol","0")');
  $_SESSION[QT]['module_gmap'] = 'Gmap';
  $_SESSION[QT]['m_gmap_gkey'] = '';
  $_SESSION[QT]['m_gmap_gcenter'] = '50.8468142558,4.35238838196';
  $_SESSION[QT]['m_gmap_gzoom'] = '10';
  $_SESSION[QT]['m_gmap_gbuttons'] = 'P10100';
  $_SESSION[QT]['m_gmap_gfind'] = 'Brussels, Belgium';
  $_SESSION[QT]['m_gmap_gsymbol'] = '0'; // Default symbol
  SMem::set('settingsage',time());
}

// ------
// Html start
// ------
include APP.'_adm_inc_hd.php';

$strInfo = '';
if ( file_exists('qtim_'.MODULE.'_info.txt') )
{
  $lines = file('qtim_'.MODULE.'_info.txt');
  foreach($lines as $line) $strInfo .= '<br>&nbsp;&middot;&nbsp;'.htmlspecialchars($line,ENT_QUOTES);
}
echo '<p>'.L('Info').':'.(empty($strInfo) ? '<br>&nbsp;&middot;&nbsp;'.L('none') : $strInfo).'</p><br>
';

echo '<h2>Checking components</h2>';
if ( !$bStep1 )
{
  echo '<p class="error">',$error,'</p>';
  include APP.'_adm_inc_ft.php';
  exit;
}
echo '<p>Ok</p>';
echo '<h2>Database settings</h2>';
if ( !$bStepZ )
{
  echo '<p class="error">',$error,'</p>';
  include APP.'_adm_inc_ft.php';
  exit;
}
echo '<p>Ok</p>';
echo '<h2>Installation completed</h2>';

if ( $_SESSION[QT]['version']=='1.8' || $_SESSION[QT]['version']=='1.9' )
{
  echo '<p class="error">Your database version is <2.0. We recommend you to upgrade to 3.0 (use the installation wizard of '.APPNAME.').</p>';
}