<?php
/*
This file is part of miniBB. miniBB is free discussion forums/message board software, without any warranty. See COPYING file for more details.
Copyright (C) 2004-2006 Paul Puzyrev, Sergei Larionov. www.minibb.net
Copyright (C) 2010, 2013 Paul Puzyrev. www.minibb.com
Latest File Update: 2013-Feb-26
*/
if (!defined('INCLUDED776')) die ('Fatal error.');
$title.=$l_userIP;

$postip=(isset($_GET['postip'])?operate_string($_GET['postip']):'');

/*
$avMods=array();
foreach($mods as $k=>$v) if(is_array($v)) foreach($v as $vv) if(!in_array($vv,$avMods)) $avMods[]=$vv;
*/

if ($user_id==1 or $isMod==1) {
$listUsers='';
$l_usersIPs=$l_usersIPs." ".$postip;

if ($row=db_simpleSelect(0,$Tp,'DISTINCT poster_name, poster_id','poster_ip','=',$postip,'poster_name')) {
$listUsers.="<ul>";
do {
$lnk1=($row[1]!=0?"<a href=\"{$main_url}/{$indexphp}action=userinfo&amp;user={$row[1]}\">":'');
$lnk2=($row[1]!=0?'</a>':'');
$listUsers.="<li class=\"limbb\">{$lnk1}{$row[0]}{$lnk2}</li>";
}
while($row=db_simpleSelect(1));
$listUsers.="</ul>";
}
else $listUsers="<span class=\"txtNr\">&nbsp;{$l_userNoIP}<br /><br /></span>";
}

$allowedToBan=TRUE;
if(isset($excludeBanning) and in_array($user_id, $excludeBanning)) $allowedToBan=FALSE;

if(($user_id==1 or $isMod==1) and $allowedToBan){
$banLink="<span class=\"txtSm\"><br /><a href=\"{$main_url}/{$indexphp}action=banip&amp;banip={$postip}\">{$l_ban}</a><br /><a href=\"{$main_url}/{$indexphp}action=banip&amp;step=deleteban1\">{$l_unsetBan}</a></span>";
}
else $banLink='';

echo load_header(); echo ParseTpl(makeUp('tools_userips')); return;
?>