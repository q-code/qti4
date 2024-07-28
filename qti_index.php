<?php // v4.0 build:20240210

session_start();
/**
 * @var CHtml $oH
 * @var array $L
 * @var CDatabase $oDB
 * @var CSection $oS
 */
require 'bin/init.php';

// ------
// SECURITY
// ------
if ( $_SESSION[QT]['board_offline']==='1' ) $oH->voidPage('tools.svg',99,true,false); //█
if ( $_SESSION[QT]['visitor_right']<1 && SUser::role()==='V' ) $oH->voidPage('user-lock.svg',11,true,false); //█

// ------
// INITIALIZE
// ------
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

// ------
// HTML BEGIN
// ------
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
    $logofile = empty($mSec['options']) ? '' : qtExplodeGet($mSec['options'],'logo',''); // specific logo, or '' for default logo
    $t->arrTd[0]->content = asImg( CSection::makeLogo($logofile,$mSec['type'],$mSec['status']), 'title='.L('Ico_section_'.$mSec['type'].'_'.$mSec['status']), url('qti_items.php?s='.$idSec) );
    $t->arrTd[1]->content = '<p class="sectionname"><a class="section" href="'.url('qti_items.php?s='.$idSec).'">'.$mSec['title'].'</a>'.(empty($mSec['descr']) ? '' : '</p><p class="sectiondesc">'.$mSec['descr'].'</p>');
    $t->arrTd[2]->content = $mSec['items']===0 ? '' : '<p>'.qtDate($mSec['lastpostdate'],'$','$',true,true,true, $sseId.'lastpostdate').'&thinsp;<a id="'.$sseId.'lastpostid" class="goto" href="'.url('qti_item.php').'?t='.$mSec['lastpostpid'].'#p'.$mSec['lastpostid'].'" title="'.L('Goto_message').'">'.qtSvg('caret-square-right').'</a></p><p>'.L('by').' <a id="'.$sseId.'lastpostuser" href="'.url('qti_user.php').'?id='.$mSec['lastpostuser'].'">'.$mSec['lastpostname'].'</a></p>';
    $t->arrTd[3]->content = qtK($mSec['items']);   $t->arrTd[3]->set('id',$sseId.'items');
    $t->arrTd[4]->content = qtK($mSec['replies']); $t->arrTd[4]->set('id',$sseId.'replies');
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
  var ns = "'.QT.'";
  var sseServer = "'.SSE_SERVER.'";
  var sseConnect = '.SSE_CONNECT.';
  var sseOrigin = "'.SSE_ORIGIN.'";
  window.setTimeout(function(){
    const script = document.createElement("script");
    script.src = "bin/js/qti_cse_index.js";
    document.getElementsByTagName("head")[0].appendChild(script);
  }, 10000);
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
$oH->symbols[] = qtSvgSymbol('caret-square-right','',['title'=>L('Goto_message'),'rect'=>true,'css'=>true]);

include 'qti_inc_ft.php';