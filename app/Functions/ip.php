<?php


function getRemoteAddr(){
	if(empty($_SERVER['HTTP_X_FORWARDED_FOR']) == false){
		return $_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	
	return $_SERVER['REMOTE_ADDR'];
}