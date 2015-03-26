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