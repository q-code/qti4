<?php // v4.0 build:20240210

/**
 * @var string $strDetailLegend
 * @var CHtml $oH
 * @var array $L
 * @var CDatabase $oDB
 * @var CSection $oS
 * @var int $s
 */

// END MAIN CONTENT
echo '
</div><!--main-ct-->
';

// time and moderator
$arr = [];
if ( !empty($_SESSION[QT]['show_time_zone']) ) {
  $arr[0] = gmdate($_SESSION[QT]['formattime'], time() + 3600*($_SESSION[QT]['time_zone']));
  if ( $_SESSION[QT]['show_time_zone']==='2' ) {
    $arr[0] .= ' (gmt';
    if ( $_SESSION[QT]['time_zone']>0 ) $arr[0] .= '+'.$_SESSION[QT]['time_zone'];
    if ( $_SESSION[QT]['time_zone']<0 ) $arr[0] .= $_SESSION[QT]['time_zone'];
    $arr[0] .= ')';
  }
}
// no moderator in case of index page and search results page (where $s=-1)
if ( QT_SHOW_MODERATOR && isset($oS) && is_a($oS,'CSection') && $oS->id>=0 ) {
  if ( !empty($oS->ownerid) && !empty($oS->ownername) ) $arr[1] = L('Role_C').': <a href="'.url('qti_user.php?id='.$oS->ownerid).'">'.$oS->ownername.'</a>';
}
echo '<div id="main-ft">
<p>'.implode(' &middot; ',$arr).'</p>
<p>';
if ( QT_SHOW_JUMPTO ) {
  echo '<select id="jumpto" size="1" onchange="window.location=this.value;" accesskey="j">';
  echo '<option disabled selected hidden>',L('Goto'),'...</option>';
  if ( $oH->selfurl==='qti_search.php' ) echo '<option value="'.url('qti_index.php').'">'.SLang::translate().'</option>';
  if ( $oH->selfurl!=='qti_search.php' && SUser::canView('V4') ) echo '<option value="'.url('qti_search.php').'">'.L('Advanced_search').'</option>';
  echo sectionsAsOption(-1,[],[],'',32,100,url('qti_items.php').'?s='); // current section is not rejected (allow returning to page 1 or top page)
  echo '</select>';
}
echo '</p>
</div>
';

// END MAIN
echo '
</main>
';

// ------
// ASIDE INFO & LEGEND
// ------
if ( $_SESSION[QT]['board_offline']!=='1' ) {
if ( $_SESSION[QT]['show_legend']==='1' ) {
if ( in_array($oH->selfurl,array('index.php','qti_index.php','qti_items.php','qti_calendars.php','qti_item.php')) ) {

// Using stats ($_SectionsStats)
$stats = isset($_SectionsStats) ? $_SectionsStats : SMem::get('_SectionsStats');
$strStatusText = '';

echo '<aside>'.PHP_EOL;
echo '<a id="aside-ctrl" class="tgl-ctrl" onclick="toggleAside(); return false;" title="'.L('Showhide_legend').'" role="switch" aria-checked="false">'.qtSVG('info').qtSVG('angle-down','','',true).qtSVG('angle-up','','',true).'</a>'.PHP_EOL;
echo '<div id="aside__info" class="article" style="display:none">'.PHP_EOL;
  echo '<h2>'.L('Information').'</h2>'.PHP_EOL;
  // section info
  echo '<p>';
  if ( isset($oS) && is_a($oS,'CSection') && $oS->id>=0 )
  {
    $strStatusText = SLang::translate('sec', 's'.$s, $oS->title).': ';
    $intTopics = empty($stats[$s]['items']) ? 0 : $stats[$s]['items'];
    $intReplies = empty($stats[$s]['replies']) ? 0 : $stats[$s]['replies'];
    echo $strStatusText.'<br>';
    echo '&nbsp; '.L('item',$intTopics).', '.L('reply',$intReplies).'<br>';
    if ( !$_SESSION[QT]['show_closed'] ) {
      $intTopicsZ = empty($stats[$s]['itemsZ']) ? 0 : $stats[$s]['itemsZ'];
      $intRepliesZ = empty($stats[$s]['repliesZ']) ? 0 : $stats[$s]['repliesZ'];
      echo '&nbsp; '.L('closed_item',$intTopicsZ).'<br>';
    }
    $strStatusText .= L('item',$intTopics).(empty($intTopicsZ) ? '' : ' ('.L('closed_item',$intTopicsZ).')').', '.L('reply',$stats[$s]['replies']);
  }
  echo '</p>';
  // application info
  $intTopics = empty($stats['all']['items']) ? 0 : $stats['all']['items'];
  $intReplies = empty($stats['all']['replies']) ? 0 : $stats['all']['replies'];
  echo '<p>';
  echo L('Total').' '.SLang::translate(),':<br>';
  if ( isset($stats['all']) ) echo '&nbsp; ',L('item', $intTopics),', ',L('reply',$intReplies);
  if ( empty($strStatusText) ) $strStatusText = L('Total').' '.SLang::translate().': '.L('item',$intTopics).', '.L('reply',$intReplies);
  echo '</p>';
  // new user info (from memcache)
  $newuser = SMem::get('_NewUser');
  if ( $newuser!==false && !empty($newuser['id']) && !empty($newuser['firstdate']) && !empty($newuser['name']) ) {
    if ( addDate($newuser['firstdate'],30,'day')>Date('Ymd') ) {
      echo '<p>'.L('Welcome_to').'<a href="'.url('qti_user.php?id='.$newuser['id']).'">'.$newuser['name'].'</a></p>';
    }
  }

  echo '</div>'.PHP_EOL;

echo '<div id="aside__detail" class="secondary" style="display:none">'.PHP_EOL;
if ( isset($strDetailLegend) ) echo '<h2>'.L('Details').'</h2>'.PHP_EOL.$strDetailLegend.PHP_EOL;
echo '</div>'.PHP_EOL;

echo '<div id="aside__legend" style="display:none">'.PHP_EOL;
echo '<h2>'.L('Legend').'</h2>'.PHP_EOL;

if ( $oH->selfurl==='qti_index.php' )
{
  echo '<p>'.asImg( QT_SKIN.'img/section_0_0.gif', 'title='.L('Ico_section_0_0') ) . ' ' . L('Ico_section_0_0') . '</p>';
  echo '<p>'.asImg( QT_SKIN.'img/section_2_0.gif', 'title='.L('Ico_section_2_0') ) . ' ' . L('Ico_section_2_0') . '</p>';
  echo '<p>'.asImg( QT_SKIN.'img/section_0_1.gif', 'title='.L('Ico_section_0_1') ) . ' ' . L('Ico_section_0_1') . '</p>';
}
else
{
  echo '<div id="aside__legend__list">';
  echo '<p>'.asImg( QT_SKIN.'img/topic_a_0.gif', 'alt=N|class=i-item' ).' '.L('Ico_item_a').'</p>';
  if ( QT_LIST_ME && $oH->selfurl!=='qti_item.php' ) echo '<p><svg class="svg-symbol symbol-ireplied"><use href="#symbol-ireplied" xlink:href="#symbol-ireplied"/></svg>'.' '.L('You_reply').'</p>';
  echo '<p>'.asImg( QT_SKIN.'img/topic_i_0.gif', 'alt=I|class=i-item' ).' '.L('Ico_item_i').'</p>';
  foreach(CTopic::getStatuses() as $k=>$arrValue)
    echo '<p>'.asImg( QT_SKIN.'img/'.$arrValue['icon'], 'alt=T|class=i-item' ).' '.$arrValue['name'].'</p>';
  if ( $oH->selfurl==='qti_item.php' ) echo '<p><span title="'.L('Ico_post_r').'">'.qtSVG('comment-dots').'</span> '.L('Ico_post_r').'</p>';
  echo '</div>'.PHP_EOL;
}
echo '</div>'.PHP_EOL;
echo '<div id="aside__status">'.$strStatusText.'</div>'.PHP_EOL;
echo '</aside>'.PHP_EOL.PHP_EOL;

$oH->scripts[] = 'function toggleAside(){
  const d = document.getElementById("aside-ctrl"); if ( !d ) return;
  d.setAttribute("aria-checked", d.getAttribute("aria-checked")==="false" ? "true" : "false" );
  qtToggle("aside__status");
  qtToggle("aside__legend");
  qtToggle("aside__detail");
  qtToggle("aside__info","block","aside-ctrl");
  qtAttrStorage("aside-ctrl","qt-aside");
  d.blur();
}
qtApplyStoredState("aside");';

}}}

// END PAGE SITE
echo '
</div>
';

// ------
// FOOTER
// ------
echo '<footer class="flex-sp">
';

// MODULE RSS
if ( !$_SESSION[QT]['board_offline'] && qtModule('rss') && $_SESSION[QT]['m_rss']=='1' ) {
if ( SUser::role()!=='V' || SUser::role().substr($_SESSION[QT]['m_rss_conf'],0,1)==='VV' ) {
  $navMenu->add('rss', 'text='.qtSVG('rss-square').'|id=menu-rss|href=qtim_rss.php');
}}

// footer menu extra definition
$navMenu->separator = ' &middot; ';
if ( SUser::role()==='A' ) $navMenu->add('admin', '['.L('Administration').']|id=menu-admin|href=qti_adm_index.php|accesskey=a');
$skip = array_diff(array_keys($navMenu->menu), ['home','privacy','stats','rss','sign','admin']);
echo '<p id="footer-menu">'.$navMenu->build($oH->selfurl, 'tag=span|onclick=return false', $skip).'</p>'.PHP_EOL;
echo '<p id="footer-credit">powered by <a href="http://www.qt-cute.org">QT-cute</a> <span title="'.VERSION.' '.BUILD.'">v'.VERSION.'</span></p>
</footer>
';

// ------
// HTML END
// ------
if ( isset($oDB->stats) ) {
  if ( empty($oDB->stats['end']) ) $oDB->stats['end'] = gettimeofday(true);
  $oH->log[] = sprintf('%d queries. %d rows fetched in %01.4f sec.', $oDB->stats['num'], $oDB->stats['rows'], $oDB->stats['end'] - $oDB->stats['start']);
}


// Automatic add script {file.php.js} if existing
if ( file_exists($oH->selfurl.'.js') ) $oH->scripts[] = '<script type="text/javascript" src="'.$oH->selfurl.'.js"></script>';

$oH->end();