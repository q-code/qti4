<?php // v4.0 build:20240210

session_start();
/**
 * @var CHtml $oH
 * @var CDatabase $oDB
 */
require 'bin/init.php';
include translate('lg_adm.php');

if ( SUser::role()!=='A' ) die('Access denied');

// INITIALISE

$oH->name = L('Board_layout');
$parentname = L('Settings');

// ------
// SUBMITTED
// ------
if ( isset($_POST['ok']) ) try {

  // Check style exists and inputs
  if ( !file_exists('skin/'.$_POST['skin'].'/'.APP.'_styles.css') ) {
    $_POST['skin'] = 'default';
    throw new Exception( L('Section_skin').' '.L('invalid').' ('.APP.'_styles.css not found). Use default.' );
  }
  if ( isset($_POST['home_name']) && empty(trim($_POST['home_name'])) ) throw new Exception( L('Home_website_name').' '.L('not_empty') );
  if ( isset($_POST['home_url']) && (strlen($_POST['home_url'])<10 || !preg_match('/^(http:\/\/|https:\/\/)/', $_POST['home_url'])) ) throw new Exception( L('Site_url').' '.L('invalid') );

  // read submitted
  $_SESSION[QT]['skin_dir'] = 'skin/'.$_POST['skin'].'/';
  $_SESSION[QT]['show_welcome'] = $_POST['show_welcome'];
  $_SESSION[QT]['show_legend'] = $_POST['show_legend'];
  $_SESSION[QT]['show_banner'] = $_POST['show_banner'];
  $_SESSION[QT]['home_menu'] = $_POST['home_menu'];
  if ( $_SESSION[QT]['home_menu']==='1' ) {
    $_SESSION[QT]['home_name'] = isset($_POST['home_name']) ? qtDb($_POST['home_name']) : L('Home');
    $_SESSION[QT]['home_url'] = isset($_POST['home_url']) ? $_POST['home_url'] : 'http://';
  }
  $_SESSION[QT]['item_firstline'] = $_POST['item_firstline'];
  $_SESSION[QT]['news_on_top'] = $_POST['news_on_top'];
  $_SESSION[QT]['items_per_page'] = $_POST['items_per_page'];
  $_SESSION[QT]['replies_per_page'] = $_POST['replies_per_page'];
  $_SESSION[QT]['show_quick_reply'] = $_POST['show_quick_reply'];
  //$_SESSION[QT]['bbc'] = $_POST['bbc'];

  // Save values
  $oDB->updSetting(['skin_dir','show_welcome','show_banner','show_legend','home_menu','home_name','home_url','items_per_page','replies_per_page','item_firstline','news_on_top','show_quick_reply']);

  // Successfull end
  $_SESSION[QT.'splash'] = L('S_save');

} catch (Exception $e) {

  $_SESSION[QT.'splash'] = 'E|'.L('E_failed');
  $oH->error = $e->getMessage();

}

// ------
// HTML BEGIN
// ------
include APP.'_adm_inc_hd.php';

// Get skin subfolders (without /)
$intHandle = opendir('skin');
$arrFiles = [];
while(false!==($strFile=readdir($intHandle))) if ( $strFile!='.' && $strFile!='..' && is_dir('skin/'.$strFile) ) $arrFiles[$strFile]=ucfirst($strFile);
closedir($intHandle);
asort($arrFiles);

// Current skin subfolder (without /)
$currentCss = substr($_SESSION[QT]['skin_dir'],5,-1);
$customCss = is_writable($_SESSION[QT]['skin_dir'].'custom.css') ? $_SESSION[QT]['skin_dir'].'custom.css' : '';
$welcomeTxt = is_writable('language/'.QT_LANG.'/app_welcome.txt') ? 'language/'.QT_LANG.'/app_welcome.txt' : '';

// FORM
echo '<form class="formsafe" method="post" action="'.$oH->php.'">
<h2 class="config">'.L('Skin').'</h2>
<table class="t-conf">
<tr title="'.L('H_Board_skin').'">
<th>'.L('Board_skin').'</th>
<td class="flex-sp"><select name="skin" onchange="toggleCustomCss(this.value,`'.$currentCss.'`);">'.qtTags($arrFiles,$currentCss).'</select><small id="custom-css">'.(empty($customCss) ? '' : '('.L('and').' custom.css <a href="tool_txt.php?exit=qti_adm_skin.php&file='.$customCss.'" title="'.L('Edit').'">'.qtSvg('pen-square').'</a>)').'</small></td>
</tr>
<tr title="'.L('H_Show_banner').'">
<th>'.L('Show_banner').'</th>
<td><select name="show_banner">'.qtTags(array(L('Show_banner0'),L('Show_banner1'),L('Show_banner2')),(int)$_SESSION[QT]['show_banner']).'</select></td>
</tr>
<tr title="'.L('H_Show_welcome').'">
<th>'.L('Show_welcome').'</th>
<td class="flex-sp"><select name="show_welcome">';
echo qtTags([2=>L('Y'),0=>L('N'),1=>L('While_unlogged')], $_SESSION[QT]['show_welcome'] );
echo '</select><small id="welcome-txt">'.(empty($welcomeTxt) ? '' : ' ('.L('edit').' '.L('file').' <a href="tool_txt.php?exit=qti_adm_skin.php&file='.$welcomeTxt.'" title="'.L('Edit').'">'.qtSvg('pen-square').'</a>)').'</small></td>
</tr>
</table>
';
echo '<h2 class="config">'.L('Layout').'</h2>
<table class="t-conf">
';
$ipp = (int)$_SESSION[QT]['items_per_page']; if ( !in_array($ipp, PAGE_SIZES) ) $ipp = PAGE_SIZES[0]; // auto-adjust if config changed
$arr = array_combine(PAGE_SIZES, array_map(function($size){return $size.' / '.L('page');}, PAGE_SIZES));
echo '<tr title="'.L('H_Items_per_section_page').'">
<th><label for="items_per_page">'.L('Items_per_section_page').'</label></th>
<td><select id="items_per_page" name="items_per_page">
'.qtTags($arr,$ipp).'
</select></td>
</tr>
';
$ipp = (int)$_SESSION[QT]['replies_per_page']; if ( !in_array($ipp, PAGE_SIZES) ) $ipp = PAGE_SIZES[0]; // auto-adjust if config changed
$arr = array_combine(PAGE_SIZES, array_map(function($size){return $size.' / '.L('page');}, PAGE_SIZES));
echo '<tr title="'.L('H_Replies_per_item_page').'">
<th><label for="replies_per_page">'.L('Replies_per_item_page').'</label></th>
<td><select id="replies_per_page" name="replies_per_page">
'.qtTags($arr,$ipp).'
</select></td>
</tr>
';
echo '<tr title="'.L('H_Show_legend').'">
<th>'.L('Show_legend').'</th>
<td>
<select name="show_legend">'.qtTags([L('N'),L('Y')],(int)$_SESSION[QT]['show_legend']).'</select>
</td>
</tr>
</table>
';
if ( $_SESSION[QT]['home_menu']==='1' && empty($_SESSION[QT]['home_name']) ) $_SESSION[QT]['home_name'] = L('Home');
if ( $_SESSION[QT]['home_menu']==='1' && empty($_SESSION[QT]['home_url']) ) $_SESSION[QT]['home_url'] = 'http://';
echo '<h2 class="config">'.L('Your_website').'</h2>
<table class="t-conf">
<tr title="'.L('H_Home_website_name').'">
<th>'.L('Add_home').'</th>
<td>
<select name="home_menu" onchange="toggleHome(this.value);">'.qtTags([L('N'),L('Y')],(int)$_SESSION[QT]['home_menu']).'</select>
&nbsp;<input type="text" id="home_name" name="home_name" size="20" maxlength="64" value="'.qtAttr($_SESSION[QT]['home_name'],64),'"',($_SESSION[QT]['home_menu']=='0' ? ' disabled' : '').'/></td>
</tr>
<tr title="'.L('H_Website').'">
<th>'.L('Home_website_url').'</th>
<td><input required type="text" id="home_url" name="home_url" pattern="^(http://|https://).*" size="40" maxlength="255" value="'.qtAttr($_SESSION[QT]['home_url']),'"',($_SESSION[QT]['home_menu']=='0' ? ' disabled' : '').'/></td>
</tr>
<tr id="home_url_help"'.($_SESSION[QT]['home_menu'] ? '': ' style="display:none"').'>
<td colspan="2" class="asterix">
'.L('Use_|_add_attributes').' <span style="color:#1364B7">http://www.site.com | target=_blank</span>
</td>
</tr>
</table>
';
// Start helper
if ( $_SESSION[QT]['home_menu'] && (strlen($_SESSION[QT]['home_url'])<10 || !preg_match('/^(http:\/\/|https:\/\/)/',$_SESSION[QT]['home_url'])) ) echo '<p>'.qtSvg('flag', 'style=font-size:1.4rem;color:#1364B7').' '.L('Home_website_url').' '.L('invalid').'</p>';

if ( !isset($_SESSION[QT]['item_firstline']) ) $_SESSION[QT]['item_firstline']='1'; // new in v4.0
$sections = sectionsAsOption();

echo '<h2 class="config">'.L('Section+').' '.L('display_options').'</h2>
<table class="t-conf">
<tr title="'.L('H_Item_firstline').'">
<th>'.L('Item_firstline').'</th>
<td style="display:flex;justify-content:space-between">
<select name="item_firstline">'.qtTags(array(L('N'),L('Y'),L('By_section')),(int)$_SESSION[QT]['item_firstline']).'</select>
<small style="display:'.($_SESSION[QT]['item_firstline']==2 ? 'inline' : 'none').'">
&nbsp;'.L('Edit').' '.L('options').'
<select onchange="if ( this.value>=0) window.location=`qti_adm_section.php?pan=2&s=` + this.value;">
<option value="-1" selected>'.L('Section').'...</option>
'.$sections.'</select>
</span>
</td>
</tr>
';
echo '<tr title="'.L('H_Show_news_on_top').'">
<th>'.L('Show_news_on_top').'</th>
<td style="display:flex;justify-content:space-between">
<select name="news_on_top">'.qtTags(array(L('N'),L('Y'),L('By_section')),(int)$_SESSION[QT]['news_on_top']).'</select>
<small style="display:'.($_SESSION[QT]['news_on_top']==2 ? 'inline' : 'none').'">
&nbsp;'.L('Edit').' '.L('options').'
<select onchange="if ( this.value>=0) window.location=`qti_adm_section.php?pan=2&s=` + this.value;">
<option value="-1" selected>'.L('Section').'...</option>
'.$sections.'</select>
</span>
</td>
</tr>
';
echo '<tr title="'.L('H_Show_quick_reply').'">
<th>'.L('Show_quick_reply').'</th>
<td style="display:flex;justify-content:space-between">
<select name="show_quick_reply">'.qtTags(array(L('N'),L('Y'),L('By_section')),(int)$_SESSION[QT]['show_quick_reply']).'</select>
<small style="display:'.($_SESSION[QT]['show_quick_reply']==2 ? 'inline' : 'none').'">
&nbsp;'.L('Edit').' '.L('options').'
<select onchange="if ( this.value>=0) window.location=`qti_adm_section.php?pan=2&s=` + this.value;">
<option value="-1" selected>'.L('Section').'...</option>
'.$sections.'</select>
</span>
</td>
</tr>
</table>
';
echo '<p class="submit"><button type="submit" name="ok" value="ok">'.L('Save').'</button></p>
</form>
';

// HTML END

include APP.'_adm_inc_ft.php';