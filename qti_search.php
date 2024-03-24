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

$s = -1; // [int]
$q = ''; // query model
$fv = ''; // keyword(s), tag(s), userid or date1
$fw = ''; // username, timeframe or date2
$fst = ''; // status
$to = false; // title only
qtArgs('int:s q fv fw fst boo:to');

// ------
// SUBMITTED
// ------
if ( isset($_POST['ok']) && !empty($q) ) try {

  $arg['s'] = $s<0 ? '' : $s;
  $arg['q'] = $q;
  switch($q) {
    case 'ref':
      if ( empty($fv) ) throw new Exception( L('Ref').' '.L('invalid') );
      // support direct open when #id is used as ref
      if ( $fv[0]==="#" ) {
        $fv = substr($fv,1);
        if ( is_numeric($fv) ) $oH->redirect('qti_item.php?t='.$fv);
      }
      $arg['fv'] = urlencode($fv);
      break;
    case 'kw':
      if ( empty($fv) ) throw new Exception( L('Keywords').' '.L('invalid') );
      $arg['fv'] = urlencode($fv);
      $arg['to'] = $to;
      break;
    case 'qkw':
      $arg['fv'] = urlencode($fv);
      break;
    case 'adv':
      if ( $fst==='' && empty($fv) && empty($fw) ) throw new Exception( L('Date').' & '.L('Status').' & Tag '.L('invalid') );
      if ( $fv===';' ) throw new Exception( 'Tag '.L('invalid') );
      $arg['fv'] = urlencode($fv);
      $arg['fw'] = $fw;
      $arg['fst'] = $fst;
      break;
    case 'user':
    case 'userm':
    case 'actor':
      if ( empty($fw) && !empty($fv) ) $fw = SUser::getUserId($oDB,$fv); // return false if wrong name or empty post
      if ( empty($fw) ) throw new Exception( L('Username').' '.L('unknown') );
      $arg['fv'] = urlencode($fv);
      $arg['fw'] = $fw;
      break;
    case 'btw':
      $fv = qtDateClean($fv,8); // Returns YYYYMMDD (no time) while browser should provide YYYY-MM-DD. Returns '' if format not supported. If $fv='now', returns today
      $fw = qtDateClean($fw,8);
      if ( empty($fv) || empty($fw) || $fv<'20000101' || $fw>'21000101' ) throw new Exception( L('Date').' '.L('invalid') );
      if ( $fv>$fw ) throw new Exception( L('Date').' '.L('invalid').' (date1 > date2)' );
      $arg['fv'] = urlencode($fv);
      $arg['fw'] = $fw;
      break;
    default: die( 'Unknown criteria '.$q );
  }
  // redirect
  $oH->redirect( APP.'_items.php?'.qtImplode($arg) );

} catch (Exception $e) {

  $oH->error = $e->getMessage();
  $_SESSION[QT.'splash'] = 'E|'.$oH->error;

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
<div>'.L('Keywords').' <div id="ac-wrapper-kw"><input required type="text" id="kw" name="fv" size="40" maxlength="64" value="'.($q=='kw' ? qtAttr($fv,0,'&quot;') : '').'" data-multi="1"/></div>*</div>
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
<div>'.L('Ref').' <div id="ac-wrapper-ref"><input required type="text" id="ref" name="fv" size="5" minlength="1" maxlength="10" value="'.($q=='ref' ? qtAttr($fv,0,'&quot;') : '').'"/>&nbsp;'.L('H_Reference').'</div></div>
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
<div>'.L('Status').'&nbsp;<select name="fst" size="1">
<option value=""'.($fst==='' ? ' selected' : '').'>'.L('Any_status').'</option>
'.qtTags($arrS,$fst).'</select> '.L('Date').'&nbsp;<select id="ti" name="fw" size="1">
<option value=""'.($fw==='' ? ' selected' : '').'>'.L('Any_time').'</option>
<option value="w"'.($fw==='w' ? ' selected' : '').'>&nbsp; '.L('This_week').'</option>
<option value="m"'.($fw==='m' ? ' selected' : '').'>&nbsp; '.L('This_month').'</option>
<option value="y"'.($fw==='y' ? ' selected' : '').'>&nbsp; '.L('This_year').'</option>
'.qtTags($L['dateMMM'],(int)$fw).'
</select><input type="hidden" id="y" name="y" value="'.date('Y').'"/>
';
if ( $_SESSION[QT]['tags']!='0' ) echo L('With_tag').'&nbsp;<div id="ac-wrapper-tag-edit"><input type="text" id="tag-edit" name="fv" size="30" value="'.($q==='adv' ? qtAttr($fv) : '').'" data-multi="1"/></div>*
</div>
<div style="flex-grow:1;text-align:right">
<input type="hidden" name="q" value="adv"/>
<input type="hidden" id="adv-s" name="s" value="'.$s.'"/>
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
</select> <div id="ac-wrapper-user"><input type="hidden" id="userid" type="text" name="fw" value="'.$fw.'"/><input required id="user" type="text" name="fv" value="'.(empty($fv) || substr($q,0,4)!=='user' ? '' : qtAttr($fv,0,'&quot;')).'" size="32" maxlenght="64"/></div></div>
<div style="flex-grow:1;text-align:right">
<input type="hidden" id="user-s" name="s" value="'.$s.'"/>
<button type="submit" name="ok" value="'.$certificate.'">'.L('Search').'</button>
</div>
</div>
</form>
';

// SEARCH BETWEEN DATES
// when btw is used, v and w are reset to no be visible in other forms
$date1 = ''; if ( $q=='btw' && strlen($fv)==8 ) { $date1 = substr($fv,0,4).'-'.substr($fv,4,2).'-'.substr($fv,6,2); $fv = ''; }
$date2 = ''; if ( $q=='btw' && strlen($fw)==8 ) { $date2 = substr($fw,0,4).'-'.substr($fw,4,2).'-'.substr($fw,6,2);  $fw = ''; }
echo '<form method="post" action="'.url($oH->selfurl).'" autocomplete="off">
<div class="search-box criteria">
'.qtSVG('search', 'class=filigrane').'
<div>'.L('Between_date').' <input required type="date" id="date1" name="fv" size="20" value="'.$date1.'" min="2000-01-01"/>
'.L('and').' <input required type="date" id="date2" name="fw" size="20" value="'.$date2.'" max="2100-01-01"/> <a href="javascript:void(0)" onclick="setToday(); return false;""><small>'.L('dateSQL.today').'</small></a></div>
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