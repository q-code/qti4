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

$oDB->exec($sql);