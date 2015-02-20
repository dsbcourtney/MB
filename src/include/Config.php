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
define('USER_CREATED_SUCCESSFULLY', 0);
define('USER_CREATE_FAILED', 1);
define('USER_ALREADY_EXISTED', 2);

define('VALIDATION_SUCCESS', 1);
define('VALIDATION_FAILURE', 0);

/* Log location */
define('LOG_LOCATION', '../logs/app.log');

/* Email variables */
define('EMAIL_FROM', 'noreply@localhost');
define('EMAIL_REPLY', 'support@localhost');
define('EMAIL_FROM_NAME', 'Matesbet Registrations');
define('EMAIL_REPLY_NAME', 'Matesbet Support');

/* Url variables */
define('URL_VALIDATE_EMAIL', 'localhost/validate/email'); // This is set to the api at the moment 