<?php // v4.0 build:20240210

/*
 * Static class
 * When datase query is required, the called method must include a CDatabase instance as first parameter
 */

class SUser
{

// Current user info from $_SESSION[QT.'_usr'][]

public static function auth() { return self::getInfo('auth',false); }
public static function role() { return self::getInfo('role','V'); }
public static function id()   { return self::getInfo('id',0); }
public static function name() { return self::getInfo('name','Guest'); }
public static function getInfo(string $key='', $alt='')
{
  // User's property as in CURRENT SESSION or $alt if property not found
  if ( isset($_SESSION[QT.'_usr'][$key]) ) return $_SESSION[QT.'_usr'][$key];
  return $alt;
}
public static function isStaff() { return self::role()==='M' || self::role()==='A'; }
public static function coppa()
{
  // No need to add query: coppa values are available in the $_SESSION[QT.'_usr']
  $children = self::getInfo('children','0');
  $parentmail = self::getInfo('parentmail','');
  return array('children'=>(int)$children,'parentmail'=>$parentmail);
}

// Login/out

public static function logIn(string $username='', string $password='', bool $remember=false)
{
  if ( empty($username) || empty($password) ) return false;

  // Format data
  $username = qtDb($username);
  $hpassword = sha1($password);
  if ( !defined('QT_LOGIN_WITH_EMAIL') ) define('QT_LOGIN_WITH_EMAIL',false);

  // Check profile exists for this username/password (auth is true if only ONE user exists)
  global $oDB;
  $sqlName = 'name=:name'.(QT_LOGIN_WITH_EMAIL ? ' OR mail=:name OR mail LIKE :firstmail' : ''); // email or first-email
  $_SESSION[QT.'_usr']['auth'] = $oDB->count(
    TABUSER." WHERE id>0 AND pwd=:pwd AND ($sqlName)",
    [':pwd'=>$hpassword, ':name'=>$username, ':firstmail'=>$username.',%']
    )===1;

  // External login: even if profile is not found (self::auth() is false) external login may be able to create a new profile
  // Note 'Admin' MUST ALWAYS BYPASS external login:
  // When ldap config/server changes, Admin (at least) MUST be able to login to change the settings!
  if ( isset($_SESSION[QT]['login_addon']) && $_SESSION[QT]['login_addon']!=='0' && $username!=='Admin' )
  {
    $sModuleKey = $_SESSION[QT]['login_addon'];
    $prefix = strtolower(substr(constant('QT'),0,3));
    if ( isset($_SESSION[QT][$sModuleKey]) && $_SESSION[QT][$sModuleKey]!=='0' )
    {
      if ( file_exists($prefix.$sModuleKey.'_login.php') )
      {
        include $prefix.$sModuleKey.'_login.php';
      } else {
        $_SESSION[QT.'_usr']['auth'] = false;
        echo 'Access denied (missing addon controler)';
      }
    }
  }
  // Register and get extra user info, if authentication is successfull (note hpassword is already hashed here)
  if ( self::auth() )
  {
    global $oDB;
    self::registerUser($oDB,$username,$hpassword,false); // get extra user info and register user's info
    $oDB->exec( "UPDATE TABUSER SET ip='".$_SERVER['REMOTE_ADDR']."' WHERE id=".self::id());
    if ( $remember )
    {
      setcookie(QT.'_cookname', $username, time()+3600*24*100, '/');
      setcookie(QT.'_cookpass', $hpassword, time()+3600*24*100, '/');
      setcookie(QT.'_cooklang', QT_LANG, time()+3600*24*100, '/');
    }
  }
  return $_SESSION[QT.'_usr']['auth'];
}
/** remove cookies and destroy session */
public static function logOut()
{
  if ( isset($_COOKIE[QT.'_cookname']) ) setcookie(QT.'_cookname', '', time()-3600, '/');
  if ( isset($_COOKIE[QT.'_cookpass']) ) setcookie(QT.'_cookpass', '', time()-3600, '/');
  if ( isset($_COOKIE[QT.'_cooklang']) ) setcookie(QT.'_cooklang', '', time()-3600, '/');
  session_destroy();
}


// Current user access right

public static function canAccess(string $param)
{
  $role = self::role(); if ( $role=='A' ) return true;
  // Page search (only in case of visitor)
  if ( $param==='search' ) {
    if ( $role==='V' && isset($_SESSION[QT]['visitor_right']) && $_SESSION[QT]['visitor_right']<4 ) return false;
    return true;
  }
  // Settings: upload, show_calendar, show_stats, show_memberlist
  if ( $param==='calendar' || $param==='stats' || $param==='memberlist' ) $param = 'show_'.$param; // settings alias
  if ( !isset($_SESSION[QT][$param]) ) return false;
  if ( $_SESSION[QT][$param]=='V' ) return true;
  if ( $_SESSION[QT][$param]=='M' && $role=='M' ) return true;
  if ( $_SESSION[QT][$param]=='U' && $role!='V' ) return true;
  return false;
}
public static function canView(string $level='V4', bool $offlinestop=true)
{
  if ( !isset($_SESSION[QT]['visitor_right']) ) return FALSE;
  if ( !isset($_SESSION[QT]['board_offline']) ) return FALSE;

  // $level user role that can access the page: U, M, A or Vi(where i=public access level)
  // $offlinestop stop when application off-line

  if ( self::role()==='A' ) return true;

  if ( $level==='U' && self::role()==='V') return false;
  if ( $level==='M' && !self::isStaff() ) return false;
  if ( $level==='A' && self::role()!=='A' ) return false;
  $strPAL='5';
  if ( isset($level[1]) ) $strPAL=$level[1]; // use second char, otherwize 5
  if ( self::role()==='V' && $_SESSION[QT]['visitor_right']<$strPAL ) return false;
  if ( $_SESSION[QT]['board_offline']==='1' && $offlinestop ) return false;
  return true;
}
public static function canSeePrivate(int $userprivacy, int $userid)
{
  // Function is used to hide information: returns false when userinfo is private and current user is not granted
  if ( self::isStaff() ) return true;
  // Check privacy: $userprivacy is the user's privacy level (can be integer !)
  if ( $userprivacy==2 || self::id()==$userid ) return true; // public or user's own info
  if ( $userprivacy==1 && self::role()!=='V') return true;
  return false;
}
public static function canEditTags(CTopic $oT)
{
  if ( empty($_SESSION[QT]['tags']) || $oT->status==='Z' ) return false;
  if ( SUser::isStaff()  ) return true;
  if ( $_SESSION[QT]['tags']==='U' && SUser::id()===$oT->firstpostuser ) return true; // 'U'=members can edit in his own ticket
  if ( $_SESSION[QT]['tags']==='U+' && SUser::role()==='U' ) return true; // 'U+'=members can edit any tickets
  if ( $_SESSION[QT]['tags']==='V' ) return true; // 'V'=Visitor can edit any tickets
  return false;
}
public static function confirmCookie(CDatabase $oDB)
{
  // Check if coockies are valid.
  if ( !self::auth() && isset($_COOKIE[QT.'_cookname']) && isset($_COOKIE[QT.'_cookpass']) ) {
    self::registerUser($oDB,$_COOKIE[QT.'_cookname'],$_COOKIE[QT.'_cookpass'],false); // false because password is already hashed in cookies
    return true; // User is auth by coockie. (true is used to ask the confimation message box after).
  }
  return false; // No confirmation needed. User remains auth or not auth
}
public static function unsetSession()
{
  // User's properties as in CURRENT SESSION
  $_SESSION[QT.'_usr'] = [];
  $_SESSION[QT.'_usr']['auth'] = false;
  $_SESSION[QT.'_usr']['id']   = 0;
  $_SESSION[QT.'_usr']['name'] = L('Role_V');
  $_SESSION[QT.'_usr']['role'] = 'V';
}
public static function loginPostProc(CDatabase $oDB)
{
  if ( !self::auth() ) die('User is not authenticated');

  global $oH;

  // check ban

  $ban = (int)self::getInfo('closed',0);
  $name = self::name();

  if ( $ban>0 )
  {
    $items = (int)self::getInfo('numpost',0);
    // protection against hacking of admin/moderator
    if ( self::id()<2 || self::isStaff() || $items==0 )
    {
      $oDB->exec( "UPDATE TABUSER SET closed='0' WHERE id=".self::id() );
      $oH->exiturl = APP.'_login.php?dfltname='.$name;
      $oH->exitname = L('Login');
      self::unsetSession();
      $oH->voidPage('', '<p>'.L('Is_banned_nomore').'</p><p><a href="'.url($oH->exiturl).'">'.$oH->exitname.'</a></p>');
    }

    // end ban control
    $last = self::getInfo('lastdate','20000101');
    $intDays = 1;
    if ( $ban==2 ) $intDays = 10;
    if ( $ban==3 ) $intDays = 20;
    if ( $ban==4 ) $intDays = 30;
    if ( $ban==5 ) $intDays = 90;
    if ( $ban==6 ) $intDays = 365;
    $endban = addDate(substr($last,0,8),$intDays,'day');

    if ( date('Ymd')>$endban )
    {
      $oDB->exec( "UPDATE TABUSER SET closed='0' WHERE id=".self::id() );
      $oH->exiturl = APP.'_login.php?dfltname='.$name;
      $oH->exitname = L('Login');
      self::unsetSession();
      $oH->voidPage('', '<p>'.L('Is_banned_nomore').'</p><p class="submit right"><a class="button" href="'.$oH->exiturl.'">'.$oH->exitname.'</a></p>');
    }
    else
    {
      self::unsetSession();
      $oH->voidPage('', '<p>'.L('E_10').'<br>'.$name.' '.strtolower(L('Is_banned')).'<br>'.L('Retry_tomorrow').'</p>');
    }
  }

  // upgrade profile if new user (secrect question)
  $oDB->query( "SELECT secret_a FROM TABUSER WHERE id=".self::id() );
  $row = $oDB->getRow();
  if ( empty($row['secret_a']) )
  {
    $oH->exiturl = APP.'_register.php?a=qa&id='.self::id();
    $oH->exitname = L('Secret_question').'...';
    $oH->voidPage('', '<h2>'.L('Welcome').' '.$name.'</h2><br><p/>'.L('Update_secret_question').'</p><p class="submit right"><a class="button" href="'.$oH->exiturl.'">'.$oH->exitname.'</a></p>');
  }
}

// Any user

/**
 * @param int $id
 * @param array|string $attr
 * @param string $altSrc alternate image source (or '')
 * @return string img tag (can be '' when $altSrc is empty and image not found)
 */
public static function getPicture(int $id=0, $attr=[], string $altSrc='bin/css/user.gif'){
  // NOSQL, uses file_exists(). Returns '' when image not found *and* $altSrc=''
  $path = qtDirData(QT_DIR_PIC,$id,true); if ( empty($path) ) return empty($altSrc) ? '' : '<img src="'.$altSrc.'"'.attrRender($attr).'/>';
  if ( is_string($attr) ) $attr = attrDecode($attr);
  if ( !is_array($attr) || isset($attr['src']) ) die(__METHOD__.' invalid attr');
  if ( !isset($attr['alt']) ) $attr['alt'] = $id;
  $src = $altSrc;
  foreach(['.jpg','.jpeg','.png','.gif'] as $mime) if ( file_exists($path.$id.$mime) ) { $src = $path.$id.$mime; break; }
  return empty($src) ? '' : '<img src="'.$src.'"'.attrRender($attr).'/>';
}
public static function getStamp(string $role, string $attr='class=stamp', bool $addDefaultTitle=true, string $alt='') {
  if ( $addDefaultTitle ) $attr = attrDecode($attr, '|', 'title='.L('Role_'.$role));
  if ( in_array($role,['A','M','U','V']) ) return '<span'.attrRender($attr).'>'.qtSvg('user-'.$role).'</span>';
  return $alt;
}
public static function getStampUnicode(string $role='', string $others='') {
  if ( $role==='A' ) return '&#xf505;';
  if ( $role==='M' ) return '&#xf4fc;';
  return $others;
}
public static function registerUser(CDatabase $oDB, string $name='', string $password='', bool $sha=true)
{
  // Read and Set user in session variable $_SESSION[QT.'_usr']
  $oDB->query( "SELECT id,name,closed,role,firstdate,lastdate,numpost,children,parentmail,parentagree FROM TABUSER WHERE pwd=? AND name=?", [$sha ? sha1($password) : $password, qtDb($name)] ); // when checking from coockies, password is already hashed
  if ( $row=$oDB->getRow() )
  {
    $_SESSION[QT.'_usr']           = $row;
    $_SESSION[QT.'_usr']['auth']   = true;
    $_SESSION[QT.'_usr']['id']     = (int)$row['id'];
    $_SESSION[QT.'_usr']['numpost']= (int)$row['numpost'];
  }
}
public static function delete(CDatabase $oDB, int $id=0)
{
  if ( $id<2 ) die('self::delete invalid argument');
  $oDB->beginTransac();
  $oDB->exec( "UPDATE TABPOST SET userid=0,username='Visitor' WHERE userid=".$id );
  $oDB->exec( "UPDATE TABTOPIC SET firstpostuser=0,firstpostname='Visitor' WHERE firstpostuser=".$id );
  $oDB->exec( "UPDATE TABTOPIC SET lastpostuser=0,lastpostname='Visitor' WHERE lastpostuser=".$id );
  $oDB->exec( "UPDATE TABSECTION SET moderator=1,moderatorname='Admin' WHERE moderator=".$id );
  $oDB->exec( "DELETE FROM TABUSER WHERE id=".$id );
  $b = $oDB->commitTransac(); // return false in case of query error or transaction failed
  SMem::clear('_Sections');
  if ( $b )
  {
    self::deletePicture($id); // remove picture
    SMem::clear('_NewUser'); // clear memcache
    return true;
  }
  return false;
}
public static function deletePicture($ids)
{
  if ( is_int($ids) ) $ids = array($ids);
  if ( !is_array($ids) ) die(__METHOD__.' arg#1 must be an id or array of id');
  foreach($ids as $id) {
    if ( !is_int($id) ) die(__METHOD__.' arg#1 must be an id or array of id');
    $dir = qtDirData(QT_DIR_PIC,$id,true); if ( empty($dir) ) continue;
    foreach(array('.jpg','.jpeg','.png','.gif') as $ext) if ( file_exists($dir.$id.$ext) ) unlink($dir.$id.$ext);
  }
}
public static function isUsedName(CDatabase $oDB, string $name){
  if ( !qtIsPwd($name) ) return L('Username').' '.L('invalid');
  if ( $oDB->count( TABUSER." WHERE name=?", [qtDb($name)] )!==0 ) return L('Username').' '.L('already_used');
  return false;
}
public static function rename(CDatabase $oDB, int $id=0, string $name='visitor')
{
  // Tips: Check isUsedName() before
  if ( empty($name) || $id<1 ) die('self::rename invalid argument');
  $name = qtDb($name);
  $oDB->beginTransac();
  $oDB->exec( 'UPDATE TABUSER SET name=? WHERE id='.$id, [$name] );
  $oDB->exec( 'UPDATE TABPOST SET username=? WHERE userid='.$id, [$name] );
  $oDB->exec( 'UPDATE TABPOST SET modifname=? WHERE modifuser='.$id, [$name] );
  $oDB->exec( 'UPDATE TABTOPIC SET firstpostname=? WHERE firstpostuser='.$id, [$name] );
  $oDB->exec( 'UPDATE TABTOPIC SET lastpostname=? WHERE lastpostuser='.$id, [$name] );
  $oDB->exec( 'UPDATE TABSECTION SET moderatorname=? WHERE moderator='.$id, [$name] );
  $b = $oDB->commitTransac();
  SMem::clear('_NewUser'); // clear memcache
  return $b; // return false in case of query error or transaction failled
}
public static function getLastMember()
{
  $arr = [];
  global $oDB; $oDB->query( "SELECT max(u.id) as maxid,u.name,u.firstdate FROM TABUSER u WHERE u.id>0" );
  if ( $row = $oDB->getRow() )
  {
    $arr['id'] = (int)$row['maxid'];
    $arr['name'] = $row['name'];
    $arr['firstdate'] = (empty($row['firstdate']) ? '0' : substr($row['firstdate'],0,8)); // date only
  }
  return $arr;
}

public static function addUser(string $username='', string $password='', string $mail='', string $role='U', string $child='0', string $parentmail='', string $secret_q='', string $secret_a='', string $birthday='')
{
  if ( empty($username) || empty($password) ) die('self::addUser invalid argument');
  $secret_a = strtolower(trim($secret_a));
  if ( !empty($secret_a) ) $secret_a = sha1($secret_a); // encode (if not empty)
  if ( (int)$birthday<19000101 ) $birthday='';
  global $oDB;
  $oDB->beginTransac();
  $id = $oDB->nextId(TABUSER);
  $oDB->exec( "INSERT INTO TABUSER (id,name,pwd,closed,role,mail,privacy,firstdate,lastdate,numpost,children,parentmail,secret_q,secret_a,birthday)
  VALUES ($id,:name,:pwd,'0',:role,:mail,'1',:firstdate,:firstdate,0,:children,:parentmail,:secret_q,:secret_a,:birthday)",
    [
    ':name'=>qtDb($username),
    ':pwd'=>sha1($password),
    ':role'=>$role,
    ':mail'=>$mail,
    ':firstdate'=>date('Ymd His'),
    ':children'=>$child,
    ':parentmail'=>$parentmail,
    ':secret_q'=>qtDb($secret_q),
    ':secret_a'=>qtDb($secret_a),
    ':birthday'=>$birthday
    ]
      );
  $oDB->commitTransac();
  self::deletePicture($id); // clean picture
  SMem::clear('_NewUser'); // clear memcache
  return empty($oDB->error) ? $id : $oDB->error;
}
public static function getUserId(CDatabase $oDB, string $name, $failed=false)
{
  // Returns FALSE when user does not exist OR when argument is wrong
  // Caution when testing returned value: userid 0 is visitor!
  if ( empty($name) ) return false;
  $oDB->query( "SELECT id FROM TABUSER WHERE name=?", [$name] );
  if ( $row=$oDB->getRow() ) return (int)$row['id'];
  return $failed;
}
public static function setCoord(CDatabase $oDB, int $id, string $coord='0,0')
{
  // Coordinates must be a string 'y,x'.
  // '0,0' can be use to remove a coordinates.
  // z is not used here
  $y=null;
  $x=null;
  $coord = explode(',',$coord);
  if ( isset($coord[0]) ) $y = (float)$coord[0];
  if ( isset($coord[1]) ) $x = (float)$coord[1];
  if ( emptyFloat($y) && emptyFloat($x) ) { $y=null; $x=null; }
  if ( is_null($y) ) $y='NULL';
  if ( is_null($x) ) $x='NULL';
  $oDB->exec( "UPDATE TABUSER SET y=$y,x=$x WHERE id=$id" );
}

}