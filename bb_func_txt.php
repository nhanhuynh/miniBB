<?php
/*
This file is part of miniBB. miniBB is free discussion forums/message board software, without any warranty. See COPYING file for more details.
Copyright (C) 2004-2007, 2011 Paul Puzyrev, Sergei Larionov. www.minibb.net
Copyright (C) 2013 Paul Puzyrev. www.minibb.com
Latest File Update: 2013-May-22
*/
if (!defined('INCLUDED776')) die ('Fatal error.');

include ($pathToFiles.'bb_codes.php');

//--------------->
if(!function_exists('customized_conversion')){
function customized_conversion($str) {

$newStr=$str;

$searchArr=array();
$replaceArr=array();

//apostrophes ISO/UTF
$searchArr[]='&#8216;'; $replaceArr[]="'";
$searchArr[]='&#8217;'; $replaceArr[]="'";
$searchArr[]=chr(226).chr(128).chr(152); $replaceArr[]="'";
$searchArr[]=chr(226).chr(128).chr(153); $replaceArr[]="'";

//quotes ISO/UTF
$searchArr[]='&#8220;'; $replaceArr[]='"';
$searchArr[]='&#8221;'; $replaceArr[]='"';
$searchArr[]=chr(226).chr(128).chr(156); $replaceArr[]='"';
$searchArr[]=chr(226).chr(128).chr(157); $replaceArr[]='"';

//middot ISO/UTF
$searchArr[]='&#8226;'; $replaceArr[]='&middot;';
$searchArr[]=chr(226).chr(128).chr(162); $replaceArr[]='&middot;';

//three dots ISO/UTF
$searchArr[]='&#8230;'; $replaceArr[]='...';
$searchArr[]=chr(226).chr(128).chr(166); $replaceArr[]='...';

//trademark
$searchArr[]=chr(226).chr(132).chr(162); $replaceArr[]='&trade;';

//baseline apostrophe ISO/UTF
$searchArr[]='&#8218;'; $replaceArr[]="'";
$searchArr[]=chr(226).chr(128).chr(154); $replaceArr[]="'";

//baseline quote
$searchArr[]='&#8222;'; $replaceArr[]='"';
$searchArr[]=chr(226).chr(128).chr(158); $replaceArr[]='"';

//euro
$searchArr[]='&#8364;'; $replaceArr[]='&euro;';
$searchArr[]=chr(226).chr(130).chr(172); $replaceArr[]='&euro;';

//pound - FF/IE UTF
$searchArr[]=chr(194).chr(163); $replaceArr[]='&pound;';
//$searchArr[]=chr(163); $replaceArr[]='&pound;';

//°
$searchArr[]=chr(194).chr(176); $replaceArr[]='&deg;';
//$searchArr[]='°'; $replaceArr[]='&deg;';

//Right-To-Left Override
$searchArr[]='&#8238;'; $replaceArr[]='';

$newStr=str_replace($searchArr, $replaceArr, $newStr);

/*
$searchArr=array();
for($i=130;$i<=156;$i++){
$searchArr[]=chr($i);
}
$replaceArr=array(',', 'NLG', '"', '...', '**', '***', '^', 'o/oo', 'S', '<', 'OE', '', 'Z', '', '', "'", "'", '"', '"', '&middot;', '-–', '--', '~', '&reg;', 's', '>', 'oe');
*/
//$newStr=str_replace($searchArr, $replaceArr, $newStr);

return $newStr;
}
}

//--------------->
function special_substr($text, $limit){
/* analogue of default substr() with exception it cuts text off not by every symbol, but by actual symbols, even if they are encoded as unicode (something like &#...; is one symbol) */

$total=0;
$returned='';
$foundUni=0;
for($i=0;$i<strlen($text);$i++){
if($text[$i]=='&') $foundUni=1;
if($foundUni==1)  { if($text[$i]==';') { $total++; $foundUni=0; } }  else $total++;
$returned.=$text[$i];
if($total>=$limit) break;
}

return $returned;
}

//--------------->
function wrapText($wrap,$text){
$exploded=explode(' ',$text);

for($i=0;$i<sizeof($exploded);$i++) {

if(!isset($foundTag)) $foundTag=0;
$str=$exploded[$i];

if (substr_count($str, '<')>0 and substr_count($str, ' ')>0) $foundTag=1;

if(substr_count($str, '&#')>0 or substr_count($str, '&quot;')>0 or substr_count($str, '&amp;')>0 or substr_count($str, '&lt;')>0 or substr_count($str, '&gt;')>0 or substr_count($str, "\n")>0) $fnAmp=1; else $fnAmp=0;

if(strlen($str)>$wrap and ($foundTag==1 or $fnAmp==1)) {

$chkPhr=''; $symbol=0;
$foundAmp=0;

for ($a=0; $a<strlen($str); $a++) {

if($foundTag==0 and $foundAmp==0) $symbol++;

if ($str[$a]=='<') { $foundTag=1; }
if ($str[$a]=='>' and $foundTag==1) { $foundTag=0;}

if ($str[$a]=='&') { $foundAmp=1; }
if ($str[$a]==';' and $foundAmp==1) { $foundAmp=0; }

if($str[$a]==' ' or $str[$a]=="\n") {$symbol=0;}
if($symbol>=$wrap and $foundTag==0 and $foundAmp==0 and isset($str[$symbol+1])) { $chkPhr.=$str[$a].' '; $symbol=0; }
else $chkPhr.=$str[$a];

}//a cycle

if (strlen($chkPhr)>0) $exploded[$i]=$chkPhr;

}
elseif (strlen($str)>$wrap) $exploded[$i]=chunk_split($exploded[$i],$wrap,' ');
else{
if (substr_count($str, '<')>0 or substr_count($str, '>')>0) {
for ($a=strlen($str)-1;$a>=0;$a--){
if($str[$a]=='>') {$foundTag=0;break;}
elseif($str[$a]=='<') {$foundTag=1;break;}
}
}
}
} //i cycle

return implode(' ',$exploded);
}

//--------------->
function urlMaker($text){

/*
Only alphanumerics [0-9a-zA-Z], the special characters "$-_.+!*'()," [not including the quotes and # - ed], and reserved characters used for their reserved purposes may be used unencoded within a URL. http://www.rfc-editor.org/rfc/rfc1738.txt
*/

//[0-9a-zA-Z$-_.+!*'(),&=\#~]

$patterns=array('#(^|[ \n]|/>)'.str_replace('.', '\\.', $GLOBALS['main_url']).'([^<> \[\]\n\r]*)#i');
$replacements=array('\\1<a href="'.$GLOBALS['main_url'].'\\2" target="_blank">'.$GLOBALS['main_url'].'\\2</a>');

if($GLOBALS['tUrl']!=$GLOBALS['main_url']){

$patterns[]='#(^|[ \n]|/>)'.str_replace('.', '\\.', $GLOBALS['tUrl']).'([^<> \[\]\n\r]*)#i';
$replacements[]='\\1<a href="'.$GLOBALS['tUrl'].'\\2" target="_blank">'.$GLOBALS['tUrl'].'\\2</a>';

}

if(substr(strtolower($GLOBALS['main_url']), 0, 11)=='http://www.') {

$patterns[]='#(^|[ \n]|/>)www\.'.str_replace(array('http://www.', '.'), array('', '\\.'), strtolower($GLOBALS['main_url'])).'([^<> \[\]\n\r]*)#i';
$replacements[]='\\1<a href="'.$GLOBALS['main_url'].'\\2" target="_blank">www.'.str_replace('http://www.', '', strtolower($GLOBALS['main_url'])).'\\2</a>';

}

if($GLOBALS['tUrl']!=$GLOBALS['main_url']){

if(substr(strtolower($GLOBALS['tUrl']), 0, 11)=='http://www.') {

$patterns[]='#(^|[ \n]|/>)www\.'.str_replace(array('http://www.', '.'), array('', '\\.'), strtolower($GLOBALS['tUrl'])).'([^<> \[\]\n\r]*)#i';
$replacements[]='\\1<a href="'.$GLOBALS['tUrl'].'\\2" target="_blank">www.'.str_replace('http://www.', '', strtolower($GLOBALS['tUrl'])).'\\2</a>';

}

}

if($GLOBALS['allowHyperlinks']==0  or $GLOBALS['user_id']==1 or ($GLOBALS['user_id']>1 and isset($GLOBALS['user_num_posts']) and $GLOBALS['user_num_posts']>=$GLOBALS['allowHyperlinks'])){

$patterns=array_merge($patterns, array("#(^|[ \n]|/>)(https|http|ftp)://([^<> \[\]\n\r]+)#i", "#(^|[ \n]|/>)ftp\.([^<> \[\]\n\r]+)#i", "#(^|[ \n]|/>)www\.([^<> \[\]\n\r]+)#i"));

$replacements=array_merge($replacements, array('\\1<a href="\\2://\\3" target="_blank"'.$GLOBALS['relFollowUrl'].'>\\2://\\3</a>', '\\1<a href="ftp://ftp.\\2" target="_blank"'.$GLOBALS['relFollowUrl'].'>ftp.\\2</a>', '\\1<a href="http://www.\\2" target="_blank"'.$GLOBALS['relFollowUrl'].'>www.\\2</a>'));

}

$ret=preg_replace($patterns, $replacements, $text);

if(preg_match("#<a href=\"(.+?)[.,\-:;?!]+\"#i", $ret)) {
$ret=preg_replace("#<a href=\"(.+?)[.,\-:;?!]+\"(.+?)>(.+?)</a>#is", '<a href="\\1"\\2>\\3</a>', $ret);
}

return $ret;
}

//--------------->
function convert_entities($text){

$search=array('&amp;nbsp;', '&amp;iexcl;', '&amp;cent;', '&amp;pound;', '&amp;curren;', '&amp;yen;', '&amp;brvbar;', '&amp;sect;', '&amp;uml;', '&amp;copy;', '&amp;ordf;', '&amp;laquo;', '&amp;not;', '&amp;shy;', '&amp;reg;', '&amp;macr;', '&amp;deg;', '&amp;plusmn;', '&amp;sup2;', '&amp;sup3;', '&amp;acute;', '&amp;micro;', '&amp;para;', '&amp;middot;', '&amp;cedil;', '&amp;sup1;', '&amp;ordm;', '&amp;raquo;', '&amp;frac14;', '&amp;frac12;', '&amp;frac34;', '&amp;iquest;', '&amp;Agrave;', '&amp;Aacute;', '&amp;Acirc;', '&amp;Atilde;', '&amp;Auml;', '&amp;Aring;', '&amp;AElig;', '&amp;Ccedil;', '&amp;Egrave;', '&amp;Eacute;', '&amp;Ecirc;', '&amp;Euml;', '&amp;Igrave;', '&amp;Iacute;', '&amp;Icirc;', '&amp;Iuml;', '&amp;ETH;', '&amp;Ntilde;', '&amp;Ograve;', '&amp;Oacute;', '&amp;Ocirc;', '&amp;Otilde;', '&amp;Ouml;', '&amp;times;', '&amp;Oslash;', '&amp;Ugrave;', '&amp;Uacute;', '&amp;Ucirc;', '&amp;Uuml;', '&amp;Yacute;', '&amp;THORN;', '&amp;szlig;', '&amp;agrave;', '&amp;aacute;', '&amp;acirc;', '&amp;atilde;', '&amp;auml;', '&amp;aring;', '&amp;aelig;', '&amp;ccedil;', '&amp;egrave;', '&amp;eacute;', '&amp;ecirc;', '&amp;euml;', '&amp;igrave;', '&amp;iacute;', '&amp;icirc;', '&amp;iuml;', '&amp;eth;', '&amp;ntilde;', '&amp;ograve;', '&amp;oacute;', '&amp;ocirc;', '&amp;otilde;', '&amp;ouml;', '&amp;divide;', '&amp;oslash;', '&amp;ugrave;', '&amp;uacute;', '&amp;ucirc;', '&amp;uuml;', '&amp;yacute;', '&amp;thorn;', '&amp;yuml;', '&amp;euro;', '&amp;trade;');

$replace=array(' ', '&#161;', '&#162;', '&#163;', '&#164;', '&#165;', '&#166;', '&#167;', '&#168;', '&#169;', '&#170;', '&#171;', '&#172;', '&#173;', '&#174;', '&#175;', '&#176;', '&#177;', '&#178;', '&#179;', '&#180;', '&#181;', '&#182;', '&#183;', '&#184;', '&#185;', '&#186;', '&#187;', '&#188;', '&#189;', '&#190;', '&#191;', '&#192;', '&#193;', '&#194;', '&#195;', '&#196;', '&#197;', '&#198;', '&#199;', '&#200;', '&#201;', '&#202;', '&#203;', '&#204;', '&#205;', '&#206;', '&#207;', '&#208;', '&#209;', '&#210;', '&#211;', '&#212;', '&#213;', '&#214;', '&#215;', '&#216;', '&#217;', '&#218;', '&#219;', '&#220;', '&#221;', '&#222;', '&#223;', '&#224;', '&#225;', '&#226;', '&#227;', '&#228;', '&#229;', '&#230;', '&#231;', '&#232;', '&#233;', '&#234;', '&#235;', '&#236;', '&#237;', '&#238;', '&#239;', '&#240;', '&#241;', '&#242;', '&#243;', '&#244;', '&#245;', '&#246;', '&#247;', '&#248;', '&#249;', '&#250;', '&#251;', '&#252;', '&#253;', '&#254;', '&#255;', '&#128;', '&#8482;');

return str_replace($search, $replace, $text);

}

//--------------->
function textFilter($text,$size,$wrap,$urls,$bbcodes,$eofs,$admin,$shorten=0){

/*
for($i=0; $i<strlen($text); $i++){
echo $text[$i].' '.ord($text[$i]).'<br />';
}
exit;
*/

//echo $text.'+++++++++++';exit;
$text=customized_conversion($text);
//echo $text.'-----------';exit;
if(get_magic_quotes_gpc()==0) $text=addslashes($text);
//echo $text; exit;

if(($admin==1 or (isset($GLOBALS['isMod']) and $GLOBALS['isMod']==1)) and isset($GLOBALS['adminHTML']) and $GLOBALS['adminHTML']) $text=trim($text);
else $text=operate_string($text);

$text=str_replace(array('\&#039;', '\&quot;', chr(92).chr(92).chr(92).chr(92), chr(92).chr(92), '&amp;#', '$'), array('&#039;', '&quot;', '&#92;&#92;', '&#92;', '&#', '&#036;'), $text);

$text=convert_entities($text);

//if(isset($GLOBALS['l_meta']) and substr_count(strtolower($GLOBALS['l_meta']), 'utf-8')==0) $text=str_replace(array('“', '”', '‘', '’', '…'), array('&quot;', '&quot;', '&#039;', '&#039;', '...'), $text);

//if(substr_count($text, '&#9')>0) $text=preg_replace("@&#9[0-9]{2,};@", '', $text);

if(isset($GLOBALS['smartLinking']) and $GLOBALS['smartLinking'] and function_exists('smartLink')) $text=smartLink($text);

if (!$bbcodes) {
$text=enCodeBB($text, $admin);
$text=str_replace('><img src=','> <img src=',$text);
}

if($urls and !$bbcodes) {
$text=urlMaker($text);
}

$text=wrapText($wrap,$text);

$sce=FALSE; if(isset($GLOBALS['simpleCodes'])) foreach($GLOBALS['simpleCodes'] as $e) { if(substr_count($text, $e)>0) { $sce=TRUE; break; } }
if(trim(strip_tags($text))=='' and !$sce) return '';

if($size and strlen($text)>$size) {
$text=special_substr($text, $size);
if ($shorten>0 and strlen($text)>$shorten) $text=substr($text,0,$shorten);

if(substr_count($text, '&')>0){
/* Avoid special symbols extract */
$tmpArr=explode ('&', $text);
$last=sizeof($tmpArr)-1;
if ($last>0) {
if (substr_count($tmpArr[$last], ';')==0) array_pop($tmpArr);
$text=implode ('&', $tmpArr);
}
}

}
if($eofs and !isset($GLOBALS['disableLineBreaks'])){
while (substr_count($text, "\r\n\r\n\r\n\r\n")>4) $text=str_replace("\r\n\r\n\r\n\r\n","\r\n",$text);
while (substr_count($text, "\n\n\n\n")>4) $text=str_replace("\n\n\n\n","\n",$text);
$text=str_replace(array("\r\n", "\n"),'<br />',$text);
}
while(substr($text,-1)==chr(92)) $text=substr($text,0,strlen($text)-1);

return $text;
}

//--------------->
function convEnt($str){
return strip_tags(str_replace(array('<br />', '&#039;', '&quot;', '&amp;', '&#036;', '&lt;', '&gt;'), array("\n", "'", '"', '&', '$', '<', '>'), $str));
}

?>