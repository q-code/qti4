const acUrlConfig = function(method,value) {
  let url = 'bin/srv_query.php?q='+method;
  let dir;
  let lang;
  let s;
  switch(method)
  {
    case 'ref':
      s = document.getElementById('user-s') ? document.getElementById('user-s').value : '*';
      url += `&s=${s}&v=${value}`;
      break;
    case 'qkw':
    case 'notify':
      url += `&v=${value}`;
      break;
    case 'kw':
      s = document.getElementById('kw-s').value; if ( s==='-1' || s==='' ) s = '*';
      url += `&s=${s}&v=${value}`;
      break;
    case 'tag-edit':
      dir = document.getElementById('tag-dir') ? document.getElementById('tag-dir').value : 'upload/';
      lang = document.getElementById('tag-lang') ? document.getElementById('tag-lang').value : 'en';
      url += `&v=${value}&lang=${lang}&dir=${dir}`;
      break;
    case 'behalf':
    case 'user':
    case 'userm':
      s = document.getElementById('user-s') ? document.getElementById('user-s').value : '*';
      url += `&s=${s}&v=${value}`;
      break;
    default: console.log('unknown input method '+method); return;
  }
  return url;
}