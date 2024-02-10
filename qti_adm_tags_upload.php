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
* @license    http://www.php.net/license PHP License 3.0
* @version    4.0 build:20240210
*/

session_start();
/**
 * @var CHtml $oH
 * @var array $L
 * @var CDatabase $oDB
 */
require 'bin/init.php';
include translate('lg_adm.php');

if ( SUser::role()!=='A' ) die('Access denied');

// INITIALISE

$pan='en';
$v = '';
$intSize = 100;
qtArgs('pan v'); if ( empty($v) ) $error = 'Missing file name';

$oH->selfurl = 'qti_adm_tags_upload.php';
$oH->selfname = L('Add').' CSV '.L('file');
$oH->exiturl = 'qti_adm_tags.php';
$oH->exitname = L('Tags');

// ------
// SUBMITTED FOR UPLOAD
// ------
if ( isset($_POST['ok']) )
{
  // Check uploaded document

  $error = validateFile($_FILES['title'],'csv,txt,text','',500);

  // Save

  if ( empty($error) )
  {
    copy($_FILES['title']['tmp_name'],'upload/'.$v);
    unlink($_FILES['title']['tmp_name']);
    $oH->pageMessage('', L('S_update'));
  }
}

// ------
// HTML BEGIN
// ------
include APP.'_adm_inc_hd.php';

echo '<br>'.PHP_EOL;
echo '<table>'.PHP_EOL;
echo '<tr>'.PHP_EOL;
echo '<td>'.PHP_EOL;

CHtml::msgBox($oH->selfname);

echo '<form method="post" action="',$oH->selfurl,'" enctype="multipart/form-data" onsubmit="return ValidateForm(this);">'.PHP_EOL;
echo '<p style="text-align:right">'.PHP_EOL;
echo $L['File'],': <input type="hidden" name="max_file_size" value="',($intSize*1024),'"/>'.PHP_EOL;
echo '<input required type="file" id="title" name="title" size="32"/><br><br><br><br>'.PHP_EOL;
echo $L['Destination'],':  upload/<input type="text" id="v" name="v" size="25" maxlength="25" value="',$v,'" onkeyup="validateWarning(this);"/><br><br>'.PHP_EOL;
echo '<span id="warning" class="warning">',(file_exists('upload/'.$v) ? $L['Overwrite_file'].' ['.$v.']' : ''),'</span> ';
echo '<input type="hidden" name="pan" value="',$pan,'"/>'.PHP_EOL;
echo '<a class="button" href="'.$oH->exiturl.'?pan='.$pan.'">'.L('Cancel').'</a> <button type="submit" name="ok" value="ok">'.L('Ok').'</button></p>'.PHP_EOL;
echo '</form>'.PHP_EOL;

CHtml::msgBox('/');

echo '
</td>
</tr>
</table>
';

// HTML END

$oH->scripts[] = 'function validateWarning(str){
  document.getElementById("warning").style.visibility = str.value=="'.$v.'" ? "visible" : "hidden";
}';

include APP.'_adm_inc_ft.php';