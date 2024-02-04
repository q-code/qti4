<?php
/**
 * @var string $strSubject
 * @var string $strMessage
 */
$strSubject=$_SESSION[QT]['site_name'].' - '.L('Notification');

$strMessage="
Please note that the ticket is now: %s
------
%s
------

Regards,
The webmaster of {$_SESSION[QT]['site_name']}
{$_SESSION[QT]['site_url']}/index.php
";