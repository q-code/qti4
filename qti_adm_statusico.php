<?php // v4.0 build:20230205

session_start();
/**
 * @var CHtml $oH
 * @var CDatabase $oDB
 */
require 'bin/init.php'; if ( SUser::role()!=='A' ) die(L('E_13'));
 include translate('lg_adm.php');

// INITIALISE

$oH->selfurl = 'qti_adm_statusico.php';
$oH->exiturl = 'qti_adm_statuses.php';
$oH->selfname = 'Icons';
$oH->exitname = L('Statuses');

$arrFiles=array();

// --------
// HTML BEGIN
// --------

const HIDE_MENU_TOC=true;
const HIDE_MENU_LANG=true;

$oH->links['cssIcons']=''; // remove webicons
include APP.'_adm_inc_hd.php';

// Browse image file

$strHandle = QT_SKIN.'img';
$intHandle = opendir($strHandle);

$i=0;
while (false !== ($file = readdir($intHandle)))
{
  $file=strtolower($file);
  if ( $file!='.' && $file!='..' )
  {
    if ( substr($file,0,3)!='bg_' && substr($file,0,10)!='background' ) $arrFiles[] = $file;
    ++$i;
  }
}
closedir($intHandle);
sort($arrFiles);

echo $strHandle,', ',$i,' files<br><br>';

echo '
<table>
<tr>
<td style="width:250px">
';

echo '<table style="background-color:#ffffff">
<groupcol><col></col><col style="width:120px"></col></groupcol>
<tr><td style="padding-left:4px"><b>Icon</b></td><td><b>File</b></td></tr>'.PHP_EOL;
foreach($arrFiles as $val)
{
  if ( strtolower(substr($val,0,10))=='ico_status' && strtolower(substr($val,-4,4))=='.gif'  )
  {
  echo '<tr><td style="padding-left:4px"><img src="',$strHandle,'/',$val,'"/></td><td class="td_icon">',$val,'</td></tr>'.PHP_EOL;
  }
}
echo '</table>
';
echo '
</td>
<td style="width:20px;"></td>
<td style="width:250px">
';
echo '<table style="background-color:#ffffff">
<groupcol><col></col><col style="width:120px"></col></groupcol>
<tr><td style="padding-left:4px"><b>Icon</b></td><td><b>File</b></td></tr>'.PHP_EOL;
foreach($arrFiles as $val)
{
  if ( strtolower(substr($val,0,10))!='ico_status' && strtolower(substr($val,-4,4))=='.gif' )
  {
  echo '<tr><td style="padding-left:4px"><img src="',$strHandle,'/',$val,'"/></td><td class="td_icon">',$val,'</td></tr>'.PHP_EOL;
  }
}
echo '</table>
';
echo '
</td>
<td>&nbsp;</td>
</tr>
</table>
';

// HTML END

include APP.'_adm_inc_ft.php';