<?php // v4.0 build:20230430

session_start();
/**
 * @var CHtml $oH
 * @var CDatabase $oDB
 */
require 'bin/init.php';
if ( !SUser::canView('V6') ) die(L('E_11'));

// --------
// Check posted certificates
// --------

$certificate = makeFormCertificate('3cb4bd2256f4642777c70f1cc0efcc77');
// Forwarding certificate. Note: 'dopreview' is ajax-transfered to edit_preview.php
if ( isset($_POST['dosend']) && $_POST['dosend']===makeFormCertificate('b7033b5983ec3b0fef7b3c251f6d0b92') ) $_POST['dosend']=$certificate;
// validate certificates
if ( isset($_POST['dosend']) && $_POST['dosend']!==$certificate ) die('Unable to check certificate');

// --------
// INITIALISE
// --------

$a = ''; // required
$s = -1;
$t = -1;
$p = -1;
qtArgs('a! int:s int:t int:p');

// Initialise containers and check $s
$oT = new CTopic($t>=0 ? $t : null); // can be -1 (new topic)
if ( $oT->pid>=0 ) $s = $oT->pid; // when editing a topic, $s is that topic pid, otherwise uses the GET/POST value
if ( $s<0 ) die('Missing parameters: section');
$oS = new CSection($s);
$oP = new CPost(($p>=0 ? $p : null));
if ( $a==='qu' && !empty($oP->text) ) {
  $str = $oP->text; $oP = new CPost(null); $oP->text = $str; // quote must be a void-post with only text
}

if ( isset($_POST['text']) ) $oP->text = trim($_POST['text']); // Note: text may be forwarded (not yet submitted) when user changes quickreply to advanced-reply

// Initialise $oH and check $a
$oH->selfurl = APP.'_edit.php';
switch($a) {
  case 'nt': $oH->selfname = L('New_item'); break;
  case 're': $oH->selfname = L('Reply'); break;
  case 'qu': $oH->selfname = L('Reply'); break;
  case 'ed': $oH->selfname = L('Edit_message'); break;
  default: die('Missing parameters a');
}
$oH->exiturl = APP.'_item.php?t='.$t; if ( $t<0 ) $oH->exiturl = 'qti_items.php?s='.$s;
$oH->exitname = L('Item+');

// Initialise others
$now = date('Ymd His');
$withDoc = false;
$tagEditor = false;
$intNotified = -1;
$strNotified = '';

// Check initial type and parent ('quote' become 'reply')
switch($a) {
  case 'nt': $oP->type = 'P'; break;
  case 're': $oP->type = 'R'; if ( $t<0 ) die('Missing parameters: t'); break;
  case 'qu': $oP->type = 'R'; if ( $t<0 || $p<0 ) die('Missing parameters: t or p');
  $oP->text = '[quote='.$oP->username.']'.$oP->text.'[/quote]';
  $a = 're';
  break;
  case 'ed': if ( $t<0 || $p<0 ) die('Missing parameters: t or p'); break;
}

// MAP

$bMap=false;
if ( qtModule('gmap') ) {
  include translate('qtim_gmap.php');
  include 'qtim_gmap_lib.php';
  if ( gmapCan($s) ) $bMap=true;
  if ( $bMap ) {
  $oH->links[]='<link rel="stylesheet" type="text/css" href="qtim_gmap.css"/>';
  if ( !isset($_SESSION[QT]['m_gmap_symbols']) ) $_SESSION[QT]['m_gmap_symbols']='0';
  }
}

// --------
// SUBMITTED
// --------

if ( isset($_POST['dosend']) ) try {

  // Current editor/creator (modifuser), can be the onbehalf
  $oP->modifuser = (int)$_POST['userid'];
  $oP->modifname = qtDb(trim($_POST['username']));
  if ( !empty($_POST['behalf']) ){
    $strBehalf = qtDb(trim($_POST['behalf']));
    $intBehalf = (int)$_POST['behalfid']; if ( $intBehalf<0 ) $intBehalf = SUser::getUserId($oDB,$strBehalf,-1);
    if ( $intBehalf<0 ) throw new Exception( L('Send_on_behalf').' '.L('invalid') );
    $oP->modifuser = $intBehalf;
    $oP->modifname = $strBehalf;
  }
  // For New post (or Reply or Quote) creator=modifuser, while creator don't change when editing existing post
  if ( $a!='ed' ) {
    $oP->userid = $oP->modifuser;
    $oP->username = $oP->modifname;
  }

  // Read submitted form values
  if ( isset($_POST['icon']) ) $oP->icon = substr($_POST['icon'],0,2);
  if ( isset($_POST['title']) && $oT->type!='I' )  $oP->title = qtInline(trim($_POST['title']),64);
  if ( isset($_POST['attach']) ) $oP->attach = $_POST['attach']; // old attachment
  if ( isset($_POST['tag-new']) ) $oT->descr = trim($_POST['tag-new']);
  if ( strlen($oP->text)>$_SESSION[QT]['chars_per_post'] ) throw new Exception( L('E_too_long').' '.sprintf(L('E_char_max'), $_SESSION[QT]['chars_per_post']) );
  if ( substr_count($oP->text,"\n")>$_SESSION[QT]['lines_per_post'] ) throw new Exception( L('E_too_long').' '.sprintf(L('E_line_max'), $_SESSION[QT]['lines_per_post']) );
  $oT->preview = qtInline($oP->text);

  // Detect basic errors
  if ( $oP->text=='' && $oT->type!=='I' ) throw new Exception( L('Message').' '.L('invalid') ); //...
  if ( $a=='nt' && $oP->title=='' && $oS->titlefield==2 ) throw new Exception( L('E_no_title') ); //...
  if ( $a=='nt' && $oP->title=='' ) CPost::makeTitle($oP);

  // Inspection result
  if ( $oT->type==='I' && $oP->type==='R' ) {
    $oP->title=$_POST['inspection'];
    if ( $oP->title=='pc' && isset($_POST['inspectionvalue']) ) $oP->title=trim($_POST['inspectionvalue']);
    if ( $oP->title=='null' ) $oP->title='';
  }

  // Check notified
  if ( isset($_POST['notifiedname']) ) {
    $strNotified = trim($_POST['notifiedname']);
    // Complete if missing notified name
    if ( $strNotified!=='' ) {
      $arrNames = getUsers('N',$strNotified,1);
      if ( count($arrNames)!==1 )  { throw new Exception( L('Notify_also').' '.L('invalid') ); }; //...
      $intNotified = array_key_first($arrNames);
    }
  }

  if ( !empty($_POST['wisheddate']) ) {
    $strArgs = qtDatestr(trim($_POST['wisheddate']),'Ymd','');
    if ( !is_string($strArgs) ) throw new Exception( L('Wisheddate').' '.L('invalid') );
    if ( substr($strArgs,0,6)==='Cannot' ) throw new Exception( L('Wisheddate').' '.L('invalid') );
    if ( substr($strArgs,0,4)==='1970' ) throw new Exception( L('Wisheddate').' '.L('invalid') );
    $oT->wisheddate = $strArgs;
  }

  if ( !empty($_POST['coord']) ) {
    $_POST['coord'] = QTstr2yx($_POST['coord']);
    if ( $_POST['coord']===FALSE ) throw new Exception( 'Invalid coordinate format' );
  }

  // Mandatory submitted fields (in case of new topic)

  if ( $a==='nt' ) {
    if ( $oS->notifycc=='2' && $intNotified<0 ) throw new Exception( L('Notify_also').': '.L('Missing') );
    if ( $oS->wisheddate=='2' && empty($_POST['wisheddate']) ) throw new Exception( L('Wisheddate').': '.L('Missing') );
    if ( $oP->title==='' && $oS->titlefield=='2' ) throw new Exception( L('E_no_title') );
  }
  if ( $a==='ed' ) {
    if ( $oP->title==='' && $oS->titlefield=='2' ) throw new Exception( L('E_no_title') );
  }

  // Check flood limit (_usr_lastpost is set in CPost::insert)
  if ( !empty($_SESSION[QT.'_usr']['lastpost']) && $_SESSION[QT.'_usr']['lastpost']+QT_FLOOD >= time() ) throw new Exception( L('E_wait') ); //...

  // check maximum post per day (not for moderators)
  if ( !SUser::isStaff() && !postsTodayAcceptable((int)$_SESSION[QT]['posts_per_day']) ) {
    $oH->exiturl = 'qti_items.php?s='.$s;
    $oH->pageMessage('', L('E_too_much')); //...
  }

  // check message module antispam
	if ( qtModule('antispam') ) include 'qtim_antispam.php';

	// check upload
	if ( $_SESSION[QT]['upload']!=='0' && !empty($_FILES['newdoc']['name']) ) {
	  include 'config/config_upload.php';
	  $info = validateFile($_FILES['newdoc'],ALLOWED_FILE_EXT,ALLOWED_MIME_TYPE,intval($_SESSION[QT]['upload_size'])*1024+16);
	  if ( !empty($info) ) throw new Exception( $info ); //...
	  $withDoc = true;
	  // remove old attach
	  if ( !empty($_POST['attach']) && file_exists(QT_DIR_DOC.$_POST['attach']) ) { unlink(QT_DIR_DOC.$_POST['attach']); $oP->attach = ''; }
	}

	// PROCESS $a
	switch($a) {

	case 'nt': // new topic
    $oDB->beginTransac();
		$oT->id = $oDB->nextId(TABTOPIC);
    $oT->numid = $oDB->nextId(TABTOPIC,'numid','WHERE section='.$s);
		$oP->id = $oDB->nextId(TABPOST);
		$oP->topic = $oT->id;
		$oP->section = $s;
		$oT->pid = $s;
    // if moderator post
    if ( isset($_POST['topictype']) ) $oT->type = $_POST['topictype'];
    if ( isset($_POST['topicstatus']) ) $oT->status = $_POST['topicstatus'];
		$oT->firstpostid = $oP->id;
		$oT->lastpostid = $oP->id;
		$oT->firstpostuser = $oP->userid;
		$oT->firstpostname = $oP->username;
		$oT->lastpostuser = $oP->userid;
		$oT->lastpostname = $oP->username;
		$oT->firstpostdate = $now;
		$oT->lastpostdate = $now;
    if ( $intNotified>=0 ) {
      $oT->notifiedid = $intNotified;
      $oT->notifiedname = $strNotified;
    }
		$oP->issuedate = $now;
		$oT->title = $oP->title;
		if ( $withDoc )
		{
      $strDir = qtDirData('',$oP->id);
      $oP->attach = $strDir.$oP->id.'_'.$_FILES['newdoc']['name'];
      copy($_FILES['newdoc']['tmp_name'],QT_DIR_DOC.$oP->attach);
		  unlink($_FILES['newdoc']['tmp_name']);
		}
		// Insert
		$oP->insertPost(); // No topic stat (topic not yet created), No user stat (computed when inserting topic)
		$oT->insertTopic(true,true,$oP,$oS);
    $oDB->commitTransac();
    $oS->updStats(array('tags'=>$oS->tags));
    ++$_SESSION[QT.'_usr']['numpost'];
    // location insert
    if ( $bMap && !empty($_POST['coord']) ) CTopic::setCoord($oDB,$oT->id,$_POST['coord']);
    // ----------
    // module rss, except for hidden section (type=0)
    if ( $oS->type!=='0' && qtModule('rss') ) { if ( $_SESSION[QT]['m_rss']==='1' ) include 'qtim_rss_inc.php'; }
    // ----------
    break;

	case 're':
	case 'qu': // SEND a reply
    $oDB->beginTransac();

		$oP->id = $oDB->nextId(TABPOST);
		$oP->topic = $t;
		$oP->section = $s;
		$oP->issuedate = $now;
		if ( $withDoc )
		{
      $strDir = qtDirData('',$oP->id);
      $oP->attach = $strDir.$oP->id.'_'.$_FILES['newdoc']['name'];
      copy($_FILES['newdoc']['tmp_name'],QT_DIR_DOC.$oP->attach);
		  unlink($_FILES['newdoc']['tmp_name']);
		}
		$oP->insertPost(false,true); // No update topic stat (done after), Update the user's stat
    $oDB->commitTransac();
    $oT->updMetadata((int)$_SESSION[QT]['posts_per_item']); // Update topic stats and close topic if full (and lastpost topic info)

    ++$_SESSION[QT.'_usr']['numpost'];

    // topic type (from staff)
    if ( isset($_POST['topictype']) && $_POST['topictype']!=$_POST['oldtype'] )
    {
      $oT->setType($_POST['topictype']);
    }

    // topic status (from staff)
    if ( isset($_POST['topicstatus']) ) {
    if ( $_POST['topicstatus']!=$_POST['oldstatus'] ) {
      $oT->setStatus($_POST['topicstatus'],true,$oP);
    }}

    // topic status (from user)
    if ( isset($_POST['topicstatususer']) ) {
    if ( $_POST['topicstatususer'][0]=='Z' ) {
      $oT->setStatus('Z');
    }}
    ++$oS->replies;
    $oS->updStats(array('items'=>$oS->items,'tags'=>$oS->tags));

    break;

	case 'ed': // SEND a edit

    if ( $bMap && isset($_POST['coord']) ) {
      if ( empty($_POST['coord']) ) { CTopic::setCoord($oDB,$t,''); } else { CTopic::setCoord($oDB,$t,$_POST['coord']); } //z is not used
    }

    $strModif='';
    // modifdate+modifuser if editor is not the creator
    if ( $oP->modifuser!=$oP->userid ) $strModif=', modifdate="'.date('Ymd His').'", modifuser='.$oP->modifuser.', modifname="'.$oP->modifname.'"';
    // modifdate+modifuser if not the last message
    if ( $oT->lastpostid!=$oP->id ) $strModif=', modifdate="'.date('Ymd His').'", modifuser='.$oP->modifuser.', modifname="'.$oP->modifname.'"';

    // Add attach
    if ( $withDoc ) {
      $strDir = qtDirData('',$oP->id);
      $oP->attach = $strDir.$oP->id.'_'.$_FILES['newdoc']['name'];
      copy($_FILES['newdoc']['tmp_name'],QT_DIR_DOC.$oP->attach);
      unlink($_FILES['newdoc']['tmp_name']);
    }

    // Drop attach
    if ( isset($_POST['dropattach']) ) { $oP->attach=''; CPost::dropAttachs($oP->id,false); }
    // save edits
    $oDB->exec( "UPDATE TABPOST SET title='".qtDb($oP->title)."', icon='".$oP->icon."',textmsg='".qtDb($oP->text)."',attach='".$oP->attach."' ".$strModif." WHERE id=".$oP->id );
    if ( isset($_POST['wisheddate']) ) $oDB->exec( 'UPDATE TABTOPIC SET wisheddate="'.$oT->wisheddate.'",modifdate="'.date('Ymd His').'" WHERE id='.$t);
    // topic type (from staff)
    if ( isset($_POST['topictype']) ) {
      if ( $_POST['topictype']!==$_POST['oldtype'] ) {
      $oT->setType($_POST['topictype']);
      }
    }
    // topic status (from staff)
    if ( isset($_POST['topicstatus']) ) {
    if ( $_POST['topicstatus']!=$_POST['oldstatus'] ) {
      $oT->setStatus($_POST['topicstatus']);
      if ( $_POST['topicstatus']=='Z' || $_POST['oldstatus']=='Z' ) $oS->updStats(array('items'=>$oS->items,'replies'=>$oS->replies,'tags'=>$oS->tags));
    }}
    // topic status (from user)
    if ( isset($_POST['topicstatususer']) ) {
    if ( $_POST['topicstatususer'][0]=='Z' ) {
      $oT->setStatus('Z');
      $oS->updStats(array('items'=>$oS->items,'replies'=>$oS->replies,'tags'=>$oS->tags));
    }}
    break;
	default: die('Invalid edit type');
	}

  // Update inspection score
  if ( $oT->type==='I' ) $oT->InspectionUpdateScore();

  // clear caches (SectionsStats, Sections, StatsGDS)
  memFlush(); memFlushStats();

  // EXIT
  if ( $a=='nt' && $oT->type=='I' ) {
    $oH->redirect('qti_dlg.php?a=itemParam&s='.$s.'&ids='.$oP->topic);
  } else {
    $strArgs = L('S_message_saved'); if ( $oS->numfield!='N' ) $strArgs = '['.sprintf($oS->numfield,$oT->numid).'] '.$strArgs;
    $_SESSION[QT.'splash'] =$strArgs;
    $oH->redirect('qti_item.php?t='.$oP->topic.'#'.$oP->id);
  }

} catch (Exception $e) {

  $error = $e->getMessage();
  $_SESSION[QT.'splash'] = 'E|'.$error;

}

// --------
// HTML BEGIN
// --------

if ( $bMap ) {
  if ( !empty($oT->y) && !empty($oT->x) ) {
    $strPname = substr($oP->title,0,25);
    $strPinfo = '<p class="small">Lat: '.QTdd2dms($oT->y).' <br>Lon: '.QTdd2dms($oT->x).'<br><br>DD: '.round($oT->y,8).', '.round($oT->x,8).'</p>';
    $oMapPoint = new CMapPoint($oT->y,$oT->x,$strPname,$strPinfo);

    // add extra $oMapPoint properties (if defined in section settings)
    $oSettings = getMapSectionSettings($s,true);
    if ( is_object($oSettings) ) foreach(array('icon','shadow','printicon','printshadow') as $prop) if ( property_exists($oSettings,$prop) ) $oMapPoint->$prop = $oSettings->$prop;
    $arrExtData[$oT->id] = $oMapPoint;
  } else {
    $oMapPoint = new CMapPoint(0,0);
  }
}

$canUpload = SUser::canAccess('upload');
$intBbc = $canUpload ? 3 : 2;

include 'qti_inc_hd.php';

// PREVIEW

echo '<div id="message-preview"></div>
';

// TOPIC (if inspection)

if ( $oT->type==='I' && ($a=='re' || $a=='qu') ) {

  echo '<h2>',L('Inspection'),'</h2>'.PHP_EOL;
  // ======
  $strState = 'p.*,u.role,u.location,u.signature FROM TABPOST p, TABUSER u WHERE p.userid = u.id AND p.topic='.$oT->id.' ';
  $oDB->query( sqlLimit($strState,'p.id ASC',0,1) );
  // ======
  $row=$oDB->getRow();
  $oInspectionPost = new CPost($row);
  $strButton='';
  if ( !empty($oInspectionPost->modifuser) ) $strButton .= '<td class="post-modif"><span class="small">&nbsp;'.L('Modified_by').' <a href="'.url('qti_user.php').'?id='.$oInspectionPost->modifuser.'" class="small">'.$oInspectionPost->modifname.'</a> ('.qtDatestr($oInspectionPost->modifdate,'$','$',true,true).')</span></td>'.PHP_EOL;
  if ( !empty($strButton) ) $strButton .= '<td>'.' '.'</td>'.PHP_EOL;
  if ( !empty($strButton) ) $strButton = '<table style="margin:10px 0 1px 0;"><tr>'.$strButton.'</tr></table>'.PHP_EOL;
  $oInspectionPost->text = qtInline($oInspectionPost->text); // Pre processing data (compact, no button)
  echo $oInspectionPost->render($oS,$oT,false,$strButton,QT_SKIN);
  // ======

}

// FORM START

echo '<form id="form-edit" method="post" action="'.url($oH->selfurl).'" enctype="multipart/form-data">
<div class="flex-sp">
<h2>'.$oH->selfname.'</h2>
';

if ( SUser::isStaff() ) {
  echo '<div id="optionsbar" title="'.L('Staff').' '.L('commands').'">'.qtSVG('user-M').'&nbsp;'.PHP_EOL;
  if ( $oP->type=='P' ) {
    echo L('Type').' <select id="newtopictype" name="topictype" size="1">'.qtTags(CTopic::getTypes(),$oT->type).'</select> ';
  } else {
    echo '<input type="hidden" id="newtopictype" name="topictype" value="'.$oT->type.'">'.PHP_EOL;
  }
  echo L('Status').' <select id="newtopicstatus" name="topicstatus" size="1">'.qtTags(CTopic::getStatuses($oT->type,true), $oT->status).'</select> ';
  echo '<span id="ac-wrapper-behalf" class="ac-wrapper">'.L('Send_on_behalf').'&nbsp;<input type="text" name="behalf" id="behalf" size="14" maxlength="24" value="'.$oP->username.'" autocomplete="off"/><input type="hidden" id="behalfid" name="behalfid" value="-1"></span></div>'; // end opitonsbar
  echo '</div>'.PHP_EOL; // end flex-sp
/*!!!
  if ( $oP->type=='P' ) {
    // initialize
    $arrStatus = SMem::get('_Statuses');
    $arrIcons = array();
    $arrNames = array();
    foreach(array_keys($arrStatus) as $k) {
      $arrIcons[$k] = isset($arrStatus[$k]['icon']) ? $arrStatus[$k]['icon'] : 'topic_tZ.gif';
      $arrNames[$k] = isset($arrStatus[$k]['name']) ? $arrStatus[$k]['name'] : L('Item');
    }
  }*/
}

echo '<div class="edit-post">
<div class="edit-type"><p class="i-container">'.CPost::getIconType($oP->type,$oT->type,$oT->status,QT_SKIN,'form-icon').'</p></div>
<div class="edit-form">
';
echo '
<input type="hidden" name="s" value="'.$s.'"/>
<input type="hidden" name="t" value="'.$t.'"/>
<input type="hidden" name="a" value="'.$a.'"/>
<input type="hidden" name="p" value="'.$oP->id.'"/>
<input type="hidden" id="userid" name="userid" value="'.SUser::id().'"/>
<input type="hidden" id="username" name="username" value="'.SUser::name().'"/>
<input type="hidden" name="oldtype" value="'.$oT->type.'"/>
<input type="hidden" name="oldstatus" value="'.$oT->status.'"/>
';
echo '<table>'.PHP_EOL;

// TITLE
if ( $oT->type==='I' && $oP->type!=='P' ) {
  echo '<tr>'.PHP_EOL;
  echo '<th>'.L('Score').'</th>'.PHP_EOL;
  $strLevel = $oT->getMF('param','Ilevel','3');
  $strSep = ' &nbsp; '; if ( $strLevel==='5' || $strLevel==='3' ) $strSep='<br>';
  $score = -1; if ( is_numeric($oP->title) ) $score = (float)$oP->title;
  echo '<td>'.htmlScore($strLevel,$strSep,(float)$score).'</td>'.PHP_EOL;
  echo '</tr>'.PHP_EOL;
} else {
  if ( $oS->titlefield!=0 ) {
    $strArgs = ''; if ( $oS->titlefield==2 && $oP->type==='P' ) $strArgs = ' required'; // required for topic in section having title required (but not for reply)
    echo '<tr>'.PHP_EOL;
    echo '<th>'.L('Title').'</th>'.PHP_EOL;
    echo '<td><input'.$strArgs.' type="text" id="title" name="title" size="80" maxlength="64" value="'.qtAttr($oP->title).'" tabindex="20"/></td>'.PHP_EOL;
    echo '</tr>'.PHP_EOL;
  }
}
// PREFIX
if ( !empty($oS->prefix) ) {
  echo '<tr>'.PHP_EOL;
  echo '<th>'.L('Prefix').'</th>'.PHP_EOL;
  echo '<td><span class="cblabel">'.PHP_EOL;
  for ($i=1;$i<10;$i++) {
    $str = icoPrefix($oS->prefix,$i);
    if ( !empty($str) ) echo '<input type="radio" name="icon" id="i0'.$i.'" value="0'.$i.'"'.($oP->icon=='0'.$i ? 'checked' : '').'/><label for="i0'.$i.'">'.$str.'</label> &nbsp;'.PHP_EOL;
  }
  echo '<input type="radio" name="icon" id="00" value="00"'.($oP->icon=='00' ? ' checked' : '').' tabindex="10"/><label for="00">'.L('None').'</label></td>'.PHP_EOL;
  echo '</span></tr>'.PHP_EOL;
}
// MESSAGE
echo '<tr>'.PHP_EOL;
echo '<th>'.L('Message').'</th>'.PHP_EOL;
echo '<td>'.PHP_EOL;
if ( QT_BBC ) echo '<div class="bbc-bar">'.bbcButtons($intBbc).'</div>';
echo PHP_EOL.'<a href="textarea"></a><textarea'.($oT->type!=='I' ? ' required': '').' id="text-area" name="text" '.(strlen($oP->text)>500 ? 'rows="25"' : 'rows="10"' ).' tabindex="25" maxlength="'.(empty($_SESSION[QT]['chars_per_post']) ? '4000' : $_SESSION[QT]['chars_per_post']).'">'.$oP->text.'</textarea>'.PHP_EOL;

if ( $canUpload ) echo '<p style="margin:0"><a id="tgl-ctrl" class="tgl-ctrl" href="javascript:void(0)" onclick="qtToggle(`tgl-container`,`table-row`); return false;">'.L('Attachment').qtSVG('angle-down','','',true).qtSVG('angle-up','','',true).'</a></p>';
echo '</td></tr>'.PHP_EOL;

// attachment
if ( $canUpload ) {
  $intMax = intval($_SESSION[QT]['upload_size'])*1024;
  echo '<tr id="tgl-container" style="display:'.(empty($oP->attach) ? 'none' : 'table-row').'">';
  echo '<th>'.qtSVG('paperclip', 'title='.L('Attachment')).'</th>';
  echo '<td>';
  if ( !empty($oP->attach) ) {
    if ( strpos($oP->attach,'/') ) { $str = substr(strrchr($oP->attach,'/'),1); } else { $str=$oP->attach; }
    if ( substr($str,0,strlen($oP->id.'_'))==($oP->id).'_' ) $str = substr($str,strlen($oP->id.'_'));
    echo $str.'<input type="hidden" id="oldattach" name="oldattach" value="'.$oP->attach.'"/>';
    echo ' &middot; <input type="checkbox" id="drop" name="drop[]" value="1"/><label for="drop">&nbsp;'.L('Drop_attachment').'</label>';
  } else {
    echo '<input type="hidden" name="MAX_FILE_SIZE" value="'.$intMax.'"/>';
    echo '<input tabindex="3" type="file" id="attach" name="attach" size="42"/>';
  }
  echo '</td></tr>'.PHP_EOL;
}

// WISHEDDATE (2 means Mandatory & no default, 3 default is today, 4 default day+1, 5 default is day+2)
if ( $oP->type=='P' && $oS->wisheddate!=0 ) {
  $strValue = ''; // no default
  if ( $oS->wisheddate>2 ) $strValue = ( $oS->wisheddate==3 ? date('Y-m-d') : date('Y-m-d',strtotime('+'.($oS->wisheddate-3).' day')) );
  if ( isset($_POST['wisheddate']) ) $strValue = $_POST['wisheddate'];
  if ( !empty($oT->wisheddate) ) $strValue = substr($oT->wisheddate,0,4).'-'.substr($oT->wisheddate,4,2).'-'.substr($oT->wisheddate,-2,2);

  echo '<tr>'.PHP_EOL;
  echo '<th>'.L('Wisheddate').'</th>'.PHP_EOL;
  echo '<td><input type="date" id="wisheddate" name="wisheddate" size="20" maxlength="10" value="'.$strValue.'" tabindex="30" min="'.date('Y-m-d').'"/> '.PHP_EOL;
  echo '<span title="'.L('dateSQL.Today').'" onclick="document.getElementById(`wisheddate`).value=`'.date('Y-m-d').'`;">'.qtSVG('calendar').'</span>'.PHP_EOL;
  echo '&nbsp;<span class="small">'.L('H_Wisheddate').'</span></td>'.PHP_EOL;
  echo '</tr>'.PHP_EOL;
}

// NOTIFIED
if ( $oS->notify==1 && $oP->type=='P' && $oS->notifycc!=0 ) {
  // default value
  $intValue = -1;
  $strValue = '';
  if ( $oS->notifycc==3 ) { $intValue = SUser::id(); $strValue = SUser::name(); }
  if ( $intNotified>=0 ) { $intValue = $intNotified; $strValue = $strNotified; }
  if ( $oT->notifiedid>=0 ) { $intValue = $oT->notifiedid; $strValue = $oT->notifiedname; }

  // row
  echo '<tr>'.PHP_EOL;
  echo '<th>'.L('Notify_also').'</th>'.PHP_EOL;
  echo '<td><input type="hidden" id="notifiedid" name="notifiedid" value="'.$intValue.'" tabindex="31"/><div id="ac-wrapper-notify" class="ac-wrapper"><input type="text" id="notify" name="notifiedname" size="20" maxlength="24" value="'.$strValue.'"/></div></td>'.PHP_EOL;
  echo '</tr>'.PHP_EOL;
}

// MAP coordinate field
if ( $oP->type=='P' && $bMap ) {
  echo '<tr><th>'.L('Coord').'</th><td><input type="text" id="yx" name="coord" size="32" value="'.(!empty($oT->y) ? $oT->y.','.$oT->x : '').'" tabindex="32"/> <span class="small">'.L('latlon').'</span></td></tr>'.PHP_EOL;
}

// SUBMIT

echo '<tr class="formsubmit">'.PHP_EOL;
echo '<th>&nbsp;</th>'.PHP_EOL;
echo '<td>'.PHP_EOL;

if ( $oT->type!=='I' && $oT->status!=='Z' && $oT->firstpostuser===SUser::id() ) {
  // topic status (from user)
  $bChecked = false;
  if ( isset($_POST['topicstatususer']) ) { if ( $_POST['topicstatususer'][0]==='Z' ) $bChecked=true; }
  echo '<input type="checkbox" id="topicstatususer" name="topicstatususer[]" value="Z"'.($bChecked ? ' checked' : ''),' tabindex="96"/><label for="topicstatususer">&nbsp;'.L('Close_my_item').' </label>';
}

echo '</td>'.PHP_EOL;
echo '</tr>'.PHP_EOL;
echo '</table>'.PHP_EOL;
// form end
echo '</div>
</div>
';

// ADD TAGS
if ( $_SESSION[QT]['tags']!=='0' && ($a==='nt' || ($a==='ed' && $oP->type==='P') ) ) {
  $arrTags=explode(';',$oT->descr);
  if ( $oT->status!=='1' ) {
    if ( SUser::isStaff() ) $tagEditor=true;
    if ( $_SESSION[QT]['tags']==='U' && SUser::id()===$oT->firstpostuser ) $tagEditor=true; // 'U'=members can edit in his own ticket
    if ( $_SESSION[QT]['tags']==='U+' && SUser::role()==='U' ) $tagEditor=true; // 'U+'=members can edit any tickets
    if ( $_SESSION[QT]['tags']==='V' ) $tagEditor=true; // 'V'=Visitor can edit any tickets
  }
  if ( $tagEditor ) {
    $arr = explode(';',$oT->descr);
    foreach($arr as $k=>$item) $arr[$k] = empty($item) ? '' : '<span class="tag" onclick="tagClick(this.innerHTML)">'.$item.'</span>';
    echo '<div class="tags right" style="padding:4px 0"><span class="tags" title="'.L('Tags').'">'.qtSVG('tag'.(count($arrTags)>1 ? 's' : '')).'</span>';
    echo ' <div id="tag-container" style="display:inline-block">';
    echo '<div id="tag-shown" style="display:inline-block">'.implode(' ',$arr).'</div> ';
    echo '<input type="hidden" id="tag-saved" value="'.qtAttr($oT->descr).'"/>';
    echo '<input type="hidden" id="tag-new" name="tag-new" maxlength="255" value="'.qtAttr($oT->descr).'"/>';
    echo '<input type="hidden" id="tag-dir" value="'.QT_DIR_DOC.'"/><input type="hidden" id="tag-lang" value="'.QT_LANG.'"/>';
    echo '<div id="ac-wrapper-tag-edit" class="ac-wrapper">';
    echo '<input type="text" id="tag-edit" size="12" maxlength="255" placeholder="'.L('Tags').'..." title="'.L('Edit_tags').'" autocomplete="off" data-multi="1"/><button type="reset" class="tag-btn" title="'.L('Reset').'" onclick="document.getElementById(`tag-edit`).value=``;qtFocus(`tag-edit`)">'.qtSVG('backspace').'</button>&nbsp;<button type="button" name="tag-btn" class="tag-btn" value="addtag" title="'.L('Add').'" onclick="tagAdd()">'.qtSVG('plus').'</button><button type="button" name="tag-btn" class="tag-btn" value="deltag" title="'.L('Delete_tags').'" onclick="tagDel()">'.qtSVG('minus').'</button>';
    echo '</div>';
    echo '</div></div>'.PHP_EOL;
  }
}

// map row
if ( $oP->type==='P' && $bMap ) {
  $oCanvas = new cCanvas();
  $strArgs = L('Gmap.cancreate');
  if ( isset($row) && !gmapEmptycoord($row) ) {
    $_SESSION[QT]['m_gmap_gcenter'] = $row['y'].','.$row['x'];
    $strArgs = L('Gmap.canmove');
  }
  $oCanvas->Header( array(), array($strArgs,'add','del'), '', 'header right' );
  $oCanvas->Footer( 'find' ,'', 'footer right' );
  echo $oCanvas->Render(false,'','gmap edit').PHP_EOL;
}

// FORM END
echo '<p class="submit">
<button type="button" tabindex="98" onclick="window.location=`'.$oH->exit().'`;">'.L('Cancel').'</button>&nbsp;
<button type="submit" id="form-edit-preview" name="dopreview" value="'.$certificate.'" tabindex="99" onclick="this.form.dataset.state=0">'.L('Preview').'...</button>&nbsp;
<button type="submit" id="dosend" name="dosend" value="'.$certificate.'" tabindex="97" onclick="this.form.dataset.state=1">'.L('Send').'</button>
</p>
</form>
';

// PREVIOUS POSTS (not for inspection)

if ( $oT->type!=='I' && ($a==='re' || $a==='qu') ) {

  echo '<div class="view-c">'.PHP_EOL;
  echo '<h2>'.L('Previous_posts').'</h2>'.PHP_EOL;
  // ======
  $strState = 'p.*,u.role,u.location,u.signature FROM TABPOST p, TABUSER u WHERE p.userid = u.id AND p.topic='.$oT->id.' ';
  $oDB->query( sqlLimit($strState,'p.id DESC',0,5) );
  // ======
  $iMsgNum = $oT->items + 2;
  $intWhile= 0;
  // ======
  while($row=$oDB->getRow())
  {
    $iMsgNum = $iMsgNum+1;
    $oP = new CPost($row,$iMsgNum);
    echo $oP->render($oS,$oT,false,true,QT_SKIN);
    ++$intWhile;
  }
  // ======
  echo '</div>'.PHP_EOL;

}

// HTML END

if ( $tagEditor || SUser::isStaff() ) {

$oH->scripts['ac'] = '<script type="text/javascript" src="bin/js/qt_ac.js"></script>
<script type="text/javascript" src="bin/js/qti_config_ac.js"></script>';
$oH->scripts[] = '<script type="text/javascript" src="bin/js/qt_tags.js"></script>';
$oH->scripts[] = 'acOnClicks["behalf"] = function(focusInput,btn) {
  if ( focusInput.id=="behalf" ) document.getElementById("behalfid").value = btn.dataset.id;
}
const arrStatus = {
"T":'.json_encode(CTopic::getStatuses('T')).',
"A":'.json_encode(CTopic::getStatuses('A')).',
"I":'.json_encode(CTopic::getStatuses('I')).'
}
function changeIcon() {
  const type = selectType.value.toUpperCase();
  const status = selectStatus.value.toUpperCase();
  const d = document.querySelector(".i-container img"); if ( !d ) return;
  d.setAttribute("data-type", type.toLowerCase());
  d.setAttribute("data-status", status.toLowerCase());
  d.setAttribute("alt", type);
  d.setAttribute("title", selectType.options[selectType.selectedIndex].text + " " + selectStatus.options[selectStatus.selectedIndex].text)
  let ico = "img/ico_status0.gif";
  switch(type) {
    case "A": ico = "img/topic_a_0.gif"; break;
    case "I": ico = "img/topic_i_0.gif"; break;
    case "T": if ( arrStatus.T.hasOwnProperty(status) && arrStatus["T"][status].hasOwnProperty("icon") ) ico = "img/"+arrStatus[type][status]["icon"]; break;
  }
  d.setAttribute("src", d.getAttribute("src").replace(/img\/.*/, ico));
}
function changeStatusOptions(type) {
  if ( !selectStatus ) return;
  while ( selectStatus.options.length>0 ) selectStatus.remove(0);
  Object.keys(arrStatus[type]).forEach(key => {
    let newText = type==="T" ? arrStatus[type][key]["name"] : arrStatus[type][key];
    let newOption = new Option(newText,key);
    selectStatus.appendChild(newOption);
  });
}
const selectType = document.getElementById("newtopictype");
const selectStatus = document.getElementById("newtopicstatus");
if ( selectType && selectStatus) {
  selectType.addEventListener("change", () => {
    let sType = selectType.value.toUpperCase();
    let sStatus = selectStatus.value.toUpperCase();
    changeStatusOptions(sType);
    if ( sType!=="T" && sStatus!=="A" && sStatus!=="Z" ) selectStatus.value = "A";
    changeIcon();
  });
  selectStatus.addEventListener("change", () => {
    changeIcon();
  });
}';

}

$oH->scripts[] = 'const btnPreview = document.getElementById("form-edit-preview");
btnPreview.addEventListener("click", (e) => {
  if ( document.getElementById("newtopictype").value!=="I" && document.getElementById("text-area").value.length===0 ) return false;
  e.preventDefault();
  let formData = new FormData(document.getElementById("form-edit"));
  fetch( "qti_edit_preview.php", {method:"POST", body:formData} )
  .then( response => response.text() )
  .then( data => {
    document.getElementById("message-preview").innerHTML = data;
    document.querySelectorAll("#message-preview a").forEach( anchor => {anchor.href="javascript:void(0)"; anchor.target="";} );
    })
  .catch( err => console.log(err) );
});
';

// MAP MODULE

if ( $bMap ) {
  /**
  * @var array $gmap_markers
  * @var array $gmap_events
  * @var array $gmap_functions
  */
  $gmap_shadow = false;
  $gmap_symbol = false;
	// add extra $oMapPoint properties (if defined in section settings)
	$oSettings = getMapSectionSettings($s);
	if ( is_object($oSettings) && property_exists($oSettings,'icon') ) $gmap_symbol = $oSettings->icon;

  // check new map center
  $y = floatval(QTgety($_SESSION[QT]['m_gmap_gcenter']));
  $x = floatval(QTgetx($_SESSION[QT]['m_gmap_gcenter']));

  // First item is the item's location and symbol
  if ( isset($arrExtData[$oT->id]) ) {
    // symbol by role
    $oMapPoint = $arrExtData[$oT->id];
    if ( !empty($oMapPoint->icon) ) $gmap_symbol = $oMapPoint->icon;
    // center on first item
    if ( !empty($oMapPoint->y) && !empty($oMapPoint->x) ) {
    $y=$oMapPoint->y;
    $x=$oMapPoint->x;
    }
  }

  // update center
  $_SESSION[QT]['m_gmap_gcenter'] = $y.','.$x;

  $gmap_markers = array();
  $gmap_events = array();
  $gmap_functions = array();
  foreach($arrExtData as $oMapPoint) {
    if ( !empty($oMapPoint->y) && !empty($oMapPoint->x) ) {
      $strSymbol = $gmap_symbol; // required to reset symbol on each user
      $strShadow = $gmap_shadow;
      if ( !empty($oMapPoint->icon) ) $strSymbol  = $oMapPoint->icon;
      $gmap_markers[] = gmapMarker($oMapPoint->y.','.$oMapPoint->x, true, $strSymbol, $oMapPoint->title, $oMapPoint->info, $strShadow );
    }
  }

  $gmap_events[] = '
	google.maps.event.addListener(markers[0], "position_changed", function() {
		if ( document.getElementById("yx")) {document.getElementById("yx").value = gmapRound(marker.getPosition().lat(),10) + "," + gmapRound(marker.getPosition().lng(),10);}
	});
	google.maps.event.addListener(markers[0], "dragend", function() {
		map.panTo(marker.getPosition());
	});';
  $gmap_functions[] = '
  function showLocation(address,title)
  {
    if ( infowindow ) infowindow.close();
    geocoder.geocode( { "address": address}, function(results, status) {
      if ( status == google.maps.GeocoderStatus.OK)
      {
        map.setCenter(results[0].geometry.location);
        if ( markers[0] )
        {
          markers[0].setPosition(results[0].geometry.location);
        } else {
          markers[0] = new google.maps.Marker({map: map, position: results[0].geometry.location, draggable: true, animation: google.maps.Animation.DROP, title: title});
        }
        gmapYXfield("yx",markers[0]);
      } else {
        alert("Geocode was not successful for the following reason: " + status);
      }
    });
  }
  function createMarker()
  {
    if ( !map ) return;
    if ( infowindow) infowindow.close();
    deleteMarker();
    '.gmapMarker('map',true,$gmap_symbol).'
    gmapYXfield("yx",markers[0]);
    google.maps.event.addListener(markers[0], "position_changed", function() { gmapYXfield("yx",markers[0]); });
    google.maps.event.addListener(markers[0], "dragend", function() { map.panTo(markers[0].getPosition()); });
  }
  function deleteMarker()
  {
    if ( infowindow) infowindow.close();
    for(var i=markers.length-1;i>=0;i--) markers[i].setMap(null);
    gmapYXfield("yx",null);
    markers=[];
  }
  ';

  include 'qtim_gmap_load.php';
}

include 'qti_inc_ft.php';