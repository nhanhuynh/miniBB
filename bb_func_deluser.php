<?php
/*
This file is part of miniBB. miniBB is free discussion forums/message board software, without any warranty. See COPYING file for more details.
Copyright (C) 2013 Paul Puzyrev. www.minibb.com
Latest File Update: 2013-Feb-19
*/
if (!defined('INCLUDED776')) die ('Fatal error.');

$title.=$l_removeUser;

if(isset($_GET['step'])) $step=$_GET['step']; elseif(isset($_POST['step'])) $step=$_POST['step']; else $step='';
if(isset($_GET['user'])) $user=(int)$_GET['user']; elseif(isset($_POST['user'])) $user=(int)$_POST['user']; else $user=0;

if($user>1 and $row=db_simpleSelect(0, $Tu, $dbUserSheme['username'][1], $dbUserId, '=', $user)) {
$uname=$row[0];
$unameTitle='&ldquo;'.$row[0].'&rdquo;';
$title.=' &quot;'.$uname.'&quot;'; 
}
else {
$uname='';
}

//print_r($_POST);

$allowedToDelete=TRUE;
if(isset($excludeDeleteUsers) and in_array($user_id, $excludeDeleteUsers)) $allowedToDelete=FALSE;

if($step=='remove' and $uname=='') $allowedToDelete=FALSE; //that means user's profile was not found...

//admin is allowed to delete all profiles
//moderators can delete all profiles, except for admin's and other moderators
if($user==1 or ($user_id!=1 and checkModerator($mods, $user))) $allowedToDelete=FALSE;

if($step=='' and $user==0) $user='';

if(($user_id==1 or $isMod==1) and $allowedToDelete){

if($step==''){
if($user>1) {
$editable_id='disabled="disabled"';
$user_field="<input type=\"hidden\" name=\"user\" value=\"{$user}\" />";
}
$tmpl=ParseTpl(makeUp('admin_removeuser1'));
}
elseif($step=='remove'){
if($csrfchk=='' or $csrfchk!=$_COOKIE[$cookiename.'_csrfchk']) die('Can not proceed: possible CSRF/XSRF attack!');

if(isset($_POST['keepblocked']) and (int)$_POST['keepblocked']==1){
${$dbUserAct}=0;
$updArray=array($dbUserAct);
if(isset($_POST['removemessages'])) {
$updArray[]=$dbUserSheme['num_topics'][1]; ${$dbUserSheme['num_topics'][1]}=0;
$updArray[]=$dbUserSheme['num_posts'][1]; ${$dbUserSheme['num_posts'][1]}=0;
}
updateArray($updArray, $Tu, $dbUserId, $user);
$warning=$l_userDeactivated;
}
else{
if(db_delete($Tu, $dbUserId, '=', $user)) $warning=$l_userDeleted." (".$uname.")"; else $warning=$l_userNotDeleted." (".$uname.")";
}

/*Delete from sendMails*/
db_delete($Ts,'user_id','=',$user);

if(isset($_POST['removemessages'])) {
//set_time_limit(0);

$aff=0;
/*Deleting user messages from posts and topics table. Topics - delete also all associated posts*/
if($rrr=db_simpleSelect(0,$Tt,'topic_id','topic_poster','=',$user)){
$ord='';
do $ord.="topic_id={$rrr[0]} or "; while($rrr=db_simpleSelect(1));
$ord=substr($ord,0,strlen($ord)-4);
$aff+=db_delete($Tp,$ord,'','');
$aff+=db_delete($Tt,$ord,'','');
}

/* Posts only */
if($rrr=db_simpleSelect(0,$Tp,'DISTINCT topic_id','poster_id','=',$user)){
do{
$topic_id=$rrr[0];
$aff+=db_delete($Tp,'topic_id','=',$topic_id,'poster_id','=',$user);
db_calcAmount($Tp,'topic_id',$topic_id,$Tt,'posts_count');
$RES1=$result;
$CNT1=$countRes;
if($lp=db_simpleSelect(0,$Tp,'post_id, post_time, poster_name','topic_id','=',$topic_id,'post_id DESC',1)){
$topic_last_post_id=$lp[0];
$topic_last_post_time=$lp[1];
$topic_last_poster=$lp[2];
$fs=updateArray(array('topic_last_post_id', 'topic_last_post_time', 'topic_last_poster'),$Tt,'topic_id',$topic_id);
$aff+=$fs;
}
$result=$RES1;
$countRes=$CNT1;
}
while($rrr=db_simpleSelect(1));
}

/* Update forums posts, topics amount */
if($res=db_simpleSelect(0,$Tf,'forum_id')){
do{
db_calcAmount($Tp,'forum_id',$res[0],$Tf,'posts_count');
db_calcAmount($Tt,'forum_id',$res[0],$Tf,'topics_count');
}
while($res=db_simpleSelect(1));
}

if ($aff>0) $warning.="<br />".$l_userMsgsDeleted; else $warning.="<br />".$l_userMsgsNotDeleted;
}
else {
/* Make user posts as Guest in Live forums */
$aff=0;
$poster_id=0; $topic_poster=0;
$aff+=updateArray(array('poster_id'),$Tp,'poster_id',$user);
$aff+=updateArray(array('topic_poster'),$Tt,'topic_poster',$user);
if ($aff>0) $warning.="<br />".$l_userUpdated0; else $warning.="<br />".$l_userNotUpdated0;
}

/* Make user posts as Guest posts in archives */
if(isset($archives)){
$aff=0;
$poster_id=0;
$topic_poster=0;

foreach($archives as $key=>$val){
$Tp_arc=$key.'_'.$Tp;
$Tt_arc=$key.'_'.$Tt;
$aff+=updateArray(array('poster_id'),$Tp_arc,'poster_id',$user);
$aff+=updateArray(array('topic_poster'),$Tt_arc,'topic_poster',$user);
}

if ($aff>0) $warning.="<br />".$l_arcUserUpdated; else $warning.="<br />".$l_arcUserNotUpdated;
}

$errorMSG=$warning;
$correctErr='';
$tmpl=ParseTpl(makeUp('main_warning'));

}

}

else{
$errorMSG=$l_forbidden; $correctErr=$backErrorLink;
$loginError=1;
$tmpl=ParseTpl(makeUp('main_warning'));
}

echo load_header(); echo $tmpl;

?>