<?php // v4.0 build:20230205

function sectionsAsOption($selected='', array $reject=[], array $disabled=[], string $optionAll='', int $textsize=32, int $max=100, string $prefixValue='')
{
  if ( !is_int($selected) && !is_string($selected) ) throw new Exception('invalid argument');
  $selected = (string)$selected;
  // Attention $selected can be [int] or [string] ('*' when $optionAll is used).
  // If $optionAll is not empty, the list includes in first position an 'all' option having the value '*' and the label $optionAll.
  // To remove some section(s) from this list, use $reject and provide an array of id's [int]. Providing one id [int] is also possible.
  $arrDS = [];
  $countS = 0;
  foreach(array_keys($GLOBALS['_Domains']) as $mDid) {
  foreach($GLOBALS['_Sections'] as $mId=>$mSec) {
      if ( $mSec['pid']!==$mDid || in_array($mId,$reject) || ($mSec['type']=='1' && !SUser::isStaff()) ) continue; // Skip rejected or hidden
      $arrDS[$mDid][$mId] = SLang::translate('sec', 's'.$mId, $mSec['title']);
      ++$countS;
  }}
  // render as options
  $optgroup = $countS>2 && count($arrDS)>1;
  $str = ''; if ( !empty($optionAll) ) $str ='<option value="*"'.($selected==='*' ? ' selected' : '').(in_array('*',$disabled,true) ? ' disabled': '').'>'.QTtrunc($optionAll,$textsize).'</option>';
  foreach($arrDS as $domId=>$arrS) {
    if ( $optgroup ) $str .= '<optgroup label="'.QTtrunc( SLang::translate('domain', 'd'.$domId, $GLOBALS['_Domains'][$domId]['title']), $textsize ).'">';
    foreach($arrS as $id=>$name) {
      $str .= '<option value="'.$prefixValue.$id.'"'.((string)$id===$selected ? ' selected' : '').(in_array($id,$disabled,true) ? ' disabled': '').'>'.QTtrunc($name,$textsize).'</option>';
      if ( --$max<1 ) break;
    }
    if ( $optgroup ) $str .= '</optgroup>';
  }
  return $str;
}

function bbcButtons($size=1)
{
  if ( !QT_BBC || $size==0 ) return '';
  $str = '<a class="bbc" onclick="qtCaret(`b`)" title="'.L('Bbc.bold').'">'.getSVG('bold').'</a>';
  $str .= '<a class="bbc" onclick="qtCaret(`i`)" title="'.L('Bbc.italic').'">'.getSVG('italic').'</a>';
  $str .= '<a class="bbc" onclick="qtCaret(`u`)" title="'.L('Bbc.under').'">'.getSVG('underline').'</a>';
  $str .= '<a class="bbc" onclick="qtCaret(`quote`)" title="'.L('Bbc.quote').'">'.getSVG('quote-right').'</a>';
  if ( $size>1 )
  {
  $str .= '<a class="bbc" onclick="qtCaret(`code`)" title="'.L('Bbc.code').'">'.getSVG('code').'</a>';
  $str .= '<a class="bbc" onclick="qtCaret(`url`)" title="'.L('Bbc.url').'">'.getSVG('link').'</a>';
  $str .= '<a class="bbc" onclick="qtCaret(`mail`)" title="'.L('Bbc.mail').'">'.getSVG('envelope').'</a>';
  }
  if ( $size>2 ) $str .= '<a class="bbc" onclick="qtCaret(`img`)" title="'.L('Bbc.image').'">'.getSVG('image').'</a>';
  return $str;
}

function icoPrefix(string $serie, int $i, string $src='config/prefix/')
{
  if ( file_exists($src.'serie-'.$serie.'.php') ) {
    include $src.'serie-'.$serie.'.php';
    if ( isset($prefixIcon[$i]) ) {
      if ( substr($prefixIcon[$i],0,3)==='fa ' ) return '<i class="prefix_icon '.$prefixIcon[$i].'" title="'.L('PrefixIcon.'.$serie.'0'.$i).'"'.(isset($prefixStyle[$i]) ? ' style="'.$prefixStyle[$i].'"' : '').'></i>';
      return '<img class="prefix_icon" src="'.$src.$prefixIcon[$i].'" alt="'.$i.'" title="'.L('PrefixIcon.'.$serie.'0'.$i).'"'.(isset($prefixStyle[$i]) ? ' style="'.$prefixStyle[$i].'"' : '').'/>';
    }
  }
}

function exitPage($content='Page not defined', string $title='!', bool $hideMenuLang=true)
{
  if ( !is_string($content) && !is_int($content) ) die('exitPage: invalid argument');
  // title can be one svg icon (if ends with '.svg')
  if ( substr($title,-4)==='.svg' ) $title = getSVG(substr($title,0,-4));
  global $oH;
  $oH->exiturl = APP.'_index.php';
  include APP.'_inc_hd.php'; // uses $hideMenuLang (true by default for error/exit pages)
  CHtml::msgBox($title, 'class=msgbox');
  if ( is_int($content) ) {
    if ( $content===99 ) {
      $content = translate('app_offline.txt',false);
      if ( file_exists($content) ) { include $content; } else { echo L('E_99'); }
    } else {
      echo L('E_'.$content);
      if ( !SUser::auth() ) echo '<p><a href="'.APP.'_login.php">'.L('Login').'...</a></p>';
    }
  } else {
    echo $content;
  }
  CHtml::msgBox('/');
  include APP.'_inc_ft.php';
  exit; //...
}

function getCheckedIds($checkbox='t1-cb',$altCsv='')
{
  if ( isset($_POST[$checkbox]) )
  {
    $arrId = is_array($_POST[$checkbox]) ? $_POST[$checkbox] : explode(',',$_POST[$checkbox]);
    return array_map('intval', $arrId);
  }
  if ( is_string($altCsv) && !empty($altCsv) )
  {
    return array_map('intval', explode(',',$altCsv));
  }
  return array();
}

function htmlLettres(string $baseFile, string $current='ALL', string $strAll='All', string $strClass='lettres', string $strTitle='Username starting with ', int $intSize=1, bool $bFilterForm=true)
{
  // When $baseFile have other arguments, group argument will be appended
  // $current is the current group, $strAll is the label of the 'ALL' group
  // Note: $baseFile can be urlrewrited
  $current = strtoupper($current);
  $and = strpos($baseFile,'?') ? '&' : '?';
  switch($intSize) {
  case 1: $arr = explode('.','A.B.C.D.E.F.G.H.I.J.K.L.M.N.O.P.Q.R.S.T.U.V.W.X.Y.Z.~'); break;
  case 2: $arr = explode('.','A|B.C|D.E|F.G|H.I|J.K|L.M|N.O|P.Q|R.S|T.U|V.W|X.Y|Z.~'); break;
  case 3: $arr = explode('.','A|B|C.D|E|F.G|H|I.J|K|L.M|N|O.P|Q|R.S|T|U.V|W.X|Y|Z.~'); break;
  case 4: $arr = explode('.','A|B|C|D.E|F|G|H.I|J|K|L.M|N|O|P.Q|R|S|T.U|V|W.X|Y|Z.~'); break;
  }
  $str = '<a '.($current==='ALL' ? ' class="active"' : '').' href="'.($current==='ALL' ? 'javascript:void(0)' : $baseFile.$and.'group=all').'">'.$strAll.'</a>';
  foreach($arr as $g) {
    $title = $strTitle.($g==='~' ? L('other_char') : str_replace('|',' '.L('or').' ',$g));
    $str .= '<a'.($current===$g ? ' class="active"' : '').' href="'.($current===$g ? 'javascript:void(0)' : $baseFile.$and.'group='.$g).'"'.(empty($title) ? '' : ' title="'.qtAttr($title).'"').'>'.str_replace('|','',$g).'</a>';
  }
  $strGroups  = '<div class="'.$strClass.'">';
  $strGroups .= L('Show').' '.$str;
  if ( $bFilterForm ) {
  $strGroups .= ' <form method="get" action="'.$baseFile.'">';
  $strGroups .= '<input required type="text" value="'.($current==='ALL' || in_array($current,$arr) ? '' : qtAttr($current)).'" name="group" size="3" maxlength="10" title="'.qtAttr($strTitle).'"/>';
  $strGroups .= '<button type="submit" value="submit">'.getSVG('search').'</button>';
  $strGroups .=  asTags(qtExplodeUri($baseFile),'','tag=hidden','',[],'urldecode',['page','group']);
  $strGroups .= '</form>';
  }
  $strGroups .= '</div>';
  return $strGroups;
}

function htmlCsvLink($strUrl,$intCount=20,$intPage=1)
{
  if ( empty($strUrl) ) return '';
  if ( $intCount<=$_SESSION[QT]['items_per_page'] )
  {
  return '<a class="csv" href="'.$strUrl.'&size=all&n='.$intCount.'" title="'.L('H_Csv').'">'.L('Csv').'</a>';
  }
  else
  {
  $strCsv = '<a class="csv" href="'.$strUrl.'&size=p'.$intPage.'&n='.$intCount.'" title="'.L('H_Csv').'">'.L('Csv').' <span class="csvpages">('.strtolower(L('Page')).')</span></a>';
  if ( $intCount<=1000 )                   $strCsv .= ' &middot; <a class="csv" href="'.$strUrl.'&size=all&n='.$intCount.'" title="'.L('H_Csv').'">'.L('Csv').' <span class="csvpages">('.strtolower(L('All')).')</span></a>';
  if ( $intCount>1000 && $intCount<=2000 ) $strCsv .= ' &middot; <a class="csv" href="'.$strUrl.'&size=m1&n='.$intCount.'" title="'.L('H_Csv').'">'.L('Csv').' <span class="csvpages">(1-1000)</span></a> &middot; <a href="'.$strUrl.'&size=m2&n='.$intCount.'" class="csv" title="'.L('H_Csv').'">'.L('Csv').' <span class="csvpages">(1000-'.$intCount.')</span></a>';
  if ( $intCount>2000 && $intCount<=5000 ) $strCsv .= ' &middot; <a class="csv" href="'.$strUrl.'&size=m5&n='.$intCount.'" title="'.L('H_Csv').'">'.L('Csv').' <span class="csvpages">(1-5000)</span></a>';
  if ( $intCount>5000 )                    $strCsv .= ' &middot; <a class="csv" href="'.$strUrl.'&size=m5&n='.$intCount.'" title="'.L('H_Csv').'">'.L('Csv').' <span class="csvpages">(1-5000)</span></a> &middot; <a href="'.$strUrl.'&size=m10&n='.$intCount.'" class="csv" title="'.L('H_Csv').'">'.L('Csv').' <span class="csvpages">(5000-10000)</span></a>';
  }
  return $strCsv;
}

function htmlScore(string $level='3', string $sep='<br>', float $i=-1)
{

switch($level)
{
case '3':
return '
<p id="input-choise">
<input type="radio" name="inspection" id="i00" value="0"'.(QTisbetween($i,0,33.32) ? ' checked' : '').'/><label for="i00">'.ValueScalebar(1,3).' '.ValueName(1,3).'</label>'.$sep.'
<input type="radio" name="inspection" id="i50" value="50"'.(QTisbetween($i,33.33,66.66) ? ' checked' : '').'/><label for="i50">'.ValueScalebar(50,3).' '.ValueName(50,3).'</label>'.$sep.'
<input type="radio" name="inspection" id="i99" value="99"'.(QTisbetween($i,66.66) ? ' checked' : '').'/><label for="i99">'.ValueScalebar(99,3).' '.ValueName(99,3).'</label>'.$sep.'
<input type="radio" name="inspection" id="inull" value="null"'.($i<0 ? ' checked' : '').'/><label for="inull">'.L('Unknown').'</label>
</p>
';
break;
case '5':
return '
<p id="input-choise">
<input type="radio" name="inspection" id="i00" value="0"'.(QTisbetween($i,0,10) ? ' checked' : '').'/><label for="i00">'.ValueScalebar(1,5).' '.ValueName(1,5).'</label><br>
<input type="radio" name="inspection" id="i20" value="20"'.(QTisbetween($i,10.01,30) ? ' checked' : '').'/><label for="i20">'.ValueScalebar(25,5).' '.ValueName(25,5).'</label>'.$sep.'
<input type="radio" name="inspection" id="i40" value="40"'.(QTisbetween($i,30.01,50) ? ' checked' : '').'/><label for="i40">'.ValueScalebar(50,5).' '.ValueName(50,5).'</label>'.$sep.'
<input type="radio" name="inspection" id="i60" value="60"'.(QTisbetween($i,50.01,70) ? ' checked' : '').'/><label for="i60">'.ValueScalebar(75,5).' '.ValueName(75,5).'</label><br>
<input type="radio" name="inspection" id="i99" value="99"'.(QTisbetween($i,70.01) ? ' checked' : '').'/><label for="i99">'.ValueScalebar(99,5).' '.ValueName(99,5).'</label><br>
<input type="radio" name="inspection" id="inull" value="null"'.($i<0 ? ' checked' : '').'/><label for="inull">'.L('Unknown').'</label>
</p>
';
break;
case '100':
return '
<p id="input-choise">
<input type="radio" name="inspection" id="i00" value="pc"'.($i>=0 ? ' checked' : '').'/><input type="number" name="inspectionvalue" id="inspectionvalue" value="'.($i<0 ? '50' : $i).'" size="3" min="0" max="100" onfocus="document.getElementById(\'i00\').checked=true;" style="max-width:50px"/><label for="i00">%</label>'.$sep.'
<input type="radio" name="inspection" id="inull" value="null"'.($i<0 ? ' checked' : '').'/><label for="inull">'.L('Unknown').'</label>
</p>
';
break;
case '2':
return '
<p id="input-choise">
<input type="radio" name="inspection" id="i00" value="0"'.(QTisbetween($i,0,49.99) ? ' checked' : '').'/><label for="i00">'.ValueScalebar(1,2).' '.ValueName(1,2).'</label>'.$sep.'
<input type="radio" name="inspection" id="i99" value="99"'.(QTisbetween($i,50) ? ' checked' : '').'/><label for="i99">'.ValueScalebar(99,2).' '.ValueName(99,2).'</label>'.$sep.'
<input type="radio" name="inspection" id="inull" value="null"'.($i<0 ? ' checked' : '').'/><label for="inull">'.L('Unknown').'</label>
</p>
';
break;
}

}

function formatCsvRow($arrFLD,$row,$arrSEC=array())
{
  if ( is_a($row,'CTopic') ) $row = get_object_vars($row);
  if ( !is_array($row) ) die('formatCsvRow: Wrong argument #3');
  if ( !is_array($arrSEC) ) die('formatCsvRow: Wrong argument $arrSEC'); // In case of item data row $arrSEC is required. In case of user data rows $arrSEC can by empty.
  $s = isset($row['section']) ? (int)$row['section'] : -1; // section index
  if ( !isset($row['type']) ) $row['type']='T';
  if ( !isset($row['status']) ) $row['status']='A';
  if ( isset($arrFLD['numid']) && isset($arrSEC[$s]) ) $row['s.numfield'] = $arrSEC[$s]['numfield'];

  // Process
  $arrValues = array();
  foreach(array_keys($arrFLD) as $strKey)
  {
    $str='';
    switch($strKey)
    {
      case 'id': $str = (int)$row['id']; break;
      case 'numid': $str = CTopic::getRef( (int)$row['numid'], (empty($row['s.numfield']) ? '' : $row['s.numfield']) ); break;
      case 'status':  $str = CTopic::makeIconName($row['type'],$row['status']); break;
      case 'text':    $str = $row['preview']; break;
      case 'section': $str = SLang::translate('sec','s'.$row['section']); break;
      case 'insertdate': $str = QTdatestr($row['insertdate'],'$',''); break;
      case 'posts': $str = (int)$row['posts']; break;
      case 'coord':
        if ( isset($row['y']) && isset($row['x']) )
        {
          $y = floatval($row['y']);
          $x = floatval($row['x']);
          if ( !empty($y) && !empty($x) )  $str = str_replace('&#176;','?',QTdd2dms($y).','.QTdd2dms($x));
        }
        break;
      case 'tags':
        $arrTags = ( empty($row['tags']) ? array() : explode(';',$row['tags']) );
        foreach (array_keys($arrTags) as $i) if ( empty($arrTags[$i]) ) unset($arrTags[$i]);
        if ( count($arrTags)>5 )
        {
          $arrTags = array_slice($arrTags,0,5);
          $arrTags[]='...';
        }
        $str = implode(' ',$arrTags);
        break;
      case 'user.id': $str = (int)$row['id']; break;
      case 'user.name': $str = $row['name']; break;
      case 'user.role': $str = $row['role']; break;
      case 'user.contact': $str = (isset($row['mail']) ? $row['mail'].' ' : '').(isset($row['www']) ? $row['www'] : ''); break;
      case 'user.location': $str = $row['location']; break;
      case 'user.name': $str = $row['name']; break;
      case 'user.notes': $str = (int)$row['notes']; break;
      case 'user.firstdate': $str = QTdatestr($row['firstdate'],'Y-m-d',''); break;
      case 'user.lastdate': $str = QTdatestr($row['lastdate'],'Y-m-d','').(empty($row['ip']) ?  '&nbsp;' : ' ('.$row['ip'].')'); break;
      default: if ( isset($row[$strKey]) ) $str = $row[$strKey]; break;
    }
    $arrValues[] = toCsv($str);
  }
  return implode(';',$arrValues);
}

function renderUserMailSymbol($row)
{
  // required $row['id|privacy|mail']
  if ( empty($row['mail']) || empty($row['id']) || !isset($row['privacy']) )
  return '<span class="disabled" title="no e-mail"><svg class="svg-symbol"><use href="#symbol-envelope" xlink:href="#symbol-envelope"></use></svg></span>';
  $str = '';
  if ( (int)$row['privacy']===2 ) $str = asEmails($row['mail'],'symbol'.(QT_JAVA_MAIL ? 'java' : ''));
  if ( (int)$row['privacy']===1 && SUser::role()!='V' ) $str = asEmails($row['mail'],'symbol'.(QT_JAVA_MAIL ? 'java' : ''));
  if ( SUser::id()==$row['id'] || SUser::isStaff() ) $str = asEmails($row['mail'],'symbol'.(QT_JAVA_MAIL ? 'java' : ''));
  return $str;
}

function renderUserWwwSymbol($row)
{
  if ( empty($row['www']) || !isset($row['privacy']) )
  return '<span class="disabled" title="no web site"><svg class="svg-symbol svg-125"><use href="#symbol-home" xlink:href="#symbol-home"></use></svg></span>';
  return '<a href="'.$row['www'].'" title="web site"><svg class="svg-symbol svg-125"><use href="#symbol-home" xlink:href="#symbol-home"></use></svg></a>';
}

function renderUserPrivSymbol(array $row=[], string $empty='')
{
  // required $row['id|privacy']
  if ( empty($row['id']) || !isset($row['privacy']) ) return $empty;
  if ( SUser::isStaff() || SUser::id()===(int)$row['id'] ) {
    if ( (int)$row['privacy']===2 )
    return '<span data-private="2" title="'.L('Privacy_visible_2').'"><svg class="svg-symbol svg-125"><use href="#symbol-door-open" xlink:href="#symbol-door-open"></use></svg></span>';
    return '<span data-private="'.$row['privacy'].'" title="'.L('Privacy_visible_'.$row['privacy']).'"><svg class="svg-symbol"><use href="#symbol-key" xlink:href="#symbol-key"></use></svg></span>';
  }
  return $empty;
}

function formatItemRow(string $strTableId='t1',array $arrFLD=[], $row, $oS, array $arrOptions=[])
{
  if ( is_a($row,'CTopic') ) {$row=get_object_vars($row); $row['section']=$row['parentid'];}
  if ( !isset($row['id']) ) die( __FUNCTION__.' Missing id in $row');
  if ( !isset($row['replies']) ) $row['replies']=0;
  if ( isset($row['type']) ) $row['type'] = strtoupper($row['type']);
  if ( isset($arrFLD['numid']) && !isset($row['numid']) ) $row['numid'] = '';
  // handle options
  $bMap = isset($arrOptions['bmap']) ? $arrOptions['bmap'] : false;
  $showFirstline = isset($arrOptions['firstline']) ? $arrOptions['firstline'] : false;
  if ( isset($arrFLD['numid']) ) {
    $formatRef = isset($arrOptions['numfield']) ? $arrOptions['numfield'] : ''; // '' means build format using row-section and memory
    if ( empty($formatRef) && isset($row['section']) && isset($GLOBALS['_Sections'][(int)$row['section']]['numfield']) ) $formatRef = $GLOBALS['_Sections'][(int)$row['section']]['numfield'];
  }
  if ( empty($formatRef) ) $formatRef = 'N';

  // PRE-PROCESS if required, this adds section-data or user-data into $row[]
  $arr = array();
  $strPrefixSerie = '';
  // prefix smile
  if ( isset($row['icon']) )
  {
    // smile-group
    if ( is_a($oS,'CSection') && $oS->id>=0 ) $strPrefixSerie = $oS->prefix;
    if ( isset($row['prefix']) ) $strPrefixSerie = $row['prefix'];
    if ( empty($strPrefixSerie) || $strPrefixSerie==='0' ) $strPrefixSerie = '';
    if ( !isset($row['icon']) ) $row['icon'] = '00';
  }
  if ( isset($arrFLD['tags']) || isset($arrFLD['title']) )
  {
    $arrTags=array();
    $arrMoreTags=array();
    if ( !empty($row['tags']) ) $arrTags=explode(';',$row['tags']);
    if ( count($arrTags)>3 ) $arrMoreTags = array_slice($arrTags,3,10);
    $arrTags = array_slice($arrTags,0,3);
  }
  // when searching in posts without title, use this to report empty title
  if ( isset($arrFLD['title']) )
  {
    if ( trim($row['title'])=='' ) $row['title']='('.L('reply').')';
    if ( empty($row['title']) && $row['title']!='0' ) $row['title']='('.L('Reply').')';
  }
  // icon
  if ( isset($arrFLD['icon']) )
  {
    if ( !isset($row['posttype']) ) $row['posttype'] = 'P';
    $strTicon = CPost::getIconType($row['posttype'], $row['type'], $row['status'], QT_SKIN);
  }

  if ( isset($arrFLD['mail']) || isset($arrFLD['usercontact']) )
  {
    $str = '';
    if ( !empty($row['mail']) )
    {
    if ( $row['privacy']==2 ) $str = asEmails($row['mail'],'icojava');
    if ( $row['privacy']==1 && SUser::role()!='V' ) $str = asEmails($row['mail'],'icojava');
    if ( SUser::id()==$row['id'] || SUser::isStaff() ) $str = asEmails($row['mail'],'icojava');
    }
    $row['u.mail'] = $str;
  }
  if ( isset($row['privacy']) )
  {
    $row['u.privacy'] = '';
    if ( SUser::id()>0 )
    {
    if ( $row['privacy']==0 ) $row['u.privacy'] = '<i class="fa fa-lock'.(SUser::isStaff() || SUser::id()==$row['id'] ? ' private' : '').'" title="'.L('Privacy_0').'"></i>';
    if ( $row['privacy']==1 ) $row['u.privacy'] = '<i class="fa fa-lock" title="'.L('Privacy_1').'"></i>';
    if ( $row['privacy']==2 ) $row['u.privacy'] = '<i class="fa fa-unlock" title="'.L('Privacy_2').'"></i>';
    }
  }
  if ( $bMap && isset($row['y']) && isset($row['x']) )
  {
    $row['coord']='';
    $row['latlon']='';
    $y = floatval($row['y']);
    $x = floatval($row['x']);
    if ( !empty($y) && !empty($x) )
    {
      $row['coord'] = '<a class="gmappoint" href="javascript:void(0)"'.($_SESSION[QT]['m_gmap_hidelist'] ? '' : ' onclick="gmapPan(\''.$y.','.$x.'\');"').' title="'.L('Coord').': '.round($y,8).','.round($x,8).'"><i class="fa fa-map-marker" title="'.L('latlon').' '.QTdd2dms($y).','.QTdd2dms($x).'"></i></a>';
      $row['latlon'] = QTdd2dms($y).'<br>'.QTdd2dms($x);
    }
  }
  if ( $bMap && empty($row['coord']) ) $row['coord'] = '<i class="fa fa-map-marker disabled" title="No coordinates"></i>';

  // FORMAT

  // ::::::::::
  foreach(array_keys($arrFLD) as $k) {
  // ::::::::::

    switch($k)
    {
    case 'checkbox': $arr[$k] = '<input type="checkbox" name="t1-cb[]" id="t1-cb-'.$row['id'].'" value="'.$row['id'].'"/>'; break;
    case 'icon': $arr[$k] = '<a href="'.Href('qti_item.php').'?t='.$row['id'].'">'.$strTicon.'</a>'; break;
    case 'numid': $arr[$k] = $formatRef=='N' ? '' : sprintf($formatRef,$row['numid']); break;
    case 'title':
      $arr[$k] = '<a class="item" href="'.Href('qti_item.php').'?t='.$row['id'].'"'.(!empty($row['preview']) ? ' title="'.qtAttr($row['preview']).'"' : '').'>'.$row['title'].'</a>';
			if ( $row['type']==='I' && $row['replies']>0 )
        $arr[$k] .= '&nbsp;'.ValueScalebar($row['z'], qtExplodeGet($row['param'],'Ilevel','3'), 50, true, L('I_v_'.qtExplodeGet($row['param'],'Iaggr','mean')).': ');
      if ( !empty($strPrefixSerie) && !empty($row['icon']) && $row['icon']!=='00' )
        $arr[$k] .= ' '.icoPrefix($strPrefixSerie,(int)$row['icon']);
      if ( !empty($arrTags) ) {
        if ( count($arrTags)>1 ) {
          $arr[$k] .= ' <span class="tags" title="'.implode(', ',$arrTags).(empty($arrMoreTags) ? '' : '...').'"><svg class="svg-symbol svg-125"><use href="#symbol-tags" xlink:href="#symbol-tags"></use></svg></span>';
        } else {
          $arr[$k] .= ' <span class="tags" title="'.$arrTags[0].'" data-tagdesc="'.$arrTags[0].'"><svg class="svg-symbol"><use href="#symbol-tag" xlink:href="#symbol-tag"></use></svg></span>';
        }
      }
      if ( !empty($row['textmsg']) && $_SESSION[QT]['item_firstline']>0 && $showFirstline )
        $arr[$k] .= '&nbsp;<small class="item-msg-preview">'.QTtrunc(QTunbbc($row['textmsg'],true,L('Bbc.*')),QT_FIRSTLINE_SIZE).(empty($row['attach']) ? '' : ' '.getSVG('paperclip', 'title='.L('Attachment'))).'</small>';
      if ( !empty($row['coord']) ) $arr[$k] .= ' '.$row['coord'];
			break;
    case 'replies':
      $arr[$k] = ($row['replies']>0 ? '<i data-re="'.$strTableId.'re'.$row['id'].'"></i> ' : '').$row['replies'];
      break;
    case 'section':
      $i = (int)$row['section'];
      $arr[$k] = '<a href="'.Href('qti_items.php').'?s='.$i.'">'.(isset($GLOBALS['_Sections'][$i]['title']) ? $GLOBALS['_Sections'][$i]['title'] : 'Section '.$i).'</a>';
      break;
    case 'firstpostname':
      $arr[$k] = '<p class="ellipsis"><a id="t'.$row['id'].'-firstpostname" href="'.Href('qti_user.php').'?id='.$row['firstpostuser'].'">'.$row['firstpostname'].'</a></p>';
      $arr[$k] .= '<p class="ellipsis"><small>'.QTdatestr($row['firstpostdate'],'$','$',true,true,true,'t'.$row['id'].'-firstpostdate').'</small></p>';
      break;
    case 'lastpostdate':
      if ( empty($row['lastpostdate']) ) {
        $arr[$k] = '&nbsp;';
      } else {
        $arr[$k] = '<p>'.QTdatestr($row['lastpostdate'],'$','$',true,true,true,'t'.$row['id'].'-lastpostdate').' <a id="t'.$row['id'].'-lastpostico" class="lastitem" href="'.Href('qti_item.php').'?t='.$row['id'].'#p'.$row['lastpostid'].'" title="'.L('Goto_message').'">'.getSVG('caret-square-right').'</a></p>';
        $arr[$k] .= '<p class="ellipsis"><small>'.L('by').' <a id="t'.$row['id'].'-lastpostname" href="'.Href('qti_user.php').'?id='.$row['lastpostuser'].'" title="'.qtAttr($row['lastpostname'],25).'">'.QTtrunc($row['lastpostname'],15).'</a></small></p>';
      }
      break;
    case 'status':
      $arrS = SMem::get('_Statuses');
      $arr[$k] = '<span title="'.(empty($row['statusdate']) ? '' : QTdatestr($row['statusdate'],'d M','H:i',true,true)).'">'.(isset($arrS[$row['status']]['name']) ? $arrS[$row['status']]['name'] : $row['status']).'</span>';
      break;
    case 'actor':
      $arr[$k] = '<a id="t'.$row['id'].'-actor" href="'.Href('qti_user.php').'?id='.$row['actorid'].'" title="'.L('Ico_user_p').'">'.QTtrunc($row['actorname'],15).'</a>';
      break;
    case 'tags':
    	$strTags = '';
    	foreach($arrTags as $str) if ( !empty($str) ) $strTags .= '<span class="tag" title="" data-tagdesc="'.$str.'">'.$str.'</span>';
    	if ( !empty($arrMoreTags) ) $strTags .= '<abbr title="'.implode(', ',$arrMoreTags).'">...</abbr>';
    	$arr[$k] = (empty($strTags) ? '&nbsp;' : $strTags);
    	break;
    case 'usercontact':
      $arr[$k] = empty($row['u.mail']) ? '<i class="fa fa-envelope fa-lg disabled" title="('.L('unknown').')"></i> ' : $row['u.mail'].' ';
      $arr[$k] .= empty($row['u.privacy']) ? '' : $row['u.privacy'];
      break;
    case 'userphoto':
      $arr[$k] = '<div class="magnifier center">'.SUser::getPicture((int)$row['id'], 'data-magnify=0|onclick=this.dataset.magnify=this.dataset.magnify==1?0:1;', '').'</div>';
      break;
    case 'username':  $arr[$k] = '<a href="'.Href('qti_user.php').'?id='.$row['id'].'" title="'.qtAttr($row['name']).'">'.qtAttr($row['name']).'</a>'; break;
    case 'usermarker': $arr[$k] = empty($row['coord']) ? '&nbsp;' : $row['coord']; break;
    case 'userrole': $arr[$k] = L('Role_'.$row['role']); break;
    case 'userlocation': $arr[$k] = empty($row['location']) ? '&nbsp;' : QTtrunc($row['location'],24); break;
    case 'usernumpost': $arr[$k] = $row['numpost']; break;
    case 'firstdate': $arr[$k] = empty($row['firstdate']) ? '&nbsp;' : QTdatestr($row['firstdate'],'$','',true,false,true); break;
    case 'modifdate': $arr[$k] = empty($row['modifdate']) ? '&nbsp;' : QTdatestr($row['modifdate'],'$','',true,false,true); break;
    case 'coord': $arr[$k] = empty($row['u.latlon']) ? '' : $row['u.latlon']; break;
    default:
      if ( isset($row[$k]) )
      {
        $arr[$k] = $row[$k];
      }
      else
      {
        $arr[$k] = '';
      }
      break;
    }

  // ::::::::::
  }
  // ::::::::::

  return $arr;
}

function ValueName($i=0, string $level='3')
{
  if ( $i<0 ) return '';
  // strLevel can be empty when ticket param is empty (and '' is returned)
  switch($level)
  {
  case '2':
    if ( QTisbetween($i,50) ) return L('I_r_yes');
    return L('I_r_no');
    break;
  case '3':
    if ( QTisbetween($i,66.67) ) return L('I_r_good');
    if ( QTisbetween($i,33.33,66.66) ) return L('I_r_medium');
    return L('I_r_bad');
    break;
  case '5':
    if ( QTisbetween($i,80) ) return L('I_r_veryhigh');
    if ( QTisbetween($i,60,79.99) ) return L('I_r_high');
    if ( QTisbetween($i,40,59.99) ) return L('I_r_medium');
    if ( QTisbetween($i,20,39.99) ) return L('I_r_low');
    return L('I_r_verylow');
    break;
  case '100':
    return strval(round($i)).'%';
    break;
  }
  return '';
}

function ValueScalebar($i=0, string $level='3', int $width=50, bool $title=true, string $prefixtitle='')
{
  if ( $i<0 ) return '';
  // strLevel can be empty when ticket param is empty (and null image is returned)
  $str = '00';
  switch($level)
  {
  case '2':
    if ( QTisbetween($i,50) ) $str='99';
    break;
  case '3':
    if ( QTisbetween($i,66.67) ) $str='99';
    elseif ( QTisbetween($i,33.33,66.66) ) $str='50';
    break;
  case '5':
    if ( QTisbetween($i,80) ) $str='99';
    elseif ( QTisbetween($i,60,79.99) ) $str='70';
    elseif ( QTisbetween($i,40,59.99) ) $str='50';
    elseif ( QTisbetween($i,20,39.99) ) $str='30';
    break;
  case '100':
    if ( QTisbetween($i,96) ) $str='99';
    elseif ( QTisbetween($i,85,96) ) $str='90';
    elseif ( QTisbetween($i,75,85) ) $str='80';
    elseif ( QTisbetween($i,65,75) ) $str='70';
    elseif ( QTisbetween($i,55,65) ) $str='60';
    elseif ( QTisbetween($i,45,55) ) $str='50';
    elseif ( QTisbetween($i,35,45) ) $str='40';
    elseif ( QTisbetween($i,25,35) ) $str='30';
    elseif ( QTisbetween($i,15,25) ) $str='20';
    elseif ( QTisbetween($i,4,15) ) $str='10';
    break;
  }
  return '<img src="bin/css/scalebar'.$str.'.gif" style="height:15px; width:'.$width.'px; vertical-align:middle"'.($title ? ' title="'.$prefixtitle.ValueName($i,$level).'"': '').' alt="--"/>';
}