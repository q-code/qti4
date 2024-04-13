<?php
/**
* @var CHtml $oH
  * @var array $gmap_markers
 * @var array $gmap_events
 * @var array $gmap_functions
 */
if ( !isset($gmap_markers) ) $gmap_markers = [];
if ( !isset($gmap_events) ) $gmap_events = [];
if ( !isset($gmap_functions) ) $gmap_functions = [];

$mapTypeId = gmapMarkerMapTypeId(gmapOption('mt'));
$streetView = gmapOption('sv')==='1' ? 'true' : 'false';
$fullView = gmapOption('fs')==='1' ? 'true' : 'false';
$scaleBar = gmapOption('sc')==='1' ? 'true' : 'false';

if ( !empty($oH->selfurl) && isset($_SESSION[QT]['viewmode']) && $_SESSION[QT]['viewmode']==='c' ) {
if ( $oH->selfurl==='qti_item.php' || $oH->selfurl==='qti_calendars.php' ) {
  $streetView = 'false';
  $scaleBar = 'false';
}}

$oH->scripts[] = '<script type="text/javascript" src="'.APP.'m_gmap_load.js"></script>';
$oH->scripts[] = 'let gmap, gmapOptions, gmapCoder, gmapInfoBox, gmapPin, maker, markers=[];
async function gmapInitialize() {
  const {Map} = await google.maps.importLibrary("maps");
  const {AdvancedMarkerElement} = await google.maps.importLibrary("marker");
  gmapInfoBox = new google.maps.InfoWindow({maxWidth: 220});
  gmapCoder = new google.maps.Geocoder();
  gmapOptions = {
    mapId: "'.strtoupper(APP.'_MAP').'",
    center: new google.maps.LatLng('.$_SESSION[QT]['m_gmap_gcenter'].'),
    mapTypeId: '.gmapMarkerMapTypeId(gmapOption('mt')).',
    streetViewControl: '.(gmapOption('sv')==='1' ? 'true' : 'false' ).',
    mapTypeControl: '.(gmapOption('bg')==='1' ? 'true' : 'false' ).',
    zoom: '.$_SESSION[QT]['m_gmap_gzoom'].',
    scaleControl:'.(gmapOption('sc')==='1' ? 'true' : 'false' ).',
    fullscreenControl:'.(gmapOption('fs')==='1' ? 'true' : 'false' ).',
    scrollwheel:'.(gmapOption('mw')==='1' ? 'true' : 'false' ).'
    };
'.implode(PHP_EOL,$gmap_markers).'
'.implode(PHP_EOL,$gmap_events).'
}
'.implode(PHP_EOL,$gmap_functions);
$oH->scripts[] = gmapApi($_SESSION[QT]['m_gmap_gkey']);