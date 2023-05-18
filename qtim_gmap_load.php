<?php
/**
* @var CHtml $oH
  * @var array $gmap_markers
 * @var array $gmap_events
 * @var array $gmap_functions
 */
if ( !isset($gmap_markers) ) $gmap_markers = array();
if ( !isset($gmap_events) ) $gmap_events = array();
if ( !isset($gmap_functions) ) $gmap_functions = array();

$mapTypeId = gmapMarkerMapTypeId(substr($_SESSION[QT]['m_gmap_gbuttons'],0,1));
$streetView = substr($_SESSION[QT]['m_gmap_gbuttons'],1,1)==='1' ? 'true' : 'false';
$fullView = substr($_SESSION[QT]['m_gmap_gbuttons'],4,1)==='1' ? 'true' : 'false';
$scaleBar = substr($_SESSION[QT]['m_gmap_gbuttons'],3,1)==='1' ? 'true' : 'false';

if ( !empty($oH->selfurl) && isset($_SESSION[QT]['viewmode']) && $_SESSION[QT]['viewmode']==='c' ) {
if ( $oH->selfurl==='qti_item.php' || $oH->selfurl==='qti_calendars.php' ) {
  $streetView = 'false';
  $scaleBar = 'false';
}}

$oH->scripts[] = 'var map, mapOptions, geocoder, infowindow;
var markers = [];
function gmapInitialize(){
  infowindow = new google.maps.InfoWindow({maxWidth: 220});
  geocoder = new google.maps.Geocoder();
  mapOptions = {
    zoom: '.$_SESSION[QT]['m_gmap_gzoom'].',
    center: new google.maps.LatLng('.$_SESSION[QT]['m_gmap_gcenter'].'),
    zoomControl:true,
    mapTypeId: '.$mapTypeId.',
    streetViewControl: '.$streetView.',
    mapTypeControl: '.(substr($_SESSION[QT]['m_gmap_gbuttons'],2,1)==='1' ? 'true' : 'false' ).',
    mapTypeControlOptions:
    {
    style:google.maps.MapTypeControlStyle.DROPDOWN_MENU,
    mapTypeIds:[google.maps.MapTypeId.ROADMAP,"OSM",google.maps.MapTypeId.SATELLITE,google.maps.MapTypeId.HYBRID,google.maps.MapTypeId.TERRAIN]
    },
    scaleControl: '.$scaleBar.',
    fullscreenControl: '.$fullView.',
    scrollwheel: '.(substr($_SESSION[QT]['m_gmap_gbuttons'],5,1)==='1' ? 'true' : 'false' ).'
    };
  map = new google.maps.Map(document.getElementById("map_canvas"), mapOptions);
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
    document.getElementById(id).value = gmapRound(marker.getPosition().lat()) + "," + gmapRound(marker.getPosition().lng());
  } else {
    document.getElementById(id).value = "";
  }
}
'.implode(PHP_EOL,$gmap_functions);
$oH->scripts[] = gmapApi($_SESSION[QT]['m_gmap_gkey']);