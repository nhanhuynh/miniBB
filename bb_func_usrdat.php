<?php
/*
This file is part of miniBB. miniBB is free discussion forums/message board software, without any warranty. See COPYING file for more details.
Copyright (C) 2004, 2006, 2010, 2013 Paul Puzyrev. www.minibb.com
Latest File Update: 2013-Feb-26
*/
if (!defined('INCLUDED776')) die ('Fatal error.');

if(isset($_POST) and count($_POST)>0){

foreach($dbUserSheme as $k=>$v){
if(isset($_POST[$v[2]])) $s = str_replace(chr(92), '', operate_string(stripslashes($_POST[$v[2]]))); else $s='';
${$v[1]}=$s; ${$v[2]}=$s;
}

if(!isset($_POST['passwd2'])) $passwd2=''; else $passwd2=str_replace(chr(92), '', operate_string(stripslashes($_POST['passwd2'])));
if(isset($login)) $login=preg_replace("#[\s]{2,}#", ' ', $login);

}
else die('Unexpected error');
?>