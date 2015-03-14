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

define('URL_HOST', 'http://'.(($_SERVER["SERVER_PORT"]!='80')?$_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"]:$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"]));

define('USER_CREATED_SUCCESSFULLY', 0);
define('USER_CREATE_FAILED', 1);
define('USER_ALREADY_EXISTED', 2);

define('VALIDATION_SUCCESS', 1);
define('VALIDATION_FAILURE', 0);

/* Log location */
define('LOG_LOCATION', '../logs');

/* Email variables */
define('EMAIL_FROM', 'noreply@localhost');
define('EMAIL_REPLY', 'support@localhost');
define('EMAIL_FROM_NAME', 'Matesbet Registrations');
define('EMAIL_REPLY_NAME', 'Matesbet Support');

/* Url variables */
define('URL_VALIDATE_EMAIL', URL_HOST.'validate/email'); // This is set to the api at the moment 
define('URL_RESET_PASSWORD', URL_HOST.'reset/password'); // This is set to the api at the moment