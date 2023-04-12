<?php // v4.0 build:20230205
/**
 * @var string $error
 * @var string $strPrev
 * @var string $strNext
 * @var string $urlPrev
 * @var string $urlNext
 */
session_start();
include 'init.php';
$error='';
$strPrev= L('Back');
$strNext= L('Finish');
$urlPrev = APP.'_setup_3.php';
$urlNext = APP.'_setup_9.php';

// CHECK DB VERSION (in case of update)
$oDB = new CDatabase();
if ( !empty($oDB->error) ) die ('<p style="color:red">Connection with database failed.<br>Check that server is up and running.<br>Check that the settings in the file <b>config/config_db.php</b> are correct for your database.</p>');
$oDB->query( 'SELECT setting FROM '.QDB_PREFIX.'qtisetting WHERE param="version"');
$row=$oDB->getRow();

// UPDAGRADE 2.0 TO 2.1

if ( $row['setting']=='2.0' )
{
  switch($oDB->type)
  {
  case 'sqlite':
    $oDB->exec( 'ALTER TABLE '.QDB_PREFIX.'qtitopic ADD statusdate text');
    break;
  case 'oci':
    $oDB->exec( 'ALTER TABLE '.QDB_PREFIX.'qtitopic ADD statusdate varchar2(20)');
    break;
  default:
    $oDB->exec( 'ALTER TABLE '.QDB_PREFIX.'qtitopic ADD statusdate varchar(20)');
    break;
  }
  $oDB->exec( 'INSERT INTO '.QDB_PREFIX.'qtisetting VALUES ("sys_change", "0", "1")');
  $oDB->exec( 'UPDATE '.QDB_PREFIX.'qtisetting SET setting="2.1" WHERE param="version"');

  $row['setting']='2.1';
  $strMessage .= '<p>Database upgraded to 2.1</p>';
}

// UPDAGRADE 2.1 TO 2.2

if ( $row['setting']=='2.1' )
{
  switch($oDB->type)
  {
  case 'sqlite':
    $oDB->exec( 'ALTER TABLE '.QDB_PREFIX.'qtitopic ADD modifdate text');
    $oDB->exec( 'ALTER TABLE '.QDB_PREFIX.'qtisection ADD options text');
    break;
  case 'oci':
    $oDB->exec( 'ALTER TABLE '.QDB_PREFIX.'qtitopic ADD modifdate varchar2(20)');
    $oDB->exec( 'ALTER TABLE '.QDB_PREFIX.'qtisection ADD options varchar2(255)');
    break;
  default:
    $oDB->exec( 'ALTER TABLE '.QDB_PREFIX.'qtitopic ADD modifdate varchar(20)');
    $oDB->exec( 'ALTER TABLE '.QDB_PREFIX.'qtisection ADD options varchar(255)');
    break;
  }

  $oDB->exec( 'UPDATE '.QDB_PREFIX.'qtisetting SET setting="2.2" WHERE param="version"');
  $row['setting']='2.2';
  $strMessage .= '<p>Database upgraded to 2.2</p>';

  // update section options

  $oDB->query( 'SELECT id,sortfield,infofield,logo FROM '.QDB_PREFIX.'qtisection');
  $arr = array();
  while($row=$oDB->getRow())
  {
    if ( $row['sortfield']=='lastpostdate' ) $row['sortfield']='0';
    if ( $row['infofield']=='N' ) $row['infofield']='0';
    $arr[$row['id']]='coord=0;order='.(empty($row['sortfield']) ? '0' : $row['sortfield']).';last='.(empty($row['infofield']) ? '0' : $row['infofield']).';logo='.(empty($row['logo']) ? '0' : $row['logo']);
  }
  foreach($arr as $strKey=>$strValue)
  {
    $oDB->exec( 'UPDATE '.QDB_PREFIX.'qtisection SET options="'.$strValue.'" WHERE id='.$strKey);
  }

  // update section stats

  foreach($arr as $strKey=>$strValue)
  {
    $oDB->query( 'SELECT count(*) as countid FROM '.QDB_PREFIX.'qtitopic WHERE section='.$strKey);
    $row = $oDB->getRow();
    $strArg = 'topics='.$row['countid'];
    $oDB->query( 'SELECT count(*) as countid FROM '.QDB_PREFIX.'qtitopic WHERE status="Z" AND section='.$strKey.' AND type="T"');
    $row = $oDB->getRow();
    $strArg .= ';itemsZ='.$row['countid'];
    $oDB->query( 'SELECT count(*) as countid FROM '.QDB_PREFIX.'qtipost WHERE section='.$strKey.' AND (type="R" OR type="F")');
    $row = $oDB->getRow();
    $strArg .= ';replies='.$row['countid'];
    $oDB->query( 'SELECT count(*) as countid FROM '.QDB_PREFIX.'qtipost p INNER JOIN '.QDB_PREFIX.'qtitopic t ON p.topic=t.id WHERE p.section='.$strKey.' AND p.type<>"P" AND t.status="Z"');
    $row = $oDB->getRow();
    $strArg .= ';repliesZ='.$row['countid'];
    $oDB->query( 'SELECT count(*) as countid FROM '.QDB_PREFIX.'qtitopic WHERE tags<>"" AND section='.$strKey);
    $row = $oDB->getRow();
    $strArg .= ';tags='.$row['countid'];
    $oDB->exec( 'UPDATE '.QDB_PREFIX.'qtisection SET stats="'.$strArg.'" WHERE id='.$strKey);
  }

}

// UPDAGRADE 2.2 TO 2.3

if ( $row['setting']=='2.2' )
{
  $oDB->exec( 'UPDATE '.QDB_PREFIX.'qtisetting SET setting="2.3" WHERE param="version"');

  $row['setting']='2.3';
  $strMessage .= '<p>Database upgraded to 2.3</p>';
}

// UPDAGRADE 2.3 TO 2.4

if ( $row['setting']=='2.3' )
{
  $oDB->exec( 'UPDATE '.QDB_PREFIX.'qtisetting SET setting="2.4" WHERE param="version"');
  switch($oDB->type)
  {
  case 'sqlite':
    $oDB->exec( 'ALTER TABLE '.QDB_PREFIX.'qtitopic ADD param text');
    break;
  case 'oci':
    $oDB->exec( 'ALTER TABLE '.QDB_PREFIX.'qtitopic ADD param varchar(255)');
    break;
  default:
    $oDB->exec( 'ALTER TABLE '.QDB_PREFIX.'qtitopic ADD param varchar(255)');
    break;
  }
  $row['setting']='2.4';
  $strMessage .= '<p>Database upgraded to 2.4</p>';
}

// UPDAGRADE 2.4 TO 2.5

if ( $row['setting']=='2.4' )
{
  $oDB->exec( 'UPDATE '.QDB_PREFIX.'qtisetting SET setting="2.5" WHERE param="version"');
  switch($oDB->type)
  {
  case 'sqlite':
    $oDB->exec( 'ALTER TABLE '.QDB_PREFIX.'qtiuser ADD secret_q text');
    $oDB->exec( 'ALTER TABLE '.QDB_PREFIX.'qtiuser ADD secret_a text');
    break;
  case 'oci':
    $oDB->exec( 'ALTER TABLE '.QDB_PREFIX.'qtiuser ADD secret_q varchar2(255)');
    $oDB->exec( 'ALTER TABLE '.QDB_PREFIX.'qtiuser ADD secret_a varchar2(255)');
    break;
  default:
    $oDB->exec( 'ALTER TABLE '.QDB_PREFIX.'qtiuser ADD secret_q varchar(255)');
    $oDB->exec( 'ALTER TABLE '.QDB_PREFIX.'qtiuser ADD secret_a varchar(255)');
    break;
  }
  $row['setting']='2.5';
  $strMessage .= '<p>Database upgraded to 2.5</p>';
}

if ( $row['setting']=='2.5' )
{
  $oDB->exec( 'UPDATE '.QDB_PREFIX.'qtisetting SET setting="3.0" WHERE param="version"');
  $oDB->exec( 'UPDATE '.QDB_PREFIX.'qtisetting SET param="show_welcome" WHERE param="sys_welcome"');
  $oDB->exec( 'UPDATE '.QDB_PREFIX.'qtisetting SET param="posts_per_item" WHERE param="posts_per_topic"');
  $oDB->exec( 'UPDATE '.QDB_PREFIX.'qtisetting SET param="items_per_page" WHERE param="topics_per_page"');
  $row['setting']='3.0';
  $strMessage .= '<p>Database upgraded to 3.0</p>';
}

// UPDAGRADE 3.0 TO 4.0

if ( $row['setting']=='3.0' )
{
  $oDB->exec( 'UPDATE '.QDB_PREFIX.'qtisetting SET setting="4.0" WHERE param="version"');
  $oDB->exec( 'UPDATE '.QDB_PREFIX.'qtisetting SET param="items_per_section" WHERE param="items_per_forum"');
  $oDB->exec( 'UPDATE '.QDB_PREFIX.'qtisetting SET param="show_welcome" WHERE param="sys_welcome"');
  $oDB->exec( 'UPDATE '.QDB_PREFIX.'qtisetting SET param="item_firstline" WHERE param="section_desc"');
  $oDB->exec( 'INSERT INTO '.QDB_PREFIX.'qtisetting VALUES ("reCAPTCHAv2pk", "")');
  $oDB->exec( 'INSERT INTO '.QDB_PREFIX.'qtisetting VALUES ("reCAPTCHAv2sk", "")');
  $oDB->exec( 'INSERT INTO '.QDB_PREFIX.'qtisetting VALUES ("reCAPTCHAv3pk", "")');
  $oDB->exec( 'INSERT INTO '.QDB_PREFIX.'qtisetting VALUES ("reCAPTCHAv3sk", "")');
  $oDB->exec( 'INSERT INTO '.QDB_PREFIX.'qtisetting VALUES ("sse", "CONNECT=10000;ORIGIN=http://localhost;MAX_ROWS=2;TIEMOUT=30;LATENCY=10000")');
  $oDB->exec( 'INSERT INTO '.QDB_PREFIX.'qtisetting VALUES ("sse_server", "")');

  $oDB->exec( 'ALTER TABLE'.QDB_PREFIX.'qtipost CHANGE forum section INT(11) NOT NULL DEFAULT 0');
  $oDB->exec( 'ALTER TABLE'.QDB_PREFIX.'qtitopic CHANGE forum section INT(11) NOT NULL DEFAULT 0');
  $oDB->exec( '
  CREATE TABLE IF NOT EXISTS '.QDB_PREFIX.'qtisection (
  id int(11) NOT NULL DEFAULT 0,
  type char(1) NOT NULL DEFAULT "0",
  status char(1) NOT NULL DEFAULT "0",
  notify char(1) NOT NULL DEFAULT "1",
  domainid int(11) NOT NULL DEFAULT 0,
  title varchar(64) NOT NULL DEFAULT "untitled",
  titleorder int(11) NOT NULL DEFAULT 255,
  moderator int(11) NOT NULL DEFAULT 0,
  moderatorname varchar(24) NOT NULL DEFAULT "Administrator",
  stats varchar(255) DEFAULT NULL,
  options varchar(255) DEFAULT NULL,
  numfield varchar(24) NOT NULL DEFAULT " ",
  titlefield char(1) NOT NULL DEFAULT "0",
  wisheddate char(1) NOT NULL DEFAULT "0",
  alternate char(1) NOT NULL DEFAULT "0",
  prefix char(1) DEFAULT NULL,
  PRIMARY KEY (id)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1
  ');
  $oDB->exec( 'INSERT INTO qtisection SELECT * FROM qtisection ');
  $row['setting']='4.0';
  $strMessage .= '<p>Database upgraded to 4.0</p>';
}

// --------
// HTML BEGIN
// --------

include APP.'_setup_hd.php';

if (!empty($strMessage) ) echo $strMessage;

if ( isset($_SESSION['qtiInstalled']) )
{
  echo '<p>Database 4.0 in place.</p>';
  echo '<p>',L('S_install_exit'),'</p>';
  echo '<div style="width:350px; padding:10px; border-style:solid; border-color:#FF0000; border-width:1px; background-color:#EEEEEE">',L('End_message'),'<br>',L('User'),': <b>Admin</b><br>',L('Password'),': <b>Admin</b><br></div><br>';
}
else
{
  echo '<h2>',L('N_install'),'</h2>';
}

// DISCONNECT to reload new variables (keep same language)
$str = $_SESSION[APP.'_setup_lang'];
$_SESSION = array();
$_SESSION[APP.'_setup_lang']=$str;

// --------
// HTML END
// --------

echo '<p>';
if ( file_exists('tool_check.php') ) echo '<a href="tool_check.php">',L('Check_install'),'...</a>';
echo '</p>';

include APP.'_setup_ft.php';