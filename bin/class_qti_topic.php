<?php // v4.0 build:20230430

class CTopic extends AContainer
{

// AContainer properties:
// id,pid,title,descr,type,status,items
public $numid = -1;
public $statusdate = '0'; // last status change date
public $wisheddate = '0';
public $tags = '';
public $firstpostid = -1;
public $lastpostid = -1;
public $firstpostuser = -1;
public $lastpostuser = -1;
public $firstpostname;
public $lastpostname;
public $firstpostdate = '0';
public $lastpostdate = '0';
public $x;
public $y;
public $z;
public $actorid = -1;
public $actorname = '';
public $notifiedid = -1;
public $notifiedname = '';
public $views = 0;
public $modifdate = '0';
public $param = ''; // multifield (inspection parameters)

public $youreply = '';
public $attachinfo;
public $preview;
public $smile;
public $title = '';

function __construct($ref=null, int $userid=-1)
{
  // Change AContainer defaults
  $this->type = 'T';   // A=News P=Post. Attention, alphabetic order can be used as display order (i.e. "News on top")
  $this->status = 'A'; // A=submitted Z=closed (News 0=closed). Attention user can sort according to the status index.
  // Construct
  $this->setFrom($ref);
  // Check default values for AContainer properties
  if ( empty($this->type) ) $this->type = 'T'; // topic
  if ( empty($this->status) ) $this->status = '0'; // open
}
private function dbFields()
{
  // relation dbfield=>property (exceptions: section,replies)
  return array('id'=>'id','numid'=>'numid','section'=>'pid','type'=>'type',
  'status'=>'status','statusdate'=>'statusdate',
  'wisheddate'=>'wisheddate',  'tags'=>'tags',
  'firstpostid'=>'firstpostid','lastpostid'=>'lastpostid',
  'firstpostuser'=>'firstpostuser','lastpostuser'=>'lastpostuser',
  'firstpostname'=>'firstpostname', 'lastpostname'=>'lastpostname',
  'firstpostdate'=>'firstpostdate','lastpostdate'=>'lastpostdate',
  'x'=>'x','y'=>'y','z'=>'z',
  'actorid'=>'actorid','actorname'=>'actorname',
  'notifiedid'=>'notifiedid','notifiedname'=>'notifiedname',
  'replies'=>'items','views'=>'views',
  'modifdate'=>'modifdate','param'=>'param');
}
public function setFrom($ref=null)
{
  // $ref can be [null|int|array|obj-class], otherwhise die
  if ( $ref===null || $ref===-1 ) return; //... exit with void-instance (default properties)
  if ( is_int($ref) ) {
    if ( $ref<0 ) die(__METHOD__.' Argument must be positive');
    global $oDB;
    $oDB->query( "SELECT * FROM TABTOPIC WHERE id=$ref" );
    $row = $oDB->getRow(); if ( $row===false ) die(__METHOD__.' No id '.$ref);
    $ref = $row; // continue as array
  }
  if ( is_array($ref) ) {
    foreach($ref as $k=>$value) {
      switch((string)$k)
      {
        case 'id':           $this->id          = (int)$value; break;
        case 'preview':
        case 'textmsg':      $this->preview      = $_SESSION[QT]['item_firstline']>0 ? qtInline($value,QT_FIRSTLINE_SIZE) : ''; break;
        case 'numid':        $this->numid        = (int)$value; break;
        case 'section':      $this->pid          = (int)$value; break;
        case 'type':         $this->type         = $value; break;
        case 'status':       $this->status       = $value; break;
        case 'statusdate':   $this->statusdate   = $value; break;
        case 'wisheddate':   $this->wisheddate   = $value; break;
        case 'tags':         $this->descr        = $value; break;
        case 'firstpostid':  $this->firstpostid  = (int)$value; break;
        case 'lastpostid':   $this->lastpostid   = (int)$value; break;
        case 'firstpostuser':$this->firstpostuser= (int)$value; break;
        case 'lastpostuser': $this->lastpostuser = (int)$value; break;
        case 'firstpostname':$this->firstpostname= $value; break;
        case 'lastpostname': $this->lastpostname = $value; break;
        case 'firstpostdate':$this->firstpostdate= $value; break;
        case 'lastpostdate': $this->lastpostdate = $value; break;
        case 'actorid':      $this->actorid      = (int)$value; if ( $this->actorid<=0) $this->actorid=-1; break;
        case 'actorname':    $this->actorname    = $value; break;
        case 'notifiedid':   $this->notifiedid   = (int)$value; if ( $this->notifiedid<=0) $this->notifiedid=-1; break;
        case 'notifiedname': $this->notifiedname = $value; break;
        case 'replies':      $this->items        = (int)$value; break;
        case 'views':        $this->views        = (int)$value; break;
        case 'icon':         $this->smile        = $value; break;
        case 'title':        $this->title        = $value; break;
        case 'modifdate':    $this->modifdate    = $value; break;
        case 'attach':       $this->attachinfo   = (string)$value; break;
        case 'x': if ( is_numeric($value) ) $this->x = (float)$value; break; // must be FLOAT (or NULL)
        case 'y': if ( is_numeric($value) ) $this->y = (float)$value; break; // must be FLOAT (or NULL)
        case 'z': if ( is_numeric($value) ) $this->z = (float)$value; break; // must be FLOAT (or NULL)
        case 'param':        $this->param      = $value; break;
      } // Unit test: $k must be [string] otherwhise key 0 can change the first case (0=='id')
    }
    return; //...
  }
  if ( is_a($ref,'CTopic') ) return $this->setFrom(get_object_vars($ref)); //...
  die(__METHOD__.' Invalid argument type');
}
public function viewsIncrement(int $userid=-1) {
  // +1 when user is not the creator himself
  if ( $userid>=0 && $userid!=$this->firstpostuser ) { global $oDB; $oDB->exec( "UPDATE TABTOPIC SET views=views+1 WHERE id=$this->id" ); }
  // TIPS: this method is not called in __construct, but is called by the display page (after page access is granted)
}
public static function getRef(int $numid=0, $format='', string $none='')
{
  // This returns the formatted ref number (numid) of this item.
  // Format can be defined by a string, a [int] section-id, or a [CSection] section.
  // In case of undefined format, this returns the numid (as '%03s' string), in case of 'N' format, return the $na string.
  if ( is_a($format,'CSection') ) $format = $format->numfield;//!!!
  if ( is_int($format) ) { $arr = SMem::get('_Sections'); if ( isset($arr[$format]['numfield']) ) $format = empty($arr[$format]['numfield']) ? '%03s' : $arr[$format]['numfield']; }
  if ( !is_string($format) ) $format = '%03s';
  if ( $format==='N' ) return $none;
  return empty($format) ? (string)$numid : sprintf($format,$numid);
}
public static function getSections($ids)
{
  // Get sections from a list of topic ids (or a [string] csv id)
  if ( is_string($ids) ) $ids = explode(',',$ids);
  if ( !is_array($ids) ) die('CTopic::getSections: wrong argument');
  global $oDB;
	$arrS = array();
	foreach ($ids as $id)
	{
    $oDB->query( 'SELECT section FROM TABTOPIC WHERE id='.$id );
		while( $row=$oDB->getRow() )
		{
    if ( !in_array((int)$row['section'],$arrS) ) $arrS[]=(int)$row['section'];
		}
	}
	return $arrS;
}
public static function makeIconSrc(string $type='T', string $status='A', string $skin='skin/default/', array $arrStatus=[], bool $checkfile=true)
{
  // Build icon filename (with skin path) and check if file exists
  // In case of type "T", use arrStatus to get the icon filename (or sys_status when arrStatus is empty)
  // To build a filename without path, use $bCheckfile=false
  switch(strtoupper($type))
  {
  case 'T':
    if ( empty($arrStatus) ) $arrStatus = SMem::get('_Statuses');
    $src = $skin.'img/'.(isset($arrStatus[$status]['icon']) ? $arrStatus[$status]['icon'] : 'topic_tZ.gif');
    if ( !$checkfile ) return $src;
    if ( file_exists($src) ) return $src;
    break;
  case 'I':
    $src = $skin.'img/topic_i_'.($status==='Z' ?  '1' : '0').'.gif';
    if ( !$checkfile ) return $src;
    if ( file_exists($src) ) return $src;
    break;
  case 'A':
    $src = $skin.'img/topic_a_'.($status==='Z' ?  '1' : '0').'.gif';
    if ( !$checkfile ) return $src;
    if ( file_exists($src) ) return $src;
    break;
  }
  return 'bin/css/ico_status.gif'; // alternate used if file check failed
}
public static function makeIconName(string $type='T',$status='A',$name='Ticket',$arrStatus=array())
{
  switch(strtoupper($type))
  {
  case 'T':
    if ( empty($arrStatus) ) $arrStatus = SMem::get('_Statuses');
    $name = isset($arrStatus[$status]['name']) ? $arrStatus[$status]['name'] : 'status '.$status;
    break;
  case 'I': $name = L('Ico_item_i'.($status==='Z' ? 'Z' : '')); break;
  case 'A': $name = L('Ico_item_a'.($status==='Z' ? 'Z' : '')); break;
  }
  return $name;
}
public static function makeIcon(string $type='T', string $status='A', string $name='Ticket', string $id='', string $skin='skin/default/', string $strurl='', string $strTitleFormat='%s', array $arrStatus=[])
{
  // Use $name='' to re-build icon-name (title)
  // Use $name=false to remove title
  if ( $type==='T' && empty($arrStatus) ) $arrStatus = SMem::get('_Statuses');
  $src = CTopic::makeIconSrc($type,$status,$skin,$arrStatus);
  if ( $name==='' ) { $name = CTopic::makeIconName($type,$status,$name,$arrStatus); $name = sprintf($strTitleFormat,$name); }
  return asImg( $src, 'id='.$id.'|class=i-item|class=i-item|data-type='.strtolower($type).'|data-status='.strtolower($status).'|alt='.$type.(empty($name) ? '' : '|title='.$name), $strurl );
}
public function getIcon(string $skin='skin/default/', string $strurl='', string $strTitleFormat='%s', string $id='')
{
  return CTopic::makeIcon($this->type,$this->status,$this->getIconName(),$id,$skin,$strurl,$strTitleFormat);
}
public function getIconName()
{
  return CTopic::makeIconName($this->type,$this->status);
}
function getTagIcon()
{
  if ( empty($this->descr) ) return '';
  $arr = explode(';',$this->descr);
  return qtSVG('tag'.(count($arr)>1 ? 's' : ''), 'title='.implode(',',$arr));
}
public function getStatusName(string $alt='unknown')
{
  $arrStatus = SMem::get('_Statuses');
  return isset($arrStatus[$this->status]['name']) ? $arrStatus[$this->status]['name'] : $alt;
}
public static function getTypes()
{
  return array('T'=>L('Item'), 'I'=>L('Inspection'), 'A'=>L('News'));
}
public static function getType($id, string $alt='unknown')
{
  $arr = self::getTypes();
  return isset($arr[$id]) ? $arr[$id] : $alt;
}
public static function getStatuses(string $type='T', bool $onlyNames=false)
{
  // Returns an array of [array] status attributes
  // For Inspections or News, returns a array of [string] statusnames
  // $bOnlyNames allow receiving only name (case type 'T')
  switch($type)
  {
  case 'T':
    $arr = SMem::get('_Statuses');
    if ( $onlyNames ) {
      $names = [];
      foreach($arr as $k=>$attr) $names[$k] = empty($attr['name']) ? 'Status '.$k : $attr['name'];
      return $names;
    }
    return $arr;
    break;
  case 'I': return array('A'=>L('I_running'),'Z'=>L('I_closed')); break;
  default: return array('A'=>L('Submitted'),'Z'=>L('Closed')); break;
  }
}
public static function getStatus($id, string $type='T', string $alt='unknown')
{
  $arr = self::getStatuses($type,true);
  return isset($arr[$id]) ? $arr[$id] : $alt;
}
public static function getOwner(int $id) {
  global $oDB;
  $oDB->query( "SELECT firstpostuser FROM TABTOPIC WHERE id=$id" );
  $row=$oDB->getRow();
  return (int)$row['firstpostuser'];
}
public function getTopicTitle()
{
  global $oDB;
  $oDB->query( 'SELECT title FROM TABPOST WHERE id='.$this->firstpostid);
  $row = $oDB->getRow();
  $this->title = $row['title'];
  return $this->title;
}
public function insertTopic(bool $userStat=true, bool $canNotify=true, $oP=null, $oS=null)
{
  // Pass smile to CTopic
  $this->smile='00';
  if ( isset($oP) && !empty($oP->icon) &&  $oP->icon!=='00') $this->smile = $oP->icon;
  // In case of Topic type 'Inspection'
  if ( $this->type==='I' && isset($oP) ) {
    $this->status='Z'; // When creating a new inspection, ticket status is closed until creator setup parameters and turn it to 'submitted'.
    $this->param = 'Istatus=0;Ilevel=3;Iaggr=mean'; // Initial parameters for an inspection
    $this->z=-1 ; // Inspection score < 0 means unknown
  }
  // bulk prepare values
  $arrValues = array();
  foreach($this->dbFields() as $k=>$prop) {
    if ( !property_exists('CTopic',$prop) ) die('no property: CTopic:'.$prop);
    $arrValues[$k]=$this->$prop;
  }
  // slashes some of the prepared values
  foreach(['firstpostname','lastpostname'] as $k) if ( !empty($arrValues[$k]) ) $arrValues[$k] = qtDb($arrValues[$k]);
  // Insert
  global $oDB;
  $oDB->exec( 'INSERT INTO TABTOPIC ('.implode(',',array_keys($arrValues)).') VALUES (:'.implode(',:',array_keys($arrValues)).')', $arrValues);
  // Status notification
  if ( $canNotify && $this->type=='T' ) $this->NotifyStatus(-1,$oP,$oS);
  // User stats
  if ( $userStat ) {
    $oDB->query( 'SELECT count(*) as countid FROM TABPOST WHERE userid='.$this->firstpostuser);
    $row = $oDB->getRow();
    $oDB->exec( 'UPDATE TABUSER SET lastdate="'.Date('Ymd His').'", numpost='.$row['countid'].', ip="'.$_SERVER['REMOTE_ADDR'].'" WHERE id='.$this->firstpostuser);
    $_SESSION[QT.'_usr']['numpost']=(int)$row['countid'];
  }
  // SSE
  SMemSSE::control(get_class().':'.__FUNCTION__, $this, '');
}
public function NotifyActor(int $intOldactorid=-1, $oS=null)
{
  if ( QT_NOTIFY_NEWACTOR || QT_NOTIFY_OLDACTOR ) {
  if ( $intOldactorid!=$this->actorid ) {

    if ( !isset($oS) ) $oS = new CSection($this->pid);

    if ( $oS->notify==1 )
    {
      global $L;
      // prepare mail
      $strTopic = ''; if ( $oS->numfield!='N' ) $strTopic = sprintf($oS->numfield,$this->numid).' ';
      $strMails = '';
      // getUserInfo can be NULL if user not found
      if ( QT_NOTIFY_NEWACTOR && $this->actorid>=0 ) $strMails .= getUserInfo($this->actorid,'mail').',';
      if ( QT_NOTIFY_OLDACTOR && $intOldactorid>=0 ) $strMails .= getUserInfo($intOldactorid,'mail');
      $strMessage = sprintf("{$L['Topic']} %s ",$strTopic);
      $strMessage .= sprintf($L['Topic_forwarded'],$this->actorname);
      $strSubject = "{$_SESSION[QT]['site_name']}: {$L['Notification']} $strTopic";
      // send mail
      if ( !empty($strMails) ) qtMail($strMails,$strSubject,$strMessage,QT_HTML_CHAR);
    }

  }}
}
public function NotifyStatus(int $oldactorid=-1, $oP=null, $oS=null)
{
  global $L;
  $arrS = SMem::get('_Statuses');

  if ( !empty($arrS[$this->status]['mailto']) )
  {
    if ( !isset($oS) ) $oS = new CSection($this->pid);

    if ( $oS->notify==1 )
    {
      // read message (and get it if not yet defined)
      if ( !isset($this->title) && isset($oP) )
      {
        if ( is_integer($oP) ) $oP = new CPost($oP); // $oP can be an integer
        $this->title = $oP->title;
        $this->preview = qtInline($oP->text,100,' ');
      }
      if ( empty($this->title) ) $this->title = '';
      if ( empty($this->preview) ) $this->preview = '';
      $strTopic = ($oS->numfield!='N' ? sprintf($oS->numfield,$this->numid).' ' : '').$this->title."\r\n".$this->preview."\r\n".$_SESSION[QT]['site_url'].'/qti_topic.php?t='.$this->id;

      $strFile = qtDirLang().'mail_status.php';

      // notify list

      $lstMails = explode(',',$arrS[$this->status]['mailto']);
      $lstMails = array_unique($lstMails);

      // notify mails
      $arrMails = array();
      foreach($lstMails as $intUser)
      {
        switch($intUser)
        {
        case 'MF': $arrMails[] = getUsersInfo($oS->ownerid,'mail'); break;
        case 'MA': if ( $this->actorid>=0 ) $arrMails[] = getUsersInfo($this->actorid,'mail'); break;
        case 'U':
          $arrMails[] = getUsersInfo(intval($this->firstpostuser),'mail');
          if ( $this->notifiedid>=0 ) $arrMails[] = getUsersInfo($this->notifiedid,'mail');
          break;
        case 'A': $arrMails = $arrMails + getUsersInfo('A','mail'); break;
        case 'S': $arrMails = $arrMails + getUsersInfo('S','mail'); break;
        default:  if ( $intUser>=0 ) $arrMails[] = getUsersInfo($intUser,'mail'); break;
        }
      }
      $arrMails = array_unique($arrMails);
      $strMails = implode(', ',$arrMails);

      // message containing 2 parameters (the status, the topic preview)
      $strMessage = "{$L['Status']}: %s \r\n%s";
      if ( file_exists($strFile) ) include $strFile;
      $strMessage = sprintf($strMessage,$arrS[$this->status]['name'],$strTopic);

      $strSubject = $_SESSION[QT]['site_name'].': '.$L['Notification'].' '.$this->title;

      // send mail
      qtMail($strMails,$strSubject,$strMessage,QT_HTML_CHAR);

      // show send mails
      $strMails = '<br><br>'.$L['Notification'].': '.$strMails;
    }
  }
}
public function setStatus( string $strStatus='A', bool $notify=true, $oP=null, bool $sectionStats=true)
{
  if ( $this->status===$strStatus ) return false;
  if ( $strStatus!=='Z' && $this->status!=='Z' ) $sectionStats = false; // only current Z or changing to Z are canditate to section-stats update

  global $oDB;
  $this->status=$strStatus;
  $this->statusdate=date('Ymd His');
  $oDB->exec( 'UPDATE TABTOPIC SET status="'.$this->status.'", statusdate="'.$this->statusdate.'",modifdate="'.date('Ymd His').'" WHERE id='.$this->id);
  $this->status=$strStatus;
  // NOTIFY
  if ( $notify ) $this->NotifyStatus(-1,$oP); // $oP can be an integer
  // UPDATE section stats if required (only if currently Z or changing to Z)
  if ( $sectionStats ) { $voidSec = new CSection(); $voidSec->id = $this->pid; $voidSec->updStats(); }
  // SSE
  SMemSSE::control( get_class().':'.__FUNCTION__, array('section'=>$this->pid,'topic'=>$this->id,'type'=>$this->type,'status'=>$this->status) ); // statusdate is added by SMem::control
  return true;
}
public function setType(string $type='T')
{
  if ( $this->type==$type ) return false;
  $this->type = $type;
  if ( $this->type!=='T' && $this->status!=='Z' ) $this->status = 'A';
  global $oDB;
  $oDB->exec( "UPDATE TABTOPIC SET type='$this->type',status='$this->status',modifdate='".date('Ymd His')."' WHERE id=$this->id" );
  // SSE
  SMemSSE::control( get_class().':'.__FUNCTION__, array('section'=>$this->pid,'topic'=>$this->id,'type'=>$this->type,'status'=>$this->status) ); // statusdate is added by SMem::control
  return true;
}
public function setActor(int $intActor=-1, bool $canNotify=true, bool $insertForwardMessage=true, string $actorname='')
{
  $intOldactorid = $this->actorid;
  if ( $intActor<0 ) die('Topic->setActor: Wrong actor id');

  global $oDB;

  // change actor
  $this->actorid = $intActor;
  $this->actorname = empty($actorname) ? getUsersInfo($this->actorid) : $actorname;
  $oDB->exec( 'UPDATE TABTOPIC SET actorid='.$this->actorid.', actorname="'.$this->actorname.'",modifdate="'.date('Ymd His').'" WHERE id='.$this->id);

  // posting a forward messsage
  if ( $insertForwardMessage )
  {
  $oP = new CPost();
  $oP->id = $oDB->nextId(TABPOST);
  $oP->section = $this->pid;
  $oP->topic = $this->id;
  $oP->type = 'F';
  $oP->title = L('Item_handled').' '.L('by').' '.$this->actorname;
  $oP->text = sprintf(L('Item_forwarded'),$this->actorname);
  $oP->userid = $this->actorid;
  $oP->username = $this->actorname;
  $oP->issuedate = date('Ymd His');
  $oP->modifdate = '';
  $oP->modifuser = '';
  $oP->insertPost(true,false); // Update topic stat, not user's stat
  }

  // email
  if ( $canNotify ) $this->NotifyActor($intOldactorid);

  // SSE
  $arr = array('topic'=>$this->id,'actorid'=>$this->actorid,'actorname'=>$this->actorname);
  if ( $insertForwardMessage ) { $arr['replies']='+1'; $arr['lastpostid']=$oP->id; $arr['lastpostuser']=$oP->userid; $arr['lastpostname']=$oP->username; $arr['lastpostdate']=date('H:i'); }
  SMemSSE::control( get_class().':'.__FUNCTION__, $arr );

}

// TAGS
public static function tagsClear($str, bool $dropDuplicate=true)
{
  if ( is_array($str) ) $str = implode(';',$str);
  if ( !is_string($str) ) die('tagsClear: wrong argument #1');
  // Returns a string 'tag1;tag2;tag3' (trimed, no empty entry, no-accent). Note: 0,'0','',' ' are also removed
  // Returns '' when $str is empty (or only ; , characters )
  // Dropping duplicate is case INsensitive (keeping the first). Exemple: 'Info;info;DATE;date;INFO;Date' returns 'Info;DATE'
  $str = qtAttr($str); // trim and no doublequote
  if ( $str==='*' ) return '*'; // used in case of delete all tags
  if ( empty($str) ) return '';
  $str = qtDropDiacritics($str);
  $str = str_replace(',',';',$str);
  $arr = explode(';',$str);
  $arrClear = array();
  $arrClearLC = array();
  foreach($arr as $str)
  {
    $str=trim($str);
    if ( empty($str) || $str==='*' ) continue; // '*' can be alone, but not inside other tags
    if ( $dropDuplicate && in_array(strtolower($str),$arrClearLC)) continue;
    $arrClear[]=$str;
    $arrClearLC[]=strtolower($str);
  }
  return implode(';',$arrClear);
}
public function tagsUpdate()
{
  global $oDB;
  if ( empty($this->descr) || $this->descr==';' ) $this->descr='';
  if ( !empty($this->descr) && substr($this->descr,-1,1)===';' ) $this->descr = substr($this->descr,0,-1);
  $oDB->exec( "UPDATE TABTOPIC SET tags=?,modifdate='".date('Ymd His')."' WHERE id=$this->id", [qtAttr($this->descr)] ); // no doublequote
}
public function tagsAdd(string $str, $oS=null)
{
  // Check and format
  $str = CTopic::tagsClear($str); // returns csv distinct tags [string] (can return '' or '*')
  if ( empty($str) || $str==='*' ) return false;
  // Append to current and clear (to remove duplicate)
  $this->descr = CTopic::tagsClear($this->descr.';'.$str);
  // Save
  $this->tagsUpdate();
  // Update section stats (if tags added)
  if ( is_null($oS) ) return; //...
  if ( is_int($oS) ) $oS = new CSection($oS);
  if ( is_a($oS,'CSection') ) {
    if ( count(explode(';',$this->descr))>0 ) {
      global $oDB;
      $stats = qtExplode($oS->stats,';');
      $stats['tags'] = $oDB->count(CSection::sqlCountItems($oS->id,'tags'));
      $oS->stats = qtImplode($stats,';');
      $oS->updateMF('stats');
    }
  }
}
public function tagsDel(string $str, $oS=null)
{
  if  ( empty($this->descr) || empty($str) ) return false;
  // Check and format
  $str = CTopic::tagsClear($str); // returns ssv distinct tags [string] (can return '' or '*')
  if ( empty($str) ) return false;
  // Build new tags list
  if ( $str==='*' )
  {
    $this->descr='';
  }
  else
  {
    $arrTag = explode(';',$this->descr); // Current tags
    $arrDel = explode(';',strtolower($str)); // Tag to delete
    $arr = array(); // new tags
    foreach($arrTag as $tag) if ( !in_array(strtolower($tag),$arrDel) ) $arr[]=$tag; // keep not deleted tags
    $this->descr = implode(';',$arr);
  }
  // Save
  $this->tagsUpdate();

  // Update section stats
  if ( is_null($oS) ) return; //...
  if ( is_int($oS) ) $oS = new CSection($oS);
  if ( is_a($oS,'CSection') ) {
    global $oDB;
    $stats = qtExplode($oS->stats,';');
    $stats['tags'] = $oDB->count(CSection::sqlCountItems($oS->id,'tags'));
    $oS->stats = qtImplode($stats,';');
    $oS->updateMF('stats');
  }
}

/**
 * Delete all reply-posts in the topic $ids (can work on several topics)
 * @param integer|array $ids the topic id or a list of id
 * @param boolean $dropAttachs
 */
public static function deleteReplies($ids, bool $dropAttachs=true) {
  if ( is_int($ids) ) $ids = [$ids];
  if ( !is_array($ids) ) die(__METHOD__.' invalid argument');
  $ids = implode(',',$ids);
  global $oDB;
  if ( $dropAttachs ) CPost::dropAttachSql( "SELECT id,attach FROM TABPOST WHERE attach<>'' AND type<>'P' AND topic IN ($ids)", false );
  return $oDB->exec( "DELETE FROM TABPOST WHERE type<>'P' AND topic IN ($ids)" );
}
/**
 * Delete the topic, replies and attachements (can work on several topics)
 * @param integer|array $ids the topic id or a list of id
 * @param boolean $dropAttachs
 * @return integer the number of topics affected
 */
public static function delete($ids, bool $dropAttachs=true) {
  if ( is_int($ids) ) $ids = array($ids);
  if ( !is_array($ids) ) die('CTopic::delete arg #1 must be an array');
  $i = count($ids);
  $ids = implode(',',$ids);
  if ( $dropAttachs ) CPost::dropAttachSQL( "SELECT id,attach FROM TABPOST WHERE attach<>'' AND topic IN ($ids)", false ); // Warning dropAttach of the replies in topics ids
  global $oDB;
  $oDB->exec( "DELETE FROM TABPOST WHERE topic IN ($ids)" );
  $oDB->exec( "DELETE FROM TABTOPIC WHERE id IN ($ids)" );
  return $i;
}

public function updMetadata(int $intMax=0, bool $inspectionUpdateScore=true)
{
  if ( $this->id<0 ) die('Topic->updMetadata: Wrong id');

  // Count
  global $oDB;
  $arr = array();
  $this->items = 0;
  $oDB->query( 'SELECT id,userid,username,issuedate,type FROM TABPOST WHERE topic='.$this->id.' ORDER BY issuedate'); // issuedate to be able to extract first and last
  while($row=$oDB->getRow())
  {
    $arr[]=$row;
    if ( $row['type']!=='P' ) ++$this->items;
  }
  // Save stats
  $i = count($arr)-1; // $arr 0=firstmessage, $i=lastmessage
  $oDB->exec( "UPDATE TABTOPIC SET replies=?,firstpostid=?,firstpostuser=?,firstpostname=?,firstpostdate=?,lastpostid=?,lastpostuser=?,lastpostname=?,lastpostdate=? WHERE id=$this->id",
  [
  $this->items,
  $arr[0]['id'],
  $arr[0]['userid'],
  $arr[0]['username'],
  $arr[0]['issuedate'],
  $arr[$i]['id'],
  $arr[$i]['userid'],
  $arr[$i]['username'],
  $arr[$i]['issuedate']
  ] );

  // close topic if full (not for inspections)
  if ( $this->type!=='I' && $intMax>1 && $this->items>$intMax ) $oDB->exec( "UPDATE TABTOPIC SET status='Z' WHERE id=".$this->id );

  // update inspection stats
  if ( $this->type==='I' && $inspectionUpdateScore ) $this->InspectionUpdateScore();
}
public static function setCoord(CDatabase $oDB, int $id, string $coord)
{
  // Coordinates must be a string 'y,x'.
  // '0,0' can be use to remove a coordinates.
  // z is not used here
  if ( empty($coord) ) $coord='0,0';
  $y=null;
  $x=null;
  $coord = explode(',',$coord);
  if ( isset($coord[0]) ) $y = (float)$coord[0];
  if ( isset($coord[1]) ) $x = (float)$coord[1];
  if ( emptyFloat($y) && emptyFloat($x) ) { $y=null; $x=null; }
  $oDB->exec( 'UPDATE TABTOPIC SET y='.(isset($y) ? $y : 'NULL').',x='.(isset($x) ? $x : 'NULL').' WHERE id='.$id);
}

// --- INSPECTION ---

public function InspectionUpdateScore()
{
  $this->z = $this->InspectionAggregate();
  global $oDB;
  $oDB->exec( 'UPDATE TABTOPIC SET z='.$this->z.' WHERE id='.$this->id );
}

function InspectionAggregate()
{
  if ( $this->id<0 || $this->items<1 ) return -1; // -1 means no results or unknown

  $strIaggr = strtolower($this->getMF('param','Iaggr')); if ( empty($strIaggr) ) $strIaggr='mean';
  global $oDB;
  $i=-1;

  switch($strIaggr)
  {
  case 'mean':
    $oDB->query( 'SELECT title FROM TABPOST WHERE topic='.$this->id.' AND type="R" AND title<>""');
    $arr = array();
    $i=0;
    while($row=$oDB->getRow())
    {
      $str = strtolower(trim($row['title']));
      if ( $str==='' || is_null($str) || $str==='null' ) continue;
      if ( strlen($str)>4 ) $str = substr($str,0,4);
      if ( !is_numeric($str) ) continue;
      $arr[] = floatval($str);
      ++$i;
    }
    if ( empty($arr) ) return -1;
    $i=(array_sum($arr))/$i;
    break;
  case 'min':
    $oDB->query( 'SELECT title FROM TABPOST WHERE topic='.$this->id.' AND type="R" AND title<>""');
    $i=999;
    while($row=$oDB->getRow())
    {
      $str = strtolower(trim($row['title']));
      if ( $str==='' || is_null($str) || $str==='null' ) continue;
      if ( strlen($str)>4 ) $str = substr($str,0,4);
      if ( !is_numeric($str) ) continue;
      if ( floatval($str)<$i ) $i=floatval($str);
    }
    if ( $i==999 ) return -1;
    break;
  case 'max':
    $oDB->query( 'SELECT title FROM TABPOST WHERE topic='.$this->id.' AND type="R" AND title<>""');
    while($row=$oDB->getRow())
    {
      $str = strtolower(trim($row['title']));
      if ( $str==='' || is_null($str) || $str==='null' ) continue;
      if ( strlen($str)>4 ) $str = substr($str,0,4);
      if ( !is_numeric($str) ) continue;
      if ( floatval($str)>$i ) $i=floatval($str);
    }
    break;
  case 'first':
    $oDB->query( 'SELECT title FROM TABPOST WHERE topic='.$this->id.' AND type="R" AND title<>"" ORDER BY issuedate');
    while($row=$oDB->getRow())
    {
      $str = strtolower(trim($row['title']));
      if ( $str==='' || is_null($str) || $str==='null' ) continue;
      if ( strlen($str)>4 ) $str = substr($str,0,4);
      if ( !is_numeric($str) ) continue;
      return round(floatval($str),1);
    }
    break;
  case 'last':
    $oDB->query( 'SELECT title FROM TABPOST WHERE topic='.$this->id.' AND type="R" AND title<>"" ORDER BY issuedate DESC');
    while($row=$oDB->getRow())
    {
      $str = strtolower(trim($row['title']));
      if ( $str==='' || is_null($str) || $str==='null' ) continue;
      if ( strlen($str)>4 ) $str = substr($str,0,4);
      if ( !is_numeric($str) ) continue;
      return round(floatval($str),1);
    }
    break;
  default: die('Unknown aggregation function ['.$strIaggr.']');
  }
  return round($i,1);
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
  if ( empty($prop) || !property_exists('CTopic', $prop) )  die('CTopic::readMF invalid property');
  $arr = qtExplode($this->$prop); // can be [] when property is empty
  if ( $assign )
  {
    foreach($arr as $key=>$value)
    {
      $key = $prefix.$key; if ( $key===$prop ) continue; // prevent reassigning $this->$prop (only other property name can be red)
      if ( property_exists('CTopic', $key) ) $this->$key=$value;
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
  if ( empty($key) )  die('CTopic::readMF invalid key');
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
  if ( empty($prop) || !property_exists('CTopic', $prop) ) die('CTopic::updateMF invalid property');
  global $oDB;
  $oDB->exec( "UPDATE TABTOPIC SET $prop=? WHERE id=$this->id", [$this->$prop] ); // db-field is param
}

}