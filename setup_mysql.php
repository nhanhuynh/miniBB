<?php
/*
This file is part of miniBB. miniBB is free discussion forums/message board software, without any warranty. See COPYING file for more details. Copyright (C) 2004-2006 Paul Puzyrev, Sergei Larionov. www.minibb.com
Latest File Update: 2011-Jun-20
*/
@mysql_connect($DBhost, $DBusr, $DBpwd) or die ('<b>Database/configuration error.</b>');
@mysql_select_db($DBname) or die ('<b>Database/configuration error (DB is missing).</b>');
if(isset($mysql_set_names)) mysql_query('set names binary');

function makeLim($page,$numRows,$viewMax){
$page=pageChk($page,$numRows,$viewMax);
if(intval($numRows/$viewMax)!=0&&$numRows>0){
if ($page>0) return (($page-(PAGE1_OFFSET+1))*$viewMax).','.$viewMax;
else return $viewMax;
}
else return '';
}

function getClForums($closedForums,$more,$prefix,$field,$syntax,$condition){
$xtr=$more.' (';
if($prefix!='') $prefix=$prefix.'.';
$siz=sizeof($closedForums);
foreach($closedForums as $c) {
$xtr.=' '.$prefix.$field.$condition.$c;
$siz--;
if ($siz!=0) $xtr.=' '.$syntax;
}
return $xtr.') ';
}

function db_simpleSelect($sus,$table='',$fields='',$uniF='',$uniC='',$uniV='',$orderby='',$limit='',$uniF2='',$uniC2='',$uniV2='',$and2=true,$groupBy=''){
if(!$sus){
$where='';
if($uniF!='') $where=' WHERE '.$uniF.$uniC."'".$uniV."'";
if($uniF2!='') {
$q=(substr_count($uniV2,'.')>0?'':"'");
$a=($and2?'AND':'WHERE');
$where.=' '.$a.' '.$uniF2.$uniC2.$q.$uniV2.$q;
}
if($limit!='') $limit='limit '.$limit;
if($orderby!='') $orderby='order by '.$orderby;
if($groupBy!='') $groupBy='group by '.$groupBy;
$xtr=(!isset($GLOBALS['xtr'])?'':$GLOBALS['xtr']);
$sql='SELECT '.$fields.' FROM '.$table.$where.' '.$xtr.' '.$groupBy.' '.$orderby.' '.$limit;
//if($sus==0 and function_exists('parseSql')) $sql=parseSql($sql);
//echo "!-- ".$sql." --><br />";
$result=mysql_query($sql);
if($result) {
$GLOBALS['countRes']=mysql_num_rows($result);
$GLOBALS['result']=$result;
}
}
if(($sus==1||(isset($result)&&$result))&&isset($GLOBALS['countRes'])&&$GLOBALS['countRes']>0)  return mysql_fetch_row($GLOBALS['result']);
elseif($sus==2){
$a=(strlen($uniF2)?'AND':'');
$w=(strlen($uniF)||strlen($uniF2)?'WHERE':'');
$xtr=(isset($GLOBALS['xtr'])?$GLOBALS['xtr']:'');
return mysql_result(mysql_query('SELECT '.$fields.' FROM '.$table.' '.$w.' '.$uniF.$uniC.$uniV.' '.$a.' '.$uniF2.$uniC2.$uniV2.' '.$xtr),0);
}
else return FALSE;
}

function insertArray($insertArray,$tabh){
$into=''; $values='';
foreach($insertArray as $ia) {
$iia=$GLOBALS[$ia];
$into.=$ia.',';
$values.=($iia=='now()'?$iia.',':"'".$iia."',");
}
$into=substr($into,0,strlen($into)-1);
$values=substr($values,0,strlen($values)-1);
//echo 'insert into '.$tabh.' ('.$into.') values ('.$values.')';
$res=mysql_query('insert into '.$tabh.' ('.$into.') values ('.$values.')') or die('<p>'.mysql_error().'. Try another name or value.');
$GLOBALS['insres']=mysql_insert_id();
return mysql_errno();
}

function updateArray($updateArray,$tabh,$uniq,$uniqVal){
$into='';
foreach($updateArray as $ia) {
$iia=$GLOBALS[$ia];
$into.=($iia=='now()'?$ia.'='.$iia.',':$ia."='".$iia."',");
}
$into=substr($into,0,strlen($into)-1);
$unupdate=($uniq!=''?' where '.$uniq.'='."'".$uniqVal."'":'');
$res=mysql_query('update '.$tabh.' set '.$into.' '.$unupdate) or die('<p>'.mysql_error().'. Please, try another name or value.');
return mysql_affected_rows();
}

function db_delete($table,$uniF='',$uniC='',$uniV='',$uniF2='',$uniC2='',$uniV2=''){
$where=($uniF!=''?'where '.$uniF.$uniC.$uniV:'');
if($uniF2!='') {
$where.=' AND '.$uniF2.$uniC2.$uniV2;
}
$sql='DELETE FROM '.$table.' '.$where;
$result=mysql_query($sql);
if($result) return mysql_affected_rows();
else return FALSE;
}

function db_ipCheck($thisIp,$thisIpMask,$user_id){
$res=mysql_query("select id, banreason from {$GLOBALS['Tb']} where banip='{$thisIp}' or banip='{$thisIpMask[0]}' or banip='{$thisIpMask[1]}' or banip='{$thisIpMask[2]}' or banip='{$user_id}'");
if($res and mysql_num_rows($res)>0 and $row=mysql_fetch_row($res)) return $row[1]; else return FALSE;
}

function db_inactiveUsers($sus,$what=''){
/*Admin - users that didnt any post */
if(!$sus) {
if(strlen($GLOBALS['makeLim'])>0) $GLOBALS['makeLim']='LIMIT '.$GLOBALS['makeLim'];
$result=mysql_query('select '.$what.' from '.$GLOBALS['Tu'].' LEFT JOIN '.$GLOBALS['Tp'].' ON '.$GLOBALS['Tu'].'.'.$GLOBALS['dbUserId'].'='.$GLOBALS['Tp'].'.poster_id where '.$GLOBALS['Tp'].'.poster_id IS NULL order by '.$GLOBALS['Tu'].'.'.$GLOBALS['dbUserId'].' '.$GLOBALS['makeLim']);
if($result) {
$GLOBALS['countRes']=mysql_num_rows($result);
$GLOBALS['result']=$result;
}
}
if(isset($GLOBALS['countRes']) and $GLOBALS['countRes']>0) return mysql_fetch_row($GLOBALS['result']);
else return FALSE;
}

function db_deadUsers($sus,$less){
/*Admin-dead users*/
if(!$sus){
$GLOBALS['makeLim']=(isset($GLOBALS['makeLim'])?'LIMIT '.$GLOBALS['makeLim']:'');
$sql='select '.$GLOBALS['Tu'].'.'.$GLOBALS['dbUserId'].','.$GLOBALS['Tu'].'.'.$GLOBALS['dbUserSheme']['username'][1].','.$GLOBALS['Tu'].'.'.$GLOBALS['dbUserDate'].','.$GLOBALS['Tu'].'.'.$GLOBALS['dbUserSheme']['user_password'][1].','.$GLOBALS['Tu'].'.'.$GLOBALS['dbUserSheme']['user_email'][1].',max('.$GLOBALS['Tp'].'.post_time) as m, '.$GLOBALS['Tu'].'.'.$GLOBALS['dbUserSheme']['num_posts'][1].' from '.$GLOBALS['Tu'].','.$GLOBALS['Tp'].' where '.$GLOBALS['Tu'].'.'.$GLOBALS['dbUserId'].'='.$GLOBALS['Tp'].'.poster_id group by '.$GLOBALS['Tp'].'.poster_id having m<'."'".$less."' ".$GLOBALS['makeLim'];
$result=mysql_query($sql);
if($result){
$GLOBALS['countRes']=mysql_num_rows($result);
$GLOBALS['result']=$result;
}
}
if(isset($GLOBALS['countRes']) and $GLOBALS['countRes']>0) return mysql_fetch_row($GLOBALS['result']);
else return FALSE;
}

function db_calcAmount($tbName,$tbKey,$tbVal,$setName,$setField,$tbKey2=''){
/* Function to get amount of values from table $tbName by criteria $tbKey=$tbVal; then update table's $setName field $setField by this amount */
if($res=mysql_query('select count(*) from '.$tbName.' where '.$tbKey.'='.$tbVal) and mysql_num_rows($res)>0 and $row=mysql_fetch_row($res)) $amount=$row[0];
else $amount=0;
if($tbKey2=='') $tbKey2=$tbKey;
mysql_query('update '.$setName.' set '.$setField.'='."'".$amount."'".' where '.$tbKey2.'='.$tbVal);
return $amount;
}

function db_calcTotalUserAmount($user_id, $tbUsers, $tbTopics, $tbPosts, $tbArcStats, $archive=''){
/* Calculating amount of user posts, taking into attention all possible archives, both live and archived topics/posts */
if($res=mysql_query("select count(*) from {$tbTopics} where topic_poster={$user_id}") and mysql_num_rows($res)>0 and $row=mysql_fetch_row($res)) $num_live_topics=$row[0];
else $num_live_topics=0;

if($res=mysql_query("select count(*) from {$tbPosts} where poster_id={$user_id}") and mysql_num_rows($res)>0 and $row=mysql_fetch_row($res)) $num_live_posts=$row[0];
else $num_live_posts=0;

$num_archived_topics=0;
$num_archived_posts=0;
$currentArchive=FALSE;
$arcSql='';

if($archive!=''){
mysql_query("update {$tbArcStats} set num_topics='{$num_live_topics}', num_posts='{$num_live_posts}' where user_id='{$user_id}' and archive_id='{$archive}'");

if($res=mysql_query("select sum(num_topics), sum(num_posts) from {$tbArcStats} where user_id={$user_id} and archive_id!='{$archive}'") and mysql_num_rows($res)>0 and $row=mysql_fetch_row($res)) { $otherArchivedTopics=$row[0]; $otherArchivedPosts=$row[1]; } else { $otherArchivedTopics=0; $otherArchivedPosts=0; }

$num_archived_topics=$num_live_topics+$otherArchivedTopics;
$num_archived_posts=$num_live_posts+$otherArchivedPosts;
$currentArchive=TRUE;

if($res=mysql_query("select {$GLOBALS['dbUserSheme']['num_live_topics'][1]}, {$GLOBALS['dbUserSheme']['num_live_posts'][1]} from {$tbUsers} where {$GLOBALS['dbUserId']}={$user_id}") and mysql_num_rows($res)>0 and $row=mysql_fetch_row($res)) {
$num_live_topics=$row[0];
$num_live_posts=$row[1];
}
}

if(isset($GLOBALS['archives']) or defined('ARCHIVING_PROCESS')){
if(!$currentArchive and $res=mysql_query("select sum(num_topics), sum(num_posts) from {$tbArcStats} where user_id={$user_id}") and mysql_num_rows($res)>0 and $row=mysql_fetch_row($res)) {
$num_archived_topics=$row[0];
$num_archived_posts=$row[1];
}

if($archive=='') $arcSql=", {$GLOBALS['dbUserSheme']['num_live_topics'][1]}='{$num_live_topics}', {$GLOBALS['dbUserSheme']['num_live_posts'][1]}='{$num_live_posts}' ";
}

$num_topics=$num_live_topics+$num_archived_topics;
$num_posts=$num_live_posts+$num_archived_posts;

mysql_query("update {$tbUsers} set {$GLOBALS['dbUserSheme']['num_topics'][1]}='{$num_topics}', {$GLOBALS['dbUserSheme']['num_posts'][1]}='{$num_posts}' {$arcSql} where {$GLOBALS['dbUserId']}='{$user_id}'");

return array($num_live_topics, $num_live_posts, $num_archived_topics, $num_archived_posts);
}

function db_calcTotalStats($prefix, $tbTopics, $tbPosts, $tbArcStats){

if($res=mysql_query("select count(*) from {$tbTopics}") and mysql_num_rows($res)>0 and $row=mysql_fetch_row($res)) $GLOBALS['num_topics']=$row[0];
else $GLOBALS['num_topics']=0;

if($res=mysql_query("select count(*) from {$tbPosts}") and mysql_num_rows($res)>0 and $row=mysql_fetch_row($res)) $GLOBALS['num_posts']=$row[0];
else $GLOBALS['num_posts']=0;

$GLOBALS['archive_id']=$prefix;

if($res=mysql_query("select archive_id from {$tbArcStats} where archive_id='{$prefix}'") and mysql_num_rows($res)>0){
updateArray(array('num_posts', 'num_topics'), $tbArcStats, 'archive_id', $prefix);
}
else{
insertArray(array('archive_id', 'num_posts', 'num_topics'), $tbArcStats);
}

}

function db_searchSelect($sus, $table='', $fields='', $sqlstr='', $makeLim='', $orderBy=''){
if(!$sus){
$sql='SELECT '.$fields.' FROM '.$table.' WHERE '.$sqlstr.' ';
if($orderBy!='') $sql.="ORDER BY $orderBy ";
if($makeLim!='') $sql.='LIMIT '.$makeLim;
//echo "!-- ".$sql." --><br />";
$result=mysql_query($sql);
if($result) {
$GLOBALS['countRes']=mysql_num_rows($result);
$GLOBALS['result']=$result;
}
}
if( ($sus==1 OR isset($result) ) AND isset($GLOBALS['countRes']) AND $GLOBALS['countRes']>0) return mysql_fetch_row($GLOBALS['result']);
else return FALSE;
}

function db_genPhrase($phrase,$where,$searchType){
if($where==0) $field='post_text';
elseif($where==1) $field='topic_title';
$phrase=str_replace('$', '&#036;', $phrase);

$sql=' (';

if($searchType==0 or $searchType==3){
$words=explode(' ',$phrase);
$gen='';
foreach($words as $w) {
$w=trim(str_replace('%','',$w));
if($w!='' and strlen($w)>2) if($searchType==0) $gen.="{$w}% "; else $gen.="%{$w}";
}
$gen=trim($gen);
if($searchType==0) $sql=" ($field like '% {$gen}' or $field like '%>{$gen}' or $field like '{$gen}' or $field like '%;{$gen}' or $field like '".substr($gen,0,strlen($gen)-1).".') ";
else $sql=" ($field like '{$gen}%') ";
}
else $sql=" ($field like '%{$phrase}%') ";

//echo $sql;

return $sql;
}

?>