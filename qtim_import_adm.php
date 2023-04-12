<?php // v4.0 build:20230205

session_start();
/**
* @var string $error
* @var string $strValue
* @var int $t
* @var int $p
* @var int $intTopicInsertId
* @var int $intPostInsertId
* @var CHtml $oH
* @var array $arrTopic
* @var array $arrPosts
* @var array $L
* @var CDatabase $oDB
*/
require 'bin/init.php';
include translate('lg_adm.php');
include translate('qtim_import.php');
if ( SUser::role()!=='A' ) die(L('E_13'));

// FUNCTIONS

function startElement($parser, $strTag, $arrTagAttr)
{
  $strTag = strtolower($strTag);
  global $arrTopic,$arrPosts;
  global $t,$p;

  switch($strTag)
  {
  case 'topic':
    $arrTopic = array();
    $arrPosts = array();
    if ( isset($arrTagAttr['ID']) ) { $t=intval($arrTagAttr['ID']); } else { $t=0; }
    $arrTopic['id'] = $t;
    $arrTopic['type'] = (isset($arrTagAttr['TYPE']) ? $arrTagAttr['TYPE'] : 'T');
    break;
  case 'post':
    if ( isset($arrTagAttr['ID']) ) { $p=intval($arrTagAttr['ID']); } else { $p=0; }
    $arrPosts[$p] = array();
    $arrPosts[$p]['id'] = $p;
    $arrPosts[$p]['type'] = (isset($arrTagAttr['TYPE']) ? $arrTagAttr['TYPE'] : 'P');
    break;
  }
}
function characterData($parser, $data)
{
  global $strValue;
  $strValue = trim($data);
}
function endElement($parser, $strTag)
{
  $strTag = strtolower($strTag);

  global $arrTopic,$arrPosts;
  global $t,$p,$intTopicInsertId,$intPostInsertId;
  global $strValue;
  global $oDB, $arrCounts;

  switch($strTag)
  {
  case 'x':         $arrTopic['x']=$strValue; break;
  case 'y':         $arrTopic['y']=$strValue; break;
  case 'z':         $arrTopic['z']=$strValue; break;
  case 'tags':      if ( !$_SESSION['m_import_xml']['droptags'] ) { $arrTopic['tags']=$strValue; } break;
  case 'eventdate': $arrTopic['eventdate']=$strValue; break;
  //case 'wisheddate':$arrTopic['wisheddate']=$strValue; break;
  case 'firstpostdate': if ( $_SESSION['m_import_xml']['dropdate'] ) { $arrTopic['firstpostdate']=date('Ymd His'); } else { $arrTopic['firstpostdate']=$strValue; } break;
  case 'lastpostdate': if ( $_SESSION['m_import_xml']['dropdate'] ) { $arrTopic['lastpostdate']=date('Ymd His'); } else { $arrTopic['lastpostdate']=$strValue; } break;
  //case 'param':     $arrTopic['param']=$strValue; break;

  case 'icon':     $arrPosts[$p]['icon']=$strValue; break;
  case 'title':    $arrPosts[$p]['title']=$strValue; break;
  case 'userid':   $arrPosts[$p]['userid']=0; break; //userid must be reset to 0
  case 'username': $arrPosts[$p]['username']=$strValue; break;
  case 'issuedate':if ( $_SESSION['m_import_xml']['dropdate'] ) { $arrPosts[$p]['issuedate']=date('Ymd His'); } else { $arrPosts[$p]['issuedate']=$strValue; } break;
  case 'modifdate':$arrPosts[$p]['modifdate']=$strValue; break;
  case 'modifuser':$arrPosts[$p]['modifuser']=0; break; //userid must be reset to 0
  case 'modifname':$arrPosts[$p]['modifname']=$strValue; break;
  case 'textmsg':  $arrPosts[$p]['textmsg']=$strValue; break;
  case 'posts':    $arrTopic['posts']=$arrPosts; break;

  case 'topic':

    // Process topic

    $oT = new CTopic($arrTopic);
    $oT->pid = $_SESSION['m_import_xml']['dest'];
    $oT->id = $intTopicInsertId; ++$intTopicInsertId;
    $oT->status = $_SESSION['m_import_xml']['status'];

    $oT->insertTopic(false);
    ++$arrCounts['topic'];

    // Process posts

    foreach($arrTopic['posts'] as $arrPost)
    {
      $oP = new CPost($arrPost); if ( $_SESSION['m_import_xml']['dropreply'] && $oP->type!='P' ) break;
      $oP->id = $intPostInsertId; ++$intPostInsertId;
      $oP->topic = $oT->id;
      $oP->section = $_SESSION['m_import_xml']['dest'];
      if ( $_SESSION['m_import_xml']['dropbbc'] ) $oP->text = QTunbbc($oP->text,true,L('Bbc.*'));

      $oP->insertPost(false,false);
      if ( $oP->type!='P' ) ++$arrCounts['reply']; // count only the replies
    }

    // Topic stats

    $oT->updMetadata(); // This update firstpost/lastpost (and do not perform close-topic check)

    break;

  default:
    if ( trim($strValue)!='' ) $arrTopic[$strTag]=$strValue;
    break;
  }
}

// INITIALISE

$intDest   = -1;
$strStatus = '1';
$bDropbbc  = false;
$bDropreply= false;
$bDroptags = false;
$bDropdate = false;
$arrCounts = array('topic'=>0,'reply'=>0);

$oH->selfurl = 'qtim_import_adm.php';
$oH->selfname = L('Import_Admin');
$oH->selfparent = L('Module');
$oH->selfversion = L('Import_Version').' 4.0';
$oH->exiturl = $oH->selfurl;
$oH->exitname = $oH->selfname;

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{
  // check file

  if (!is_uploaded_file($_FILES['title']['tmp_name'])) $error = $L['Import_E_nofile'];

  // check form value

  if ( empty($error) )
  {
    if ( isset($_POST['dropbbc']) ) $bDropbbc=true;
    if ( isset($_POST['dropreply']) ) $bDropreply=true;
    if ( isset($_POST['droptags']) ) $bDroptags=true;
    if ( isset($_POST['dropdate']) ) $bDropdate=true;
    $intDest = intval($_POST['section']);
    $strStatus = $_POST['status'];
    $_SESSION['m_import_xml']=array('dest'=>$intDest,'status'=>$strStatus,'dropbbc'=>$bDropbbc,'dropreply'=>$bDropreply,'droptags'=>$bDroptags,'dropdate'=>$bDropdate);
  }

  // check format

  if ( empty($error) )
  {
    if ( $_FILES['title']['type']!='text/xml' )
    {
    $error = $L['Import_E_format'];
    unlink($_FILES['title']['tmp_name']);
    }
  }

  // import xml

  if ( empty($error) )
  {
    $arrTopic = array();
    $arrPosts = array();
    $t = 0;
    $p = 0;
    $strValue = '';

    $intTopicInsertId = $oDB->nextId(TABTOPIC);
    $intPostInsertId = $oDB->nextId(TABPOST);

    $xml_parser = xml_parser_create();
    xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, true);
    xml_set_element_handler($xml_parser, 'startElement', 'endElement'); // SQL is precessed in the endElement function
    xml_set_character_data_handler($xml_parser, 'characterData');
    if ( !($fp = fopen($_FILES['title']['tmp_name'],'r')) ) die('could not open XML input');
    while ($data = fread($fp,4096))
    {
      if ( !xml_parse($xml_parser, $data, feof($fp)) ) die(sprintf('XML error: %s at line %d', xml_error_string(xml_get_error_code($xml_parser)), xml_get_current_line_number($xml_parser)));
    }
    xml_parser_free($xml_parser);

  }

  if ( empty($error) )
  {
    // Clean file

    unlink($_FILES['title']['tmp_name']);

    // Update section stats and system stats

    CSection::updLastPostDate($intDest); //!!!
    CSection::updEachItemReplies($intDest);

    // End message (pause)

    $oH->pageMessage('', '<p>'.L('Item',$arrCounts['topic']).'<br>'.L('Reply',$arrCounts['reply']).'</p><br>'.$L['Import_S_import'].'<p><a href="'.$oH->exiturl.'">'.$oH->exitname.'</a></p>', 'admin');
  }
}

// --------
// HTML BEGIN
// --------

include APP.'_adm_inc_hd.php';

if ( isset($_SESSION['m_import_xml']['dest']) )      $intDest   = $_SESSION['m_import_xml']['dest'];
if ( isset($_SESSION['m_import_xml']['status']) )    $strStatus = $_SESSION['m_import_xml']['status'];
if ( isset($_SESSION['m_import_xml']['dropbbc']) )   $bDropbbc  = $_SESSION['m_import_xml']['dropbbc'];
if ( isset($_SESSION['m_import_xml']['dropreply']) ) $bDropreply= $_SESSION['m_import_xml']['dropreply'];
if ( isset($_SESSION['m_import_xml']['droptags']) )  $bDroptags = $_SESSION['m_import_xml']['droptags'];
if ( isset($_SESSION['m_import_xml']['dropdate']) )  $bDropdate = $_SESSION['m_import_xml']['dropdate'];

echo '<form method="post" action="'.$oH->selfurl.'" enctype="multipart/form-data">
<input type="hidden" name="maxsize" value="5242880"/>

<h2 class="config">'.$L['Import_File'].'</h2>
<table class="t-conf">
<tr>
<th>'.$L['Import_File'].'</th>
<td><input type="file" id="title" name="title" required/></td>
</tr>
';
if ( $_SESSION[QT]['board_offline']=='0' )
{
echo '
<tr>
<th>'.getSVG('exclamation-triangle').'</th>
<td>It is recommended to turn the board off-line while importing. <a href="qti_adm_index.php">Board status...</a></td>
</tr>
';
}
echo '</table>
';

echo '
<h2 class="config">'.$L['Import_Content'].'</h2>
<table class="t-conf">
<tr>
<th>'.$L['Import_Drop_tags'].'</th>
<td><input type="checkbox" id="droptags" name="droptags"'.($bDroptags ? ' checked' : '').'/> <label for="droptags">'.$L['Import_HDrop_tags'].'</label></td>
</tr>
<tr>
<th>'.$L['Import_Drop_reply'].'</th>
<td><input type="checkbox" id="dropreply" name="dropreply"'.($bDropreply ? ' checked' : '').'/> <label for="dropreply">'.$L['Import_HDrop_reply'].'</label></td>
</tr>
<tr>
<th>'.$L['Import_Drop_bbc'].'</th>
<td><input type="checkbox" id="dropbbc" name="dropbbc"'.($bDropbbc ? ' checked' : '').'/> <label for="dropbbc">'.$L['Import_HDrop_bbc'].'</label></td>
</tr>
</table>
';
echo '<h2 class="config">'.$L['Destination'].'</h2>
<table class="t-conf">
<tr>
<th style="width:200px"><label for="section">'.$L['Import_Destination'].'</label></td>
<td><select id="section" name="section">'.sectionsAsOption().'</select> <a href="qti_adm_sections.php">'.$L['Section_add'].'</a></td>
</tr>
<tr>
<th><label for="status">'.$L['Status'].'</label></th>
<td><select id="status"  name="status">
'.asTags(CTopic::getStatuses(),$strStatus).'</select></td>
</tr>
<tr>
<th>'.$L['Import_Dropdate'].'</th>
<td><input type="checkbox" id="dropdate" name="dropdate"'.($bDropdate ? ' checked' : '').'/> <label for="dropdate">'.$L['Import_HDropdate'].'</label></td>
</tr>
</table>
';
echo '<p class="submit"><button type="submit" name="ok" value="ok">'.L('Ok').'</button></p>
</form>
';

// HTML END

include APP.'_adm_inc_ft.php';