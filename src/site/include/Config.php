<?php
// Log locations
define('LOG_LOCATION', '../logs/app.log');

// Session name to put at the beginning or variables
define('SESSION_VAR', 'MB_');

// API URL
define('URL_API', 'http://localhost/api/v1');

define('URL_HOST', 'http://'.(($_SERVER["SERVER_PORT"]!='80')?$_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"]:$_SERVER["SERVER_NAME"]).'/site/web');

// Template config variables
$template_array = array();
$template_array['site_title'] = ' - Mates Bet'; 
$template_array['base_url'] = URL_HOST;