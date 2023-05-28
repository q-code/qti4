<?php // v4.0 build:20230430

session_start();
/**
 * @var CHtml $oH
 * @var array $L
 * @var CDatabase $oDB
 * @var string $s
 */
require 'bin/init.php';
$oH->selfurl = 'qti_calendars.php';
if ( !SUser::canView('V2') || !SUser::canAccess('show_calendar') ) die('Access denied');

if ( !isset($_SESSION[QT]['cal_shownews']) ) $_SESSION[QT]['cal_shownews']=FALSE;
if ( !isset($_SESSION[QT]['cal_showinsp']) ) $_SESSION[QT]['cal_showinsp']=FALSE;
if ( !isset($_SESSION[QT]['cal_showall']) ) $_SESSION[QT]['cal_showall']=FALSE;
if ( !isset($_SESSION[QT]['cal_showZ']) ) $_SESSION[QT]['cal_showZ']=FALSE;

// ---------
// FUNCTIONS
// ---------

function FirstDayDisplay($intYear,$intMonth,$intWeekstart=1)
{
  // search date of the first 'monday' (or weekstart if not 1)
  // before the beginning of the month (to display gey-out in the calendar)
  if ( $intWeekstart<1 || $intWeekstart>7 ) die ('FirstDayDisplay: Arg #3 must be an int (1-7)');

  $arr = array(1=>'monday','tuesday','wednesday','thursday','friday','saturday','sunday'); // system weekdays reference
  $strWeekstart = $arr[$intWeekstart];
  $d = mktime(0,0,0,$intMonth,1,$intYear); // first day of the month
  if ( strtolower(date('l',$d))==$strWeekstart ) return $d;

  for($i=1;$i<8;++$i)
  {
    $d = strtotime('-1 day',$d);
    if ( strtolower(date('l',$d))==$strWeekstart ) return $d;
  }
  return $d;
}

function ArraySwap($arr,$n=1)
{
  // Move the first value to the end of the array. Action is repeated $n times. Keys are not moved.
  if ( $n>0)
  {
    $arrK = array_keys($arr);
    while($n>0) { array_push($arr,array_shift($arr)); $n--; }
    $arrV = array_values($arr);
    $arr = array();
    for($i=0;$i<count($arrK);++$i) $arr[$arrK[$i]] = $arrV[$i];
  }
  return $arr;
}

// ---------
// INITIALISE
// ---------

$s = -1;
$v = 'firstpostdate';
qtArgs('int:s! v');
if ( !in_array($v,array('firstpostdate','lastpostdate','wisheddate')) ) die('Wrong calendar field');

$intYear = intval(date('Y')); if ( isset($_GET['y']) ) $intYear = intval($_GET['y']);
$intYearP  = $intYear;
$intYearN  = $intYear;
$intMonth = intval(date('n')); if ( isset($_GET['m']) ) $intMonth = intval($_GET['m']);
$intMonthP = $intMonth-1; if ( $intMonthP<1 ) { $intMonthP=12; --$intYearP; }
$intMonthN = $intMonth+1; if ( $intMonthN>12 ) { $intMonthN=1; ++$intYearN; }
$strMonth  = '0'.$intMonth; $strMonth = substr($strMonth,-2,2);
$strMonthP = '0'.$intMonthP; $strMonthP = substr($strMonthP,-2,2);
$strMonthN = '0'.$intMonthN; $strMonthN = substr($strMonthN,-2,2);
$arrWeekCss = array(1=>'monday','tuesday','wednesday','thursday','friday','saturday','sunday'); // system weekdays reference

$dToday  = mktime(0,0,0,date('n'),date('j'),date('Y'));
$dMonth  = mktime(0,0,0,$intMonth,1,$intYear); // First day of the month

if ( $intYear>2100 ) die('Invalid year');
if ( $intYear<1900 ) die('Invalid year');
if ( $intMonth>12 ) die('Invalid month');
if ( $intMonth<1 ) die('Invalid month');

// moderator settings

$strOptions = '';
if ( isset($_GET['Maction']) )
{
  if ( $_GET['Maction']=='this' ) $_SESSION[QT]['cal_showall'] = false;
  if ( $_GET['Maction']=='all' ) $_SESSION[QT]['cal_showall'] = true;
  if ( $_GET['Maction']=='show_Z' ) $_SESSION[QT]['cal_showZ'] = true;
  if ( $_GET['Maction']=='hide_Z' ) $_SESSION[QT]['cal_showZ'] = false;
  if ( $_GET['Maction']=='hide_News' ) $_SESSION[QT]['cal_shownews'] = false;
  if ( $_GET['Maction']=='show_News' ) $_SESSION[QT]['cal_shownews'] = true;
  if ( $_GET['Maction']=='hide_Insp' ) $_SESSION[QT]['cal_showinsp'] = false;
  if ( $_GET['Maction']=='show_Insp' ) $_SESSION[QT]['cal_showinsp'] = true;
}
if ( !$_SESSION[QT]['cal_showZ'] ) $strOptions .= "status<>'Z' AND ";
if ( !$_SESSION[QT]['cal_showall'] ) $strOptions .= "section=$s AND ";
if ( !$_SESSION[QT]['cal_shownews'] ) $strOptions .= "type<>'A' AND ";
if ( !$_SESSION[QT]['cal_showinsp'] ) $strOptions .= "type<>'I' AND ";

$oS = new CSection($s);

$oH->selfuri = 'qti_calendars.php?s='.$s.'&v='.$v.'&y='.$intYear.'&m='.$intMonth;
$oH->selfname = L('section').' '.qtQuote($oS->title,"&'");

$arrS = SMem::get('_Statuses');

// Shift language names and cssWeek to match with weekstart setting, if not 1 (monday)

if ( QT_WEEKSTART>1 )
{
  $L['dateDDD'] = ArraySwap($L['dateDDD'],intval(QT_WEEKSTART)-1);
  $L['dateDD'] = ArraySwap($L['dateDD'],intval(QT_WEEKSTART)-1);
  $L['dateD'] = ArraySwap($L['dateD'],intval(QT_WEEKSTART)-1);
  $arrWeekCss = ArraySwap($arrWeekCss,intval(QT_WEEKSTART)-1);
}

// MAP MODULE

if ( qtModule('gmap') )
{
  /**
   * @var string $strCheck
   * @var string $jMapSections
   */
  $strCheck=$s;
  include 'qtim_gmap_ini.php';
  if ( empty($jMapSections) && file_exists(APP.'m_gmap/config_gmap.php') ) include APP.'m_gmap/config_gmap.php';
}
else
{
  $bMap=false;
}

// --------
// LIST OF TOPICS PER DAY IN THIS FORUM
// --------

$arrEvents = array();
$arrEventsN = array();
$intEvents = 0;
$intEventsN = 0;

$oDB->query( "SELECT id,section,numid,type,status,$v as eventday,y,x FROM TABTOPIC WHERE $strOptions (" . sqlDateCondition((string)($intYear*100+$intMonth),$v,6) . " OR " . sqlDateCondition((string)($intYearN*100+$intMonthN),$v,6) . ")" );

$i=0;
while($row=$oDB->getRow())
{
  $i++;
  if ( strlen($row['eventday'])>=8 )
  {
    $strM = substr($row['eventday'],4,2);
    $idxEvent = substr($row['eventday'],0,8);
    if ( $strM==$strMonth )  { $arrEvents[(int)$idxEvent][]=$row; $intEvents++; }
    if ( $strM==$strMonthN ) { $arrEventsN[(int)$idxEvent]=1; $intEventsN++; }
  }
  if ( $i>8 ) break;
}

// --------
// HTML BEGIN
// --------

$oH->links[] = '<link rel="stylesheet" type="text/css" href="'.QT_SKIN.'qti_calendar.css"/>';

include 'qti_inc_hd.php';

// Moderator actions
if ( SUser::isStaff() )
{
echo '<div class="right">
<div id="optionsbar" title="'.L('My_preferences').'">
<form method="get" action="'.url($oH->selfurl).'" id="modaction">
'.L('Options').'&nbsp;<input type="hidden" name="s" value="'.$s.'"/>
<input type="hidden" name="v" value="'.$v.'"/>
<input type="hidden" name="y" value="'.$intYear.'"/>
<input type="hidden" name="m" value="'.$intMonth.'"/>
<select name="Maction" onchange="document.getElementById(`modaction`).submit();">
<option value="">&nbsp;</option>
';
if ( $_SESSION[QT]['cal_showZ']) { echo '<option value="hide_Z">&#9745; '.L('Item_closed_show').'</option>'; } else { echo '<option value="show_Z">&#9744; '.L('Item_closed_show').'</option>'; }
if ( $_SESSION[QT]['cal_shownews']) { echo '<option value="hide_News">&#9745; '.L('Item_news_show').'</option>';} else { echo '<option value="show_News">&#9744; '.L('Item_news_show').'</option>'; }
if ( $_SESSION[QT]['cal_showinsp']) { echo '<option value="hide_Insp">&#9745; '.L('Item_insp_show').'</option>';} else { echo '<option value="show_Insp">&#9744; '.L('Item_insp_show').'</option>'; }
if ( $_SESSION[QT]['cal_showall']) { echo '<option value="this">&#9745; '.L('Item_show_all').'</option>'; } else { echo '<option value="all">&#9744; '.L('Item_show_all').'</option>'; }
echo '
</select><button id="action_ok" type="submit" name="Mok" value="'.L('Ok').'" style="display:none"/>
</form>
</div>
</div>
';
}

// --------
// MAIN CALENDAR
// --------

$dCurrentDate = mktime(0,0,0,$intMonth,1,$intYear);
$dFirstDay = FirstDayDisplay($intYear,$intMonth,QT_WEEKSTART);

// DISPLAY MAIN CALENDAR

$arrYears = array($intYear-1=>$intYear-1,$intYear,$intYear+1);
if ( !isset($arrYears[intval(date('Y'))]) ) $arrYears[intval(date('Y'))]=intval(date('Y'));

echo '<div id="ct-title" class="flex-sp">';
echo '<h1>'.$L['dateMMM'][date('n',$dCurrentDate)].' '.date('Y',$dCurrentDate).', '.$oH->selfname.'</h1>';
echo '<form method="get" action="'.url($oH->selfurl).'" id="cal_month">';
echo '<input type="hidden" name="s" value="'.$s.'"/> ';
echo '<input type="hidden" name="y" value="'.$intYear.'"/> ';
echo L('Month').' <select name="m" onchange="document.getElementById(`cal_month`).submit();">';
for ($i=1;$i<13;$i++) echo '<option'.($i==date('n') ? ' class="bold"' : '').' value="'.$i.'"'.($i==$intMonth ? ' selected' : '').'>'.$L['dateMMM'][$i].'</option>'.PHP_EOL;
echo '</select>&nbsp;';
if ( date('n',$dCurrentDate)>1 )
  echo '<a class="button" href="'.$oH->selfurl.'?s='.$s.'&m='.(date('n',$dCurrentDate)-1).'">&lt;</a>&thinsp;';
else
  echo '<a class="button disabled">&lt;</a>&thinsp;';
if ( date('n',$dCurrentDate)<12 )
  echo '<a class="button" href="'.$oH->selfurl.'?s='.$s.'&m='.(date('n',$dCurrentDate)+1).'">&gt;</a>';
else
  echo '<a class="button disabled">&gt;</a>';
echo '</form>'.PHP_EOL;
echo '</div>'.PHP_EOL;

echo '<table id="calendar">'.PHP_EOL;
echo '<tr>';
echo '<th class="week">&nbsp;</th>';
for ($i=1;$i<8;++$i)
{
  echo '<th style="width:95px">'.$L['dateDDD'][$i].'</th>';
}
echo '</tr>'.PHP_EOL;

$iShift=0;
$nWeek = (int)date('W',$dFirstDay);

for ($intWeek=0;$intWeek<6;++$intWeek)
{
  echo '<tr>';
  echo '<th class="week">'.$nWeek.'</th>'; $nWeek++; if ( $nWeek>52 ) $nWeek=1;
  for ($intDay=1;$intDay<8;++$intDay)
  {
    $d = strtotime("+$iShift days",$dFirstDay); ++$iShift;
    $intShiftYear = date('Y',$d);
    $intShiftMonth = date('n',$d);
    $intShiftDay = date('j',$d);
    $intShiftItem = (int)date('Ymd',$d);

		echo '<td class="'.$arrWeekCss[$intDay].( date('n',$dMonth)!=date('n',$d) ? ' outdate' : '').'"'.(date('Ymd',$dToday)==date('Ymd',$d) ? ' id="datetoday"' : '').'>';
		echo '<p class="datenumber">'.$intShiftDay.'</p><p class="dateicon">&nbsp;';
		// date info topic
		if ( !isset($arrEvents[$intShiftItem]) ) continue;
    $intTopics = 0;

    foreach($arrEvents[$intShiftItem] as $arrValues)
    {

      ++$intTopics;
      $oT = new CTopic($arrValues);

      if ( $bMap && !empty($oT->y) && !empty($oT->x) ) {

        $strPname = $intShiftDay.' '.$L['dateMMM'][date('n',$dMonth)].' - ';
        if ( $s==$oT->pid ) { $strPname .= ($oS->numfield=='N' ? '' : sprintf($oS->numfield,$oT->numid)); } else { $strPname .= sprintf('%03s',$oT->numid); }
        $strPname .= ' '.$arrS[$oT->status]['name'];
        $strPlink = '<a class="gmap" href="'.url('qti_item.php').'?t='.$oT->id.'">'.L('Item').'</a>';
        $strPinfo = '<span class="small bold">Lat: '.QTdd2dms($oT->y).' <br>Lon: '.QTdd2dms($oT->x).'</span><br><span class="small">DD: '.round($oT->y,8).', '.round($oT->x,8).'</span><br>'.$strPlink;
        $oMapPoint = new CMapPoint($oT->y,$oT->x,$strPname,$strPname.'<br>'.$strPinfo);

        // add extra $oMapPoint properties (if defined in section settings)
        $oSettings = getMapSectionSettings($oT->pid,false,$jMapSections);
        if ( is_object($oSettings) ) foreach(array('icon','shadow','printicon','printshadow') as $prop) if ( property_exists($oSettings,$prop) ) $oMapPoint->$prop = $oSettings->$prop;
        $arrExtData[(int)$row['id']] = $oMapPoint;

      }

      // icon
      $strTicon = $oT->getIcon(QT_SKIN,'',($oS->numfield==='N' ? '%s' : sprintf($oS->numfield,$oT->numid).' - %s'),'t'.$oT->id.'-itemicon');

      $strArgs='';
      if ( $intTopics>=12 )
      {
        echo '...';
        break;
      }
      else
      {
        $strArgs = ' onmouseover="show_gmap(``);"';
        if ( $bMap ) {
          /**
           * @var boolean $bMapGoogle
           */
        if ( $bMapGoogle && !$_SESSION[QT]['m_gmap_hidelist'] && !empty($oT->y) && !empty($oT->x) ) {
        $strArgs = ' onmouseover="show_gmap(`'.$oT->y.','.$oT->x.'`);"';
        }}
        echo '<a class="ajaxmouseover'.($oT->pid==$s ? '' : ' othersection').'" id="t'.$oT->id.'"'.$strArgs.' href="'.url('qti_item.php').'?t='.$oT->id.'">'.$strTicon.'</a> ';
      }

    }

    echo '</p>';
  }
  echo '</tr>'.PHP_EOL;
  if ( $intShiftMonth>$intMonth && $intShiftYear==$intYear ) break;
}

echo '</table>
';
if ( $_SESSION[QT]['cal_showall']) echo '<p class="disabled small">'.L('Items_other_section_are_gayout').'</p>';

// DISPLAY SUBDATA

echo '<div class="table-ui bot">'.PHP_EOL;
echo '<div class="cal_info left">'.PHP_EOL;

// PREPARE NEXT MONTH

$dCurrentDate = mktime(0,0,0,$intMonthN,1,$intYearN);
$dFirstDay = mktime(0,0,0,$intMonthN,1,$intYearN);
if ( date("l",$dFirstDay)!=='Monday' )
{
  $dFirstDay = strtotime('-1 week',$dFirstDay);
  $dFirstMonday = strtotime('next monday',$dFirstDay);
  // correction for php 4.2
  // find last monday
  for ($i=date('j',$dFirstDay);$i<32;$i++)
  {
    $dI = mktime(0,0,0,date('n',$dFirstDay),$i,date('Y',$dFirstDay));
    if ( !$dI )
    {
      if ( date('N',$dI)==1 ) $dFirstMonday = $dI;
    }
  }
  $dFirstDay = $dFirstMonday;
}

// DISPLAY NEXT MONTH

echo '<h2>',$L['dateMMM'][date('n',$dCurrentDate)],'</h2>';
echo '<table id="calendarnext">'.PHP_EOL;
echo '<tr>'.PHP_EOL;
for ($intDay=1;$intDay<8;$intDay++)
{
  echo '<th class="date_next">'.$L['dateD'][$intDay].'</th>'.PHP_EOL;
}
echo '</tr>'.PHP_EOL;

$iShift=0;
$d=0;
for ($intWeek=0;$intWeek<6;$intWeek++)
{
  echo '<tr>'.PHP_EOL;
  for ($intDay=1;$intDay<8;$intDay++)
  {
    $d = strtotime("+$iShift days",$dFirstDay);
    $iShift++;
    $intShiftDay = date('j',$d);
    $intShiftItem = (int)date('Ymd',$d);
    // date number
    if ( date('n',$dCurrentDate)==date('n',$d) )
    {
      echo '<td class="date_next '.$arrWeekCss[$intDay].'"'.(date('z',$dToday)==date('z',$d) ? ' id="todaynext"' : '').'>';
      echo isset($arrEventsN[$intShiftItem]) ? '<a class="date_next" href="'.url('qti_calendars.php').'?s='.$s.'&m='.$intMonthN.'">'.$intShiftDay.'</a>' : $intShiftDay;
    }
    else
    {
      echo '<td class="outdate">';
      echo $intShiftDay;
    }
    echo '</td>'.PHP_EOL;
  }
  echo '</tr>'.PHP_EOL;
  // limit
  if ( $intWeek>3 && date('j',$d)<7) break;
}

echo '</table>'.PHP_EOL;

echo '</div>'.PHP_EOL;
echo '<div class="cal_info center secondary article">'.PHP_EOL;

  // DISPLAY MAP
  if ( $bMap )
  {
    if ( count($arrExtData)>0 )
    {
      $oCanvas = new cCanvas();
      $oCanvas->Header( $arrExtData );
      $oCanvas->Footer( sprintf(L('Gmap.items'),count($arrExtData),L('item',$intEvents)) );
      echo $oCanvas->Render( false, 'gmapCalendar' );
    }
    else
    {
      echo '<p class="gmap nomap">'.L('Gmap.No_coordinates').'</p>'.PHP_EOL;
    }
  }

echo '</div>'.PHP_EOL;
echo '<div class="cal_info">'.PHP_EOL;

  // DISPLAY Preview
  echo '<h2>'.L('Information').'</h2>';
  echo '<div id="previewcontainer"></div>'.PHP_EOL;

echo '</div>'.PHP_EOL;
echo '</div>'.PHP_EOL;

// --------
// HTML END
// --------
if ( $s>=0 && SMemSSE::useSSE() ) {
  $oH->scripts[] = 'var cseMaxRows = '.SSE_MAXROWS.';
var cseShowZ = '.($_SESSION[QT]['cal_showZ'] ? 1 : 0).';
if ( typeof EventSource==="undefined" ) {
  window.setTimeout(function(){location.reload(true);}, 120000); // use refresh (120s) when browser does not support SSE
} else {
  var ns = "'.QT.'";
  var sseServer = "'.SSE_SERVER.'";
  var sseConnect = '.SSE_CONNECT.';
  var sseOrigin = "'.SSE_ORIGIN.'";
  window.setTimeout(function(){
  var script = document.createElement("script");
  script.src = "bin/js/qti_cse_calendar.js";
  document.getElementsByTagName("head")[0].appendChild(script);
  }, 10000);
}';
}

$oH->scripts[] = 'function show_gmap(latlng,id="gmapCalendar"){
  if ( !document.getElementById(id) ) return;
  if ( latlng=="" ) { document.getElementById(id).style.visibility="hidden"; return; }
  document.getElementById(id).style.visibility="visible";
  gmapPan(latlng);
}
if ( document.getElementById("gmapCalendar") ) document.getElementById("gmapCalendar").style.visibility="hidden";
const dir ="'.QT_DIR_PIC.'";
const iso ="'.QT_LANG.'";
const lang = "'.qtDirLang().'";
const elements = document.querySelectorAll(".ajaxmouseover");
elements.forEach( el => el.addEventListener("mouseover", (e) => {
  fetch( `bin/srv_calendaritem.php?term=${el.id}&iso=${iso}&lang=${lang}` )
  .then( response => {
    return response.text()
    .then( text => { if ( text.length>0 ) {
      document.getElementById("previewcontainer").innerHTML = text;
      const node = el.firstChild.cloneNode(false); node.id += "-itemicon-preview";
      const icon = document.getElementById("preview-itemicon"); icon.innerHTML = ""; icon.appendChild(node);
      }
     } )
    } )
  .catch( err => console.log(err) );
  })
);';

// MAP MODULE

if ( $bMap )
{
  /**
   * @var array $gmap_markers
   * @var array $gmap_events
   * @var array $gmap_functions
   */
  $gmap_shadow = false;
  $gmap_symbol = false;
  if ( !empty($_SESSION[QT]['m_gmap_gsymbol']) )
  {
    $arr = explode(' ',$_SESSION[QT]['m_gmap_gsymbol']);
    $gmap_symbol=$arr[0];
    if ( isset($arr[1]) ) $gmap_shadow=$arr[1];
  }

  // check new map center
  $y = floatval(QTgety($_SESSION[QT]['m_gmap_gcenter']));
  $x = floatval(QTgetx($_SESSION[QT]['m_gmap_gcenter']));

  // center on the first item
  foreach($arrExtData as $oMapPoint)
  {
    if ( !empty($oMapPoint->y) && !empty($oMapPoint->x) )
    {
    $y=$oMapPoint->y;
    $x=$oMapPoint->x;
    break;
    }
  }
  // update center
  $_SESSION[QT]['m_gmap_gcenter'] = $y.','.$x;

  $gmap_markers = array();
  $gmap_events = array();
  $gmap_functions = array();
  foreach($arrExtData as $oMapPoint)
  {
    if ( !empty($oMapPoint->y) && !empty($oMapPoint->x) )
    {
      $user_symbol = $gmap_symbol; // required to reset symbol on each user
      $user_shadow = $gmap_shadow;
      if ( !empty($oMapPoint->icon) ) $user_symbol = $oMapPoint->icon;
      $gmap_markers[] = gmapMarker($oMapPoint->y.','.$oMapPoint->x,false,$user_symbol,$oMapPoint->title,$oMapPoint->info,$user_shadow);
    }
  }
  $gmap_functions[] = '
  function zoomToFullExtend()
  {
    if ( markers.length<2 ) return;
    var bounds = new google.maps.LatLngBounds();
    for (var i=markers.length-1; i>=0; i--) bounds.extend(markers[i].getPosition());
    map.fitBounds(bounds);
  }
  function showLocation(address)
  {
    if ( infowindow ) infowindow.close();
    geocoder.geocode( { "address": address}, function(results, status) {
      if ( status == google.maps.GeocoderStatus.OK)
      {
        map.setCenter(results[0].geometry.location);
        if ( marker )
        {
          marker.setPosition(results[0].geometry.location);
        } else {
          marker = new google.maps.Marker({map: map, position: results[0].geometry.location, draggable: true, animation: google.maps.Animation.DROP, title: "Move to define the default map center"});
        }
      } else {
        alert("Geocode was not successful for the following reason: " + status);
      }
    });
  }

  ';
  include 'qtim_gmap_load.php';
}

include 'qti_inc_ft.php';