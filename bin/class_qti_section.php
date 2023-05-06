<?php // v4.0 build:20230430

/*
 COMPATIBILITY REQUIREMENTS:
 Methods uses typed-arguments (basic types)
 Method typed-return and typed-properties are NOT used to stay compatible with php 7.1 (typed-properties requires php 7.4, method typed-return requires php 8, mixed or pseudo-types requires php 8)
 */

class CSection extends AContainer implements IContainer
{

// AContainer properties:
// id,pid,title,descr,type,status,items
public $ptitle='';      // Domain name
public $notify=1;        // Notify: 0=disable, 1=enabled
public $ownerid=1;        // Moderator id
public $ownername='Admin'; // Moderator name
public $numfield='N';    // Format of the ref number: 'N' means no ref number
public $titlefield=1;    // Topic title: 0=None, 1=Optional, 2=Mandatory
//public $eventdate=0;   // Topic eventdate: 0=None, 1=Optional, 2=Mandatory, 3=Date+time (optional), 4=Date+time (mandatory)
public $wisheddate=0;    // Topic wisheddate: 0=None, 1=Optional, 2=Mandatory
public $notifycc=0;      // Topic alternate notify: 0=None, 1=Optional, 2=Mandatory
public $prefix='a';      // Prefix icon from the serie 'a'
public $options='';      // Several options
public $stats='';        // Several statistics
// computed values
public $lastpostid=-1;
public $lastpostpid=-1;
public $lastpostdate='0';
public $lastpostuser=0;
public $lastpostname='Visitor';
// stats ($items already in AContainer)
/* tbd !!! */
public $itemsZ=0;    // Total topics closed (including news)
public $replies=0;   // Total replies (type R and F), not D
public $repliesZ=0;  // Total replies in topics closed (type R,F or  D)
public $tags=0;      // Number of distinct tags on items in this section

function __construct($ref=null, bool $translate=false)
{
  $this->setFrom($ref);
  if ( $translate ) {
    $this->title = SLang::translate('sec', 's'.$this->id, $this->title);
    $this->descr = SLang::translate('secdesc', 's'.$this->id, '');
    $this->ptitle = SLang::translate('domain', 'd'.$this->pid, empty($GLOBALS['_Domains'][$this->pid]['title']) ? '(domain-'.$this->pid.')' : $GLOBALS['_Domains'][$this->pid]['title'] );
  }
  if ( isset($GLOBALS['_SectionsStats'][$this->id]) ) {
    $this->items = isset($GLOBALS['_SectionsStats'][$this->id]['items']) ? $GLOBALS['_SectionsStats'][$this->id]['items'] : 0;
    $this->replies = isset($GLOBALS['_SectionsStats'][$this->id]['replies']) ? $GLOBALS['_SectionsStats'][$this->id]['replies'] : 0;
    $this->lastpostid = isset($GLOBALS['_SectionsStats'][$this->id]['lastpostid']) ? $GLOBALS['_SectionsStats'][$this->id]['lastpostid'] : -1;
    $this->lastpostpid = isset($GLOBALS['_SectionsStats'][$this->id]['lastpostpid']) ? $GLOBALS['_SectionsStats'][$this->id]['lastpostpid'] : -1;
    $this->lastpostdate = isset($GLOBALS['_SectionsStats'][$this->id]['lastpostdate']) ? $GLOBALS['_SectionsStats'][$this->id]['lastpostdate'] : '';
    $this->lastpostuser = isset($GLOBALS['_SectionsStats'][$this->id]['lastpostuser']) ? $GLOBALS['_SectionsStats'][$this->id]['lastpostuser'] : -1;
    $this->lastpostname = isset($GLOBALS['_SectionsStats'][$this->id]['lastpostname']) ? $GLOBALS['_SectionsStats'][$this->id]['lastpostname'] : '';
  }
}
// --------
// IContainer methods
// --------
public function setFrom($ref=null)
{
  // $ref can be [null|int|array|obj-class], otherwhise die
  if ( $ref===null ) return; //... exit with void-instance (default properties)
  if ( is_int($ref) ) {
    if ( $ref<0 ) die(__METHOD__.' Argument must be positive');
    $oDB = new CDatabase();
    $oDB->query( "SELECT s.*,d.title as ptitle FROM TABSECTION s INNER JOIN TABDOMAIN d ON d.id=s.domainid WHERE s.id=$ref" );
    $row = $oDB->getRow(); if ( $row===false ) die(__METHOD__.' No domain '.$ref);
    $ref = $row; // continue as array
  }
  if ( is_array($ref) ) {
    foreach($ref as $k=>$value) {
      switch((string)$k)
      {
        case 'id':           $this->id        = (int)$value; break;
        case 'title':        $this->title     = (string)$value; break;
        case 'domainid':
        case 'pid':          $this->pid       = (int)$value; break;
        case 'ptitle':       $this->ptitle    = (string)$value; break;
        case 'type':         $this->type      = (string)$value; break;
        case 'status':       $this->status    = (string)$value; break;
        case 'options':      $this->options   = (string)$value; break;
        case 'stats':        $this->stats     = (string)$value; break;
        case 'notify':       $this->notify    = (int)$value; break;
        case 'ownerid':
        case 'moderator':    $this->ownerid   = (int)$value; break;
        case 'ownername':
        case 'moderatorname':$this->ownername = (string)$value; break;
        case 'numfield':     $this->numfield  = (string)$value; break;
        case 'titlefield':   $this->titlefield= (int)$value; break;
        case 'wisheddate':   $this->wisheddate= (int)$value; break;
        case 'alternate':    $this->notifycc  = (int)$value; break;
        case 'prefix':       $this->prefix    = (string)$value; break;
      } // Unit test: $k must be [string] otherwhise key 0 can change the first case (0=='id')
    }
    return; //...
  }
  if ( is_a($ref,'CSection') ) return $this->setFrom(get_object_vars($ref)); //...
  die(__METHOD__.' Invalid argument type');
}
public static function create(string $title='untitled', int $pid=-1, bool $uniquetitle=true)
{
  if ( empty($title) || $pid<0 ) die('CSection::create invalid argument');
  global $oDB;
  // unique title
  if ( $uniquetitle && $oDB->count( TABSECTION." WHERE domainid=$pid AND title=?", [qtDb($title)] )>0 ) throw new Exception( L('Name').' '.L('already_used') );
  // create
  $id = $oDB->nextId(TABSECTION);
  $oDB->exec( "INSERT INTO TABSECTION (id,domainid,title,stats,options,prefix) VALUES ($id,$pid,?,'','coord=0;order=0;last=0;logo=0','a')", [qtDb($title)] );
  SMem::clear('_Sections'); // clear cache
  return $id;
}
public static function delete(int $id=-1)
{
  if ( $id<0 ) die('CSection::delete invalid argument'); // section 0 can be deleted
  global $oDB;
  CSection::deleteItems($id);
  $oDB->exec( 'DELETE FROM TABSECTION WHERE id='.$id);
  SLang::delete('sec,secdesc','s'.$id);
  SMem::clear('_Sections'); // clear cache
  memFlushLang();
}
public static function rename(int $id=-1, string $title='untitled')
{
  if ( $id<0 ) die('CSection::rename Argument #1 must be an integer');
  if ( empty($title) ) die('CSection::rename Argument #2 must be a string');
  global $oDB;
  $oDB->exec( 'UPDATE TABSECTION SET title=? WHERE id='.$id, [qtDb($title)] );
  SMem::clear('_Sections'); // clear cache
}
public static function getOwner(int $id)
{
  global $oDB;
  $oDB->query( "SELECT ownerid FROM TABSECTION WHERE id=$id" );
  $row=$oDB->getRow();
  return (int)$row['ownerid'];
}
// --------
// Other methods
// --------
public function logo(string $alt='')
{
  return CSection::makeLogo($this->getMF('options','logo',$alt), $this->type, $this->status);
}
public static function translate(int $id, string $type='sec')
{
  // returns translated title (from session memory), uses config name if no translation
  switch($type){
    case 'sec': return SLang::translate('sec', 's'.$id, empty($GLOBALS['_Sections'][$id]['title']) ? '' : $GLOBALS['_Sections'][$id]['title']);
    case 'secdesc': return SLang::translate('secdesc', 's'.$id, empty($GLOBALS['_Sections'][$id]['descr']) ? '' : $GLOBALS['_Sections'][$id]['descr']);
    default: die(__FUNCTION__.' invalid argument #2');
  }
}
/**
 * @param int $id
 * @param array|string $attr
 * @param string $altSrc alternate image source (or '')
 * @return string img tag (can be '' when $altSrc is empty and image not found)
 */
public static function getImage(int $id=0, $attr=[], string $altSrc=''){
  if ( is_string($attr) ) $attr = attrDecode($attr);
  if ( !is_array($attr) || isset($attr['src']) ) die(__METHOD__.' invalid attr');
  if ( !isset($attr['alt']) ) $attr['alt'] = $id;
  $path = QT_DIR_DOC.'section/';
  if ( empty($path) ) return empty($altSrc) ? '' : '<img src="'.$altSrc.'"'.attrRender($attr).'/>';
  $src = '';
  foreach(['.jpg','.jpeg','.png','.gif'] as $mime) if ( file_exists($path.$id.$mime) ) { $src = $path.$id.$mime; break; }
  if ( empty($src) ) return empty($altSrc) ? '' : '<img src="'.$altSrc.'"'.attrRender($attr).'/>';
  return '<img src="'.$src.'"'.attrRender($attr).'/>';
}
public static function deleteImage($ids){
  if ( is_int($ids) ) $ids = array($ids);
  if ( !is_array($ids) ) die(__METHOD__.' arg#1 must be an id or array of id');
  foreach($ids as $id) {
    if ( !is_int($id) ) die(__METHOD__.' arg#1 must be an id or array of id');
    $dir = QT_DIR_DOC.'section/';
    if ( is_dir($dir) ) foreach(array('.jpg','.jpeg','.png','.gif') as $ext) if ( file_exists($dir.$id.$ext) ) unlink($dir.$id.$ext);
  }
}
public static function makeLogo(string $src='', string $type='0', string $status='0')
{
  return !empty($src) && file_exists(QT_DIR_DOC.'section/'.$src) ? QT_DIR_DOC.'section/'.$src : QT_SKIN.'img/section_'.$type.'_'.$status.'.gif';
}
private function getSectionLastPost()
{
  // Initialize
  $this->lastpostid = null;
  $this->lastpostpid = null;
  $this->lastpostdate = null;
  $this->lastpostuser = null;
  $this->lastpostname = null;

  // Check
  if ( $this->id<0 ) return null;

  // Search (maxid)
  global $oDB;
  $intId = $oDB->count( "SELECT MAX(id) as countid FROM TABPOST WHERE section=".$this->id );

  // Get lastpost informations
  if ( $intId>0 )
  {
    $oDB->query( 'SELECT id,topic,issuedate,userid,username FROM TABPOST WHERE id='.$intId );
    if ( $row=$oDB->getRow() )
    {
    $this->lastpostid = (int)$row['id'];
    $this->lastpostpid = (int)$row['topic'];
    $this->lastpostdate = $row['issuedate'];
    $this->lastpostuser = (int)$row['userid'];
    $this->lastpostname = $row['username'];
    }
  }
}
public static function getIdsInContainer(int $pid)
{
  global $oDB;
  $oDB->query( "SELECT id FROM TABSECTION WHERE domainid=$pid" );
  $ids = [];
  while( $row=$oDB->getRow() ) $ids[] = (int)$row['id'];
  return $ids;
}
public static function getSectionsStats(bool $closed=false)
{
  // Returns items and replies by section
  // Array also includes a 'all' key, containing the sums
  $arr = ['all'=>['items'=>0,'replies'=>0,'itemsZ'=>0,'repliesZ'=>0,'lastpostid'=>-1,'lastpostpid'=>-1,'lastpostdate'=>'','lastpostuser'=>-1,'lastpostname'=>'']];
  global $oDB;
  $oDB->query( "SELECT section,count(id) as items,sum(replies) as replies FROM TABTOPIC GROUP BY section" );
  while($row=$oDB->getRow()) {
    $id = (int)$row['section'];
    $i = empty($row['items']) ? 0 : (int)$row['items'];
    $arr[$id]['items'] = $i;
    $arr['all']['items'] += $i;
    $i = empty($row['replies']) ? 0 : (int)$row['replies'];
    $arr[$id]['replies'] = $i;
    $arr['all']['replies'] += $i;
  }
  if ( $closed ) {
    $oDB->query( "SELECT section,count(id) as items,sum(replies) as replies FROM TABTOPIC WHERE status='1' GROUP BY section" );
    while($row=$oDB->getRow()) {
      $id = (int)$row['section'];
      $i = empty($row['items']) ? 0 : (int)$row['items'];
      $arr[$id]['itemsZ'] = $i;
      $arr['all']['itemsZ'] += $i;
      $i = empty($row['replies']) ? 0 : (int)$row['replies'];
      $arr[$id]['repliesZ'] = $i;
      $arr['all']['repliesZ'] += $i;
    }
  }
  $oDB->query( "SELECT section,max(id) as lastpostid,topic,userid,username,issuedate FROM TABPOST GROUP BY section" );
  while($row=$oDB->getRow()) {
    $id = (int)$row['section'];
    $arr[$id]['lastpostid'] = (int)$row['lastpostid'];
    $arr[$id]['lastpostpid'] = (int)$row['topic'];
    $arr[$id]['lastpostdate'] = $row['issuedate'];
    $arr[$id]['lastpostuser'] = (int)$row['userid'];
    $arr[$id]['lastpostname'] = $row['username'];
    if ( $arr[$id]['lastpostid']>$arr['all']['lastpostid'] )
      foreach(['lastpostid','lastpostpid','lastpostdate','lastpostuser','lastpostname'] as $k) $arr['all'][$k] = $arr[$id][$k];
  }
  return $arr;
}
public static function getTranslatedTitles(array $ids=[])
{
  if ( count($ids)===0 ) $ids = array_keys($GLOBALS['_Sections']); // empty list means all sections
  $arr = [];
  foreach($ids as $id) {
    $arr[$id] = SLang::translate('sec', 's'.$id, empty($GLOBALS['_Sections'][$id]['title']) ? '' : $GLOBALS['_Sections'][$id]['title']);
  }
  return $arr;
}
public static function getLastPost(int $id=0)
{
  // Returns the LastPost attributes (with keys lastpostid lastpostpid lastpostdate lastpostuser lastpostname
  $arr = array('lastpostid'=>-1,'lastpostpid'=>-1,'lastpostdate'=>'','lastpostuser'=>-1,'lastpostname'=>'');
  global $oDB; $oDB->query( 'SELECT topic,issuedate,userid,username FROM TABPOST WHERE id='.$id );
  while( $row=$oDB->getRow() ) {
    $arr['lastpostid'] = $id;
    $arr['lastpostpid'] = (int)$row['topic'];
    $arr['lastpostdate'] = $row['issuedate'];
    $arr['lastpostuser'] = (int)$row['userid'];
    $arr['lastpostname'] = $row['username'];
  }
  return $arr;
}
public static function getTagsUsed(int $s=-1, int $intMax=20, string $sqlWhere='')
{
  // -1 to compute on all sections
  // maximum returned is $intMax distinct tags (from most recents active messages)
  // Attention, if defined, $strWhere overwrites $s
  // prepare where clause if not provided
  if ( empty($sqlWhere) ) $sqlWhere = ' WHERE '.($s<0 ? 't.section>=0' : 't.section='.$s);

  // Process

  $arrTags = array();
  global $oDB;
  $oDB->query( 'SELECT DISTINCT t.tags,t.lastpostdate FROM TABTOPIC t '.$sqlWhere.' ORDER BY t.lastpostdate DESC' );
  $i=0;
  while($row=$oDB->getRow()) {
  if ( !empty($row['tags']) ) {

    $arr = explode(';',$row['tags']);
    foreach($arr as $str)
    {
      if ( !empty($str) ) {
      if ( !in_array($str,$arrTags) ) {
        $arrTags[$str] = $str;
        $i++;
        if ( $i>$intMax ) break;
      }}
    }
    if ( $i>$intMax ) break;

  }}
  if ( count($arrTags)>2 ) asort($arrTags);
  return $arrTags;
}
public static function moveAllItems(int $s, int $dest, int $renum=1, bool $dropprefix=true, string $status='', string $type='', string $year='', string $where='')
{
  if ( $s<0 || $dest<0 || $s==$dest ) die('CSection::moveAllItems invalid argument $s or $dest');
  // renumbering method: 0=reset to 0, 1=Keep same number, 2=Increment
  // criteria
  if ( $status==='*' || $status==='-1' || $status==='all' ) $status='';
  if ( $type==='*' || $type==='-1' || $type==='all' ) $type='';
  if ( $year==='*' || $year==='-1' || $year==='all' ) $year=''; // year can also be '*','all' or 'old'
  if ( strlen($year)>4 ) die('CSection::moveAllItems: arg #7 must be string');
  // build sql criteria
  $strS = 'section='.$s;
  $strD = 'section='.$dest;
  if ( $status!=='' ) $status = ' AND status="'.$status.'"';
  if ( $type!=='' ) $type = ' AND type="'.$type.'"';
  if ( $year!=='' ) $year = ' AND '.sqlDateCondition($year);
  $prefix = $dropprefix ? ',icon="00"' : '';
  global $oDB;
  $strNum = $renum===0 ? ', numid=0' : '';
  if ( $renum==2 )
  {
    $nextnumid = $oDB->nextId(TABTOPIC,'numid','WHERE '.$strD);
    $oDB->query( "SELECT MIN(numid) as minnumid FROM TABTOPIC WHERE $strS" );
    $row = $oDB->getRow();
    $minnumid = $row['minnumid'];
    $strNum = ", numid = $nextnumid + (numid - $minnumid)";
  }
  // Update topics and posts
  $oDB->exec( "UPDATE TABTOPIC SET $strD $strNum, modifdate='".date('Ymd His')."' WHERE ".$strS.$type.$status.$year.$where );
  $oDB->exec( "UPDATE TABPOST SET $strD $prefix WHERE ".$strS.str_replace('firstpostdate','issuedate',$year).$where );
  // Mem
  memFlush(); memFlushStats();
}
public static function moveItems($ids, int $dest=0, int $renum=1, bool $dropprefix=false)
{
  // $ids can be an int, an array of int, or string csv, or a array of numeric
  if ( is_int($ids) ) $ids=array($ids);
  if ( is_string($ids) ) $ids=explode(';',$ids);
  if ( !is_array($ids) ) die('CSection->moveItems: Argument #1 must be list of ids');
  foreach($ids as $id) if ( !is_numeric($id) ) die('CSection->moveItems: Argument #1 must be list of ids');
  if ( $dest<0 ) die('CSection->moveItems: Argument #2 must be int'); // destination section

  switch($renum)
  {
  case 2: $strNum = ',numid=(SELECT MAX(numid)+1 FROM TABTOPIC WHERE section='.$dest.')'; break;
  case 0: $strNum = ',numid=0'; break;
  default: $strNum = '';
  }

  // Update topics and posts
  global $oDB;
  $oDB->exec( 'UPDATE TABTOPIC SET section='.$dest.$strNum.',modifdate="'.date('Ymd His').'" WHERE id IN ('.implode(',',$ids).')' );
  $oDB->exec( 'UPDATE TABPOST SET section='.$dest.($dropprefix ? ',icon="00"' : '').' WHERE topic IN ('.implode(',',$ids).')' );
  // Mem
  memFlush(); memFlushStats();
}
public static function deleteItems(int $id=-1, string $status='', string $type='', string $year='', string $where='', bool $onlyReplies=false, bool $dropAttachs=true)
{
  // year can also be '*','all' or 'old'
  if ( $status==='*' || $status==='-1' || $status==='all' ) $status=''; // '1' means status closed
  if ( $type==='*' || $type==='-1' || $type==='all' ) $type='';
  if ( $year==='*' || $year==='-1' || $year==='all' ) $year='';
  if ( strlen($year)>4 ) die('CSection::deleteItems: arg #5 must be string');
  // build sql criteria
  $s = $id<0 ? 'section>=0' : 'section='.$id;
  if ( $status!=='' ) $status = " AND status='$status'";
  if ( $type!=='' ) $type = " AND type='$type'";
  if ( $year!=='' ) $year = " AND ".sqlDateCondition($year);

  global $oDB;
  if ( $status.$type.$year.$where==='' ) {
    // purge the section
    if ( $onlyReplies ) {
      if ( $dropAttachs ) CPost::dropAttachSql( "SELECT id,attach FROM TABPOST WHERE attach<>'' AND $s AND type='R'", false );
      $oDB->exec( "DELETE FROM TABPOST WHERE $s AND type='R')" );
    } else {
      if ( $dropAttachs ) CPost::dropAttachSql( "SELECT id,attach FROM TABPOST WHERE attach<>'' AND $s", false );
      $oDB->exec( "DELETE FROM TABPOST WHERE ".$s );
      $oDB->exec( "DELETE FROM TABTOPIC WHERE ".$s );
    }
  } else {
    // delele based on criteria
    if ( $onlyReplies ) {
      if ( $dropAttachs ) CPost::dropAttachSql( "SELECT id,attach FROM TABPOST WHERE attach<>'' AND $s AND type='R' AND topic IN (SELECT id FROM TABTOPIC WHERE $s $status $type $year $where)", false );
      $oDB->exec( "DELETE FROM TABPOST WHERE $s AND type='R' AND topic IN (SELECT id FROM TABTOPIC WHERE $s $status $type $year $where)" );
    } else {
      if ( $dropAttachs ) CPost::dropAttachSql( "SELECT id,attach FROM TABPOST WHERE attach<>'' AND $s AND topic IN (SELECT id FROM TABTOPIC WHERE $s $status $type $year $where)", false );
      $oDB->exec( "DELETE FROM TABPOST WHERE $s AND topic IN (SELECT id FROM TABTOPIC WHERE $s $status $type $year $where)" );
      $oDB->exec( "DELETE FROM TABTOPIC WHERE ".$s.$status.$type.$year.$where );
    }
  }
  memFlush( empty($year) ? 'default' : array((int)$year) );
  return true;
}
public static function sqlCountItems($s, string $q='topics', string $status='',string $type='', string $year='', int $days=10, string $where='')
{
  // $q can be a specific queries (where other arguments may not applied). See switch
  // year can also be '*','all' or 'old'
  // "unreplied" are items opened and without reply since $days (note: can of type news)
  // defaults
  if ( $status==='*' || $status==='-1' || $status==='all' ) $status='';
  if ( $type==='*' || $type==='-1' || $type==='all' ) $type='';
  if ( $year==='*' || $year==='-1' || $year==='all' ) $year='';
  if ( strlen($year)>4 ) die('CSection::sqlCountItems: arg #5 must be string');
  if ( !is_string($where) ) die('CSection::sqlCountItems: arg #5 must be string');
  if ( $days<1 ) die('CSection::sqlCountItems: Wrong argument #2 (d<1)');
  // Items Alias
  if ( $q==='*' || empty($q) || $q==='-1' ) $q='topics';
  if ( $q=='unreplied' ) { $status='0'; $year=''; $where.= " AND t.replies=0 AND t.firstpostdate<'".addDate(date('Ymd His'),-$days,'day')."'"; }
  if ( $q=='tags' ) { $where.= " AND t.tags<>''"; } // count items having tag(s)
  // build sql criteria
  $s = $s==='*' || $s==='' || $s==='-1' ? 'section>=0' : 'section='.$s; // add p. or t. in the query
  if ( $status!=='' ) $status = " AND t.status='$status'";
  if ( $type!=='' ) $type = " AND t.type='$type'";
  if ( $year!=='' ) $year = " AND ".sqlDateCondition($year,'p.issuedate');

  switch($q)
  {
    // standard query and alias
    case 'items':
    case 'topics':
    case 'unreplied':
    case 'tags':     $year = str_replace('p.issuedate','t.firstpostdate',$year);
    return "TABTOPIC t WHERE t.$s $status $type $year $where";
    // specific queries
    case 'attachs':  if ( $type.$status.$year.$where==='' ) return "TABPOST p WHERE p.attach<>'' AND p.$s";
    return "TABPOST p INNER JOIN TABTOPIC t ON p.topic=t.id WHERE p.attach<>'' AND p.$s $type $year $where";
    case 'replies':  return "TABPOST p WHERE p.$s AND type<>'P' $year $where";
    case 'repliesZ': return "TABPOST p INNER JOIN TABTOPIC t ON p.topic=t.id WHERE p.$s AND p.type<>'P' AND t.status='1' $where";
    case 'messages': return "TABPOST p WHERE p.$s $where";
    default: die('CSection::sqlCountItems: Wrong argument (q) '.$q);
  }
}
public static function getPropertiesAll(string $order='d.titleorder,s.titleorder')
{
  // Returns an array of all objects properties
  global $oDB;
  $arr = array();
  $oDB->query( "SELECT s.* FROM TABSECTION s INNER JOIN TABDOMAIN d ON s.domainid=d.id ORDER BY $order" );
  while($row=$oDB->getRow()) {
    $oS = new CSection($row);
    // title,descr,ptitle are not translated
    // items,replies,lastpost are recomputed
    $arr[$oS->id] = (array)$oS;
  }
  return $arr;
}
public function updLastPostDate()
{
  global $oDB;
  if ( in_array(QDB_SYSTEM,array('pdo.sqlite','sqlite','pdo.sqlsrv','sqlsrv','pdo.pg','pg')) )
  {
  $oDB->exec( 'UPDATE TABTOPIC SET lastpostdate=(SELECT MAX(issuedate) FROM TABPOST p, TABTOPIC t WHERE t.id=p.topic) WHERE section='.$this->id );
  }
  else
  {
  $oDB->exec( 'UPDATE TABTOPIC t SET t.lastpostdate=(SELECT MAX(p.issuedate) FROM TABPOST p WHERE t.id=p.topic) WHERE t.section='.$this->id );
  }
}
public function updStats($arrValues=array(),$bLastPostDate=false,$bReplies=false)
{
  if ( $this->id<0 ) die('CSection::updStats Wrong id');

  // Process (provided values are not recomputed)
  global $oDB;
  if ( !isset($arrValues['topics']) )   $arrValues['topics']  = $oDB->count( CSection::sqlCountItems($this->id,'topics') );
  if ( !isset($arrValues['replies']) )  $arrValues['replies'] = $oDB->count( CSection::sqlCountItems($this->id,'replies') );
  if ( !isset($arrValues['tags']) )     $arrValues['tags']    = $oDB->count( CSection::sqlCountItems($this->id,'tags') );
  if ( !isset($arrValues['itemsZ']) )  $arrValues['itemsZ'] = $oDB->count( CSection::sqlCountItems($this->id,'topics','1') );
  if ( !isset($arrValues['repliesZ']) ) $arrValues['repliesZ']= $oDB->count( CSection::sqlCountItems($this->id,'repliesZ') );

  $this->stats = qtImplode($arrValues,';');
  $this->updateMF('stats'); // also send sse
  // Update lastpostdate or replies of EACH item in this section
  if ( $bLastPostDate ) CSection::updLastPostDate($this->id); // used after import or prune
  if ( $bReplies ) CSection::updEachItemReplies($this->id);  // used after import

  return $this->stats;
}
public function updEachItemReplies()
{
  global $oDB;
  $oDB->exec( "UPDATE TABTOPIC SET replies=(SELECT COUNT(*) FROM TABPOST WHERE TABTOPIC.id=TABPOST.topic AND TABPOST.type<>'P') WHERE section=$this->id" );
}

// --------
// Multifield implementation
// --------
/**
 * Read the multivalues-property $prop (or a ini-string) and return an array [key=>value]
 * @param string $prop name of the property (can also be a ini-string)
 * @param boolean $assign assign the key-values as object properties
 * @param string $prefix add a prefix to the key to match the porperty-name
 * @return array of key-value (can be an empty array if property is empty)
 */
public function readMF(string $prop, bool $assign=false, string $prefix='')
{
  if ( empty($prop) || !property_exists('CSection', $prop) )  die('CSection::readMF invalid property');
  $arr = qtExplode($this->$prop); // can be [] when property is empty
  if ( $assign )
  {
    foreach($arr as $key=>$value)
    {
      $key = $prefix.$key; if ( $key===$prop ) continue; // prevent reassigning $this->$prop (only other property name can be red)
      if ( property_exists('CSection', $key) ) $this->$key=$value;
    }
  }
  return $arr;
}
/**
 * Get a specific $key value from the multivalues-property $prop (or $na if the key does not exist)
 * @param string $prop the multivalues-property
 * @param string $key
 * @param mixed $alt
 * @return string or [mixed] $alt
 */
public function getMF(string $prop,string $key, $alt='')
{
  if ( empty($key) )  die('CSection::readMF invalid key');
  $arr = $this->readMF($prop,false); // read without properties assignement (also checks $this->$prop exists)
  return isset($arr[$key]) ? $arr[$key] : $alt;
}
/**
 * Change or add (or remove) a key-value into the property $prop
 * @param string $prop name of the property that contains the mutlifield string
 * @param string $key
 * @param mixed $value (NULL removes the key)
 * @param boolean $save store the values in the database
 */
public function setMF(string $prop, string $key, $value, bool $save=true)
{
  if ( empty($key) ) die('CSection::setMF invalid key');
  $arr = $this->readMF($prop); // read $this->$prop without properties assignement
  $arr[$key] = $value; // add/change the key=value (value NULL removes the key)
  $this->$prop = qtImplode($arr,';');
  if ( $save ) $this->updateMF($prop);
}
public function updateMF(string $prop)
{
  if ( empty($prop) || !property_exists('CSection', $prop) ) die('CSection::updateMF invalid property');
  global $oDB;
  $oDB->exec( "UPDATE TABSECTION SET $prop=? WHERE id=$this->id", [$this->$prop] );
  if ( $prop==='stats' ) SMemSSE::control('CSection:updateStats', array('section'=>$this->id,'stats'=>$this->stats)); //System update
}

}