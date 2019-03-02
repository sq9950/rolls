<?php

/**
 * 获取文件后缀
 * return false/string(jpg/.jpg)
 */
function getFileSuffix($filename, $substr=true){
	$suffix = strrchr($filename, '.');
	if($suffix){
		return  $substr?substr($suffix, 1):$suffix;
	}
	return false;
}

/**
 * 获取上一次访问URL
 */
 function getHttpReferer(){
	return isset($_SERVER['HTTP_REFERER'])?trim($_SERVER['HTTP_REFERER']):'/';
 }
 
 