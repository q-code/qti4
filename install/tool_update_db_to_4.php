<?php // v4.0 build:20240210

session_start();
/**
* @var CHtml $oH
 */
include 'init.php'; if ( SUser::role()!=='A' ) die('Access denied');

$database = strpos(QDB_SYSTEM,'sqlite') ? '../'.QDB_DATABASE : QDB_DATABASE; // using SQLite, database file is in the root directory
$oH->error='';
$strMessage = '';

// CHECK DB VERSION (in case of update)

$oDB = new CDatabase(QDB_SYSTEM,QDB_HOST,$database);
if ( !empty($oDB->error) ) die ('<p><font color="red">Connection with database failed.<br>Please contact the webmaster for further information.</font></p><p>The webmaster must check that server is up and running, and that the settings in the config file are correct for the database.</p>');

$oDB->query( "SELECT setting FROM ".QDB_PREFIX."qtisetting WHERE param='version'" );
$row=$oDB->getRow();

// UPDAGRADE TO 2.1

if ( $row['setting']=='2.0' )
{
  switch($oDB->type)
  {
    case 'sqlite':
      $oDB->exec( "ALTER TABLE ".QDB_PREFIX."qtitopic ADD modifdate text" );
      $oDB->exec( "ALTER TABLE ".QDB_PREFIX."qtisection ADD options text" );
      break;
    case 'oci':
      $oDB->exec( "ALTER TABLE ".QDB_PREFIX."qtitopic ADD modifdate varchar2(20)" );
      $oDB->exec( "ALTER TABLE ".QDB_PREFIX."qtisection ADD options varchar2(255)" );
      break;
    default:
      $oDB->exec( "ALTER TABLE ".QDB_PREFIX."qtitopic ADD modifdate varchar(20)" );
      $oDB->exec( "ALTER TABLE ".QDB_PREFIX."qtisection ADD options varchar(255)" );
      break;
  }
  // update section options

  $oDB->query( "SELECT id,sortfield,infofield,logo FROM ".QDB_PREFIX."qtisection" );
  $arr = [];
  while($row=$oDB->getRow())
  {
    if ( $row['sortfield']=='lastpostdate' ) $row['sortfield']='0';
    if ( $row['infofield']=='N' ) $row['infofield']='0';
    $arr[$row['id']]='coord=0;order='.(empty($row['sortfield']) ? '0' : $row['sortfield']).';last='.(empty($row['infofield']) ? '0' : $row['infofield']).';logo='.(empty($row['logo']) ? '0' : $row['logo']);
  }
  foreach($arr as $strKey=>$strValue)
  {
    $oDB->exec( "UPDATE ".QDB_PREFIX."qtisection SET options='$strValue' WHERE id=$strKey" );
  }

  // Register version

  $oDB->exec( "UPDATE ".QDB_PREFIX."qtisetting SET setting='2.1' WHERE param='version'" );
  $row['setting']='2.1';
  $strMessage .= '<p>Database upgraded to 2.1</p>';
}

// UPDAGRADE 2.1 TO 2.4

if ( $row['setting']=='2.1' || $row['setting']=='2.2' || $row['setting']=='2.3' )
{
  $oDB->exec( "UPDATE ".QDB_PREFIX."qtisetting SET setting='2.4' WHERE param='version'" );
  switch($oDB->type)
  {
    case 'sqlite':
      $oDB->exec( "ALTER TABLE ".QDB_PREFIX."qtitopic ADD param text" );
      break;
    case 'oci':
      $oDB->exec( "ALTER TABLE ".QDB_PREFIX."qtitopic ADD param varchar2(255)" );
      break;
    default:
      $oDB->exec( "ALTER TABLE ".QDB_PREFIX."qtitopic ADD param varchar(255)" );
      break;
  }
  $row['setting']='2.4';
  $strMessage .= '<p>Database upgraded to 2.4</p>';
}

// UPDAGRADE 2.4 TO 2.5

if ( $row['setting']=='2.4' )
{
  $oDB->exec( "UPDATE ".QDB_PREFIX."qtisetting SET setting='2.5' WHERE param='version'" );
  switch($oDB->type)
  {
    case 'sqlite':
      $oDB->exec( "ALTER TABLE ".QDB_PREFIX."qtiuser ADD secret_q text" );
      $oDB->exec( "ALTER TABLE ".QDB_PREFIX."qtiuser ADD secret_a text" );
      break;
    case 'oci':
      $oDB->exec( "ALTER TABLE ".QDB_PREFIX."qtiuser ADD secret_q varchar2(255)" );
      $oDB->exec( "ALTER TABLE ".QDB_PREFIX."qtiuser ADD secret_a varchar2(255)" );
      break;
    default:
      $oDB->exec( "ALTER TABLE ".QDB_PREFIX."qtiuser ADD secret_q varchar(255)" );
      $oDB->exec( "ALTER TABLE ".QDB_PREFIX."qtiuser ADD secret_a varchar(255)" );
      break;
  }
  $row['setting']='2.5';
  $strMessage .= '<p>Database upgraded to 2.5</p>';
}

if ( $row['setting']=='2.5' )
{
  $oDB->exec( "UPDATE ".QDB_PREFIX."qtisetting SET setting='3.0' WHERE param='version'" );
  $oDB->exec( "UPDATE ".QDB_PREFIX."qtisetting SET param='posts_per_item' WHERE param='posts_per_member'" );
  $oDB->exec( "UPDATE ".QDB_PREFIX."qtisetting SET param='items_per_page' WHERE param='topics_per_page'" );
  $oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting VALUES ('login_addon','0','1')" );
  $row['setting']='3.0';
  $strMessage .= '<p>Database upgraded to 3.0</p>';
}

if ( $row['setting']=='3.0' )
{
  $oDB->exec( "UPDATE ".QDB_PREFIX."qtisetting SET param='item_firstline' WHERE param='section_desc'" );
  $oDB->exec( "UPDATE ".QDB_PREFIX."qtisetting SET setting='4.0' WHERE param='version'" );
  $row['setting']='4.0';
  $strMessage .= '<p>Database upgraded to 4.0</p>';
  $oDB->exec( "ALTER TABLE ".QDB_PREFIX."qtisetting DROP COLUMN loaded" );
}


// ------
// HTML BEGIN
// ------
include 'setup_hd.php';

if ( !empty($strMessage) ) echo $strMessage;

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

// document folders

$oH->error='';
if ( !is_dir('upload') )
{
  $oH->error .= '<font color=red>Directory <b>upload</b> not found.</font><br>Please create this directory and make it writeable (chmod 777) if you want to allow uploads<br>';
}
else
{
  if ( !is_readable('upload') ) $oH->error .= '<font color=red>Directory <b>upload</b> is not readable.</font><br>Change permissions (chmod 777) if you want to allow uploads<br>';
  if ( !is_writable('upload') ) $oH->error .= '<font color=red>Directory <b>upload</b> is not writable.</font><br>Change permissions (chmod 777) if you want to allow uploads<br>';
}

if ( empty($oH->error) )
{
  $iY = intval(date('Y'));
  for ($i=$iY;$i<=$iY+5;$i++)
  {
    if ( !is_dir('upload/'.$i) )
    {
      if ( mkdir('upload/'.$i) )
      {
        for ($j=1;$j<=12;$j++)
        {
        mkdir('upload/'.$i.'/'.($i*100+$j));
        }
      }
    }
  }
}

// DISCONNECT to reload new variables (keep same language)
$str = $_SESSION['setup_lang'];
$_SESSION = [];
$_SESSION['setup_lang']=$str;

// ------
// HTML END
// ------
if ( file_exists('check.php') ) echo '<p><a href="check.php">',L('Check_install'),'</a></p>';

include 'setup_ft.php';