<?php
// v4.0 build:20230618
// WARNING: requires config/config_db.php
// WARNING: requires php 5.6.x or next (uses scalar expression const)

// -----------------
// System constants (CANNOT be changed by webmasters)
// -----------------
const APP = 'qti'; // application file prefix
const APPNAME = 'QuickTicket';
define('QT', APP.(defined('QDB_INSTALL') ? substr(QDB_INSTALL,-1) : '')); // memory namespace "qti{n}"
const VERSION = '4.0';
const BUILD = 'build:20230618';
const TABDOMAIN = QDB_PREFIX.'qtidomain';
const TABSECTION = QDB_PREFIX.'qtisection';
const TABUSER = QDB_PREFIX.'qtiuser';
const TABTOPIC = QDB_PREFIX.'qtitopic';
const TABPOST = QDB_PREFIX.'qtipost';
const TABSTATUS = QDB_PREFIX.'qtistatus';
const TABSETTING = QDB_PREFIX.'qtisetting';
const TABLANG = QDB_PREFIX.'qtilang';
const TABTABLES = ['TABTOPIC','TABPOST','TABSECTION','TABUSER','TABLANG','TABSETTING','TABDOMAIN','TABSTATUS'];
const QSEPARATOR = ';'; // Values separator in search queries (used as jQuery autocomplete-ajax separator and qti_tags.js) CANNOT BE EMPTY!
// About the memory namespace (session variablenames starting with constant QT):
// By default QT = "qti{n}" where n is the last character of QDB_INSTALL value (in the configuration file config_db.php)
// If you run 2 applications on the same server, each application requires a unique namespace:
// check that QDB_INSTALL values are different in each config_db.php files (ex: qti1 and qti2)
const BAN_DAYS = [0,1,7,15,30,90,365]; // Index 0..6 correspond to ban duration between 0 and 365 days

// -----------------
// Interface constants (can be changed by webmasters)
// -----------------
const QT_COLOR_SCHEME = 'light dark'; // meta color-schemes for the browser
const QT_LOGIN_WITH_EMAIL = true; // allow login with email (false to use only username)
const QT_MENU_CONTRAST = true; // allow user to change css mode: contrast/normal
const QT_FLOOD = 5; // Prevent double-post of message by a user (delay in seconds)
const QT_BBC = true; // allow bbc tag in text message
const QT_DIR_PIC = 'avatar/'; // Storage location for uploaded userphoto, if allowed (with final '/')
const QT_DIR_DOC = 'upload/'; // Storage location for uploaded files attachement, if allowed (with final '/')
const QT_CRUMBTRAIL = '&#8201;&middot;&#8201;'; // crumbtrail separator
const QT_DELIMITER = ';'; // Character delimiter for multiple input values (ex: tags)
const QT_UPLOAD_MAXSIZE = 8; // Maximum attachement size in Mb (8 recommended). Severs have several limits (upload_max_filesize, post_max_size and memory_limits). Some providers/web-hoster limit upload at 8Mb.
const QT_DFLT_VIEWMODE = 'n'; // default view mode: n=normal view, c=compact view
const QT_SHOW_VIEWMODE = true; // allow user changing view mode
const QT_SHOW_PARENT_DESCR = true; // Show section description or ticket reference as content title
const QT_SHOW_MEMBERLIST = true; // show memberlist in the menu
const QT_SHOW_MODERATOR = true; // show moderator in the bottom bar
const QT_FIRSTLINE_SIZE = 64; // Message first line (size) in the list of topics
const QT_SHOW_JUMPTO = true; // show selection-list in the bottom bar
const QT_SHOW_DOMAIN = false; // show domain + section name in the crumbtrail bar
const QT_SHOW_NEWSSTAMP = false; // Add 'News' before a news title (or translation). Note: stamp is not be displayed when screen is too small
const QT_NOTIFY_NEWACTOR = true; // notify new actor when topic actor changes (this option is applicable only in sections having notification activated!)
const QT_NOTIFY_OLDACTOR = true; // notify old actor when topic actor changes (this option is applicable only in sections having notification activated!)
const QT_CONVERT_AMP = false; // In message and title, saves & instead of &. Use TRUE to make &#0000; symbols NOT working.
const QT_SIMPLESEARCH = true; // Shows simple search popup (false goes directly to advanced search)
const QT_LIST_ME = true; // In the ticket list, symbol indicating: i replied to the ticket. Using False will DISABLE the search and the symbol.
const QT_LIST_TAG = true; // display a quick search link for the tags in section list.
const QT_JAVA_MAIL = false; // Protect e-mail by a javascript
const QT_WEEKSTART = 1; // Start of the week (use code 1=monday,...,7=sunday)
const QT_STAFFEDITSTAFF = true; // Staff member can edit posts issued by an other staff member
const QT_STAFFEDITADMIN = true; // Staff member can edit posts issued by an administrator
const QT_STAFFEDITPROFILES = true; // Staff members can edit user/staff profiles (not admin profile)
const QT_CHANGE_USERNAME = true; // User can change username (if false, only admin can change usernames)
const QT_SECTIONLOGO_SIZE = 2; // Maximum size of section logo (MB). Used in picture upload cropping tool
const QT_SECTIONLOGO_WIDTH = 100; // Maximum size of section logo (pixels). Used in picture upload cropping tool
const QT_SECTIONLOGO_HEIGHT = 100; // Maximum size of section logo (pixels). Used in picture upload cropping tool
const QT_REMEMBER_ME = true; // Allows "remember me" on login page (i.e. coockie login + confirmation message)
const QT_URLREWRITE = false;
// URL rewriting (for expert only):
// Rewriting url requires that your server is configured with following rule for the application folder: RewriteRule ^(.+)\.html(.*) qti_$1.php$2 [L]
// This can NOT be activated if you application folder contains html pages (they will not be accessible anymore when urlrewriting is acticated)

// -----------------
// MEMCACHE (this can be changed by webmaster)
// -----------------
const MEMCACHE_HOST = 'localhost'; // Memcache allows storing frequently used values in memcache server (instead of runnning sql requests)
const MEMCACHE_PORT = 11211; // memcache port (integer). Default port is 11211.
const MEMCACHE_TIMEOUT = 9999; // default memcache timeout in seconds (0=no timeout)
// If memcache is not available on your server use false as host. Ex: const MEMCACHE_HOST = false;
// otherwise define your host name. Ex: const MEMCACHE_HOST = 'localhost';

// -----------------
// OTHER
// -----------------
if ( !defined('PHP_VERSION_ID') ) { $arr=explode('.',PHP_VERSION); define('PHP_VERSION_ID',($arr[0]*10000+$arr[1]*100+$arr[2])); }