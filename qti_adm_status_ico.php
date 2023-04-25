<?php

// v2.4 build:20230205

session_start();
/**
 * @var CHtml $oH
 * @var array $L
 * @var CDatabase $oDB
 */
require 'bin/init.php';
include translate('lg_adm.php');

if ( SUser::role()!=='A' ) die(L('E_13'));

// INITIALISE

$oH->selfurl = 'qti_adm_status_ico.php';
$oH->exiturl = 'qti_adm_statuses.php';
$oH->selfname = 'Icons';
$oH->exitname = $L['Status+'];

$arrFiles=array();
$arrStatuses=array();

// --------
// HTML START
// --------

include APP.'_adm_inc_hd.php';
include 'qti_adm_p_title.php';

// Browse image file

$intHandle = opendir(QT_SKIN);

$i=0;
while (false !== ($file = readdir($intHandle)))
{
  $file=strtolower($file);
  if ( $file!='.' && $file!='..' ) {
    if ( substr($file,0,6)==='status' )
    {
    $arrStatuses[] = $file;
    }
    else
    {
    if ( substr($file,0,3)!=='bg_' && substr($file,0,10)!=='background' ) $arrFiles[] = $file;
    }
    ++$i;
  }
}
closedir($intHandle);
sort($arrStatuses);
sort($arrFiles);

echo QT_SKIN,', ',$i,' files<br><br>';

echo '
<table>
<tr>
<td style="width:250px;vertical-align:top">
';

echo '<table style="background-color:#ffffff">
<groupcol><col></col><col style="width:120px"></col></groupcol>
<tr><td style="padding-left:4px"><b>Icon</b></td><td><b>File</b></td></tr>'.PHP_EOL;
foreach($arrStatuses as $val)
{
  if ( strtolower(substr($val,-4,4))==='.gif')
  {
  echo '<tr><td style="padding-left:4px"><img src="'.QT_SKIN.$val.'"/></td><td class="td_icon">'.$val.'</td></tr>'.PHP_EOL;
  }
}
echo '</table>
';
echo '
</td>
<td style="width:20px;">
<td style="width:250px;vertical-align:top">
';
echo '<table style="background-color:#ffffff">
<groupcol><col></col><col style="width:120px"></col></groupcol>
<tr><td style="padding-left:4px"><b>Icon</b></td><td><b>File</b></td></tr>'.PHP_EOL;
foreach($arrFiles as $val)
{
  if ( strtolower(substr($val,-4,4))==='.gif')
  {
  echo '<tr><td style="padding-left:4px"><img src="'.QT_SKIN.$val.'"/></td><td class="td_icon">'.$val.'</td></tr>'.PHP_EOL;
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