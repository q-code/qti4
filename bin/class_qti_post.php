<?php // v4.0 build:20230430

class CPost
{

public $id = -1;
public $topic = -1;
public $section = -1;
public $type = 'P';
public $icon = '00';
public $title = '';
public $issuedate = '0';
public $text = '';
public $modifdate = '0';
public $modifuser;
public $modifname;
public $attach;
public $userid;
public $username;
public $userrole;
public $userloca;
public $usersign;
public $num = '+'; // optional sequence-number (show '+' in preview)

function __construct($ref=null, int $num=-1 ,bool $text255=false)
{
  $this->setFrom($ref);
  if ( $this->type==='D' ) $this->title = '&nbsp;';
  if ( $num>=0 ) $this->num = $num;
  if ( $_SESSION[QT]['viewmode']=='C' ) $this->text = qtInline($this->text,510,'...',false);
  if ( $text255 && isset($this->text[255]) ) $this->text = substr($this->text,0,255);
}
public function setFrom($ref=null)
{
  // $ref can be [null|int|array|obj-class], otherwhise die
  if ( $ref===null ) return; //... exit with void-instance (default properties)
  if ( is_int($ref) ) {
    if ( $ref<0 ) die(__METHOD__.' Argument must be positive');
    global $oDB;
    $oDB->query( "SELECT p.*,u.role,u.location,u.signature FROM TABPOST p LEFT JOIN TABUSER u ON p.userid=u.id WHERE p.id=$ref" );
    $row = $oDB->getRow(); if ( $row===false ) die(__METHOD__.' No domain '.$ref);
    $ref = $row; // continue as array
  }
  if ( is_array($ref) ) {
    foreach($ref as $k=>$value) {
      switch((string)$k)
      {
        case 'id':       $this->id     = (int)$value; break;
        case 'section':  $this->section= (int)$value; break;
        case 'topic':    $this->topic  = (int)$value; break;
        case 'type':     $this->type   = $value; break;
        case 'icon':     $this->icon   = $value; break;
        case 'title':    $this->title  = $value; break;
        case 'textmsg':  $this->text   = $value; break;
        case 'issuedate':$this->issuedate= $value; break;
        case 'userid':   $this->userid   = (int)$value; break;
        case 'username': $this->username = $value; break;
        case 'role':     $this->userrole = $value; break;
        case 'location': $this->userloca = $value; break;
        case 'signature':$this->usersign = $value; break;
        case 'modifdate':$this->modifdate= $value; break;
        case 'modifuser':$this->modifuser= (int)$value; break;
        case 'modifname':$this->modifname= $value; break;
        case 'attach':   $this->attach   = $value; break;
      } // Unit test: $k must be [string] otherwhise key 0 can change the first case (0=='id')
    }
    return; //...
  }
  if ( is_a($ref,'CSection') ) return $this->setFrom(get_object_vars($ref)); //...
  die(__METHOD__.' Invalid argument type');
}
public static function getOwner(int $id) {
  if ( $id<0 ) die('CPost::getOwner Invalid argument');
  global $oDB;
  $oDB->query( "SELECT userid FROM TABPOST WHERE id=$id" );
  $row=$oDB->getRow();
  return (int)$row['userid'];
}
function insertPost(bool $topicStat=false, bool $bUserStat=false, bool $bCheckUserPostsToday=true)
{
  global $oDB;
  $oDB->query(
      "INSERT INTO TABPOST (id,section,topic,title,type,icon,userid,username,issuedate,textmsg,attach)
      VALUES ($this->id,$this->section,$this->topic,?,?,?,$this->userid,?,'".date('Ymd His')."',?,?)",
      [
      qtDb($this->title),
      $this->type,
      $this->icon,
      qtDb($this->username),
      qtDb($this->text),
      empty($this->attach) ? '' : $this->attach
      ]
      );
  // Update Topic's replies and lastpost (inserting a post does NOT change de modifdate of the topic!)
  if ( $topicStat )
  {
    $oDB->exec( "UPDATE TABTOPIC SET replies=replies+1,lastpostid=$this->id,lastpostuser=$this->userid,lastpostname='".qtDb($this->username)."',lastpostdate='".$this->issuedate."' WHERE id=$this->topic" );
  }
  // Lastpost delay control
  $_SESSION[QT.'_usr']['lastpost'] = time();
  // Number of posts today control
  if ( isset($_SESSION[QT.'_usr']['posts_today']) && $bCheckUserPostsToday ) {
    if ( $this->type==='P' || $this->type==='R' ) ++$_SESSION[QT.'_usr']['posts_today'];
  }
  // User stat
  if ( $bUserStat ) {
    $oDB->exec( "UPDATE TABUSER SET lastdate='".Date('Ymd His')."', numpost=numpost+1, ip='".$_SERVER['REMOTE_ADDR']."' WHERE id=.$this->userid" );
    if ( !isset($_SESSION[QT.'_usr']['numpost']) ) $_SESSION[QT.'_usr']['numpost']=0;
    ++$_SESSION[QT.'_usr']['numpost'];
  }
  // broadcast only for reply (new topic is broadcasted by CTopic)
  if ( $this->type==='R' ) SMemSSE::control(get_class().':'.__FUNCTION__, $this);
}
public static function delete($ids)
{
  if ( is_int($ids) ) $ids = array($ids);
  if ( !is_array($ids) ) die('CPost::delete arg #1 must be an array');
  $i = count($ids); if ( $i==0 ) return 0;
  global $oDB;
  CPost::dropAttachs($ids,false);
  $ids = implode(',',$ids);
  global $oDB; $oDB->exec( "DELETE FROM TABPOST WHERE id IN ($ids)" );
  return $i;
}

public static function dropAttachs($ids, bool $updAttach=true, bool $idsAreTopics=false) {
  if ( is_int($ids) ) $ids = array($ids);
  if ( !is_array($ids) ) die('CPost::dropAttachs arg #1 must be an array');
  $ids = implode(',',$ids);
  if ( $idsAreTopics ) return CPost::dropAttachSql( "SELECT id,attach FROM TABPOST WHERE attach<>'' AND topic IN ($ids)", $updAttach );
  return CPost::dropAttachSql( "SELECT id,attach FROM TABPOST WHERE attach<>'' AND id IN ($ids)", $updAttach );
}
public static function dropAttachSql(string $sql='', bool $updAttach=true) {
  // sql MUST be SELECT id,attach FROM TABPOST...
  if ( empty($sql) ) die('CPost::dropAttachSql arg #1 must be a string');
  $arr = array();
  global $oDB; $oDB->query( $sql );
  while( $row=$oDB->getRow() ) { $arr[(int)$row['id']] = $row['attach']; }
  return CPost::dropDocuments($arr,$updAttach);
}
public static function dropDocuments(array $docs, bool $updAttach=true) {
  $i = count($docs); if ( $i>500 ) { $i=500; $docs = array_slice($docs,0,500,true); }
  if ( $i==0 ) return 0;
  // drop attach
  foreach($docs as $doc) if ( file_exists(QT_DIR_DOC.$doc) ) unlink(QT_DIR_DOC.$doc);
  // update
  if ( $updAttach ) {
    $ids = implode(',',array_keys($docs));
    global $oDB; $oDB->exec( "UPDATE TABPOST SET attach='' WHERE attach<>'' AND id IN ($ids)" );
  }
  return $i;
}
public function getSrcAttach(){
  // source name: drop dir and {id_}
  $str = strpos($this->attach,'/') ? substr(strrchr($this->attach,'/'),1) : $this->attach;
  $n = $this->id.'_';
  if ( substr($str,0,strlen($n))==$n ) $str = substr($str,strlen($n));
  return $str;
}
public static function getPrefix($serie='a', string $icon='00', string $skin='skin/default/', string $class='')
{
  $key='';
  if ( is_a($serie,'CSection') ) $key = $serie->prefix;  // serie can be a section id [int] or a CSection
  if ( is_int($serie) ) { $arr = SMem::get('_Sections'); $key = ( empty($arr[$serie]['prefix']) ? '' : $arr[$serie]['prefix'] ); }
  if ( is_string($serie) ) $key = $serie;
  if ( empty($icon) || $icon==='00' || empty($key) || !is_string($key) ) return '';

  $src = CPost::getPrefixSrc($key,$icon,$skin);
  if ( empty($src) ) return '';
  return '<img'.(empty($class) ? '' : ' '.$class).' src="'.$src.'" alt="[o]" title="'.L('PrefixIcon.'.$key.$icon).'"/>';
}
public static function getPrefixSrc($serie='a', string $icon='00', string $skin='skin/default/')
{
  $key='';
  if ( is_a($serie,'CSection') ) $key = $serie->prefix;  // serie can be a section id [int] or a CSection
  if ( is_int($serie) ) { $arr = SMem::get('_Sections'); $key = ( empty($arr[$serie]['prefix']) ? '' : $arr[$serie]['prefix'] ); }
  if ( is_string($serie) ) $key = $serie;
  if ( empty($icon) || $icon==='00' || empty($key) || !is_string($key) ) return '';
  return $skin.'/img/prefix_'.$key.'_'.$icon.'.gif';
}
public static function getIconType(string $type='P', string $parentType='T', string $parentStatus='A', string $skin='skin/default/', string $id='')
{
  switch($type)
  {
  case 'P':
    $strIcon = CTopic::makeIcon($parentType,$parentStatus,'',$id);
    break;
  case 'R':
    $strIcon = qtSVG('comment-dots', 'title='.L('Ico_post_r').(empty($id) ? '' : '|id='.$id));
    break;
  case 'D':
    $strIcon = qtSVG('trash', 'title='.L('Ico_post_d').(empty($id) ? '' : '|id='.$id));
    break;
  case 'F':
    $strIcon = qtSVG('share', 'title='.L('Ico_post_f').(empty($id) ? '' : '|id='.$id));
    break;
  default:
    $strIcon = '<img id="'.$id.'" src="'.$skin.'img/post_'.strtolower($type).'.gif" alt="P" title="'.L('Ico_post_'.strtolower($type)).'" class="i-item"/>';
    break;
  }
  return $strIcon;
}
function getScore(CTopic $oT)
{
  if ( $oT->type==='I' )
  {
    $i = strtolower(trim($this->title));
    if ( $i==='' || is_null($i) || $i==='null' ) $i=-1;
    if ( strlen($i)>4 ) $i = substr($i,0,4);
    if ( is_numeric($i) ) { $i=floatval($i); } else { $i=-1; }
    return $i;
  }
  return -1;
}
function getScoreImage(CTopic $oT,$bName=true)
{
  $i = $this->getScore($oT);
  return $i<0 ? '<small>('.L('Unknown').')</small>' : ValueScalebar($i,$oT->getMF('param','Ilevel')).($bName ? ' '.ValueName($i,$oT->getMF('param','Ilevel')) : '');
}
function getScoreName(CTopic $oT)
{
  $i = $this->getScore($oT);
  return $i<0 ? '' : ValueName($i,$oT->getMF('param','Ilevel'));
}
public function render(CSection $oS, CTopic $oT, bool $avatar=true, bool $cmd=true, string $strSkin='skin/default')
{
  if ( !isset($oS) ) die('oPost->Show: Missing $oS');
  if ( !isset($oT) ) die('oPost->Show: Missing $oT');
  // prepare icon
  $strIcon = CPost::getIconType($this->type,$oT->type,$oT->status,$strSkin);
  // prepare title //!!! wished date style
  $strTitle = $this->type=='D' ? '<span title="'.$this->text.'">'.L('Message_deleted').'</span>' : $this->title;
  if ( $this->type==='P' && !empty($oS->wisheddate) && !empty($oT->wisheddate) ) {
    $strLink = '<a'.(SUser::canAccess('show_calendar') ? ' href="'.url('qti_calendars.php').'?s='.$oS->id.'&v=wisheddate&y='.substr($oT->wisheddate,0,4).'&m='.substr($oT->wisheddate,4,2).'"' : '').' title="'.L('Calendar').': '.qtDatestr($oT->wisheddate,'Y-m-d','',true).'">%s</a>';
    $strTitle .= '<span class="wisheddate">&#8201;&middot;&#8201;'.L('Wisheddate').': '.sprintf($strLink,qtDatestr($oT->wisheddate,'d M','',true)).'</span>';
  }
  // message attachment and signature
  $msg = '<p>';
  if ( !empty($oS->prefix) && $this->icon!='00' ) $msg .=  icoPrefix($oS->prefix,(int)$this->icon).'&nbsp; ';
  // format the text
  $str = qtBBcode($this->text,'',L('Bbc.*'));
  // show the image (if any)
  if ( !empty($this->attach) && strpos($str, 'src="@"') && in_array(substr($this->attach,-4,4),array('.gif','.jpg','jpeg','.png')) ) $str = str_replace('src="@"','src="'.QT_DIR_DOC.$this->attach.'"',$str);
  // if message shortened
  if ( $_SESSION[QT]['viewmode']=='C' && substr($str,-3)==='...' ) $str .= '<a id="viewmode" href="'.url('qti_item.php').'?t='.$this->topic.'&view=N" title="'.L('View_n').'">'.qtSVG('window-maximize').' '.qtSVG('long-arrow-alt-down').'</a>';
  $msg .= $str.'</p>'.PHP_EOL;
  // attachements
  if ( !empty($this->attach) ) $msg .= '<p class="post-attachment">'.qtSVG('paperclip', 'title='.L('Attachment')).' <a href="'.QT_DIR_DOC.$this->attach.'" class="attachment" target="_blank">'.$this->getSrcAttach().'</a></p>';
  // signature
  if ( $_SESSION[QT]['viewmode']!='C' && $this->type!='F' && !empty($this->usersign) )
  {
    $msg .= '<p class="post-sign">'.qtBBcode($this->usersign).'</p>'.PHP_EOL;
  }
  // user picture
  $picUser = $_SESSION[QT]['viewmode']!='C' && $avatar ? SUser::getPicture($this->userid,'class=post-user','') : '';

  // buttons
  $strEndLine = '';
  if ( $cmd )
  {
    if ( SUser::auth() )
    {
      if ( $oT->status==='Z' && $oS->status==='0' )
      {
        $strEndLine .= '<a class="button" href="'.url('qti_edit.php').'?t='.$oT->id.'&a=qu&p='.$this->id.'">'.L('Quote').'</a>';
      }
      if ( $this->userid==SUser::id() || SUser::isStaff() )
      {
        $strEndLine .= '<a class="button" href="'.url('qti_edit.php').'?t='.$oT->id.'&p='.$this->id.'&a=ed">'.L('Edit').'</a>';
        if ( $this->type=='P')
        {
          $strEndLine .= '<a class="button" href="'.url('qti_dlg.php').'?s='.$oS->id.'&a=itemDelete&ids='.$oT->id.($this->type=='P' ? '' : '&p='.$this->id).'">'.L('Delete').'</a>';
        }else{
          $strEndLine .= '<a class="button" href="'.url('qti_dlg.php').'?s='.$oS->id.'&a=replyDelete&t='.$oT->id.'&p='.$this->id.'">'.L('Delete').'</a>';
        }
        if ( $oT->type==='I' && $this->type==='P' ) $strEndLine .= '<a class="button" href="'.url('qti_dlg.php').'?a=itemParam&ids='.$oT->id.'">'.L('Parameters').'</a>';
      }
    }
  }
  // closed topic
  if ( $this->type==='P' && $oT->status==='Z' && !empty($oT->statusdate) ) {
    $strEndLine .= '<span>'.L('Closed_item').' ('.strtolower(qtDatestr($oT->statusdate,'$','$',true)).')</span>';
  }

  // Show message
  return '
  <div id="p'.$this->id.'" class="post post-'.$this->type.'">
  <div class="g-p-type"><p class="i-container">'.$strIcon.'</p></div>
  <div class="g-p-title">
    <p class="post-title-r">'.$strTitle.'</p>
    <p class="post-title-l" data-num="'.$this->num.'"><a href="'.url('qti_user.php').'?id='.$this->userid.'">'.$this->username.'</a>&#8201;&middot;&#8201;'.qtDatestr($this->issuedate,'$','$',true).'</p>
  </div>
  <div class="g-p-msg article">'.$picUser.$msg.'</div>
  <div class="g-p-status"><p class="post-cmd">'.$strEndLine.'</p></div>
  </div>
  ';
}

public function renderInspectionResult(CSection $oS, CTopic $oT, bool $avatar=true, string $strEndLine='', string $strSkin='skin/default')
{
  if ( !isset($oS) ) die('oPost->Show: Missing $oS');
  if ( !isset($oT) ) die('oPost->Show: Missing $oT');
  // prepare icon
  $strIcon = CPost::getIconType($this->type,$oT->type,$oT->status,$strSkin);
  // message attachment and signature
  $msg = '<p>';
  if ( !empty($oS->prefix) && $this->icon!='00' ) $msg .=  icoPrefix($oS->prefix,(int)$this->icon).'&nbsp; ';
  // format the text
  $str = qtBBcode($this->text,'',L('Bbc.*'));
  // show the image (if any)
  if ( !empty($this->attach) && strpos($str, 'src="@"') && in_array(substr($this->attach,-4,4),array('.gif','.jpg','jpeg','.png')) ) $str = str_replace('src="@"','src="'.QT_DIR_DOC.$this->attach.'"',$str);
  // if message shortened
  if ( $_SESSION[QT]['viewmode']=='C' && substr($str,-3)==='...' ) $str .= '<a id="viewmode" href="'.url('qti_item.php').'?t='.$this->topic.'&view=N" title="'.L('View_n').'">'.qtSVG('window-maximize').' '.qtSVG('long-arrow-alt-down').'</a>';
  $msg .= $str.'</p>'.PHP_EOL;
  // attachements
  if ( !empty($this->attach) ) $msg .= '<p><small>'.qtSVG('paperclip', 'title='.L('Attachment')).' <a href="'.QT_DIR_DOC.$this->attach.'" class="attachment" target="_blank">'.$this->getSrcAttach().'</a></small></p>';
  // signature
  if ( $_SESSION[QT]['viewmode']!='C' && $this->type!='F' && !empty($this->usersign) )
  {
    $msg .= '<p class="post-sign">'.qtBBcode($this->usersign).'</p>'.PHP_EOL;
  }
  // Commands
  $cmds = '';
  if ( $this->userid==SUser::id() || SUser::isStaff() ) {
    $cmds .= '<a href="qti_edit.php?t='.$this->topic.'&p='.$this->id.'&a=ed" id="p'.$this->id.'-edit" style="display:none" title="'.L('Edit').'">'.qtSVG('pen-square').'</a> &nbsp;';
    $cmds .= '<a href="qti_dlg.php?s='.$oS->id.'&a=replyDelete&t='.$this->topic.'&p='.$this->id.'" id="p'.$this->id.'-del" style="display:none" title="'.L('Delete').'">'.qtSVG('trash').'</a> &nbsp;';
  }
  // Show message
  return '
  <div id="p'.$this->id.'" class="inspection-row">
  <div class="inspection-date">'.$strIcon.' '.qtDatestr($this->issuedate,'$','$',true,true,true).'</div>
  <div class="inspection-score">'.$this->getScoreImage($oT,false).'</div>
  <div class="inspection-text" id="p'.$this->id.'-short">'.qtInline($this->text,200).'</div>
  <div class="inspection-text" id="p'.$this->id.'-long" style="display:none">'.$msg.'</div>
  <div class="inspection-user">'.L('by').' <a href="qti_user.php?id='.$this->userid.'">'.$this->username.'</a></div>
  <div class="inspection-cmd">'.$cmds.'<a href="javascript:void(0)" onclick="showAlt(`p'.$this->id.'`); return false;">'.qtSVG('folder-open').'</a>
  </div>
  </div>
  ';
}

/**
 * Update empty title using the text (maximum 64 characters)
 * @param cPOST $oP [by reference] to be updated
 * @param string $default default title if text is empty
 * @param number $max maximum title size
 * @return string the post title
 */
public static function makeTitle(CPost &$oP, string $default='untitled', int $max=64, string $end='...') {
  if ( !empty($oP->title) ) return $oP->title;
  if ( $max<1 ) die('makeTitle: arg #3 must be a integer (minimum 1)');
  $str = empty($oP->text) ? $default : $oP->text;
  $i=strpos($str,"\r\n"); if ( $i>5 ) $str=substr($str,0,$i); // first line if at least 5 characters
  $oP->title = qtInline($str,$max,$end);
  return $oP->title;
}

}