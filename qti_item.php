<?php // v4.0 build:20240210

session_start();
/**
 * @var CHtml $oH
 * @var CDatabase $oDB
 */
require 'bin/init.php';
$oH->selfurl = 'qti_item.php';
if ( !SUser::canView('V3') ) $oH->voidPage('user-lock.svg',11,true); //...

// ------
// PRE-INITIALISE
// ------
$t = -1; qtArgs('int:t!'); if ( $t<0 ) die('Invalid argument');
$oT = new CTopic($t,SUser::id()); //provide userid to update stats
$s = $oT->pid;

// ------
// SUBMITTED
// ------
if ( isset($_POST['Maction']) )
{
  $oH->exiturl  = 'qti_items.php?s='.$s;
  $oH->exitname = L('Section');
  if ( empty($_POST['Maction']) ) $oH->redirect (url('qti_item.php').'?t='.$t );
  if ( substr($_POST['Maction'],0,7)==='status_' ) $oT->setStatus( substr($_POST['Maction'],-1,1) );
  if ( substr($_POST['Maction'],0,5)==='type_' ) $oT->setType( substr($_POST['Maction'],-1,1) );
  if ( $_POST['Maction']==='reply' ) $oH->redirect( url('qti_edit.php').'?a=re&t='.$t, L('Reply') );
  if ( $_POST['Maction']==='move' ) $oH->redirect( url('qti_dlg.php').'?a=itemsMove&s='.$s.'&ids='.$t, L('Move') );
  if ( $_POST['Maction']==='delete' ) $oH->redirect( url('qti_dlg.php').'?a=itemsDelete&s='.$s.'&ids='.$t, L('Delete') );
}
if ( isset($_POST['actorid']) && $_POST['actorid']>0 && !empty($_POST['actorname']) )
{
  $oT->setActor((int)$_POST['actorid'], true, true, $_POST['actorname']);
  $oH->redirect(url('qti_item.php').'?t='.$t);
}
// ------
// INITIALISE and check grant access
// ------
$oS = new CSection($s);
if ( $oS->type==='1' && (SUser::role()==='V' || SUser::role()==='U') )
{
  // exit
  $oH->selfname = L('Section');
  $oH->exitname = SLang::translate();
  $oH->voidPage('', L('R_staff'));
}
if ( $oS->type==='2' && SUser::role()==='V' && $oT->type!=='A' )
{
  // exit
  $oH->selfname = L('Section');
  $oH->exitname = SLang::translate();
  $oH->voidPage('');
}
if ( $oS->type==='2' && SUser::role()==='U' && $oT->firstpostuser != SUser::id() && $oT->type!=='A' )
{
  // exit
  $oH->selfname = L('Section');
  $oH->exitname = SLang::translate();
  $oH->voidPage('', L('E_item_private').'<br>'.L('R_member'));
}
// access granted
$oT->viewsIncrement(SUser::id()); // increment views (only when access is granted)
$tagEditor = SUser::canEditTags($oT);
$navCommands = '';
$limit = 0;
$currentPage = 1;
if ( isset($_GET['page']) ) { $limit = ($_GET['page']-1)*$_SESSION[QT]['replies_per_page']; $currentPage=(int)$_GET['page']; }
if ( isset($_GET['view']) ) { $_SESSION[QT]['viewmode'] = $_GET['view']; }
if ( isset($_GET['order']) ) { $_SESSION[QT]['replyorder'] = $_GET['order']; }
$oH->exiturl = 'qti_items.php?s='.$s;
$oH->selfname = L('Messages');

// SUBMITTED CHANGE TAGS (tag-new can be empty to delete all tags)

if ( isset($_POST['tag-ok']) && isset($_POST['tag-new']) )
{
  $oT->tags = strip_tags(qtAttr($_POST['tag-new'])); // no quote in tags
  $oT->tagsUpdate();
}

// MAP MODULE

/**
 * @var string $strCheck
 * @var string $strCoord
 * @var string $strPcoord
 * @var boolean $bMapGoogle
 */
if ( qtModule('gmap') ) { $strCheck=$s; include 'qtim_gmap_ini.php'; } else { $bMap=false; }

if ( $bMap ) {
if ( !empty($oT->y) && !empty($oT->x) ) {

  $y = floatval($oT->y);
  $x = floatval($oT->x);
  $strIco = ''; if ( isset($oT->type) && isset($oT->status) ) $strIco = CTopic::makeIcon($oT->type,$oT->status,false,'',QT_SKIN).' ';
  $strPname = CTopic::getRef($oT->numid,$oS->numfield);
  $strPinfo = $strIco.(empty($strPname) ? '' : $strPname.'<br>').( $_SESSION[QT]['viewmode']==='c' ? '' : 'Lat: '.QTdd2dms($y).' <br>Lon: '.QTdd2dms($x).'<br>DD: '.round($oT->y,8).', '.round($oT->x,8) );
  $oMapPoint = new CMapPoint($y,$x,$strPname,'<p class="gmap">'.$strPinfo.'</p>');

  // add extra $oMapPoint properties (if defined in section settings)
  $oSettings = getMapSectionSettings($s);
  if ( is_object($oSettings) ) foreach(array('icon','shadow','printicon','printshadow') as $prop) if ( property_exists($oSettings,$prop) ) $oMapPoint->$prop = $oSettings->$prop;

  $arrExtData = array($oMapPoint);
  $strCoord = '<a href="javascript:void(0)"'.($bMapGoogle && !$_SESSION[QT]['m_gmap_hidelist'] ? ' onclick="gmapPan(`'.$y.','.$x.'`); return false;"' : '').' title="'.L('Coord').': '.round($y,8).','.round($x,8).'"><span title="'.L('latlon').' '.QTdd2dms($y).','.QTdd2dms($x).'">'.qtSVG('map-marker').'</span></a>';
  $strPcoord = '<a href="javascript:void(0)"'.($bMapGoogle && !$_SESSION[QT]['m_gmap_hidelist'] ? ' onclick="gmapPan(`'.$y.','.$x.'`); return false;"' : '').' title="'.L('map_Center').'">'.qtSVG('map-marker').'</a> Lat,Lon: '.QTdd2dms($y).','.QTdd2dms($x).( $_SESSION[QT]['viewmode']==='c' ? '' : ' DD: '.round($oT->y,8).','.round($oT->x,8) );

}}

// ------
// HTML BEGIN
// ------
include 'qti_inc_hd.php';

// -- Title and staff commands --
echo '<div class="right">';
if ( SUser::isStaff() ) include 'qti_item_ui.php';
echo '</div>';

// ITEM DESCRIPTION AND MAP

$strDescr = '';
$strLocation = '';
// Item description
if ( QT_SHOW_PARENT_DESCR )
{
  $strArgs = CTopic::getRef($oT->numid,$oS->numfield).' ';
  switch($oT->type)
  {
  case 'T': $strArgs .= $oT->getStatusName('unknown status'); break;
  case 'I': $strArgs .= L('Inspection');  break;
  case 'A': $strArgs .= L('News');  break;
  }
  $strDescr .= '<p class="pg-title">'.$strArgs.'</p>';
}

// map module
if ( $bMap )
{
  if ( !gmapEmptycoord($oT) )
  {
    $oCanvas = new CCanvas();
    if ( $_SESSION[QT]['viewmode']!=='c' && ($oT->firstpostuser==SUser::id() || SUser::isStaff()) ) $oCanvas->Footer(L('Gmap.editmove'));
    if ( isset($strPcoord) ) $oCanvas->Footer($strPcoord);
    $strLocation = $oCanvas->Render(false,'','gmap item'.($_SESSION[QT]['viewmode']==='c' ? ' compact' : ''));
  }
  else
  {
  $strLocation .= '<p class="gmap nomap">'.L('Gmap.No_coordinates').'</p>'.PHP_EOL;
  }
}

if ( !empty($strDescr) || !empty($strLocation) )
{
echo '<div id="title-top" class="flex-sp bot">'.PHP_EOL;
echo '<div id="title-top-l">'.(empty($strDescr) ? '' : $strDescr).'</div>'.PHP_EOL;
echo '<div id="title-top-r">'.(empty($strLocation) ? '' : $strLocation).'</div>'.PHP_EOL;
echo '</div>'.PHP_EOL;
}

// CONTENT

// Navigation buttons
$def = 'href='.url('qti_edit.php').'?t='.$oT->id.'&a=re|class=button';
if ( $oS->status==='1' || $oT->status==='Z' || (SUser::role()==='V' && $_SESSION[QT]['visitor_right']<6) ) {
  $def .= ' disabled|href=javascript:void(0)|tabindex=-1'; // class=button disabled
  if ( $oS->status==='1' )     { $def .= '|title='.L('E_section_closed'); }
  elseif ( $oT->status==='Z' ) { $def .= '|title='.L('Closed_item'); }
  else                         { $def .= '|title='.L('R_member'); }
}
$navCommands = $oH->backButton().'<a'.attrRender($def).'>'.L('Reply').'</a>';
echo '<div id="t1-nav-top" class="nav-top">'.$navCommands.'</div>
';

// First message
$oP = new CPost($oT->firstpostid,1);
echo $oP->render($oS,$oT,true,true,QT_SKIN,'r1');
if ( $_SESSION[QT]['tags']!='0' && ($tagEditor || !empty($oT->descr)) )
{
  $arrTags= empty($oT->descr) ? array() : explode(';',$oT->descr);
  echo '<div class="tags right" style="padding:4px 0"><span class="tags" title="'.L('Tags').'">'.qtSVG('tag'.(count($arrTags)>1 ? 's' : '')).'</span>&nbsp; ';
  if ( $tagEditor )
  {
    foreach($arrTags as $k=>$item) $arrTags[$k] = empty($item) ? '' : '<span class="tag" onclick="tagClick(this.innerHTML)" title="..." data-tagdesc="'.$item.'">'.$item.'</span>';
    echo '<div id="tag-shown" style="display:inline-block">'.implode(' ',$arrTags).'</div>';
    echo ' &nbsp; <a href="javascript:void(0)" id="tag-ctrl" class="tgl-ctrl" onclick="qtToggle(`tag-container`,`block`,`tag-ctrl`)" title="'.L('Edit').'">'.qtSVG('pen').qtSVG('angle-down','','',true).qtSVG('angle-up','','',true).'</a>'.PHP_EOL;
    echo '<div id="tag-container" style="display:none"><form method="post" action="'.url($oH->selfurl).'?s='.$s.'&t='.$t.'" onreset="qtFocus(`tag-edit`)">';
    echo '<input type="hidden" id="tag-dir" value="'.QT_DIR_DOC.'"/><input type="hidden" id="tag-lang" value="'.QT_LANG.'"/>';
    echo '<input type="hidden" id="tag-saved" value="'.qtAttr($oT->descr).'"/>';
    echo '<input type="hidden" id="tag-new" name="tag-new" maxlength="255" value="'.qtAttr($oT->descr).'"/>';
    echo '<div id="ac-wrapper-tag-edit"><div style="display:flex;align-items:center">';
    echo '<input required type="text" id="tag-edit" size="12" maxlength="255" placeholder="'.L('Tags').'..." title="'.L('Edit_tags').'" data-multi="1" autocomplete="off"/><button type="reset" class="tag-btn" title="'.L('Reset').'">'.qtSVG('backspace').'</button>&nbsp;<button type="submit" class="tag-btn" title="'.L('Add').'" onclick="tagAdd(); asyncSaveTag('.$t.'); return false;">'.qtSVG('plus').'</button><button type="submit" class="tag-btn"  title="'.L('Delete_tags').'" onclick="tagDel(); asyncSaveTag('.$t.'); return false;">'.qtSVG('minus').'</button>';
    echo '</div></div></form></div>';
  }
  else
  {
    foreach($arrTags as $strTag) echo '<span class="tag" title="...">'.$strTag.'</span> ';
  }
  echo '</div>'.PHP_EOL;
}

// REPLIES

if ( $oT->items>0 )
{

  $paging = makePager( url('qti_item.php?t='.$oT->id), $oT->items, (int)$_SESSION[QT]['replies_per_page'], $currentPage);
  if ( $paging!='' ) $paging = L('Page').$paging;

  echo '<p class="paging">'.$paging.'</p>
  ';
  // ========
  if ( !isset($_SESSION[QT]['replyorder']) ) $_SESSION[QT]['replyorder'] = 'A';
  $order = $_SESSION[QT]['replyorder']=='D' ? 'p.id DESC' : 'p.id ASC';
  $state = "p.*,u.role,u.location,u.signature FROM TABPOST p, TABUSER u WHERE p.id<>$oT->firstpostid AND p.userid=u.id AND p.topic=$oT->id";
  $oDB->query( sqlLimit($state,$order,$limit,$_SESSION[QT]['replies_per_page'],$oT->items) );
  // ========
  $iMsgNum = $limit+1;
  $intWhile= 0;
  $arrPReplies = []; // post replies
  $arrIReplies = []; // inspection replies
  // ========
  while ( $row=$oDB->getRow() )
  {
    $iMsgNum = $iMsgNum+1;
    $oP = new CPost($row,$iMsgNum); // when compact view $oP->text is qtInline
    if ( $oT->type==='I' ) {
      $arrIReplies[] = $oP->renderInspectionResult($oS,$oT,true,true,QT_SKIN);
    } else {
      $arrPReplies[] = $oP->render($oS,$oT,true,true,QT_SKIN);
    }
    $intWhile++;
  }

  // Add stacked replies (inspection results)
  if ( !empty($arrPReplies) ) {
    foreach($arrPReplies as $row) echo $row;
  }
  if ( !empty($arrIReplies) ) {
    echo '<p id="inspection-replies">';
    echo '<a title="'.L('order').'" href="qti_item.php?'.qtImplode(qtExplodeUri(url('qti_item.php?t='.$oT->id), 'order')).'&po='.($_SESSION[QT]['replyorder']=='A' ? 'D' : 'A').'">'.qtSVG('sort-amount-'.($_SESSION[QT]['replyorder']==='D' ? 'up' : 'down')).'</a>';
    echo ' &middot; ';
    echo L('Reply',$oT->items);
    echo ' &middot; ';
    echo L('I_v_'.$oT->getMF('param','Iaggr','mean')).' ';
    echo getScalebar($oT->z, $oT->getMF('param','Ilevel','3'));
    echo '</p>';
    foreach($arrIReplies as $row) echo $row;
  }

  // ========
  if ( $oT->type==='I' ) {
    $oH->scripts[] = 'function showAlt(id) {
    let dShort = document.getElementById(id+"-short");
    let dLong = document.getElementById(id+"-long");
    let dEdit = document.getElementById(id+"-edit");
    let dDel = document.getElementById(id+"-del");
    dShort.style.display = dShort.style.display=="none" ? "block" : "none";
    dLong.style.display = dLong.style.display=="none" ? "block" : "none";
    dEdit.style.display = dEdit.style.display=="none" ? "inline" : "none";
    dDel.style.display = dDel.style.display=="none" ? "inline" : "none";
  }';
  }
  // BUTTON LINE AND PAGER
  echo '<p class="paging">'.$paging.'</p>';

  if ( $oT->type!='I' && $oT->items>2 )
  {
  echo '
  <div class="table-ui bot">
  <div>'.str_replace(' accesskey="r"','',$navCommands).'</div>
  </div>
  ';
  }

}

// QUICK REPLY
if ( SUser::role()==='V' && $_SESSION[QT]['visitor_right']<7 ) {} else {
if ( $oS->status==='0' && $oT->status!=='Z' ) {
if ( $_SESSION[QT]['show_quick_reply']=='1' || ($_SESSION[QT]['show_quick_reply']=='2' && $oS->getMF('options','qr')==='1') ) {

$certificate = makeFormCertificate('b7033b5983ec3b0fef7b3c251f6d0b92');
echo '
<div id="message-preview"></div>
<form id="form-qr" method="post" action="'.url('qti_edit.php').'" data-itemtype="'.$oT->type.'">
<div class="quickreply">
';
echo '<div class="g-qr-icon"><p class="i-container" title="'.L('Reply').'">'.qtSVG('comment-dots').'</p></div>
<div class="g-qr-title">'.L('Quick_reply').'</div>
<div class="g-qr-bbc">'.(QT_BBC ? '<div class="bbc-bar">'.bbcButtons(1).'</div>' : '').'</div>
<div class="g-qr-text"><textarea'.($oT->type==='I' ? '' : ' required').' id="text-area" name="text" rows="4"></textarea>';
if ( $oT->type==='I' ) echo htmlScore($oT->getMF('param','Ilevel','3'), ' &nbsp;');
echo '
<p id="quickreply-footer"><a href="javascript:void(0)" onclick="document.getElementById(`form-qr`).submit();">'.L('More').'...</a></p>
</div>
';
echo '<div class="g-qr-btn">
<input type="hidden" name="s" value="'.$s.'"/>
<input type="hidden" name="t" value="'.$oT->id.'"/>
<input type="hidden" name="a" value="re"/>
<input type="hidden" name="userid" value="'.SUser::id().'"/>
<input type="hidden" name="username" value="'.SUser::name().'"/>
<input type="hidden" name="ref" value="'.$oT->numid.'"/>
<input type="hidden" name="icon" value="00"/>
<input type="hidden" name="title" />
<button type="submit" id="form-qr-preview" name="preview" value="'.$certificate.'" onclick="this.form.dataset.state=0">'.L('Preview').'...</button><button type="submit" id="dosend" name="dosend" value="'.$certificate.'">'.L('Send').'</button>
</div>
';
echo '</div>
</form>
';

// ------
// HTML END
// ------
$oH->scripts[] = 'const d = document.getElementById("form-qr-preview");
d.addEventListener("click", (e) => {
  if ( d.dataset.itemtype!=="I" && document.getElementById("text-area").value==="" ) return false;
  e.preventDefault();
  let formData = new FormData(document.getElementById("form-qr"));
  fetch("qti_edit_preview.php", {method:"POST", body:formData})
  .then( response => response.text() )
  .then( data => {
    document.getElementById("message-preview").innerHTML = data;
    document.querySelectorAll("#message-preview a").forEach( anchor => {anchor.href="javascript:void(0)"; anchor.target="";} ); } )
  .catch( err => console.log(err) );
});
';

}}}

// MAP MODULE

if ( $bMap ) {

  /**
   * @var array $gmap_markers
   * @var array $gmap_events
   * @var array $gmap_functions
   */
  $gmap_shadow = false;
  $gmap_symbol = false;
  if ( !empty($_SESSION[QT]['m_gmap_gsymbol']) )
  {
    $arr = explode(' ',$_SESSION[QT]['m_gmap_gsymbol']);
    $gmap_symbol=$arr[0];
    if ( isset($arr[1]) ) $gmap_shadow=$arr[1];
  }

  // check new map center
  $y = floatval(QTgety($_SESSION[QT]['m_gmap_gcenter']));
  $x = floatval(QTgetx($_SESSION[QT]['m_gmap_gcenter']));

  // First item is the item's location and symbol
  if ( isset($arrExtData[$oT->id]) )
  {
    // symbol by role
    $oMapPoint = $arrExtData[$oT->id];
    if ( !empty($oMapPoint->marker) ) $gmap_symbol = $oMapPoint->marker;

    // center on first item
    if ( !empty($oMapPoint->y) && !empty($oMapPoint->x) )
    {
    $y=$oMapPoint->y;
    $x=$oMapPoint->x;
    }
  }

  // update center
  $_SESSION[QT]['m_gmap_gcenter'] = $y.','.$x;

  $gmap_markers = [];
  $gmap_events = [];
  $gmap_functions = [];
  foreach($arrExtData as $oMapPoint)
  {
    if ( !empty($oMapPoint->y) && !empty($oMapPoint->x) )
    {
      $strSymbol = $gmap_symbol; // required to reset symbol on each user
      $strShadow = $gmap_shadow;
      if ( !empty($oMapPoint->marker) ) $strSymbol  = $oMapPoint->marker;
      $gmap_markers[] = gmapMarker($oMapPoint->y.','.$oMapPoint->x, false, $strSymbol, $oMapPoint->title, $oMapPoint->info, $strShadow );
    }
  }

  include 'qtim_gmap_load.php';

}

if ( $_SESSION[QT]['tags']!='0' ) {

  $oH->scripts['tagdesc'] = '<script type="text/javascript" src="bin/js/qt_tagdesc.js" data-dir="'.QT_DIR_DOC.'" data-lang="'.QT_LANG.'"></script>';
  if ( $tagEditor) {
    $oH->scripts['ac'] = '<script type="text/javascript" src="bin/js/qt_ac.js"></script>
    <script type="text/javascript" src="bin/js/qti_config_ac.js"></script>';
    $oH->scripts[] = '<script type="text/javascript" src="bin/js/qt_tags.js"></script>';
    $oH->scripts[] = 'function asyncSaveTag(item){
  const tag = document.getElementById("tag-new");
  fetch( `bin/srv_tagupdate.php?ref='.MD5(QT.session_id()).'&id=${item}&tag=${tag.value}` )
  .catch( err => console.log(err) );
}';
  }

}

if ( SUser::isStaff() ) {

  $oH->scripts['ac'] = '<script type="text/javascript" src="bin/js/qt_ac.js"></script>
  <script type="text/javascript" src="bin/js/qti_config_ac.js"></script>';
  $oH->scripts[] = 'acOnClicks["user"] = function(focusInput,btn) {
  if ( focusInput.id=="usr" ) {
    const d = document.getElementById("actorid");
    d.value= btn.dataset.id;
    document.getElementById("submitactor").disabled=false;
  }
}';

}

include 'qti_inc_ft.php';