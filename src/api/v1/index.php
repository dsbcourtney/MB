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
 
// User id from db - Global Variable
$user_id = NULL;

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
    registrationEmail($email);
    $user = $db->getUserByEmail($email);
    $response['id'] = $user['id'];
    $response['username'] = $user['username'];
    $response['apiKey'] = $user['api_key'];
    echoRespnse(201, $response);
  } else if ($res == USER_CREATE_FAILED) {
    $response["error"] = true;
    $response["message"] = "Oops! An error occurred while registering";
    echoRespnse(200, $response);
  } else if ($res == USER_EMAIL_ALREADY_EXISTED) {
    $response["error"] = true;
    $response["message"] = "Sorry, this email already exists";
    echoRespnse(200, $response);
  } else if ($res == USER_USERNAME_ALREADY_EXISTED) {
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
  }

});

/**
* Forgotten Login
* url - /forgotten-login
* method - POST
* params - email
*/
$app->post('/forgotten/login', function() use ($app) {
  $email = $app->request()->post('email');
  $db = new DbHandler();
  $response = array();

  $user = $db->getUserByEmail($email);

  if ($user != NULL) {
    forgottenLoginEmail($email);
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
*/
$app->get('/reset/password', function() use ($app) {
  $response = array();
  $ident = $app->request()->get('ident');

  $userid = substr($ident,0,1);
  $string = substr($ident,1);
  $db = new DbHandler();
  /*
  $res = $db->validateUser($userid, $string);

  if ($res == VALIDATION_SUCCESS) {
    $response["error"] = false;
    $response["message"] = "Validation successful";    
    echoRespnse(201, $response);
  } else if ($res == VALIDATION_FAILURE) {
    $response["error"] = true;
    $response["message"] = "Sorry, validation failed";
    echoRespnse(200, $response);
  }
  */


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
  $email = $app->request()->post('email');
  $password = $app->request()->post('password');
  $response = array();

  $db = new DbHandler();
  // check for correct email and password
  if ($db->checkLogin($email, $password)) {
    // get the user by email
    $user = $db->getUserByEmail($email);

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
      global $user_id;
      // get user primary key id
      $user = $db->getUserId($api_key);
      if ($user != NULL)
        $user_id = $user["id"];
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