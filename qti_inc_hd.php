<?php // v4.0 build:20230430

/**
 * @var bool $hideMenuLang
 * @var bool $bMyBoard
 * @var CHtml $oH
 * @var CSection $oS
 * @var CTopic $oT
 * @var string $q
 * @var int $s
 */

// Page log
if ( $_SESSION[QT]['board_offline'] ) $oH->log[] = 'Warning: the board is offline. Only administrators can perform actions.';

// Check banner
if ( !isset($_SESSION[QT]['show_banner']) ) $_SESSION[QT]['show_banner'] = '0';

// Menus definition
$navMenu = new CMenu();
if ( $_SESSION[QT]['home_menu']==='1' && !empty($_SESSION[QT]['home_url']) )
$navMenu->add('home', 'text='.qtAttr($_SESSION[QT]['home_name']).'|href='.$_SESSION[QT]['home_url']);
$navMenu->add('privacy', 'text='.L('Legal').'|href=qti_privacy.php');
$navMenu->add('index', 'text='. SLang::translate().'|href=qti_index.php|accesskey=i|class=secondary|activewith=qti_index.php qti_items.php qti_item.php qti_calendars.php qti_edit.php');
$navMenu->add('search', 'text='.L('Search').'|id=nav-search|activewith=qti_search.php');
if ( $oH->selfurl!=='qti_search.php' && SUser::canAccess('search') )
$navMenu->menu['search'] .= '|href=qti_search.php|accesskey=s'.(QT_SIMPLESEARCH ? '|onclick=if ( document.getElementById(`searchbar`).style.display===`flex`) return; qtToggle(`searchbar`,`flex`); qtFocusAfter(`qkw`); return false;' : '');
  // SUser::canAccess('search') not included here... We want the searchbar/page shows a message for not granted users
if ( SUser::canAccess('show_memberlist') )
$navMenu->add('users', 'text='.L('Memberlist').'|href=qti_users.php');
if ( SUser::canAccess('show_stats') )
$navMenu->add('stats', 'text='.L('Statistics').'|href=qti_stats.php');
if ( SUser::auth() ) {
$navMenu->add('profile', 'text='.L('Profile').'|href=qti_user.php?id='.SUser::id().'|class=secondary|activewith=qti_user.php qti_register.php');
$navMenu->add('sign', 'text='.L('Logout').'|href=qti_login.php?a=out|class=nav-sign');
} else {
$navMenu->add('profile', 'text='.L('Register').'|href=qti_register.php?a=rules|class=secondary');
$navMenu->add('sign', 'text='.L('Login').'|href=qti_login.php|class=nav-sign');
}
// Menu when board offline or urlrewrite
if ( $_SESSION[QT]['board_offline'] && SUser::role()!=='A' ) {
  $navMenu->update('search','href', '');
  $navMenu->update('search','onclick', '');
  $navMenu->update('profile','href', '');
}
if ( QT_URLREWRITE ) {
  foreach(array_keys($navMenu->menu) as $k) {
    $navMenu->update( $k, 'href', url($navMenu->get($k,'href')) );
    $navMenu->update( $k, 'activewith', implode(' ',array_map('url',explode(' ',$navMenu->get($k,'activewith')))) );
  }
}

if ( !isset($hideMenuLang) ) $hideMenuLang = false;
if ( defined('HIDE_MENU_LANG') && HIDE_MENU_LANG ) $hideMenuLang = true;

// menu-lang (user,lang,contrast)
if ( !$hideMenuLang ) {
  $langMenu = new CMenu();
  // user
  $langMenu->add( '!'.qtSVG('user-'.SUser::role(), 'title='.L('Role_'.SUser::role())) );
  $langMenu->add( SUser::id()>0 ? 'text='.SUser::name().'|id=logname|href='.url(APP.'_user.php').'?id='.SUser::id() : 'text='.L('Role_V').'|tag=span|id=logname');
  // lang
  if ( $_SESSION[QT]['userlang'] ) {
    if ( is_array(LANGUAGES) && count(LANGUAGES)>1 ) {
      $langMenu->add( '!|' );
      foreach (LANGUAGES as $iso=>$language) {
        $arr = explode(' ',$language,2);
        $langMenu->add( 'text='.$arr[0].'|id=lang-'.$iso.'|href='.url($oH->selfurl).'?'.qtURI('lang').'&lang='.$iso.'|title='.(isset($arr[1]) ? $arr[1] : $arr[0]) );
      }
    } else {
      $langMenu->add('!missing file:config/config_lang.php');
    }
  }
  // contrast
  if ( QT_MENU_CONTRAST ) {
    $langMenu->add( 'text='.qtSVG('adjust').'|href=javascript:void(0)|id=contrast-ctrl|aria-label=High contrast|title=High contrast display|role=switch|aria-checked=false' );
    $oH->links['cssContrast'] = '<link id="contrastcss" rel="stylesheet" type="text/css" href="bin/css/contrast.css" disabled/>';
    $oH->scripts[] = "document.getElementById('contrast-ctrl').addEventListener('click', tglContrast);
      qtApplyStoredState('contrast');
    function tglContrast(){
      this.setAttribute('aria-checked', this.getAttribute('aria-checked')==='true' ? 'false' : 'true');
      qtAttrStorage('contrast-ctrl','qt-contrast');
      qtApplyStoredState('contrast');
    }";
  }
}

// --------
// HTML BEGIN
// --------

$oH->title = (empty($oH->selfname) ? '' : $oH->selfname.' - ').$oH->title;
$oH->head();
$oH->body();

CHtml::getPage('id=site|'.($_SESSION[QT]['viewmode']==='C' ? 'class=compact' : ''));

// ----------
// HEADER shows BANNER LANG-MENU NAV
// ----------
// header layout
echo '<header id="banner" data-layout="'.$_SESSION[QT]['show_banner'].'">'.PHP_EOL; // css data-layout (0=no-banner|1=nav-after|2=nav-inside)
// logo
if ( $_SESSION[QT]['show_banner']!=='0' ) echo '<div id="logo"><img src="'.QT_SKIN.'img/'.APP.'_logo.gif" alt="'.qtAttr($_SESSION[QT]['site_name'],24).'" title="'.qtAttr($_SESSION[QT]['site_name']).'"/></div>'.PHP_EOL;
// menu-lang (user,lang,contrast)
if ( !$hideMenuLang ) echo '<div id="menulang">'.$langMenu->build('lang-'.QT_LANG, 'tag=span|class=active').'</div>'.PHP_EOL;
// header nav (intersect to use only some head menus)
$skip = array_diff(array_keys($navMenu->menu), ['home','index','search','users','profile','sign']);
echo '<nav>'.$navMenu->build(url($oH->selfurl), 'default', $skip).'</nav>'.PHP_EOL;
echo '</header>'.PHP_EOL;

// ----------
// SEARCH BAR
// ----------
if ( QT_SIMPLESEARCH && $oH->selfurl!==APP.'_search.php' ) {
  echo '<div id="searchbar" style="display:none">'.PHP_EOL;
  if ( !SUser::canAccess('search') ) {
    echo L('E_11');
  } else {
    echo '<a href="'.url(APP.'_search.php').'">'.L('Advanced_search').'...</a>';
    echo asImg( QT_SKIN.'img/topic_t_0.gif', 'alt=T|class=img|title='.L('Recent_items'), url(APP.'_items.php').'?q=last' );
    echo asImg( QT_SKIN.'img/topic_a_0.gif', 'alt=T|class=img|title='.L('All_news'), url(APP.'_items.php').'?q=news' );
    echo asImg( QT_SKIN.'img/topic_i_0.gif', 'alt=T|class=img|title='.L('Inspections'), url(APP.'_items.php').'?q=insp' );
    if ( SUser::role()!=='V' ) echo '<a href="'.url(APP.'_items.php').'?q=user&v2='.SUser::id().'&v='.urlencode(SUser::name()).'" title="'.L('All_my_items').'">'.qtSVG('user').'</a>';
    echo '<form method="post" action="'.url(APP.'_search.php').'" style="display:inline">';
    echo '<button id="searchSubmit" type="submit" style="display:none" name="ok" value="'.makeFormCertificate('65699386509abf064aec83e5124c1f30').'">ok</button>';
    echo '<input type="hidden" name="q" value="qkw">';
    echo '<div id="ac-wrapper-qkw" class="ac-wrapper"><input required id="qkw" name="v" type="text" size="25" placeholder="'.L('Number_or_keyword').'" autocomplete="off" /></div> <a class="btn-search" href="javascript:void(0)" title="'.L('Search').' '.L('in_all_sections').'" onclick="document.getElementById(`searchSubmit`).click();">'.qtSVG('search').'</a>';
    echo '</form>';
    $oH->scripts['ac'] = '<script type="text/javascript" src="bin/js/qt_ac.js"></script><script type="text/javascript" src="bin/js/'.APP.'_config_ac.js"></script>';
    $oH->scripts[] = 'acOnClicks["qkw"] = function(focusInput,btn){ if ( focusInput.id=="qkw" && focusInput.value.substring(0,1)==="#" ) window.location="'.APP.'_item.php?t="+focusInput.value.substring(1); }';
  }
  echo '<a class="button button-x" href="javascript:void(0)" onclick="qtToggle(`searchbar`);" title="'.L('Close').'">'.qtSVG('times').'</a>'.PHP_EOL;
  echo '</div>'.PHP_EOL;
}

// ----------
// WELCOME
// ----------
$showWelcome = false;
if ( ( $_SESSION[QT]['show_welcome']=='2' || ($_SESSION[QT]['show_welcome']==='1' && !SUser::auth()) ) && file_exists(translate('app_welcome.txt')) && !$_SESSION[QT]['board_offline'] ) $showWelcome = true;
if ( $showWelcome && $oH->selfurl!==APP.'_register.php' ) {
  echo '<div id="intro">';
  include translate('app_welcome.txt');
  echo '</div>';
}

// ----------
// MAIN
// ----------
echo '
<main>
';

echo '
<div id="main-hd">
<p id="crumbtrail"><a href="'.url('qti_index.php').'"'.($oH->selfurl=='qti_index.php' ? ' onclick="return false;"' : ''),'>',SLang::translate().'</a>';
if ( isset($oS) && $oS->id>=0 ) { // $oS->id=-1 in case of 'void'-section
  if ( QT_SHOW_DOMAIN ) echo QT_CRUMBTRAIL.CDomain::translate($oS->pid);
  echo QT_CRUMBTRAIL.'<a href="'.url('qti_items.php').'?s='.$s.'">'.CSection::translate($s).'</a>';
  if ( $oS->type==='2' && !SUser::isStaff() ) echo QT_CRUMBTRAIL.'<small>'.L('all_my_items').'</small>';
}
if ( $oH->selfurl==='qti_user.php') echo QT_CRUMBTRAIL.L('Profile');
echo '</p>
<p id="page-ui">
';

switch($oH->selfurl)
{
case 'qti_item.php':
  $strURI = qtURI('view');
  if ( $_SESSION[QT]['viewmode']=='C' ) {
    echo '<a id="viewmode" href="'.url($oH->selfurl).'?'.$strURI.'&view=N" title="'.L('View_n').'">'.qtSVG('window-maximize').' '.qtSVG('long-arrow-alt-down').'</a>';
  } else {
    echo '<a id="viewmode" href="'.url($oH->selfurl).'?'.$strURI.'&view=C" title="'.L('View_c').'">'.qtSVG('window-maximize').' '.qtSVG('long-arrow-alt-up').'</a>';
  }
  break;
case 'qti_items.php':
  $bodyctId = 's'.($s>=0 ? $s : 'null'); if ( !empty($q) ) $bodyctId = 'q-'.$q;
  if ( !empty($oH->items) ) {
    if ( !isset($q) ) $q = 's';
    $strCrumbtrail = $q!=='s' ? ''.qtSVG('search').' ' : '';
    $strCrumbtrail .= L( in_array($q,['qkw','kw','userm']) ? 'message' : 'item', $oH->items);
    if ( !empty($oH->itemsHidden) ) $strCrumbtrail .= ' ('.L('hidden',$oH->itemsHidden).')';
    echo '<span id="crumbtrail-info">'.$strCrumbtrail.'</span>';
  }
  if ( SUser::canAccess('show_calendar') && $q==='s' ) {
    echo ' <a href="'.url('qti_calendars.php').'?s='.$s.'"><span title="'.L('View_f_c').'">'.qtSVG('calendar').'</span></a>';
  }
  break;
case 'qti_calendars.php':
  if ( SUser::canAccess('show_calendar') ) {
  echo '<a href="'.url('qti_items.php').'?s='.$s.'"><span title="'.L('View_f_n').'">'.qtSVG('th-list').'</span></a>';
  }
  break;
case 'qti_stats.php':
  $strURI = qtURI('view');
  break;
case 'qti_users.php':
  if ( empty($_SESSION[QT]['formatpicture']) ) $_SESSION[QT]['formatpicture']='mime=0;width=100;height=100';
  if ( !empty(qtExplodeGet($_SESSION[QT]['formatpicture'], 'mime')) ) {
    if ( $_SESSION[QT]['viewmode']=='C' ) {
      echo '<a id="viewmode" href="'.url($oH->selfurl).'?view=N" title="'.L('View_n').'">'.qtSVG('window-maximize').' '.qtSVG('long-arrow-alt-down').'</a>';
    } else {
      echo '<a id="viewmode" href="'.url($oH->selfurl).'?view=C" title="'.L('View_c').'">'.qtSVG('window-maximize').' '.qtSVG('long-arrow-alt-up').'</a>';
    }
  }
  break;
}

echo '</p>
</div>
';

// index's MY BOARD
if ( $oH->selfurl==='qti_index.php' && !empty($bMyBoard) ) include 'qti_inc_myboard.php';

// MAIN CONTENT / $oS->id=-1 in case of 'void'-section
$str =  isset($oS) && $oS->id>=0 ? ' data-section-type="'.$oS->type.'" data-section-status="'.$oS->status.'"' : '';
echo '<div id="main-ct" class="pg-'.qtBasename($oH->selfurl).'"'.$str.'>
';
if ( !empty($error) ) echo '<p class="error center">'.$error.'</p>';
if ( isset($bodyctId) ) echo '<div id="pg-'.$bodyctId.'"></div>'.PHP_EOL;