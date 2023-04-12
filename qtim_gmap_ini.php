<?php
/**
 * @var string $strCheck
* @var CHtml $oH
 */
$bMap=true;
if ( empty($_SESSION[QT]['m_gmap_gkey']) ) $bMap=false;
if ( $bMap ) { require 'qtim_gmap_lib.php'; if ( !gmapCan($strCheck,SUser::role()) ) $bMap=false; }
if ( $bMap )
{
  include translate('qtim_gmap.php');
  $bMapGoogle=true;
  if ( $bMapGoogle ) $oH->links[] = '<link rel="stylesheet" type="text/css" href="qtim_gmap.css" />';
  if ( isset($_GET['hidemap']) ) $_SESSION[QT]['m_gmap_hidelist']=true;
  if ( isset($_GET['showmap']) ) $_SESSION[QT]['m_gmap_hidelist']=false;
  if ( !isset($_SESSION[QT]['m_gmap_hidelist']) ) $_SESSION[QT]['m_gmap_hidelist']=false;
}