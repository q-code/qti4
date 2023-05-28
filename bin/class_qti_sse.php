<?php // v4.0 build:20230430

/**
 * SMemSSE provides sse and specific shared-memory control
 * broadcasting sse info, or clearing shared-memory are trigered by several class-methods
 * self::control() For some class-methods, performs memory update/reset, collects and broadcasts info about the last modifications the class-method.
 * self::broadcast() broadcast information in shared-memory about the last modifications
 */

class SMemSSE {

private static bool $debug = false;

public static function useSSE()
{
  return (defined('SSE_CONNECT') && SSE_CONNECT); // true if >0
}
public static function control(string $event='', $data='', string $metadata='', bool $debug=false)
{
  if ( $debug ) self::$debug = true;
  if ( self::$debug ) var_dump(__METHOD__.' debugging is on');
  if ( self::$debug ) var_dump('event: '.$event);
  if ( self::$debug ) var_dump('useSSE: '.self::useSSE());
  if ( !self::useSSE() || empty($event) ) return;

  // $data is generally an array generated by a compact() where key was the variable name.
  // In some case $data can be CTopic or CPost object.

  // To force a page reload, broadcast on the section channel {"s":"reset"}
  $a = 0; // Action indicator used with Topic ONLY (0=update,1=insert,-1=delete)
  switch($event)
  {
  case 'CPost:insertPost':

    if ( !is_a($data,'CPost') ) break;
    // Fetch basic properties and last-post info
    $jsondata = '{"s":'.$data->section.',"t":'.$data->topic.',"replies":"+1","lastpostid":'.$data->id.',"lastpostdate":"'.date('H:i').'","lastpostuser":'.$data->userid.',"lastpostname":"'.$data->username.'"}';
    self::broadcast('reply',$jsondata,true);
    break;

  case 'CTopic:DeletePost':

    if ( !is_a($data,'CTopic') ) break;
    // Fetch basic properties and last-post info //!!!
    $jsondata = '{"s":'.$data->section.',"t":'.$data->topic.',"replies":'.$data->items.',"lastpostid":'.$data->id.',"lastpostdate":"'.date('H:i').'","lastpostuser":'.$data->userid.',"lastpostname":"'.$data->username.'"}';
    self::broadcast('reply',$jsondata,true);
    break;

  case 'CTopic:insertTopic':

    // data must be a CTopic object
    if ( !is_a($data,'CTopic') ) break;
    // Fetch basic properties and first/last-post info
    $imgsrc = CTopic::makeIconSrc($data->type,$data->status,QT_SKIN);
    $prefixsrc = CPost::getPrefixSrc($data->pid,$data->smile,QT_SKIN);
    $jsondata = '{"a":1,"s":'.$data->pid.',"t":'.$data->id.',"numid":"'.CTopic::getRef($data->numid,$data->pid).'","firstpostid":'.$data->firstpostid.',"lastpostid":'.$data->lastpostid.',"firstpostdate":"'.date('H:i').'","lastpostdate":"'.date('H:i').'","firstpostuser":'.$data->firstpostuser.',"lastpostuser":'.$data->lastpostuser.',"firstpostname":"'.$data->firstpostname.'","lastpostname":"'.$data->lastpostname.'","replies":'.$data->items.',"title":"'.$data->title.'","imgsrc":"'.$imgsrc.'","prefixsrc":"'.$prefixsrc.'"';
    $jsondata .= '}';
    self::broadcast('topic',$jsondata,true);
    break;

  case 'CTopic:setStatus':

    // data must be an array (section,topic,status,statusdate)
    if ( !is_array($data) ) break;
    // Fetch value
    if ( isset($data['section']) ) $s = (int)$data['section'];
    if ( isset($data['topic']) ) $t = (int)$data['topic'];
    if ( isset($data['status']) ) {
      $status = $data['status'];
      $type = isset($data['type']) ? $data['type'] : 'T';
      $imgsrc = CTopic::makeIconSrc($type,$status,QT_SKIN);
    }
    $statusdate = date('H:i');

    $jsondata = json_encode(compact('a','s','t','status','statusdate','imgsrc')); // Works also when variable are missing !
    self::broadcast('topic',$jsondata,true);
    break;

  case 'CTopic:setType':

    // data must be an array (section,topic,status,statusdate)
    if ( !is_array($data) ) break;
    if ( !isset($data['topic']) ) break; // [t] topic is mandatory
    // Fetch topic and type
    $s = isset($data['section']) ? (int)$data['section'] : -1; // [s] section is mandatory
    $t = (int)$data['topic'];
    $type = isset($data['type']) ? $data['type'] : 'T';
    $status = isset($data['status']) ? $data['status'] : 'A';
    $imgsrc = CTopic::makeIconSrc($type,$status,QT_SKIN);
    $jsondata = json_encode(compact('a','s','t','type','imgsrc','stamp')); // Works also when variable are missing !
    self::broadcast('topic',$jsondata,true);
    break;

  case 'CTopic:setActor':

    // data must be an array (section,topic,status,statusdate)
    if ( !is_array($data) ) break;
    if ( !isset($data['topic']) ) break; // [t] topic is mandatory
    // Fetch topic and type
    $s = isset($data['section']) ? (int)$data['section'] : -1; // [s] section is mandatory
    $t = (int)$data['topic'];
    if ( isset($data['actorid']) ) $actorid = $data['actorid'];
    if ( isset($data['actorname']) ) $actorname = $data['actorname'];
    if ( isset($data['replies']) ) $replies = $data['replies'];
    if ( isset($data['lastpostid']) ) $lastpostid = $data['lastpostid'];
    if ( isset($data['lastpostuser']) ) $lastpostuser = $data['lastpostuser'];
    if ( isset($data['lastpostname']) ) $lastpostname = $data['lastpostname'];
    if ( isset($data['lastpostdate']) ) $lastpostdate = $data['lastpostdate'];
    $jsondata = json_encode(compact('a','s','t','actorid','actorname','replies','lastpostid','lastpostuser','lastpostname','lastpostdate')); // Works also when variable are missing !
    self::broadcast('topic',$jsondata,true);
    break;

  case 'CSection:updateStats':

    // data must be an array (section,topic,status,statusdate)
    if ( !is_array($data) ) break;
    // Fetch section, items and replies
    if ( isset($data['section']) ) $s = (int)$data['section'];
    if ( isset($data['stats']) ) {
    $stats = qtExplode($data['stats']);
    if ( isset($stats['items']) ) $sumitems = (int)$stats['items'];
    if ( isset($stats['replies']) ) $sumreplies = (int)$stats['replies'];
    }
    $jsondata = json_encode(compact('s','sumitems','sumreplies')); // Works also when items or replies are missing !
    self::broadcast('section',$jsondata,true);
    break;

  case 'CDomain:Rename':
    Unset($GLOBALS['_L']['domain']);
    SMem::clear('_Sections');
    SMem::clear('_Domains');
    self::broadcast('section','{"s":"reset"}');
    break;

  case 'CDomain:Create':
    SMem::clear('_Domains');
    self::broadcast('section','{"s":"reset"}');
    break;

  case 'CDomain:Drop':
    SMem::clear('_Sections');
    SMem::clear('_Domains');
    $GLOBALS['_L'] = array();
    self::broadcast('section','{"s":"reset"}');
    break;

  }
}

private static function broadcast(string $event, string $jsondata, bool $append=false, int $timeout=30)
{
  if ( $timeout<5 ) $timeout = 30;
  $str = '{"event":"'.strtolower($event).'","data":'.$jsondata.'}';
  if ( $append ) {
    $old = SMem::get('_sse_'.$event);
    if ( $old!==false && $old!=='' ) {
      if ( substr($old,0,1)==='[' ) $old = substr($old,1);
      if ( substr($old,-1,1)===']' ) $old = substr($old,0,-1);
      $str = $old.','.$str;
    }
  }
  $res = SMem::set('_sse_'.$event, '['.$str.']', $timeout+1000 ); //!!!
  var_dump('res = ', $res);
  var_dump(SMem::get('_sse_'.$event));
}

}
