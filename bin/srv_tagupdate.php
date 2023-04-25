<?php // v4.0 build:20230205

// SERVEUR SCRIPT
// Perform async queries on request from web pages (ex: using ajax) with GET method
// Ouput (echo) results as string

session_start(); // uses session_id() for update security reason
include '../config/config_db.php';
if ( strpos(QDB_SYSTEM,'sqlite') ) define ('QDB_SQLITEPATH', '../');
define( 'QT', 'qti'.(defined('QDB_INSTALL') ? substr(QDB_INSTALL,-1) : '') );

if ( !isset($_GET['ref']) || $_GET['ref']!== MD5(QT.session_id()) ) { echo 'Unable to save tags'; exit; }
if ( !isset($_GET['id']) ) { echo 'Unable to save tags'; exit; }
if ( !isset($_GET['tag']) ) { echo 'Unable to save tags'; exit; }
if ( substr($_GET['tag'],-1,1)===';' ) $_GET['tag'] = substr($_GET['tag'],0,-1);

include 'class/class.qt.db.php';
function QTdropaccent(string $txt) {
  if ( empty($txt) ) return $txt;
  return strtr(utf8_decode($txt), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
}

// format input
$str = str_replace('"','',trim($_GET['tag'])); // trim and no doublequote
if ( substr($str,-1,1)===';' ) $str = substr($str,0,-1);
$str = QTdropaccent($str);
// query (without table constants)
$oDBAJAX = new CDatabase();
if ( !empty($oDBAJAX->error) ) { echo 'Unable to save tags'; exit; }
$oDBAJAX->exec( "UPDATE ".QDB_PREFIX."qtitopic SET tags=?,modifdate='".date('Ymd His')."' WHERE id=".$_GET['id'], [$str] );

echo empty($oDBAJAX->error) ? $str : $oDBAJAX->error;