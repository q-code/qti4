<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

include 'qti_index.php';

if ( isset($_GET['debugsql']) )
{
  switch($_GET['debugsql'])
  {
  case '1': $_SESSION['QTdebugsql']=true; var_dump($_SESSION['QTdebugsql']); break;
  case 'log': $_SESSION['QTdebugsql']='log'; var_dump($_SESSION['QTdebugsql']); break;
  default: unset($_SESSION['QTdebugsql']);
  }
}
if ( isset($_GET['statsql']) )
{
  if ( $_GET['statsql']==='1' ) { $_SESSION['QTstatsql']=true; echo 'QTstatsql='.$_SESSION['QTstatsql']; } else { unset($_SESSION['QTstatsql']); }
}
if ( isset($_GET['debugsse']) )
{
  if ( $_GET['debugsse']==='1' ) { $_SESSION['QTdebugsse']=true; echo 'QTdebugsse='.$_SESSION['QTdebugsse']; } else { unset($_SESSION['QTdebugsse']); }
}
if ( isset($_GET['debuglang']) )
{
  if ( $_GET['debuglang']==='1' ) { $_SESSION['QTdebuglang']=true; echo 'QTdebuglang='.$_SESSION['QTdebuglang']; } else { unset($_SESSION['QTdebuglang']); }
}
if ( isset($_GET['debugmem']) )
{
  if ( $_GET['debugmem']==='1' ) { $_SESSION['QTdebugmem']=true; echo 'QTdebugmem='.$_SESSION['QTdebugmem']; } else { unset($_SESSION['QTdebugmem']); }
}