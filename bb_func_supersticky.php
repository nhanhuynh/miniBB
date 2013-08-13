<?php
/*
This file is part of miniBB. miniBB is free discussion forums/message board software, without any warranty. See COPYING file for more details.
Copyright (C) 2012 Paul Puzyrev. www.minibb.com
Latest File Update: 2012-Mar-28
*/
if (!defined('INCLUDED776')) die ('Fatal error.');

$topicTmp=$topic;
$forumTmp=$forum;
unset($xtr);
$superStickyModule=TRUE;

if($action=='' and $page==PAGE1_OFFSET+1) {
if($startPageModern) $sp=makeUp('main_modern_lcell'); else $sp=makeUp('main_last_discuss_cell');
}
else $sp=makeUp('main_topics_cell');

$specialThreads='';
$specialThreadsArr=array();

$bg='tbCel3';
//Get info about topics
if(isset($clForumsUsers)) $closedForums=getAccess($clForums, $clForumsUsers, $user_id); else $closedForums='n';
if($closedForums!='n') $xtr=getClForums($closedForums,'and','','forum_id','and','!='); else $xtr='';

$collst=array();
if(isset($textLd)) $lPostst=array();

if($colst=db_simpleSelect(0, $Tt, 'topic_id, topic_title, topic_poster, topic_poster_name, topic_time, topic_status, posts_count, sticky, topic_views, topic_last_post_id, topic_last_post_time, topic_last_poster, forum_id','sticky','=','2','topic_id DESC')) {
do {
if(isset($textLd)) $lPostst[]=$colst[9];
//else { if($user_sort==0) $lPosts[]=$cols[9]; else $lPosts[]=$cols[0]; }
//$colls[]=array($cols[0], $cols[1], $cols[2], $cols[3], $cols[4], $cols[5], $cols[6], $cols[7], $cols[8], $cols[9], $cols[10], $cols[11]);
$collst[]=$colst;
}
while($colst=db_simpleSelect(1));
}

if(isset($textLd)){

if(sizeof($lPostst)>0) {
if($user_sort==0) { $ordb='post_id'; $ordSql='DESC'; } else { $ordb='topic_id'; $ordSql='ASC'; }
$xtr=getClForums($lPostst, 'where', '', $ordb, 'or', '=');
}
else $xtr='';

if($xtr!=''){
if($row=db_simpleSelect(0, $Tp, 'poster_id, poster_name, post_time, topic_id, post_text, post_id', '', '', '', 'post_id '.$ordSql))
do
if(!isset($pValst[$row[3]])) $pValst[$row[3]]=array($row[0],$row[1],$row[2],$row[4],$row[5]); else continue;
while($row=db_simpleSelect(1));
unset($xtr);
}
}

//print_r($collst);

//if($row=db_simpleSelect(0, $Tt, 'topic_id, forum_id, topic_title, topic_poster, topic_poster_name, topic_time, topic_views, topic_last_post_id, posts_count, topic_last_post_time, topic_last_poster', 'sticky', '=', '2', '', '', 'forum_id', '!=', $forum)){
foreach($collst as $colst){
//do{

//print_r($colst);

list($topic, $topic_title, $topicTitle, $numReplies, $topic_views, $topicAuthor, $whenPosted, $topic_last_post_id, $lm, $forum) = array($colst[0], $colst[1], $colst[1], $colst[6], $colst[8], $colst[3], convert_date($colst[4]), $colst[9], $colst[9], $colst[12]);


$specialThreadsArr[]=$topic;

//$lPosts[]=$row[7];

$topic_reverse='';
if(isset($themeDesc) and in_array($topic,$themeDesc)) {
$vv=TRUE; $vvpn=TRUE;
$topic_reverse="<img src=\"{$main_url}/img/topic_reverse.gif\" style=\"vertical-align:middle\" alt=\"\" />&nbsp;";
}
else {
$vv=FALSE;
$vvpn=FALSE;
}

if($numReplies<$viewmaxreplys or $vvpn) $pagelm=PAGE1_OFFSET+1; else $pagelm=ceil(($numReplies+1)/$viewmaxreplys)+PAGE1_OFFSET;

if($action=='') $fName=$fTitle[$forum]; else $fName=$forumsArray[$forum][0];

if(isset($mod_rewrite) and $mod_rewrite) {
$urlp=genTopicURL($main_url, $forum, $fName, $topic, $topic_title);
$urlForum=addForumURLPage(genForumURL($main_url, $forum, $fName), PAGE1_OFFSET+1);
$urlType='Topic';
$lmurl1=addTopicURLPage($urlp, $pagelm)."#msg{$lm}";
$linkToTopic=addTopicURLPage($urlp, PAGE1_OFFSET+1);
}
else{
$urlp="{$main_url}/{$indexphp}action=vthread&amp;forum=$forum&amp;topic=$topic";
$urlForum="{$main_url}/{$indexphp}action=vtopic&amp;forum=$forum";
$urlType='Gen';
$lmurl1="{$main_url}/{$indexphp}action=vthread&amp;forum={$forum}&amp;topic={$topic}&amp;page={$pagelm}#msg{$lm}";
$linkToTopic="{$main_url}/{$indexphp}action=vthread&amp;forum={$forum}&amp;topic={$topic}";
}

if($numReplies>=1) $numReplies-=1;

if(!isset($lastPostIcon)) $lastPostIcon="<img src=\"{$main_url}/img/s.gif\" style=\"width:12px;height:9px;padding-top:6px\" alt=\"{$l_lastAuthor}\" title=\"{$l_lastAuthor}\" />";
if($numReplies>0) $LinkToLastPostInTopic='<a href="'.$lmurl1.'">'.$lastPostIcon.'</a>';
else $LinkToLastPostInTopic='';

$topicIcon="<img src=\"{$main_url}/img/topic_supersticky.gif\" style=\"width:{$fIconWidth}px;height:{$fIconHeight}px;vertical-align:middle\" alt=\"{$topic_title}\" title=\"{$topic_title}\" />";

$pageNavCell=pageNav(PAGE1_OFFSET+1,$numReplies+1,$urlp,$viewmaxreplys,TRUE,$urlType);

if(isset($pValst[$topic][0])) $lastPosterID=$pValst[$topic][0]; else $lastPosterID='N/A';

if($numReplies>0 and isset($colst[11]) and $colst[11]!='') $lastPoster=$colst[11];
elseif($numReplies>0 and isset($pValst[$topic][1])) $lastPoster=$pValst[$topic][1];
else $lastPoster='&mdash;';

if($numReplies>0 and isset($colst[10])) $lastPostDate=convert_date($colst[10]);
elseif($numReplies>0 and isset($pValst[$topic][2])) $lastPostDate=convert_date($pValst[$topic][2]);
else $lastPostDate='';

if(isset($textLd) and isset($pValst[$topic][3])){
$lptxt=($textLd==1?$pValst[$topic][3]:strip_tags(str_replace('<br />', ' ', $pValst[$topic][3])));
if(!isset($preModerationType) or $preModerationType==0) $lastPostText=$lptxt;
elseif($preModerationType>0 and isset($premodTopics) and in_array($colst[0], $premodTopics)) $lastPostText=$l_postQueued;
elseif($preModerationType>0 and isset($premodPosts) and in_array($pValst[$topic][4], $premodPosts)) $lastPostText='';
else $lastPostText=$lptxt;
}

$numReplies=parseStatsNum($numReplies);
$topic_views=parseStatsNum($topic_views);

if(function_exists('parseTopic')) parseTopic();

$specialThreads.=ParseTpl($sp);

//}
//while($row=db_simpleSelect(1));

}

unset($xtr);
unset($superStickyModule);

$forum=$forumTmp;
$topic=$topicTmp;

?>