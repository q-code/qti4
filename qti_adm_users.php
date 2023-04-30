<?php // v4.0 build:20230205

session_start();
/**
* @var CHtml $oH
* @var CDatabase $oDB
* @var string $formAddUser
*/
require 'bin/init.php';

if ( SUser::role()!=='A' ) die('Access denied');
include translate('lg_adm.php');
include translate('lg_reg.php');

// ---------
// INITIALISE
// ---------

$oDB->query( 'SELECT count(*) as countid FROM TABUSER WHERE id>0'); // Count all users
$row = $oDB->getRow();
$intUsers = (int)$row['countid'];
$oH->selfurl = 'qti_adm_users.php';
$oH->selfname = L('Users').' ('.$intUsers.')';
$oH->selfparent = L('Board_content');
$oH->exiturl = 'qti_adm_users.php';
$oH->exitname = '&laquo; '.L('Users');
$pageGroup = 'all';
$intLimit = 0;
$intPage  = 1;
$strOrder = 'name';
$strDirec = 'asc';
$strOrder2 = ',name ASC';
$strCateg = 'all';
$intChecked = -1; // allow checking an id (-1 means no check)
// security check 1
if ( isset($_GET['group']) ) $pageGroup = substr($_GET['group'],0,7);
if ( isset($_GET['page']) ) $intPage = (int)$_GET['page'];
if ( isset($_GET['order']) ) $strOrder = $_GET['order'];
if ( isset($_GET['dir']) ) $strDirec = strtolower($_GET['dir']);
if ( isset($_GET['cat']) ) $strCateg = $_GET['cat'];
// security check 2 (no long argument)
if ( isset($strOrder[12]) ) die('Invalid argument #order');
if ( isset($strDirec[4]) ) die('Invalid argument #dir');
// items per page
$ipp = isset($_COOKIE[QT.'_admusersipp']) ? $_COOKIE[QT.'_admusersipp'] : 25;
if ( isset($_GET['ipp']) && in_array($_GET['ipp'],['25','50','100']) ) {
  $ipp = $_GET['ipp'];
  if ( PHP_VERSION_ID<70300 ) { setcookie(QT.'_admusersipp', $ipp, time()+3600*24*100, '/'); } else { setcookie(QT.'_admusersipp', $ipp, ['expires'=>time()+3600*24*100,'path'=>'/','samesite'=>'Strict']); }
}
$intLimit = ($intPage-1)*25;

// User menu

include 'qti_adm_users_edit.php';

// Prepare to check the last created user
if ( isset($_GET['cid']) )  $intChecked = (int)strip_tags($_GET['cid']); // allow checking an id. Note checklast overridres this id
if ( isset($_POST['cid']) ) $intChecked = (int)strip_tags($_POST['cid']);
if ( isset($_POST['checklast']) || isset($_GET['checklast']) )
{
  $oDB->query( 'SELECT max(id) as countid FROM '.TABUSER); // Find last id. This overrides the cid value !
  $row = $oDB->getRow();
  $intChecked = (int)$row['countid'];
}

// --------
// HTML BEGIN
// --------

include 'qti_adm_inc_hd.php';

// Global statistics
$intUsers = $oDB->count( TABUSER." WHERE id>0" ); // users
$intFalse = $oDB->count( TABUSER." WHERE id>1 AND firstdate=lastdate" ); // users without post
$str = addDate(date('Ymd His'),-1,'year');
$intSleeping = $oDB->count( TABUSER." WHERE id>1 AND lastdate<'$str'" ); // users sleeping 1 year

$icon = [' ', getSVG('chevron-circle-right'), getSVG('circle', 'class=disabled')];
echo '<div id="users-metadata">
<div id="users-filter">
';
echo '<table class="t-item">
<tr>
<th class="c-info">'.L('Members').'</th>
<td class="c-info">&nbsp;</td>
<td class="bold">'.$intUsers.'</td>
<td>'.($strCateg==='all' ? $icon[2] : '<a href="qti_adm_users.php" title="'.L('Show').'">'.$icon[1].'</a>').'</td>
</tr>
';
echo '<tr>
<th class="c-info">'.L('Users_FM').'</th>
<td class="c-info">'.L('H_Users_FM').'</td>
<td class="bold">'.$intFalse.'</td>
<td>'.($intFalse==0 ? $icon[0] : ($strCateg==='FM' ? $icon[2] : '<a href="qti_adm_users.php?cat=FM" title="'.L('Show').'">'.$icon[1].'</a>')).'</td>
</tr>
';
echo '<tr>
<th class="c-info">'.L('Users_SM').'</th>
<td class="c-info">'.L('H_Users_SM').'</td>
<td class="bold">'.$intSleeping.'</td>
<td>'.($intSleeping==0 ? $icon[0] : ($strCateg==='SM' ? $icon[2] : '<a href="qti_adm_users.php?cat=SM" title="'.L('Show').'">'.$icon[1].'</a>')).'</td>
</tr>
';
echo '</table>
';

echo '</div>
<div id="participants">
<p class="title">'.L('Top_participants').'</p>
<table>
';
  // Top 5 participants
  $strState = 'name, id, numpost FROM TABUSER WHERE id>0';
  $oDB->query( sqlLimit($strState,'numpost DESC',0,5) );
  for ($i=0;$i<5;$i++)
  {
    $row = $oDB->getRow();
    if ( !$row ) break;
    echo '<tr><td><a href="'.Href('qti_user.php').'?id='.$row['id'].'">'.$row['name'].'</a></td><td class="right">'.intK((int)$row['numpost']).'</td></tr>';
  }
echo '</table>';

echo '</div>
</div>
';

// Add user(s) form

echo '<p style="margin:12px 0">'.($strCateg=='all' ? '' : '<a href="qti_adm_users.php">'.getSVG('chevron-left').L('Show').' '.L('all').'</a> | ');
if ( !empty($formAddUser) ) echo '<a id="tgl-ctrl" class="tgl-ctrl" href="javascript:void(0)" onclick="qtToggle(); return false;">'.L('User_add').getSVG('angle-down','','',true).getSVG('angle-up','','',true).'</a> | ';
echo '<a href="qti_adm_users_imp.php">'.L('Users_import_csv').'...</a> | ';
echo '<a href="qti_adm_users_exp.php">'.L('Users_export_csv').'...</a></p>';
if ( !empty($formAddUser) ) echo $formAddUser;

// --------
// Category subform
// --------

if ( $strCateg!='all' ) {
  $intCount = $intFalse;
  if ( $strCateg=='SM' ) $intCount = $intSleeping;
  echo '<br><h1 class="title">'.L('Users_'.$strCateg).' <span style="font-size:11pt">('.L('h_Users_'.$strCateg).': '.$intCount.')<span></h1>'.PHP_EOL;
}

// Query by lettre
$arrGroup = array_filter(explode('|',$pageGroup)); // filter to remove empty
if ( count($arrGroup)===1 ) {
  switch((string)$pageGroup) {
    case 'all': $sqlWhere = ''; break;
    case '~':   $sqlWhere = ' AND '.sqlFirstChar('name','~'); break;
    default:    $sqlWhere = ' AND '.sqlFirstChar('name','u',strlen($pageGroup)).'="'.strtoupper($pageGroup).'"'; break;
  }
} else {
  $arr = array();
  foreach($arrGroup as $str) $arr[] = sqlFirstChar('name','u').'="'.strtoupper($str).'"';
  $sqlWhere = ' AND ('.implode(' OR ',$arr).')';
}


// Query by category
if ( $strCateg=='FM' ) $sqlWhere .= ' AND firstdate=lastdate'; //false members
if ( $strCateg=='SM' ) $sqlWhere .= ' AND lastdate<"'.addDate(date('Ymd His'),-1,'year').'"'; //sleeping members
if ( $strCateg=='CH' ) $sqlWhere .= ' AND children<>"0"'; //children
if ( $strCateg=='SC' ) $sqlWhere .= ' AND children="2"'; //sleeping children

// Count query
$intCount = $oDB->count( TABUSER.' WHERE id>0 '.$sqlWhere );

// Lettres bar
if ( $intCount>$ipp || $pageGroup!=='all' ) echo htmlLettres(Href($oH->selfurl).'?'.getURI('group,page'), $pageGroup, L('All'), 'lettres', L('Username').' '.L('starting_with').' ', $intCount>300 ? 1 : ($intCount>$ipp*2 ? 2 : 3));

// End if no result
if ( $intCount===0 )
{
  echo L('None');
  include 'qti_adm_inc_ft.php';
  exit;
}

// Build paging
$strPaging = makePager("qti_adm_users.php?cat=$strCateg&group=$pageGroup&order=$strOrder&dir=$strDirec",$intCount,$ipp,$intPage);
if ( !empty($strPaging) ) $strPaging = L('Page').$strPaging;
if ( $intCount<$intUsers ) $strPaging = '<span class="small">'.L('user',$intCount).' ('.L('from').' '.$intUsers.')</span>'.(empty($strPaging) ? '' : ' | '.$strPaging);

// --------
// Memberlist
// --------

$rowCommands = L('selection').': <a class="datasetcontrol" href="javascript:void(0)" data-action="usersrole">'.L('role').'</a> &middot; <a class="datasetcontrol" href="javascript:void(0)" data-action="usersdel">'.L('delete').'</a> &middot; <a class="datasetcontrol" href="javascript:void(0)" data-action="usersban">'.strtolower(L('Ban')).'</a> &middot; <a class="datasetcontrol" href="javascript:void(0)" data-action="userspic">'.L('picture').'</a>';
echo PHP_EOL.'<form id="form-users" method="post" action="'.APP.'_adm_register.php"><input type="hidden" id="form-users-action" name="a" />'.PHP_EOL;
echo '<div id="tabletop" class="table-ui top">';
echo '<div id="t1-edits-top" class="left checkboxcmds">'.getSVG('corner-up-right','class=arrow-icon').$rowCommands.'</div>';
echo '<div class="right">'.$strPaging.'</div></div>'.PHP_EOL;

// Table definition
$t = new TabTable('id=t1|class=t-item', $intCount);
$t->activecol = $strOrder;
$t->activelink = '<a href="'.$oH->selfurl.'?cat='.$strCateg.'&group='.$pageGroup.'&page=1&order='.$strOrder.'&dir='.($strDirec=='asc' ? 'desc' : 'asc').'">%s</a>&nbsp;'.getSVG('caret-'.($strDirec==='asc' ? 'up' : 'down')).'';
// TH
$t->arrTh['checkbox'] = new TabHead(($t->countDataRows<2 ? '&nbsp;' : '<input type="checkbox" name="t1-cb-all" id="t1-cb-all"/>'), 'class=c-checkbox');
$t->arrTh['name'] = new TabHead(L('User'), 'class=c-name', '<a href="'.$oH->selfurl.'?cat='.$strCateg.'&group='.$pageGroup.'&page=1&order=name&dir=asc">%s</a>');
$t->arrTh['pic'] = new TabHead(getSVG('camera'), 'class=c-pic|title='.L('Picture'));
$t->arrTh['role'] = new TabHead(L('Role'), 'class=c-role', '<a href="'.$oH->selfurl.'?cat='.$strCateg.'&group='.$pageGroup.'&page=1&order=role&dir=asc">%s</a>');
$t->arrTh['numpost'] = new TabHead(getSVG('comments'), 'class=c-numpost|title='.L('Messages'), '<a href="'.$oH->selfurl.'?cat='.$strCateg.'&group='.$pageGroup.'&page=1&order=numpost&dir=desc">%s</a>');
if ( $strCateg=='FM' || $strCateg=='SC' ) {
$t->arrTh['firstdate'] = new TabHead(L('Joined'), 'class=c-joined ellipsis', '<a href="'.$oH->selfurl.'?cat='.$strCateg.'&group='.$pageGroup.'&page=1&order=firstdate&dir=desc">%s</a>');
} else {
$t->arrTh['lastdate'] = new TabHead(L('Last_message').' (ip)', 'class=c-lastdate ellipsis', '<a href="'.$oH->selfurl.'?cat='.$strCateg.'&group='.$pageGroup.'&page=1&order=lastdate&dir=desc">%s</a>');
}
$t->arrTh['closed'] = new TabHead(getSVG('ban'), 'class=c-ban', '<a href="'.$oH->selfurl.'?cat='.$strCateg.'&group='.$pageGroup.'&page=1&order=closed&dir=desc" title="'.L('Banned').'">%s</a>');
$t->arrTh['id'] = new TabHead('Id', 'class=c-id',' <a href="'.$oH->selfurl.'?cat='.$strCateg.'&group='.$pageGroup.'&page=1&order=id&dir=asc">%s</a>');
// TD
$t->cloneThTd();

// === TABLE START DISPLAY ===

echo PHP_EOL;
echo $t->start().PHP_EOL;
echo '<thead>'.PHP_EOL;
echo $t->getTHrow().PHP_EOL;
echo '</thead>'.PHP_EOL;
echo '<tbody>'.PHP_EOL;

//-- LIMIT QUERY --
$strState = 'id,name,closed,role,numpost,firstdate,lastdate,ip FROM TABUSER WHERE id>'.($strCateg=='all' ? '0' : '1').$sqlWhere;
$oDB->query( sqlLimit($strState,$strOrder.' '.strtoupper($strDirec).($strOrder==='name' ? '' : $strOrder2),$intLimit,$ipp) );
// --------

$arrRow=array(); // rendered row. To remove duplicate in seach result
$intRow=0; // count row displayed
$days = BAN_DAYS;
while($row=$oDB->getRow())
{
  if ( in_array((int)$row['id'], $arrRow) ) continue; // this remove duplicate users in case of search result

  $arrRow[] = (int)$row['id'];
  if ( empty($row['name']) ) $row['name']='('.L('unknown').')';
  $bChecked = $row['id']==$intChecked;

  $intLock = (int)$row['closed']; if ( !array_key_exists($intLock,BAN_DAYS) ) $intLock=0;
  $strLock = $intLock ? '<span class="ban" title="'.L('Banned').' '.L('day',$days[$intLock]).'">'.$days[$intLock].'<span>' : L('n');

  // prepare row
  $t->arrTd['checkbox']->content = '<input type="checkbox" name="t1-cb[]" id="t1-cb-'.$row['id'].'" value="'.$row['id'].'"'.($bChecked ? 'checked' : '').' data-row="'.$intRow.'"/>'; if ( $row['id']<2) $t->arrTd['checkbox']->content = '&nbsp;';
  $t->arrTd['name']->content = '<a href="'.Href('qti_user.php').'?id='.$row['id'].'" title="'.qtAttr($row['name'],24).'">'.qtTrunc($row['name'],24).'</a>';
  $t->arrTd['pic']->content = '<div class="magnifier center">'.SUser::getPicture((int)$row['id'], 'data-magnify=0|onclick=this.dataset.magnify=this.dataset.magnify==1?0:1;', '').'</div>';
  $t->arrTd['role']->content = L('Role_'.strtoupper($row['role']));
  $t->arrTd['numpost']->content = intK((int)$row['numpost']);
  if ( $strCateg=='FM' || $strCateg=='SC' )
  {
  $t->arrTd['firstdate']->content = empty($row['firstdate']) ? '' : QTdatestr($row['firstdate'],'$','',true);
  }
  else
  {
  $t->arrTd['lastdate']->content = (empty($row['lastdate']) ? '' : QTdatestr($row['lastdate'],'$','',true)) . (empty($row['ip']) ? '' : '<br><small>('.$row['ip'].')</small>');
  }
  $t->arrTd['closed']->content = $strLock;
  $t->arrTd['id']->content = $row['id'];

  echo $t->getTDrow('id=t1-tr-'.$row['id'].'|class=t-item hover rowlight');
  ++$intRow; if ( $intRow>=$ipp ) break;

}

// === TABLE END DISPLAY ===

echo '</tbody>'.PHP_EOL;
echo '</table>'.PHP_EOL;
echo '<div id="tablebot" class="table-ui bot">';
echo $rowCommands ? '<div id="t1-edits-bot" class="left checkboxcmds">'.getSVG('corner-down-right','class=arrow-icon').$rowCommands.'</div>' : '<div></div>';
echo '<div class="right">'.$strPaging.'</div></div>'.PHP_EOL;
echo '</form>'.PHP_EOL;

// Extra command
if ( $strCateg!=='all' ) {
  echo '<p class="submit"><a href="'.APP.'_adm_register.php?a=catdel&cat='.$strCateg.'&n='.$intCount.'">'.L('Delete').' '.L('users_'.$strCateg).'...</a></p>';
}

// Extra user preference ipp
$m = new CMenu(['25|id=u25|href='.$oH->selfurl.'?ipp=25', '50|id=u50|href='.$oH->selfurl.'?ipp=50', '100|id=u100|href='.$oH->selfurl.'?ipp=100']);
echo '<p class="right">'.L('Show').': '.$m->build('u'.$ipp, 'default|style=color:#444;text-decoration:underline').' / '.L('page').'</p>';

// HTML END

$oH->scripts[] = '<script id="cbe" type="text/javascript" src="bin/js/qt_table_cb.js" data-tableid="t1"></script>';
$oH->scripts[] = 'const cmds = document.getElementsByClassName("checkboxcmds");
for (const el of cmds){ el.addEventListener("click", (e)=>{
  if ( e.target.tagName==="A" ) datasetcontrol_click("t1-cb[]", e.target.dataset.action);
}); }
function datasetcontrol_click(checkboxname,action)
{
  const checkboxes = document.getElementsByName(checkboxname);
  let n = 0;
  for (let i=0; i<checkboxes.length; ++i) if ( checkboxes[i].checked ) ++n;
  if ( n>0 ) {
    document.getElementById("form-users-action").value=action;
    document.getElementById("form-users").submit();
  } else {
    alert("'.L('Nothing_selected').'");
  }
  return false;
}';

// hide table-ui-bottom-controls if less than 5 table rows
$oH->scripts[] = 'qtHideAfterTable("t1-edits-bot","t1",true);';

include 'qti_adm_inc_ft.php';