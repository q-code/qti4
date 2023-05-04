<?php // v4.0 build:20230430

session_start();
/**
 * @var CHtml $oH
 * @var array $L
 * @var string $warning
 * @var CDatabase $oDB
 */
require 'bin/init.php';
include translate('lg_adm.php');

if ( SUser::role()!=='A' ) die(L('E_13'));

// INITIALISE
$id = ''; qtArgs('id!'); if ( empty($id) ) die('Missing status id...');
$oH->selfurl = 'qti_adm_status.php';
$oH->selfuri = 'qti_adm_status.php?id='.$id;
$oH->selfname = L('Statuses');
$oH->selfparent = L('Board_content');
$oH->exiturl = 'qti_adm_statuses.php';
$oH->exitname = getSVG('angle-left').' '.L('Statuses');

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) ) try {

  // check id
  if ( !preg_match('/[A-Z]/',$id)) throw new Exception( $id.': id '.L('invalid').' (B-Y)' ); //A and Z can be edited (hidden input)
  // change id
  if ( $_POST['oldid']!=$id ) SStatus::chgId($_POST['oldid'],$id);
  // check name
  $name = qtDb(trim($_POST['name']));
  if ( empty($name) ) { $name='Unknown'; throw new Exception( 'Status name '.' '.L('invalid') ); }
  // check unic name
  if ( $_POST['oldname']!=$name && $oDB->count( TABSTATUS." WHERE name=?", [qtAttr($name,24)] )>0 ) throw new Exception( '['.$name.'] '.L('already_used') );
  // check color
  $color = strip_tags(trim($_POST['color']));
  if ( $color==='#' ) $color='';
  // check icon
  $icon = strip_tags(trim($_POST['icon']));
  $icon = htmlspecialchars($icon,ENT_QUOTES);
  if ( $icon!=trim($_POST['icon']) ) throw new Exception( L('Icon').' '.L('invalid') );
  // check notified
  $lst_mail = array();
  if ( isset($_POST['mailto'])) $lst_mail = $_POST['mailto'];
  $lst_others = preg_split("/[\s,]+/", $_POST['others']);
  $lst_saved = array();
  $i=array_search('U',$lst_mail);
  if ( ($i===false) || (is_null($i)) ) { $bolUser=false; } else { $bolUser=true; $lst_saved[] = 'U'; }
  $i=array_search('MA',$lst_mail);
  if ( ($i===false) || (is_null($i)) ) { $bolOper=false; } else { $bolOper=true; $lst_saved[] = 'MA'; }
  $i=array_search('MF',$lst_mail);
  if ( ($i===false) || (is_null($i)) ) { $bolMode=false; } else { $bolMode=true; $lst_saved[] = 'MF'; }
  $i=array_search('1',$lst_mail);
  if ( ($i===false) || (is_null($i)) ) { $bolAdmi=false; } else { $bolAdmi=true; $lst_saved[] = '1'; }
  $lst_saved = array_merge($lst_saved,$lst_others);
  $lst_saved = array_unique($lst_saved);
  $saved = implode(",",$lst_saved);
  // save
  $oDB->exec( "UPDATE ".TABSTATUS." SET name=?,color=?,mailto=?,icon=? WHERE id='$id'", [$name, $color, $saved, $icon] );
  SMem::clear('_Statuses');
  // save translation (cache unchanged)
  SLang::delete('status,statusdesc',$id);
  foreach($_POST as $key=>$str) {
    if ( substr($key,0,1)==='T' && !empty($str) ) SLang::add('status',substr($key,1),$id,$_POST[$key]);
    if ( substr($key,0,1)==='D' && !empty($str) ) SLang::add('statusdesc',substr($key,1),$id,$_POST[$key]);
  }
  memFlushLang(); // clear cache
  $_SESSION[QT.'splash'] = L('S_save');
  $oH->redirect($oH->exiturl);

} catch (Exception $e) {

  $id = $_POST['oldid'];
  $error = $e->getMessage();
  $_SESSION[QT.'splash'] = 'E|'.$error;

}

// --------
// HTML BEGIN
// --------

include APP.'_adm_inc_hd.php';

// ANALYSE NOTIFY

$lst_mail = explode(',',$_Statuses[$id]['mailto']);
$lst_mail = array_unique($lst_mail);
$others = '';

$i = array_search('U',$lst_mail);
if ( ($i===false) || (is_null($i)) ) { $bolUser=false; } else { unset($lst_mail[$i]); $bolUser=true; }
$i = array_search('MA',$lst_mail);
if ( ($i===false) || (is_null($i)) ) { $bolOper=false; } else { unset($lst_mail[$i]); $bolOper=true; }
$i = array_search('MF',$lst_mail);
if ( ($i===false) || (is_null($i)) ) { $bolMode=false; } else { unset($lst_mail[$i]); $bolMode=true; }
$i = array_search('1',$lst_mail);
if ( ($i===false) || (is_null($i)) ) { $bolAdmi=false; } else { unset($lst_mail[$i]); $bolAdmi=true; }

$others = implode(',',$lst_mail);

// DISPLAY RESULT

echo '<table>
<tr>
<td style="width:25px">'.$id.'</td>
<td style="width:30px">'.SStatus::getIcon($id,'class=i-status').'</td>
<td style="width:100px;padding:3px 10px 3px 10px;text-align:center;background-color:'.(empty($_Statuses[$id]['color']) ? 'transparent' : $_Statuses[$id]['color']).'; border-style:solid; border-color:#dddddd; border-width:1px">'.$_Statuses[$id]['name'].'</td>
<td>&nbsp;</td>
</tr>
</table>
<br>'.PHP_EOL;
echo '<form method="POST" action="'.$oH->selfuri.'">'.PHP_EOL;
echo '<h2 class="config">'.L('Definition').'</h2>'.PHP_EOL;
echo '<table class="t-conf">'.PHP_EOL;
echo '<tr>';
echo '<th style="width:150px">Id</th>';
echo '<td>';
if ( ($id=='A') || ($id=='Z') ) {
  echo $id.'&nbsp;<input type="hidden" name="id" value="'.$id.'"/>';
} else {
  echo '<input type="text" id="id" name="id" size="1" maxlength="1" value="'.$id.'"/>';
}
echo '</td>';
echo '<tr>';
echo '<th style="width:150px"><label for="name">'.L('Name').'</label></th>';
echo '<td><input type="text" id="name" name="name" size="24" maxlength="24" value="'.$_Statuses[$id]['name'].'" style="background-color:#dbf4ff"/></td>';
echo '</tr>'.PHP_EOL;
echo '<tr>';
echo '<th style="width:150px"><label for="icon">Icon</label></th>';
echo '<td><input type="text" id="icon" name="icon" size="24" maxlength="64" value="'.$_Statuses[$id]['icon'].'"/>&nbsp;'.SStatus::getIcon($id,'class=i-status').' &middot; <a href="qti_adm_statusico.php" target="_blank">show icons</a></td>';
echo '</tr>'.PHP_EOL;
echo '<tr>';
echo '<th style="width:150px"><label for="color">'.L('Status_background').'</label></th>';
echo '<td>
<input type="text" class="colortext" id="color" name="color" size="10" maxlength="24" value="'.(empty($_Statuses[$id]['color']) ? '#' : $_Statuses[$id]['color']).'" onchange="qtFormSafe.not();"/>
<input type="color" id="colorpicker" value="'.(empty($_Statuses[$id]['color']) ? '#ffffff' : $_Statuses[$id]['color']).'" onchange="document.getElementById(`color`).value=this.value;"/>
&nbsp;<span class="small">'.L('H_Status_background').'</span>
</td>';
echo '</tr>'.PHP_EOL.'</table>'.PHP_EOL;

echo '<h2 class="config">'.L('Options').'</h2>'.PHP_EOL;
echo '<table class="t-conf">'.PHP_EOL;
echo '<tr>
<th style="width:150px">'.L('Notification').'</span></th>
<td>
  <table>
  <tr>
  <td width="200">
  <input id="mailtoU" type="checkbox" name="mailto[]" value="U"'.($bolUser ? ' checked' : '').'/> <label for="mailtoU">'.L('Role_U').'</label><br>
  <input id="mailtoMA" type="checkbox" name="mailto[]" value="MA"'.($bolOper ? ' checked' : '').'/> <label for="mailtoMA">'.L('Actor').'</label><br>
  <input id="mailtoMF" type="checkbox" name="mailto[]" value="MF"'.($bolMode ? ' checked' : '').'/> <label for="mailtoMF">'.L('Role_C').'</label><br>
  <input id="mailto1" type="checkbox" name="mailto[]" value="1"'.($bolAdmi ? ' checked' : '').'/> <label for="mailto1">'.L('Role_A').'</label><br></td>
  <td>'.L('Notify_also').':<br>
  <textarea id="others" name="others" cols="40" rows="2">'.$others.'</textarea><br>
  <span class="small">'.L('H_Status_notify').'</span>
  </td>
  </tr>
  </table>
</td>
';
echo '</tr>'.PHP_EOL.'</table>'.PHP_EOL;

echo '<h2 class="config">'.L('Translations').'</h2>'.PHP_EOL;

$arrTrans = SLang::get('status','*',$id);
$arrDescTrans = SLang::get('statusdesc','*',$id);

echo '<table class="t-conf">
<tr>
<td colspan="2" style="background-color:transparent"><small>'.L('E_no_translation').'<strong style="color:#1364B7">'.$_Statuses[$id]['name'].'</strong></small></td>
</tr>
<tr>
<th style="width:150px">',L('Name'),'</th>
<td><div class="languages-scroll">
';
foreach(LANGUAGES as $iso=>$values)
{
  $arr = explode(' ',$values,2); if ( empty($arr[1]) ) $arr[1]=$arr[0];
  echo '<p class="iso" title="'.L('Status').' ('.$arr[1].')">'.$arr[0].'</p><p><input type="text" id="T'.$iso.'" name="T'.$iso.'" size="20" maxlength="64" placeholder="'.ucfirst(str_replace('_',' ',$_Statuses[$id]['name'])).'" value="'.(isset($arrTrans[$iso]) ? $arrTrans[$iso] : '').'" onchange="qtFormSafe.not();"/></p>'.PHP_EOL;
}
echo '</div></td>
</tr>
<tr>
<th style="width:150px">'.L('Description').'</th>
<td><div class="languages-scroll">
';
foreach(LANGUAGES as $iso=>$values)
{
  $arr = explode(' ',$values,2); if ( empty($arr[1]) ) $arr[1]=$arr[0];
  echo '<p class="iso" title="'.L('Description').' ('.$arr[1].')">'.$arr[0].'</p><p><input type="text" id="D'.$iso.'" name="D'.$iso.'" size="50" maxlength="255"  value="'.(isset($arrDescTrans[$iso]) ? $arrDescTrans[$iso] : '').'" onchange="qtFormSafe.not();"/></p>'.PHP_EOL;
}
echo '</div></td>
</tr>
</table>
';
echo '<input type="hidden" name="oldid" value="'.$id.'"/>
<input type="hidden" name="oldname" value="'.$_Statuses[$id]['name'].'"/>
<p class="submit"><button type="submit" name="ok" value="ok">'.L('Save').'</button></p>
</form>
<p class="submit"><a href="'.$oH->exiturl.'" onclick="return qtFormSafe.exit(e0);">'.$oH->exitname.'</a></p>
';

// HTML END

include APP.'_adm_inc_ft.php';