<?php

function L(string $k='', $n=false, bool $show_n=true, string $sep_n=' ', array $A=[], string $pk='')
{
  if ( trim($k)==='' ) throw new Exception('Invalid argument');
  // check n
  if ( $n!==false && !is_int($n) ) throw new Exception('Invalid argument');
  if ( is_int($n) && $n<0 ) throw new Exception('Invalid argument');
  // initialise
  if ( $A===[] ) { global $L; $A=$L; }
  $str = $pk.$k;
  // check subarray
  if ( strpos($k,'.')>0 )
  {
    $part = explode('.', $k,2);
    if ( empty($A[$part[0]]) || !is_array($A[$part[0]]) ) return $k;
    if ( $part[1]==='*' ) return $A[$part[0]];
    return L($part[1],$n,$show_n,$sep_n,$A[$part[0]],$part[0].'.');
  }
  // check plural
  $s = $n==false || $n<2 ? '' : 's';
  // search word
  if ( !empty($A[$k.$s]) ) {
    $str = $A[$k.$s];
  } elseif ( !empty($A[ucfirst($k.$s)]) ) {
    $str = strtolower($A[ucfirst($k.$s)]);
  } elseif ( !empty($A[$k]) ) {
    $str = $A[$k];
  } elseif ( !empty($A[ucfirst($k)]) ) {
    $str = strtolower($A[ucfirst($k)]);
  } else {
    if ( substr($str,0,2)==='E_' ) $str = 'error: '.substr($str,2); // key is an (un-translated) error code, returns the error code
    if ( strpos($str,'_')!==false ) $str = str_replace('_',' ',$str); // When word is missing, returns the key code without _ (with the parentkey if used)
    if ( isset($_SESSION['QTdebuglang']) && $_SESSION['QTdebuglang'] ) $str = '<span style="color:red">'.$str.'</span>';
  }
  // if number add before
  return ($n!==false && $show_n ? $n.$sep_n : '').$str;
}