<?php
/**
 * @var CDatabase $oDB
 */
switch($oDB->type)
{

case 'pdo.mysql':
case 'mysql':
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtilang (
  objtype varchar(10),
  objlang varchar(2),
  objid varchar(24),
  objname varchar(4000),
  PRIMARY KEY (objtype,objlang,objid)
  )';
  break;

case 'pdo.sqlsrv':
case 'sqlsrv':
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtilang (
  objtype varchar(10),
  objlang varchar(2),
  objid varchar(24),
  objname varchar(4000),
  CONSTRAINT pk_'.QDB_PREFIX.'qtilang PRIMARY KEY (objtype,objlang,objid)
  )';
  break;

case 'pdo.pg':
case 'pg':
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtilang (
  objtype varchar(10),
  objlang varchar(2),
  objid varchar(24),
  objname varchar(4000),
  PRIMARY KEY (objtype,objlang,objid)
  )';
  break;

case 'pdo.sqlite':
case 'sqlite':
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtilang (
  objtype text,
  objlang text,
  objid text,
  objname text,
  PRIMARY KEY (objtype,objlang,objid)
  )';
  break;

case 'pdo.oci':
case 'oci':
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtilang (
  objtype varchar2(10),
  objlang varchar2(2),
  objid varchar2(24),
  objname varchar2(4000),
  CONSTRAINT pk_'.QDB_PREFIX.'qtilang PRIMARY KEY (objtype,objlang,objid))';
  break;

default:
  die("Database type [{$oDB->type}] not supported... Must be mysql, sqlsrv, pg, sqlite, oci");

}

echo '<span style="color:blue;">';
$b = $oDB->exec($sql);
echo '</span>';

if ( !empty($oDB->error) || $b===false )
{
  echo '<div class="setup_err">',sprintf (L('E_install'),QDB_PREFIX.'qtilang',QDB_DATABASE,QDB_USER),'</div>';
  echo '<br /><table class="button"><tr><td></td><td class="button" style="width:120px">&nbsp;<a href="qti_setup_1.php">',L('Restart'),'</a>&nbsp;</td></tr></table>';
  exit;
}