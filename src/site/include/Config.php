<?php
// Log locations
define('LOG_LOCATION', '../logs');

// Session name to put at the beginning or variables
define('SESSION_VAR', 'MB_');

// API URL
define('URL_API', 'http://localhost'.(($_SERVER["SERVER_PORT"]!='80')?':'.$_SERVER["SERVER_PORT"]:'').'/api/v1');

define('URL_HOST', 'http://'.(($_SERVER["SERVER_PORT"]!='80')?$_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"]:$_SERVER["SERVER_NAME"]).'/site/web');

// Template config variables
$template_array = array();
$template_array['site_title'] = ''; 
$template_array['base_url'] = URL_HOST;

$vars = array();
$vars['userid'] = (isset($_SESSION[SESSION_VAR.'userid']) && $_SESSION[SESSION_VAR.'userid'])?$_SESSION[SESSION_VAR.'userid']:0;
$vars['username'] = (isset($_SESSION[SESSION_VAR.'username']) && $_SESSION[SESSION_VAR.'username'])?$_SESSION[SESSION_VAR.'username']:'';
$vars['userkey'] = (isset($_SESSION[SESSION_VAR.'userkey']) && $_SESSION[SESSION_VAR.'userkey'])?$_SESSION[SESSION_VAR.'userkey']:'';
$vars['fb_appid'] = '4155015962';
$vars['fb_appsecret'] = '776757d6d99343e42819e2ef2167d3e1';
$vars['fb_appversion'] = 'v2.0';
$vars['fb_callback'] = '/login?fb=1';
$vars['tw_key'] = 'rlqXrXvH46X9dIkL3rzQ';
$vars['tw_secret'] = 'CwnYIJrcQElEqQ7yN09VzT94mLo57b6csBlV9hab8Sk';
$vars['tw_access_token'] = '19532878-LhtZsZpvybHFkkq68zzfz6LK923UfSAyZiM4x0o';
$vars['tw_access_secret'] = 'KmrNVKXqhsLsexfTjgHqVmx8jyRTxpbeK98utKQ2KQ';
$vars['tw_callback'] = '/login?tw=1';