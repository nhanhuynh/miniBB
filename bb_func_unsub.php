<?php
/*
This file is part of miniBB. miniBB is free discussion forums/message board software, without any warranty. See COPYING file for more details. Copyright (C) 2011 Paul Puzyrev. www.minibb.com
Latest File Update: 2011-Mar-02
*/
if (!defined('INCLUDED776')) die ('Fatal error.');

$usrid=(isset($_GET['usrid'])?$_GET['usrid']+0:0);

$allowUnsub=FALSE;
$chkCode=FALSE;

if(isset($_GET['code']) and preg_match("#[a-zA-Z0-9]+#", $_GET['code'])){
//trying to unsubscribe directly from email
$chkField='email_code';
$chkVal=$_GET['code'];
$userCondition=TRUE;
$chkCode=TRUE;
}
else{
//manual unsubsribe
$chkField='user_id';
$chkVal=$user_id;
$userCondition=($usrid==$user_id);
}

if ($topic!=0 and $usrid>0 and $userCondition and $ids=db_simpleSelect(0, $Ts, 'id, user_id', 'topic_id', '=', $topic, '', '', $chkField, '=', $chkVal)) {

$finalAllow=( ($chkCode and $ids[1]==$usrid) OR (!$chkCode and $user_id==$usrid) );

$op=0;
if($finalAllow) $op=db_delete($Ts,'id','=',$ids[0]);

if ($op>0) {
$errorMSG=$l_completed; $title.=$l_completed;
}

else {
$title.=$l_itseemserror; $errorMSG=$l_itseemserror;
}

}
else {
$title.=$l_forbidden; $errorMSG=$l_accessDenied;
}

$correctErr='';
echo load_header(); echo ParseTpl(makeUp('main_warning')); return;
?>