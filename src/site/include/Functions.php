<?php

/**
* CURL Post Data
* @param url
* @param variables in an array
*/
function postData($url, $vars, $headers=array()) {

	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	if (count($headers)>0) {
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	}
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	// Always json so return that
	return json_decode(curl_exec($ch));
	curl_close($ch);
	//print_r($response);
}

/**
* CURL Get Data
* @param url
* @param headers in an array
*/
function getData($url, $headers=array()) {
	$ch = curl_init($url);
	if (count($headers)>0) {
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	}
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	// Always json so return that
	return json_decode(curl_exec($ch));
	curl_close($ch);
}