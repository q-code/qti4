<?php // v4 build:20240210
error_reporting(E_ALL);//!!!

// Connection config and Constants
require 'config/config_db.php';
include 'config/config_lang.php'; if ( !defined('LANGUAGES') ) define('LANGUAGES',array('en'=>'EN English'));
require 'config/config_cst.php';

// ------
// Class and functions
// ------
require 'bin/lib_qt_core.php';
require 'bin/class/class.qt.db.php';
require 'bin/class/class.qt.core.php';
require 'bin/class/class.qt.html.php';
require 'bin/class/class.qt.table.php';
require 'bin/class/class.qt.menu.php';
require 'bin/class/class.qt.domain.php';
require 'bin/class_qti_status.php';
require 'bin/class_qti_user.php';
require 'bin/class_qti_section.php';
require 'bin/class_qti_topic.php';
require 'bin/class_qti_post.php';
require 'bin/class_qti_sse.php';
require 'bin/lib_qti_base.php';
require 'bin/lib_qti_html.php';

// ------
// Initialise Classes
// ------
$oH = new CHtml(); // $oH must be created before $oDB to allow using debug log
$oDB = new CDatabase();
SMem::create($oH->warning); // create memcache object (or null), namespace is QT by default (can also issue a $oH->warning message if connection failed)
$arrExtData = []; // Can be used by extensions

// Check settings AGE against session age
if ( !isset($_SESSION[QT.'settingsage']) ) $_SESSION[QT.'settingsage'] = time()-1;
// Read and register settings (if not yet red)
if ( !isset($_SESSION[QT]['version']) || SMem::get('settingsage')>$_SESSION[QT.'settingsage'] ) {
  $oH->log[] = 'registering new settings...'; //!!!
  unset($_SESSION[QT.'settingsage']);
  $oDB->getSettings('',true); // only settings are registered
  // IMPORTANT
  // SMem::get('settingsage') returns [int]time, [null]no connection or [false]not found
  // When memchache is disabled (or when session age is not found), settings are read once (session startup)
  // Admin pages put age in shared-memory when saving settings
}

// check major parameters
define( 'FORMATDATE', empty($_SESSION[QT]['formatdate']) ? 'j-M-Y' : $_SESSION[QT]['formatdate'] );
define( 'FORMATTIME', empty($_SESSION[QT]['formattime']) ? 'G:i' : $_SESSION[QT]['formattime'] );
if ( empty($_SESSION[QT]['language']) ) $_SESSION[QT]['language'] = 'en'; // default setting (fallback for language change)
if ( empty($_SESSION[QT]['skin_dir']) ) $_SESSION[QT]['skin_dir'] = 'skin/default/';
if ( substr($_SESSION[QT]['skin_dir'],0,5)!=='skin/' ) $_SESSION[QT]['skin_dir'] = 'skin/'.$_SESSION[QT]['skin_dir'];
if ( substr($_SESSION[QT]['skin_dir'],-1)!=='/' ) $_SESSION[QT]['skin_dir'] .= '/';  // final / is required (v4.0)

// User changes language
if ( isset($_GET['lang']) && array_key_exists($_GET['lang'],LANGUAGES)) {
  $_SESSION[QT.'isoUser'] = $_GET['lang'];
  if ( PHP_VERSION_ID<70300 ) { setcookie(QT.'_cooklang', $_GET['lang'], time()+3600*24*100, '/'); } else { setcookie(QT.'_cooklang', $_GET['lang'], ['expires'=>time()+3600*24*100,'path'=>'/','samesite'=>'Strict']); }
}
// Apply user language (from session or from coockies)
$isoUser = empty($_SESSION[QT.'isoUser']) ? '' : $_SESSION[QT.'isoUser'];
if ( empty($isoUser) && isset($_COOKIE[QT.'_cooklang']) ) $isoUser = $_COOKIE[QT.'_cooklang'];
if ( empty($isoUser) ) $isoUser =  $_SESSION[QT]['language']; // fallback

// Alias
define('QT_SKIN', $_SESSION[QT]['skin_dir']); // format: skin/themename/
define('QT_LANG', $isoUser); // format: iso-code

// ------
// Initialiase cache (domains, sections and title-translations)
// ------
if ( !isset($_SESSION[QT]['viewmode']) ) $_SESSION[QT]['viewmode'] = QT_DFLT_VIEWMODE;
if ( !isset($_SESSION[QT]['userlang']) ) $_SESSION[QT]['userlang'] = '1';
if ( !isset($_SESSION[QT]['show_welcome']) ) $_SESSION[QT]['show_welcome'] = '1'; // 1 = while unlogged
// Initialise list
// $_name means that the variable will be global, using $GLOBALS['_name'] in function or class
// Note: SMem::get() puts the data in the shared-memory if not existing, and returns the data
// When one changes, the class clears the shared-memory while following get() recomputes and stores it
$_L = SMem::get('_L'.QT_LANG); // includes types ['index','domain','sec','secdesc','status','statusdesc'], for each id (words translated to QT_LANG)
$_Domains = SMem::get('_Domains');
$_SectionsTitle = SMem::get('_SectionsTitle');
$_SectionsStats = SMem::get('_SectionsStats');
$_Sections = SMem::get('_Sections');
$_Statuses = SMem::get('_Statuses');

// ------
// Load dictionary
// ------
include translate('lg_main.php');
include translate('lg_icon.php');

// ------
// SSE constants
// ------
// load sse parametre as constants (with SSE_ prefix)
if ( empty($_SESSION[QT]['sse']) ) $_SESSION[QT]['sse'] = 'CONNECT=0;ORIGIN=http://localhost;MAXROWS=2;TIMEMOUT=30;LATENCY=10;SERVER=0';
foreach(qtExplode($_SESSION[QT]['sse']) as $key=>$value) if ( !defined('SSE_'.strtoupper($key)) ) define('SSE_'.strtoupper($key),$value);

// ------
// Default HTML settings
// ------
$oH->html = '<!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml" dir="'.(defined('QT_HTML_DIR') ? QT_HTML_DIR : 'ltr').'" xml:lang="'.(defined('QT_HTML_LANG') ? QT_HTML_LANG : 'en').'" lang="'.(defined('QT_HTML_LANG') ? QT_HTML_LANG : 'en').'">';
$oH->metas[0] = '<title>'.$_SESSION[QT]['site_name'].'</title>';
$oH->metas[] = '<meta charset="'.QT_HTML_CHAR.'"/>
<meta name="color-scheme" content="'.QT_COLOR_SCHEME.'"/>
<meta name="description" content="QT '.APPNAME.'"/>
<meta name="keywords" content="quickticket,trouble ticket,knowledge,qt-cute,OpenSource"/>
<meta name="author" content="qt-cute.org"/>';
$oH->links['ico'] = '<link rel="shortcut icon" href="'.QT_SKIN.'img/qt.ico"/>';
$oH->links['cssCore'] = '<link rel="stylesheet" type="text/css" href="bin/css/qt_core.css"/>';
$oH->links['css'] = '<link rel="stylesheet" type="text/css" href="'.QT_SKIN.'qti_styles.css"/>';
if ( file_exists(QT_SKIN.'custom.css') ) $oH->links['cssCustom'] = '<link rel="stylesheet" type="text/css" href="'.QT_SKIN.'custom.css"/>';
$oH->scripts_top['core'] = '<script type="text/javascript" src="bin/js/qt_core.js"></script>';
if ( defined('QT_URLCONST') && !empty(QT_URLCONST) )
$oH->scripts_top[] = '<script type="text/javascript" src="bin/js/qt_urlconst.js" data-url="'.QT_URLCONST.'"></script>';

// ------
//  Time setting (for PHP >=5.2)
// ------
if ( PHP_VERSION_ID>=50200 && isset($_SESSION[QT]['defaulttimezone']) && $_SESSION[QT]['defaulttimezone']!=='' ) date_default_timezone_set($_SESSION[QT]['defaulttimezone']);

// Admin system command
define('SECURE_QT_HASHKEY', QDB_PWD.QDB_INSTALL);
if ( isset($_GET['memflush']) && MEMCACHE_HOST ) {
  $deep = $_GET['memflush']==='**';
  unset($_GET['memflush']);
  $oH->log[] = SUser::role()==='A' ? 'Info: memcache cleared and rebuild' : 'Warning: only admin can perform memFlush';
  if ( SUser::role()==='A' ) {
    if ( $deep ) {  memFlush([],'**'); } else { memFlush([]); memFlushLang(); memFlushStats(isset($_GET['years']) ? explode(',',$_GET['years']) : 'default'); }
  }
}

// ------
// For user not authenticated, try cookie-login
// if config QT_REMEMBER_ME allows it and if this page is not the _login.php itself
// ------
if ( $oH->php!==APP.'_login.php' && QT_REMEMBER_ME && empty($bypassConfirmCookie) && SUser::confirmCookie($oDB) ){
  // This confirmation dialog is shown if cookie-login has been launched
  include APP.'_inc_hd.php';
  CHtml::msgBox(L('Login'), 'class=msgbox login');
  echo '<h2>'.L('Welcome').' '.SUser::name().'</h2><p><a href="'.url($oH->exiturl).'">'.L('Continue').'</a> &middot; <a href="'.url(APP.'_login.php?a=out&r=in').'">'.sprintf(L('Welcome_not'),SUser::name()).'</a></p>';
  CHtml::msgBox('/');
  include APP.'_inc_ft.php';
  exit;
}