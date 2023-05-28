<?php // v4.0 build:20230430

session_start();
/**
 * @var CHtml $oH
 * @var CDatabase $oDB
 * @var string $navCommandsRefine
 * @var string $pageTitle
 * @var string $uiCommands
 */
require 'bin/init.php';

$oH->selfurl = 'qti_items.php';
if ( !SUser::canView('V2') ) exitPage(11,'user-lock.svg'); //...

// ---------
// INITIALISE
// ---------

// check arguments
$q = ''; // type of search (if missing use $q='s')
$s = ''; // section $s can be '*' or [int] (after argument checking only [int] is allowed)
$st = ''; // status $st can be '*' or [string]
$v = ''; // searched text [string]
$v2 = ''; // timeframe [string]
$cid = -1; // allows checking an id when EditByRows (-1 means nothing)
qtArgs('q s st v v2 int:cid');
if ( empty($q) ) $q = 's';
if ( $s==='*' || $s==='' || !is_numeric($s) ) $s = '-1';
if ( $st==='' ) $st = '*';
$v = qtCleanArray($v); // array of (unique) values trimmed (not empty)

// initialise section
$s = (int)$s;
if ( $q==='s' && $s<0 ) die(__FILE__.' Missing argument s');
if ( $q==='s' || $s>=0 ) {
  $oS = new CSection($_Sections[$s]); // new Section($s)
                                                            // exit if user role not granted
  if ( $oS->type==='1' && (SUser::role()==='V' || SUser::role()==='U')) {
    $oH->selfname = L('Section');
    $oH->exitname = SLang::translate();
    exitPage(12); //...
  }
  if ( $oS->type==='2' && SUser::role()==='V') {
    $oH->selfname = L('Section');
    $oH->exitname = SLang::translate();
    exitPage(11,'user-lock.svg'); //...
  }
  $oH->selfname = L('Section').': '.$oS->title;
} else {
  $oS = new CSection(); // void-section in case of search query
  $oH->selfname = L('Search_results');
}

// initialise others
$oH->selfuri = qtURI('page|order|dir');
$strOrder = 'lastpostdate';
$strDirec = 'desc';
$strLastcol = $oS->getMF('options','last'); if  ($strLastcol=='N' || strtolower($strLastcol)==='none' ) $strLastcol='';
$intPage = 1;
$intLimit = 0;
if ( isset($_GET['page']) ) { $intPage = (int)$_GET['page']; $intLimit = ($intPage-1)*$_SESSION[QT]['items_per_page']; }
if ( isset($_GET['order']) ) $strOrder = $_GET['order'];
if ( isset($_GET['dir']) ) $strDirec = strtolower(substr($_GET['dir'], 0, 4));
if ( !isset($_SESSION['EditByRows']) || !SUser::isStaff() ) $_SESSION['EditByRows'] = 0;
if ( !isset($_SESSION[QT]['lastcolumn']) || $_SESSION[QT]['lastcolumn']=='none' ) $_SESSION[QT]['lastcolumn'] = '0';
$navCommands = '';
$rowCommands = ''; // commands when EditByRows

// ---------
// SUBMITTED preferences and staff action
// ---------

if ( isset($_POST['pref'])) {
  if ( in_array($_POST['pref'], array( 'n10', 'n20', 'n30', 'n50', 'n100'))) $_SESSION[QT]['items_per_page'] = substr($_POST['pref'], 1, 3);
  if ( $_POST['pref']=='togglenewsontop') $_SESSION[QT]['news_on_top'] = ($_SESSION[QT]['news_on_top'] ? '0' : '1');
  if ( $_POST['pref']=='toggleclosed') $_SESSION[QT]['show_closed'] = ($_SESSION[QT]['show_closed'] ? '0' : '1');
}
if ( isset($_POST['modaction']) && SUser::isStaff()) {
  if ( $_POST['modaction']=='nt') $oH->redirect('qti_edit.php?s='.$s.'&a=nt', L('New_item')); // exit
  $_SESSION[QT]['lastcolumn'] = empty($_POST['modaction']) ? '0' : $_POST['modaction']; // can be '0' (none)
}
if ( isset($_POST['toggleedit']) && $_POST['toggleedit']==='1' && SUser::isStaff()) {
  $_SESSION['EditByRows'] = $_SESSION['EditByRows'] ? '0' : '1';
}
// change lastcolumn if preference exisits
if ( !empty($_SESSION[QT]['lastcolumn']) ) $strLastcol = $_SESSION[QT]['lastcolumn']; // advanced query can override preference

// -----
// QUERY parts definition
// -----

$sqlFields = ($_SESSION[QT]['news_on_top'] ? "CASE WHEN t.type='A' AND t.status='A' THEN 'A' ELSE 'Z' END as typea," : '');
$sqlFields .= 't.*,p.title,p.icon,p.id as postid,p.type as posttype,p.textmsg,p.issuedate,p.username';
$sqlFrom = ' FROM TABTOPIC t INNER JOIN TABPOST p ON t.firstpostid=p.id'; // warning: include only firstpostid (not the replies)
$sqlWhere = ' WHERE t.section'.($q==='s' ? '='.$s : '>=0');
  // In private section, show topics created by user himself
  if ( $q==='s' && $oS->type==='2' && !SUser::isStaff()) $sqlWhere .= " AND (t.firstpostuser=".SUser::id()." OR (t.type='A' AND t.status='A'))";
$sqlValues = array(); // list of values for the prepared-statements
$sqlCount = 'SELECT count(*) as countid FROM TABTOPIC t'.$sqlWhere;
$sqlCountAlt='';
if ( $q!=='s' ) {
  include 'bin/lib_qti_query.php'; // warning: this changes $sqlFrom to include any post (also replies)
  $sqlWarning = sqlQueryParts($sqlFrom,$sqlWhere,$sqlValues,$sqlCount,$sqlCountAlt,$oH->selfuri); //selfuri is not urldecoded
  if ( $q==='adv' && !empty($v) ) $strLastcol = 'tags'; // forces display column tags
}

$forceShowClosed = $_SESSION[QT]['show_closed']==='0' && $st==='Z';
$sqlHideClosed = $_SESSION[QT]['show_closed']==='0' && !$forceShowClosed ? " AND t.status<>'Z'" : ''; // User preference, hide closed items (not for advanced query having status specified)

// Count topics & visible for current user ONLY
if ( ($q=='s' && $oS->type!==2) || ( $q=='s' && SUser::isStaff()) ) {
  // Using stats ($_SectionsStats)
  $stats = isset($_SectionsStats) ? $_SectionsStats : SMem::get('_SectionsStats');
  if ( !$forceShowClosed && !isset($stats[$s]['itemsZ']) ) $stats[$s]['itemsZ'] = $oDB->count(CSection::sqlCountItems($s,'items','1'));
  $oH->items = empty($stats[$s]['items']) ? 0 : (int)$stats[$s]['items'];
  if ( !empty($sqlHideClosed) ) $oH->itemsHidden = (int)$stats[$s]['itemsZ'];
} else {
  $oH->items = $oDB->count($sqlCount, $sqlValues);
  if ( !empty($sqlHideClosed) ) $oH->itemsHidden = $oH->items - $oDB->count($sqlCount.$sqlHideClosed, $sqlValues);
}
$intCount = $oH->items - $oH->itemsHidden;

// BUTTON LINE AND PAGER
if ( $q==='s' ) {
  $def = 'href="'.url('qti_edit.php').'?s='.$oS->id.'&a=nt|class=button btn-cmd';
  if ( $oS->status==='1' || (SUser::role()==='V' && $_SESSION[QT]['visitor_right']<7) ) {
    $def .= ' disabled|href=javascript:void(0)|tabindex=-1|title='.($oS->status==='1' ? L('E_section_closed') : L('R_member')); // class=button btn-cmd disabled
  }
  $navCommands = '<a'.attrRender($def).'>'.L('New_item').'</a>';
}
$navCommands .= '<a class="button btn-search" href="'.url('qti_search.php').'?'.$oH->selfuri.'" title="'.L('Search').'">'.qtSVG('search').'</a>';

$strPaging = makePager( url($oH->selfurl).'?'.$oH->selfuri, $intCount, (int)$_SESSION[QT]['items_per_page'], $intPage);
if ( $strPaging!='') $strPaging = L('Page').$strPaging;

// MAP
$useMap = false;
if ( qtModule('gmap')) {
  include translate(APP.'m_gmap.php');
  include 'qtim_gmap_lib.php';
  if ( gmapCan(empty($q) ? $oS->id : 'S')) $useMap = true;
  if ( $useMap) $oH->links[] = '<link rel="stylesheet" type="text/css" href="qtim_gmap.css"/>';
  if ( isset($_GET['hidemap'])) $_SESSION[QT]['m_gmap_hidelist'] = true;
  if ( isset($_GET['showmap'])) $_SESSION[QT]['m_gmap_hidelist'] = false;
  if ( !isset($_SESSION[QT]['m_gmap_hidelist'])) $_SESSION[QT]['m_gmap_hidelist'] = false;
}

// Page title description
$pageTitle ='';
$navCommandsRefine = '';
switch($q)
{
  case 's': if ( QT_SHOW_PARENT_DESCR ) $pageTitle = CSection::translate($s,'secdesc'); break;
  case 'ref': $pageTitle .= sprintf( L('Search_results_ref'), $v[0] ); break;
  case 'qkw':
  case 'kw':
    $arrVlbl = qtQuote($v,"&'");
    $to = isset($_GET['to']) ? $_GET['to'] : '0';
    $pageTitle .= sprintf( L('Search_results_keyword'), strtolower(implode(' '.L('or').' ',$arrVlbl)) );
    // for refine search detection: trim and remove quote on $v to avoid trailing quote be interpreted as a 2d word
    if ( count($v)==1 && strpos(qtAttr($v[0]),' ')!==false ) $navCommandsRefine = '<a class="button" href="'.$oH->selfurl.'?q=kw&to='.$to.'&v='.urlencode(str_replace(' ',QSEPARATOR,$v[0])).'"><small>'.L('Search_by_words').'</small></a>';
    if ( count($v)==1 && strpos($v[0],QSEPARATOR)!==false ) $navCommandsRefine = '<a class="button" href="'.$oH->selfurl.'?q=kw&to='.$to.'&v='.urlencode(str_replace(QSEPARATOR,' ',$v[0])).'"><small>'.L('Search_exact_words').' &lsquo;'.str_replace(QSEPARATOR,' ',$v).'&rsquo;</small></a>';
    if ( $to=='1' ) $pageTitle .= ' '. L('in_title_only');
    break;
  case 'user':
    $pageTitle .= sprintf(L('Search_results_user'), implode(' '.L('or').' ',$v));
    $navCommandsRefine = '<a class="button" href="'.url('qti_items.php').'?q=userm&'.qtUri('q').'"><small>'.L('Search').': '.L('item+').' '.L('and').' '.L('reply+').'</small></a>';
    break;
  case 'userm':
    $pageTitle .= sprintf(L('Search_results_user_m'), implode(' '.L('or').' ',$v));
    $navCommandsRefine = '<a class="button" href="'.url('qti_items.php').'?q=user&'.qtUri('q').'"><small>'.L('Search').': '.L('item+').' '.L('only').'</small></a>';
      break;
  case 'actor': $pageTitle .= sprintf(L('Search_results_actor'), implode(' '.L('or').' ',$v)); break;
  case 'last':
  case 'news':
  case 'insp': $pageTitle .= L('Search_results_'.$q); break;
  case 'adv':
    if ( empty($v2) ) $v2 = '*';
    $arrVlbl = qtQuote($v,"&'");
    $pageTitle .= sprintf( L(empty($arrVlbl) ? 'Search_results' : 'Search_results_tags'), strtolower(implode(' '.L('or').' ',$arrVlbl)) );
    if ( $v2!=='*' ) {
      switch($v2){
        case 'y': $pageTitle .= ' '.L('this_year'); break;
        case 'm': $pageTitle .= ' '.L('this_month'); break;
        case 'w': $pageTitle .= ' '.L('this_week'); break;
        default: $pageTitle .= ', '.L('dateMMM.'.$v2);
      }
    }
    break;
  default:
    $arrVlbl = $v;
    $pageTitle .= empty($arrVlbl) ? L('Item+',$oS->items) : sprintf( L('Search_results'), $oS->items, implode(' '.L('or').' ',$arrVlbl) );
}

// search options subtitle
$pageSubtitle = '';
if ( $q!=='s' ) {
  if ( $s>=0 ) $pageSubtitle = L('only_in_section').' &lsquo;'.CSection::translate($s).'&rsquo;';
  if ( $st!=='*' ) $pageSubtitle .= (empty($pageSubtitle) ? '' : ', ').L('status').' '.CTopic::getStatus($st); // statusnames for type 'T'
}
// full title
if ( !empty($pageTitle) ) $pageTitle = '<p class="pg-title">'.$pageTitle.'</p>'.(empty($pageSubtitle) ? '' : '<p class="pg-title pg-subtitle">'.$pageSubtitle.'</p>');

// --------
// HTML BEGIN
// --------

include APP.'_inc_hd.php';

// PAGE title and UI
include APP.'_items_ui.php'; // $ui

if ( !empty($pageTitle) || !empty($ui) ) {
  echo '<div id="title-top" class="flex-sp top">'.PHP_EOL;
  echo '<div id="title-top-l">'.(empty($pageTitle) ? '' : $pageTitle).'</div>'.PHP_EOL;
  echo '<div id="title-top-r" class="optionsbar-container">'.(empty($ui) ? '' : $ui).'</div>'.PHP_EOL;
  echo '</div>'.PHP_EOL;
}

if  ( !empty($sqlWarning) ) echo '<p class="warning">'.qtSVG('exclamation-triangle').' '.$sqlWarning.'</p>';

$navCommands = $oH->backButton().$navCommands.$navCommandsRefine;

// End if no results
if ( $intCount==0 ) {
  // if no result with sqlHideClosed, re-count without
  if ( !empty($sqlHideClosed) ) $intCount = $oDB->count($sqlCount, $sqlValues);
  echo '<div class="nav-top">'.$navCommands.'</div>'.PHP_EOL;
  echo '<p class="center" style="margin:1rem 0">'.L('No_result').'...</p>';
  if ( $oS->type==='2' && !SUser::isStaff() ) echo '<p class="center">'.L('Only_your_items').'</p>';
  if ( $intCount ) echo '<p class="center">'.qtSVG('exclamation-triangle').' '.L('Closed_item',$intCount).'. '.L('Closed_hidden_by_pref').' (<a href="javascript:void(0)" onclick="let d=document.getElementById(`pref`); if ( d) {d.value=`toggleclosed`;doSubmit(`formPref`);}">'.L('show').' '.L('closed_items').'</a>).</p>';
  // alternate query
  $arg = 'q='.$q;
  if ( $q==='user' || $q==='kw' || $q==='adv' ) $arg .= '&v='.implode(';',$v).'&v2='.urlencode($v2);
  echo '<p class="center"><a href="'.url('qti_items.php').'?'.$arg.'">'.L('Try_without_options').'</a></p>';
  include 'qti_inc_ft.php';
  exit;
}

// LIST TOPICS

// Last column: can be '0' (moderator requests no-field)
if ( isset($_SESSION[QT]['lastcolumn']) ) $strLast = $_SESSION[QT]['lastcolumn'];
if ( empty($strLast) || $strLast==='none' ) $strLast = '';

// Table definition
$useNewsOnTop = $_SESSION[QT]['news_on_top'];
// selfuri contains arguments WITHOUT order,dir
$t = new TabTable('id=t1|class=t-item|data-cbe', $intCount);
$t->activecol = $strOrder;
$t->activelink = '<a href="'.$oH->selfurl.'?'.$oH->selfuri.'&order='.$strOrder.'&dir='.($strDirec==='asc' ? 'desc' : 'asc').'">%s</a> '.qtSVG('caret-'.($strDirec==='asc' ? 'up' : 'down'));
$t->thead();
$t->tbody('data-dataset='.($useNewsOnTop ? 'newsontop' : 'items'));
// TH (note: class are defined after).
if ( !empty($_SESSION['EditByRows']) )
$t->arrTh['checkbox'] = new TabHead('<input type="checkbox"id="t1-cb-all" data-target="t1-cb[]"/>');
$t->arrTh['icon'] = new TabHead('&bull;', '', '<a href="'.$oH->selfurl.'?'.$oH->selfuri.'&order=icon&dir=asc">%s</a>');
if ( $q!=='s' || ($q==='s' && $oS->numfield!=='N' && $oS->numfield!=='') )
$t->arrTh['numid'] = new TabHead(L('Ref'), '', '<a href="'.$oH->selfurl.'?'.$oH->selfuri.'&order=numid&dir=desc">%s</a>');
$t->arrTh['title'] = new TabHead(L('Item+'), '', '<a href="'.$oH->selfurl.'?'.$oH->selfuri.'&order=title&dir=asc">%s</a>');
if ( $q!=='s' && $s<0 )
$t->arrTh['section'] = new TabHead(L('Section'), '', '<a href="'.$oH->selfurl.'?'.$oH->selfuri.'&order=section&dir=asc">%s</a>');
$t->arrTh['firstpostname'] = new TabHead(L('Author'), '', '<a href="'.$oH->selfurl.'?'.$oH->selfuri.'&order=firstpostname&dir=asc">%s</a>');
$t->arrTh['lastpostdate'] = new TabHead(L('Last_message'), '', '<a href="'.$oH->selfurl.'?'.$oH->selfuri.'&order=lastpostdate&dir=desc">%s</a>');
$t->arrTh['replies'] = new TabHead(L('Reply+'), '', '<a href="'.$oH->selfurl.'?'.$oH->selfuri.'&order=replies&dir=desc">%s</a>');
if ( in_array($strLastcol,['id','views','status','tags','wisheddate','notifiedname']) )
$t->arrTh[$strLastcol] = new TabHead(L(ucfirst($strLastcol)), '', '<a href="'.$oH->selfurl.'?'.$oH->selfuri.'&order='.$strLastcol.'&dir=desc">%s</a>');
// add class c-$k
foreach(array_keys($t->arrTh) as $k) $t->arrTh[$k]->add('class', 'c-'.$k);
// append class secondary
foreach(['firstpostname','tags','userlocation','usernumpost','views','prefix'] as $k) {
  if ( isset($t->arrTh[$k])) $t->arrTh[$k]->append('class', 'secondary');
}
// append class ellipsis
foreach(['firstpostname','lastpostdate','replies','views','id','status','section'] as $k) {
  if ( isset($t->arrTh[$k])) $t->arrTh[$k]->append('class', 'ellipsis');
}
// TD
$t->cloneThTd();

// Edit mode
if ( $_SESSION['EditByRows']) {
  $rowCommands = '&nbsp;<a class="rowcommands" href="javascript:void(0)" data-action="itemsType">'.L('Type').'/'.L('Status').'</a>';
  $rowCommands .= ' &middot; <a class="rowcommands" href="javascript:void(0)" data-action="itemsTags">'.L('Tags').'</a>';
  $rowCommands .= ' &middot; <a class="rowcommands" href="javascript:void(0)" data-action="itemsMove">'.L('Move').'</a>';
  $rowCommands .= ' &middot; <a class="rowcommands" href="javascript:void(0)" data-action="itemsDelete">'.L('Delete').'</a>'.PHP_EOL;

  $oH->scripts[] = '<script type="text/javascript" src="bin/js/qt_table_cb.js"></script>';
  $oH->scripts[] = 'const cmds = document.getElementsByClassName("checkboxcmds");
  for (const el of cmds){ el.addEventListener("click", (e)=>{ if ( e.target.tagName==="A" ) datasetcontrol_click("t1-cb[]", e.target.dataset.action); }); }
  function datasetcontrol_click(checkboxname,action)
  {
    const checkboxes = document.getElementsByName(checkboxname);
    let n = 0;
    for (let i=0; i<checkboxes.length; ++i) if ( checkboxes[i].checked ) ++n;
    if ( n>0 ) {
      document.getElementById("form-items-action").value=action;
      document.getElementById("form-items").submit();
    } else {
      alert("'.L('Nothing_selected').'");
    }
    return false;
  }';

}

// Buttons and paging
echo '<div id="t1-nav-top" class="nav-top">'.$navCommands.'</div>'.PHP_EOL;
echo '<div id="tabletop" class="table-ui top">';
echo $rowCommands ? '<div id="t1-edits-top" class="left checkboxcmds">'.qtSVG('corner-up-right','class=arrow-icon').$rowCommands.'</div>' : '<div></div>';
echo '<div class="right">'.$strPaging.'</div></div>'.PHP_EOL;

// === TABLE START DISPLAY ===
if ( $_SESSION['EditByRows'] )
{
echo '<form id="form-items" method="post" action="'.url('qti_dlg.php').'">
<input type="hidden" id="form-items-action" name="a" />
<input type="hidden" name="uri" value="'.$oH->selfuri.'"/>
';
}

echo $t->start();
echo '<thead>'.PHP_EOL;
echo $t->getTHrow();
echo '</thead>'.PHP_EOL;
echo '<tbody>'.PHP_EOL;

// ========
$sqlOrder = $strOrder=='title' ? 'p.title' : 't.'.$strOrder;
if ( $sqlOrder==='t.icon' ) $sqlOrder='t.status';
$oDB->query(sqlLimit($sqlFields.$sqlFrom.$sqlWhere.$sqlHideClosed, (empty($sqlOntop) ? '' : 'typea ASC,').$sqlOrder.' '.strtoupper($strDirec), $intLimit, $_SESSION[QT]['items_per_page'], $intCount), $sqlValues);
// ========

$intWhile=0;
$arrTopics=array(); // topics having replies (use in post-processing)
$arrTags=array();
$arrS = isset($t->arrTd['status']) ? SMem::get('_Statuses') : [];
$arrOptions = [];
$arrOptions['bmap'] = $useMap;
if ( $_SESSION[QT]['item_firstline']==='0' ) {
  $arrOptions['firstline'] = false;
} elseif ( $_SESSION[QT]['item_firstline']==='2' ) {
  $arrOptions['firstline'] = $oS->getMF('options','if','0')==='1';
} else {
  $arrOptions['firstline'] = true;
}
if ( $oS->id>=0 && !empty($oS->numfield) ) $arrOptions['numfield'] = $oS->numfield;
if ( $useMap && !empty($q) && !gmapCan($oS->id) ) $arrOptions['bmap'] = false; // skip map processing when search result includes an item from a section having mapping off

while( $row = $oDB->getRow() ) {

  // check if end of a tbody (dataset group)
  if ( $useNewsOnTop && !empty($row['typea']) && $row['typea']==='Z' ) {
    $useNewsOnTop = false; // end of news on top
    echo $t->tbody->end();
    $t->tbody->add('data-dataset', 'items');
    echo $t->tbody->start();
  }

  // FORMAT the data
  $t->setTDcontent(formatItemRow('t1', $t->getTHnames(), $row, $oS, $arrOptions), false); // adding extra columns not allowed
  // dynamic style
  if ( isset($t->arrTd['status']) )
  $t->arrTd['status']->add('style', empty($arrS[$row['status']]['color']) ? '' : 'background-color:'.$arrS[$row['status']]['color']);
  // add id in each cell (cse-sse)
  foreach(array_keys($t->arrTd) as $tdname)
  $t->arrTd[$tdname]->add('id','t'.$row['id'].'-c-'.$tdname);
  // add checkbox if edit mode
  if ( $_SESSION['EditByRows'] && $row['posttype']==='P' )
  $t->arrTd['checkbox']->content = '<input type="checkbox" name="t1-cb[]" id="t1-cb-'.$row['id'].'" value="'.$row['id'].'"'.($row['id']==$cid ? ' checked' : '').'/>';

  // OUPUT the row
  echo $t->getTDrow('id=t1-tr-'.$row['id'].'|class=t-item hover rowlight');

  // COLLECT
  if ( $row['replies']>0 ) $arrTopics[]=(int)$row['id'];
  if ( QT_LIST_TAG && !empty($_SESSION[QT]['tags']) && !empty($row['tags']) && count($arrTags)<32 ) $arrTags = qtCleanArray($row['tags'], ';', $arrTags);
  if ( $useMap && !gmapEmpty($row['x']) && !gmapEmpty($row['y']) ) {
    $y = (float)$row['y'];
    $x = (float)$row['x'];
    $strIco = $strRef = $strTitle = $strAttr = '';
     if ( isset($row['type']) && isset($row['status']) ) $strIco = CTopic::makeIcon($row['type'],$row['status'],false,'',QT_SKIN).' ';
     if ( isset($row['numid']) && isset($row['section']) && isset($arrSEC[(int)$row['section']]['numfield']) ) $strRef = CTopic::getRef($row['numid'],$arrSEC[(int)$row['section']]['numfield']).' ';
    if ( !empty($row['title']) ) $strTitle = qtTrunc($row['title'],25);
    if ( isset($row['firstpostdate']) && isset($row['firstpostname']) ) $strAttr = L('By').' '.$row['firstpostname'].' ('.qtDatestr($row['firstpostdate'],'$','$',true,true).')<br>';
    if ( isset($row['replies']) ) $strAttr .= L('Reply',(int)$row['replies']).' ';
    $strPname = $strRef.$strTitle;
    $strPinfo = $strIco.$strRef.'<br>'.$strTitle.'<br><span class="small">'.$strAttr.'</span> <a class="gmap" href="'.url('qti_item.php').'?t='.$row['id'].'">'.L('Open').'</a>';
    $oMapPoint = new CMapPoint($y,$x,$strPname,$strPinfo);
    // add extra $oMapPoint properties (if defined in section settings)
    $oSettings = getMapSectionSettings($q==='s' ? $s : 'S');
    if ( is_object($oSettings) ) foreach(array('icon','shadow','printicon','printshadow') as $prop) if ( property_exists($oSettings,$prop) ) $oMapPoint->$prop = $oSettings->$prop;
    $arrExtData[(int)$row['id']] = $oMapPoint;
  }
  // security limit break
  ++$intWhile; if ( $intWhile>=$_SESSION[QT]['items_per_page'] ) break;
}

// === TABLE END DISPLAY ===

echo '</tbody>
</table>
';
if ( SUser::isStaff() && !empty($_SESSION['EditByRows']) ) echo '</form>'.PHP_EOL;

// BUTTON LINE AND PAGER
$strCsv = '';
if ( SUser::isStaff() && !empty($_SESSION['EditByRows'])) $strCsv .= '<a id="cmd-export-selected" class="csv" href="javascript:void(0)" title="'.L('H_Csv').' ('.L('selected').')">'.L('Export').qtSVG('check-square').'</a> &middot; ';
$strCsv .= SUser::role()==='V' ? '' : htmlCsvLink(url('qtf_items_csv.php').'?'.$oH->selfuri, $intCount, $intPage);
echo '<div id="tablebot" class="table-ui bot">';
echo $rowCommands ? '<div id="t1-edits-bot" class="left checkboxcmds">'.qtSVG('corner-down-right','class=arrow-icon').$rowCommands.'</div>' : '<div></div>';
echo '<div class="right">'.$strPaging.'</div></div>'.PHP_EOL;
echo '<p class="right table-ui-export">'.$strCsv.'</p>'.PHP_EOL;
echo '<div id="t1-nav-bot" class="nav-bot">'.$navCommands.'</div>'.PHP_EOL;

// TAGS FILTRING
if ( QT_LIST_TAG && !empty($_SESSION[QT]['tags']) && count($arrTags)>0 ) {
  sort($arrTags);
  echo '<div class="tag-box"><p>'.qtSVG('tags').' '.L('Show_only_tag').'</p>';
  foreach($arrTags as $strTag) echo '<a class="tag" href="'.url('qti_items.php').'?q=adv&s='.$s.'&v='.urlencode($strTag).'" title="...">'.$strTag.'</a>';
  echo qtSVG('search','','',true).'</div>';
  $oH->scripts['tagdesc'] = '<script type="text/javascript" src="bin/js/qt_tagdesc.js" id="tagdesc" data-dir="'.QT_DIR_DOC.'" data-lang="'.QT_LANG.'"></script>';
}

// Post-compute user's replied items (for topics having replies). Result is added using js.
if ( QT_LIST_ME && count($arrTopics)>0 && (int)SUser::getInfo('numpost',0)>0 ) {
  $arr = array();
  $oDB->query( 'SELECT topic,issuedate FROM TABPOST WHERE type="R" AND userid='.SUser::id().' AND topic IN ('.implode(',',$arrTopics).')' );
  while($row=$oDB->getRow()) $arr[(int)$row['topic']] = '"'.qtDatestr($row['issuedate'],'j M','H:i',true,true).'"';
  if ( count($arr)>0 ) {
    $oH->scripts[] = 'function addIRe(table,tids,ttitles,title="I replied") {
      for (let i=0;i<tids.length;++i) {
        const el = document.getElementById(table+"re"+tids[i]);
        if ( el ) el.setAttribute("title", ttitles[i]+", "+title);
      }
    }
    addIRe("t1",['.implode(',', array_keys($arr)).'],['.implode(',', $arr).'],"'.L('You_reply').'");';
  }
}

// hide href column if empty
if ( $q!=='s' ) $oH->scripts[] = 'qtHideEmptyColumn();qtHideEmptyColumn("#t1 td.c-prefix", "#t1 th.c-prefix");';

// MAP MODULE, Show map

if ( $useMap ) {
  echo PHP_EOL,'<!-- Map module -->'.PHP_EOL;
  if ( count($arrExtData)==0 ) {
    echo '<p class="gmap nomap">'.L('Gmap.No_coordinates').'</p>';
    $useMap=false;
  } else {
    $oCanvas = new cCanvas();
    $oCanvas->Header( $arrExtData );
    $oCanvas->Footer( sprintf(L('Gmap.items'),L('item',count($arrExtData)),L('item',$intCount)) );
    echo $oCanvas->Render(true);
  }
  echo '<!-- Map module end -->'.PHP_EOL.PHP_EOL;
}

// --------
// HTML END
// --------

// MAP MODULE
/**
 * @var array $gmap_markers
 * @var array $gmap_events
 * @var array $gmap_functions
 */
if ( $useMap && !$_SESSION[QT]['m_gmap_hidelist'] )
{
  $gmap_shadow = false;
  $gmap_symbol = false;
  if ( !empty($_SESSION[QT]['m_gmap_gsymbol']) ) {
    $arr = explode(' ',$_SESSION[QT]['m_gmap_gsymbol']);
    $gmap_symbol=$arr[0];
    if ( isset($arr[1]) ) $gmap_shadow=$arr[1];
  }

  // check new map center
  $y = floatval(QTgety($_SESSION[QT]['m_gmap_gcenter']));
  $x = floatval(QTgetx($_SESSION[QT]['m_gmap_gcenter']));

  // center on the first item
  foreach($arrExtData as $oMapPoint) {
    if ( !empty($oMapPoint->y) && !empty($oMapPoint->x) ) {
      $y=$oMapPoint->y;
      $x=$oMapPoint->x;
      break;
    }
  }
  // update center
  $_SESSION[QT]['m_gmap_gcenter'] = $y.','.$x;

  $gmap_markers = array();
  $gmap_events = array();
  $gmap_functions = array();
  foreach($arrExtData as $oMapPoint) {
    if ( !empty($oMapPoint->y) && !empty($oMapPoint->x) ) {
      $user_symbol = $gmap_symbol; // required to reset symbol on each user
      if ( !empty($oMapPoint->icon) ) $user_symbol = $oMapPoint->icon;
      $gmap_markers[] = gmapMarker($oMapPoint->y.','.$oMapPoint->x,false,$user_symbol,$oMapPoint->title,$oMapPoint->info);
    }
  }
  $gmap_functions[] = 'function zoomToFullExtend(){
    if ( markers.length<2 ) return;
    var bounds = new google.maps.LatLngBounds();
    for (var i=markers.length-1; i>=0; i--) bounds.extend(markers[i].getPosition());
    map.fitBounds(bounds);
  }
  function showLocation(address){
    if ( infowindow ) infowindow.close();
    geocoder.geocode( { "address": address}, function(results, status) {
      if ( status == google.maps.GeocoderStatus.OK)
      {
        map.setCenter(results[0].geometry.location);
        if ( marker )
        {
          marker.setPosition(results[0].geometry.location);
        } else {
          marker = new google.maps.Marker({map: map, position: results[0].geometry.location, draggable: true, animation: google.maps.Animation.DROP, title: "Move to define the default map center"});
        }
      } else {
        alert("Geocode was not successful for the following reason: " + status);
      }
    });
  }';
  include 'qtim_gmap_load.php';
}

if ( isset($_GET['cb']) ) $oH->scripts[] = 'qtCheckboxIds(['.$_GET['cb'].']);';

// hide table-ui-bottom-controls if less than 5 table rows
$oH->scripts[] = 'qtHideAfterTable("t1-nav-bot");qtHideAfterTable("tablebot");';

// SSE (if section is not empty)
if ( $intCount>0 && SMemSSE::useSSE() ) {
  $oH->scripts[] = 'var cseMaxRows = '.SSE_MAXROWS.';
var cseShowZ = '.$_SESSION[QT]['show_closed'].';
if ( typeof EventSource==="undefined" ) {
  window.setTimeout(function(){location.reload(true);}, 120000); // use refresh (120s) when browser does not support SSE
} else {
  var ns = "'.QT.'";
  var sseServer = "'.SSE_SERVER.'";
  var sseConnect = '.SSE_CONNECT.';
  var sseOrigin = "'.SSE_ORIGIN.'";
  var cseStatusnames = '.json_encode(CTopic::getStatuses('T',true)).';
  var cseTypenames = '.json_encode(CTopic::getTypes()).';
  window.setTimeout(function(){
  const script = document.createElement("script");
  script.src = "bin/js/qti_cse_items.js";
  document.getElementsByTagName("head")[0].appendChild(script);
  }, 10000);
}';
} // TIPS: sse-constants MUST be VAR to be available in other javascript

// Symbols
echo '<svg xmlns="http://www.w3.org/2000/svg" style="display:none">'.PHP_EOL;
echo qtSVG('symbol-caret-square-right').PHP_EOL;
if ( QT_LIST_ME ) echo qtSVG('symbol-ireplied').PHP_EOL;
if ( $_SESSION[QT]['upload']!=='0' ) echo qtSVG('symbol-paperclip').PHP_EOL;
if ( !empty($_SESSION[QT]['tags']) ) {
  echo qtSVG('symbol-tag').PHP_EOL;
  echo qtSVG('symbol-tags').PHP_EOL;
}
echo '</svg>'.PHP_EOL;

include 'qti_inc_ft.php';