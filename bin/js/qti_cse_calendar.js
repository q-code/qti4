// sseServer, sseConnect, sseOrigin, ns (namespace) are created in html <script> page
const sseSource = new EventSource(sseServer+'ext/qti_srv_sse.php?ns='+ns+'&retry='+sseConnect); // We include the retry-delay parametre. It will used in the server's broadcasted messages.
var cseGarbageTopic = new Array();
var cseNewRow = 0;

// Event topic
sseSource.addEventListener('topic', function(e) {
  if ( !inOrigin(e) || !('data' in e) ) return;
  const jd = cseReadSse(e,cseGarbageTopic,['s','t']); if ( !jd ) return;
  if ( !document.getElementById('t'+jd.t) )  return;
  console.log('SSE send topic event with data: '+e.data);
  qtUpdateItemIcon(jd);
  qtUpdateItemIcon(jd,'-itemicon-preview');
  qtFlash('td.date:has(#t'+jd.t+')',3000);
}, false);

// Event error
// We use a named-event 'error' because the method .onerror is triggered when server script ends
// When server script ends, sseSource must stay opened as the client retry automatically.
sseSource.addEventListener('error', function(e) {
  if ( !inOrigin(e) || !('data' in e) ) return;
  const hm = new Date();
  console.log('SSE('+hm.getHours()+':'+hm.getMinutes()+') Server send error event with data: '+e.data);
  sseSource.close();
  console.log('SSE('+hm.getHours()+':'+hm.getMinutes()+') Client stops sse communication');
}, false);

// Default message
sseSource.onmessage = function(e) {
  if ( !inOrigin(e) || !('data' in e) ) return;
  console.log('SSE message '+JSON.stringify(e.data));
  if ( document.getElementById('serverData') )
  {
    if ( document.getElementById('serverData').innerHTML.length>255 ) document.getElementById('serverData').innerHTML='';
    document.getElementById('serverData').innerHTML += JSON.stringify(e.data)+'<br/>';
  }
};

/**
 * @param {MessageEvent} e
 * @returns {boolean}
 */
function inOrigin(e) {
  let url = e?.origin || e?.target?.url;
  if ( url ) {
    url = (new URL(url)).origin; // protocol+domain+port
    for( const origin of sseOrigin.split(' ') ) if ( url===origin ) return true;
  }
  console.log('Unknown sse origin: message from '+url);
  return false;
}
// Read and control sse event
function cseReadSse(e,garbage,minimumData=[]) {
  // This checks the event origin, format, and manage a garbage of already processed events (byref).
  // Returns an object (json data parsed) or FALSE when the data is in the garbage (or when format/minimumdata is wrong)
  const jd = JSON.parse(e.data);
  for (i = minimumData.length - 1; i >= 0; --i) if ( !(minimumData[i] in jd) ) return false;
  if ( garbage.indexOf(e.data) > -1 ) return false;
  if ( garbage.length > 5 ) garbage.shift();
  garbage.push(e.data);
  return jd;
}

function qtUpdateItemIcon(jd,suffix='-itemicon') {
  if ( !('id' in jd) && !('t' in jd) ) return false;
  var id = 't' + (('t' in jd) ? jd.t : jd.id) + suffix;
  if ( !document.getElementById(id) ) return false;
  if ( !('imgsrc' in jd) ) jd.imgsrc = 'bin/js/qti_cse_items.gif';
  jd.imgsrc = jd.imgsrc.replace(/\\/g, '');
  if ( document.getElementById(id).src.indexOf(jd.imgsrc)>0 ) return false;
  if ( !('imgtitle' in jd) ) jd.imgtitle = '';
  if ( !('imgalt' in jd) ) jd.imgalt = '';
  document.getElementById(id).src = jd.imgsrc;
  document.getElementById(id).title = jd.imgtitle;
  document.getElementById(id).alt = jd.imgalt;
  return true;
}
/**
 * @param {string} selector
 * @param {numeric} duration
 */
function qtFlash(selector,duration=2000){
  const d = document.querySelector(selector);
  if ( d ) {
    d.classList.add('bg-flash');
    setTimeout( () => {d.classList.remove('bg-flash');}, duration);
  }
}