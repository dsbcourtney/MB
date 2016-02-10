<?php
/**
* Database configuration
*/
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_HOST', 'localhost');
define('DB_NAME', 'mb');
 
/**
* Other configuration 
*/ 

define('URL_HOST', 'http://'.(($_SERVER["SERVER_PORT"]!='80')?$_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"]:$_SERVER["SERVER_NAME"]).'/api/v1');

define('USER_CREATED_SUCCESSFULLY', 0);
define('USER_CREATE_FAILED', 1);
define('USER_EMAIL_ALREADY_EXISTS', 2);
define('USER_USERNAME_ALREADY_EXISTS', 3);
define('USER_UPDATE_SUCCESSFUL', 4);
define('USER_UPDATE_FAILED', 5);
define('VALIDATION_SUCCESS', 6);
define('VALIDATION_FAILURE', 7);

/* Log location */
define('LOG_LOCATION', '../logs');

/* Email variables */
define('EMAIL_FROM', 'noreply@localhost');
define('EMAIL_REPLY', 'support@localhost');
define('EMAIL_FROM_NAME', 'Matesbet Registrations');
define('EMAIL_REPLY_NAME', 'Matesbet Support');

/* Url variables */
define('URL_VALIDATE_EMAIL', URL_HOST.'/validate/email'); // This is set to the api at the moment 
define('URL_RESET_PASSWORD', URL_HOST.'/reset/password'); // This is set to the api at the moment

define('FB_APPID', '4155015962');
define('FB_APPSECRET', '776757d6d99343e42819e2ef2167d3e1');
define('FB_APPVERSION', 'v2.0');