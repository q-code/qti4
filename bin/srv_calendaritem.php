<?php // v4.0 build:20230205

if ( empty($_GET['term']) || substr($_GET['term'],0,1)!=='t' ) { echo 'configuration error'; return; }

include '../config/config_db.php';
if ( strpos(QDB_SYSTEM,'sqlite') ) define ('QDB_SQLITEPATH', '../');
define( 'QT', 'qti'.(defined('QDB_INSTALL') ? substr(QDB_INSTALL,-1) : '') );
include 'lib_qt_sys.php';
include 'lib_qt_txt.php';
include 'class/class.qt.db.php';
include 'lib_qti_base.php';

// Protection against injection (accept only 3 'lang')
$id = (int)substr($_GET['term'],1);
$iso = strip_tags($_GET['iso']);
$lang = strip_tags($_GET['lang']);
include '../'.$lang.'lg_main.php';

$oDBAJAX = new CDatabase();
if ( !empty($oDBAJAX->error) ) exit;

// Query (without table constants)
$oDBAJAX->query( "SELECT t.*,p.icon,p.title,p.icon as smile,p.textmsg FROM (".QDB_PREFIX."qtitopic t INNER JOIN ".QDB_PREFIX."qtipost p ON t.firstpostid = p.id) WHERE t.id=".$id);
if ( $row=$oDBAJAX->getRow() )
{
  $oDBAJAX->query( "SELECT s.numfield,s.title,l.objname FROM ".QDB_PREFIX."qtisection s LEFT JOIN ".QDB_PREFIX."qtilang l ON (s.id=l.objid AND l.objtype='sec' AND l.objlang='$iso') WHERE s.id=".$row['section'] );
  $row2 = $oDBAJAX->getRow();

  // Output the response
  echo '<p class="preview_section ellipsis">'.L('Section').': '.(empty($row2['objname']) ? $row2['title'] : $row2['objname']).'</p>';
  echo '<div class="preview_item"><p class="preview_title"><span id="preview-itemicon"></span>&nbsp;';
  if ( $row2['numfield']!='N' )
  {
    printf($row2['numfield'],$row['numid']);
    echo '<br>';
  }
  echo $row['title'].'</p>';

  echo '<p class="preview_message">'.QTinline($row['textmsg']).'</p>';
  echo '<p class="preview_user ellipsis">'.$row['firstpostname'].'</p></div>';
  echo '<p class="preview_date ellipsis">'.L('Created').': '.QTdatestr($row['firstpostdate'],'M d','',true).'</p>';
  if ( !empty($row['wisheddate']) ) echo '<p class="preview_date ellipsis">'.L('Wisheddate').': '.QTdatestr($row['wisheddate'],'M d','',true).'</p>';
  if ( $row['actorid']>=0 ) echo '<p class="preview_date">'.L('Actor').': '.$row['actorname'].'</p>';
}
else
{
  echo 'Missing post. Unable to show topic details.';
}