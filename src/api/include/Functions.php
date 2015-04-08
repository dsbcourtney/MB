<?php
/**
* Functions file
*
* @author David Courtney
*/

/**
* Validating email address
*/
function validateEmail($email) { 
  $app = \Slim\Slim::getInstance();
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response["error"] = true;
    $response["message"] = 'Email address '.$email.' is not valid';
    echoRespnse(400, $response);
    $app->stop();
  }
}

/**
* Validating password
* @param Password for validating
*/
function validatePass($pass) {
  $app = \Slim\Slim::getInstance(); 
  if (strlen($pass)<8) {
    $response["error"] = true;
    $response["message"]= 'Password must be at least 8 characters in length';
    echoRespnse(400, $response);
    $app->stop();
  } elseif (!preg_match("#[0-9]+#", $pass)) {
    $response["error"] = true;
    $response["message"]= 'Password must include at least 1 number';
    echoRespnse(400, $response);
    $app->stop();
  } elseif (!preg_match("#[a-z]+#", $pass)) {
    $response["error"] = true;
    $response["message"]= 'Password must include at least 1 letter';
    echoRespnse(400, $response);
    $app->stop();
  }
}

/**
* Validating username
* @param Username for validating
*/
function validateUsername($username) {
  $app = \Slim\Slim::getInstance();
  if (strlen($username)<4) {
    $response["error"] = true;
    $response["message"]= 'Username must be at least 4 characters in length';
    echoRespnse(400, $response);
    $app->stop();   
  }
}

/**
 * Verifying required params posted or not
 */
function verifyRequiredParams($required_fields) {
  $error = false;
  $error_fields = "";
  $request_params = array();
  $request_params = $_REQUEST;
  // Handling PUT request params
  if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    $app = \Slim\Slim::getInstance();
    parse_str($app->request()->getBody(), $request_params);
  }
  foreach ($required_fields as $field) {
    if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
      $error = true;
      $error_fields .= $field . ', ';
    }
  }

  if ($error) {
    // Required field(s) are missing or empty
    // echo error json and stop the app
    $response = array();
    $app = \Slim\Slim::getInstance();
    $response["error"] = true;
    $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
    echoRespnse(400, $response);
    $app->stop();
  }
}

/**
* Send registration email
* @param Email used within registration
* @param User object array
* @param validateUrl Is the url that will get parsed into the email 
*/
function registrationEmail($email, $user, $validateUrl='') {
  $app = \Slim\Slim::getInstance();
  $db = new DbHandler();
  if ($user != NULL) {
    if ($user['status']==1) { // Not validated yet

      // Create validation hash
      $randomString = randomString(20);

      $message = file_get_contents('../templates/registration_email.html');
      $message = str_replace("%users_name%", $user['name'], $message);
      $message = str_replace("%users_email%", $email, $message);
      $message = str_replace("%users_username%", $user['username'], $message);
      if ($validateUrl=='') {
        $message = str_replace("%url_validate_email%", URL_VALIDATE_EMAIL.'?ident='.$user['id'].$randomString, $message);
      } else {
        $message = str_replace("%url_validate_email%", $validateUrl.'?ident='.$user['id'].$randomString, $message);
      }
      $mail = new PHPMailer;
      $mail->IsSMTP();
      $mail->setFrom(EMAIL_FROM, EMAIL_FROM_NAME);
      $mail->addReplyTo(EMAIL_REPLY, EMAIL_REPLY_NAME);
      $mail->addAddress($email, $user['name']);
      $mail->Subject = 'Matesbet Registration';
      $mail->msgHTML($message);
      $mail->AltBody = ''; // Body if they don't except html. Some mobiles etc may use this

      if (!$mail->send()) {
        $app->log->warning(logValue('Registration email did not send to user id '.$user['id'], 'Warning'));
      } else {
        // Update database to send welcome and verification email has been sent
        $user['validate_email'] = $randomString;
        $user['validate_count']++;
        $db->updateUser($user);
      }

    } else {
      $response["error"] = true;
      $response["message"] = 'Email address already validated';    
      return $response;
      $app->stop();     
    }

  } else {
    $response["error"] = true;
    $response["message"] = 'User could not be found to send the registration email';    
    return $response;
    $app->stop();
  }
}

/** 
* Forgotten login email
* @oaram Email parsed
*/
function forgottenLoginEmail($email, $validateUrl='') {
  $app = \Slim\Slim::getInstance();
  $db = new DbHandler();
  $user = $db->getUserByEmail($email);
  if ($user != NULL) {

    // Create validation hash
    $randomString = randomString(20);

    $message = file_get_contents('../templates/forgottenLogin_email.html');
    $message = str_replace("%users_name%", $user['name'], $message);
    if ($validateUrl=='') {
      $message = str_replace("%url_reset_password%", URL_RESET_PASSWORD.'?ident='.$user['id'].$randomString, $message);      
    } else {
      $message = str_replace("%url_reset_password%", $validateUrl.'?ident='.$user['id'].$randomString, $message);
    }

    $mail = new PHPMailer;
    $mail->IsSMTP();
    $mail->setFrom(EMAIL_FROM, EMAIL_FROM_NAME);
    $mail->addReplyTo(EMAIL_REPLY, EMAIL_REPLY_NAME);
    $mail->addAddress($user['email'], $user['name']);
    $mail->Subject = 'Matesbet Reset Password';
    $mail->msgHTML($message);
    $mail->AltBody = ''; // Body if they don't except html. Some mobiles etc may use this

    if (!$mail->send()) {
      $app->log->warning(logValue('Reset password did not send to user id '.$user['id'], 'Warning'));
    } else {
      // Update database to send welcome and verification email has been sent
      $user['reset_password'] = $randomString;
      $db->updateUser($user);
    }


  }
}

/**
 * Echoing json response to client
 * @param String $status_code Http response code
 * @param Int $response Json response
 */
function echoRespnse($status_code, $response) {
  $app = \Slim\Slim::getInstance();
  // Http response code
  $app->status($status_code);
  // setting response content type to json
  $app->contentType('application/json');
  echo json_encode($response);
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


/** 
* Random string generator
* @param length of required string
*/
function randomString($length) {
  $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $charactersLength = strlen($characters);
  $randomString = '';
  for ($i = 0; $i < $length; $i++) {
    $randomString .= $characters[rand(0, $charactersLength - 1)];
  }
  return $randomString;
}