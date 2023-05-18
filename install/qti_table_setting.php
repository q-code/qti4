<?php
/**
 * @var CDatabase $oDB
 */
switch($oDB->type)
{

case 'pdo.mysql':
case 'mysql':
  $strQ='CREATE TABLE '.QDB_PREFIX.'qtisetting (
  param varchar(24),
  setting varchar(255)
  )';
  break;

case 'pdo.sqlsrv':
case 'sqlsrv':
  $strQ='CREATE TABLE '.QDB_PREFIX.'qtisetting (
  param varchar(24),
  setting varchar(255)
  )';
  break;

case 'pdo.pg':
case 'pg':
  $strQ='CREATE TABLE '.QDB_PREFIX.'qtisetting (
  param varchar(24),
  setting varchar(255)
  )';
  break;

case 'pdo.sqlite':
case 'sqlite':
  $strQ='CREATE TABLE '.QDB_PREFIX.'qtisetting (
  param text,
  setting text
  )';
  break;

case 'pdo.oci':
case 'oci':
  $strQ='CREATE TABLE '.QDB_PREFIX.'qtisetting (
  param varchar2(24),
  setting varchar2(255)
  )';
  break;

default:
  die("Database type [{$oDB->type}] not supported... Must be mysql, sqlsrv, pg, sqlite, oci");

}

echo '<span style="color:blue;">';
$b=$oDB->exec($strQ);
echo '</span>';

if ( !empty($oDB->error) || $b===false )
{
  echo '<div class="setup_err">',sprintf (L('E_install'),QDB_PREFIX.'qtisetting',QDB_DATABASE,QDB_USER),'</div>';
  echo '<br><table class="button"><tr><td></td><td class="button" style="width:120px">&nbsp;<a href="qti_setup_1.php">',L('Restart'),'</a>&nbsp;</td></tr></table>';
  exit;
}

$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('version','4.0')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('board_offline','1')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('site_name','QT-cute')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('site_url','http://')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('home_name','Home')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('home_url','http://www.qt-cute.org')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('admin_email','')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('admin_name','')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('admin_addr','')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('items_per_section','1000')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('posts_per_item','100')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('chars_per_post','4000')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('lines_per_post','250')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('time_zone','1')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('show_time_zone','1')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('home_menu','0')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('posts_per_day','100')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('site_width','800')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('register_safe','text')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('smtp_password','')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('smtp_username','')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('smtp_host','')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('use_smtp','0')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('show_welcome','1')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('items_per_page','20')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('replies_per_page','20')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('language','".(empty($_SESSION['qti_setup_lang']) ? 'en' : $_SESSION['qti_setup_lang'])."')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('userlang','1')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('item_firstline','1')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('show_banner','1')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('show_legend','1')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('index_name','Support Centre')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('skin_dir','default')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('formatdate','j M Y')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('formattime','G:i')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('formatpicture','mime=gif jpg jpeg png;width=100;height=100')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('show_id','T-%03s')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('show_back','1')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('news_on_top','1')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('show_closed','1')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('register_mode','direct')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('daylight','1')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('visitor_right','5')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('show_quick_reply','1')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('show_calendar','U')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('show_memberlist','U')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('upload','U')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('upload_size','8192')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('show_stats','U')" ); //v1.3
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('tags','U')" ); //v2.0
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('unreplied_days','10')" ); //v3.0
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtisetting (param,setting) VALUES ('sse','')" ); //v4.0