<?php  // v4.0 build:20230618

session_start();
/**
 * @var CHtml $oH
 * @var array $L
 * @var CDatabase $oDB
 */
require 'bin/init.php';
if ( SUser::role()!=='A' ) die(L('E_13'));
include translate('lg_adm.php');

// INITIALISE

$oH->selfurl = 'qti_adm_statuses.php';
$oH->exiturl = 'qti_adm_statuses.php';
$oH->selfname = L('Statuses');
$oH->selfparent = L('Board_content');
$oH->exitname = L('Statuses');

// --------
// SUBMITTED for add
// --------

if ( isset($_POST['ok']) ) try {

  // Check id, name and duplicate id
  $id = strtoupper(substr($_POST['id'],0,1));
  if ( !preg_match('/[B-Y]/',$id) ) throw new Exception( 'Id '.$id.' '.L('invalid').' (B-Y)' );
  $name = trim($_POST['name']); if ( empty($name) ) { $name = 'Unknown'; throw new Exception( L('Status').' '.L('invalid') ); }
  if ( array_key_exists($id,$_Statuses) ) throw new Exception( 'Id '.$id.' '.L('already_used') );
  // Add
  SStatus::add($id,$name,'ico_status0.gif');
  //Exit
  $_SESSION[QT.'splash'] = L('S_insert');
  $oH->redirect('qti_adm_status.php?id='.$id);

} catch (Exception $e) {

  $error = $e->getMessage();
  $_SESSION[QT.'splash'] = 'E|'.$error;

}

// --------
// SUBMITTED for show option
// --------

if ( isset($_POST['ok_show']) ) {
  $_SESSION[QT]['show_closed'] = $_POST['show_closed'];
  $oDB->updSetting('show_closed',$_SESSION[QT]['show_closed']);
}

// --------
// HTML BEGIN
// --------

include APP.'_adm_inc_hd.php';

echo '<form method="post" action="'.$oH->self().'">
<table class="t-item">
<tr>
<th style="width:30px;text-align:center">Id</th>
<th style="width:30px">&nbsp;</th>
<th>'.L('Status').'</th>
<th>'.L('Email').'</th>
<th>'.L('Action').'</th>
<th style="width:50px">#</th>
</tr>
';

foreach($_Statuses as $id=>$arrStatus)
{
  echo '<tr class="t-item hover">'.PHP_EOL;
  echo '<td class="center" style="width:50px">'.$id.'</td>'.PHP_EOL;
  echo '<td class="center">'.SStatus::getIcon($id,'class=i-status').'</td>'.PHP_EOL;
  echo '<td><a href="qti_adm_status.php?id='.$id.'">'.SStatus::translate($id).'</a></td>'.PHP_EOL;
  echo '<td>'.($arrStatus['mailto']!='' ? L('Y') : '<span class="disabled">'.L('None').'</span>').'</td>'.PHP_EOL;
  echo '<td><a href="qti_adm_status.php?id='.$id.'">'.L('Edit').'</a>&nbsp;&middot;&nbsp;';
  if ( ($id=='A') || ($id=='Z') ) { echo '<span class="disabled">'.L('Delete'); } else { echo '<a href="qti_dlg_adm.php?a=status_del&s='.$id.'">'.L('Delete').'</a>'; }
  echo '</td>'.PHP_EOL;
  echo '<td'.( empty($arrStatus['color']) ? '' : ' style="background-color:'.$arrStatus['color'].'"').'>&nbsp;</td>'.PHP_EOL;
  echo '</tr>'.PHP_EOL;
}
echo '
<tr class="tr group hover">
<td><input required type="text" name="id" size="1" maxlength="1" pattern="[A-Za-z]{1}"/></td>
<td>&nbsp;</td>
<td><input required type="text" name="name" size="10" maxlength="24"/></td>
<td>&nbsp;</td>
<td><button type="submit" name="ok" value="add">'.L('Add').'</button></td>
<td>&nbsp;</td>
</tr>
</table>
</form>
<br>
';

echo '<h2 class="config">'.L('Display_options').'</h2>
<form method="post" action="'.$oH->self().'">
<table class="t-conf">
<tr>
<th style="width:150px"><label for="show_closed">'.L('Show_z').'</label></th>
<td><select id="show_closed" name="show_closed">
<option value="0"'.($_SESSION[QT]['show_closed']=='0' ? ' selected' : '').'>'.L('N').'</option>
<option value="1"'.($_SESSION[QT]['show_closed']=='1' ? ' selected' : '').'>'.L('Y').'</option>
</select> <span class="small">'.sprintf(L('H_Show_z'),SStatus::translate($id)).'</span></td>
<td><button type="submit" name="ok" value="ok">'.L('Save').'</button></td>
</tr>
</table>
</form>
';

// --------
// HTML END
// --------

include APP.'_adm_inc_ft.php';