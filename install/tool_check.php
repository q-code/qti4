<?php // V4.0 build:20230618

$root = '../';
define('THISAPPNAME', 'QuickTicket');

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" xml:lang="en" lang="en">

<head>
<title>'.THISAPPNAME.' installation checker</title>
<meta http-equiv="content-type" content="text/html; charset=iso-8859-1"/>
<style type="text/css">
div.page {margin:5px auto;width:700px}
#banner {color:inherit;background:linear-gradient(to bottom,#0C4C8C 20%,#156AC2)}
#logo {padding:5px}
div.body {padding:10px; border:solid 1px #156AC2}
h1,h2,p,a,select,input,textarea,td,th,fieldset,div {font-family:Verdana, Arial, sans-serif}
h1 {margin-top:10px; margin-bottom:5px; font-size:14pt; font-weight:bold}
h2 {margin-top:10px; margin-bottom:5px; font-size:12pt; font-weight:bold}
p,select,input,textarea,td,th,a,fieldset {font-size:9pt; text-decoration:none}
a {color:#0000FF}
a.visited {color:#0000FF}
a:hover {color:#0000FF; text-decoration:underline}
p.check {margin:5px 0 0 0; padding:0}
p.endcheck {margin:5px 0 0 0; padding:5px; border:solid 1px #aaaaaa}
span.ok {color:#00aa00; background-color:inherit}
span.nok {color:#ff0000; background-color:inherit}
</style>
</head>

<body>

<div class="page">

<div id="banner">
<img id="logo" src="'.$root.'bin/css/qti_logo.gif" width="175" height="50" style="border-width:0" alt="'.THISAPPNAME.'" title="'.THISAPPNAME.'"/>
</div>

<div class="body">
';

// --------
// 1 CONFIG
// --------

echo '<p style="text-align:right">'.THISAPPNAME.' 4.0 build:20230618</p>';
echo '<p style="text-align:right"><a href="qti_setup.php">Install...</a>';
if ( file_exists('tool_tables.php') ) echo ' | <a href="tool_tables.php">Tool tables...</a>';
echo '</p>';

echo '<h1>Checking your configuration</h1>';

$error = '';

// 1 file exist

  echo '<p class="check">Checking installed files... ';

  if ( !file_exists($root.'config/config_db.php') ) $error .= 'File <b>config_db.php</b> is not in the <b>config</b> directory. Communication with database is impossible.<br>';
  if ( !file_exists($root.'bin/init.php') ) $error .= 'File <b>init.php</b> is not in the <b>bin</b> directory. Application cannot start.<br>';
  if ( !file_exists($root.'bin/lib_qt_core.php') ) $error .= 'File <b>lib_qt_core.php</b> is not in the <b>bin</b> directory. Application cannot start.<br>';
  if ( !file_exists($root.'bin/lib_qti_base.php') ) $error .= 'File <b>lib_qti_base.php</b> is not in the <b>bin</b> directory. Application cannot start.<br>';
  if ( !file_exists($root.'bin/class/class.qt.core.php') ) $error .= 'File <b>class.qt.core.php</b> is not in the <b>bin/class</b> directory. Application cannot start.<br>';
  if ( !file_exists($root.'bin/class/class.qt.db.php') ) $error .= 'File <b>class.qt.db.php</b> is not in the <b>bin/class</b> directory. Application cannot start.<br>';
  if ( !file_exists($root.'bin/class_qti_section.php') ) $error .= 'File <b>class_qti_section.php</b> is not in the <b>bin/class</b> directory. Application cannot start.<br>';
  if ( !file_exists($root.'bin/class_qti_topic.php') ) $error .= 'File <b>class_qti_topic.php</b> is not in the <b>bin/class</b> directory. Application cannot start.<br>';
  if ( !file_exists($root.'bin/class_qti_post.php') ) $error .= 'File <b>class_qti_post.php</b> is not in the <b>bin/class</b> directory. Application cannot start.<br>';

  if ( empty($error) )
  {
  echo '<span class="ok">Main files found.</span></p>';
  }
  else
  {
  die('<span class="nok">'.$error.'</span></p>');
  }

// 2 config is correct

  echo '<p class="check">Checking config folder... ';

  include $root.'config/config_db.php'; $database = strpos(QDB_SYSTEM,'sqlite') ? $root.QDB_DATABASE : QDB_DATABASE; // using SQLite, database file is in the root directory
  include $root.'config/config_cst.php';
  include $root.'bin/lib_qti_base.php';

  if ( !defined('QDB_SYSTEM') )   $error .= 'Variable <b>QDB_SYSTEM</b> is not defined in the file <b>config/config_db.php</b>. Communication with database is impossible.<br>';
  if ( !defined('QDB_HOST') )     $error .= 'Variable <b>QDB_HOST</b> is not defined in the file <b>config/config_db.php</b>. Communication with database is impossible.<br>';
  if ( !defined('QDB_DATABASE') ) $error .= 'Variable <b>QDB_DATABASE</b> is not defined in the file <b>config/config_db.php</b>. Communication with database is impossible.<br>';
  if ( !defined('QDB_PREFIX') )   $error .= 'Variable <b>QDB_PREFIX</b> is not defined in the file <b>config/config_db.php</b>. Communication with database is impossible.<br>';
  if ( !defined('QDB_USER') )     $error .= 'Variable <b>QDB_USER</b> is not defined in the file <b>config/config_db.php</b>. Communication with database is impossible.<br>';
  if ( !defined('QDB_PWD') )      $error .= 'Variable <b>QDB_PWD</b> is not defined in the file <b>config/config_db.php</b>. Communication with database is impossible.<br>';

  if ( !empty($error) )  die('<span class="nok">'.$error.'</span>');

  // check db type
  if ( !in_array(QDB_SYSTEM,array('pdo.mysql','mysql','pdo.sqlsrv','sqlsrv','pdo.pg','pg','pdo.sqlite','sqlite','pdo.oci','oci')) ) die('Unknown db type '.QDB_SYSTEM);
  // check other values
  if ( empty(QDB_DATABASE) )  $error .= '<br>Variable <b>QDB_DATABASE</b> is not defined in the file <b>config/config_db.php</b>. Communication with database is impossible.';

  if ( empty($error) )
  {
  echo '<span class="ok">Done.</span></p>';
  }
  else
  {
  die('<span class="nok">'.$error.'</span></p>');
  }

// 3 test db connection

  echo '<p class="check">Connecting to database... ';

  include $root.'bin/class/class.qt.db.php';

  $oDB = new CDatabase(QDB_SYSTEM,QDB_HOST,$database,QDB_USER,QDB_PWD);

  if ( empty($oDB->error) )
  {
  echo '<span class="ok">Done.</span></p>';
  }
  else
  {
  die('<span class="nok">Connection with database failed.<br>Check that server is up and running.<br>Check that the settings in the file <b>config/config_db.php</b> are correct for your database.</span></p>');
  }

// end CONFIG tests

  echo '<p class="endcheck">Configuration tests completed successfully.</p>';

// --------
// 2 DATABASE
// --------

$error = '';

echo '
<h1>Checking your database design</h1>
';

// 1 setting table

  echo '<p class="check">Checking setting table... ';

  $oDB->query( 'SELECT setting FROM '.QDB_PREFIX.'qtisetting WHERE param="version"');
  if ( !empty($oDB->error) ) die('<span class="nok">Problem with table '.QDB_PREFIX.'qtisetting</span>');
  $row = $oDB->getRow();
  $strVersion = $row['setting'];

  echo '<span class="ok">Table [',QDB_PREFIX,'qtisetting] exists. Version is ',$strVersion,'.</span>';
  if ( !in_array(substr($strVersion,0,3),array('3.0','3.1','4.0')) ) die('<span class="nok">But data in this table refers to an incompatible version (must be version 3.0).</span></p>');
  echo '</p>';

// 2 domain table

  echo '<p class="check">Checking domain table... ';
  $intCount = $oDB->count( QDB_PREFIX.'qtidomain' );
  echo '<span class="ok">Table [',QDB_PREFIX,'qtidomain] exists. ',$intCount,' domain(s) found.</span></p>';

// 3 team table

  echo '<p class="check">Checking forum table...';
  $intCount = $oDB->count( QDB_PREFIX.'qtisection' );
  echo '<span class="ok">Table [',QDB_PREFIX,'qtisection] exists. ',$intCount,' section(s) found.</span></p>';

// 4 topic table

  echo '<p class="check">Checking topic table...';
  $intCount = $oDB->count( QDB_PREFIX.'qtitopic' );
  echo '<span class="ok">Table [',QDB_PREFIX,'qtitopic] exists. ',$intCount,' topic(s) found.</span></p>';

// 5 post table

  echo '<p class="check">Checking post table...';
  $intCount = $oDB->count( QDB_PREFIX.'qtipost');
  echo '<span class="ok">Table [',QDB_PREFIX,'qtipost] exists. ',$intCount,' post(s) found.</span></p>';

// 6 user table

  echo '<p class="check">Checking user table... ';
  $intCount = $oDB->count( QDB_PREFIX.'qtiuser' );
  echo '<span class="ok">Table [',QDB_PREFIX,'qtiuser] exists. ',$intCount,' user(s) found.</span></p>';

// end DATABASE tests

  echo '<p class="endcheck">Database tests completed successfully.</p>';

// --------
// 3 LANGUAGE AND SKIN
// --------

$error = '';

echo '
<h1>Checking language and skin options</h1>
';

  echo '<p class="check">Files... ';

  $oDB->query( 'SELECT setting FROM '.QDB_PREFIX.'qtisetting WHERE param="language"');
  $row = $oDB->getRow();
  $str = $row['setting'];
  if ( empty($str) ) $error .= 'Setting <b>language</b> is not defined in the setting table. Application can only work with english.<br>';
  if ( !file_exists($root."language/$str/lg_main.php") ) $error .= "File <b>lg_main.php</b> is not in the <b>language/xx</b> directory.<br>";
  if ( !file_exists($root."language/$str/lg_adm.php") )  $error .= "File <b>lg_adm.php</b> is not in the <b>language/xx</b> directory.<br>";
  if ( !file_exists($root."language/$str/lg_icon.php") ) $error .= "File <b>lg_icon.php</b> is not in the <b>language/xx</b> directory.<br>";
  if ( !file_exists($root."language/$str/lg_reg.php") )  $error .= "File <b>lg_reg.php</b> is not in the <b>language/xx</b> directory.<br>";
  if ( !file_exists($root."language/$str/lg_zone.php") ) $error .= "File <b>lg_zone.php</b> is not in the <b>language/xx</b> directory.<br>";
  if ( $str!='english' )
  {
  if ( !file_exists($root."language/en/lg_main.php") ) $error .= "File <b>lg_main.php</b> is not in the <b>language/en</b> directory. English language is mandatory.<br>";
  if ( !file_exists($root."language/en/lg_adm.php") )  $error .= "File <b>lg_adm.php</b> is not in the <b>language/en</b> directory. English language is mandatory.<br>";
  if ( !file_exists($root."language/en/lg_icon.php") ) $error .= "File <b>lg_icon.php</b> is not in the <b>language/en</b> directory. English language is mandatory.<br>";
  if ( !file_exists($root."language/en/lg_reg.php") )  $error .= "File <b>lg_reg.php</b> is not in the <b>language/en</b> directory. English language is mandatory.<br>";
  if ( !file_exists($root."language/en/lg_zone.php") ) $error .= "File <b>lg_zone.php</b> is not in the <b>language/en</b> directory. English language is mandatory.<br>";
  }

  $oDB->query( 'SELECT setting FROM '.QDB_PREFIX.'qtisetting WHERE param="skin_dir"');
  $row = $oDB->getRow();
  $str = $row['setting']; if ( substr($str,0,5)!=='skin/' ) $str = 'skin/'.$str;

  if ( empty($str) ) $error .= 'Setting <b>skin</b> is not defined in the setting table. Application will not display correctly.<br>';
  if ( !file_exists($root."$str/qti_styles.css") ) $error .= "File <b>qti_styles.css</b> is not in the <b>$str</b> directory.<br>";
  if ( !file_exists($root."skin/default/qti_styles.css") ) $error .= 'File <b>qti_styles.css</b> is not in the <b>skin/default</b> directory. Default skin is mandatory.<br>';

  if ( empty($error) )
  {
  echo '<span class="ok">Ok.</span>';
  }
  else
  {
  echo '<span class="nok">',$error,'</span>';
  }

  echo '</p>';

// end LANGUAGE AND SKIN tests

  echo '<p class="endcheck">Language and skin files tested.</p>';

// --------
// 4 ADMINISTRATION TIPS
// --------

$error = '';

echo '
<h1>Administration tips</h1>
';

// 1 admin email

  echo '<p class="check">Email setting... ';

  $oDB->query( 'SELECT setting FROM '.QDB_PREFIX.'qtisetting WHERE param="admin_email"');
  $row = $oDB->getRow();
  $strMail = $row['setting'];
  if ( empty($strMail) )
  {
  $error .= 'Administrator e-mail is not yet defined. It\'s mandatory to define it!';
  }
  else
  {
  if ( !preg_match("/^[A-Z0-9._%-]+@[A-Z0-9][A-Z0-9.-]{0,61}[A-Z0-9]\.[A-Z]{2,6}$/i",$strMail) ) $error .= 'Administrator e-mail format seams incorrect. Please check it';
  }
  if ( empty($error) ) {
    echo '<span class="ok">Done.</span></p>';
  } else {
    echo '<span class="nok">'.$error.'</span></p>';
  }
  $error = '';

// 2 admin password
  echo '<p class="tool_check">Security check... <span class="ok">Done.</span><br>';

  $oDB->query( 'SELECT pwd FROM '.QDB_PREFIX.'qtiuser WHERE id=1');
  $row = $oDB->getRow();
  $strPwd = $row['pwd'];
  if ( $strPwd==sha1('Admin') ) echo '<span class="nok">Administrator password is still the initial password. It\'s recommended to change it</span><br>';
  if ( is_dir($root.'install') ) echo '<span class="nok">Install folder must be encrypted or removed to prevent other installation</span><br>';
  echo '</p>';

// 3 site url

  echo '<p class="check">Site url... ';

  $oDB->query( 'SELECT setting FROM '.QDB_PREFIX.'qtisetting WHERE param="site_url"');
  $row = $oDB->getRow();
  $strText = trim($row['setting']);
  if ( substr($strText,0,7)!=='http://' && substr($strText,0,8)!=='https://' )
  {
    $error .= 'Site url is not yet defined (or not starting by http://). It\'s mandatory to define it !<br>';
  }
  else
  {
    $strURL = ( empty($_SERVER['SERVER_HTTPS']) ? 'http://' : 'https://' ).$_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
    $strURL = substr($strURL,0,-10);
    if ( $strURL!=$strText ) $error .= 'Site url seams to be different that the current url. Please check it<br>';
  }

  if ( empty($error) )
  {
  echo '<span class="ok">Done.</span></p>';
  }
  else
  {
  echo '<span class="nok">',$error,'</span></p>';
  }
  $error = '';

// 4 avatar folder permission

  echo '<p class="check">Folder permissions... ';

  if ( !is_dir($root.'avatar') )
  {
    $error .= 'Directory <b>avatar</b> not found.<br>Please create this directory and make it writeable (chmod 777) if you want to allow avatars.<br>';
  }
  else
  {
    if ( !is_readable($root.'avatar') ) $error .= 'Directory <b>avatar</b> is not readable.</font><br>Change permissions (chmod 777) if you want to allow avatars.<br>';
    if ( !is_writable($root.'avatar') ) $error .= 'Directory <b>avatar</b> is not writable.</font><br>Change permissions (chmod 777) if you want to allow avatars.<br>';
  }

  if ( !empty($error) ) echo '<span class="nok">',$error,'</span></p>';
  echo '<span class="ok">Done.</span></p>';
  $error = '';

echo '<p class="endcheck">Administration tips completed.</p>';

// --------
// 5 END
// --------

echo '
<h1>Result</h1>
<p class="check">The checker did not found blocking issues in your configuration.<br>';

  $oDB->query( 'SELECT setting FROM '.QDB_PREFIX.'qtisetting WHERE param="board_offline"');
  $row = $oDB->getRow();
  $strOff = $row['setting'];
  if ( $strOff=='1' ) echo 'Your board seams well installed, but is currently <font color="red">off-line</font>.<br>Log as Administrator and go to the Administration panel to turn your board on-line.<br>';

  echo '</p><p>';
  if ( is_dir($root.'install') ) echo '<a href="qti_setup_9.php">Prevent other installation...</a> | ';
  echo '<a href="'.$root.'qti_index.php">Go to '.THISAPPNAME.'</a></p>';

// --------
// HTML END
// --------

echo '
</div>

</div>
</body>
</html>';