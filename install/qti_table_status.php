<?php
/**
 * @var CDatabase $oDB
 */
switch($oDB->type)
{

case 'pdo.mysql':
case 'mysql':
  $strQ='CREATE TABLE '.QDB_PREFIX.'qtistatus (
  id char(1),
  name varchar(24),
  icon varchar(24),
  mailto varchar(255),
  color varchar(24),
  PRIMARY KEY (id)
  )';
  break;

case 'pdo.sqlsrv':
case 'sqlsrv':
  $strQ='CREATE TABLE '.QDB_PREFIX.'qtistatus (
  id char(1) NOT NULL CONSTRAINT pk_'.QDB_PREFIX.'qtistatus PRIMARY KEY,
  name varchar(24) NULL,
  icon varchar(24) NULL,
  mailto varchar(255) NULL,
  color varchar(24) NULL
  )';
  break;

case 'pdo.pg':
case 'pg':
  $strQ='CREATE TABLE '.QDB_PREFIX.'qtistatus (
  id char(1),
  name varchar(24),
  icon varchar(24),
  mailto varchar(255),
  color varchar(24),
  PRIMARY KEY (id)
  )';
  break;

case 'pdo.sqlite':
case 'sqlite':
  $strQ='CREATE TABLE '.QDB_PREFIX.'qtistatus (
  id text,
  name text,
  icon text,
  mailto text,
  color text,
  PRIMARY KEY (id)
  )';
  break;

case 'pdo.oci':
case 'oci':
  $strQ='CREATE TABLE '.QDB_PREFIX.'qtistatus (
  id char(1),
  name varchar2(24),
  icon varchar2(24),
  mailto varchar2(255),
  color varchar2(24),
  CONSTRAINT pk_'.QDB_PREFIX.'qtistatus PRIMARY KEY (id))';
  break;

default:
  die("Database type [{$oDB->type}] not supported... Must be mysql, sqlsrv, pg, oci, sqlite");

}

echo '<span style="color:blue;">';
$b=$oDB->exec($strQ);
echo '</span>';

if ( !empty($oDB->error) || $b===false )
{
  echo '<div class="setup_err">',sprintf (L('E_install'),QDB_PREFIX.'qtistatus',QDB_DATABASE,QDB_USER),'</div>';
  echo '<br><table class="button"><tr><td></td><td class="button" style="width:120px">&nbsp;<a href="qti_setup_1.php">',L('Restart'),'</a>&nbsp;</td></tr></table>';
  exit;
}

// add default values

$oDB->exec( 'INSERT INTO '.QDB_PREFIX.'qtistatus (id,name,icon) VALUES ("A","Submitted","ico_status0.gif")');
$oDB->exec( 'INSERT INTO '.QDB_PREFIX.'qtistatus (id,name,icon) VALUES ("B","In process","ico_status2.gif")');
$oDB->exec( 'INSERT INTO '.QDB_PREFIX.'qtistatus (id,name,icon,color) VALUES ("C","Completed","ico_status4.gif","#AFED9A")');
$oDB->exec( 'INSERT INTO '.QDB_PREFIX.'qtistatus (id,name,icon,color) VALUES ("X","Cancelled","ico_status8.gif","#FF8181")');
$oDB->exec( 'INSERT INTO '.QDB_PREFIX.'qtistatus (id,name,icon,color) VALUES ("Z","Closed","topic_t_1.gif","#EEEEEE")');