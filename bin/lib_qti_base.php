<?php // v4.0 build:20230205

function makeFormCertificate(string $publickey)
{
  switch($publickey) {
    case '2b174f48ab4d9704934dda56c6997b3a':
    case 'fd352f14798ecfe7a6ae09fd447c207b':
    case '5ca766092492acd750e3061b032bd0d8':
    case '65699386509abf064aec83e5124c1f30':
    case 'b7033b5983ec3b0fef7b3c251f6d0b92':
    case '3cb4bd2256f4642777c70f1cc0efcc77': return md5($publickey.APP.$_SERVER['REMOTE_ADDR'].QT_HASHKEY);
    default: global $oH; $oH->log[]='makeFormCertificate: cannot make certificate on public key '.$publickey; return '';
  }
  // Allow checking that POST requests come from a qtx page (register,login,search,items,item,edit)
}

// --------
// Specific functions: added for or using [SMem] class
// --------

function memInitialise(string $key, $alt=false)
{
  // Recomputes basic qt data (will be stored in shared-memory with reference $key)
  if ( substr($key,0,2)==='_L' )
  {
    $iso = substr($key,2);
    return [
      'index' => SLang::get('index', $iso, '*'),
      'domain' => SLang::get('domain', $iso, '*'),
      'sec' => SLang::get('sec', $iso, '*'),
      'secdesc' => SLang::get('secdesc', $iso, '*'),
      'status' => SLang::get('status', $iso, '*'),
      'statusdesc' => SLang::get('statusdesc', $iso, '*') ];
  }
  switch($key) {
    case 'settingsage': return time();
    case '_Domains': return CDomain::getPropertiesAll(); // ALL domains (including empty/invisible domains), array contains property=>value from CDomain class
    case '_Sections': return CSection::getPropertiesAll(); // ALL sections (including empty/invisible sections), array contains property=>value from CSection class
    case '_NewUser': global $oDB; return SUser::getLastMember($oDB); // last registered user
    case '_SectionsStats': return CSection::getSectionsStats(true); // count topics|Z and replies|Z, by section (all)
    case '_Statuses': return SStatus::getAll();
  }
  return $alt; // unknown key returns (false)
}
function memFlush(array $arrKeep=['_Domains'])
{
  if ( MEMCACHE_HOST===false ) return;
  // Flush keys, if not in the $arrKeep list (by default, _Domains is preserved)
  foreach(['_Domains','_Sections','_SectionsStats','_Statuses'] as $k) if ( !in_array($k,$arrKeep) ) SMem::clear($k);
  return true;
}
function memFlushLang()
{
  if ( MEMCACHE_HOST===false ) return;
  foreach(array_keys(LANGUAGES) as $iso) SMem::clear('_L'.$iso);
  return true;
}
function memFlushStats($arrYears='default')
{
  if ( MEMCACHE_HOST===false ) return;
  // Flushes global stat
  SMem::clear('statG');
  // Check list of years. 'default' means last 2 years.
  if ( $arrYears==='default' ) { $y=(int)date('Y'); $arrYears=array($y-1,$y); }
  if ( !is_array($arrYears) ) die('memFlushStats: arg #1 must be array');
  // Stats are stored by [year][serie][blocktime] using keys:
  // serie: T=topics, R=replies, Z=unreplied and opened, U=users having post, N=type news, C=status closed, ATT=attachments,
  // blocktime: m=per month, q=per quarter, d=last 10 days
  foreach($arrYears as $year) {
  foreach(array('T','R','Z','U','N','C','ATT') as $serie) {
  foreach(array('q','m','d') as $bt) {
  SMem::clear('statD'.$year.$serie.$bt);
  SMem::clear('statS'.$year.$serie.$bt);
  }}}
  return true;
}

// --------
// COMMON FUNCTIONS
// --------

function emptyFloat($i)
{
  // Return true when $i is empty or a value starting with '0.000000'
  if ( empty($i) ) return true;
  if ( !is_string($i) && !is_float($i) && !is_int($i) ) die('emptyFloat: Invalid argument #1, must be a float, int or string');
  if ( substr((string)$i,0,8)=='0.000000' ) return true;
  return false;
}

function asEmails($emails, string $render='txt', bool $first=false, string $none='')
{
  if ( empty($emails) ) return $none;
  if ( is_string($emails) && strpos($emails,';')!==false ) $emails = str_replace(';', ',', $emails); //comma is recommanded as email separator
  if ( is_string($emails) ) $emails = asCleanArray($emails,',');
  if ( !is_array($emails) || empty($emails) ) return $none;
  if ( $first ) $emails = array($emails[0]);
  // build expression
  $return = '';
  $hmails = str_replace('@','-at-',str_replace('.','-dot-',implode(',',$emails)));
  switch($render)
  {
    case 'txt':
      $return .= '<a href="mailto:'.implode(',',$emails).'">';
      $return .= implode(', ',$emails);
      $return .= '</a>';
      break;
    case 'ico':
    case 'img':
      $return .= '<a href="mailto:'.implode(',',$emails).'" title="'.$emails[0].'">';
      $return .= ''.getSVG('envelope');
      $return .= '</a>';
      break;
    case 'txtjava':
      $return .= '<script type="text/javascript">const m = "'.$hmails.'"; document.write(`<a href="javascript:void(0)" onmouseover="qtHrefShow(this);" onmouseout="qtHrefHide(this);" data-emails="${m}">${qtDecodeEmails(m)}</a>`);</script>';
      break;
    case 'icojava':
    case 'imgjava':
      $return .= '<a href="javascript:void(0)" onmouseover="qtHrefShow(this);" onmouseout="qtHrefHide(this);" data-emails="'.$hmails.'">';
      $return .= ''.getSVG('envelope');
      $return .= '</a>';
      break;
    default: die('invalid render');
  }
  return $return;
}

function asImg(string $src='', string $attr='', string $href='', string $attrHref='', string $imgDflt='alt=S|class=i-sec')
{
  $attr = attrDecode($attr, '|', $imgDflt);
  // no href
  if ( empty($href) ) return '<img src="'.$src.'"'.attrRender($attr).'/>';
  // with href
  return '<a href="'.$href.'"'.attrRender($attrHref).'><img src="'.$src.'"'.attrRender($attr).'/></a>';
}

/**
 * @param string $d
 * @param int $i
 * @param string $str
 * @return string
 */
function addDate(string $d='', int $i=-1, string $str='year')
{
  if ( empty($d) ) die('addDate: Argument #1 must be a string');
  $intY = (int)substr($d,0,4);
  $intM = (int)substr($d,4,2);
  $intD = (int)substr($d,6,2);
  switch($str)
  {
  case 'year': $intY += $i; break;
  case 'month': $intM += $i; break;
  case 'day': $intD += $i; break;
  }
  if ( in_array($intM,array(1,3,5,7,8,10,12)) && $intD>31 ) { $intM++; $intD -= 31; }
  if ( in_array($intM,array(4,6,9,11)) && $intD>30 ) { $intM++; $intD -= 30; }
  if ( $intD<1 ) { $intM--; $intD += 30; }
  if ( $intM>12 ) { $intY++; $intM -= 12; }
  if ( $intM<1 ) { $intY--; $intM += 12; }
  if ( $intM==2 && $intD>28 ) { $intM++; $intD -= 28; }
  return (string)($intY*10000+$intM*100+$intD).(strlen($d)>8 ? substr($d,8) : '');
}

function getSections(string $role='V', int $domain=-1, array $reject=[], string $filter='', string $order='d.titleorder,s.titleorder')
{
  // Returns an array of [key] section id, array of [values] section
  // Use $domain to get section in this domain only
  // $domain=-1 mean in alls domains. -2 means in all domains but grouped by domain
  // Attention: using $domain -2, when a domains does NOT contains sections, this key is NOT existing in the returned list !

  global $oDB;

  $sqlWhere = $domain>=0 ? "s.domainid=$domain" : "s.domainid>=0";
  if ( $role=='V' || $role=='U' ) $sqlWhere .= " AND s.type<>'1'";
  if ( !empty($filter) ) $sqlWhere .= " AND $filter";

  $arrSections = array();
  $oDB->query( "SELECT s.* FROM TABSECTION s INNER JOIN TABDOMAIN d ON s.domainid=d.id WHERE $sqlWhere ORDER BY $order" );
  while($row=$oDB->getRow())
  {
    $id = (int)$row['id'];
    // if reject
    if ( in_array($id,$reject,true) ) continue;
    // search translation
    $row['title'] = SLang::translate('sec', 's'.$id, $row['title']);
    // compile sections
    if ( $domain==-2 )
    {
      $arrSections[(int)$row['domainid']][$id] = $row;
    }
    else
    {
      $arrSections[$id] = $row;
    }
  }
  return $arrSections;
}

function getURI(string $reject='')
{
  $reject = explode(',',$reject);
  $arr = qtExplodeUri();
  foreach($reject as $key) unset($arr[trim($key)]);
  return qtImplode($arr);
}

function getItemsInfo(CDatabase $oDB) {
  $arr = array();
  $arr['post'] = $oDB->count( TABPOST );
  $arr['startdate'] = $arr['post']==0 ? '' : QTdatestr( $oDB->count( "SELECT min(firstpostdate) as countid FROM ".TABTOPIC ),'$', '' );
  $arr['topic'] = $oDB->count( TABTOPIC );
  $arr['reply'] = $oDB->count( TABPOST." WHERE type<>'P'" );
  $arr['content'] = L('Message',$arr['post']).' <span  class="small">('.L('Item',$arr['topic']).', '.L('Reply',$arr['reply']).')</span>';
  return $arr;
}

function getUserInfo($ids, string $fields='name', bool $excludezero=true)
{
   return array_shift(getUsersInfo($ids, $fields, $excludezero)); // can return null (if ids not found)
}
function getUsersInfo($ids, string $fields='name', bool $excludezero=true)
{
  // ids can be a int|'A'|'M'|'S'|csv|array
  $where = '';
  if ( is_int($ids) && $ids>=0 ) {
    if ( $excludezero && $ids===0 ) die('getUsersInfo: ids=0');
    $where = "id=$ids";
  } elseif ( $ids==='A' || $ids==='M' ) {
    $where = "role='$ids'";
  } elseif ( $ids==='S' ) {
    $where = "(role='A' OR role='M')";
  } else {
    if ( is_string($ids) ) $ids = explode(',',$ids);
    if ( !is_array($ids) ) die('getUsersInfo: unknown ids type');
    $ids = array_map('intval',$ids); // csv and array are casted as int (non-intval members become 0)
    if ( $excludezero && in_array(0,$ids) ) die('getUsersInfo: ids includes 0');
    $where = 'id IN ('.implode(',',$ids).')';
  }
  if ( empty($where) ) die('getUsersInfo: invalid ids');

  $res = array();
  global $oDB; $oDB->query( "SELECT id,$fields FROM TABUSER WHERE $where" );
  while( $row=$oDB->getRow() ) $res[(int)$row['id']] = $row;
  return $res;
}

function getUsers(string $q='A', string $name='', int $max=100)
{
  // $q={A|S|M|U|N|N*} role admin, admin or moderator, moderator, user or name $name or name starting by $name
  // $max maximum number of results (0 means unlimited)
  global $oDB;
  switch($q)
  {
    case 'A': $where = "role='A'"; break;
    case 'S': $where = "role='A' OR role='M'"; break;
    case 'M': $where = "role='M'"; break;
    case 'U': $where = "role='U'"; break;
    case 'N':
      if ( empty($name) ) die('getUsers: invadid name value');
      $name = QTdb(trim($name));
      $where = "name='$name'";
      break;
    case 'N*':
      if ( empty($name) ) die('getUsers: invadid name value');
      $name = QTdb(trim($name));
      $like = $oDB->type==='pg' ? 'ILIKE' : 'LIKE';
      $where = "name $like '$name%'";
      break;
    default: die('getUser: invalid query');
  }
  $oDB->query( "SELECT id,name FROM TABUSER WHERE $where ORDER BY name" );
  $res = array();
  while ($row=$oDB->getRow())
  {
    $res[(int)$row['id']]=$row['name'];
    if ( --$max===0 ) break; // never breaks when $max starts at 0
  }
  return $res;
}

function validateFile(&$arrFile=[], $extensions='', $mimes='', int $size=0, int $width=0, int $height=0, bool $strictName=true)
{
  // For the uploaded document ($arrFile), this function returns [string] '' if it matches with all conditions
  // and an error message otherwize (and unlink the uploaded document)
  // $arrFile: The uploaded document ($_FILES['fieldname']).
  // $extensions: csv valid extensions. Empty to skip.
  // $mimes: csv mimetypes. Empty to skip
  // $size: Maximum file size (kb). 0 to skip.
  // $width: Maximum image width (pixels). 0 to skip.
  // $height: Maximum image width (pixels). 0 to skip.

  // Check arguments
  if ( is_array($extensions) ) $extensions = implode(',', $extensions);
  if ( is_array($mimes) ) $mimes = implode(',', $mimes);
  if ( !is_array($arrFile) || !is_string($extensions) || !is_string($mimes) ) die('CheckUpload: invalid argument type');

  // Check load
  if ( !is_uploaded_file($arrFile['tmp_name']) ) {
    unlink($arrFile['tmp_name']);
    return 'You did not upload a file!';
  }

  // Check size (kb)
  if ( $size>0 && $arrFile['size']>($size*1024+16) ) {
    unlink($arrFile['tmp_name']);
    return L('E_file_size').' (&lt;'.$size.' Kb)';
  }

  // check extension
  if ( !empty($extensions) ) {
    $result = validateFileExt($arrFile['name'], $extensions);
    if ( $result ) {
      unlink($arrFile['tmp_name']);
      return $result;
    }
  }

  // Check mimetype
  if ( !empty($mimes) && strpos(strtolower($mimes),strtolower($arrFile['type']))===false ) {
    unlink($arrFile['tmp_name']);
    return 'Format ['.$arrFile['type'].'] not supported... Use '.$extensions;
  }

  // Check size (pixels)
  if ( $width>0 || $height>0 ) {
    $size = getimagesize($arrFile['tmp_name']);
    if ( $width>0 && $size[0]>$width ) {
      unlink($arrFile['tmp_name']);
      return $width.'x'.$height.' '.L('e_pixels_max');
    }
    if ( $height>0 && $size[1]>$height ) {
      unlink($arrFile['tmp_name']);
      return $width.'x'.$height.' '.L('e_pixels_max');
    }
  }

  if ( $strictName ) {
    $arrFile['name'] = strtolower(QTdropaccent($arrFile['name']));
    $arrFile['name'] = preg_replace('/[^a-z0-9_\-\.]/i', '_', $arrFile['name']);
  }

  return '';
}

function validateFileExt($file, $extensions='')
{
  if ( is_array($extensions) ) $extensions = implode(',', $extensions);
  if ( !is_string($file) || empty($file) ) die('validateFileExt: argument #1 must be a string');
  if ( !is_string($extensions) || empty($extensions) ) die('validateFileExt: argument #2 must be a string');
  $file = strtolower($file);
  $extensions = strtolower($extensions);
  $ext = strrpos($file,'.'); if ( $ext===false ) return 'file extension not found';
  $ext = substr($file,$ext+1);
  if ( strpos($extensions,$ext)===false ) return 'Format ['.$ext.'] not supported... Use '.$extensions;
  return '';
}

function makePager(string $uri, int $count, int $intPagesize=50, int $currentpage=1, string $sep='', string $currentclass='current')
{
  // $sep (space) is inserted before each page-number
  if ( $currentpage<1 ) $currentpage=1;
  if ( $intPagesize<5 ) $intPagesize=50;
  if ( $count<2 || $count<=$intPagesize ) return ''; //...
  $arg = qtImplode(qtArradd(qtExplodeUri($uri),'page',null)); // extract query part and drop the 'page'-part (arguments remain urlencoded)
  $uri = parse_url($uri, PHP_URL_PATH); // redifine $uri as the path-part only
  $strPages='';
  $firstpage='';
  $lastpage='';
  $top = ceil($count/$intPagesize);
  $arrPages = array(1,2,3,4,5);
  if ( $currentpage>4 ) $arrPages = $currentpage==$top ? array($top-4,$top-3,$top-2,$top-1,$top) : array($currentpage-2,$currentpage-1,$currentpage,$currentpage+1,$currentpage+2);
  // pages
  foreach($arrPages as $page)
  {
    if ( $page>=1 && $page<=$top )
    {
      $first = $page==1 ? ' first' : '';
      $last = $page==$top ? ' last' : '';
      $strPages .= $sep.($currentpage===$page ? '<a class="page '.$currentclass.$first.$last.'" href="javascript:void(0)" tabindex="-1">'.$page.'</a>' : '<a class="page'.$first.$last.'" href="'.$uri.'?'.$arg.'&page='.$page.'">'.$page.'</a>');
    }
  }
  // extreme
  if ( $count>($intPagesize*5) )
  {
    if ( $arrPages[0]>1 ) $firstpage = $sep.'<a class="page first" href="'.$uri.'?'.$arg.'&page=1" title="'.L('First').'">&laquo;</a>';
    if ( $arrPages[4]<$top ) $lastpage = $sep.'<a class="page last" href="'.$uri.'?'.$arg.'&page='.$top.'" title="'.L('Last').': '.$top.'">&raquo;</a>';
  }
  return $firstpage.$strPages.$lastpage;
}

function QTcheckL($arr)
{
  if ( is_string($arr) ) $arr=explode(';',$arr);
  if ( !is_array($arr) ) die('QTcheckL: arg #1 be an array');
  foreach($arr as $str) if ( !isset($GLOBALS['_L'][$str]) ) $GLOBALS['_L'][$str] = SLang::get($str,QT_LANG,'*');
}

function array_prefix_keys($str,$arrSource)
{
  // add the prefix $str to the keys in an array.
  if ( empty($str) || !is_string($str) ) die('array_prefix_keys: arg #1 must be a string');
  if ( !is_array($arrSource) ) die('array_prefix_keys: arg #2 must be an array');
  $arr = array();
  foreach($arrSource as $key=>$value) $arr[$str.$key]=$value;
  return $arr;
}

function sqlLimit(string $state, string $order='id', int $start=0, int $length=50)
{
  if ( empty($order) ) die('sqlLimit: invalid argument'); // order is required with limit
  global $oDB;
  $order = trim($order); if ( strtolower(substr($order,-3,3))!='asc' && strtolower(substr($order,-4,4))!='desc' ) $order .= ' asc';
  switch($oDB->type)
  {
    case 'mysql':
    case 'pdo.mysql': return "SELECT $state ORDER BY $order LIMIT $start,$length"; break;
    case 'sqlsrv':
    case 'pdo.sqlsrv':
      if ($start==0 ) return "SELECT TOP $length $state ORDER BY $order";
      return "SELECT * FROM (SELECT ROW_NUMBER() OVER (ORDER BY $order) AS rownum, $state) AS orderrows WHERE rownum BETWEEN ".($start+1)." AND ".($start+$length)." ORDER BY rownum )"; break;
    case 'pdo.pg':
    case 'pg': return "SELECT $state ORDER BY $order LIMIT $length OFFSET $start"; break;
    case 'pdo.sqlite':
    case 'sqlite': return "SELECT $state ORDER BY $order LIMIT $length OFFSET $start"; break;
    case 'pdo.oci':
    case 'oci': return ($start==0 ? "SELECT * FROM (SELECT $state ORDER BY $order) WHERE ROWNUM<$length" : "SELECT * FROM (SELECT a.*, rownum rn FROM (SELECT $state ORDER BY $order) a WHERE rownum<$start+1+$length) WHERE rn>=$start"); break;
    default: return "SELECT $state ORDER BY $order LIMIT $start,$length"; break;
  }
}

function sqlFirstChar(string $field, string $case='u', int $len=1)
{
  // returns the whereclause of the $field's first-character(s) being:
  // 'u' uppercase, 'l' lowercase, '~' symbol/digit or '' unchanged (strick case)
  global $oDB;
  switch($oDB->type)
  {
    case 'pdo.sqlsrv':
    case 'sqlsrv':
      if ( $case==='u' ) return "UPPER(LEFT($field,$len))";
      if ( $case==='l' ) return "LOWER(LEFT($field,$len))";
      if ( $case==='~' ) return "(ASCII(UPPER(LEFT($field,1)))<65 OR ASCII(UPPER(LEFT($field,1)))>90)";
      if ( empty($case) ) return "LEFT($field,$len)";
      break;
    case 'pdo.pg':
    case 'pg':
      if ( $case==='u' ) return "UPPER(SUBSTRING($field FROM 1 FOR $len))";
      if ( $case==='l' ) return "LOWER(SUBSTRING($field FROM 1 FOR $len))";
      if ( $case==='~' ) return "UPPER($field) !~ '^[A-Z]'";
      if (empty($case) ) return "SUBSTRING($field FROM 1 FOR $len)";
      break;
    case 'pdo.sqlite':
    case 'sqlite':
      if ( $case==='u' ) return "UPPER(SUBSTR($field,1,$len))";
      if ( $case==='l' ) return "LOWER(SUBSTR($field,1,$len))";
      if ( $case==='~' ) return "(UPPER(SUBSTR($field,1,1))<'A' OR UPPER(SUBSTR($field,1,1))>'Z')";
      if ( empty($case) ) return "SUBSTR($field,1,$len)";
      break;
    case 'pdo.oci':
    case 'oci':
      if ( $case==='u' ) return "UPPER(SUBSTR($field,1,$len))";
      if ( $case==='l' ) return "LOWER(SUBSTR($field,1,$len))";
      if ( $case==='~' ) return "(ASCII(UPPER(SUBSTR($field,1,1)))<65 OR ASCII(UPPER(SUBSTR($field,1,1)))>90)";
      if ( empty($case) ) return "SUBSTR($field,1,$len)";
      break;
    default:
      if ( $case==='u' ) return "UPPER(LEFT($field,$len))";
      if ( $case==='l' ) return "LOWER(LEFT($field,$len))";
      if ( $case==='~' ) return "UPPER($field) NOT REGEXP '^[A-Z]'";
      if ( empty($case) ) return "LEFT($field,$len)";
      break;
  }
}

/**
 * @param string $date (or 'old')
 * @param string $field
 * @param number $length 8 for yyyymmdd, 4 for year only
 * @param string $oper operator
 * @param string $quote single or double quote (or empty in case of prepared statement)
 * @return string
 */
function sqlDateCondition(string $date='', string $field='firstpostdate', int $length=4, string $oper='=', string $quote="'")
{
  // Creates a where close for a date field. strDate can be an integer or the string 'old' (2 years or more)
  global $oDB;
  if ( $date==='old' ) { $oper = '<='; $date = Date('Y')-2; }
  switch($oDB->type)
  {
  case 'pdo.pg':
  case 'pg': return 'SUBSTRING('.$field.' FROM 1 FOR '.$length.')'.$oper.$quote.$date.$quote; break;
  case 'pdo.sqlite':
  case 'sqlite':
  case 'pdo.oci':
  case 'oci': return 'SUBSTR('.$field.',1,'.$length.')'.$oper.$quote.$date.$quote; break;
  default: return 'LEFT('.$field.','.$length.')'.$oper.$quote.$date.$quote;
  }
}
function GetStats($bClosed=false)
{
  // returns topics and replies per section id
  $arr = array('all'=>array('items'=>0,'replies'=>0,'itemsZ'=>0,'repliesZ'=>0));
  global $oDB;
  $oDB->query( 'SELECT s.id,count(t.id) as topics,sum(t.replies) as replies FROM TABSECTION s LEFT JOIN TABTOPIC t ON s.id=t.section GROUP BY s.id' );
  while($row=$oDB->getRow())
  {
    $i = (isset($row['items']) ? intval($row['items']) : 0);
    $arr[intval($row['id'])]['items']=$i; $arr['all']['items'] += $i;
    $i = (isset($row['replies']) ? intval($row['replies']) : 0);
    $arr[intval($row['id'])]['replies']=$i; $arr['all']['replies'] += $i;
  }
  if ( $bClosed )
  {
    $oDB->query( 'SELECT s.id,count(t.id) as topics,sum(t.replies) as replies FROM TABSECTION s LEFT JOIN TABTOPIC t ON s.id=t.section WHERE t.status="1" GROUP BY s.id' );
    while($row=$oDB->getRow())
    {
    $i = (isset($row['itemsZ']) ? intval($row['itemsZ']) : 0);
    $arr[intval($row['id'])]['itemsZ']=$i; $arr['all']['itemsZ'] += $i;
    $i = (isset($row['repliesZ']) ? intval($row['repliesZ']) : 0);
    $arr[intval($row['id'])]['repliesZ']=$i; $arr['all']['repliesZ'] += $i;
    }
  }
  return $arr;
}

function toCsv($str, string $quote='"',string $quoteAlt="'", string $sep=';', string $null='""')
{
  // Works recursively with an array
  // Note: null value becomes "" by default, boolean becomes 0 or 1
  // Note: Value can be null, string, bool, int or float (cannot be an object)
  if ( is_array($str) ) { $arr = []; foreach($str as $v) $arr[] = toCsv($v,$quote,$quoteAlt,$sep,$null); return implode($sep,$arr); }
  if ( is_int($str) || is_float($str) ) return $str;
  if ( $str==='' ) return $quote.$quote;
  if ( is_bool($str)) return (int)$str;
  if ( is_null($str) ) return $null;
  if ( !is_string($str) ) die('toCsv: invalid argument');
  $str = str_replace("\r\n",' ',$str);
  if ( strpos($str,'&')!==false ) {
    $str = str_replace('&nbsp;',' ',$str);
    $str = CDatabase::sqlDecode($str);
  }
  $str = str_replace($quote,$quoteAlt,$str);
  return $quote.$str.$quote;
}

function postsTodayAcceptable(int $intMax=100)
{
  if ( SUser::isStaff() || SUser::getInfo('numpost',0)<$intMax ) return true;
  // count if not yet defined
  if ( !isset($_SESSION[QT.'_usr']['posts_today']) )
  {
    global $oDB;
    $_SESSION[QT.'_usr']['posts_today'] = $oDB->count( "!!! WHERE userid=".SUser::id()." AND ".sqlDateCondition(date('Ymd'),'issuedate',8) );
  }
  if ( $_SESSION[QT.'_usr']['posts_today']===false || $_SESSION[QT.'_usr']['posts_today']<$intMax ) return true;
  return false;
}