<?php
/**
 * @var string $strSubject
 * @var string $strMessage
 */
$strSubject=$_SESSION[QT]['site_name'].' - '.L('Notification');

$strMessage="
Veuillez noter que le ticket est maintenant : %s
------
%s
------

Salutations,
Le webmaster de {$_SESSION[QT]['site_name']}
{$_SESSION[QT]['site_url']}/index.php
";