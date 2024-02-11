<?php
/**
 * @var CDatabase $oDB
 */
switch($oDB->type)
{

case 'pdo.mysql':
case 'mysql':
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtistatus (
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
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtistatus (
  id char(1) NOT NULL CONSTRAINT pk_'.QDB_PREFIX.'qtistatus PRIMARY KEY,
  name varchar(24) NULL,
  icon varchar(24) NULL,
  mailto varchar(255) NULL,
  color varchar(24) NULL
  )';
  break;

case 'pdo.pg':
case 'pg':
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtistatus (
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
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtistatus (
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
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtistatus (
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

$oDB->exec($sql);
$oDB->exec( 'INSERT INTO '.QDB_PREFIX.'qtistatus (id,name,icon) VALUES ("A","Submitted","ico_status0.gif")');
$oDB->exec( 'INSERT INTO '.QDB_PREFIX.'qtistatus (id,name,icon) VALUES ("B","In process","ico_status2.gif")');
$oDB->exec( 'INSERT INTO '.QDB_PREFIX.'qtistatus (id,name,icon,color) VALUES ("C","Completed","ico_status4.gif","#AFED9A")');
$oDB->exec( 'INSERT INTO '.QDB_PREFIX.'qtistatus (id,name,icon,color) VALUES ("X","Cancelled","ico_status8.gif","#FF8181")');
$oDB->exec( 'INSERT INTO '.QDB_PREFIX.'qtistatus (id,name,icon,color) VALUES ("Z","Closed","topic_t_1.gif","#EEEEEE")');