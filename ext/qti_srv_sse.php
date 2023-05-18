<?php
header('Content-Type: text/event-stream');
header('Cache-control: no-cache');
$debug = true; // allows broadcasting specific 'debug'-event message that is catched by the client and displayed in the console log

// This script plays the role of Server for sse communications.
// It reads information from the memcache and send sse-message (json) to the client.
// Note:
// This script required the sid (include as a GET parametre)
// This script is completely isolated (independant from qti classes or variables) this script uses PHP memcache-class (not the class_qt_mem nor DB query)
// This script required the memcache constants (ext/config_mem.php)

// -------
// Library
// -------

function sse_echo(string $msg='data: ping', int $retry=10000)
{
  // Send a message-event (or error) to the client. Messages are visible in the client's java console.
  if ( empty($msg) ) return;
  echo ($retry>0 ? 'retry: '.$retry.PHP_EOL : '').$msg; // to apply retry delay, 'retry:' (milliseconds) must be placed BEFORE the stream
  ob_flush();
  flush();
}

// -------
// Checks sid and retry
// -------

if ( !isset($_GET['sid']) || substr($_GET['sid'],0,3)!=='qti' ) { sse_echo( 'event: error'.PHP_EOL.'data: The client do not provide the sid argument'.PHP_EOL.PHP_EOL, 0 ); exit; }

define('QT',$_GET['sid']);

if ( $debug ) sse_echo('event: debug'.PHP_EOL.'data: server assign sid from GET request'.PHP_EOL);

if ( !isset($_GET['retry']) || !is_numeric($_GET['retry']) || (int)$_GET['retry']<1 ) { sse_echo( 'event: error'.PHP_EOL.'data: Invalid retry delay'.PHP_EOL.PHP_EOL, 0 ); exit; }
$retry = ((int)$_GET['retry'])*1000; // use milliseconds

if ( $debug ) sse_echo('event: debug'.PHP_EOL.'data: server assign retry-delay to '.$retry.' milliseconds from GET request'.PHP_EOL);

// -------
// Checks memcache
// -------

include 'config_mem.php'; // config_mem must be in the same directory as qti_srv_sse.php. This generate a warning is file is missing.

if ( !defined('MEMCACHE_HOST') || empty(MEMCACHE_HOST) ) { sse_echo( 'event: error'.PHP_EOL.'data: Memcache configuration missing or memcache disabled'.PHP_EOL.PHP_EOL,0 ); exit; }

// detect library
if ( class_exists('Memcached') ) {
  $memcache = new Memcached(); // use addServer() to connect
  $fx_con = 'addServer';
} elseif ( class_exists('Memcache') ) {
  $memcache = new Memcache; // use connect() to connect
  $fx_con = 'connect';
} else {
  sse_echo( 'event: error'.PHP_EOL.'data: Memcache/Memcached library not found on the webserver'.PHP_EOL.PHP_EOL, 0 ); exit;
}

// connect
if ( !$memcache->$fx_con(MEMCACHE_HOST,MEMCACHE_PORT) ) { sse_echo( 'event: error'.PHP_EOL.'data: Unable to contact Memcache daemon ['.MEMCACHE_HOST.'] port []'.PHP_EOL.PHP_EOL, 0 ); exit; }

if ( $debug ) sse_echo('event: debug'.PHP_EOL.'data: server uses '. get_class($memcache).' daemon on host '.MEMCACHE_HOST.':'.MEMCACHE_PORT.PHP_EOL);

// -------
// SSE Broacasting
// -------

// broadcast 3 shared memories (if they contain values) on each connection request comming from the client.
// The default timelap to retry connection (10 seconds) is included in the broadcasted message.

$isBroadcasted = false;
foreach(['section','topic','reply'] as $memory) {

  if ( $debug ) sse_echo('event: debug'.PHP_EOL.'data: server checks shared-memory for '.$memory.'-entry'.PHP_EOL);

  $m = $memcache->get(QT.'_sse_'.$memory); // read last actions
  if ( $m===false ) continue;

  if ( $debug ) sse_echo('event: debug'.PHP_EOL.'data: server processes '.QT.'_sse_'.$memory.' entry'.PHP_EOL);

  if ( substr($m,0,1)!=='[' ) $m = '['.$m.']';
  if ( $debug ) sse_echo('event: debug'.PHP_EOL.'data: dataset is '.$m.PHP_EOL);

  $jd = json_decode($m,true);
  if ( count($jd)>9 ) $memcache->delete(QT.'_sse_'.$memory); // garbadge collector released
  foreach($jd as $j) {
    $event = isset($j['event']) ? $j['event'] : '';
    $data = isset($j['data']) ? json_encode($j['data']) : '';
    if ( empty($event) && empty($data) ) {
      $msg = 'event: error'.PHP_EOL.'data: memcache structure unknown in key '.QT.'_sse_'.$memory.PHP_EOL;
    } else {
      $msg = (empty($event) ? '' : 'event: '.$event.PHP_EOL).'data: '.$data.PHP_EOL;
    }
    $isBroadcasted = true;
    sse_echo($msg,$retry);
    $memcache->delete(QT.'_sse_noevent');
  }

}

// When there is nothing in the shared memories, we send a simple message 'no event'
if ( !$isBroadcasted )
{
  if ( $retry < 20000 ){
    $end = $memcache->get(QT.'_sse_noevent');
    if ( $end===false ) { $end = time()+60; $memcache->set(QT.'_sse_noevent',$end); }
    if ( time() > $end ) {
      $retry = 30000;
      if ( time() > $end+240 ) $memcache->delete(QT.'_sse_noevent');
    }
  }
  // log no event
  sse_echo( 'data: no event, retry in '.($retry/1000).'s'.PHP_EOL.PHP_EOL, $retry);
}
