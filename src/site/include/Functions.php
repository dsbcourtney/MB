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

/**
* Turns value into more descriptive log value 
* @param Log value
* @param Error type
* $app->log->debug(logValue($val, $type));
* $app->log->info(logValue($val, $type));
* $app->log->notice(logValue($val, $type));
* $app->log->warning(logValue($val, $type));
* $app->log->error(logValue($val, $type));
* $app->log->critical(logValue($val, $type));
* $app->log->alert(logValue($val, $type));
* $app->log->emergency(logValue($val, $type));
*/
function logValue($val, $errtype) {
  $errtype = strtolower($errtype);
  if ($errtype=='alert') {
    // SEND EMAIL ALERT TO ME
  } elseif ($errtype=='emergency') {
    // DO SOMETHING MORE LIKE RING ME OR SOMETHING?!
  }
  return date('Y-m-d H:i:s', time()).' - '.ucwords($errtype).' - '.$val;
}