<?php
ini_set('display_errors', 1);
require_once '../include/Config.php';
require_once '../include/DbHandler.php';
require_once '../include/PassHash.php';
require_once '../include/Functions.php';
require '../libs/vendor/autoload.php';
 
//\Slim\Slim::registerAutoloader();

$logWriter = new \Slim\Extras\Log\DateTimeFileWriter(array(
            'path' => LOG_LOCATION,
            'name_format' => 'Y-m-d',
            'message_format' => '%label% - %date% - %message%'
        ));

$app = new \Slim\Slim(array(
    'debug'=>false,
    'log.enabled'=>true,
    'log.writer'=>$logWriter
  ));
 
// Global Variables
$user_id = NULL;
$user = array();

/**
 * Adding Middle Layer to authenticate every request
 * Checking if the request has valid api key in the 'Authorization' header
 */
function authenticate(\Slim\Route $route) {
  // Getting request headers
  $headers = apache_request_headers();
  $response = array();
  $app = \Slim\Slim::getInstance();

  // Verifying Authorization Header
  if (isset($headers['Authorization'])) {
    $db = new DbHandler();

    // get the api key
    $api_key = $headers['Authorization'];
    // validating api key
    if (!$db->isValidApiKey($api_key)) {
      // api key is not present in users table
      $response["error"] = true;
      $response["message"] = "Access Denied. Invalid Api key";
      echoRespnse(401, $response);
      $app->stop();
    } else {
      global $user_id, $user;
      // get user primary key id
      $user = $db->getUserId($api_key);
      if ($user != NULL) {
        $user_id = $user["id"];
        $user = $db->getUserById($user_id);
      }
    }
  } else {
    // api key is missing in header
    $response["error"] = true;
    $response["message"] = "Api key is misssing";
    echoRespnse(400, $response);
    $app->stop();
  }
}

/**
 * User Registration
 * url - /register
 * method - POST
 * params - name, email, password
 */
$app->post('/register', function() use ($app) {
  // check for required params
  verifyRequiredParams(array('name', 'email', 'password', 'username'));

  $response = array();

  // reading post params
  $name = $app->request->post('name');
  $email = $app->request->post('email');
  $password = $app->request->post('password');
  $username = $app->request->post('username');
  $validateUrl = $app->request->post('validateUrl');

  // validating email address
  validateEmail($email);
  // validating password 
  validatePass($password);
  // validating username
  validateUsername($username);

  $db = new DbHandler();
  $res = $db->createUser($name, $email, $password, $username);

  if ($res == USER_CREATED_SUCCESSFULLY) {
    $response["error"] = false;
    $response["message"] = "You are successfully registered";
    // Send the user a registration email
    $user = $db->getUserByEmail($email);
    registrationEmail($email, $user, $validateUrl);
    $response['id'] = $user['id'];
    $response['username'] = $user['username'];
    $response['apiKey'] = $user['api_key'];
    echoRespnse(201, $response);
  } else if ($res == USER_CREATE_FAILED) {
    $response["error"] = true;
    $response["message"] = "Oops! An error occurred while registering";
    echoRespnse(200, $response);
  } else if ($res == USER_EMAIL_ALREADY_EXISTS) {
    $response["error"] = true;
    $response["message"] = "Sorry, this email already exists";
    echoRespnse(200, $response);
  } else if ($res == USER_USERNAME_ALREADY_EXISTS) {
    $response["error"] = true;
    $response["message"] = "Sorry, this username already exists";
    echoRespnse(200, $response);
  }
});

/**
* Validate email
* url - /validate/email
* method - GET
* params - ident (indentification based on database and userid combination)
*/
$app->get('/validate/email', function() use ($app) {
  $response = array();
  $ident = $app->request->get('ident');
  
  $userid = substr($ident,0,1);
  $string = substr($ident,1);
  $db = new DbHandler();
  $res = $db->validateUser($userid, $string);

  if ($res == VALIDATION_SUCCESS) {
    $response["error"] = false;
    $response["message"] = "Validation successful";    
    echoRespnse(201, $response);
  } else if ($res == VALIDATION_FAILURE) {
    $response["error"] = true;
    $response["message"] = "Sorry, validation failed";
    echoRespnse(200, $response);
  } elseif ($res == VALIDATION_NOT_NEEDED) {
    $response["error"] = false;
    $response["message"] = "Validation already successful";
    echoRespnse(201, $response);    
  }

});

/**
* Re send validate email
* url - /validation/email
* @param 
*/
$app->get('/validation/email/:id', 'authenticate', function($userid) use ($app) {
  $db = new DbHandler();
  $user = $db->getUserById($userid);
  if ($user != NULL) {
    $validateUrl = ($app->request->get('validateUrl')!==NULL)?$app->request->get('validateUrl'):'';
    registrationEmail($user['email'], $user, $validateUrl);
    $response['error'] = false;
    $response['message'] = 'Registration email re-sent';
    $response['user'] = $user;  
  } else {
    $response['error'] = true;
    $response['message'] = 'Account not found';  
  }
  echoRespnse(200, $response);
});

/**
* Forgotten Login
* url - /forgotten-login
* method - POST
* params - email
*/
$app->post('/forgotten/login', function() use ($app) {
  $email = $app->request()->post('email');
  $validateUrl = $app->request()->post('validateUrl');
  $db = new DbHandler();
  $response = array();

  $user = $db->getUserByEmail($email);

  if ($user != NULL) {
    forgottenLoginEmail($email, $validateUrl);
    $response['error'] = false;
    $response['message'] = 'Forgotten login email sent';
  } else {
    $response['error'] = true;
    $response['message'] = 'Account not found';      
  }
  echoRespnse(200, $response);
});

/**
* Reset Password
* url - /reset/password
* method - GET
* params - ident (indentification based on database and userid combination)
* This will just return a boolean of whether allowed to do it or not
*/
$app->get('/reset/password', function() use ($app) {
  $response = array();
  $ident = $app->request->get('ident');
  $db = new DbHandler();
  $user = $db->checkResetPassword($ident);
  if ($user != NULL) {
    $response['error'] = false;
    $response['message'] = 'Password reset allowed';
  } else {
    $response['error'] = true;
    $response['message'] = 'Password reset not allowed';
  }
  echoRespnse(200, $response);
});

/** 
* Reset Password 
* url - /reset/password
* method - POST
* params - ident, new password
* This will update the password depending upon the ident sent
**/
$app->post('/reset/password', function() use ($app) {
  $ident = $app->request->post('ident');
  $password = $app->request->post('password');
  $db = new DbHandler();
  $user = $db->checkResetPassword($ident);
  if ($user != NULL) {
    if ($user['id']>0) {
      validatePass($password);
      $db->updatePassword($user['id'], $password);
      $response['error'] = false;
      $response['message'] = 'Password reset successfully';
      echoRespnse(201, $response);  
    } else {
      $response['error'] = true;
      $response['message'] = 'User not found';
      echoRespnse(200, $response);      
    }
  } else {
    $response['error'] = true;
    $response['message'] = 'Password reset not allowed';
    echoRespnse(200, $response);
  }

});

/**
 * User Login
 * url - /login
 * method - POST
 * params - email, password
 */
$app->post('/login', function() use ($app) {

  // check for required params
  verifyRequiredParams(array('email', 'password'));

  // reading post params
  $ident = $app->request()->post('email');
  $password = $app->request()->post('password');

  $response = array();

  $db = new DbHandler();
  // check for correct email and password
  if ($db->checkLogin($ident, $password)) {
    // get the user by email
    $user = $db->getUserByIdent($ident);

    if ($user != NULL) {
      $response["error"] = false;
      $response['id'] = $user['id'];
      $response['name'] = $user['name'];
      $response['email'] = $user['email'];
      $response['apiKey'] = $user['api_key'];
      $response['createdAt'] = $user['created_at'];
      $response['username'] = $user['username'];
    } else {
      // unknown error occurred
      $response['error'] = true;
      $response['message'] = "An error occurred. Please try again";
    }
  } else {
    // user credentials are wrong
    $response['error'] = true;
    $response['message'] = 'Login failed. Incorrect credentials';
  }

  echoRespnse(200, $response);
});



/** 
 * Get user information
 * all the account information
*/
$app->get('/user/:id', 'authenticate', function($userid) {

  $db = new DbHandler();
  $user = $db->getUserById($userid);
  $response["user"] = $user;
  $response["error"] = false;
  $response["message"] = "Success";
  echoRespnse(201, $response);

});


/** 
* Update user, needs to have the apikey to do so 
**/
$app->post('/user/update', 'authenticate', function() use ($app) {

  verifyRequiredParams(array('name', 'email', 'username'));

  $userid = $app->request->post('id');
  $email = $app->request->post('email');
  $name = $app->request->post('name');
  $username = $app->request->post('username');
  $validateUrl = $app->request->post('validateUrl');

  // validating email address
  validateEmail($email);
  // validating username
  validateUsername($username);

  $db = new DbHandler();
  $user = $db->getUserById($userid);

  if ($user['status']>1) {

    $emailChange = false;
    if ($user['email']!=$email) {
      $emailChange = true; // User is changing his email address !
      $user['status'] = 1;
    }

    $oldEmail = $user['email'];
    $user['email'] = $email;
    $user['name'] = $name;
    $user['username'] = $username;
    $res = $db->updateUser($user);

    if ($res == USER_UPDATE_SUCCESSFUL) {

      if ($emailChange) { // Send email to old address and then registration email to new one
        //inform of email change
        //emailChangeEmail($user['email']); // Not created yet
        registrationEmail($email, $user, $validateUrl);
      }
      $user = $db->getUserById($userid);
      $response["user"] = $user;
      $response["error"] = false;
      $response["message"] = "Your details have been updated";
      echoRespnse(201, $response);
    } elseif ($res == USER_UPDATE_FAILED) {
      $user = $db->getUserById($userid);
      $response["user"] = $user;
      $response["error"] = true;
      $response["message"] = "Oops! An error occurred while updating";
      echoRespnse(200, $response);
    } elseif ($res == USER_USERNAME_ALREADY_EXISTS) {
      $user = $db->getUserById($userid);
      $response["user"] = $user;
      $response["error"] = true;
      $response["message"] = "Sorry, the username '".$username."' already exists";
      echoRespnse(200, $response);
    } elseif ($res == USER_EMAIL_ALREADY_EXISTS) {
      $user = $db->getUserById($userid);
      $response["user"] = $user;
      $response["error"] = true;
      $response["message"] = "Sorry, the email '".$email."' already exists";
      echoRespnse(200, $response);
    }

  } else {
    $response["user"] = $user;
    $response["error"] = true;
    $response["message"] = "Sorry, you need to validate your account via the email we sent you in order to edit your details";
    echoRespnse(200, $response);   
  }

});

/******************************************************
** MATE PAGES
**/
/**
* Get list of mates based on the user id we get on authentication
**/
$app->get('/mates/list', 'authenticate', function() use ($app) {
  global $user_id;
  $db = new DbHandler();
  $mates = $db->getMatesList($user_id);
  $response = array();
  if ($mates) {
    $response['error'] = false;
    $response['message'] = 'Success';
    $response['mates'] = array();
    while ($mate = $mates->fetch_assoc()) {
      $tmp = array();
      $tmp['id'] = $mate['id'];
      $tmp['mate_id'] = $mate['mate_id'];
      $tmp['email'] = $mate['email'];
      $tmp['nickname'] = $mate['nickname'];
      $tmp['date_added'] = $mate['date_added'];
      
      array_push($response['mates'], $tmp);
    }
    echoRespnse(201, $response);
  } else {
    $response['error'] = true;
    $response['message'] = 'Failed to get mates list';
    echoRespnse(200, $response);    
  }

});

/**
* Get a single mates details
* Need to make sure that the authenticated mate is allowed to get their details
**/
$app->get('/mates/:id', 'authenticate', function($mate_id) use ($app) {
  global $user_id;
  $db = new DbHandler();
  $mate = $db->getMateById($user_id, $mate_id);
  $response = array();
  if ($mate) {
    $response['id'] = $mate_id;
    $response['email'] = $mate['email'];
    $response['nickname'] = $mate['nickname'];
    $response['error'] = false;
    $response['message'] = '';
    echoRespnse(201, $response);
  } else {
    $response['error'] = true;
    $response['message'] = 'Mates details not available';
    echoRespnse(200, $response);
  }

});

$app->post('/mates/:id', 'authenticate', function($mate_id) use ($app) {
  global $user_id;
  $db = new DbHandler();
})

$app->post('/mates/add', 'authenticate', function() use ($app) {
  global $user_id, $user;
  verifyRequiredParams(array('name'));
  $email = $app->request->post('email');
  $name = $app->request->post('name');
  $db = new DbHandler();

  $doCreate = true;
  $errmess = '';
  // First lets see if they already have that username in their mates list
  if ($db->getMateByNickname($user_id, $name)) {
    $doCreate = false;
    $errmess = 'You already have a mate by that nickname, please choose another';
  }

  if ($email!='' && $db->getMateByEmail($user_id, $email)) {
    $doCreate = false;
    $errmess = 'You already have a mate using that email address, please choose another';
  }

  if ($email!='' && $email==$user['email']) {
    $doCreate = false;
    $errmess = 'You are trying to add your own email address to a mate, please use another';    
  } 

  if ($doCreate) {
    // Now lets check to see if that email exists as a user, if so get his userid
    $mate_id = 0;
    if ($email!='') {
      $user = $db->getUserByEmail($email);
      if ($user!=NULL) {
        $mate_id = $user['id'];
      }
    } else {
      $email = '';
    }
    $datetime = date('Y-m-d H:i:s', time());
    $res = $db->addMate($name, $email, $mate_id, $user_id, $datetime); // True or false
    
    if ($res) {
      // If mate_id=0 and email exists send invitation email
      $response["error"] = false;
      $response["message"] = "Your mate has been added";    
      echoRespnse(201, $response);
    } else {
      $response["error"] = true;
      $response["message"] = "Sorry an error occurred whilst adding your mate";
      echoRespnse(200, $response); 
    }
  } else {
    $response["error"] = true;
    $response["message"] = $errmess;    
    echoRespnse(200, $response);     
  }
  
});




/** 
* Betting API part
**/
$app->get('/bet/list', 'authenticate', function() use ($app) {
  global $user_id;
  $db = new DbHandler();
  $bets = $db->getBetsList($user_id);
  $response = array();
  if ($bets) {
    $response['error'] = false;
    $response['message'] = 'Success';
    $response['bets'] = array();
    while ($bet = $bets->fetch_assoc()) {
      $tmp = array();
      $tmp['id'] = $bet['id'];
      $tmp['mate_name'] = $bet['mate_name'];
      $tmp['title'] = $bet['title'];
      $tmp['prize'] = $bet['prize'];
      $tmp['dateadded'] = $bet['dateadded'];
      $tmp['datedue'] = $bet['datedue'];
      $tmp['nickname'] = $bet['nickname'];
      
      array_push($response['bets'], $tmp);
    }
    echoRespnse(201, $response);
  } else {
    $response['error'] = true;
    $response['message'] = 'Failed to get bet list';
    echoRespnse(200, $response);    
  }  
  
});


/**
  * Add a new bet
**/

$app->post('/bet/add', 'authenticate', function() use ($app) {
  global $user_id, $user;
  verifyRequiredParams(array('description', 'prize'));
  $description = $app->request->post('description');
  $name_id = $app->request->post('name_id');
  $name = $app->request->post('name');
  $prize = $app->request->post('prize');
  $datedue = $app->request->post('datedue');
  
  // Handle the name
  if ($name_id==0 && $name=='') { // problem as we haven't received an id or name (should never happen as it is handled in js)
    $response["error"] = true;
    $response["message"] = 'No bet opponent found';    
    echoRespnse(200, $response);    
  } else {
    $db = new DbHandler();
    $res = $db->addSimpleBet($user_id, $description, $name_id, $name, $prize, $datedue);
    if ($res) {
      $response["error"] = false;
      $response["message"] = "Your bet has been added";    
      echoRespnse(201, $response);
    }
  }
});

$app->get('/bet/edit/:id', 'authenticate', function($bet_id) use ($app) {
  global $user_id, $user;
  


});


/**
 * Creating new task in db
 * method POST
 * params - name
 * url - /tasks/
 */
$app->post('/tasks', 'authenticate', function() use ($app) {
  // check for required params
  verifyRequiredParams(array('task'));

  $response = array();
  $task = $app->request->post('task');

  global $user_id;
  $db = new DbHandler();

  // creating new task
  $task_id = $db->createTask($user_id, $task);

  if ($task_id != NULL) {
    $response["error"] = false;
    $response["message"] = "Task created successfully";
    $response["task_id"] = $task_id;
  } else {
    $response["error"] = true;
    $response["message"] = "Failed to create task. Please try again";
  }
  echoRespnse(201, $response);
});


/**
 * Listing all tasks of particual user
 * method GET
 * url /tasks          
 */
$app->get('/tasks', 'authenticate', function() {
  global $user_id;
  $response = array();
  $db = new DbHandler();

  // fetching all user tasks
  $result = $db->getAllUserTasks($user_id);

  $response["error"] = false;
  $response["tasks"] = array();

  // looping through result and preparing tasks array
  while ($task = $result->fetch_assoc()) {
    $tmp = array();
    $tmp["id"] = $task["id"];
    $tmp["task"] = $task["task"];
    $tmp["status"] = $task["status"];
    $tmp["createdAt"] = $task["created_at"];
    array_push($response["tasks"], $tmp);
  }

  echoRespnse(200, $response);
});

/**
 * Listing single task of particual user
 * method GET
 * url /tasks/:id
 * Will return 404 if the task doesn't belongs to user
 */
$app->get('/tasks/:id', 'authenticate', function($task_id) {
  global $user_id;
  $response = array();
  $db = new DbHandler();

  // fetch task
  $result = $db->getTask($task_id, $user_id);

  if ($result != NULL) {
    $response["error"] = false;
    $response["id"] = $result["id"];
    $response["task"] = $result["task"];
    $response["status"] = $result["status"];
    $response["createdAt"] = $result["created_at"];
    echoRespnse(200, $response);
  } else {
    $response["error"] = true;
    $response["message"] = "The requested resource doesn't exists";
    echoRespnse(404, $response);
  }
});

/**
 * Updating existing task
 * method PUT
 * params task, status
 * url - /tasks/:id
 */
$app->put('/tasks/:id', 'authenticate', function($task_id) use($app) {
  // check for required params
  verifyRequiredParams(array('task', 'status'));

  global $user_id;            
  $task = $app->request->put('task');
  $status = $app->request->put('status');

  $db = new DbHandler();
  $response = array();

  // updating task
  $result = $db->updateTask($user_id, $task_id, $task, $status);
  if ($result) {
    // task updated successfully
    $response["error"] = false;
    $response["message"] = "Task updated successfully";
  } else {
    // task failed to update
    $response["error"] = true;
    $response["message"] = "Task failed to update. Please try again!";
  }
  echoRespnse(200, $response);
});

/**
 * Deleting task. Users can delete only their tasks
 * method DELETE
 * url /tasks
 */
$app->delete('/tasks/:id', 'authenticate', function($task_id) use($app) {
  global $user_id;

  $db = new DbHandler();
  $response = array();
  $result = $db->deleteTask($user_id, $task_id);
  if ($result) {
    // task deleted successfully
    $response["error"] = false;
    $response["message"] = "Task deleted succesfully";
  } else {
    // task failed to delete
    $response["error"] = true;
    $response["message"] = "Task failed to delete. Please try again!";
  }
  echoRespnse(200, $response);
});

$app->run();