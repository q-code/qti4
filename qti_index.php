<?php // v4.0 build:20230430

session_start();
/**
 * @var CHtml $oH
 * @var array $L
 * @var CDatabase $oDB
 * @var CSection $oS
 */
require 'bin/init.php';

// --------
// SECURITY
// --------

if ( $_SESSION[QT]['board_offline']==='1' ) exitPage(99,'tools.svg',false); //...
if ( $_SESSION[QT]['visitor_right']<1 && SUser::role()==='V' ) exitPage(11,'user-lock.svg',false); //...

// --------
// INITIALIZE
// --------

// MYBOARD Count MyTopics and MyAssign
$bMyBoard = SUser::role()!=='V'; // no myboard for visitor
if ( $bMyBoard ) {
  $intMyTopics = 0;
  $intMyAssign = 0;
  // Count my topics [firstpostuser]
  $oDB->query( 'SELECT count(id) as countid FROM TABTOPIC WHERE firstpostuser='.SUser::id().' AND status<>"Z"' );
  $row = $oDB->getRow();
  $intMyTopics = (int)$row['countid'];
  // Count Assigned topics
  if ( SUser::isStaff() )
  {
  $oDB->query( 'SELECT count(id) as countid FROM TABTOPIC WHERE actorid='.SUser::id().' AND status<>"Z"' );
  $row = $oDB->getRow();
  $intMyAssign = (int)$row['countid'];
  }
  // Activate my board
  if ( $intMyTopics===0 && $intMyAssign===0 ) { $bMyBoard=false; } else { $oH->links[] = '<link rel="stylesheet" type="text/css" href="'.QT_SKIN.'qti_myboard.css"/>'; }
}

// --------
// HTML BEGIN
// --------

include 'qti_inc_hd.php'; // includes myboard

// Table definition
$t = new TabTable('class=t-sec');
$t->thead();
$t->tbody();
// TH
$t->arrTh[0] = new TabHead('&nbsp;',          'class=c-icon');
$t->arrTh[1] = new TabHead('&nbsp;',          'class=c-section');
$t->arrTh[2] = new TabHead(L('Last_message'), 'class=c-issue ellipsis');
$t->arrTh[3] = new TabHead(L('Item+'),        'class=c-items ellipsis');
$t->arrTh[4] = new TabHead(L('Reply+'),       'class=c-replies ellipsis');
// TD
$t->cloneThTd();

$intSec = 0;
foreach($_Domains as $domId=>$pDomain) {

  $arrSections = CDomain::get_pSectionsVisible($domId); // Sections visible for user-role
  if ( count($arrSections)==0 ) continue; // Skip domain without section
  $t->arrTh[1]->content = $pDomain['title'];

  // Render domain/sections
  echo $t->start();
  echo $t->thead->start();
  echo $t->getTHrow();
  echo $t->thead->end();
  echo $t->tbody->start();
  foreach($arrSections as $idSec=>$mSec) {
    // translations
    $mSec['title'] = CSection::translate($idSec);
    $mSec['descr'] = CSection::translate($idSec,'secdesc');
    // output
    $intSec++;
    $sseId = 's'.$idSec.'-';
    $strLastpost = '&nbsp;';
    $logofile = empty($mSec['options']) ? '' : qtExplodeGet($mSec['options'],'logo',''); // specific logo, or '' for default logo
    if ( $mSec['items']>0 ) {
      $strLastpost = qtDatestr($mSec['lastpostdate'],'$','$',true,true,true, $sseId.'lastpostdate');
      $strLastpost .= ' <a id="'.$sseId.'lastpostid" class="lastitem" href="'.url('qti_item.php').'?t='.$mSec['lastpostpid'].'#p'.$mSec['lastpostid'].'" title="'.L('Goto_message').'">'.qtSVG('caret-square-right').'</a><br><small>'.L('by').' <a id="'.$sseId.'lastpostuser" href="'.url('qti_user.php').'?id='.$mSec['lastpostuser'].'">'.$mSec['lastpostname'].'</a></span>';
    }
    $t->arrTd[0]->content = asImg( CSection::makeLogo($logofile,$mSec['type'],$mSec['status']), 'title='.L('Ico_section_'.$mSec['type'].'_'.$mSec['status']), url('qti_items.php?s='.$idSec) );
    $t->arrTd[1]->content = '<a class="section" href="'.url('qti_items.php?s='.$idSec).'">'.$mSec['title'].'</a>'.(empty($mSec['descr']) ? '' : '<br><span class="sectiondesc">'.$mSec['descr'].'</span>');
    $t->arrTd[2]->content = $strLastpost;
    $t->arrTd[3]->content = qtK($mSec['items']);   $t->arrTd[3]->add('id',$sseId.'items');
    $t->arrTd[4]->content = qtK($mSec['replies']); $t->arrTd[4]->add('id',$sseId.'replies');
    echo $t->getTDrow('class=hover');
  }
  echo $t->tbody->end();
  echo $t->end();

}

// No public section

if ( $intSec===0 ) echo '<p>'.(SUser::role()==='V' ? L('E_no_public_section') : L('E_no_visible_section')).'</p>';

// HTML END

if ( isset($oS) ) unset($oS);

if ( SMemSSE::useSSE() )
{
  $oH->scripts[] = 'if ( typeof EventSource==="undefined" ){
  window.setTimeout(function(){location.reload(true);}, 120000); // use polyfill (refresh 120s) when browser does not support SSE
} else {
  var sid = "'.QT.'";
  var sseServer = "'.SSE_SERVER.'";
  var sseConnect = '.SSE_CONNECT.';
  var sseOrigin = "'.(defined('SSE_ORIGIN') ? SSE_ORIGIN : 'http://localhost').'";
  window.setTimeout(function(){
    const script = document.createElement("script");
    script.src = "bin/js/qti_cse_index.js";
    document.getElementsByTagName("head")[0].appendChild(script);
  },'.(defined('SSE_LATENCY') ? SSE_LATENCY*1000 : 10000).');
 }';
 // TIPS: sse-constants MUST be VAR to be available in other javascript
}

// DEBUG SSE
if ( isset($_SESSION['QTdebugsse']) && $_SESSION['QTdebugsse'] ) echo '<div id="serverData"></div>';

$oH->scripts[] ='const rows = document.querySelectorAll("tr.wayin");
rows.forEach( (row) => {
  if ( row.id.indexOf("-row")>=0 ) {
    const lnk = document.getElementById("wayout-"+row.id);
    if ( lnk ) window.location.assign(lnk.href);
  }
  row.style.cursor = "pointer";
});';

// Symbols
echo '<svg xmlns="http://www.w3.org/2000/svg" style="display:none">'.PHP_EOL;
echo qtSVG('symbol-caret-square-right').PHP_EOL;
echo '</svg>'.PHP_EOL;

include 'qti_inc_ft.php';