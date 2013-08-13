<?php
/*
This file is part of miniBB. miniBB is free discussion forums/message board software, without any warranty. See COPYING file for more details. Copyright (C) 2004-2009 Paul Puzyrev, Sergei Larionov. www.minibb.com
Latest File Update: 2011-Jun-21
*/
if (!defined('INCLUDED776')) die ('Fatal error.');
if (!defined('NOFOLLOW')) $nof=' rel="nofollow"'; else $nof='';

$unset=array('num_current_topics', 'num_current_posts', 'num_archived_posts', 'num_archived_topics');
for($i=0;$i<sizeof($unset);$i++) if(isset(${$unset[$i]})) { ${$unset[$i]}=''; unset(${$unset[$i]}); }

$USERINFO='';

$user=(isset($_GET['user'])?(integer)$_GET['user']+0:0);

$blockedMod=FALSE;
foreach($mods as $k=>$v) { if(in_array($user,$v)) { $blockedMod=TRUE; break; } }

$canBlock=TRUE;
if($user_id!=1 and $isMod==1 and $blockedMod) $canBlock=FALSE;
if($canBlock and ($user_id==1 or $isMod==1) and $user!=1 and $user_id!=$user and isset($_GET['activity']) and ($_GET['activity']==1 or $_GET['activity']==0)){
$$dbUserAct=$_GET['activity'];
updateArray(array($dbUserAct),$Tu,$dbUserId,$user);
//remove emails from subscriptions when making the user inactive
if($_GET['activity']==0) db_delete($Ts, 'user_id', '=', $user);
}

$usEmail='';
if(isset($directEmailEnabled)){
if(($user_id==0 and $directEmailGuests) or $user_id>0) $usEmail='<a href="'.$indexphp.'action=senddirect&amp;user='.$user.'">'.$l_sendDirect.'</a>';
}

if(!function_exists('parseUserInfo_username')){
function parseUserInfo_username($val){
if(!defined('ARCHIVE')) return $val;
else return "<a href=\"{$GLOBALS['forums_url']}/{$GLOBALS['indexphp']}action=userinfo&amp;user={$GLOBALS['user']}\">{$val}</a>";
}
}

if(!function_exists('parseUserInfo_user_regdate')){
function parseUserInfo_user_regdate($val){
if(strstr($val,'-')) return convert_date($val); else return convert_date(date('Y-m-d H:i:s',$val));
}
}

if(!function_exists('parseUserInfo_user_email')){
function parseUserInfo_user_email($val){
if ($GLOBALS['row'][3]!=1) return $GLOBALS['usEmail']; elseif($GLOBALS['user_id']>0) return '<a href="mailto:'.$val.'">'.$val.'</a>'; else return '';
}
}

if(!function_exists('parseUserInfo_user_website')){
function parseUserInfo_user_website($val){
if ($val!='' and $GLOBALS['user_id']>0) return '<a href="'.$val.'" target="_blank"'.$GLOBALS['nof'].'>'.$val.'</a>';
else return $val;
}
}

if(!function_exists('parseUserInfo_num_posts')){
function parseUserInfo_num_posts($val){

$coreVal=$val-$GLOBALS['row'][10];

if($coreVal=='0') return '';
else{
if(!isset($GLOBALS['archives'])) {
$newVal=parseStatsNum($coreVal);
}

else{
if(!defined('ARCHIVE')){
$liveVal=$GLOBALS['row'][13]-$GLOBALS['row'][12];
$arcVal=$coreVal-$liveVal;
if($arcVal==0){
//that would mean user have no archived posts, that's why no need to display them
$newVal=parseStatsNum($coreVal);
}
else{
$newVal="{$GLOBALS['l_totalTP']}: ".parseStatsNum($coreVal)." / {$GLOBALS['l_liveTP']}: ".parseStatsNum($liveVal)." / <a href=\"{$GLOBALS['main_url']}/{$GLOBALS['indexphp']}action=archives&amp;user={$GLOBALS['user']}\" rel=\"nofollow\">{$GLOBALS['l_archivedTP']}</a>: ".parseStatsNum($arcVal);
}
}
else{
$newVal="{$GLOBALS['l_totalTP']}: ".parseStatsNum($coreVal)." / {$GLOBALS['l_archiveTP']}: ".parseStatsNum($GLOBALS['num_current_posts']);
}
}
return $newVal;
}
}
}

if(!function_exists('parseUserInfo_num_topics')){
function parseUserInfo_num_topics($val){

if($val=='0') return '';
else{
if(!isset($GLOBALS['archives'])) {
$newVal=parseStatsNum($val);
}
else{
if(!defined('ARCHIVE')){
$liveVal=$GLOBALS['row'][12];
$arcVal=$val-$liveVal;
if($arcVal==0){
//that would mean user have no archived topics, that's why no need to display them
$newVal=parseStatsNum($val);
}
else{
$newVal="{$GLOBALS['l_totalTP']}: ".parseStatsNum($val)." / {$GLOBALS['l_liveTP']}: ".parseStatsNum($liveVal)." / <a href=\"{$GLOBALS['main_url']}/{$GLOBALS['indexphp']}action=archives&amp;user={$GLOBALS['user']}\" rel=\"nofollow\">{$GLOBALS['l_archivedTP']}</a>: ".parseStatsNum($arcVal);
}
}
else{
$newVal="{$GLOBALS['l_totalTP']}: ".parseStatsNum($val)." / {$GLOBALS['l_archiveTP']}: ".parseStatsNum($GLOBALS['num_current_topics']);
}
}
return $newVal;
}
}
}

$savedFields=array();

$num_current_topics=0;
$num_current_posts=0;

$addFieldsGen=array('user_icq','user_website','user_occ','user_from','user_interest','num_topics','num_posts');

if(isset($archives)){
$addFieldsGen[]='num_live_topics';
$addFieldsGen[]='num_live_posts';
}

if(!defined('ARCHIVE')) {
//$addFieldsGen[]=$dbUserSheme['num_topics'][1]; $addFieldsGen[]=$dbUserSheme['num_posts'][1];
}
else{

//$addFieldsGen[]=$dbUserSheme['num_live_topics'][1];
//$addFieldsGen[]=$dbUserSheme['num_live_posts'][1];

if($ress=db_simpleSelect(0, $Taus, 'num_topics, num_posts', 'user_id', '=', $user, '', '', 'archive_id', '=', ARCHIVE)) {
$num_current_topics=$ress[0];
$num_current_posts=$ress[1]-$num_current_topics;
}

}

//$addFieldsGen=array('user_icq','user_website','user_occ','user_from','user_interest');

$addFd='';
$addCustomFd='';
foreach($addFieldsGen as $k=>$v) if(isset($dbUserSheme[$v][1])) $addFd.=','.$dbUserSheme[$v][1]; else $addFd.=',null';
foreach($dbUserSheme as $k=>$v) if(strstr($k,'user_custom')) $addCustomFd.=','.$v[1];

$sqle=$dbUserAct.','.$dbUserSheme['username'][1].','.$dbUserDate.','.$dbUserSheme['user_viewemail'][1].','.$dbUserSheme['user_email'][1].$addFd.$addCustomFd;

if ($row=db_simpleSelect(0,$Tu,$sqle,$dbUserId,'=',$user)) {

$usrCell=makeUp('main_user_info_cell');

$infLn=0;
foreach($l_usrInfo as $key=>$val) if($key>$infLn) $infLn=$key;
$infLn++;

$sqlEx=explode(',',$sqle);

for($i=1; $i<$infLn; $i++){
if (isset($l_usrInfo[$i]) and $row[$i]!='') {
$ix=$sqlEx[$i];

if(function_exists('parseUserInfo_'.$ix)) $whatValue=call_user_func('parseUserInfo_'.$ix,$row[$i]);
else $whatValue=$row[$i];
if(!isset($customProfileList) and $whatValue!='') { $what=$l_usrInfo[$i]; $USERINFO.=ParseTpl($usrCell); }
elseif(isset($customProfileList) and $whatValue!='') { $savedFields[$ix][0]=$l_usrInfo[$i]; $savedFields[$ix][1]=$whatValue; }
}
}

if(sizeof($savedFields)>0){
foreach($customProfileList as $k){

if(isset($savedFields[$k])) {
$what=$savedFields[$k][0]; $whatValue=$savedFields[$k][1]; $USERINFO.=ParseTpl($usrCell);
}
}
}


$forumNames=array();
if($rw=db_simpleSelect(0,$Tf,'forum_id,forum_name')){
do $forumNames[$rw[0]]=$rw[1];
while($rw=db_simpleSelect(1));
}

/* Latest topics */
if(!defined('NOT_SHOW_LATEST_TOPICS')){

if(!isset($clForumsUsers)) $clForumsUsers=array();
$closedForums=getAccess($clForums, $clForumsUsers, $user_id);
if ($closedForums!='n') $xtr=getClForums($closedForums,'AND','','forum_id','AND','!='); else $xtr='';

$topicAll=array();
if ($lastT=db_simpleSelect(0,$Tt,'topic_id, forum_id, topic_title','topic_poster','=',$user, 'topic_id desc', $viewmaxtopic)) {
$what=$l_userLastTopics;
$whatValue='<ul class="limbb">';
do {
$topicAll[]=$lastT[0];

if(isset($preModerationType) and $preModerationType>0 and isset($premodTopics) and in_array($lastT[0], $premodTopics)) $lastT[2]=$l_topicQueued;

if(isset($mod_rewrite) and $mod_rewrite) $urlp=addTopicURLPage(genTopicURL($main_url, $lastT[1], $forumNames[$lastT[1]], $lastT[0], $lastT[2]), PAGE1_OFFSET+1); else $urlp="{$main_url}/{$indexphp}action=vthread&amp;forum={$lastT[1]}&amp;topic={$lastT[0]}";

$whatValue.="<li><a href=\"{$urlp}\"{$nof}>{$lastT[2]}</a></li>";
}
while ($lastT=db_simpleSelect(1));
$whatValue.='</ul>';
$USERINFO.=ParseTpl($usrCell);
}

}

/* Latest posts */
if(!defined('NOT_SHOW_LATEST_REPLIES')){

if(sizeof($topicAll)>0){

$xtr2=getClForums($topicAll,'AND','','topic_id','AND','!=');
$xtr=$xtr.' '.$xtr2;

}//are topics

$topicAll=array();
$postsAll=array();
$num=1;
if($ls=db_simpleSelect(0,$Tp,'topic_id,post_id','poster_id','=',$user,'post_id DESC')){
do if(!in_array($ls[0],$topicAll)) { $topicAll[]=$ls[0]; $postsAll[$ls[0]]=$ls[1]; $num++; }
while($ls=db_simpleSelect(1) AND $num<=$viewmaxtopic);
}

$xtr=getClForums($topicAll,'where','','topic_id','OR','=');

$topicVals=array();

if(sizeof($topicAll)>0 and $lastT=db_simpleSelect(0,$Tt,'topic_id, forum_id, topic_title','','','','topic_last_post_id DESC')){
do {
if(isset($preModerationType) and $preModerationType>0 and isset($premodTopics) and in_array($lastT[0], $premodTopics)) $lastT[2]=$l_topicQueued;

$topicVals[$lastT[0]]="<li><a href=\"{$main_url}/{$indexphp}action=search&amp;loc=1&amp;forum={$lastT[1]}&amp;topic={$lastT[0]}&amp;page={$postsAll[$lastT[0]]}\" rel=\"nofollow\">{$lastT[2]}</a></li>";
}
while ($lastT=db_simpleSelect(1));
}

if(sizeof($postsAll)>0){
$what=$l_userLastPosts;
$whatValue='<ul class="limbb">';
foreach($postsAll as $key=>$val){
$whatValue.=$topicVals[$key];
}
$whatValue.='</ul>';
$USERINFO.=ParseTpl($usrCell);
}

}

/* Activities */
if(!defined('NOT_SHOW_ACTIVITIES')){

$closedForums=getAccess($clForums, $clForumsUsers, $user_id);
if ($closedForums!='n') $xtr=getClForums($closedForums,'AND','','forum_id','AND','!='); else $xtr='';

$what=$l_usrInfoActivities;

$forums=array();
$forumIds=array();
if($rw=db_simpleSelect(0,$Tp,'forum_id','poster_id','=',$user)){
do {
if(!isset($forums[$rw[0]])) $forums[$rw[0]]=1; else $forums[$rw[0]]++;
if(!in_array($rw[0],$forumIds)) $forumIds[]=$rw[0];
}
while($rw=db_simpleSelect(1));

asort($forums,SORT_NUMERIC);
$forums=array_reverse($forums,TRUE);

//$xtr=getClForums($forumIds,'where','','forum_id','OR','=');

$userID=$user+0;
$key2='';
$whatValue='';
$tpl=makeUp('stats_bar');
if(sizeof($forumNames)>0){
foreach($forums as $k=>$val){
if(!isset($vMax)) $vMax=$val;
$stats_barWidth=round(100*($val/$vMax));

if(isset($mod_rewrite) and $mod_rewrite) $urlp=addForumURLPage(genForumURL($main_url, $k, $forumNames[$k]), PAGE1_OFFSET+1); else $urlp="{$main_url}/{$indexphp}action=vtopic&amp;forum={$k}";

if($stats_barWidth>$stats_barWidthLim) $key="<a href=\"{$urlp}\" {$nof}>{$forumNames[$k]}</a>";
else{
$key2="<a href=\"{$urlp}\" {$nof}>{$forumNames[$k]}</a>";
$key="<a href=\"{$urlp}\" {$nof}>...</a>";
}
$val=parseStatsNum($val);
$whatValue.=ParseTpl($tpl);
}
}

$USERINFO.=ParseTpl($usrCell);
}//if posts

}

if($user>1 and ($user_id==1 or ($isMod==1 and $user_id!=$user and !$blockedMod) ) ){
/* activity link */
$act=$row[0]; $actnew=($act==0?1:0);
$mes1=($act==0?$l_no:$l_yes);
$mes2=($act==0?$l_yes:$l_no);
$what=$l_member; $whatValue="{$mes1} [<a href=\"{$main_url}/{$indexphp}action=userinfo&amp;user={$user}&amp;activity={$actnew}\">{$mes2}</a>]";
$USERINFO.=ParseTpl($usrCell);

/* edit profile link */

$what=$l_editPrefs;
$whatValue="<a href=\"{$main_url}/{$indexphp}action=prefs&amp;adminUser={$user}\">&gt;&gt;&gt;</a>";
$USERINFO.=ParseTpl($usrCell);
}

/* finally */

$userInfo=$l_about.' &ldquo;'.$row[1].'&rdquo;';
$title.=$l_about.' '.$row[1];
$tpl=makeUp('main_user_info'); 

}
else {
$title.=$l_userNotExists; $errorMSG=$l_userNotExists; $correctErr=$backErrorLink;
$tpl=makeUp('main_warning');
}

echo load_header(); echo ParseTpl($tpl); return;
?>