<?php
/**
 * @var string $error
 * @var string $warning
 * @var CHtml $oH
 * @var array $L
 * @var CDatabase $oDB
 */
$oH->links['icon'] = '<link rel="shortcut icon" href="bin/css/qti_icon.ico"/>';
$oH->links['cssBase'] = '<link rel="stylesheet" type="text/css" href="bin/css/qt_core.css"/>'; // attention qt_core
unset($oH->links['cssContrast']);
$oH->links['css'] = '<link rel="stylesheet" type="text/css" href="bin/css/admin.css"/>';
$oH->scripts[] = 'var e0 = '.(isset($L['E_editing']) ? '"'.L('E_editing').'"' : 'Data not yet saved. Quit without saving?').';';
$oH->links['cssCustom'] = null;

$oH->head();
$oH->body();

echo '<div class="pg-admin">'.PHP_EOL;

if ( file_exists(translate($oH->selfurl.'.txt'))  )
{
  echo '<div class="hlp-box">';
  echo '<div class="hlp-head">'.L('Help').'</div>';
  echo '<div class="hlp-body">';
  include translate($oH->selfurl.'.txt');
  echo '</div></div>';
}

echo '
<div id="banner"><img id="logo" src="bin/css/'.APP.'_logo.gif" style="border-width:0" alt="'.APPNAME.'" title="'.APPNAME.'"/></div>
';

if ( !defined('HIDE_MENU_LANG') || !HIDE_MENU_LANG )
{
  $langMenu = new CMenu();
  $langMenu->add('text='.qtSVG('caret-square-left').'|id=lang-exit|href='.APP.'_index.php|title='.L('Exit').'|onclick=return qtFormSafe.exit(e0);');
  // lang
  if ( $_SESSION[QT]['userlang'] ) {
    if ( is_array(LANGUAGES) && count(LANGUAGES)>1 ) {
      foreach (LANGUAGES as $iso=>$lang) {
        $lang = explode(' ',$lang);
        $lang = empty($lang[1]) ? strtoupper($iso) : $lang[1]; // uppercase iso code if no description
        $langMenu->add('lang-'.$iso, strtoupper($iso).'|href='.$oH->selfurl.'?'.qtURI('lang').'&lang='.$iso.'||title='.$lang.'|onclick=return qtFormSafe.exit(e0);');
      }
    } else {
      $langMenu->add('!missing file:config/config_lang.php');
    }
  }
  $langMenu->separator = '&nbsp;';
  echo '<div id="menulang">'.$langMenu->build('lang-'.QT_LANG, 'tag=span|onclick=return false').'</div>';
}

echo '<div id="pg-layout">
';

if ( !defined('HIDE_MENU_TOC') || !HIDE_MENU_TOC )
{
  $navMenu = new CMenu(null,'');
  echo '<div id="toc">'.PHP_EOL;
  $navMenu->add(L('Info').          '|tag=p|class=group');
  $navMenu->add(L('Board_status').  '|href=qti_adm_index.php|class=item|onclick=return qtFormSafe.exit(e0);');
  $navMenu->add(L('Board_general'). '|href=qti_adm_site.php|class=item|onclick=return qtFormSafe.exit(e0);');
  echo '<div class="group">'.$navMenu->build($oH->selfurl).'</div>';
  // group settings
  $navMenu->menu = [];
  $navMenu->add(L('Settings').      '|tag=p|class=group');
  $navMenu->add(L('Board_region').  '|href=qti_adm_region.php|class=item|onclick=return qtFormSafe.exit(e0);|activewith=qti_adm_time.php');
  $navMenu->add(L('Board_layout').  '|href=qti_adm_skin.php|class=item|onclick=return qtFormSafe.exit(e0);');
  $navMenu->add(L('Board_security').'|href=qti_adm_secu.php|class=item|onclick=return qtFormSafe.exit(e0);');
  $navMenu->add('SSE|href=qti_adm_sse.php|class=item|onclick=return qtFormSafe.exit(e0);');
  echo '<div class="group">'.$navMenu->build($oH->selfurl).'</div>';
  // group Content
  $navMenu->menu = [];
  $navMenu->add(L('Board_content'). '|tag=p|class=group');
  $navMenu->add(L('Section+').      '|href=qti_adm_sections.php|class=item|onclick=return qtFormSafe.exit(e0);|activewith=qti_adm_section.php qti_adm_domain.php');
  $navMenu->add(L('Item+').         '|href=qti_adm_items.php|class=item|onclick=return qtFormSafe.exit(e0);');
  $navMenu->add(L('Statuses').      '|href=qti_adm_statuses.php|class=item|onclick=return qtFormSafe.exit(e0);|activewith=qti_adm_status.php');
  $navMenu->add(L('Tags').          '|href=qti_adm_tags.php|class=item|onclick=return qtFormSafe.exit(e0);');
  $navMenu->add(L('Users').         '|href=qti_adm_users.php|class=item|onclick=return qtFormSafe.exit(e0);|activewith=qti_adm_users_exp.php qti_adm_users_imp.php');
  echo '<div class="group">'.$navMenu->build($oH->selfurl).'</div>';
  // group modules
  $navMenu->menu = [];
  $navMenu->add(L('Board_modules'). '|tag=p|class=group');
  if ( !isset($_SESSION[QT]['mModules']) ) $_SESSION[QT]['mModules'] = $oDB->getSettings('param LIKE "module%"'); // store list of modules in memory if not yet done
  foreach($_SESSION[QT]['mModules'] as $k=>$module)
  {
    $k = str_replace('module_','',$k);
    $navMenu->add($module.'|href=qtim_'.$k.'_adm.php|class=item|onclick=return qtFormSafe.exit(e0);');
  }
  echo '<div class="group">'.$navMenu->build($oH->selfurl).'<p class="item"><a href="qti_adm_module.php?a=add" onclick="return qtFormSafe.exit(e0);">['.L('Add').']</a> &middot; <a href="qti_adm_module.php?a=rem" onclick="return qtFormSafe.exit(e0);">['.L('Remove').']</a></p></div>';
  echo '<a style="display:block;margin:8px 0" class="button center" href="'.APP.'_index.php" onclick="return qtFormSafe.exit(e0);">'.L('Exit').'</a>';
  echo qtSVG('user-A', 'class=filigrane');
  echo '</div>'.PHP_EOL;
}

CHtml::getPage();

// Title (and error)
echo '<h1 class="title"'.(isset($oH->selfparent) ? ' data-parent="'.$oH->selfparent.'"' : '').'>'.$oH->selfname.'</h1>';
if ( !empty($oH->selfversion) ) echo '<p class="pageversion">'.$oH->selfversion.'</p>';
if ( !empty($oH->error) ) echo '<p class="center error">'.$oH->error.'</p>';
if ( !empty($oH->warning) ) echo '<p class="center warning">'.$oH->warning.'</p>';