<?php
/**
 * @var CDatabase $oDB
 */
switch($oDB->type)
{

case 'pdo.mysql':
case 'mysql':
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtisection (
  id int,
  type char(1) NOT NULL default "0",
  status char(1) NOT NULL default "0",
  notify char(1) NOT NULL default "1",
  domainid int NOT NULL default 0,
  title varchar(64) NOT NULL default "untitled",
  titleorder int NOT NULL default 255,
  moderator int NOT NULL default 0,
  moderatorname varchar(24) NOT NULL default "Administrator",
  stats varchar(255),
  options varchar(255),
  numfield varchar(24) NOT NULL default " ",
  titlefield char(1) NOT NULL default "0",
  wisheddate char(1) NOT NULL default "0",
  alternate char(1) NOT NULL default "0",
  prefix char(1),
  PRIMARY KEY (id)
  )';
  break;

case 'pdo.sqlsrv':
case 'sqlsrv':
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtisection (
  id int NOT NULL CONSTRAINT pk_'.QDB_PREFIX.'qtisection PRIMARY KEY,
  type char(1) NOT NULL default "0",
  status char(1) NOT NULL default "0",
  notify char(1) NOT NULL default "1",
  domainid int NOT NULL default 0,
  title varchar(64) NOT NULL default "untitled",
  titleorder int NOT NULL default 0,
  moderator int NOT NULL default 0,
  moderatorname varchar(24) NOT NULL default "Administrator",
  stats varchar(255),
  options varchar(255),
  numfield varchar(24) NOT NULL default " ",
  titlefield char(1) NOT NULL default "0",
  wisheddate char(1) NOT NULL default "0",
  alternate char(1) NOT NULL default "0",
  prefix char(1) NULL,
  )';
  break;

case 'pdo.pg':
case 'pg':
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtisection (
  id integer,
  type char(1) NOT NULL default "0",
  status char(1) NOT NULL default "0",
  notify char(1) NOT NULL default "1",
  domainid integer NOT NULL default 0,
  title varchar(64) NOT NULL default "untitled",
  titleorder integer NOT NULL default 255,
  moderator integer NOT NULL default 0,
  moderatorname varchar(24) NOT NULL default "Administrator",
  stats varchar(255),
  options varchar(255),
  numfield varchar(24) NOT NULL default " ",
  titlefield char(1) NOT NULL default "0",
  wisheddate char(1) NOT NULL default "0",
  alternate char(1) NOT NULL default "0",
  prefix char(1) NULL,
  PRIMARY KEY (id)
  )';
  break;

case 'pdo.sqlite':
case 'sqlite':
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtisection (
  id integer,
  type text NOT NULL default "0",
  status text NOT NULL default "0",
  notify text NOT NULL default "1",
  domainid integer NOT NULL default 0,
  title text NOT NULL default "untitled",
  titleorder integer NOT NULL default 255,
  moderator integer NOT NULL default 0,
  moderatorname text NOT NULL default "Administrator",
  stats text,
  options text,
  numfield text NOT NULL default " ",
  titlefield text NOT NULL default "0",
  wisheddate text NOT NULL default "0",
  alternate text NOT NULL default "0",
  prefix text,
  PRIMARY KEY (id)
  )';
  break;

case 'pdo.oci':
case 'oci':
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtisection (
  id number(32),
  type char(1) default "0" NOT NULL,
  status char(1) default "0" NOT NULL,
  notify char(1) default "1" NOT NULL,
  domainid number(32) default 0 NOT NULL,
  title varchar2(64) default "untitled" NOT NULL,
  titleorder number(32) default 255 NOT NULL,
  moderator number(32) default 0 NOT NULL,
  moderatorname varchar2(24) default "Administrator" NOT NULL,
  stats varchar2(255),
  options varchar(255),
  numfield varchar2(24) default " " NOT NULL,
  titlefield char(1) default "0" NOT NULL,
  wisheddate char(1) default "0" NOT NULL,
  alternate char(1) default "0" NOT NULL,
  prefix char(1),
  CONSTRAINT pk_'.QDB_PREFIX.'qtisection PRIMARY KEY (id))';
  break;

default:
  die("Database type [{$oDB->type}] not supported... Must be mysql, sqlsrv, pg, sqlite, oci");

}

$oDB->exec($sql);
$sql = "INSERT INTO ".QDB_PREFIX."qtisection (
id,type,status,notify,domainid,title,titleorder,moderator,moderatorname,stats,options,numfield,titlefield,wisheddate,alternate,prefix)
VALUES (0,'1','0','0',0,'Administration section',0,0,'Admin','','logo=0','T-%03s','1','0','0','a')";
$oDB->exec($sql);

$sql = "INSERT INTO ".QDB_PREFIX."qtisection (
  id,type,status,notify,domainid,title,titleorder,moderator,moderatorname,stats,options,numfield,titlefield,wisheddate,alternate,prefix)
  VALUES (1,'0','0','0',1,'Public section',0,0,'Admin','','logo=0','T-%03s','1','0','0','a')";
$oDB->exec($sql);