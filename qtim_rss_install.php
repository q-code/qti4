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
 */
require 'bin/init.php';
include translate('lg_adm.php');
if ( SUser::role()!=='A' ) die('Access denied');

// INITIALISE

$strVersion='v4.0';
$oH->name = 'Installation module RSS '.$strVersion;

$bStep0 = true;
$bStep1 = true;
$bStep2 = true;
if ( isset($_SESSION[QT]['m_rss']) ) unset($_SESSION[QT]['m_rss']);
if ( isset($_SESSION[QT]['m_rss_conf']) ) unset($_SESSION[QT]['m_rss_conf']);

// STEP 0: check version

$strQTI = VERSION; if ( substr(VERSION,0,1)==='v') $strQTI = substr(VERSION,1);
$arrQTI = explode('.',$strQTI);
if ( intval($arrQTI[0])<2 ) $error="Your QuickTicket version is $strQTI. Please, upgrade to QuickTicket 3.0 before installing this module...";
if ( !empty($error) ) $bStep0 = false;

// STEP 1

if ( empty($error) )
{
  $strFile = 'qtim_rss.php';
  if ( !file_exists($strFile) ) $error="Missing file: $strFile<br>This module cannot be used.";
  $strFile = 'qtim_rss_adm.php';
  if ( !file_exists($strFile) ) $error="Missing file: $strFile<br>This module cannot be used.";
  $strFile = 'qtim_rss_inc.php';
  if ( !file_exists($strFile) ) $error="Missing file: $strFile<br>This module cannot be used.";
  if ( !empty($error) ) $bStep1 = false;
}

// STEP 2

if ( empty($error) )
{
  $strFile = 'rss';
  if ( !is_dir($strFile) )
  {
  $error='Missing directory '.$strFile.': First create this directory and make it writable.<br>This module cannot be used because a writable directory is mandatory.';
  }
  else
  {
  if ( !is_writable($strFile) ) $error='Directory '.$strFile.' is not writable.<br>This module cannot be used because a writable directory is mandatory.';
  }
  if ( !empty($error) ) $bStep2 = false;
}

// INSTALL

if ( empty($error) )
{
  $oDB->exec( 'DELETE FROM TABSETTING WHERE param="module_rss" OR param="m_rss" OR param="m_rss_conf"');
  $oDB->exec( 'INSERT INTO TABSETTING (param,setting) VALUES ("module_rss","RSS")');
  $oDB->exec( 'INSERT INTO TABSETTING (param,setting) VALUES ("m_rss","0")');
  $oDB->exec( 'INSERT INTO TABSETTING (param,setting) VALUES ("m_rss_conf","V rss2 3")');
  $_SESSION[QT]['module_rss'] = 'RSS';
  $_SESSION[QT]['m_rss'] = '0';
  $_SESSION[QT]['m_rss_conf'] = 'V rss2 3';
  SMem::set('settingsage',time());
}

// ------
// Html start
// ------
include APP.'_adm_inc_hd.php';

if ( !$bStep0 )
{
  echo '<p class="error">',$error,'</p>';
  include APP.'_adm_inc_ft.php';
  exit;
}
echo '<h2>Checking components</h2>';
if ( !$bStep1 )
{
  echo '<p class="error">',$error,'</p>';
  include APP.'_adm_inc_ft.php';
  exit;
}
echo '<p>Ok</p>';
echo '<h2>Checking rss subdirectory</h2>';
if ( !$bStep2 )
{
  echo '<p class="error">',$error,'</p>';
  include APP.'_adm_inc_ft.php';
  exit;
}
echo '<p>Ok</p>';
echo '<h2>Database settings</h2>';
echo '<p>Ok</p>';
echo '<h2>Installation completed</h2>';

include APP.'_adm_inc_ft.php';