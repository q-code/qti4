<?php
/**
 * @var CDatabase $oDB
 */
switch($oDB->type)
{

case 'pdo.mysql':
case 'mysql':
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtitopic (
  id int,
  numid int NOT NULL default 0,
  section int NOT NULL default 0,
  type char(1) NOT NULL default "T",
  status char(1) NOT NULL default "A",
  statusdate varchar(20) NOT NULL default "0",
  wisheddate varchar(20) NOT NULL default "0",
  tags varchar(4000),
  firstpostid int NOT NULL default 0,
  lastpostid int NOT NULL default 0,
  firstpostuser int NOT NULL default 0,
  lastpostuser int NOT NULL default 0,
  firstpostname varchar(64),
  lastpostname varchar(64),
  firstpostdate varchar(20) NOT NULL default "0",
  lastpostdate varchar(20) NOT NULL default "0",
  x decimal(13,10),
  y decimal(13,10),
  z decimal(13,2),
  actorid int,
  actorname varchar(64),
  notifiedid int,
  notifiedname varchar(64),
  replies int NOT NULL default 0,
  views int NOT NULL default 0,
  modifdate varchar(20) NOT NULL default "0",
  param varchar(255),
  PRIMARY KEY (id)
  )';
  break;

case 'pdo.sqlsrv':
case 'sqlsrv':
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtitopic (
  id int NOT NULL CONSTRAINT pk_'.QDB_PREFIX.'qtitopic PRIMARY KEY,
  numid int NOT NULL default 0,
  section int NOT NULL default 0,
  type char(1) NOT NULL default "T",
  status char(1) NOT NULL default "A",
  statusdate varchar(20) NOT NULL default "0",
  wisheddate varchar(20) NOT NULL default "0",
  tags varchar(4000),
  firstpostid int NOT NULL default 0,
  lastpostid int NOT NULL default 0,
  firstpostuser int NOT NULL default 0,
  lastpostuser int NOT NULL default 0,
  firstpostname varchar(64) NULL,
  lastpostname varchar(64) NULL,
  firstpostdate varchar(20) NOT NULL default "0",
  lastpostdate varchar(20) NOT NULL default "0",
  actorid int NULL,
  actorname varchar(64) NULL,
  notifiedid int NULL,
  notifiedname varchar(64) NULL,
  x decimal(13,10) NULL,
  y decimal(13,10) NULL,
  z decimal(13,2) NULL,
  replies int NOT NULL default 0,
  views int NOT NULL default 0,
  modifdate varchar(20) NOT NULL default "0",
  param varchar(255) NULL
  )';
  break;

case 'pdo.pg':
case 'pg':
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtitopic (
  id integer,
  numid integer NOT NULL default 0,
  section integer NOT NULL default 0,
  type char(1) NOT NULL default "T",
  status char(1) NOT NULL default "A",
  statusdate varchar(20) NOT NULL default "0",
  wisheddate varchar(20) NOT NULL default "0",
  tags varchar(4000) NULL,
  firstpostid integer NOT NULL default 0,
  lastpostid integer NOT NULL default 0,
  firstpostuser integer NOT NULL default 0,
  lastpostuser integer NOT NULL default 0,
  firstpostname varchar(64) NULL,
  lastpostname varchar(64) NULL,
  firstpostdate varchar(20) NOT NULL default "0",
  lastpostdate varchar(20) NOT NULL default "0",
  actorid integer NULL,
  actorname varchar(64) NULL,
  notifiedid integer NULL,
  notifiedname varchar(64) NULL,
  x decimal(13,10) NULL,
  y decimal(13,10) NULL,
  z decimal(13,2) NULL,
  replies integer NOT NULL default 0,
  views integer NOT NULL default 0,
  modifdate varchar(20) NOT NULL default "0",
  param varchar(255) NULL,
  PRIMARY KEY (id)
  )';
  break;

case 'pdo.sqlite':
case 'sqlite':
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtitopic (
  id integer,
  numid integer NOT NULL default 0,
  section integer NOT NULL default 0,
  type text NOT NULL default "T",
  status text NOT NULL default "A",
  statusdate text default "0",
  wisheddate text default "0",
  tags text,
  firstpostid integer NOT NULL default 0,
  lastpostid integer NOT NULL default 0,
  firstpostuser integer NOT NULL default 0,
  lastpostuser integer NOT NULL default 0,
  firstpostname text,
  lastpostname text,
  firstpostdate text default "0",
  lastpostdate text default "0",
  actorid integer,
  actorname text,
  notifiedid integer,
  notifiedname text,
  x real,
  y real,
  z real,
  replies integer NOT NULL default 0,
  views integer NOT NULL default 0,
  modifdate text default "0",
  param text,
  PRIMARY KEY (id)
  )';
  break;

case 'pdo.oci':
case 'oci':
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtitopic (
  id number(32),
  numid number(32) default 0 NOT NULL,
  section number(32) default 0 NOT NULL,
  type char(1) default "T" NOT NULL,
  status char(1) default "A" NOT NULL,
  statusdate varchar2(20) default "0" NOT NULL,
  wisheddate varchar2(20) default "0" NOT NULL,
  tags varchar2(4000),
  firstpostid number(32) default 0 NOT NULL,
  lastpostid number(32) default 0 NOT NULL,
  firstpostuser number(32) default 0 NOT NULL,
  lastpostuser number(32) default 0 NOT NULL,
  firstpostname varchar2(64),
  lastpostname varchar2(64),
  firstpostdate varchar2(20) default "0" NOT NULL,
  lastpostdate varchar2(20) default "0" NOT NULL,
  x decimal(13,10),
  y decimal(13,10),
  z decimal(13,2),
  actorid int,
  actorname varchar2(64),
  notifiedid int,
  notifiedname varchar2(64),
  replies number(32) default 0 NOT NULL,
  views number(32) default 0 NOT NULL,
  modifdate varchar2(20) default "0" NOT NULL,
  param varchar2(255),
  CONSTRAINT pk_'.QDB_PREFIX.'qtitopic PRIMARY KEY (id))';
  break;

default:
  die("Database type [{$oDB->type}] not supported... Must be mysql, sqlsrv, pg, oracle, sqlite");

}

echo '<span style="color:blue;">';
$b = $oDB->exec($sql);
echo '</span>';

if ( !empty($oDB->error) || $b===false )
{
  echo '<div class="setup_err">',sprintf (L('E_install'),QDB_PREFIX.'qtitopic',QDB_DATABASE,QDB_USER),'</div>';
  echo '<br /><table class="button"><tr><td></td><td class="button" style="width:120px">&nbsp;<a href="setup_1.php">',L('Restart'),'</a>&nbsp;</td></tr></table>';
  exit;
}