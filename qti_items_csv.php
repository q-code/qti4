<?php // v4.0 build:20230205

session_start();
require 'bin/init.php';
/**
 * @var CHtml $oH
 * @var CDatabase $oDB
 * @var string $bt
 * @var string $ft
 */
$oH->selfurl = 'qti_items_csv.php';
if ( !SUser::canView('V2') ) exitPage(11,'user-lock.svg'); //...

// ---------
// INITIALISE
// ---------

$strOrder = 'lastpostdate';
$strDirec = 'desc';
if ( isset($_GET['order']) ) $strOrder = $_GET['order'];
if ( isset($_GET['dir']) ) $strDirec = strtolower(substr($_GET['dir'],0,4));

$size = ( isset($_GET['size']) ? strip_tags($_GET['size']) : 'all');
$intCount = (int)$_GET['n'];
$intLimit = 0;
$intLen = (int)$_SESSION[QT]['items_per_page'];

// Check arguments

if ( empty($size) || $intCount <= $intLen ) $size='all';
if ( strlen($size)>6 ) die('Invalid argument');
if ( substr($size,0,1)!='p' && substr($size,0,1)!='m' && $size!=='all') die('Invalid argument');
if ( substr($size,0,1)=='p' )
{
  $i = (int)substr($size,1);
  if ( empty($i) || $i<0 ) die('Invalid argument');
  if ( ($i-1) > $intCount/$intLen ) die('Invalid argument');
}
if ( substr($size,0,1)=='m' )
{
  if ( $size!='m1' && $size!='m2' && $size!='m5' && $size!='m10' ) die('Invalid argument');
}
if ( $intCount>1000 && $size=='all' ) die('Invalid argument');
if ( $intCount<=1000 && substr($size,0,1)=='m' ) die('Invalid argument');
if ( $intCount>1000 && substr($size,0,1)=='p' ) die('Invalid argument');

// Uri arguments

$q = 's';   // in case of search, query type
$s = '*';  // section filter can be '*' or [int]
$fs = '*';  // section filter ($fs will become $s if provided)
$ft = '*';  // type (of filter). Can be urlencoded
$fst = '*';  // status can be '*' or [int]
$v = '';  // term/tag searched

// User's preferences (stored as coockies)

$u_fst='*';
$u_dir='asc';
if ( isset($_COOKIE[QT.'_u_fst']) ) $u_fst=$_COOKIE[QT.'_u_fst'];
if ( isset($_COOKIE[QT.'_u_dir']) ) $u_dir=$_COOKIE[QT.'_u_dir'];

$fst=$u_fst;// filter by status
$dir=$u_dir;// id order ('asc'|'desc')

// Read Uri arguments

qtHttp('q s fs ft fst v');
if ( $fs==='' ) $fs='*';
if ( $s==='' || $s<0 ) $s='*';
if ( $fst==='' ) $fst='*';
if ( $fs!=='*' ) $s=(int)$fs; // $fs becomes $s in this page
if ( $s!=='*' ) $s=(int)$s;
if ( $fst!=='*' ) $fst=(int)$fst;
if ( !empty($q) ) $fst='*'; // status user preference is not applied in case of search results

// Section (can be an empty section in case of search result)
$oS = new CSection($s==='*' ? null : $s);
if ( $q=='last' || $q=='user' ) { $strOrder='issuedate'; $dir='desc'; }
if ( isset($_GET['order']) ) $strOrder = $_GET['order'];
if ( isset($_GET['dir']) ) $strDirec = $_GET['dir'];
if ( !isset($_SESSION[QT]['lastcolumn']) || $_SESSION[QT]['lastcolumn']=='none' ) $_SESSION[QT]['lastcolumn'] = 'default';
$strLastcol = $oS->getMF('options','last'); if  ($strLastcol=='N' || strtolower($strLastcol)=='none' ) $strLastcol='0';
$strDirec = strtolower($dir);
$csv = '';

// apply argument
if ( $size=='all') { $intLimit=0; $intLen=$intCount; }
if ( $size=='m1' ) { $intLimit=0; $intLen=999; }
if ( $size=='m2' ) { $intLimit=1000; $intLen=1000; }
if ( $size=='m5' ) { $intLimit=0; $intLen=4999; }
if ( $size=='m10') { $intLimit=5000; $intLen=5000; }
if ( substr($size,0,1)=='p' ) { $i = (int)substr($size,1); $intLimit = ($i-1)*$intLen; }

// -----
// QUERY parts definition
// -----

$sqlFields = ($_SESSION[QT]['news_on_top'] ? "CASE WHEN t.type='A' AND t.status='A' THEN 'A' ELSE 'Z' END as typea," : '');
$sqlFields .= 't.*,p.title,p.icon,p.id as postid,p.type as posttype,p.textmsg,p.issuedate,p.username';
$sqlFrom = ' FROM TABTOPIC t INNER JOIN TABPOST p ON t.firstpostid=p.id';
$sqlWhere = ' WHERE t.forum'.($q==='s' ? '='.$s : '>=0');
  // In private section, show topics created by user himself
  if ( $q==='s' && $oS->type==='2' && !SUser::isStaff()) $sqlWhere .= " AND (t.firstpostuser=".SUser::id()." OR (t.type='A' AND t.status='A'))";
$sqlValues = array(); // list of values for the prepared-statements
$sqlCount = 'SELECT count(*) as countid FROM TABTOPIC t'.$sqlWhere;
$sqlCountAlt='';
if ( $q!=='s' ) {
  include'bin/lib_qti_query.php';
  $error = sqlQueryParts($sqlFrom,$sqlWhere,$sqlValues,$sqlCount,$sqlCountAlt,$oH->selfuri); //selfuri is not urldecoded
  if ( !empty($error) ) die($error);
  if ( $q==='adv' && !empty($v) ) $strLastcol = 'tags'; // forces display column tags
}

// Option to hide closed items
if ( $_SESSION[QT]['show_closed']=='0' ) $sqlWhere.=" AND t.status<>'1'";

// Count topics visible for current user ONLY
$intCount = $oDB->count($sqlCount);

// --------
// OUPUT
// --------

$t = new TabTable();
$t->arrTh['type'] = new TabHead(L('Type'));
$t->arrTh['numid'] = new TabHead(L('Ref'));
$t->arrTh['title'] = new TabHead(L('Item'));
if ( !empty($q) && $s<0 ) $t->arrTh['sectiontitle'] = new TabHead(L('Section'));
$t->arrTh['firstpostname'] = new TabHead(L('Author'));
$t->arrTh['firstpostdate'] = new TabHead(L('First_message'));
$t->arrTh['lastpostdate'] = new TabHead(L('Last_message'));
$t->arrTh['replies'] = new TabHead(L('Reply+'));
if ( !empty($strLastcol) ) $t->arrTh[$strLastcol] = new TabHead(L(ucfirst($strLastcol)));

$csv = toCsv($t->getTHnames()).PHP_EOL;

// ========
$sqlFullOrder = $strOrder==='title' ? 'p.title' : 't.'.$strOrder;
$oDB->query( sqlLimit($sqlFields.$sqlFrom.$sqlWhere,($_SESSION[QT]['news_on_top'] ? 'typea ASC, ' : '').$sqlFullOrder.' '.strtoupper($strDirec),$intLimit,$_SESSION[QT]['items_per_page'],$intCount) );
// ========
$intWhile=0;
while($row=$oDB->getRow())
{
  $csv .= formatCsvRow($t->getTHnames(),$row,$oS).PHP_EOL;
  $intWhile++; if ( $intWhile>=$intCount ) break;//odbcbreak
}
// ========

if ( isset($_GET['debug']) ) { echo $csv; exit; }

// Header sould not have been sent yet. Define a download header. Otherwise file or messages are displayed as a new html page.
if ( !headers_sent() )
{
  header('Content-Type: text/csv; charset='.QT_HTML_CHAR);
  header('Content-Disposition: attachment; filename="'.APP.'_'.date('YmdHi').'.csv"');
}
echo $csv;