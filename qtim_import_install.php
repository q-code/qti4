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

define('MODULE', 'import');
$oH->selfurl = 'qtim_'.MODULE.'_install.php';
$oH->selfname = 'Install module: '.MODULE;

$bStep1 = true;

// STEP 1

$strFile = 'qtim_'.MODULE.'_uninstall.php';
if ( !file_exists($strFile) ) $error='Missing file: '.$strFile.'<br>This module cannot be used.';
$strFile = 'qtim_'.MODULE.'_adm.php';
if ( !file_exists($strFile) ) $error='Missing file: '.$strFile.'<br>This module cannot be used.';
if ( !empty($error) ) $bStep1 = false;

// STEP 2

// STEP Z

if ( empty($error) )
{
  $oDB->exec( 'DELETE FROM TABSETTING WHERE param="module_'.MODULE.'" OR param="module_import_qti"');
  $oDB->exec( 'INSERT INTO TABSETTING (param,setting) VALUES ("module_'.MODULE.'","Import")');
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
echo '
<p>Ok</p>
<h2>Database settings</h2>
<p>Ok</p>
<h2>Installation completed</h2>
';

include APP.'_adm_inc_ft.php';