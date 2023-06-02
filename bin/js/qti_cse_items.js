// sseServer, sseConnect, sseOrigin, ns (namespace) are created in html <script> page
const sseSource = new EventSource(sseServer+'ext/qti_srv_sse.php?ns='+ns+'&retry='+sseConnect); // We include the retry-delay parametre. It will used in the server's broadcasted messages.
const cseGarbageSection = new Array();
const cseGarbageTopic = new Array();
const cseGarbageReply = new Array();
const cseNewRow = 0;

// Event topic
sseSource.addEventListener('topic', function(e) {
  if ( !inOrigin(e) || !('data' in e) ) return;
  const jd = cseReadSse(e,cseGarbageTopic,['s','t']); if ( !jd ) return;
  let b =  document.getElementById('pg-s'+jd.s); // can insert
  if ( !b ) b = document.getElementById('pg-q-last');
  if ( !b ) b = document.getElementById('pg-q-news') && ('type' in jd) && jd.type=='A';
  console.log('SSE type topic: '+e.data); //!!!
  if ( document.getElementById('t1-tr-'+jd.t) ) { cseUpdate(jd,true); } else { if ( b ) cseInsert('t1',jd); }
}, false);

// Event reply
sseSource.addEventListener('reply', function(e) {
  if ( !inOrigin(e) || !('data' in e) ) return;
  const jd = cseReadSse(e,cseGarbageReply,['s','t']); if ( !jd ) return;
  let b =  document.getElementById('pg-s'+jd.s);
  if ( !b ) b = document.getElementById('pg-q-last');
  if ( !b ) b = document.getElementById('pg-q-news') && ('type' in jd) && jd.type=='A';
  if ( !b ) return;
  if ( !document.getElementById('t1-tr-'+jd.t) ) return;
  console.log('SSE type reply: '+e.data); //!!!
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
function cseGetStatusname(id) {
  return (id in cseStatusnames) ? cseStatusnames[id] : 'status '+id;
}
function cseGetTypename(id) {
  return (id in cseTypenames) ? cseTypenames[id] : 'type '+id;
}
function csegetIconname(type,status) {
  return type=='T' ? cseGetStatusname(status) : cseGetTypename(type);
}
function cseIsset(jd,prop,id,diff=false) {
  if ( !(prop in jd) ) return false;
  if ( !document.getElementById(id) ) return false;
  if ( diff && jd[prop].toString()==document.getElementById(id).innerHTML ) return false;
  return true;
}

// Update page content
function cseUpdate(jd,light=false) {

  if ( !('t' in jd) ) return;
  if ( ('a' in jd) && jd.a==1) return;
  const idrow = 't1-tr-'+jd.t;
  let d;
  if ( cseIsset(jd,'numid','t'+jd.t+'-c-numid',true) ) {
    document.getElementById('t'+jd.t+'-c-numid').innerHTML=jd.numid;
    if (light) qtFlash('#t'+jd.t+'-c-numid');
  }
  if ( cseIsset(jd,'title',idrow) ) {
    d = document.querySelector(idrow+' > .c-title > a');
    if (d) { d.innerHTML=jd.title; d.href='qti_item.php?t='+jd.t; if (light) qtFlash('#'+idrow+' > .c-title'); }
  }
  if ( cseIsset(jd,'replies','t'+jd.t+'-replies',true) ) {
    d = document.getElementById('t'+jd.t+'-replies');
    if (d) {
      let b = true;
      let r = 0;
      if ( jd.replies=='+1' || jd.replies=='-1' ) {
        r = parseInt(d.innerHTML);
        if ( cseIsset(jd,'lastpostid','t'+jd.t+'-lastpostid',true) ) { if ( jd.replies=='+1' ) { ++r; } else { --r; if (r<0) r=0; } } else { b=false; }
        jd.replies=r;
      }
      d.innerHTML = jd.replies;
      if (light && b) qtFlash('#'+idrow+' > .c-replies');
    }
  }
  if ( cseIsset(jd,'firstpostid','t'+jd.t+'-firstpostid',true) ) {
    if ( ('firstpostdate' in jd) && ('firstpostuser' in jd) && ('firstpostname' in jd) ) {
      if (light) qtFlash('#'+idrow+' > .c-firstpostname');
      d = document.getElementById('t'+jd.t+'-firstpostdate'); if (d) { d.innerHTML=jd.firstpostdate; }
      d = document.getElementById('t'+jd.t+'-firstpostname'); if (d) { d.innerHTML=jd.firstpostname; d.href='qti_user.php?id='+jd.firstpostuser; }
    }
  }
  if ( cseIsset(jd,'lastpostid','t'+jd.t+'-lastpostid',true) ) {
    if ( ('lastpostdate' in jd) && ('lastpostuser' in jd) && ('lastpostname' in jd) ) {
      if (light) qtFlash('#'+idrow+' > .c-lastpostdate');
      d = document.getElementById('t'+jd.t+'-lastpostid'); if (d) { d.innerHTML=jd.lastpostid; }
      d = document.getElementById('t'+jd.t+'-lastpostico'); if (d) { d.href='qti_item.php?t='+jd.t+"#p"+jd.lastpostid; }
      d = document.getElementById('t'+jd.t+'-lastpostdate'); if (d) { d.innerHTML=jd.lastpostdate; }
      d = document.getElementById('t'+jd.t+'-lastpostname'); if (d) { d.innerHTML=jd.lastpostname; d.href='qti_user.php?id='+jd.lastpostuser; }
    }
  }
  if ( cseIsset(jd,'status',idrow) ) {
    d = document.querySelector('#'+idrow+' > .c-status');
    if ( d ) {
      const nameN = cseGetStatusname(jd.status);
      const nameO = document.querySelector('#'+idrow+' > .c-status > span');
      if ( nameO && nameN!=nameO.innerHTML ) {
        if ( light ) qtFlash('#'+idrow+' > .c-status');
        nameO.innerHTML = nameN;
        if ( 'statusdate' in jd ) d.title=jd.statusdate;
      }
    }
  }
  if ( cseIsset(jd,'type','t'+jd.t+'-itemicon') ) {
    if ( !('status' in jd) ) jd.status='A';
    jd.imgtitle = csegetIconname(jd.type,jd.status);
    let b = qtUpdateItemIcon(jd); if (light && b) qtFlash('#'+idrow+' > .c-icon');
  }
  if ( cseIsset(jd,'actorname','t'+jd.t+'-actor'),true ) {
    if ( !('actorid' in jd) ) jd.actorid = 1;
    if (light) qtFlash('#'+idrow+' > .c-actor');
    d = document.getElementById('t'+jd.t+'-actor'); if (d) { d.innerHTML=jd.actorname; d.href='qti_user.php?id='+jd.actorid; }
  }
  if ( cseIsset(jd,'imgsrc','t'+jd.t+'-itemicon') ) {
    if ( !('type' in jd) ) jd.type='T';
    if ( !('status' in jd) ) jd.status='A';
    jd.imgtitle = csegetIconname(jd.type,jd.status);
    let b = qtUpdateItemIcon(jd); if (light && b) qtFlash('#'+idrow+' > .c-icon');
  }
  if ( ('stamp' in jd) ) {
    if (jd.stamp=='' ) { document.querySelector('#'+idrow+' > .c-title > span.news').remove(); } else { d = document.querySelector('#'+idrow+' > .c-title'); d.innerHTML = '<span class="news">'+jd.stamp+'</span>' + d.innerHTML; }
  }
}

function cseInsert(tableid,jd) {
  if ( cseMaxRows<1 || cseMaxRows>5 ) cseMaxRows=2;
  var t1 = document.getElementById(tableid);
  if ( t1==null ) return;
  if ( cseShowZ==0 && ('status' in jd) && jd.status=='Z' ) return; // Skip z-status message when client hides Z (closed)
  if ( cseNewRow==cseMaxRows ) { t1.deleteRow(cseMaxRows-1); --cseNewRow; }

  var row1 = t1.rows[t1.rows.length-1];
  var row1topicid = row1.id.replace(tableid+'-tr-','');
  var row0 = t1.insertRow(0); row0.id = tableid+'-tr-'+jd.t;
  ++cseNewRow;
  for(i=0;i<row1.cells.length;i++) {
    row0.insertCell(i);
    row0.cells[i].className = row1.cells[i].className;
    row0.cells[i].innerHTML = row1.cells[i].innerHTML.replaceAll('id="t'+row1topicid+'-','id="t'+jd.t+'-');
    row0.cells[i].id = 't'+jd.t + row1.cells[i].id.substring(row1.cells[i].id.indexOf('-'));
  }
  if ( ('a' in jd) ) jd.a=0;
  cseClearRow(tableid,jd.t);
  cseUpdate(jd);
  document.getElementById(tableid+'-tr-'+jd.t).style.backgroundColor = "#FFFFAA";
}

function cseClearRow(tableid,id) {
  let d;
  d = document.getElementById('t'+id+'-itemicon'); if (d) { d.src='bin/js/qti_cse_items.gif'; d.title=''; }
  d = document.querySelector('#'+tableid+'-tr-'+id+' > .c-title'); if (d) { d.innerHTML='<a class="item" href="javascript:void(0);">unknown title</a>'; }
  d = document.querySelector('#'+tableid+'-tr-'+id+' > .c-numid'); if (d) { d.innerHTML='000'; }
  d = document.querySelector('#'+tableid+'-tr-'+id+' > .c-replies'); if (d) { d.innerHTML='0'; }
  d = document.querySelector('#'+tableid+'-tr-'+id+' > .c-sectiontitle'); if (d) { d.innerHTML='&nbsp;'; }
  d = document.getElementById('t'+id+'-firstpostid'); if (d) { d.innerHTML='-1'; }
  d = document.getElementById('t'+id+'-lastpostid'); if (d) { d.innerHTML='-1'; }
  d = document.getElementById('t'+id+'-firstpostdate'); if (d) { d.innerHTML='now'; }
  d = document.getElementById('t'+id+'-firstpostname'); if (d) { d.innerHTML='visitor'; d.href='javascript:void(0);'; }
  d = document.getElementById('t'+id+'-lastpostico'); if (d) { d.href='javascript:void(0);'; }
  d = document.getElementById('t'+id+'-lastpostdate'); if (d) { d.innerHTML='now'; }
  d = document.getElementById('t'+id+'-lastpostname'); if (d) { d.innerHTML='visitor'; d.href='javascript:void(0);'; }
}

function qtUpdateItemIcon(jd,suffix='-itemicon') {
  if ( !('id' in jd) && !('t' in jd) ) return false;
  const id = 't' + (('t' in jd) ? jd.t : jd.id) + suffix;
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