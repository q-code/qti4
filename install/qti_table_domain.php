<?php
/**
 * @var CDatabase $oDB
 */
switch($oDB->type)
{

case 'pdo.mysql':
case 'mysql':
  $strQ='CREATE TABLE '.QDB_PREFIX.'qtidomain (
  id int,
  title varchar(64) NOT NULL default "untitled",
  titleorder int NOT NULL default 0,
  PRIMARY KEY (id)
  )';
  break;

case 'pdo.sqlsrv':
case 'sqlsrv':
  $strQ='CREATE TABLE '.QDB_PREFIX.'qtidomain (
  id int NOT NULL CONSTRAINT pk_'.QDB_PREFIX.'qtidomain PRIMARY KEY,
  title varchar(64) NOT NULL default "untitled",
  titleorder int NOT NULL default 0
  )';
  break;

case 'pdo.pg':
case 'pg':
  $strQ='CREATE TABLE '.QDB_PREFIX.'qtidomain (
  id integer,
  title varchar(64) NOT NULL default "untitled",
  titleorder integer NOT NULL default 0,
  PRIMARY KEY (id)
  )';
  break;

case 'pdo.sqlite':
case 'sqlite':
  $strQ='CREATE TABLE '.QDB_PREFIX.'qtidomain (
  id integer,
  title text NOT NULL default "untitled",
  titleorder integer NOT NULL default 0,
  PRIMARY KEY (id)
  )';
  break;

case 'pdo.oci':
case 'oci':
  $strQ='CREATE TABLE '.QDB_PREFIX.'qtidomain (
  id number(32),
  title varchar2(64) default "untitled" NOT NULL,
  titleorder number(32) default 0 NOT NULL,
  CONSTRAINT pk_'.QDB_PREFIX.'qtidomain PRIMARY KEY (id))';
  break;

default:
  die("Database type [{$oDB->type}] not supported... Must be mysql, sqlsrv, pg, sqlite, oci");

}

echo '<span style="color:blue;">';
$b=$oDB->exec($strQ);
echo '</span>';

if ( !empty($oDB->error) || $b===false )
{
  echo '<div class="setup_err">',sprintf (L('E_install'),QDB_PREFIX.'qtidomain',QDB_DATABASE,QDB_USER),'</div>';
  echo '<br /><table class="button"><tr><td></td><td class="button" style="width:120px">&nbsp;<a href="qti_setup_1.php">',L('Restart'),'</a>&nbsp;</td></tr></table>';
  exit;
}

$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtidomain (id,title,titleorder) VALUES (0,'Administration domain',0)" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtidomain (id,title,titleorder) VALUES (1,'Public domain',1)" );