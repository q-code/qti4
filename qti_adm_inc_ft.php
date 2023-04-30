<?php // v4.0 build:20230430
/**
 * @var CHtml $oH
 * @var CDatabase $oDB
 */

echo '
</div>
';

if ( isset($oDB->stats) )
{
  $end = (float)vsprintf('%d.%06d', gettimeofday());
  if ( isset($oDB->stats['num']) ) echo $oDB->stats['num'],' queries. ';
  if ( isset($oDB->stats['start']) ) echo 'End queries in ',round($end-$oDB->stats['start'],4),' sec. ';
  if ( isset($oDB->stats['pagestart']) ) echo 'End page in ',round($end-$oDB->stats['pagestart'],4),' sec. ';
}

echo '
</div>
';

// Automatic add script {file.php.js} if existing
if ( file_exists($oH->selfurl.'.js') ) $oH->scripts[] = '<script type="text/javascript" src="'.$oH->selfurl.'.js"></script>';

$oH->end();

ob_end_flush();