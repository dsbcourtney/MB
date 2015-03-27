<?php
session_cache_limiter(false);
session_start();
ini_set('display_errors', 1);
require_once '../include/Config.php';
require_once '../include/Functions.php';
require '../libs/vendor/autoload.php';

$logWriter = new \Slim\Extras\Log\DateTimeFileWriter(array(
  'path' => LOG_LOCATION,
  'name_format' => 'Y-m-d',
  'message_format' => '%label% - %date% - %message%'
));

$app = new \Slim\Slim(array(
  'debug'=>false,
  'log.enabled'=>true,
  'log.writer'=>$logWriter,
  'view'=> new \Slim\Views\Twig(),
  'templates.path'=> '../views'
));

$view = $app->view();
$view->parserOptions = array(
  'debug' => true,
  'cache' => '../cache'
);
$view->parserExtensions = array(
	new \Slim\Views\TwigExtension(),
);

// Get some variables in the views
$app->hook('slim.before', function () use ($app) {
  global $template_array, $vars;
  $app->view()->appendData($template_array);
  $app->view()->appendData($vars);
});

/**
 * Adding Middle Layer to authenticate pages that need to be logged into
 */
function authenticate(\Slim\Route $route) {
  global $vars;
  $app = \Slim\Slim::getInstance();
  if (!isset($vars['userid']) || $vars['userid']==0) { // Not logged in
    $vars = array('title'=>'Login', 'error'=>1, 'message'=>'You need to be logged in to view this page');
    $app->render('login.twig.html', $vars);
    $app->stop();
  }
}

/**
 * Adding Middle Layer to unauthenticate pages that shouldn't be shown if logged in
 */
function unauthenticate(\Slim\Route $route) {
  global $vars;
  $app = \Slim\Slim::getInstance();
  if (isset($vars['userid']) && $vars['userid']>0) { // Not logged in
    header('location:'.URL_HOST.'/account');
    exit;
  }  
}

/**
* Home page
**/
$app->get('/', function() use ($app) {
  global $vars;
  array_push($vars, array('title'=>'Home'));
	$app->render('index.twig.html', $vars);
});

/** 
* Login page
**/
$app->get('/login', 'unauthenticate', function() use ($app) {
  $vars = array('title'=>'Login');
  $app->render('login.twig.html', $vars);
});

/**
* Login page on submit
**/
$app->post('/login', 'unauthenticate', function() use ($app) {
  $email = $app->request->post('email');
  $password = $app->request->post('password');
  $vars = array('email'=>$email, 'password'=>$password);
  $result = postData(URL_API.'/login', $vars);
  if (isset($result)) {
    if (!$result->error && $result->id>0) {
      // Login accepted
      // Set sessions
      $_SESSION[SESSION_VAR.'username'] = $result->username;
      $_SESSION[SESSION_VAR.'userid'] = $result->id;
      $_SESSION[SESSION_VAR.'userkey'] = $result->apiKey;
      // Cookie set
      if ($app->request->post('remember')=='remember-me') {
        //setcookie();
      }
      // Redirect to account
      header('location:'.URL_HOST.'/account');
      exit;        
    } else {
      $vars = array('title'=>'Login', 'error'=>1, 'message'=>$result->message, 'email'=>$email);
      $app->render('login.twig.html', $vars);
    }
  } else {
    $vars = array('title'=>'Error', 'message'=>'API not working');
    $app->render('error.twig.html', $vars);
  }
});

/**
* Register page 
**/
$app->get('/register', 'unauthenticate', function() use ($app) {
  $vars = array('title'=>'Register');
  $app->render('register.twig.html', $vars);
});

/**
* Register page on submit
**/
$app->post('/register', 'unauthenticate', function() use ($app) {
  $name = $app->request->post('name');
  $email = $app->request->post('email');
  $password = $app->request->post('password');
  $username = $app->request->post('username');

  $vars = array('name'=>$name, 'email'=>$email, 'password'=>$password, 'username'=>$username, 'validateUrl'=>URL_HOST.'/account/validate');
  $result = postData(URL_API.'/register', $vars);
  if (isset($result)) {
    if (!$result->error) {
      $_SESSION[SESSION_VAR.'username'] = $result->username;
      $_SESSION[SESSION_VAR.'userid'] = $result->id;
      $_SESSION[SESSION_VAR.'userkey'] = $result->apiKey;
      header('location:'.URL_HOST.'/account');
      exit;
    } else {
      $vars = array_merge($vars, array('title'=>'Register', 'error'=>1, 'message'=>$result->message));
      $app->render('register.twig.html', $vars);
    }        
  } else {
    $vars = array('title'=>'Error', 'message'=>'API not working');
    $app->render('error.twig.html', $vars);
  }
});

/**
* Forgotten login page
**/
$app->get('/forgotten-login', 'unauthenticate', function() use ($app) {

});

/**
* Account page
**/
$app->get('/account', 'authenticate', function() use ($app) {
  global $vars;
  $headers = array('Authorization: '.$vars['userkey']);
  $user = getData(URL_API.'/user/'.$vars['userid'], $headers);
  if (!$user->error) {
    $vars = array_merge($vars, array('title'=>'Account'));
    $vars['object'] = $user;
    //print_r($vars);
    $app->render('account.twig.html', $vars);
  } else {
    $vars = array('title'=>'Login', 'error'=>1, 'message'=>'You need to be logged in to view this page');
    $app->render('login.twig.html', $vars);
  }
});

/**
* Account details page
**/
$app->get('/account/details', 'authenticate', function() use ($app) {
  global $vars;
  $headers = array('Authorization: '.$vars['userkey']);
  $user = getData(URL_API.'/user/'.$vars['userid'], $headers);
  if (!$user->error) {
    $vars = array_merge($vars, array('title'=>'Account'));
    $vars['object'] = $user;
    //print_r($vars);
    $app->render('account_details.twig.html', $vars);
  } else {
    $vars = array('title'=>'Login', 'error'=>1, 'message'=>'You need to be logged in to view this page');
    $app->render('login.twig.html', $vars);
  } 
});

/**
* Account details save page
**/
$app->post('/account/details', 'authenticate', function() use ($app) {
  global $vars;
  $name = $app->request->post('name');
  $email = $app->request->post('email');
  $username = $app->request->post('username');
  $headers = array('Authorization: '.$vars['userkey']);
  $vars = array('id'=>$vars['userid'], 'name'=>$name, 'email'=>$email, 'username'=>$username);

  $result = postData(URL_API.'/user/update', $vars, $headers);
  if (isset($result)) {
    $vars = array_merge($vars, array('title'=>'Account', 'error'=>$result->error, 'message'=>$result->message));
    $vars['object'] = $result;        
    $app->render('account_details.twig.html', $vars);
  } else {
    $vars = array('title'=>'Error', 'message'=>'API not working');
    $app->render('error.twig.html', $vars);      
  }      
});

/**
* Validate account from email address
* Maybe logged in maybe not so no authenticate or not
**/
$app->get('/account/validate', function() use ($app) {
  global $vars;
  $ident = $app->request->get('ident');
  $result = getData(URL_API.'/validate/email?ident='.$ident);
  if (isset($result)) {
    if ($result->error) {
      $vars = array('title'=>'Validate','alert'=>'danger', 'heading'=>'Whoops!', 'message'=>$result->message);
      $app->render('alert.twig.html', $vars);
    } else {
      $vars = array('title'=>'Validate','alert'=>'success', 'heading'=>'Success!', 'message'=>$result->message);
      $app->render('alert.twig.html', $vars);
    }
  }

});

/**
* Logout page, has to be logged in to view this
**/
$app->get('/logout', 'authenticate', function() use ($app) {
  $_SESSION[SESSION_VAR.'username'] = '';
  $_SESSION[SESSION_VAR.'userid'] = 0;
  unset($_SESSION[SESSION_VAR.'username']);
  unset($_SESSION[SESSION_VAR.'userid']);  
  header('location:'.URL_HOST.'/');
  exit;  
});

/**
* Run the application
**/
$app->run();