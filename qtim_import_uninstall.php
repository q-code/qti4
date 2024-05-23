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
$oH->name = 'Uninstall module: '.MODULE;

// UNINSTALL

$oDB->exec( 'DELETE FROM TABSETTING WHERE param="module_'.MODULE.'" OR param="module_'.MODULE.'_qti"');
unset($_SESSION[QT]['m_'.MODULE]);
SMem::set('settingsage',time());

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

echo '<h2>Removing database settings</h2>
<p>Ok</p>
<h2>Uninstall completed</h2>
';

include APP.'_adm_inc_ft.php';