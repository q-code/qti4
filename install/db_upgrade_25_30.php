<?php

// v4.0 build:20230205

session_start();

include '../config/config_db.php';
include '../bin/class/class.qt.db.php';

$oDB = new CDatabase();
if ( !empty($oDB->error) ) die ('<p style="color:red">Connection with database failed.<br />Check that server is up and running.<br />Check that the settings in the file <b>bin/config.php</b> are correct for your database.</p>');

$oDB->debug=true;

$oDB->exec( 'UPDATE '.QDB_PREFIX.'qtisetting SET setting="3.0" WHERE param="version"');
$oDB->exec( 'UPDATE '.QDB_PREFIX.'qtisetting SET param="show_welcome" WHERE param="sys_welcome"');
$oDB->exec( 'UPDATE '.QDB_PREFIX.'qtisetting SET param="posts_per_item" WHERE param="posts_per_topic"');
$oDB->exec( 'UPDATE '.QDB_PREFIX.'qtisetting SET param="items_per_page" WHERE param="topics_per_page"');
echo '<p>Database upgraded to 3.0</p>';