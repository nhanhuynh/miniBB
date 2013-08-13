<?php
/*
This file is part of miniBB. miniBB is free discussion forums/message board software, without any warranty. See COPYING file for more details.
Copyright (C) 2011 Paul Puzyrev. www.minibb.com
Latest File Update: 2011-Oct-28
*/
if (!defined('INCLUDED776')) die ('Fatal error.');

if(!isset($_GET['chstat'])) die('Fatal error.'); else $sticky=(int)$_GET['chstat'];

if ($logged_admin==1 or $isMod==1) {

if(($sticky==2 and isset($disableSuperSticky)) or ($sticky==2 and $logged_admin==0)) $sticky=0;

if(updateArray(array('sticky'),$Tt,'topic_id',$topic)>0) $errorMSG=(($sticky>0)?$l_topicSticked:$l_topicUnsticked);
else $errorMSG=$l_itseemserror;

if($sticky==0){
if(isset($mod_rewrite) and $mod_rewrite) $urlp=addTopicURLPage(genTopicURL($main_url, $forum, '#GET#', $topic, '#GET#'), PAGE1_OFFSET+1);
else $urlp="{$main_url}/{$indexphp}action=vthread&amp;forum={$forum}&amp;topic={$topic}";
}
elseif($sticky==2) $urlp="{$main_url}/{$startIndex}";
else {
if(isset($mod_rewrite) and $mod_rewrite) $urlp=addForumURLPage(genForumURL($main_url, $forum, '#GET#'), PAGE1_OFFSET+1);
else $urlp="{$main_url}/{$indexphp}action=vtopic&amp;forum={$forum}";
}

$correctErr="<a href=\"{$urlp}\">{$l_back}</a>";
}
else {
$errorMSG=$l_forbidden; $correctErr=$backErrorLink;
}

$title.=$errorMSG;
echo load_header(); echo ParseTpl(makeUp('main_warning')); return;
?>