<?php // v4.0 build:20240210

// NOTE: Don't put TABSTATUS in query (not decoded by CDatabase::sqlConst)

class SStatus
{

public static function getAll($translate=false)
{
  $arr = [];
  global $oDB; $oDB->query( "SELECT * FROM ".TABSTATUS." ORDER BY id" );
  while($row=$oDB->getRow()) {
    $arr[$row['id']]['name'] = $row['name'];
    $arr[$row['id']]['icon'] = $row['icon'];
    $arr[$row['id']]['mailto'] = $row['mailto'];
    $arr[$row['id']]['color'] = $row['color'];
  }
  return $arr;
}
public static function add(string $id='', string $name='', string $icon='', string $color='', string $mailto='')
{
  // Check
  if ( !self::isAZ($id) ) die('SStatus::add Argument #1 invalid');
  if ( empty($name) ) die('SStatus::add Argument #2 must be a string');
  $name = qtDb($name);
  // Unique id and name
  global $oDB;
  if ( $oDB->count( TABSTATUS." WHERE id='$id'" )>0 ) throw new Exception( "Status id [$id] already used" );
  if ( $oDB->count( TABSTATUS." WHERE name='$name'" )>0 ) throw new Exception( "Status name [$name] already used" );
  // Save
  $oDB->exec( "INSERT INTO ".TABSTATUS." (id,name,color,mailto,icon) VALUES (?,?,?,?,?)", [$id,$name,$color,$mailto,$icon] );
  // Exit
  SMem::clear('_Statuses');
}
public static function delete(string $id='', string $to='A')
{
  if ( $id==$to ) die('SStatus::delete: Argument #1 equal #2');
  if ( !self::isAZ($id,true) )  throw new Exception( 'SStatus::delete id must be between B and Y' );
  if ( !self::isAZ($to,false) )  throw new Exception( 'SStatus::delete to must be between A and Z' );
  // Process - status id > to and delete id
  global $oDB;
  $oDB->exec( "UPDATE TABTOPIC SET status='$to' WHERE status='$id'" );
  $oDB->exec( "DELETE FROM ".TABSTATUS." WHERE id='$id'" );
  $oDB->exec( "DELETE FROM TABLANG WHERE (objtype='status' OR objtype='statusdesc') AND objid='$id'" );
  // Exit
  SMem::clear('_Statuses');
}
public static function chgId(string $id='', string $to='')
{
  if ( !self::isAZ($id,true) || !self::isAZ($to,true) )  throw new Exception( 'SStatus::chgId id must be between B and Y' );
  if ( $id==$to ) throw new Exception( 'SStatus::chgId: same id' );
  // Process
  global $oDB;
  $oDB->exec( "UPDATE TABTOPIC SET status='$to' WHERE status='$id'" );
  $oDB->exec( "UPDATE ".TABSTATUS." SET id='$to' WHERE id='$id'" );
  // Exit
  SMem::clear('_Statuses');
}
public static function isAZ(string $str, bool $noAZ=false)
{
  if ( strlen($str)!==1 || empty($str) ) return false;
  if ( $str<'A' || $str>'Z' ) return false;
  if ( $noAZ && ($str==='A' || $str==='Z' ) ) return false;
  return true;
}
public static function getIcon(string $str='A', string $attr='')
{
  $src = self::getIconFile($str);
  if ( substr($src,-4)==='.svg' ) return qtSvg(substr($src,0,-4), $attr);
  return asImg(QT_SKIN.'img/'.$src, $attr);
}
public static function getName(string $str='A')
{
  return empty($GLOBALS['_Statuses'][$str]['name']) ? $str : $GLOBALS['_Statuses'][$str]['name'];
}
public static function getIconFile(string $str='A')
{
  return empty($GLOBALS['_Statuses'][$str]['icon']) ? 'status_0.gif' : $GLOBALS['_Statuses'][$str]['icon'];
}
public static function translate(string $k='A', string $type='status')
{
  // returns translated title (from session memory), uses config name if no translation
  return SLang::translate($type, $k, self::getName($k));
}

}