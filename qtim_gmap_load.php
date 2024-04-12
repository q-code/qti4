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

$oH->scripts[] = 'let map, mapOptions, geocoder, infowindow;
var markers = [];
async function gmapInitialize()
{
  const {Map} = await google.maps.importLibrary("maps");
  const {AdvancedMarkerElement} = await google.maps.importLibrary("marker");
  infowindow = new google.maps.InfoWindow({maxWidth: 220});
  geocoder = new google.maps.Geocoder();
  mapOptions = {
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
  map = new Map(document.getElementById("map_canvas"), mapOptions);
  //Define OSM map type pointing at the OpenStreetMap tile server
    map.mapTypes.set("OSM", new google.maps.ImageMapType({
    getTileUrl: function(coord, zoom) { return "http://tile.openstreetmap.org/" + zoom + "/" + coord.x + "/" + coord.y + ".png"; },
    tileSize: new google.maps.Size(256, 256),
    name: "Street",
    maxZoom: 18
    }));
  var marker;
'.implode(PHP_EOL,$gmap_markers).'
'.implode(PHP_EOL,$gmap_events).'
}
function gmapInfo(marker,info){
  if ( !marker || !info || info=="" ) return;
  google.maps.event.addListener(marker, "click", function() { infowindow.setContent(info); infowindow.open(map,marker); });
}
function gmapPan(latlng){
  if ( !latlng ) return;
  if ( latlng.length==0 ) return;
  if ( infowindow ) infowindow.close();
  var yx = latlng.split(",");
  map.panTo(new google.maps.LatLng(parseFloat(yx[0]),parseFloat(yx[1])));
}
function gmapRound(num){
  return Math.round(num*Math.pow(10,11))/Math.pow(10,11);
}
function gmapYXfield(id,marker){
  if ( !document.getElementById(id)) return;
  if ( marker )
  {
    document.getElementById(id).value = gmapRound(marker.position.lat) + "," + gmapRound(marker.position.lng);
  } else {
    document.getElementById(id).value = "";
  }
}
'.implode(PHP_EOL,$gmap_functions);
$oH->scripts[] = gmapApi($_SESSION[QT]['m_gmap_gkey']);