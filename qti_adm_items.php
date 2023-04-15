<?php // v4.0 build:20230205 allows app impersonation [qt f|i ]

session_start();
/**
 * @var CHtml $oH
 * @var CDatabase $oDB
 */
require 'bin/init.php'; if ( SUser::role()!=='A' ) die(L('E_13'));

include translate('lg_adm.php');

// INITIALISE

if ( empty($_SESSION[QT]['unreplied_days']) ) $_SESSION[QT]['unreplied_days'] = '10';
$d = (int)$_SESSION[QT]['unreplied_days']; // days
qtHttp('int:d');

$oH->selfurl = APP.'_adm_items.php';
$oH->selfname = L('Item+');
$oH->selfparent = L('Board_content');
$oH->exitname = '&laquo; '.L('Item+');

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) ) try {

  if ( !QTisbetween($d,1,99) ) throw new Exception( L('Days').' '.L('invalid').' (1-99)' );
  if ( $_SESSION[QT]['unreplied_days']!=$d ) {
    $_SESSION[QT]['unreplied_days']=$d;
    $oDB->exec( "DELETE FROM TABSETTING WHERE param='unreplied_days'" );
    $oDB->exec( "INSERT INTO TABSETTING (param,setting) VALUES ('unreplied_days','$d')" );
    SMem::set('settingsage',time());
  }

} catch (Exception $e) {

  $_SESSION[QT.'splash'] = 'E|'.$e->getMessage();

}

// --------
// HTML BEGIN
// --------

include APP.'_adm_inc_hd.php';

$arrDomains = CDomain::getTitles();
$stats = CSection::getSectionsStats(); // force recompute stats

echo '<p style="margin:0 0 10px 0;text-align:right"><a id="tgl-ctrl" class="tgl-ctrl" href="javascript:void(0)" onclick="qtToggle(); return false;">'.L('Unreplied').getSVG('angle-down','','',true).getSVG('angle-up','','',true).'</a></p>
<div id="tgl-container" style="display:none;padding:6px;text-align:right;border:1px solid #CCC;background-color:#F6F6F6">
<form method="post" action="'.$oH->selfurl.'">'.
sprintf(L('Unreplied_def'),'<input type="number" name="d" min="1" max="99" value="'.$d.'"/>').
'&nbsp;<button type="submit" name="ok" value="ok">'.L('Ok').'</button></form>
</div>';

echo '
<table class="t-sec">
<thead>
<tr>
<th class="c-section" colspan="2">'.L('Domain').'/'.L('Section').'</th>
<th class="c-data ellipsis">'.L('Item+').'</th>
<th class="c-data ellipsis">'.L('Reply+').'</th>
<th class="c-data ellipsis">'.L('Unreplied').'</th>
<th class="c-action ellipsis">'.L('Action').'</th>
</tr>
</thead>
<tbody>
';

foreach($arrDomains as $idDom=>$strDomtitle)
{
  echo '<tr><td class="c-section group" colspan="6">'.$strDomtitle.'</td></tr>'.PHP_EOL;
  $arrSections = qtArrget(getSections('A',$idDom)); // GET SECTIONS (with hidden)
  foreach(array_keys($arrSections) as $s)
  {
    $oS = new CSection($s);
    $intU = $oDB->count( CSection::sqlCountItems($s,'unreplied','','','',$d) );
    echo '<tr class="hover">';
    echo '<td class="c-icon">'.asImg( $oS->logo(), 'title='.L('Ico_section_'.$oS->type.'_'.$oS->status) ).'</td>';
    echo '<td class="c-section"><span class="sectionname">'.$oS->title.'</span><br><span class="small">'.L('Section_type.'.$oS->type).($oS->status==='1' ? ', '.L('Section_status.1') : '').'</span></td>';
    echo '<td class="c-data ellipsis">'.$stats[$s]['items'].'</td>';
    echo '<td class="c-data ellipsis">'.$stats[$s]['replies'].'</td>';
    echo '<td class="c-data ellipsis">'.$intU.'</td>';
    echo '<td class="c-action ellipsis">';
    if ( $stats[$s]['items']>0 ) {
    echo '<a href="'.APP.'_dlg_adm.php?a=Scntmove&s='.$s.'">'.L('Move').'</a><br>';
    echo '<a href="'.APP.'_dlg_adm.php?a=Scntdelete&s='.$s.'&d='.$d.'">'.L('Delete').'</a><br>';
    } else {
    echo '<span class="disabled">'.L('Move').'</span><br>';
    echo '<span class="disabled">'.L('Delete').'</span><br>';
    }
    if ( $intU>0 ) {
    echo '<a href="'.APP.'_dlg_adm.php?a=Sprune&s='.$s.'&d='.$d.'">'.L('Prune').'</a>';
    } else {
    echo '<span class="disabled">'.L('Prune').'</span>';
    }
    echo '</td></tr>'.PHP_EOL;
  }
}
echo '</tbody>
</table>
<p class="minor">'.getSVG('info').' '.L('Unreplied').': '.sprintf(L('unreplied_def'),$d).'</p>
';

// HTML END

include APP.'_adm_inc_ft.php';