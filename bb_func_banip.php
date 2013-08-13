<?php
/*
This file is part of miniBB. miniBB is free discussion forums/message board software, without any warranty. See COPYING file for more details.
Copyright (C) 2010, 2013 Paul Puzyrev. www.minibb.com
Latest File Update: 2013-Feb-26
*/
if (!defined('INCLUDED776')) die ('Fatal error.');

$allowedToBan=TRUE;
if(isset($excludeBanning) and in_array($user_id, $excludeBanning)) $allowedToBan=FALSE;

if(($user_id==1 or $isMod==1) and $allowedToBan){

if(isset($_GET['step'])) $step=$_GET['step']; elseif(isset($_POST['step'])) $step=$_POST['step']; else $step='';

if($step=='' or $step=='banUsr1' or $step=='banUsr2') $title.=$l_ban;
if($step=='deleteban1' or $step=='deleteban2') $title.=$l_unsetBan;

if($step=='banUsr2'){

$warning='';
foreach($_POST as $key=>$val) $$key=operate_string($val);
if (preg_match("/^[0-9.+]+$/", $banip) and trim($banip)!='0') {
$thisIp=$banip; $thisIpMask=array($banip,$banip,$banip);
if(db_ipCheck($thisIp,$thisIpMask,$user_id)) {
$errorMSG=$l_IpExists;
$correctErr="<a href=\"{$main_url}/{$indexphp}action=banip&amp;banip={$thisIp}\">{$l_back}</a>";
$tmpl=ParseTpl(makeUp('main_warning'));
}
else{
$fs=insertArray(array('banip','banreason'),$Tb);
//$errorMSG=($fs==0?$l_IpBanned:$l_mysql_error);
//$correctErr="<a href=\"{$main_url}/{$indexphp}action=banip\">{$l_ban}</a> {$l_sepr} <a href=\"{$main_url}/{$indexphp}action=banip&amp;step=deleteban1\">{$l_unsetBan}</a>";
$step='deleteban1';
}
}
else{
$warning=$l_incorrectIp;
$step='';
}

}

if($step=='deleteban2'){

$banip=(isset($_POST['banip'])?$_POST['banip']:array());
$i=0;
$row=0;
if (sizeof($banip)>0) {
while (list($key)=each($banip)) {
$delban[$i]=$key;
$i++;
}
$xtr=getClForums($delban,'','','id','or','=');
$row=db_delete($Tb,$xtr);
}

unset($xtr);
$step='deleteban1';

}

if($step=='deleteban1'){

$warning='';
$banipID='';
$bannedIPs='';
if ($banned=db_simpleSelect(0,$Tb,'id,banip,banreason','','','','banip')) {
do {

if(preg_match("#^[0-9]+$#", $banned[1])) {
$t1='<strong>';
$t2='</strong>';
}
else{
$t1='<em>';
$t2='</em>';
}

if(trim($banned[2])!='') $banned[2]='('.$banned[2].')';
$bannedIPs.='<input type="checkbox" name="banip['.$banned[0].']" />'.$t1.'&nbsp;&nbsp;'.$banned[1].$t2.'&nbsp;'.$banned[2]."<br />\n";
}
while($banned=db_simpleSelect(1));
$tmpl=ParseTpl(makeUp('admin_deleteban1'));
}
else {
$errorMSG=$l_noBans;
$correctErr="<a href=\"{$main_url}/{$indexphp}action=banip\">{$l_back}</a>";
$tmpl=ParseTpl(makeUp('main_warning'));
}

}

if($step=='banUsr1' or $step==''){
if(!isset($warning)) $warning='';
$banip=(isset($_GET['banip'])?$_GET['banip']:'');
$tmpl=ParseTpl(makeUp('admin_banusr1'));
}

}
else{
$errorMSG=$l_forbidden; $correctErr=$backErrorLink;
$title.=$l_forbidden; $loginError=1;
$tmpl=ParseTpl(makeUp('main_warning'));
}

echo load_header(); echo $tmpl;

?>