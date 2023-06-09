<?php // v4.0 build:20230618
/**
 * @var string $strPrev
 * @var string $strNext
 * @var string $urlPrev
 * @var string $urlNext
 */
session_start();
include 'init.php';
$error='';
$urlPrev = APP.'_setup_1.php';
$urlNext = APP.'_setup_3.php';

// --------
// HTML BEGIN
// --------

include APP.'_setup_hd.php';

if ( isset($_POST['ok']) ) {

  try {

    include '../bin/lib_qti_base.php';

    if ( isset($_SESSION['qti_dbopwd']) )
    {
      $user = $_SESSION['qti_dbologin'];
      $pwd = $_SESSION['qti_dbopwd'];
    }
    else
    {
      $user = QDB_USER;
      $pwd = QDB_PWD;
    }

    $oDB = new CDatabase(QDB_SYSTEM,QDB_HOST,QDB_DATABASE,$user,$pwd);
    if ( !empty($oDB->error) ) throw new Exception( $oDB->error );
    // Install the tables
    $strTable = TABSETTING;
    echo '<p>A) '.L('Installation').' '.$strTable.'... ';
    include 'qti_table_setting.php';
    echo L('Done'),', ',L('Default_setting'),'<br>';
    $strTable = TABDOMAIN;
    echo 'B) '.L('Installation').' '.$strTable.'... ';
    include 'qti_table_domain.php';
    echo L('Done'),', ',L('Default_domain'),'<br>';
    $strTable = TABSECTION;
    echo 'C) '.L('Installation').' '.$strTable.'... ';
    include 'qti_table_section.php';
    echo L('Done'),', ',L('Default_section'),'<br>';
    $strTable = TABTOPIC;
    echo 'D) '.L('Installation').' '.$strTable.'... ';
    include 'qti_table_topic.php';
    echo L('Done'),'<br>';
    $strTable = TABPOST;
    echo 'E) '.L('Installation').' '.$strTable.'... ';
    include 'qti_table_post.php';
    echo L('Done'),'<br>';
    $strTable = TABUSER;
    echo 'F) '.L('Installation').' '.$strTable.'... ';
    include 'qti_table_user.php';
    echo L('Done'),', ',L('Default_user'),'<br>';
    $strTable = TABLANG;
    echo 'G) '.L('Installation').' '.$strTable.'... ';
    include 'qti_table_lang.php';
    $strTable = TABSTATUS;
    echo 'H) '.L('Installation').' '.$strTable.'... ';
    include 'qti_table_status.php';
    echo L('Done'),', ',L('Default_status'),'</p>';
    if ( !empty($oDB->error) ) throw new Exception( $oDB->error );

    echo '<div class="setup_ok">',L('S_install'),'</div>';
    $_SESSION['qtiInstalled'] = true;
    // save the url
    $strURL = ( empty($_SERVER['SERVER_HTTPS']) ? "http://" : "https://" ).$_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
    $strURL = substr($strURL,0,-24);
    $oDB->exec( 'UPDATE TABSETTING SET setting="'.$strURL.'" WHERE param="site_url"');

  } catch (Exception $e) {

    $error = $e->getMessage();
    echo '<div class="setup_err">Problem to execute a query in database ['.QDB_DATABASE.'] on server ['.QDB_HOST.']<br>See the error message... '.$error.'</div>';

  }

}
else
{
  echo '
  <h2>',L('Install_db'),'</h2>
  <table>
  <tr valign="top">
  <td width="475" style="padding:5px">
  <form method="post" name="install" action="qti_setup_2.php" onsubmit="showWait()">
  <p>',L('Upgrade2'),'</p>
  <p><button type="submit" id="btn-create" name="ok" value="ok" onclick="return this.innerHTML!=msgWait">'.sprintf(L('Create_tables'),QDB_DATABASE).'</button></p>
  </form>
  </td>
  <td><div class="setup_help">',L('Help_2'),'</div></td>
  </tr>
  </table>
  <script type="text/javascript">
  const msgWait = "Installing...";
  function showWait(){
    document.body.style.cursor="wait";
    let d = document.getElementById("btn-create");
    if ( d ) d.innerHTML=msgWait;
  }
  </script>
  ';
}

// --------
// HTML END
// --------

include 'qti_setup_ft.php';