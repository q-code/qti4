<?php // v4.0 build:20240210

// Actions GET['a'] are (with access rights)
// [staff] itemsType: change type (A|T|I) or status (A..Z)
// [staff] itemsTags: add remove tags
// [staff] itemsMove: move to a section
// [staff] itemsDelete: delete items
// [owner] itemDelete: delete 1 item
// [owner] replyDelete: delete 1 reply
// [staff] itemParam: setup Inspection-parameters

session_start();
/**
 * @var CHtml $oH
 * @var array $L
 * @var CDatabase $oDB
 */
require 'bin/init.php';
if ( SUser::role()==='V' ) die('Access denied'); // minimum access rights

$a = '';
$s = 0;
$ids = '';
$uri = '';
qtArgs('a! int:s ids uri');
$ids = array_map('intval', explode(',',$ids));
if ( isset($_POST['t1-cb']) ) $ids = getPostedValues('t1-cb');
$strIds = implode(',',$ids);

$oH->selfname = L('Item+');
$oH->selfurl = APP.'_dlg.php';
$oH->exitname = L('Exit');
$oH->exiturl = APP.'_items.php';
$oH->exituri = empty($uri) ? 's='.$s : $uri;

$frm_title = 'Multiple edit';
$frm_dflt_args = '<input type="hidden" name="a" value="'.$a.'"/>
<input type="hidden" id="ids" name="ids" value="'.$strIds.'"/>
<input type="hidden" name="s" value="'.$s.'"/>
<input type="hidden" name="uri" value="'.$uri.'"/>';
$frm_hd = '';
$frm = [];
$frm_ft = '';

function renderItems(array $ids, bool $tags=false, bool $replies=false, bool $attach=false, bool $typeIcon=true) {
  $topIds = array_slice($ids,0,5);
  // process ids [array of int]
  $str = '';
  global $oDB;
  $oDB->query( "SELECT p.title,p.attach,t.status,t.type,t.firstpostname,t.firstpostdate,t.tags,t.replies FROM TABTOPIC t INNER JOIN TABPOST p ON t.firstpostid=p.id WHERE t.id IN (".implode(',',$topIds).")" );
  while( $row=$oDB->getRow() ) {
    $oT = new CTopic($row);
    $str .= '<p class="list ellipsis">';
    if ( $typeIcon ) $str .= $oT->getIcon(QT_SKIN).' ';
    $str .= '"'.qtTrunc($oT->title,30).'"';
    if ( $replies && $oT->items ) $str .= ' '.qtSVG('comments', 'title='.L('reply',$oT->items));
    if ( $attach && !empty($oT->attachinfo) ) $str .= ' '.qtSVG('paperclip', 'title='.L('Attachment'));
    if ( $tags ) $str .= ' '.$oT->getTagIcon();
    $str .= ' <span class="minor">'.L('by').' '.qtTrunc($oT->firstpostname,20).' ('.qtDate($oT->firstpostdate,'j M').')</span>';
    $str .= '</p>';
  }
  return $str.(count($ids)>5 ? '<p>...</p>' : '');
}
function renderReply(int $id, string $parentType='T', string $parentStatus='1') {
  global $oDB;
  $oDB->query( "SELECT * FROM TABPOST WHERE id=$id" );
  while( $row=$oDB->getRow() ) {
    $str = '<p class="indent" class="list ellipsis">'.CPost::getIconType($row['type'],$parentType,$parentStatus,QT_SKIN);
    $str .= ' "'.qtTrunc($row['textmsg'],100).'"<br>';
    $str .= '<small>'.L('by').' '.qtTrunc($row['username'],20).' ('.strtolower(qtDate($row['issuedate'],'j M')).')</small></p>';
  }
  return $str;
}
function listTags(array $ids, bool $sort=true, bool $format=true, int $max=32) {
  $arr = [];
  global $oDB;
  $oDB->query( "SELECT tags FROM TABTOPIC WHERE id IN (".implode(',',$ids).")" );
  while( $row=$oDB->getRow() ) {
    if ( count($arr)>$max ) break;
    if ( !empty($row['tags']) ) foreach(explode(';',$row['tags']) as $tag) if ( !in_array($tag,$arr) ) $arr[]=$tag;
  }
  if ( count($arr)===0 ) return array('('.L('none').')');
  if ( $sort ) sort($arr);
  if ( count($arr)>$max ) { $arr = array_slice($arr,0,$max-1); $arr[] = '...'; }
  if ( $format ) foreach($arr as $k=>$str) $arr[$k]='<span class="tag" onclick="tagClick(this.innerHTML)" data-tagdesc="'.qtAttr($str).'">'.$str.'</span>';
  return $arr;
}

// PROCESS $a
switch($a) {

case 'itemsType':

  // ACCESS RIGHTS
  if ( !SUser::isStaff() ) die('Access denied');

  // SUBMITTED
  if ( isset($_POST['ok']) ) {

    // update status (in not U unchanged)
    if ( isset($_POST['status']) && $_POST['status']!=='U' ) {
    $oDB->exec( 'UPDATE TABTOPIC SET status="'.$_POST['status'].'",statusdate="'.date('Ymd His').'" WHERE id IN ('.implode(',',$ids).')' );
    }
    // update type
    if ( isset($_POST['type']) && $_POST['type']!=='U' ) {
    $oDB->exec( 'UPDATE TABTOPIC SET type="'.$_POST['type'].'" WHERE id IN ('.implode(',',$ids).')' );
    }
    memFlushStats(); // clear cache
    $_SESSION[QT.'splash'] = L('S_update');
    $oH->redirect('exit');

  }

  // FORM (default type/status is U=unchanged)
  $frm_title = L('Change').' '.L('type').'/'.L('status');
  $frm[] = '<form method="post" action="'.url($oH->self()).'" onsubmit="return validateForm(this)">'.$frm_dflt_args;
  $frm[] = '<article>';
  $frm[] = '<p>'.L('Item+').':</p>';
  $frm[] = renderItems($ids,false,true);
  $frm[] = '</article>';
  $frm[] = '<article>';
  $frm[] = '<p>'.L('Type').' <select id="newtype" name="type" size="1"><option value="U" selected>('.L('unchanged').')</option>';
  $frm[] .= qtTags(CTopic::getTypes()).'</select> '.L('Status').' <select id="newstatus" name="status" size="1"><option value="U" selected>('.L('unchanged').')</option>'.qtTags(CTopic::getStatuses('T',true)).'</select></p>';
  $frm[] = '</article>';
  $frm[] = '<p class="submit right"><button type="button" name="cancel" value="cancel" onclick="window.location=`'.url($oH->exit()).'`;">'.L('Cancel').'</button> <button type="submit" name="ok" value="ok">'.L('Ok').' ('.count($ids).')</button></p>';
  $frm[] = '</form>';
  $oH->scripts[] = 'function validateForm(f) {
  if ( f.elements[0].value=="U" && f.elements[1].value=="U") { alert("'.L('Nothing_selected').'"); return false; }
  document.body.style.cursor = "wait";
  return true;
}
const nt = document.getElementById("newtype");
const ns = document.getElementById("newstatus");
nt.addEventListener("change", ()=>{
  if ( nt.value==="T" || nt.value==="U" ) {
    document.querySelectorAll("#newstatus option").forEach(opt => { opt.disabled = false; });
    return;
  }
  document.querySelectorAll("#newstatus option").forEach(opt => { if ( opt.value!=="U" && opt.value!=="A" && opt.value!=="Z") opt.disabled = true; });
  if ( ns.value==="U" ) return;
  if ( ns.value!=="A" && ns.value!=="Z" ) ns.value = "A";
});
  ';

  break;

case 'itemsTags':

  // ACCESS RIGHTS
  if ( !SUser::isStaff() ) die('Access denied');

  // SUBMITTED
  if ( isset($_POST['tag-ok']) && !empty($_POST['tagsedit']) ) {

    // update status
    foreach($ids as $id) {
      $oT = new CTopic($id);
      if ( $_POST['tag-ok']==='addtag' ) $oT->tagsAdd($_POST['tag-edit']);
      if ( $_POST['tag-ok']==='deltag' ) $oT->tagsDel($_POST['tag-edit']);
    }
    // exit
    $_SESSION[QT.'splash'] = L('S_update');

  }

  // FORM (default type/status is U=unchanged)
  $frm_title = L('Change').' '.L('tags');
  $frm[] = '<form method="post" action="'.url($oH->self()).'" autocomplete="off">'.$frm_dflt_args;
  $frm[] = '<article>';
  $frm[] = '<p>'.L('Item+').':</p>';
  $frm[] = renderItems($ids,true);
  $frm[] = '</article>';
  $frm[] = '<article>';
  $frm[] = '<p>'.L('Used_tags').':</p>';
  $frm[] = '<p>'.implode(' ',listTags($ids)).'</p>';
  $frm[] = '</article>';
  $frm[] = '<article>';
  $frm[] = '<p class="row-confirm">'.L('Change').' '.L('item',count($ids)).':</p>';
  $frm[] = '<div id="ac-wrapper-tag-edit">';
  $frm[] = '<input type="hidden" id="tag-dir" value="'.QT_DIR_DOC.'"/>';
  $frm[] = '<input type="hidden" id="tag-lang" value="'.QT_LANG.'"/>';
  $frm[] = '<input required type="text" id="tag-edit" name="tag-edit" size="15" maxlength="255" placeholder="'.L('Tags').'..." title="'.L('Edit_tags').'" data-multi="1" autocomplete="off"/><button type="reset" class="tag-btn" title="'.L('Reset').'" onclick="qtFocus(`tag-edit`)">'.qtSVG('backspace').'</button>&nbsp;<button type="submit" name="tag-ok" class="tag-btn" value="addtag" title="'.L('Add').'">'.qtSVG('plus').'</button><button type="submit" name="tag-ok" class="tag-btn" value="deltag" title="'.L('Delete_tags').'">'.qtSVG('minus').'</button>';
  $frm[] = '</div>';
  $frm[] = '</article>';
  $frm[] = '<p class="submit right"><button type="button" name="cancel" value="cancel" onclick="window.location=`'.url($oH->exit()).'`;">'.L('Cancel').'</button></p>';
  $frm[] = '</form>';
  $oH->scripts['tagdesc'] = '<script type="text/javascript" src="bin/js/qt_tagdesc.js" id="tagdesc" data-dir="'.QT_DIR_DOC.'" data-lang="'.QT_LANG.'"></script>';
  $oH->scripts['tags'] = '<script type="text/javascript" src="bin/js/qt_tags.js"></script>';
  $oH->scripts['ac'] = '<script type="text/javascript" src="bin/js/qt_ac.js"></script>
  <script type="text/javascript" src="bin/js/qti_config_ac.js"></script>';

  break;

case 'itemsMove':

  // ACCESS RIGHTS
  if ( !SUser::isStaff() ) die('Access denied');

  // SUBMITTED
  if ( isset($_POST['ok']) && isset($_POST['destination']) && $_POST['destination']!=='' ) {
    CSection::moveItems($ids, (int)$_POST['destination'], (int)$_POST['ref'], isset($_POST['dropprefix']) ? true : false);
    // exit
    $_SESSION[QT.'splash'] = L('S_update');
    $oH->redirect('exit');
  }

  // FORM (default type/status is U=unchanged)
  $frm_title = L('Move').' '.L('item+');
  $frm[] = '<form method="post" action="'.url($oH->self()).'" onsubmit="return validateForm(this)">'.$frm_dflt_args;
  $frm[] = '<article>';
  $frm[] = '<p>'.L('Item+').':</p>';
  $frm[] = renderItems($ids,false,true,true);
  $frm[] = '</article>';
  $frm[] = '<article>';
  $frm[] = '<p>'.L('Destination').' <select name="destination" size="1" required>
  <option value="-1" disabled selected hidden></option>
  '.sectionsAsOption(-1,[],[$fq]).'
  </select></p>';
  $frm[] = '</article>';
  $frm[] = '<article>';
  $frm[] = '<p>'.L('Options').':</p>';
  $frm[] = '<p>'.L('Ref').': <select name="ref" size="1">
  <option value="1">'.L('Move_keep').'</option>
  <option value="0">'.L('Move_reset').'</option>
  <option value="2">'.L('Move_follow').'</option>
  </select></p>';
  $frm[] = '<p><span class="cblabel"><input type="checkbox" id="dropprefix" name="dropprefix" checked/> <label for="dropprefix">'.L('Remove').' '.L('item').' '.L('prefix').'</label></span></p>';
  $frm[] = '</article>';
  $frm[] = '<p class="submit right"><button type="button" name="cancel" value="cancel" onclick="window.location=`'.url($oH->exit()).'`;">'.L('Cancel').'</button> <button type="submit" name="ok" value="ok">'.L('Ok').' ('.count($ids).')</button></p>';
  $frm[] = '</form>';

  break;

  case 'itemDelete':
  case 'itemsDelete':

    // ACCESS RIGHTS (staff or owner), for multiple edit, only staff
    if ( !SUser::isStaff() ) {
      if ( $a==='itemsDelete' ) die('Access denied');
      if ( SUser::id()!==CTopic::getOwner($ids[0]) ) die('Access denied');
    }

    // SUBMITTED
    if ( isset($_POST['ok']) ) try {

      if ( isset($_POST['deleteT']) ) {
        if ( count($ids)===0 ) throw new Exception( L('Delete').' '.L('item+').': 0 '.L('found') );
        CTopic::delete($ids,true);
      } elseif ( isset($_POST['deleteR']) ) {
        if ( count($ids)===0 || $oDB->count( "TABPOST WHERE type<>'P' AND topic IN ($strIds)" )===0 ) throw new Exception( L('Delete').' '.L('replies').': 0 '.L('found') );
        CTopic::deleteReplies($ids,true);
      } elseif ( isset($_POST['dropattach']) ) {
        if ( count($ids)===0 || $oDB->count( "TABPOST WHERE attach<>'' AND topic IN ($strIds)" )===0 ) throw new Exception( L('Drop_attachments').': 0 '.L('found') );
        CPost::dropAttachs($ids,true,true); // use a list of topics
      } else {
        throw new Exception( L('Nothing_selected') );
      }
      memFlushStats(); // clear cache
      $_SESSION[QT.'splash'] = L('S_delete');
      $oH->redirect('exit');

    } catch (Exception $e) {

      $error = $e->getMessage();

    }

  // FORM (default type/status is U=unchanged)
  $frm_title = L('Delete');
  $frm[] = '<form method="post" action="'.url($oH->self()).'" onsubmit="return validateForm(this)">'.$frm_dflt_args;
  $frm[] = '<article>';
  $frm[] = '<p>'.L('Item+').':</p>';
  $frm[] = renderItems($ids,false,true,true);
  $frm[] = '</article>';
  $frm[] = '<p class="row-confirm">'.L('Confirm').':</p>';
  $frm[] = '<p><span class="cblabel"><input type="checkbox" id="deleteT" name="deleteT"/> <label for="deleteT">'.L('Delete').' '.L('item+').'</label></span></p>';
  $frm[] = '<p><span class="cblabel"><input type="checkbox" id="deleteR" name="deleteR"/> <label for="deleteR">'.L('Delete').' '.L('reply+').'</label></span></p>';
  $frm[] = '<p><span class="cblabel"><input type="checkbox" id="deleteA" name="dropattach"/> <label for="deleteA">'.L('Drop_attachments').'<small id="attachoption"></small></label></span></p>';
  $frm[] = '<p class="submit right"><button type="button" name="cancel" value="cancel" onclick="window.location=`'.url($oH->exit()).'`;">'.L('Cancel').'</button> <button type="submit" name="ok" value="ok">'.L('Ok').' (<span id="submit-sum">...</span>)</button></p>';
  $frm[] = '</form>';
  $oH->scripts[] = 'const deleteT = document.getElementById("deleteT");
const deleteR = document.getElementById("deleteR");
const deleteA = document.getElementById("deleteA");
const optionA = document.getElementById("attachoption");
deleteT.addEventListener("change", () => {
  submitSum();
  optionA.innerHTML = "";
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
function validateForm() {
  if ( deleteT.checked || deleteR.checked || deleteA.checked ) return true;
  alert("'.L('Nothing_selected').'");
  return false;
}
function unConfirm() {
  deleteT.checked=false;
  deleteR.checked=false;
  deleteR.disabled=false;
  deleteA.checked=false;
  deleteA.disabled=false;
  optionA.innerHTML = "";
  document.getElementById("submsit-sum").innerHTML = "...";
}
function updateCounts(q) {
  fetch( `bin/srv_count.php?fq=${fq}&ids='.$strIds.'` )
  .then( response => response.json() )
  .then( data => { submitSum(data); } )
  .catch( err => console.log(err) );
}
function submitSum(n="...") { document.getElementById("submit-sum").innerHTML = n; }';

  break;

case 'replyDelete':

  $t = isset($_GET['t']) ? (int)$_GET['t'] : -1; if ( isset($_POST['t']) ) $t = (int)$_POST['t'];
  $p = isset($_GET['p']) ? (int)$_GET['p'] : -1; if ( isset($_POST['p']) ) $p = (int)$_POST['p'];
  if ( $t<0 || $p<0 ) die('replyDelete: missing argument');
  $oH->exiturl = APP.'_item.php?t='.$t;

  // ACCESS RIGHTS (user can be staff or post creator)
  if ( !SUser::isStaff() && SUser::id()!==CPost::getOwner($p) ) die('Access denied');

  // SUBMITTED
  if ( isset($_POST['ok']) ) {
    // delete only reply posts
    if ( isset($_POST['deletereply']) ) {
      CPost::delete($p);
      // find the new topic lastpost and count posts
      $voidTopic = new CTopic();
      $voidTopic->id = $t;
      $voidTopic->updMetadata((int)$_SESSION[QT]['posts_per_item']);
    }
   memFlushStats(); // clear cache
    $_SESSION[QT.'splash'] = L('S_delete');
    $oH->redirect('exit');
  }

  // FORM (default type/status is U=unchanged)
  $frm_title = L('Delete');
  $frm[] = '<form method="post" action="'.url($oH->self()).'"><input type="hidden" name="t" value="'.$t.'"/><input type="hidden" name="p" value="'.$p.'"/>'.$frm_dflt_args;
  $frm[] = '<article>';
  $frm[] = '<p>'.L('Reply').':</p>';
  $frm[] = renderReply($p);
  $frm[] = '</article>';
  $frm[] = '<p class="row-confirm">'.L('Confirm').':</p>';
  $frm[] = '<p><span class="cblabel"><input required type="checkbox" id="deletereply" name="deletereply"/> <label for="deletereply">'.L('Delete').' '.L('reply').'</label></span></p>';
  $frm[] = '<p class="submit right"><button type="button" name="cancel" value="cancel" onclick="window.location=`'.url($oH->exit()).'`;">'.L('Cancel').'</button> <button type="submit" name="ok" value="ok">'.L('Ok').'</button></p>';
  $frm[] = '</form>';

  break;

case 'itemParam':

  if ( !SUser::isStaff() ) die('Access denied');
  if ( count($ids)!=1 ) die('Editing parameters requires one '.L('Inspection').' ticket');

  $t = $ids[0]; // only one
  $oT = new CTopic($t);
  if ( $oT->type!='I' ) die('Editing parameters requires one '.L('Inspection').' ticket');
  $oH->exiturl = APP.'_item.php?t='.$t;

  $arr = qtExplode($oT->param);
  // Support for legacy options-coding
  if ( !isset($arr['Istatus']) && isset($arr['Itype']) ) $arr['Istatus'] = $arr['Itype'];
  if ( !isset($arr['Iaggr']) && isset($arr['Istat']) ) $arr['Iaggr'] = $arr['Istat'];

  // SUBMITTED
  if ( isset($_POST['ok']) ) {

    // change settings
    $arr['Istatus'] = $_POST['status'];
    $arr['Ilevel'] = $_POST['level'];
    $arr['Iaggr'] = $_POST['aggr'];
    $oT->param = qtImplode($arr,';');
    $oT->updateMF('param');

    // activate inspection and recompute aggregation
    $oT->setStatus($arr['Istatus'],false);
    if ( $oT->items>0 ) {
      $oT->z = $oT->InspectionAggregate();
      $oDB->exec( 'UPDATE TABTOPIC SET z='.$oT->z.' WHERE id='.$oT->id );
    }
    // exit
    $_SESSION[QT.'splash'] = L('S_update');
    $oH->redirect('exit');

  }

  // FORM
  $frm_title = L('Inspection').' '.L('Parameters');
  $frm[] = '<form method="post" action="'.url($oH->self()).'">'.$frm_dflt_args;
  $frm[] = '<article>';
  $frm[] = '<p>'.L('Item').':</p>';
  $frm[] = renderItems($ids,false,true,true);
  $frm[] = '</article>';
  $frm[] = '<article>';
  $frm[] = '<p>'.L('Status').':</p>';
  $frm[] = '<p><select name="status" size="1">'.qtTags(
    CTopic::getStatuses('I'),
    $arr['Istatus']).'</select></p>';
  $frm[] = '<p>'.L('I_level').':</p>';
  $frm[] = '<p><select name="level" size="1">'.qtTags(
    [
    2=>'2 - '.L('I_r_yes').', '.L('I_r_no'),
    3=>'3 - '.L('I_r_good').', '.L('I_r_medium').', '.L('I_r_bad'),
    5=>'5 - '.L('I_r_veryhigh').', '.L('I_r_high').', '.L('I_r_medium').', '.L('I_r_low').', '.L('I_r_verylow'),
    100=>'100 - '.L('Percent')
    ],
    $arr['Ilevel']).'</select></p>';
  $frm[] = '<p>'.L('I_aggregation').':</p>';
  $frm[] = '<p><select name="aggr" size="1">'.qtTags(
    [
    'mean'=>L('I_v_mean'),
    'min'=>L('I_v_min'),
    'max'=>L('I_v_max'),
    'first'=>L('I_v_first'),
    'last'=>L('I_v_last')
    ],
    $arr['Iaggr']).'</select></p>';
    $frm[] = '</article>';
    $frm[] = '<p class="submit right"><button type="button" name="cancel" value="cancel" onclick="window.location=`'.url('qti_item.php').'?t='.$t.'`;">'.L('Cancel').'</button> <button type="submit" name="ok" value="ok">'.L('Ok').'</button></p>';
  $frm[] = '</form>';

  break;

default: die('Unknown command '.$a);

}

// DISPLAY PAGE
$adm = substr($a,0,3)==='adm' ? '_adm' : '';
const HIDE_MENU_TOC=true;
const HIDE_MENU_LANG=true;
include APP.$adm.'_inc_hd.php';

if ( !empty($frm_hd) ) echo $frm_hd.PHP_EOL;

CHtml::msgBox($frm_title);
echo implode(PHP_EOL,$frm);
CHtml::msgBox('/');

if ( !empty($frm_ft) ) echo $frm_ft.PHP_EOL;

include APP.$adm.'_inc_ft.php';