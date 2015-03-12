<?php
// Log locations
define('LOG_LOCATION', '../logs/app.log');

// Session name to put at the beginning or variables
define('SESSION_VAR', 'MB_');

// API URL
define('URL_API', 'http://localhost:8888/api/v1');

define('URL_HOST', 'http://'.(($_SERVER["SERVER_PORT"]!='80')?$_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"]:$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"]));

// Template config variables
$template_array = array();
$template_array['site_title'] = ' - Mates Bet'; 