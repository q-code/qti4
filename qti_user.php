<?php

session_start();
/**
 * @var CHtml $oH
 * @var CDatabase $oDB
 * @var array $L
 */
require 'bin/init.php';

$oH->selfurl = 'qti_user.php';
if ( SUser::role()==='V' ) exitPage(11,'user-lock.svg'); //...

$id = -1;
qtArgs('int:id!');
if ( $id<0 ) die('Wrong id');

if ( isset($_GET['edit']) ) $_SESSION[QT]['editing']=($_GET['edit']=='1' ? true : false);
if ( isset($_POST['edit']) ) $_SESSION[QT]['editing']=($_POST['edit']=='1' ? true : false);

// ------
// FUNCTION
// ------
function show_ban($strRole='V',$intBan=0,$name='')
{
  if ( $intBan<1 ) return '';
  if ( $strRole=='A' || $strRole=='M' )
  {
    if ( $intBan>1 ) $intBan=($intBan-1)*10;
    return '<p class="small error">'.$name.' '.strtolower(L('Is_banned').' '.L('Day',$intBan).' '.L('Since').' '.L('Last_message')).'</p>';
  }
}

// ------
// INITIALISE
// ------
include 'bin/class/class.phpmailer.php';
include translate('lg_reg.php');

$canEdit = false;
if ( SUser::id()==$id ) $canEdit=true;
if ( SUser::isStaff() ) $canEdit=true;
if ( $id==0 ) $canEdit=false;
if ( !isset($_SESSION[QT]['editing']) || !$canEdit) $_SESSION[QT]['editing']=false;

$oH->selfname = L('Profile');

// MAP MODULE

$bMap=false;
if ( qtModule('gmap') )
{
  include translate(APP.'m_gmap.php');
  include 'qtim_gmap_lib.php';
  if ( gmapCan('U') ) $bMap=true;
  if ( $bMap )
  {
  $oH->links[]='<link rel="stylesheet" type="text/css" href="qtim_gmap.css"/>';
  if ( !isset($_SESSION[QT]['m_gmap_symbols']) ) $_SESSION[QT]['m_gmap_symbols']='0';
  $arrSymbolByRole = ( empty($_SESSION[QT]['m_gmap_symbols']) ? array() : qtExplode($_SESSION[QT]['m_gmap_symbols']) );
  }
}

// ------
// SUBMITTED
// ------
if ( isset($_POST['ok']) ) try {

  // check form
  $strLoca = qtDb(trim($_POST['location']));
  $strMail = trim($_POST['mail']); // html support multiple emails with ','
  if ( empty($strMail) ) throw new Exception( L('Email').' '.$strMail.' '.L('invalid') );
  if ( substr_count($strMail,',')>4 ) throw new Exception( '5 '.L('emails').' '.L('maximum') );
  if ( empty($_POST['birth_y']) || empty($_POST['birth_d']) || empty($_POST['birth_d']) ) {
    $strBirth = '0';
  } else {
    $i = intval($_POST['birth_y'])*10000+intval($_POST['birth_m'])*100+intval($_POST['birth_d']);
    if ( !qtIsValiddate($i,true,false,false) ) throw new Exception( L('Birthday').' ('.$_POST['birth_y'].'-'.$_POST['birth_m'].'-'.$_POST['birth_d'].') '.L('invalid') );
    $strBirth = $i;
  }
  if ( isset($_POST['child']) ) { $strChild = substr($_POST['child'],0,1); } else { $strChild = '0'; }
  if ( $id==1 && $strChild!='0' ) throw new Exception( 'user id[1] is admin and child status cannot be changed...' );
  if ( $id==0 && $strChild!='0' ) throw new Exception( 'user id[0] is visitor and child status cannot be changed...' );
  if ( isset($_POST['parentmail']) ) {
    $strParentmail = trim($_POST['parentmail']);
    if ( !empty($strParentmail) ) throw new Exception( L('Parent_mail').' '.L('invalid') );
 }

  $strWww = qtAttr(trim($_POST['www']));
  if ( !empty($strWww) && substr($strWww,0,4)!=='http' ) throw new Exception( L('Website').' '.L('invalid') );
  if ( empty($strWww) || $strWww=='http://' || $strWww=='https://' ) $strWww='';

  // Save
  $oDB->exec( "UPDATE TABUSER SET birthday=?,location=?,mail=?,www=?,privacy=?,children=?,parentmail=? WHERE id=".$id,
    [$strBirth,$strLoca,$strMail,$strWww,$_POST['privacy'],$strChild,$strParentmail]
    );
  if ( isset($_POST['coord']) )
  {
    $coord = strip_tags(trim($_POST['coord']));
    $coord = str_replace(' ','',$coord); // remove spaces between coordinates y,x
    SUser::setCoord($oDB,$id,$coord); // coord can be empty (coordinates are removed)
  }

  // exit (if no error)
  $oH->exiturl = 'qti_user.php?id='.$id;
  $oH->exitname = $L['Profile'];
  $_SESSION[QT.'splash'] = L('S_update');
  $oH->redirect('exit');

} catch (Exception $e) {

  $error = $e->getMessage();
  $_SESSION[QT.'splash'] = 'E|'.$error;
}

// ------
// STATS AND USER
// ------
// COUNT TOPICS
$items = $oDB->count( TABTOPIC.' WHERE firstpostuser='.$id );
// COUNT MESSAGES
$countmessages = $oDB->count( TABPOST.' WHERE userid='.$id );
// QUERY USER
$oDB->query( 'SELECT * FROM TABUSER WHERE id='.$id);
$row = $oDB->getRow();
$row['privacy']= (int)$row['privacy']; // int
// check privacy
if ( !SUser::canSeePrivate($row['privacy'],$id) ) { $row['y']=null; $row['x']=null; }
// staff cannot edit other staff nor admin
if ( $row['role']=='M' && SUser::role()==='M' && !QT_STAFFEDITSTAFF && SUser::id()!=$id ) { $canEdit=false; $_SESSION[QT]['editing']=false; }
if ( $row['role']=='A' && SUser::role()==='M' && !QT_STAFFEDITADMIN ) { $canEdit=false; $_SESSION[QT]['editing']=false; }

// map settings
if ( $bMap && !gmapEmpty($row['x']) && !gmapEmpty($row['y']) )
{
  $y = (float)$row['y']; $x = (float)$row['x'];
  $strPname = $row['name'];
  $oMapPoint = new CMapPoint($y,$x,$strPname);
  if ( !empty($arrSymbolByRole[$row['role']]) ) $oMapPoint->icon = $arrSymbolByRole[$row['role']];
  $arrExtData[$id] = $oMapPoint;
}

// DEFAULT
$strMail = '';  if ( !empty($row['mail']) && SUser::canSeePrivate($row['privacy'],$id) ) $strMail = renderEmail($row['mail'],'txt'.(QT_JAVA_MAIL ? 'java' : ''));
$strLocation = ''; if ( !empty($row['location']) && SUser::canSeePrivate($row['privacy'],$id) ) $strLocation = $row['location'];
$strCoord = ''; // coordinates with visual units
$strYX = ''; // coordinates in map unit [y,x]
if ( $bMap && !empty($row['x']) && !empty($row['y']) && SUser::canSeePrivate($row['privacy'],$id) )
{
  $strYX = round((float)$row['y'],8).','.round((float)$row['x'],8);
  $strCoord = QTdd2dms((float)$row['y']).', '.QTdd2dms((float)$row['x']).' '.$L['Coord_latlon'].' <span class="small disabled">DD '.$strYX.'</span>';
}
$strPriv = renderUserPrivSymbol($row);

// ------
// HTML BEGIN
// ------
include 'qti_inc_hd.php';

if ( $id<0 )  die('Wrong id in qti_user.php');

// USER name and UI
echo '<div id="user-name"><h1>'.$row['name'].' '.SUser::getStamp($row['role']).'</h1></div>
<div id="user-ui" class="right">';
include 'qti_user_ui.php';
echo '</div>
';

// USER PROFILE
echo '<div id="user-menu">
';
echo SUser::getPicture($id, 'id=userimg').PHP_EOL;

if ( $canEdit )
{
  if ( !empty(qtExplodeGet($_SESSION[QT]['formatpicture'],'mime')) )
  {
  echo '<p><a href="'.url('qti_user_img.php').'?id='.$id.'">'.L('Change_picture').'</a></p>';
  }
  echo '<p><a href="'.url('qti_register.php').'?a=sign&id='.$id.'">'.L('Change_signature').'</a></p>';
  echo '<p><a href="'.url('qti_register.php').'?a=pwd&id='.$id.'">'.L('Change_password').'</a></p>';
  echo '<p><a href="'.url('qti_register.php').'?a=qa&id='.$id.'">'.L('Secret_question').'</a></p>';
  if ( SUser::role()==='A' || (SUser::id()==$id && QT_CHANGE_USERNAME) ) {
  if ( $id>0 ) echo '<p><a href="'.url('qti_register.php').'?a=name&id='.$id.'">'.L('Change_name').'</p></a>';
  }
  if ( $id>1 && (SUser::id()===$id || SUser::role()==='A') )
  {
  echo '<p><a href="'.url('qti_register.php').'?a=out&id='.$id.'">'.L('Unregister').'</a></p>';
  }
}
if ( SUser::canAccess('show_calendar') )
{
echo '<p><a href="'.url('qti_calendar.php').(empty($row['birthday']) ? '' : '?m='.substr($row['birthday'],4,2)).'">'.L('Birthdays_calendar').'</a></p>';
}
if ( !empty($row['closed']) ) echo '<hr/>'.show_ban(SUser::role(),$row['closed'],$row['name']);

echo '</div>
<div id="user-main">
';

// -- EDIT PROFILE --
if ( $_SESSION[QT]['editing'] ) {
// -- EDIT PROFILE --

echo '<form method="post" action="'.url('qti_user.php').'?id='.$id.'">
<table class="t-profile">
<tr><th>'.L('Username').'</th><td clss="c-name">'.$row['name'].'</td></tr>
<tr><th>'.L('Role').'</th><td>'.L('Role_'.$row['role']).'</td></tr>
<tr><th>'.L('Location').'</th><td><input type="text" name="location" size="35" maxlength="24" value="'.$row['location'].'"/></td></tr>
<tr><th>'.L('Email').'</th><td><input type="email" name="mail" size="35" maxlength="64" value="'.$row['mail'].'" multiple/></td></tr>
<tr><th>'.L('Website').'</th><td><input type="text" name="www" pattern="^(http://|https://).*" size="35" maxlength="64" value="'.( !empty($row['www']) ? $row['www'] : 'http://' ).'" title="'.$L['H_Website'].'"/></td></tr>
<tr><th>'.L('Birthday').'</th>
';
$strBrith_y = '';
$strBrith_m = '';
$strBrith_d = '';
if ( !empty($row['birthday']) )
{
  $strBrith_y = intval(substr(strval($row['birthday']),0,4));
  $strBrith_m = intval(substr(strval($row['birthday']),4,2));
  $strBrith_d = intval(substr(strval($row['birthday']),6,2));
}
echo '<td><select name="birth_d" size="1">'.PHP_EOL;
echo qtTags([0=>'',1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31],$strBrith_d);
echo '</select>'.PHP_EOL;
echo '<select name="birth_m" size="1">'.PHP_EOL;
echo '<option value="0"></option>'.qtTags($L['dateMMM'],$strBrith_m);
echo '</select>'.PHP_EOL;
echo '<input type="text" id="birth_y" name="birth_y" pattern="(19|20)[0-9]{2}" size="4" maxlength="4" value="'.$strBrith_y.'"/>';
echo '</td></tr>'.PHP_EOL;
echo '<tr>
<th>'.L('Privacy').'</th>
<td>'.L('Email').'/'.L('Location').($bMap ? '/'.L('Gmap.position') : '').' <select size="1" name="privacy">
<option value="2"'.($row['privacy']===2 ? ' selected' : '').'>'.L('Privacy_visible_2').'</option>
<option value="1"'.($row['privacy']===1 ? ' selected' : '').'>'.L('Privacy_visible_1').'</option>
<option value="0"'.($row['privacy']===0 ? ' selected' : '').'>'.L('Privacy_visible_0').'</option>
</select></td>
</tr>
';

if ( $bMap )
{
  $strPosition  = '<p class="small commands" style="margin:2px 0 4px 2px;text-align:right">'.L('Gmap.cancreate');
  if ( !empty($row['x']) && !empty($row['y']) )
  {
    $_SESSION[QT]['m_gmap_gcenter'] = $strYX;
    $strPosition  = '<p class="small commands" style="margin:2px 0 4px 2px;text-align:right">'.L('Gmap.canmove');
  }
  $strPosition .= ' | <a class="small" href="javascript:void(0)" onclick="createMarker(); return false;" title="'.L('Gmap.H_pntadd').'">'.L('Gmap.pntadd').'</a>';
  $strPosition .= ' | <a class="small" href="javascript:void(0)" onclick="deleteMarker(); return false;">'.L('Gmap.pntdelete').'</a>';
  $strPosition .= '</p>'.PHP_EOL;
  $strPosition .= '<div id="map_canvas"></div>'.PHP_EOL;
  $strPosition .= '<p class="small commands" style="margin:4px 0 2px 2px;text-align:right">'.L('Gmap.addrlatlng').' ';
  $strPosition .= '<input type="text" size="24" id="find" name="find" class="small" value="'.$_SESSION[QT]['m_gmap_gfind'].'" title="'.L('Gmap.H_addrlatlng').'" onkeypress="enterkeyPressed=qtKeyEnter(event); if ( enterkeyPressed) showLocation(this.value,null);"/>';
  $strPosition .= '<span id="btn-geocode" onclick="showLocation(document.getElementById(`find`).value,null);" title="'.L('Search').'">'.qtSVG('search').'</span></p>';

  echo '<tr>'.PHP_EOL;
  echo '<th>'.$L['Coord'].'</th>';
  echo '<td><input type="text" id="yx" name="coord" pattern="^(-?\d+(\.\d+)?),\s*(-?\d+(\.\d+)?)$" size="32" value="'.$strYX.'" title="y,x in decimal degree (without trailing spaces)"/> <small>'.$L['Coord_latlon'].'</span></td>';
  echo '</tr>'.PHP_EOL;
}

echo '<tr>
<th><input type="hidden" name="id" value="'.$id.'"/><input type="hidden" name="name" value="'.$row['name'].'"/></th>
<td><button type="submit" name="ok" value="ok">'.L('Save').'</button>'.( !empty($error) ? ' <span class="error">'.$error.'</span>' : '' ).'</td>
</tr>
';

if ( $bMap )
{
echo '<tr>
<td colspan="2" id="gmapcontainer">'.$strPosition.'</td>
</tr>
';
}

echo '
</table>
</form>
';

// ------
} else {
// ------
$strParticip = '';
if ( $items>0 )
{
$strParticip .= '<a href="'.url('qti_items.php').'?q=user&v2='.$id.'&v='.urlencode($row['name']).'">'.L('Item',$items).'</a>, ';
}
if ( $countmessages>0 )
{
  $strParticip .= '<a href="'.url('qti_items.php').'?q=userm&v2='.$id.'&v='.urlencode($row['name']).'">'.L('Message',$countmessages).'</a>';
  $strParticip .= ', '.strtolower($L['Last_message']).' '.qtDate($row['lastdate'],'$','$',true);
  $oDB->query( 'SELECT p.id,p.topic,p.section FROM TABPOST p WHERE p.userid='.$id.' ORDER BY p.issuedate DESC' );
  $row2 = $oDB->getRow();
  $strParticip .= ' <a href="'.url('qti_item.php').'?t='.$row2['topic'].'#p'.$row2['id'].'" title="'.L('Goto_message').'">'.qtSVG('caret-square-right').'</a>';
}

echo '
<table class="t-profile">
<tr><th>'.L('Username').'</th><td>'.$row['name'].'</td></tr>
<tr><th>'.L('Role').'</th><td>'.$L['Role_'.$row['role']].'</td></tr>
<tr><th>'.L('Location').'</th><td class="fix-sp"><span>'.$strLocation.'</span><span>'.$strPriv.'</span></td></tr>
<tr><th>'.L('Email').'</th><td class="fix-sp"><span>'.$strMail.'</span><span>'.$strPriv.'</span></td></tr>
<tr><th>'.L('Website').'</th><td>'.( empty($row['www']) ? '&nbsp;' : '<a href="'.$row['www'].'" target="_blank">'.$row['www'].'</a>' ).'</td></tr>
<tr><th>'.L('Birthday').'</th><td>'.(empty($row['birthday']) ? '&nbsp;' : qtDate($row['birthday'],'$','')).'</td></tr>
<tr><th>'.L('Joined').'</th><td>'.qtDate($row['firstdate'],'$','$',true).'</td></tr>
<tr><th>'.L('Messages').'</th><td>'.$strParticip.'</td></tr>
';

if ( is_null($row['x']) || is_null($row['y']) ) $bMap = false;
if ( $bMap ) {
  $strPlink = '<a href="http://maps.google.com?q='.$row['y'].','.$row['x'].'" class="small" title="'.L('Gmap.In_google').'" target="_blank">[G]</a>';
  $strPosition = '<div id="map_canvas" style="width:100%; height:350px;"></div>';
  echo '<tr><th>'.L('Coord').'</th><td class="fix-sp"><span>'.$strCoord.' '.$strPlink.'</span><span>'.$strPriv.'</span></td></tr>'.PHP_EOL;
  echo '<tr><td colspan="2" id="gmapcontainer">'.$strPosition.'</td></tr>'.PHP_EOL;
}

echo '</table>
';

// ------
}
// ------
if ( !$_SESSION[QT]['editing'] ) {
if ( SUser::id()==$id || SUser::isStaff() ) {
  echo '<p class="right small">'.$strPriv.' '.L('Privacy_visible_'.$row['privacy']).'</p>';
  $intBan = empty($row['closed']) ? 0 : (int)$row['closed'];
  $days = BAN_DAYS;
  if ( $intBan && array_key_exists($intBan,$days) ) echo '<p class="right small">'.qtSVG('ban').' '.$row['name'].' '.strtolower(sprintf(L('Is_banned_since'),L('day',$days[$intBan]))).'</p>';
}}

echo '</div>
';

// ------
// HTML END
// ------
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
  $y = (float)QTgety($_SESSION[QT]['m_gmap_gcenter']);
  $x = (float)QTgetx($_SESSION[QT]['m_gmap_gcenter']);

  // First item is the user's location and symbol
  if ( isset($arrExtData[$id]) )
  {
    // symbol by role
    $oMapPoint = $arrExtData[$id];
    if ( !empty($oMapPoint->icon) ) $gmap_symbol = $oMapPoint->icon;

    // center on user
    if ( !empty($oMapPoint->y) && !empty($oMapPoint->x) )
    {
    $y=$oMapPoint->y;
    $x=$oMapPoint->x;
    }
  }

  // update center
  $_SESSION[QT]['m_gmap_gcenter'] = $y.','.$x;

  $gmap_markers = [];
  $gmap_events = [];
  $gmap_functions = [];
  if ( isset($arrExtData[$id]) && !empty($oMapPoint->y) && !empty($oMapPoint->x) )
  {
    $gmap_markers[] = gmapMarker($oMapPoint->y.','.$oMapPoint->x,true,$gmap_symbol,$row['name'],'',$gmap_shadow);
    $gmap_events[] = '
	google.maps.event.addListener(markers[0], "position_changed", function() {
		if ( document.getElementById("yx")) {document.getElementById("yx").value = gmapRound(marker.getPosition().lat(),10) + "," + gmapRound(marker.getPosition().lng(),10);}
	});
	google.maps.event.addListener(markers[0], "dragend", function() {
		map.panTo(marker.getPosition());
	});';
  }
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
    for(var i=markers.length-1;i>=0;i--)
    {
      markers[i].setMap(null);
    }
    gmapYXfield("yx",null);
    markers=[];
  }
  ';
  include 'qtim_gmap_load.php';
}

include 'qti_inc_ft.php';