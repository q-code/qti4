<?php // v4.0 build:20230430

/**
 * @var string $strPrev
 * @var string $strNext
 * @var string $urlPrev
 * @var string $urlNext
 */
session_start();
include 'init.php';
$urlPrev = APP.'_setup_4.php';
$urlNext  = APP.'_setup_4.php';

function SqlDrop(string $table, string $constrain='')
{
  global $oDB;
  if ( !empty($constrain) && $oDB->type=='oci' ) $oDB->exec( 'ALTER TABLE '.$table.' DROP CONSTRAINT '.$constrain);
  $oDB->exec( 'DROP TABLE '.$table);
}

// INITIALISATION

include 'qti_language.php';

// --------
// HTML BEGIN
// --------

include APP.'_setup_hd.php'; // this will show $error

echo '<p><span class="bold">1. Opening database connection</span>... ';

$oDB = new CDatabase();
if ( !empty($oDB->error) ) die ('<p style="color:red">Connection with database failed.<br>Check that server is up and running.<br>Check that the settings in the file <b>config/config_db.php</b> are correct for your database.<br>'.$oDB->error.'</p>');

echo 'done.</p>
<p class="indent">  driver: '.$oDB->type.'
<br>  database name: '.QDB_DATABASE.'</p>';

// SUBMITTED

if ( isset($_GET['a']) )
{
  switch($_GET['a'])
  {
  case 'Drop ALL tables':
    echo ' Dropping Post...'; SqlDrop(TABPOST,'pk_'.QDB_PREFIX.'qtipost'); echo 'done.<br>';
    echo ' Dropping Topic...'; SqlDrop(TABTOPIC,'pk_'.QDB_PREFIX.'qtitopic'); echo 'done.<br>';
    echo ' Dropping Section...'; SqlDrop(TABSECTION,'pk_'.QDB_PREFIX.'qtisection'); echo 'done.<br>';
    echo ' Dropping Domain...'; SqlDrop(TABDOMAIN,'pk_'.QDB_PREFIX.'qtidomain'); echo 'done.<br>';
    echo ' Dropping User...'; SqlDrop(TABUSER,'pk_'.QDB_PREFIX.'qtiuser'); echo 'done.<br>';
    echo ' Dropping Status...'; SqlDrop(TABSTATUS,'pk_'.QDB_PREFIX.'qtistatus'); echo 'done.<br>';
    echo ' Dropping Setting...'; SqlDrop(TABSETTING); echo 'done.<br>';
    echo ' Dropping Lang...'; SqlDrop(TABLANG); echo 'done.<br>';
    break;
  case 'Drop table Post':
    echo ' Dropping Post...'; SqlDrop(TABPOST,'pk_'.QDB_PREFIX.'qtipost'); echo 'done.<br>'; break;
  case 'Drop table Topic':
    echo ' Dropping Topic...'; SqlDrop(TABTOPIC,'pk_'.QDB_PREFIX.'qtitopic'); echo 'done.<br>'; break;
  case 'Drop table Section':
    echo ' Dropping Section...'; SqlDrop(TABSECTION,'pk_'.QDB_PREFIX.'qtisection'); echo 'done.<br>'; break;
  case 'Drop table Domain':
    echo ' Dropping Domain...'; SqlDrop(TABDOMAIN,'pk_'.QDB_PREFIX.'qtidomain'); echo 'done.<br>'; break;
  case 'Drop table User':
    echo ' Dropping User...'; SqlDrop(TABUSER,'pk_'.QDB_PREFIX.'qtiuser'); echo 'done.<br>'; break;
  case 'Drop table Status':
    echo ' Dropping Status...'; SqlDrop(TABSTATUS,'pk_'.QDB_PREFIX.'qtistatus'); echo 'done.<br>'; break;
  case 'Drop table Setting':
    echo ' Dropping Setting...'; SqlDrop(TABSETTING); echo 'done.<br>'; break;
  case 'Drop table Lang':
    echo ' Dropping Lang...'; SqlDrop(TABLANG); echo 'done.<br>'; break;
  case 'Add table Post':
    include 'qti_table_post.php'; echo $_GET['a'],' done'; break;
  case 'Add table Topic':
    include 'qti_table_topic.php'; echo $_GET['a'],' done'; break;
  case 'Add table Section':
    include 'qti_table_section.php'; echo $_GET['a'],' done'; break;
  case 'Add table Domain':
    include 'qti_table_domain.php'; echo $_GET['a'],' done'; break;
  case 'Add table User':
    include 'qti_table_user.php'; echo $_GET['a'],' done'; break;
  case 'Add table Status':
    include 'qti_table_status.php'; echo $_GET['a'],' done'; break;
  case 'Add table Setting':
    include 'qti_table_setting.php'; echo $_GET['a'],' done'; break;
  case 'Add table Lang':
    include 'qti_table_lang.php'; echo $_GET['a'],' done'; break;
  default: echo '<p class="error">Unknown command !</p>';
  }
}

// Tables do drop

echo '<form action="tool_tables.php" method="get">';
echo '<br><p>2. <span class="bold">Drop the tables</span></p>';
echo '<p><button type="submit" name="a" value="Drop ALL tables">Drop ALL tables</button> from the database ',QDB_DATABASE,'</p><br>';
echo '<p>&nbsp;&nbsp;<button type="submit" name="a" value="Drop table Setting" onclick="return doIt(this.value);">Drop table Setting</button> ',TABSETTING,'</p>';
echo '<p>&nbsp;&nbsp;<button type="submit" name="a" value="Drop table Post" onclick="return doIt(this.value);">Drop table Post</button> ',TABPOST,'</p>';
echo '<p>&nbsp;&nbsp;<button type="submit" name="a" value="Drop table Topic" onclick="return doIt(this.value);">Drop table Topic</button> ',TABTOPIC,'</p>';
echo '<p>&nbsp;&nbsp;<button type="submit" name="a" value="Drop table User" onclick="return doIt(this.value);">Drop table User</button> ',TABUSER,'</p>';
echo '<p>&nbsp;&nbsp;<button type="submit" name="a" value="Drop table Section" onclick="return doIt(this.value);">Drop table Section</button> ',TABSECTION,'</p>';
echo '<p>&nbsp;&nbsp;<button type="submit" name="a" value="Drop table Domain" onclick="return doIt(this.value);">Drop table Domain</button> ',TABDOMAIN,'</p>';
echo '<p>&nbsp;&nbsp;<button type="submit" name="a" value="Drop table Status" onclick="return doIt(this.value);">Drop table Status</button> ',TABSTATUS,'</p>';
echo '<p>&nbsp;&nbsp;<button type="submit" name="a" value="Drop table Lang" onclick="return doIt(this.value);">Drop table Lang</button> ',TABLANG,'</p>';
echo '<br><p>3. <span class="bold">Add tables</span></p>';
echo '<p>&nbsp;&nbsp;<button type="submit" name="a" value="Add table Setting" onclick="return doIt(this.value);">Add table Setting</button> ',TABSETTING,'</p>';
echo '<p>&nbsp;&nbsp;<button type="submit" name="a" value="Add table Post" onclick="return doIt(this.value);">Add table Post</button> ',TABPOST,'</p>';
echo '<p>&nbsp;&nbsp;<button type="submit" name="a" value="Add table Topic" onclick="return doIt(this.value);">Add table Topic</button> ',TABTOPIC,'</p>';
echo '<p>&nbsp;&nbsp;<button type="submit" name="a" value="Add table User" onclick="return doIt(this.value);">Add table User</button> ',TABUSER,'</p>';
echo '<p>&nbsp;&nbsp;<button type="submit" name="a" value="Add table Section" onclick="return doIt(this.value);">Add table Section</button> ',TABSECTION,'</p>';
echo '<p>&nbsp;&nbsp;<button type="submit" name="a" value="Add table Domain" onclick="return doIt(this.value);">Add table Domain</button> ',TABDOMAIN,'</p>';
echo '<p>&nbsp;&nbsp;<button type="submit" name="a" value="Add table Status" onclick="return doIt(this.value);">Add table Status</button> ',TABSTATUS,'</p>';
echo '<p>&nbsp;&nbsp;<button type="submit" name="a" value="Add table Lang" onclick="return doIt(this.value);">Add table Lang</button> ',TABLANG,'</p>';
echo '</form>
<script type="text/javascript">
function doIt(a) { return confirm("Are you sure you want to "+a+"?"); }
</script>
';


echo '<br><p><a href="qti_setup.php">Install...</a>';
if ( file_exists('tool_check.php') ) echo ' | <a href="tool_check.php">Check installation...</a>';
echo '</p>';

// --------
// HTML END
// --------
include APP.'_setup_ft.php'; // this will show $error