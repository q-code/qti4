<?php
/**
 * @var CDatabase $oDB
 */
switch($oDB->type)
{

case 'pdo.mysql':
case 'mysql':
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtidomain (
  id int,
  title varchar(64) NOT NULL default "untitled",
  titleorder int NOT NULL default 0,
  PRIMARY KEY (id)
  )';
  break;

case 'pdo.sqlsrv':
case 'sqlsrv':
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtidomain (
  id int NOT NULL CONSTRAINT pk_'.QDB_PREFIX.'qtidomain PRIMARY KEY,
  title varchar(64) NOT NULL default "untitled",
  titleorder int NOT NULL default 0
  )';
  break;

case 'pdo.pg':
case 'pg':
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtidomain (
  id integer,
  title varchar(64) NOT NULL default "untitled",
  titleorder integer NOT NULL default 0,
  PRIMARY KEY (id)
  )';
  break;

case 'pdo.sqlite':
case 'sqlite':
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtidomain (
  id integer,
  title text NOT NULL default "untitled",
  titleorder integer NOT NULL default 0,
  PRIMARY KEY (id)
  )';
  break;

case 'pdo.oci':
case 'oci':
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtidomain (
  id number(32),
  title varchar2(64) default "untitled" NOT NULL,
  titleorder number(32) default 0 NOT NULL,
  CONSTRAINT pk_'.QDB_PREFIX.'qtidomain PRIMARY KEY (id))';
  break;

default:
  die("Database type [{$oDB->type}] not supported... Must be mysql, sqlsrv, pg, sqlite, oci");

}

$oDB->exec($sql);
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtidomain (id,title,titleorder) VALUES (0,'Administration domain',0)" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtidomain (id,title,titleorder) VALUES (1,'Public domain',1)" );