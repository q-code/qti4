<?php

/**
* PHP version 7
*
* LICENSE: This source file is subject to version 3.0 of the PHP license
* that is available through the world-wide-web at the following URI:
* http://www.php.net/license. If you did not receive a copy of
* the PHP License and are unable to obtain it through the web, please
* send a note to license@php.net so we can mail you a copy immediately.
*
* @package    QuickTicket
* @author     Philippe Vandenberghe <info@qt-cute.org>
* @copyright  2015 The PHP Group
* @version    4.0 build:20240210
*/

session_start();
/**
 * @var CHtml $oH
 * @var array $L
 * @var CDatabase $oDB
 */
require 'bin/init.php';
include translate('lg_adm.php');

if ( SUser::role()!=='A' ) die(L('E_13'));

$oH->selfurl = 'qti_adm_sse.php';
$oH->selfname = 'SSE (server-sent events)';
$oH->selfparent = L('Settings');
$oH->exiturl = $oH->selfurl;
$oH->exitname = $oH->selfname;

// INITIALISE variable $sse_* from $_SESSION[QT]['sse']
foreach(qtExplode($_SESSION[QT]['sse']) as $key=>$value) {
  $key = 'sse_'.strtolower($key);
  $$key = $value;
}

// ------
// SUBMITTED
// ------
if ( isset($_POST['ok']) ) try {

  qtArgs('int:sse_connect sse_server sse_origin int:sse_maxrows', false); // in post only

  if ( empty($sse_origin) ) { $error = L('origin').' '.L('Invalid'); $sse_origin = 'http://localhost'; }
  if ( !qtIsBetween($sse_maxrows,1,5) ) { $error = L('rows').' '.L('Invalid').' (0-5)'; $sse_maxrows = '2'; }

  // save (without SSE_ prefix. The prefix is added during initialisaiton)
  if ( empty($error) ) {
    $_SESSION[QT]['sse'] = 'CONNECT='.$sse_connect.';ORIGIN='.$sse_origin.';MAXROWS='.$sse_maxrows.';SERVER='.$sse_server;
    $oDB->updSetting('sse');
    SMem::set('settingsage',time());
  }

  // exit
  $_SESSION[QT.'splash'] = L('S_save');

} catch (Exception $e) {

  $_SESSION[QT.'splash'] = 'E|'.$e->getMessage();

}

// ------
// HTML BEGIN (fieldnames are uppercase as they are used to define constants)
// ------
// warning
if ( !empty($sse_origin) && substr($sse_origin,0,7)!=='http://' && substr($sse_origin,0,8)!=='https://' ) $oH->warning = L('Origin').' '.sprintf(L('Address_http'),'http','https');
if ( !empty($sse_server) && substr($sse_server,0,7)!=='http://' && substr($sse_server,0,8)!=='https://' ) $oH->warning = L('Server').' '.sprintf(L('Address_http'),'http','https');

include APP.'_adm_inc_hd.php';

// FORM
echo '
<form class="formsafe" method="post" action="'.$oH->self().'">
<h2 class="config">SSE</h2>
<table class="t-conf">
';
echo '<tr>
<th>'.L('Requery_status').'</th>
<td>
<span style="display:inline-block;width:16px;background-color:'.(empty($sse_connect) ? 'red' : 'green').';border-radius:3px">&nbsp;</span> <span>'.L((empty($sse_connect) ? 'Off' : 'On').'_line').'</span>
<span class="indent"><input required id="sse_connect" type="text" name="sse_connect" size="2" maxlength="2" value="'.$sse_connect.'" pattern="[0-9]{1,2}"/> '.L('seconds').'</span>
</td>
</tr>
';
echo '<tr>
<th>&nbsp;</th>
<td>';
echo isset($L['SSE_1']) ? $L['SSE_1'] : 'To enable SSE set a requery delay value (recommended 10 seconds).<br>To disable SSE, use 0.';
echo '</td>
</tr>
';
echo '<tr>
<th>'.L('Recent_messages').'</th>
<td><select id="sse_maxrows" name="sse_maxrows">'.qtTags([1=>1,2,3,4,5],(int)$sse_maxrows).'</select></td>
</tr>
';
echo '<tr>
<th>&nbsp;</th>
<td>';
echo isset($L['SSE_3']) ? $L['SSE_3'] : 'Number of recent tickets that can be added on top of the section list.<br>When more topics arrive, the oldest is replaced.<br>Recommended 2.';
echo '</td>
</tr>
';
echo '<tr>
<th>'.L('External_server').'</th>
<td><select id="useServer" name="useServer" onchange="qtToggle(`sse_server`,`inline`);">'.qtTags([L('N'),L('Y')], empty($sse_server) ? 0 : 1).'</select>
 <input id="sse_server" type="text" name="sse_server" size="50" style="display:'.(empty($sse_server) ? 'none' : 'inline').'" placeholder="Path to ext directory, ex: https://srv01.domain.com/app/qti/" />
</td>
</tr>
';
echo '<tr>
<th>&nbsp;</th>
<td>';
echo isset($L['SSE_4']) ? $L['SSE_4'] : 'This is possible only if memcache and [ext] directory are on an other server.';
echo '</td>
</tr>
</table>
';

echo '<h2 class="config">'.L('Security').'</h2>
<table class="t-conf">
';
echo '<tr>
<th>'.L('Origin').'</th>
<td><input type="text" id="sse_origin" name="sse_origin" size="50" maxlength="500" value="'.qtAttr($sse_origin).'" title="Protocol domain (and port if not default). Ex: https://www.mycompany.com"/></td>
</tr>
';
echo '<tr>
<th>&nbsp;</th>
<td>';
echo isset($L['SSE_2']) ? $L['SSE_2'] : 'Origin is a security control required to reject messages coming from other servers. It\'s possible to enter here several origins (space separated). If the server script (qti_srv_sse.php) is on the same server as the other pages, it must be your board url (http://www.yourdomain.com).<br><br>To identify the correct origin, put temporary http://x here, then check the javascript consol log on the index page. The origin will be reported after 10 seconds.';
echo '</td>
</tr>
</table>
';
echo '<p class="submit"><button type="submit" name="ok" value="ok">'.L('Save').'</button></p>
</form>
';

// HTML END

include APP.'_adm_inc_ft.php';