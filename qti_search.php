<?php // v4.0 build:20240210

session_start();
/**
 * @var CHtml $oH
 * @var array $L
 * @var CDatabase $oDB
 */
require 'bin/init.php';
if ( !SUser::canAccess('search') ) exitPage(11,'user-lock.svg'); //...

// INITIALISE

$oH->selfurl = 'qti_search.php';
$oH->selfname = L('Search');
$oH->exitname = L('Search');

// Check certificates
$certificate = makeFormCertificate('5ca766092492acd750e3061b032bd0d8');
// certificates forwarding
if ( isset($_POST['ok']) && $_POST['ok']===makeFormCertificate('65699386509abf064aec83e5124c1f30') ) $_POST['ok']=$certificate;
// Check certificate
if ( isset($_POST['ok']) && $_POST['ok']!==$certificate ) die('Unable to check certificate');

$q = '';  // query model
$v = '';  // keyword(s), tag(s), userid or date1
$v2 = ''; // username, timeframe or date2
$to = false; // title only
$s = -1;  // [int]
$st = '*';
qtArgs('q v v2 boo:to int:s st');
if ( $st==='' || $st==='-1' ) $st='*';

// ------
// SUBMITTED
// ------
if ( isset($_POST['ok']) && !empty($q) )
{
  $arg=''; // criterias (other than filters)
  switch($q)
  {
    case 'ref':
      if ( empty($v) ) $criteriaError = L('Ref').' '.L('invalid');
      // support direct open when #id is used as ref
      if ( $v[0]==="#" )
      {
        $v = substr($v,1);
        if ( is_numeric($v) ) $oH->redirect('qti_item.php?t='.$v);
      }
      $arg = '&v='.urlencode($v);
      break;
    case 'kw':
      if ( empty($v) ) $criteriaError = L('Keywords').' '.L('invalid');
      $arg = '&v='.urlencode($v).'&to='.$to;
      break;
    case 'qkw':
      $arg = '&v='.urlencode($v);
      break;
    case 'adv':
      if ( $st==='*' && empty($v) && empty($v2) ) $criteriaError = L('Date').' & '.L('Status').' & Tag '.L('invalid');
      if ( $v===';' ) $criteriaError = 'Tag '.L('invalid');
      $arg = '&v2='.$v2.'&st='.$st.'&v='.urlencode($v);
      break;
    case 'user':
    case 'userm':
    case 'actor':
      if ( empty($v2) && !empty($v) ) $v2 = SUser::getUserId($oDB,$v); // return false if wrong name or empty post
      if ( empty($v2) ) $criteriaError = L('Username').' '.L('unknown');
      $arg = '&v='.urlencode($v).'&v2='.$v2;
      break;
    case 'btw':
      $v = qtDateClean($v,8); // Returns YYYYMMDD (no time) while browser should provide YYYY-MM-DD. Returns '' if format not supported. If $v='now', returns today
      $v2 = qtDateClean($v2,8);
      if ( empty($v) || empty($v2) || $v<'20000101' || $v2>'21000101' ) $criteriaError = L('Date').' '.L('invalid');
      if ( $v>$v2 ) $criteriaError = L('Date').' '.L('invalid').' (date1 > date2)';
      $arg = '&v='.$v.'&v2='.$v2;
      break;
    default: die('Unknown criteria '.$q);
  }
  // redirect
  if ( empty($criteriaError) ) {
    $oH->redirect('qti_items.php?q='.$q.'&s='.$s.'&st='.$st.$arg);
    exit;
  } else {
    $_SESSION[QT.'splash'] = 'E|'.L('Search_criteria').' '.L('invalid');
  }
}

// ------
// HTML BEGIN
// ------
include 'qti_inc_hd.php';

// SEARCH SHORTCUTS
include APP.'_search_ui.php';

// SEARCH OPTIONS
echo '<h2>'.L('Search_option').'</h2>'.PHP_EOL;
echo '<section class="search-box options" id="broadcasted-options">'.PHP_EOL;
echo qtSVG('cog', 'id=opt-icon|class=filigrane'.($s<0 ? '' : ' spinning'));
echo '<div>'.L('Section').' <select id="opt-s" name="s" size="1" autocomplete="off">'.sectionsAsOption($s,[],[],L('In_all_sections')).'</select></div>';
echo '</section>'.PHP_EOL;

// SEARCH CRITERIA
echo '<h2>'.L('Search_criteria').'</h2>'.PHP_EOL;

// ERROR MESSAGE
if ( !empty($criteriaError) ) echo '<p class="error">'.$criteriaError.'</p>';

// SEARCH BY KEY
echo '<form method="post" action="'.url($oH->selfurl).'" autocomplete="off">
<section class="search-box criteria">
'.qtSVG('search', 'class=filigrane').'
<div>'.L('Keywords').' <div id="ac-wrapper-kw"><input required type="text" id="kw" name="v" size="40" maxlength="64" value="'.($q=='kw' ? qtAttr($v,0,'&quot;') : '').'" data-multi="1"/></div>*</div>
<div><span class="cblabel"><input type="checkbox" id="to" name="to"'.($to ? ' checked' : '').' value="1"/> <label for="to">'.L('In_title_only').'</label></span></div>
<div style="flex-grow:1;text-align:right">
<input type="hidden" name="q" value="kw"/>
<input type="hidden" id="kw-s" name="s" value="'.$s.'"/>
<button type="submit" name="ok" value="'.$certificate.'">'.L('Search').'</button>
</div>
</section>
</form>
';

// SEARCH BY REF
$refExists=false;
foreach($_Sections as $mSec)
{
  if ( $mSec['type']=='1' && !SUser::isStaff() ) continue;
  if ( $mSec['numfield']!=='N' ) { $refExists=true; break; }
}
if ( $refExists )
{
echo '<form method="post" action="'.url($oH->selfurl).'" autocomplete="off">
<div class="search-box criteria">
'.qtSVG('search', 'class=filigrane').'
<div>'.L('Ref').' <div id="ac-wrapper-ref"><input required type="text" id="ref" name="v" size="5" minlength="1" maxlength="10" value="'.($q=='ref' ? qtAttr($v,0,'&quot;') : '').'"/>&nbsp;'.L('H_Reference').'</div></div>
<div style="flex-grow:1;text-align:right">
<input type="hidden" name="q" value="ref"/>
<input type="hidden" id="ref-s" name="s" value="'.$s.'"/>
<button type="submit" name="ok" value="'.$certificate.'">'.L('Search').'</button>
</div>
</div>
</form>
';
}

// SEARCH BY STATUS, DATE & TAGS
$arrS = SMem::get('_Statuses');
echo '<form method="post" action="'.url($oH->selfurl).'" autocomplete="off">
<div class="search-box criteria">
'.qtSVG('search', 'class=filigrane').'
<div>'.L('Status').'&nbsp;<select id="st" name="st" size="1">
<option value="*"'.($st==='*' ? ' selected' : '').'>'.L('Any_status').'</option>
'.qtTags($arrS,$st).'</select> '.L('Date').'&nbsp;<select id="ti" name="v2" size="1">
<option value="*"'.($v2==='*' ? ' selected' : '').'>'.L('Any_time').'</option>
<option value="w"'.($v2==='w' ? ' selected' : '').'>&nbsp; '.L('This_week').'</option>
<option value="m"'.($v2==='m' ? ' selected' : '').'>&nbsp; '.L('This_month').'</option>
<option value="y"'.($v2==='y' ? ' selected' : '').'>&nbsp; '.L('This_year').'</option>
'.qtTags($L['dateMMM'],(int)$v2).'
</select><input type="hidden" id="y" name="y" value="'.date('Y').'"/>
';
if ( $_SESSION[QT]['tags']!='0' ) echo L('With_tag').'&nbsp;<div id="ac-wrapper-tag-edit"><input type="text" id="tag-edit" name="v" size="30" value="'.($q==='adv' ? qtAttr($v) : '').'" data-multi="1"/></div>*
</div>
<div style="flex-grow:1;text-align:right">
<input type="hidden" name="q" value="adv"/>
<input type="hidden" id="tag-s" name="s" value="'.$s.'"/>
<button type="submit" name="ok" value="'.$certificate.'">'.L('Search').'</button>
</div>
</div>
</form>
';

// SEARCH NAME
echo '<form method="post" action="'.url($oH->selfurl).'" autocomplete="off">
<div class="search-box criteria">
'.qtSVG('search', 'class=filigrane').'
<div>
<select name="q" size="1">
'.qtTags( ['user'=>L('Item').' '.L('author'),'userm'=>L('Item').'/'.L('reply').' '.L('author'),'actor'=>L('Item').' '.L('actor')], $q ).'
</select> <div id="ac-wrapper-user"><input type="hidden" id="userid" type="text" name="v2" value="'.$v2.'"/><input required id="user" type="text" name="v" value="'.(empty($v) || substr($q,0,4)!=='user' ? '' : qtAttr($v,0,'&quot;')).'" size="32" maxlenght="64"/></div></div>
<div style="flex-grow:1;text-align:right">
<input type="hidden" id="user-s" name="s" value="'.$s.'"/>
<button type="submit" name="ok" value="'.$certificate.'">'.L('Search').'</button>
</div>
</div>
</form>
';

// SEARCH BETWEEN DATES
// when btw is used, v and v2 are reset to no be visible in other forms
$date1 = ''; if ( $q=='btw' && strlen($v)==8 ) { $date1 = substr($v,0,4).'-'.substr($v,4,2).'-'.substr($v,6,2); $v=''; }
$date2 = ''; if ( $q=='btw' && strlen($v2)==8 ) { $date2 = substr($v2,0,4).'-'.substr($v2,4,2).'-'.substr($v2,6,2);  $v2=''; }
echo '<form method="post" action="'.url($oH->selfurl).'" autocomplete="off">
<div class="search-box criteria">
'.qtSVG('search', 'class=filigrane').'
<div>'.L('Between_date').' <input required type="date" id="date1" name="v" size="20" value="'.$date1.'" min="2000-01-01"/>
'.L('and').' <input required type="date" id="date2" name="v2" size="20" value="'.$date2.'" max="2100-01-01"/> <a href="javascript:void(0)" onclick="setToday(); return false;""><small>'.L('dateSQL.today').'</small></a></div>
<div style="flex-grow:1;text-align:right">
<input type="hidden" name="q" value="btw"/>
<input type="hidden" id="btw-s" name="s" value="'.$s.'"/>
<button type="submit" name="ok" value="'.$certificate.'">'.L('Search').'</button>
</div>
</div>
</form>
';

echo '* <small>'.sprintf(L('Multiple_input'),QSEPARATOR).'</small>';

// HTML END
$oH->scripts['ac'] = '<script type="text/javascript" src="bin/js/qt_ac.js"></script>
<script type="text/javascript" src="bin/js/qti_config_ac.js"></script>';
include 'qti_inc_ft.php';