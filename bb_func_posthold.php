<?php
/*
This file is part of miniBB. miniBB is free discussion forums/message board software, without any warranty.
See COPYING file for more details.
Copyright (C) 2006-2008, 2013 Paul Puzyrev. www.minibb.com
Latest File Update: 2013-Feb-23
*/

if (!defined('INCLUDED776')) die ('Fatal error.');

if(isset($_POST) and sizeof($_POST)>0){

if(get_magic_quotes_gpc()==1){
foreach($_POST as $key=>$val) if(!is_array($val)) $_POST[$key]=stripslashes($val);
}

$hiddenFields='';

$wLen=strlen($postRange);

/* login data */

if(isset($_POST['user_usr']) and isset($_POST['user_pwd']) and isset($displayFormElements['userData'])) {
$user_pwd='';

$userDataFields=<<<out
{$l_sub_name}: <input type="text" size="18" maxlength="40" name="user_usr" value="{$user_usr}" class="textForm" /> {$l_sub_pass}: <input type="password" size="18" maxlength="32" name="user_pwd" class="textForm" /><br /><br />
out;
unset($_POST['user_usr']); unset($_POST['user_pwd']);
}
else $userDataFields='';

/* topic title */

if(isset($_POST['topicTitle']) and isset($displayFormElements['topicTitle'])) {
$topicTitle=operate_string($_POST['topicTitle']);
if(isset($fieldsReadOnly)) $topicTitleField="<em>{$l_topic}:</em><br /><br />{$topicTitle}<br /><br />";
elseif($topicTitle!=''){
$topicTitleField="{$l_topic}:<br /><input type=\"text\" name=\"topicTitle\" class=\"textForm topicTitle\" value=\"{$topicTitle}\" maxlength=\"{$topic_max_length}\" /><br /><br />";
unset($_POST['topicTitle']);
}
else $topicTitleField='';
}
else $topicTitleField='';

/* message text */

if(isset($_POST['postText']) and isset($displayFormElements['postText'])) {
if(!function_exists('deCodeBB')) require_once($pathToFiles.'bb_codes.php');
$postText=deCodeBB(trim($_POST['postText']));

if(isset($fieldsReadOnly)) $postTextField="<em>{$l_message}:</em><br /><br />".str_replace("\n", '<br />', $postText)."<br /><br />";
elseif($postText!=''){
$postTextField="{$l_message}:<br /><textarea name=\"postText\" cols=\"38\" rows=\"10\" class=\"textForm postingForm\">{$postText}</textarea><br /><br />";
unset($_POST['postText']);
}
else $postTextField='';
}
else $postTextField='';



foreach($_POST as $key=>$val) {

if(!is_array($val)) {

if(substr_count($val, '"')>0 or substr_count($val, '<')>0 or substr_count($val, '>')>0 or substr_count($val, '&')>0){
$hiddenFields.="<textarea name=\"{$key}\" style=\"display:none\" cols=\"0\" rows=\"0\">".operate_string($val)."</textarea>\n";
}

else $hiddenFields.="<input type=\"hidden\" name=\"{$key}\" value=\"{$val}\" />";
}
}

if(isset($antiSpam)){

if(isset($enableCaptcha) and $enableCaptcha){
$_SESSION['authorized']=1;
}

$timeLeft=$postRange-$asTime+0+1;

$textAntiSpam=<<<out
<script type="text/javascript">
document.write('<input type="text" id="timert" name="timert" readonly="readonly" size="{$wLen}" maxlength="{$wLen}" class="textForm" />&nbsp;');
</script>
out;

$jsAntiSpam=<<<out
<script type="text/javascript">
var c=$timeLeft;
var t;

function stopCount(){
clearTimeout(t);
}

function timedCount() {
document.forms['resubmit'].elements['timert'].value=c;
c=c-1;
t=setTimeout("timedCount()",1000);
if(c<0) {
clearTimeout(t);
document.forms['resubmit'].elements['subbut'].disabled=false;
document.forms['resubmit'].elements['timert'].style.display='none';
}
}

document.forms['resubmit'].elements['subbut'].disabled=true;
timedCount();

</script>
out;

}
else{
$textAntiSpam='';
$jsAntiSpam='';
}

if(isset($fieldsReadOnly)){
$formDisplay=<<<out
<br />
<table class="tbTransparentmb">
<tr><td>
<span class="txtNr">{$antiWarn}</span><br /><br />
{$topicTitleField}{$postTextField}
</td></tr></table>
out;
}
else{
$formDisplay=<<<out
<br />
<table class="tbTransparentmb">
<tr><td>
<span class="txtNr">{$antiWarn}</span><br /><br />
<form action="{$indexphp}" method="post" class="formStyle" name="resubmit">
{$userDataFields}{$topicTitleField}{$postTextField}
{$textAntiSpam}<input type="submit" value="{$l_postholdRepeat}" class="inputButton" name="subbut" />
{$hiddenFields}
</form>
</td></tr></table>

{$jsAntiSpam}

out;
}

}

?>