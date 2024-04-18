<?php // 4.0 build:20240210

// This generates a preview message (href links are disabled)
// Data come from qti_edit.php by a ajax POST request. The preview is included in the form
// Attachment cannot be rendered (only filename is displayed)

session_start();
/**
 * @var CHtml $oH
 * @var CDatabase $oDB
 */
require 'bin/init.php';
$oH->selfurl = 'qti_edit_preview.php';

$a = '';
$s = -1;
$t = -1;
qtArgs('int:s! a! int:t!',false,true); // in POST only
if ( $s<0 ) die('Missing parameters: section id');
if ( !in_array($a,['nt','re','ed','qu','de']) ) die('Invalid parameter a');

// INITIALISE

$error = '';
$oH->selfname = L('Message');
$intNotified=-1;
$strNotified='';
$oS = new CSection($s);
$oT = new CTopic($t);
$oP = new CPost(); if ( isset($_POST['p']) ) $oP->id = (int)$_POST['p']; // id = -1 while preview
$oP->type = 'R';
$oP->issuedate = date('Y-m-d H:i:s');
$oP->text = trim($_POST['text']);
$oP->userid = (int)$_POST['userid'];
$oP->username = $_POST['username'];
$oP->modifuser = (int)$_POST['userid']; // can be onbehalf
$oP->modifname = $_POST['username'];
if ( empty($oP->modifname) ) { $oP->modifuser = SUser::id(); $oP->modifname = SUser::id(); }

// ------
// SUBMITTED
// ------
try {

  // For New post (or Reply or Quote) creator=modifuser, while creator don't change when editing existing post
  if ( $a!='ed' ) {
    $oP->userid = $oP->modifuser;
    $oP->username = $oP->modifname;
  }

  // Read submitted form values
  if ( isset($_POST['icon']) )   $oP->icon = substr($_POST['icon'],0,2);
  if ( isset($_POST['title']) )  $oP->title = qtInline(trim($_POST['title']),64);
  if ( isset($_POST['attach']) ) $oP->attach = $_POST['attach'];
  if ( isset($_POST['tag-new']) ) $oT->descr = trim($_POST['tag-new']);
  if ( strlen($oP->text)>$_SESSION[QT]['chars_per_post'] ) { $oP->text = substr($oP->text,0,255).' [...]'; throw new Exception( L('E_too_long').' '.sprintf(L('E_char_max'), $_SESSION[QT]['chars_per_post']) ); }
  if ( substr_count($oP->text,"\n")>$_SESSION[QT]['lines_per_post'] ) { $oP->text = substr($oP->text,0,255).' [...]'; throw new Exception( L('E_too_long').' '.sprintf(L('E_line_max'), $_SESSION[QT]['lines_per_post']) ); }
  $oT->preview = qtInline($oP->text);

  // Detect basic errors
  if ( $oP->text=='' && !isset($_POST['inspection']) ) throw new Exception( L('Message').' '.L('invalid') ); //...
  if ( $a=='nt' && $oP->title=='' && $oS->titlefield==2 ) throw new Exception( L('E_no_title') ); //...
  if ( $a=='nt' && $oP->title=='' ) CPost::makeTitle($oP);
  if ( isset($_POST['inspection']) ) {
    $oP->title = $_POST['inspection']==='pc' ? $_POST['inspectionvalue'] : $_POST['inspection'];
    $oP->title = $oP->getScoreImage($oT);
  }

  // Check flood limit
  if ( !empty($_SESSION[QT.'_usr']['lastpost']) && $_SESSION[QT.'_usr']['lastpost']+QT_FLOOD >= time() ) throw new Exception( L('E_wait') ); //...

  // check maximum post per day (not for moderators)
  if ( !SUser::isStaff() && !postsTodayAcceptable((int)$_SESSION[QT]['posts_per_day']) ) {
    $oH->exiturl = 'qti_items.php?s='.$s;
    $oH->voidPage('', L('E_too_much')); //...
  }

  // Module antispam
  if ( qtModule('antispam') ) include 'qtim_antispam.php';

  // check upload
  if ( $_SESSION[QT]['upload']!=='0' && !empty($_FILES['newdoc']['name']) ) {
    include 'config/config_upload.php';
    fileValidate($_FILES['newdoc'],ALLOWED_FILE_EXT,ALLOWED_MIME_TYPE,intval($_SESSION[QT]['upload_size'])*1024+16);
  }

  // Other POSTS
  //... tags is not previewed (part of the topic)
  if ( isset($_POST['notifiedname']) ) $strNotified = trim($_POST['notifiedname']);
  // complete if missing behalf name
  if ( $strNotified!=='' ) {
    $arrNames = getUsers('N',$strNotified,1);
    if ( count($arrNames)!==1 ) throw new Exception( L('Notify_also').' '.L('invalid') ); //...
    $intNotified = array_key_first($arrNames);
  }
  if ( $oS->notifycc=='2' && $intNotified<0 && $_POST['a']=='nt' ) throw new Exception( L('Notify_also').' '.L('missing') ); //...
  if ( $oS->wisheddate=='2' && empty($_POST['wisheddate']) && $_POST['a']=='nt' ) throw new Exception( L('Wisheddate').' '.L('missing') ); //...

} catch (Exception $e) {

  $error = $e->getMessage();

}

// PREPARE DISPLAY
if ( $_POST['a']=='nt' ) { $oH->selfname = L('New_item'); $oP->type = 'P'; }
if ( $_POST['a']=='ed' ) $oH->selfname = L('Edit');
if ( $_POST['a']=='qu' || $_POST['a']=='re' ) $oH->selfname = L('Reply');

// get user info
$oDB->query( 'SELECT signature,location,role FROM TABUSER WHERE id='.$oP->userid);
$row = $oDB->getRow();
$oP->userloca = $row['location'];
$oP->usersign = $row['signature'];
$oP->userrole = $row['role'];

// ------
// HTML PART
// ------
echo '<h2>'.L('Preview').'</h2>
';
if ( !empty($error) ) echo '<p><span class="error">'.$error.'</span></p>';

echo $oP->render($oS,$oT,true,'',QT_SKIN,'1');

echo '<p class="right">'.( empty($oP->attach) ? '' : '<small>'.qtSVG('info').' '.L('No_attachment_preview').'</small>').'</p><br>';