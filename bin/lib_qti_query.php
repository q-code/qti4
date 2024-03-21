<?php // v4.0 build:20240210

/**
 * Returns a sql date condition selecting a timeframe
 * @param string $dbtype database type
 * @param string $tf timeframe {y|m|w|1..12|YYYY|YYYYMM|*}
 * @param string $prefix AND
 * @param string $field
 * @return string
 */
function getSqlTimeframe($dbtype,$tf='*',$prefix=' AND ',$field='t.firstpostdate') {
  if ( empty($tf) || $tf==='*' ) return ''; // no timeframe
  if ( !is_string($dbtype) || !is_string($tf) || !is_string($prefix) || !is_string($prefix) || empty($field) ) die(__FUNCTION__.' requires string arguments');
  // $tf can be {y|m|w|1..12|YYYY|YYYYMM} i.e. this year, this month, last week, previous month#, a specific year YYYY, a specific yearmonth YYYYMM
  $operator = '=';
  switch($tf)
  {
    case 'y':	// this year
      $strDate = date('Y');
      break;
    case 'm': // this month
      $strDate = date('Ym');
      break;
    case 'w':	// last week
      $operator = '>';
      $strDate = (string)date('Ymd', strtotime("-8 day", strtotime(date('Ymd'))));
      break;
    default: // $tf is the month number or a specific datemonth
      if ( !qtCtype_digit($tf) ) die(__FUNCTION__.' invalid tf argument');
      switch(strlen($tf))
      {
        case 1:
        case 2:
          $intMonth = (int)$tf;
          $intYear = (int)date('Y'); if ( $intMonth>date('n') ) --$intYear; // check if month from previous year
          $strDate = (string)($intYear*100+$intMonth);
          break;
        case 4:
          $strDate = $tf;
          break;
        case 6:
          $strDate = $tf;
          break;
        default: die(__FUNCTION__.' invalid tf argument');
      }
  }
  $len = strlen($strDate);
  switch($dbtype)
  {
    case 'pdo.pg':
    case 'pg': return $prefix . "SUBSTRING($field FROM 1 FOR $len) $operator '$strDate'"; break;
    case 'pdo.sqlite':
    case 'sqlite':
    case 'pdo.oci':
    case 'oci': return $prefix . "SUBSTR($field,1,$len) $operator '$strDate'"; break;
    default: return $prefix . "LEFT($field,$len) $operator '$strDate'";
  }
}

/**
 * Parse url arguments, urldecode and check contents
 * @param string $query urlencoded arguments string
 * @param boolean $trimV trim the search-text ($args['v'])
 * @return array or die if some arguments are missing or invalid
 */
function validateQueryArgs(array $arg, bool $trimV=true)
{
  // check
  if ( !isset($args['q']) ) $args['q']='s'; // if missing q, assume q=s
  if ( !empty($args['v']) && strpos($args['v'],'"')!==false ) $args['v'] = str_replace('"','',$args['v']);
  if ( !empty($args['w'])  && strpos($args['w'],'"')!==false ) $args['w'] = str_replace('"','',$args['w']);
  switch($args['q'])
  {
    case 's':
      if ( !isset($args['s']) || !is_numeric($args['s']) || $args['s']<0 ) $args['s'] = -1;
      if ( count($args)!==2 ) die(__FUNCTION__.' Invalid arguments'); // For section-query, only q and s can be used
      break;
    case 'ref':
    case 'kw':
    case 'qkw':
      if ( empty($args['v']) || strlen($args['v'])>64 ) die(__FUNCTION__.'Invalid argument v');
      if ( $trimV ) $args['v'] = trim($args['v']);
      break;
    case 'news': break;
    case 'insp': break;
    case 'user':
    case 'userm':
    case 'actor':
      // search using userid [w] (search [w] from [v] if missing)
      if ( empty($args['w']) && !empty($args['v']) ) { global $oDB; $args['w'] = SUser::getUserId($oDB,$args['v']); } // return false if not found)
      if ( empty($args['w']) || !is_numeric($args['w']) || $args['w']<0 ) die(__FUNCTION__.' Invalid argument w');
      break;
    case 'btw':
      if ( empty($args['v']) || empty($args['w']) || $args['v']<'19000101' || $args['w']>'21000101' ) die(__FUNCTION__.' Invalid argument dates');
      $args['v'] = qtDateClean($args['v'],8); // Returns YYYYMMDD (no time) while browser should provide YYYY-MM-DD. Returns '' if format not supported. If $v='now', returns today
      $args['w'] = qtDateClean($args['w'],8);
      if ( $args['v']>$args['w'] ) die(__FUNCTION__.' Invalid date (date1 > date2)');
      break;
    case 'adv':
      if ( !isset($args['st']) ) $args['st']='*'; // if missing st, assume status is all
      if ( !isset($args['w']) ) $args['w']='*'; // if missing w, assume time is all
      if ( empty($args['v']) && $args['w']==='*' && $args['st']==='*' ) die(__FUNCTION__.' Invalid argument date or tag');
      if ( strlen($args['v'])>128 ) die(__FUNCTION__.' Invalid argument tag');
      if ( strlen($args['w'])>2 ) die(__FUNCTION__.' Invalid argument date');
      if ( strlen($args['st'])>1 ) die(__FUNCTION__.' Invalid argument status');
      if ( $trimV ) $args['v'] = trim($args['v']);
      break;
    case 'last':
      if ( isset($args['v']) ) die(__FUNCTION__.' Invalid argument v'); // only filter arguments, no text argument
      break;
    default: die(__FUNCTION__.' Invalid query argument q');
  }
  // check injection
  if ( isset($args['s']) && $args['s']==='*' ) $args['s']='-1';
  if ( isset($args['s']) && !is_numeric($args['s']) ) die(__FUNCTION__.' Invalid argument s');
  if ( isset($args['w']) && ( strpos($args['w'],'"')!==false || strpos($args['w'],"'")!==false ) ) die(__FUNCTION__.' Invalid date');
  if ( isset($args['st']) && ( strpos($args['st'],'"')!==false || strpos($args['st'],"'")!==false ) ) die(__FUNCTION__.' Invalid status');

  return $args;
}
/**
 * Update sql statement parts (from,where,values,count-query) using the url arguments ($query)
 * @param string $sqlFrom
 * @param string $sqlWhere
 * @param string $sqlValues
 * @param string $sqlCount
 * @param string $sqlCountAlt
 * @param string $argFilters
 * @return string '' or a result warning (string parts are updated by reference)
 */
function sqlQueryParts(&$sqlFrom,&$sqlWhere,&$sqlValues,&$sqlCount,&$sqlCountAlt,string $argFilters)
{
  $args = []; parse_str($argFilters, $args); if ( count($args)===0 ) die(__FUNCTION__.' missing query argument');
  $args = validateQueryArgs($args);

  // Assgin query arguments or set to default
  $s = isset($args['s']) && is_numeric($args['s']) ? (int)$args['s'] : -1;
  $to = empty($args['to']) ? '0' : '1';
  $v  = isset($args['v']) ? $args['v'] : '';
  $w = isset($args['w']) ? $args['w'] : '';
  $tf = isset($args['tf']) ? $args['tf'] : '*';
  $st = isset($args['st']) ? $args['st'] : '*';
  $arrV = strlen(trim($v))===0 ? [] : array_unique(array_filter(array_map('trim',explode(';',mb_strtolower(str_replace("\r\n"," ",$v))))));

  // Prepare sql parts
  $sqlFrom = ' FROM TABTOPIC t INNER JOIN TABPOST p ON t.id=p.topic';
  $sqlWhere = ' WHERE t.section'.($s>=0 ? '='.$s : '>=0');
  // prevent searching in Admin sections while not staffmember
  if ( $s<0 && !SUser::isStaff() && isset($GLOBALS['_Sections']) )
  {
    $ad_Sections = [];
    foreach($GLOBALS['_Sections'] as $mId=>$mSec) if ( isset($mSec['type']) && $mSec['type']=='1' ) $ad_Sections[] = $mId;
    if ( !empty($ad_Sections) ) $sqlWhere = ' WHERE t.section NOT IN ('.implode(',',$ad_Sections).')';
  }
    // prevent user searching (other creator) in private section (can search announces)
    if ( $s>=0 && !SUser::isStaff() && isset($GLOBALS['_Sections'][$s]['type']) && $GLOBALS['_Sections'][$s]['type']=='2' ) {
      $sqlWhere .= " AND (t.firstpostuser=".SUser::id()." OR t.type='A')";
    }
    // status
  if ( $st!=='*' ) { $sqlWhere .= ' AND t.status=:status'; $sqlValues[':status'] = $st; }

  switch($args['q']) {

    case 'qkw':

      // support multiple qkw (arrV)
      // search in posts and in replies (no type condition)
      if ( count($arrV)>0 )
      {
        $sqlWhere .= ' AND p.type="P"';
        for($i=0;$i<count($arrV);$i++)
        {
          if ( is_numeric($arrV[$i]) )
          {
            $sqlValues[':numid'.$i] = $arrV[$i];
            $arrV[$i]='t.numid=:numid'.$i;
          }
          else
          {
            $sqlValues[':like'.$i] = '%'.strtoupper($arrV[$i]).'%';
            global $oDB;
            switch($oDB->type) {
              case 'pdo.sqlsrv':
              case 'sqlsrv': $arrV[$i] = 'UPPER(CAST(p.title AS VARCHAR(2000))) LIKE :like'.$i.(empty($to) ? ' OR UPPER(CAST(p.textmsg AS VARCHAR(2000))) LIKE :like'.$i : ''); break;
              default:       $arrV[$i] = 'UPPER(p.title) LIKE :like'.$i.(empty($to) ? ' OR UPPER(p.textmsg) LIKE :like'.$i : ''); break;
            }
          }
        }
        $sqlWhere .= ' AND ('.implode(' OR ',$arrV).')';
      }
      $sqlCount = 'SELECT count(*) as countid'.$sqlFrom.$sqlWhere;
      break;

    case 'ref':

      // support multiple ref (arrV)
      // search in posts (not replies) and only ref sections
      $refSections = [];
      foreach($GLOBALS['_Sections'] as $mSec) if ( $mSec['numfield']!=='N' ) $refSections[] = $mSec['id'];
      if ( empty($refSections) ) $refSections = [0];
      $refSections = implode(',',$refSections);

      $sqlWhere .= " AND t.forum IN ($refSections) AND p.type='P'";
      if ( count($arrV)>0 )
      {
        for($i=0;$i<count($arrV);$i++) {
          $sqlValues[':numid'.$i] = $arrV[$i];
          $arrV[$i]='t.numid=:numid'.$i;
        }
        $sqlWhere .= " AND (".implode(' OR ',$arrV).")";
      }
      $sqlCount = "SELECT count(*) as countid $sqlFrom $sqlWhere";
      break;

    case 'kw':

      // support multiple kw (arrV)
      // search in posts and in replies (no type condition)
      global $oDB;
      for($i=0;$i<count($arrV);$i++)
      {
        $sqlValues[':like'.$i] = '%'.strtoupper($arrV[$i]).'%';
        switch($oDB->type) {
          case 'pdo.sqlsrv':
          case 'sqlsrv': $arrV[$i] = 'UPPER(CAST(p.title AS VARCHAR(2000))) LIKE :like'.$i.(empty($to) ? ' OR UPPER(CAST(p.textmsg AS VARCHAR(2000))) LIKE :like'.$i : ''); break;
          default:       $arrV[$i] = 'UPPER(p.title) LIKE :like'.$i.(empty($to) ? ' OR UPPER(p.textmsg) LIKE :like'.$i : ''); break;
        }
      }
      $sqlWhere .= ' AND ('.implode(' OR ',$arrV).')';
      $sqlCount = 'SELECT count(*) as countid'.$sqlFrom.$sqlWhere;
      break;

    case 'last':

      // get the lastpost date
      // search in posts (not replies)
      global $oDB;
      $oDB->query( 'SELECT max(p.issuedate) as f1 FROM TABPOST p ');
      $row = $oDB->getRow();
      if ( empty($row['f1']) ) $row['f1'] = date('Ymd');
      $sqlValues[':lastdate'] = substr(addDate($row['f1'],-8,'day'), 0, 8);
      // query post of this day
      $sqlWhere .= " AND p.type='P' AND " . sqlDateCondition(':lastdate','p.issuedate',8,'>','');
      $sqlCount = 'SELECT count(*) as countid'.$sqlFrom.$sqlWhere;
      break;

    case 'user':
    case 'userm':
    case 'actor':

      if ( $args['q']!=='userm') $sqlWhere .= " AND p.type='P'";
      $sqlWhere .= " AND p.userid=$w";
      $sqlCount  = "SELECT count(*) as countid $sqlFrom $sqlWhere"; // count all messages
      $sqlCountAlt = "SELECT count(*) as countid FROM TABTOPIC WHERE firstpostuser=$w"; // count topic only
      break;

    case 'btw':

      global $oDB;
      $sqlValues[':postdate_a'] = $v;
      $sqlValues[':postdate_b'] = $w;
      $sqlWhere .= sqlDateCondition(':postdate_a','t.firstpostdate',8,'>=','').' AND '.sqlDateCondition(':postdate_b','t.firstpostdate',8,'<=','');
      $sqlWhere  .= " AND p.type='P' AND ";
      switch($oDB->type)
      {
        case 'pdo.pg':
        case 'pg': $sqlWhere .= '(SUBSTRING(t.firstpostdate FROM 1 FOR 8)>=:postdate_a AND SUBSTRING(t.firstpostdate FROM 1 FOR 8)<=:postdate_b)'; break;
        case 'pdo.sqlite':
        case 'sqlite':
        case 'pdo.oci':
        case 'oci': $sqlWhere .= '(SUBSTR(t.firstpostdate,1,8)>=:postdate_a AND SUBSTR(t.firstpostdate,1,8)<=:postdate_b)'; break;
        default: $sqlWhere .= '(LEFT(t.firstpostdate,8)>=:postdate_a AND LEFT(t.firstpostdate,8)<=:postdate_b)';
      }
      $sqlCount = 'SELECT count(*) as countid'.$sqlFrom.$sqlWhere;
      break;

    case 'adv':

      global $oDB;
      // Timeframe
      $sqlWhere .= getSqlTimeframe($oDB->type,$tf);

      // Only Topics
      $sqlWhere .= " AND p.type='P'";

      // Topics Tags
      if ( count($arrV)>0 )
      {
        for($i=0;$i<count($arrV);++$i)
        {
          $sqlValues[':like'.$i] = '%'.strtoupper($arrV[$i]).'%';
          switch($oDB->type)
          {
            case 'sqlsrv':
            case 'pdo.sqlsrv':$arrV[$i] = 'UPPER(CAST(t.tags AS VARCHAR(2000))) LIKE :like'.$i; break;
            default:     $arrV[$i] = 'UPPER(t.tags) LIKE :like'.$i; break;
          }
        }
        $sqlWhere .= ' AND ('.implode(' OR ',$arrV).')';
      }

      $sqlCount = 'SELECT count(*) as countid'.$sqlFrom.$sqlWhere;
      break;

    case 'news':
      $sqlWhere .= " AND t.type='A' AND p.type='P'";
      $sqlCount = 'SELECT count(*) as countid'.$sqlFrom.$sqlWhere;
      break;

    case 'insp':
      $sqlWhere .= " AND t.type='I'AND p.type='P'";
      $sqlCount = 'SELECT count(*) as countid'.$sqlFrom.$sqlWhere;
      break;
  }

  return '';
}