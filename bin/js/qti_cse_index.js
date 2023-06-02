// sseServer, sseConnect, sseOrigin, ns (namespace) are created in html <script> page
// jd (json data) is a data object (parsed from a json data string)
// Garbage(s) are array of (max 5) json data string (not parsed)
const sseSource = new EventSource(sseServer+'ext/qti_srv_sse.php?ns='+ns+'&retry='+sseConnect); // Retry-delay will used in the sse broadcasted messages.
const cseGarbageSection = new Array();
const cseGarbageTopic = new Array();
const cseGarbageReply = new Array();
const debug=true;

// Event topic
sseSource.addEventListener('topic', function(e) {
  if ( !inOrigin(e) || !('data' in e) ) return;
  const jd = cseReadSse(e,cseGarbageTopic,['t']); if ( !jd ) return;
  if ( debug) console.log('SSE type section: '+e.data);
  cseUpdate(jd,true);
}, false);

// Event section
sseSource.addEventListener('section', function(e) {
  if ( !inOrigin(e) || !('data' in e) ) return;
  const jd = cseReadSse(e,cseGarbageSection,['s']); if ( !jd ) return;
  if ( debug) console.log('SSE type section: '+e.data);
  if ( jd.s==='reset' ) { window.setTimeout(function(){ location.reload(true); },10000); return; }
  cseUpdate(jd,true);
}, false);

// Event error
// We use a named-event 'error' because the method .onerror is triggered when server script ends
// When server script ends sseSource must stay opened as the client retry automatically.
sseSource.addEventListener('error', function(e) {
  if ( !inOrigin(e) || !('data' in e) ) return;
  const hm = new Date();
  console.log('SSE('+hm.getHours()+':'+hm.getMinutes()+') Server send error event with data: '+e.data);
  sseSource.close();
  console.log('SSE('+hm.getHours()+':'+hm.getMinutes()+') Client stops sse communication');
}, false);

// Default message
sseSource.onmessage = function(e) {
  if ( !inOrigin(e) ) return;
  console.log('SSE message '+JSON.stringify(e.data));
  if ( document.getElementById('serverData') ) {
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
    url = (new URL(url)).origin; // protocol + domain (+port if not 80|443)
    if ( sseOrigin.split(' ').some(item => item===url) ) return true;
  }
  console.log('Unknown sse origin: message from '+url);
  return false;
}
/**
 * Parse JSON data and manage the Garbage (5 previous data)
 * @param {MessageEvent} e
 * @param {Array} garbage (byref)
 * @param {Array} required mandatory properties in e.data
 * @returns {object|false} json parsed Object or FALSE when data already in the garbage or missing properties
 */
function cseReadSse(e, garbage, required=[]) {
  try{
    const jd = JSON.parse(e.data);
    if ( required.some(item => !(item in jd)) ) return false;
    if ( garbage.indexOf(e.data) > -1 ) return false;
    if ( garbage.length > 5 ) garbage.shift();
    garbage.push(e.data);
    return jd;
  } catch {
    return false;
  }
}
/**
 * Check if property exists in jd, DOM element exists, and (optionaly) if contents are different
 * @param {object} jd
 * @param {string} prop
 * @param {string} id element id
 * @param {boolean} checkContent check content
 * @returns {boolean} is old, update can be applied
 */
function cseIsOld(jd, prop, id, checkContent=false) {
  if ( !(prop in jd) ) return false;
  if ( !document.getElementById(id) ) return false;
  if ( checkContent && jd[prop].toString()===document.getElementById(id).innerHTML ) return false;
  return true;
}
/**
 * Update page content
 * @param {object} jd
 * @param {boolean} light
 */
function cseUpdate(jd, light=false) {
  if ( !('s' in jd) || jd.s<0 ) jd.s='null';
  var id = 's'+jd.s;
  if ( cseIsOld(jd,'sumitems',id+'-items',true) ) { document.getElementById(id+'-items').innerHTML = jd.sumitems; if (light) qtFlash('#'+id+'-items'); }
  if ( cseIsOld(jd,'sumreplies',id+'-replies',true) ) { document.getElementById(id+'-replies').innerHTML = jd.sumreplies; if (light) qtFlash('#'+id+'-replies'); }
  if ( cseIsOld(jd,'lastpostdate',id+'-issue') ) {
    if (light) qtFlash('#'+id+'-issue');
    if ( cseIsOld(jd,'lastpostdate',id+'-lastpostdate') ) document.getElementById(id+'-lastpostdate').innerHTML = jd.lastpostdate;
    if ( cseIsOld(jd,'lastpostpid',id+'-lastpostpid') ) document.getElementById(id+'-lastpostpid').href = 'qti_item.php?t='+jd.lastpostpid+'#p'+jd.lastpostid;
    if ( cseIsOld(jd,'lastpostuser',id+'-lastpostuser',true) ) {
      document.getElementById(id+'-lastpostuser').href = 'qti_user.php?id'+jd.lastpostuser;
      if ( cseIsOld(jd,'lastpostname',id+'-lastpostuser') ) document.getElementById(id+'-lastpostuser').innerHTML = jd.lastpostname;
    }
  }
  // MyLastTicket
  if ( cseIsOld(jd,'t','t'+jd.t+'-itemicon') ) { b = qtUpdateItemIcon(jd); if (light && b) qtFlash('#mylastitem > p.title'); }
}
/**
 * @param {object} jd
 * @param {string} suffix
 * @returns {boolean}
 */
function qtUpdateItemIcon(jd,suffix='-itemicon'){
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