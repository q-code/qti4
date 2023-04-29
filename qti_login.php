<?php

session_start();
/**
 * @var CHtml $oH
 * @var CDatabase $oDB
 */
require 'bin/init.php';

include Translate('lg_reg.php');

// --------
// SUBMITTED for loggout. To redirect to login page use url '?a=out&r=in'
// --------

if ( isset($_GET['a']) && $_GET['a']=='out' ) {
  $str = L('Goodbye');
  SUser::logOut();
  // REBOOT
  $oDB->getSettings('',true);
  session_start();
  $_SESSION[QT.'splash'] = $str;
  $oH->redirect( APP.'_'.(isset($_GET['r']) && $_GET['r']==='in' ? 'login' : 'index').'.php' );
}

// --------
// INITIALISE
// --------

$oH->selfurl = 'qti_login.php';
$oH->selfname = L('Login');
$strName = isset($_GET['dfltname']) ? qtAttr($_GET['dfltname']) : '';
$certificate = makeFormCertificate('fd352f14798ecfe7a6ae09fd447c207b');

// --------
// SUBMITTED for login
// --------

if ( isset($_POST['ok']) ) try {

  // check certificate
  if ( $_POST['ok']!==$certificate ) die('Unable to check certificate');
  // check values
  $strName = trim($_POST['usr']); // Trim required. qtDb encode is performed while sql query
  if ( !qtIsPwd($strName) ) throw new Exception( L('Username').' '.L('invalid') );
  $strPwd = $_POST['pwd']; // Trim required. Sha is performed while sql query
  if ( !qtIsPwd($strPwd) ) throw new Exception( L('Password').' '.L('invalid') );

  // LOGIN
  SUser::logIn($strName,$strPwd,isset($_POST['remember'])); //name and pwd qtDb-encode is performed while sql query
  if ( !SUser::auth() ) throw new Exception( L('E_login') );

  // check ban, unban and secret question
  SUser::loginPostProc($oDB); //... can exit to register specific page

  // end message
  $_SESSION[QT.'splash'] = L('Welcome').' '.$strName;
  $oH->redirect('qti_index.php');

} catch (Exception $e) {

  $error = $e->getMessage();
  $_SESSION[QT.'splash'] = 'E|'.$error;

}

// --------
// HTML BEGIN
// --------

include 'qti_inc_hd.php';

CHtml::msgBox($oH->selfname, 'class=msgbox formLogin');

$str = L('Username').(QT_LOGIN_WITH_EMAIL ? ' '.L('or').' '.L('email') : '');
echo '<form method="post" action="'.Href($oH->selfurl).'">'.PHP_EOL;
echo '<p><a href="'.Href('qti_register.php?a=id').'">'.L('Forgotten_pwd').'</a></p>';
echo '<p title="'.$str.'">'.getSVG('user','class=svg-label').' <input required type="text" id="usr" name="usr" size="24" minlength="4" maxlength="50" value="'.qtAttr($strName).'" placeholder="'.$str.'"/></p>';
echo '<p class="input-pwd" title="'.L('Password').'">'.getSVG('lock','class=svg-label').' <input required type="password" id="pwd-1" name="pwd" size="24" minlength="4" maxlength="50" placeholder="'.L('Password').'" />'.getSVG('eye', 'class=toggle-pwd clickable|onclick=togglePwd(1)').'</p>';
echo '<p class="submit">';
if ( QT_REMEMBER_ME ) echo '<span class="cblabel"><input type="checkbox" id="remember" name="remember"/>&nbsp;<label for="remember">'.L('Remember'),'</label></span>&nbsp;&nbsp;';
echo '<button type="submit" name="ok" value="'.$certificate.'">'.L('Ok').'</button></p>';
echo '</form>';

CHtml::msgBox('/');

// HTML END

$oH->scripts[] = 'let doc = document.getElementById("usr"); doc.focus(); if ( doc.value.length>1 ) document.getElementById("pwd-1").focus();
function togglePwd(id) {
  let d = document.getElementById("pwd-"+id);
  if ( d.type==="password" ) { d.type="text"; } else { d.type="password"; }
}';

include 'qti_inc_ft.php';