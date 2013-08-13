<?php
/*
This file is part of miniBB. miniBB is free discussion forums/message board software, without any warranty.
See COPYING file for more details.
Copyright (C) 2004-2006, 2011 Paul Puzyrev, Sergei Larionov. www.minibb.net
Copyright (C) 2012-2013 Paul Puzyrev. www.minibb.com
Latest File Update: 2013-May-22
*/
if (!defined('INCLUDED776')) die ('Fatal error.');
if (!defined('NOFOLLOW')) $nof=' rel="nofollow"'; else $nof='';

function gen_vthread_url($forum, $forum_title, $topic, $topic_title, $page){
if(isset($GLOBALS['mod_rewrite']) and $GLOBALS['mod_rewrite']) return addTopicURLPage(genTopicURL($GLOBALS['main_url'], $forum, $forum_title, $topic, $topic_title), $page);
else return addGenURLPage($GLOBALS['main_url'].'/'.$GLOBALS['indexphp'].'action=vthread&amp;forum='.$forum.'&amp;topic='.$topic, $page);
}

if(isset($_GET['days'])) $days=(string) $_GET['days']; elseif(isset($_POST['days'])) $days=(string) $_POST['days']; else $days='0000';
if(isset($_GET['lst'])) $lst=(integer) $_GET['lst']+0; elseif(isset($_POST['lst'])) $lst=(integer) $_POST['lst']+0; else $lst=0;
if(isset($_GET['top'])) $top=(integer) $_GET['top']+0;  elseif(isset($_POST['top'])) $top=(integer) $_POST['top']+0; else $top=0;

$days=substr($days,0,4)+0;
if($days<=0) $days=$defDays;

if(!isset($clForumsUsers)) $clForumsUsers=array();
$closedForums=getAccess($clForums, $clForumsUsers, $user_id);
$extra=($closedForums!='n'?1:0);

if (isset($topStats) and in_array($topStats,array(1,2,3,4))) $tKey=$topStats; else $tKey=4;

$timeLimit=date('Y-m-d H:i:s', time()-$days*86400);

if(!defined('ARCHIVE')){
$tuW='topic_last_post_time';
$tuE='>=';
$tuP=$timeLimit;
//if($lst==3) $xtrp='WHERE'; else 
$xtrp='AND';
}
else{
$tuW='';
$tuE='';
$tuP='';
$xtrp='WHERE';
}

$stats_barWidth='';$statsOpt='';$list_stats_viewed='';$list_stats_popular='';$list_stats_aUsers='';$list_stats_forums='';

$lstLim=3;

$key2='';
if($top+1>$tKey) $top=$tKey-1;
if($lst>$lstLim) $lst=$lstLim;
function fTopa($top){
if($top==0) $topa=5;
elseif($top==1) $topa=10;
elseif($top==2) $topa=20;
else $topa=40;
return $topa;
}

$statsTop=' . ';
for($i=0;$i<$tKey;$i++) $statsTop.=($i<>$top?'<a href="'.$main_url.'/'.$indexphp.'action=stats&amp;top='.$i.'&amp;days='.$days.'&amp;lst='.$lst.'"'.$nof.'>'.$l_stats_top.' '.fTopa($i).'</a> . ':$l_stats_top.' '.fTopa($i).' . ');
$makeLim=fTopa($top);

/* forum information */
$forumNames=array();
if($row=db_simpleSelect(0, $Tf, 'forum_id, forum_name, forum_icon')){
do $forumNames[$row[0]]=array($row[1], $row[2]);
while($row=db_simpleSelect(1));
}

/* lst: 0 - popular, 1 - viewed, 2 - users */

if(!$enableViews) $l_stats_viewed='';
$statsOptL=array($l_stats_popular,$l_stats_viewed,$l_stats_aUsers,$l_stats_pop_forums);

for($i=0;$i<=$lstLim;$i++){
if($i!=$lst and $statsOptL[$i]!='') $statsOpt.=' / <b><a href="'.$main_url.'/'.$indexphp.'action=stats&amp;top='.$top.'&amp;days='.$days.'&amp;lst='.$i.'"'.$nof.'>'.$statsOptL[$i].'</a></b>';
elseif($statsOptL[$i]!='') $statsOpt.= ' / <b>'. $statsOptL[$i].'</b>';
}

$tpl=makeUp('stats_bar');

if($lst==0 or $lst==3){
$xtr=($extra==1?getClForums($closedForums,$xtrp,'','forum_id','AND','!='):'');
}
elseif($enableViews&&$lst==1){
$xtr=($extra==1?getClForums($closedForums,$xtrp,'','forum_id','AND','!='):'');
}

if($lst==0&&$cols=db_simpleSelect(0,$Tt,'topic_id, topic_title, forum_id, posts_count',$tuW,$tuE,$tuP,'posts_count DESC',$makeLim)){
do{

if(isset($preModerationType) and $preModerationType>0 and isset($premodTopics) and in_array($cols[0], $premodTopics)) $cols[1]=$l_topicQueued;

$val=$cols[3]-1;
if(!isset($vMax)) $vMax=$val;
if ($vMax!=0) $stats_barWidth=round(100*($val/$vMax));
if($stats_barWidth>$stats_barWidthLim) $key='<a href="'.gen_vthread_url($cols[2], $forumNames[$cols[2]][0], $cols[0], $cols[1], PAGE1_OFFSET+1).'"'.$nof.'>'.$cols[1].'</a>';
else{
$key2='<a href="'.gen_vthread_url($cols[2], $forumNames[$cols[2]][0], $cols[0], $cols[1], PAGE1_OFFSET+1).'"'.$nof.'>'.$cols[1].'</a>';
$key='<a href="'.gen_vthread_url($cols[2], $forumNames[$cols[2]][0], $cols[0], $cols[1], PAGE1_OFFSET+1).'"'.$nof.'>...</a>';
}
$val=parseStatsNum($cols[3]-1);
$list_stats_popular.=ParseTpl($tpl);
}
while($cols=db_simpleSelect(1));
}

elseif($lst==2 && $cols=db_simpleSelect(0,$Tu,$dbUserId.', '.$dbUserSheme['username'][1].' ,'.$dbUserSheme['num_posts'][1],$dbUserId,'!=','1',$dbUserSheme['num_posts'][1].' DESC',$makeLim)){
do{
if($cols[0]!=1) {
$val=$cols[2];
if(!isset($vMax)) $vMax=$val;
if ($vMax!=0) $stats_barWidth=round(100*($val/$vMax));
if($stats_barWidth>$stats_barWidthLim) $key='<a href="'.$main_url.'/'.$indexphp.'action=userinfo&amp;user='.$cols[0].'"'.$nof.'>'.$cols[1].'</a>';
else{
$key2='<a href="'.$main_url.'/'.$indexphp.'action=userinfo&amp;user='.$cols[0].'"'.$nof.'>'.$cols[1].'</a>';
$key='<a href="'.$main_url.'/'.$indexphp.'action=userinfo&amp;user='.$cols[0].'"'.$nof.'>...</a>';
}
$val=parseStatsNum($cols[2]);
$list_stats_aUsers.=ParseTpl($tpl);
}
}
while($cols=db_simpleSelect(1));
}

elseif($enableViews&&$lst==1&&$cols=db_simpleSelect(0,$Tt,'topic_id, topic_views, topic_title, forum_id',$tuW,$tuE,$tuP,'topic_views DESC, topic_id DESC',$makeLim,'','','',true)){
do{
if($cols[1]){

if(isset($preModerationType) and $preModerationType>0 and isset($premodTopics) and in_array($cols[0], $premodTopics)) $cols[2]=$l_topicQueued;

if(!isset($vMax)) $vMax=$cols[1];
$val=$cols[1];
$stats_barWidth=round(100*($val/$vMax));
if($stats_barWidth>$stats_barWidthLim) $key='<a href="'.gen_vthread_url($cols[3], $forumNames[$cols[3]][0], $cols[0], $cols[2], PAGE1_OFFSET+1).'"'.$nof.'>'.$cols[2].'</a>';
else{
$key2='<a href="'.gen_vthread_url($cols[3], $forumNames[$cols[3]][0], $cols[0], $cols[2], PAGE1_OFFSET+1).'"'.$nof.'>'.$cols[2].'</a>';
$key='<a href="'.gen_vthread_url($cols[3], $forumNames[$cols[3]][0], $cols[0], $cols[2], PAGE1_OFFSET+1).'"'.$nof.'>...</a>';
}
$val=parseStatsNum($cols[1]);
$list_stats_viewed.=ParseTpl($tpl);
}
else break;
}
while($cols=db_simpleSelect(1));
}

if($lst==3){
if($cols=db_simpleSelect(0,$Tt,'forum_id,posts_count',$tuW,$tuE,$tuP,'posts_count DESC')){
$forumsTop=array();
do{
if(!isset($forumsTop[$cols[0]])) $forumsTop[$cols[0]]=0;
$forumsTop[$cols[0]]+=$cols[1];
}
while($cols=db_simpleSelect(1));
}

if(isset($forumsTop) and is_array($forumsTop)){
arsort($forumsTop);

$ah=0;
foreach($forumsTop as $forumId=>$forumAmnt){
$val=$forumAmnt;
if(!isset($vMax)) $vMax=$val;
if ($vMax!=0) $stats_barWidth=round(100*($val/$vMax));

if(isset($mod_rewrite) and $mod_rewrite) $fUrl=addForumURLPage(genForumURL($main_url, $forumId, $forumNames[$forumId][0]), PAGE1_OFFSET+1);
else $fUrl="{$main_url}/{$indexphp}action=vtopic&amp;forum={$forumId}";

$fName=$forumNames[$forumId][0];
if($stats_barWidth>$stats_barWidthLim) $key='<a href="'.$fUrl.'"'.$nof.'>'.$fName.'</a>';
else{
$key2='<a href="'.$fUrl.'"'.$nof.'>'.$fName.'</a>';
$key='<a href="'.$fUrl.'"'.$nof.'>...</a>';
}
$val=parseStatsNum($forumAmnt);
$forumIconTd="<td><a href=\"{$fUrl}\"><img src=\"{$main_url}/img/forum_icons/{$forumNames[$forumId][1]}\" alt=\"{$fName}\" title=\"{$fName}\" /></a></td>";
$list_stats_forums.=ParseTpl($tpl);
$ah++;
if($ah>=fTopa($top)) break;
}
}

}

unset($xtr);

$numUsers=parseStatsNum(db_simpleSelect(2,$Tu,'count(*)'));

if(isset($archives)){
if(defined('ARCHIVE')) $achive_id=ARCHIVE; else $achive_id='';
if($row=db_simpleSelect(0, $Tas, 'num_topics, num_posts', 'archive_id', '=', $achive_id)) {
$numTopics=$row[0];
$numPosts=$row[1]-$numTopics;
}

if(!defined('ARCHIVE')){
$arcTopics=0;
$arcPosts=0;
if($row=db_simpleSelect(0, $Tas, 'sum(num_topics), sum(num_posts)', 'archive_id', '!=', '')) {
$arcTopics=$row[0];
$arcPosts=$row[1]-$arcTopics;
}
$totalTopics=$numTopics+$arcTopics;
$totalPosts=$numPosts+$arcPosts;
$numTopics=parseStatsNum($numTopics)." / <a href=\"{$archives_url}\">{$l_archivedTP}</a>: ".parseStatsNum($arcTopics)." / {$l_totalTP}: ".parseStatsNum($totalTopics);
$numPosts=parseStatsNum($numPosts)." / <a href=\"{$archives_url}\">{$l_archivedTP}</a>: ".parseStatsNum($arcPosts)." / {$l_totalTP}: ".parseStatsNum($totalPosts);
}
else{
$totalTopics=0;
$totalPosts=0;
if($row=db_simpleSelect(0, $Tas, 'sum(num_topics), sum(num_posts)')) {
$totalTopics=$row[0];
$totalPosts=$row[1]-$totalTopics;
}
$numTopics="{$l_archiveTP}: ".parseStatsNum($numTopics)." / {$l_totalTP}: ".parseStatsNum($totalTopics);
$numPosts="{$l_archiveTP}: ".parseStatsNum($numPosts)." / {$l_totalTP}: ".parseStatsNum($totalPosts);
}


}
else{
$numTopics=parseStatsNum(db_simpleSelect(2,$Tt,'count(*)'));
$numPosts=parseStatsNum(db_simpleSelect(2,$Tp,'count(*)')-$numTopics);
}

$adminInf=db_simpleSelect(2,$Tu,$dbUserSheme['username'][1],$dbUserId,'=',1);
$lastRegUsr=db_simpleSelect(0,$Tu,"{$dbUserId}, {$dbUserSheme['username'][1]}",'','','',"{$dbUserId} DESC",1);

$title=$title.$l_stats;

echo load_header(); 
$trpl=makeUp('stats');
if(defined('ARCHIVE') or $lst==2) $trpl=preg_replace("#<!--forLiveForum-->(.+?)<!--/forLiveForum-->#is", '', $trpl);
echo ParseTpl($trpl);
?>