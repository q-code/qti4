<?php
// QT 4.0 build:20240210
// Actions GET['a'] are deldom,delsec,prune,delsecitems,status_del

session_start();
/**
* @var CHtml $oH
* @var CDatabase $oDB
*/
require 'bin/init.php';
if ( SUser::role()!=='A' ) die('Access denied');

include translate('lg_adm.php');

$a = '';
$s = -1;
qtArgs('a! int:s!'); if ( empty($a) || $s<0 ) die('Missing arg a or s'); // mandatory $a,$s from get or post

$parentname = L('Board_content');
$oH->name = L('Section');
$oH->exitname = L('Exit');
$oH->exiturl = APP.'_adm_sections.php';

$frm_title = 'Management';
$frm_dflt_args = '
<input type="hidden" name="a" value="'.$a.'"/>
<input type="hidden" name="s" value="'.$s.'"/>';
$frm_hd = '';
$frm = [];
$frm_ft = '';

switch($a) {

case 'deldom':

  // Caution: $s is the domain id in this case
  if ( $s==0 ) die('Domain 0 cannot be delete');
  $oH->name = L('Domain');

  // SUBMITTED
  if ( isset($_POST['ok']) && isset($_POST['itemDelete']) ) {

    if ( isset($_POST['dest']) ) CDomain::moveSections($s,(int)$_POST['dest']);
    CDomain::delete($s);
    // exit
    $_SESSION[QT.'splash'] = L('S_delete');
    $oH->redirect(); //█

  }

  // FORM
  $arrSections = CSection::getTitles(true, 'WHERE id IN ('.implode(',',CSection::getIdsInContainer($s)).')');
  $strSections = '('.L('none').')';
  if ( count($arrSections)>4 ) { $arrSections = array_slice($arrSections,0,4); $arrSections[]='...'; }
  if ( count($arrSections)>0 ) { $strSections = implode('<br>',$arrSections); }

  $frm_title = L('Domain_del');
  $frm[] = '<form method="post" action="'.$oH->php.'">'.$frm_dflt_args;
  $frm[] = '<article>';
  $frm[] = '<p>'.L('Domain').':</p>';
  $frm[] = '<p class="ellipsis indent"><span class="bold">'.CDomain::translate($s).'</span><br><span class="minor">#'.$s.' &middot; '.(isset($_Domains[$s]['title']) ? $_Domains[$s]['title'] : 'Domain '.$s).'</span></p>';
  $frm[] = '</article>';
  $frm[] = '<article>';
  $frm[] = '<p>'.L('Containing_sections').':</p>';
  $frm[] = '<p class="indent">'.$strSections.'</p>';
  if ( count($arrSections)>0 )
  {
    $frm[] = '<article>';
    $frm[] = '<p>'.L('Move_sections_to').':</p>';
    $frm[] = '<p class="indent"><select name="dest" size="1">'.qtTags(CDomain::getTitles([$s]), 0).'</select></p><br>';
    $frm[] = '</article>';
  }
  $frm[] = '<p class="row-confirm">'.L('Confirm').':</p>';
  $frm[] = '<p class="indent"><span class="cblabel"><input required type="checkbox" id="itemDelete" name="itemDelete"/> <label for="itemDelete">'.L('Domain_del').(count($arrSections)==0 ? '' : ' '.L('and').' '.L('move').' '.L('section',count($arrSections))).'</label></span></p>';
  $frm[] = '<p class="submit right"><button type="button" name="cancel" value="cancel" onclick="window.location=`'.$oH->exiturl.'`;">'.L('Cancel').'</button> <button type="submit" name="ok" value="ok">'.$frm_title.'</button></p>';
  $frm[] = '</form>';

  break;

case 'delsec':

  // SUBMITTED
  if ( isset($_POST['ok']) && isset($_POST['itemDelete']) ) {

    CSection::delete($s);
    $_SESSION[QT.'splash'] = L('S_delete');
    $oH->redirect(); //█

  }

  // FORM
  $countT = $oDB->count( CSection::sqlCountItems($s) );
  $countA = $countT===0 ? 0 : $oDB->count( CSection::sqlCountItems($s,'','A') );
  $countR = $countT===0 ? 0 : $oDB->count( CSection::sqlCountItems($s,'replies') );
  $frm_title = L('Section_del');
  $frm[] = '<form method="post" action="'.$oH->php.'">'.$frm_dflt_args;
  $frm[] = '<article>';
  $frm[] = '<p>'.L('Section').':</p>';
  $frm[] = '<p class="ellipsis indent"><span class="bold">'.CSection::translate($s).'</span><br><span class="minor">'.L('item',$countT).', '.L('news',$countA).', '.L('reply',$countR).' &middot; #'.$s.' '.(isset($_Sections[$s]['title']) ? $_Sections[$s]['title'] : 'Domain '.$s).'</span></p>';
  $frm[] = '</article>';
  if ( $countT+$countA>0 )
  $frm[] = '<article><p>'.L('Option').':</p><p class="indent"><a href="'.APP.'_dlg_adm.php?a=moveitems&s='.$s.'">'.L('Move_items').'...</a></p><br></article>';
  $frm[] = '<p class="row-confirm">'.L('Confirm').':</p>';
  $frm[] = '<p class="indent"><span class="cblabel"><input required type="checkbox" id="itemDelete" name="itemDelete"/> <label for="itemDelete">'.L('Section_del').($countT ? ' '.L('and').' '.L('item',$countT) : '').'</label></span></p>';
  $frm[] = '<p class="submit right"><button type="button" name="cancel" value="cancel" onclick="window.location=`'.$oH->exiturl.'`;">'.L('Cancel').'</button> <button type="submit" name="ok" value="ok">'.$frm_title.'</button></p>';
  $frm[] = '</form>';

  break;

case 'prune':

  $oH->name = L('Item+');
  $oH->exiturl = APP.'_adm_items.php';

  $days = 10;
  if ( isset($_GET['d']) ) $days=(int)$_GET['d'];
  if ( isset($_POST['d']) ) $days=(int)$_POST['d'];

  // SUBMITTED
  if ( isset($_POST['ok']) && isset($_POST['PruneT']) ) {

    CSection::deleteItems( $s, '0', (isset($_POST['type']) ? $_POST['type'] : ''), '', " AND replies=0 AND firstpostdate<'".addDate(date('Ymd His'),-$days,'day')."'" );
    $_SESSION[QT.'splash'] = L('S_delete');
    $oH->redirect(); //█

  }

  // FORM (default type/status is U=unchanged)
  $countU = $oDB->count( CSection::sqlCountItems($s,'unreplied','','','',$days) );
  $countUA = $countU===0 ? 0 : $oDB->count( CSection::sqlCountItems($s,'unreplied','0','A','',$days) );
  $frm_title = L('Prune');
  $frm[] = '<form method="post" action="'.$oH->php.'" onsubmit="validateForm();">'.$frm_dflt_args;
  $frm[] = '<input type="hidden" id="inDay" name="d" value="'.$days.'"/>';
  $frm[] = '<article>';
  $frm[] = '<p><span class="minor">'.qtSVG('info').' '.L('Unreplied').': '.sprintf(L('unreplied_def'),$days).'</span></p>';
  $frm[] = '</article>';
  $frm[] = '<article>';
  $frm[] = '<p>'.L('Items_in_section').':</p>';
  $frm[] = '<p class="ellipsis indent"><span class="bold">'.CSection::translate($s).'</span><br><span class="minor">'.L('Unreplied',$countU).', '.L('Unreplied_news',$countUA).' &middot; #'.$s.' '.(isset($_Sections[$s]['title']) ? $_Sections[$s]['title'] : 'Domain '.$s).'</span></p>';
  $frm[] = '</article>';
  $frm[] = '<article>';
  $frm[] = '<p>'.L('Options').':</p>';
  $frm[] = '<p class="indent">'.L('Type').' <select id="inType" name="type" size="1"'.($countUA>0 ? '' : ' disabled').'>
  <option value="*" selected>('.L('all').')</option>
  '.qtTags(CTopic::getTypes() ).'
  </select></p>';
  $frm[] = '</article>';
  $frm[] = '<p class="row-confirm">'.L('Confirm').':</p>';
  $frm[] = '<p class="indent"><span class="cblabel"><input required type="checkbox" id="inPrune" name="PruneT"/> <label for="inPrune">'.L('Delete').'</label></span></p>';
  $frm[] = '<p class="submit right"><button type="button" name="cancel" value="cancel" onclick="window.location=`'.$oH->exiturl.'`;">'.L('Cancel').'</button> <button type="submit" name="ok" value="ok">'.L('Delete').' (<span id="submit-sum">...</span>)</button></p>';
  $frm[] = '</form>';

$oH->scripts[] = 'const inDay = document.getElementById("inDay");
const inType = document.getElementById("inType");
const inPrune = document.getElementById("inPrune");
inType.addEventListener("change", unConfirm);
inPrune.addEventListener("change", () =>{ submitSum(); if ( inPrune.checked ) updateCounts("unreplied"); });
function validateForm() {
  if ( inPurne.checked ) return true;
  alert("'.L('Nothing_selected').'");
  return false;
}
function unConfirm() {
  document.getElementById("inPrune").checked=false;
  document.getElementById("submit-sum").innerHTML = "...";
}
function updateCounts(q) {
  fetch( `bin/srv_count.php?q=${q}&s='.$s.'&d=${inDay.value}&ft=${inType.value}&fs=0` )
  .then( response => response.json() )
  .then( data => { submitSum(data); } )
  .catch( err => console.log(err) );
}
function submitSum(n="...") { document.getElementById("submit-sum").innerHTML = n; }';

  break;

case 'delsecitems':

  $oH->name = L('Item+');
  $oH->exiturl = APP.'_adm_items.php';

  // SUBMITTED
  if ( isset($_POST['ok']) && (isset($_POST['deleteT']) || isset($_POST['deleteR'])) )
  {
    $found = 0;
    // count matching
    if ( !isset($_POST['deleteT']) )
    {
      // count matching replies
      $found = $oDB->count( CSection::sqlCountItems($s,'replies','','',$_POST['ti']) );
    }
    else
    {
      // count matching topics with criteria
      $found = $oDB->count( CSection::sqlCountItems($s,'items',$_POST['status'],$_POST['type'],$_POST['ti']) );
    }

    if ( $found ) {
      $onlyReplies = false; if ( !isset($_POST['deleteT']) && isset($_POST['deleteR']) ) $onlyReplies = true;
      CSection::deleteItems( $s, $_POST['status'], $_POST['type'], $_POST['ti'], '' ,$onlyReplies );
      // exit
      $_SESSION[QT.'splash'] = L('S_delete');
      $oH->redirect(); //█
    } else {
      $error = L('Nothing_selected');
    }
  }

  // FORM (default type/status is U=unchanged)
  // stat by year (keys with 'y-' required to force index as string)
  $arrCount= [];
  $arrCount['*']['T'] = $oDB->count( CSection::sqlCountItems($s) );
  $arrCount['*']['R'] = $arrCount['*']['T']===0 ? 0 : $oDB->count( CSection::sqlCountItems($s,'replies') );
  $arrCount['*']['A'] = $arrCount['*']['T']===0 ? 0 : $oDB->count( CSection::sqlCountItems($s,'items','','A') );
  $arrDisabled=array();
  $intYear = (int)date('Y');
  $arrYears = array('old'=>($intYear-2).' and older',($intYear-1)=>$intYear-1,$intYear=>$intYear);
  foreach(array_keys($arrYears) as $k) {
    $arrCount[$k]['T'] = $arrCount['*']['T']==0 ? 0 : $oDB->count( CSection::sqlCountItems($s,'items','','',$k) );
    if ( $arrCount[$k]['T']==0 ) $arrDisabled[]=$k;
  }
  foreach(array_keys($arrYears) as $k) $arrYears[$k] .= ' ('.L('item',$arrCount[$k]['T']).')';

  $frm_title = L('Delete');
  $frm[] = '<form method="post" action="'.$oH->php.'" onsubmit="if (this.deleteT.checked || this.deleteR.checked || this.deleteA.checked) return true; alert(`'.L('Nothing_selected').'`); return false;">'.$frm_dflt_args;
  $frm[] = '<article>';
  $frm[] = '<p>'.L('Items_in_section').':</p>';
  $frm[] = '<p class="ellipsis indent"><span class="bold">'.CSection::translate($s).'</span><br><span class="minor">'.L('item',$arrCount['*']['T']).', '.L('news',$arrCount['*']['A']).', '.L('reply',$arrCount['*']['R']).' &middot; #'.$s.' '.(isset($_Sections[$s]['title']) ? $_Sections[$s]['title'] : 'Domain '.$s).'</span></p><br>';
  $frm[] = '</article>';
  $frm[] = '<article>';
  $frm[] = '<p>'.L('Options').':</p>';
  $frm[] = '<p class="indent">'.L('Year').' <select id="tf" name="tf" size="1"><option value="*" selected>('.L('all').')</option>
  '.qtTags($arrYears,'','','',$arrDisabled).'</select></p>';
  $frm[] = '<p class="indent">'.L('Type').' <select id="inType" name="type" size="1"><option value="*" selected>('.L('all').')</option>'.qtTags(CTopic::getTypes() ).'</select></p>';
  $frm[] = '<p class="indent">'.L('Status').' <select id="inStatus" name="status" size="1"><option value="*" selected>('.L('all').')</option>'.qtTags(CTopic::getStatuses() ).'</select></p>';
  $frm[] = '</article>';
  $frm[] = '<p class="row-confirm">'.L('Confirm').':</p>';
  $frm[] = '<p class="indent"><span class="cblabel"><input type="checkbox" id="deleteT" name="deleteT"/> <label for="deleteT">'.L('Delete').' '.L('item+').'</label></span></p>';
  $frm[] = '<p class="indent"><span class="cblabel"><input type="checkbox" id="deleteR" name="deleteR"/> <label for="deleteR">'.L('Delete').' '.L('reply+').'</label></span></p>';
  $frm[] = '<p class="indent"><span class="cblabel"><input type="checkbox" id="deleteA" name="dropattach" /> <label for="deleteA">'.L('Drop_attachments').'<small id="attachoption"></small></label></span></p>';
  $frm[] = '<p class="submit right"><button type="button" name="cancel" value="cancel" onclick="window.location=`'.$oH->exiturl.'`;">'.L('Cancel').'</button> <button type="btnSubmit" name="ok" value="ok">'.L('Delete').' (<span id="submit-sum">...</span>)</button></p>';
  $frm[] = '</form>';

$oH->scripts[] = 'const inTF = document.getElementById("inTF");
const inType = document.getElementById("inType");
const inStatus = document.getElementById("inStatus");
const deleteT = document.getElementById("deleteT");
const deleteR = document.getElementById("deleteR");
const deleteA = document.getElementById("deleteA");
const optionA = document.getElementById("attachoption");
inTF.addEventListener("change", unConfirm);
inType.addEventListener("change", unConfirm);
inStatus.addEventListener("change", unConfirm);
deleteT.addEventListener("change", () => {
  submitSum();
  if ( deleteT.checked ) {
    deleteR.checked = true;
    deleteR.disabled = true;
    deleteA.checked = true;
    deleteA.disabled = true;
    updateCounts("T");
  } else {
    deleteR.checked = false;
    deleteR.disabled = false;
    deleteA.checked = false;
    deleteA.disabled = false;
    optionA.innerHTML = "";
  }
});
deleteR.addEventListener("change", () => {
  submitSum();
  if ( deleteR.checked ) {
    deleteA.checked = true;
    deleteA.disabled = true;
    optionA.innerHTML = " ('.L('reply+').' '.L('only').')";
    updateCounts("R");
  } else {
    deleteA.checked = false;
    deleteA.disabled = false;
    optionA.innerHTML = "";
  }
});
deleteA.addEventListener("change", () => {
  submitSum();
  if ( deleteA.checked ) updateCounts("attach");
});
function unConfirm() {
  deleteT.checked=false;
  deleteR.checked=false;
  deleteR.disabled=false;
  deleteA.checked=false;
  deleteA.disabled=false;
  optionA.innerHTML = "";
  document.getElementById("submit-sum").innerHTML = "...";
}
function updateCounts(q) {
  fetch( `bin/srv_count.php?q=${q}&s='.$s.'&fs=${inStatus.value}&ft=${inType.value}&ti=${inTF.value}` )
  .then( response => response.json() )
  .then( data => { submitSum(data); } )
  .catch( err => console.log(err) );
}
function submitSum(n="...") { document.getElementById("submit-sum").innerHTML = n; }';

  break;

case 'moveitems':

  $oH->name = L('Item+');
  $oH->exiturl = APP.'_adm_items.php';

  // SUBMITTED
  if ( isset($_POST['ok']) && isset($_POST['dest']) && $_POST['dest']!=='' ) {

    $found = $oDB->count( CSection::sqlCountItems($s,'items',$_POST['status'],$_POST['type'],$_POST['ti']) );
    if ( $found ) {
      CSection::moveAllItems( $s, (int)$_POST['dest'],(int)$_POST['renum'], isset($_POST['dropprefix']), $_POST['status'], $_POST['type'], $_POST['ti'] );
      // exit
      $_SESSION[QT.'splash'] = L('S_update');
      $oH->redirect(); //█
    } else {
      $error = L('Nothing_selected');
    }

  }

  // FORM (default type/status is U=unchanged)
  // stat by year (keys with 'y-' required to force index as string)
  $arrCount['*']['T'] = $oDB->count( CSection::sqlCountItems($s) );
  $arrCount['*']['A'] = $arrCount['*']['T']===0 ? 0 : $oDB->count( CSection::sqlCountItems($s,'items','','A') );
  $arrCount['*']['R'] = $arrCount['*']['T']===0 ? 0 : $oDB->count( CSection::sqlCountItems($s,'replies') );
  $arrCount['*']['C'] = $arrCount['*']['T']===0 ? 0 : $oDB->count( CSection::sqlCountItems($s,'items','1') );
  $arrDisabled=array();
  $intYear = (int)date('Y');
  $arrYears = array('old'=>($intYear-2).' and older',($intYear-1)=>$intYear-1,$intYear=>$intYear);
  foreach(array_keys($arrYears) as $k) {
    $arrCount[$k]['T'] = $arrCount['*']['T']==0 ? 0 : $oDB->count( CSection::sqlCountItems($s,'items','','',$k) );
    if ( $arrCount[$k]['T']===0 ) $arrDisabled[]=$k;
  }
  foreach(array_keys($arrYears) as $k) $arrYears[$k] .= ' ('.L('item',$arrCount[$k]['T']).')';

  $frm_title = L('Move');
  $frm[] = '<form method="post" action="'.$oH->php.'">'.$frm_dflt_args;
  $frm[] = '<article>';
  $frm[] = '<p>'.L('Items_in_section').':</p>';
  $frm[] = '<p class="ellipsis indent"><span class="bold">'.CSection::translate($s).'</span><br><span class="minor">'.L('item',$arrCount['*']['T']).', '.L('news',$arrCount['*']['A']).', '.L('reply',$arrCount['*']['R']).' &middot; #'.$s.' '.(isset($_Sections[$s]['title']) ? $_Sections[$s]['title'] : 'Domain '.$s).'</span></p>';
  $frm[] = '</article>';
  $frm[] = '<article>';
  $frm[] = '<p>'.L('Move_to').':</p>';
  $frm[] = '<p class="indent"><select name="dest" size="1" required><option value="" disabled selected hidden></option>'.sectionsAsOption(-1,[$s]).'</select></p>';
  $frm[] = '<p class="indent"><select name="renum" size="1">
  <option value="1">'.L('Move_keep').'</option>
  <option value="0">'.L('Move_reset').'</option>
  <option value="2">'.L('Move_follow').'</option>
  </select></p>';
  $frm[] = '<p class="indent"><span class="cblabel"><input type="checkbox" id="dropprefix" name="dropprefix" checked/> <label for="dropprefix">'.L('Remove').' '.L('item').' '.L('prefix').'</label></span></p>';
  $frm[] = '</article>';
  $frm[] = '<article>';
  $frm[] = '<p>'.L('Options').':</p>';
  $frm[] = '<p class="indent">'.L('Year').' <select id="inTF" name="tf" size="1">
  <option value="*" selected>('.L('all').')</option>
  '.qtTags($arrYears,'','','',$arrDisabled).'
  </select></p>';
  $frm[] = '<p class="indent">'.L('Type').' <select id="inType" name="type" size="1">
  <option value="*" selected>('.L('all').')</option>
  '.qtTags(CTopic::getTypes()).'
  </select></p>';
  $frm[] = '<p class="indent">'.L('Status').' <select id="inStatus" name="status" size="1">
  <option value="*" selected>('.L('all').')</option>
  '.qtTags(CTopic::getStatuses()).'
  </select></p>';
  $frm[] = '</article>';
  $frm[] = '<p class="row-confirm">'.L('Confirm').':</p>';
  $frm[] = '<p class="indent"><span class="cblabel"><input required type="checkbox" id="inMove" name="MoveT"/> <label for="inMove">'.L('Move').' '.L('item+').'</label></span></p>';
  $frm[] = '<p class="submit right"><button type="button" name="cancel" value="cancel" onclick="window.location=`'.$oH->exiturl.'`;">'.L('Cancel').'</button> <button type="submit" name="ok" value="ok">'.L('Move').' (<span id="submit-sum">...</span>)</button></p>';
  $frm[] = '</form>';

$oH->scripts[] = 'const inTF = document.getElementById("inTF");
const inType = document.getElementById("inType");
const inStatus = document.getElementById("inStatus");
const inMove = document.getElementById("inMove");
inTF.addEventListener("change", unConfirm);
inType.addEventListener("change", unConfirm);
inStatus.addEventListener("change", unConfirm);
inMove.addEventListener("change", () =>{ submitSum(); if ( inMove.checked ) updateCounts("T"); });
function unConfirm() {
  document.getElementById("inMove").checked=false;
  document.getElementById("submit-sum").innerHTML = "...";
}
function updateCounts(q) {
  fetch( `bin/srv_count.php?q=${q}&s='.$s.'&fs=${inStatus.value}&ft=${inType.value}&ti=${inTF.value}` )
  .then( response => response.json() )
  .then( data => { submitSum(data); } )
  .catch( err => console.log(err) );
}
function submitSum(n="...") { document.getElementById("submit-sum").innerHTML = n; }';

  break;

case 'status_del':

  if ( $s=='A' || $s=='Z' ) die('Status A and Z cannot be delete');

  // SUBMITTED
  if ( isset($_POST['ok']) && isset($_POST['itemDelete']) && isset($_POST['dest']) ) {

    SStatus::delete($s,$_POST['dest']);
    // exit
    $_SESSION[QT.'splash'] = L('S_delete');
    $oH->redirect(); //█

  }

  // FORM
  // list of status destination
  $strSdest = '';
  $arrS = SMem::get('_Statuses');
  foreach($arrS as $strKey=>$arrStatus) {
    if ( $strKey!=$s ) $strSdest .= '<option value="'.$strKey.'"/>'.$strKey.' - '.$arrStatus['name'].'</option>';
  }

  $frm_title = L('Delete').' '.L('status');
  $frm[] = '<form method="post" action="'.$oH->php.'">'.$frm_dflt_args;
  $frm[] = '<article>';
  $frm[] = '<p>'.L('Status').':</p>';
  $frm[] = '<p class="indent">'.$s.'&nbsp;&nbsp;'.asImg( QT_SKIN.$arrS[$s]['icon'], 'class=i-status|alt=-|title='.$arrS[$s]['name'] ).'&nbsp;&nbsp;'.$arrS[$s]['title'].'</p>';
  if ( !empty($arrS[$s]['statusdesc']) )
  $frm[] = '<p class="indent">'.$arrS[$s]['statusdesc'].'</p>';
  $frm[] = '</article>';
  $frm[] = '<article>';
  $frm[] = '<p>'.L('H_Status_move').':</p>';
  $frm[] = '<p class="indent"><select name="dest" size="1">'.$strSdest.'</select></p>';
  $frm[] = '</article>';
  $frm[] = '<p class="row-confirm">'.L('Confirm').':</p>';
  $frm[] = '<p class="indent"><span class="cblabel">
  <input required type="checkbox" id="itemDelete" name="itemDelete"/> <label for="itemDelete">'.L('Delete').' '.L('status').'</label></span></p>';
  $frm[] = '<p class="submit right"><button type="button" name="cancel" value="cancel" onclick="window.location=`'.$oH->exiturl.'`;">'.L('Cancel').'</button> <button type="submit" name="ok" value="ok">'.$frm_title.'</button></p>';
  $frm[] = '</form>';

  break;

default: die('Unknown command '.$a);

}

// DISPLAY PAGE
const HIDE_MENU_TOC = true;
const HIDE_MENU_LANG = true;
include APP.'_adm_inc_hd.php';

if ( !empty($frm_hd) ) echo $frm_hd.PHP_EOL;

CHtml::msgBox($frm_title);
echo implode(PHP_EOL,$frm);
CHtml::msgBox('/');

if ( !empty($frm_ft) ) echo $frm_ft.PHP_EOL;

unset($oH->scripts['formsafe']);
include APP.'_adm_inc_ft.php';