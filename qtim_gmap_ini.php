<?php
/**
 * @var string $strCheck
* @var CHtml $oH
 */
$useMap=true;
if ( empty($_SESSION[QT]['m_gmap_gkey']) ) $useMap=false;
if ( $useMap ) { require 'qtim_gmap_lib.php'; if ( !gmapCan($strCheck,SUser::role()) ) $useMap=false; }
if ( $useMap )
{
  include translate('qtim_gmap.php');
  $useMapGoogle=true;
  if ( $useMapGoogle ) $oH->links[] = '<link rel="stylesheet" type="text/css" href="qtim_gmap.css" />';
  if ( isset($_GET['hidemap']) ) $_SESSION[QT]['m_gmap_hidelist']=true;
  if ( isset($_GET['showmap']) ) $_SESSION[QT]['m_gmap_hidelist']=false;
  if ( !isset($_SESSION[QT]['m_gmap_hidelist']) ) $_SESSION[QT]['m_gmap_hidelist']=false;
}