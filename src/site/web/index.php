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
* Needs to be logged out for this.
**/
$app->get('/forgotten-login', 'unauthenticate', function() use ($app) {
  $vars = array('title'=>'Forgotten Login');
  $app->render('forgotten_login.twig.html', $vars); 
});

/**
* Forgotten login page post
* @param email address
**/
$app->post('/forgotten-login', 'unauthenticate', function() use ($app) {
  $email = $app->request->post('email');
  $validateUrl = $app->request->post('validateUrl');
  $vars = array('email'=>$email, 'validateUrl'=>$validateUrl);
  $result = postData(URL_API.'/forgotten/login', $vars);
  if (!$result->error) {
    $vars = array('title'=>'Forgotten Login', 'alert'=>'success', 'heading'=>'Success!', 'message'=>$result->message);
    $app->render('alert.twig.html', $vars);
  } else {
    $vars = array('error'=>$result->error, 'message'=>$result->message, 'title'=>'Forgotten Login');
    $app->render('forgotten_login.twig.html', $vars);
  }
});

/** 
* Reset password
* method - get
* @param identification variable
**/
$app->get('/reset/password', 'unauthenticate', function() use ($app) {
  $ident = $app->request->get('ident');
  $result = getData(URL_API.'/reset/password?ident='.$ident);
  if ($result->error) {
    $vars = array('title'=>'Reset Password', 'alert'=>'danger', 'heading'=>'Error!', 'message'=>'Sorry password reset not allowed');    
    $app->render('alert.twig.html', $vars);    
  } else {
    $vars = array('title'=>'Reset Password', 'ident'=>$ident);
    $app->render('reset_password.twig.html', $vars);
  }
});

/**
* Reset password
* method - post
* @param ident, password
**/
$app->post('/reset/password', 'unauthenticate', function() use ($app) {
  $ident = $app->request->post('ident');
  $password = $app->request->post('password');
  $vars = array('ident'=>$ident, 'password'=>$password);
  $result = postData(URL_API.'/reset/password', $vars);
  if ($result->error) {
    $vars = array('title'=>'Reset Password', 'error'=>1, 'message'=>$result->message, 'ident'=>$ident);
    $app->render('reset_password.twig.html', $vars);
  } else {
    $vars = array('title'=>'Login', 'success'=>1, 'message'=>'Password updated successfully');
    $app->render('login.twig.html', $vars);    
  }

});

/**
* Account page
**/
$app->get('/account', 'authenticate', function() use ($app) {
  global $vars;
  $headers = array('Authorization: '.$vars['userkey']);
  $user = getData(URL_API.'/user/'.$vars['userid'], $headers);
  if (!$user->error) {
    $vars = array_merge($vars, array('title'=>'Account', 'page'=>'account'));
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
    $vars = array_merge($vars, array('title'=>'Account', 'page'=>'details'));
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
  $validateUrl = $app->request->post('validateUrl');
  $headers = array('Authorization: '.$vars['userkey']);
  $vars = array('id'=>$vars['userid'], 'name'=>$name, 'email'=>$email, 'username'=>$username, 'validateUrl'=>$validateUrl);

  $result = postData(URL_API.'/user/update', $vars, $headers);
  if (isset($result)) {
    $vars = array_merge($vars, array('title'=>'Account', 'error'=>$result->error, 'message'=>$result->message, 'page'=>'details'));
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
* Resend the validation email
**/
$app->get('/account/validation/email', 'authenticate', function() use ($app) {
  global $vars;
  $headers = array('Authorization: '.$vars['userkey']);
  $url = URL_API.'/validation/email/'.$_SESSION[SESSION_VAR.'userid'].'?validateUrl='.URL_HOST.'/account/validate';
  //echo $url;
  $result = getData($url, $headers);
  if (isset($result)) {
    if ($result->error) {
      $vars = array_merge($vars, array('title'=>'Account', 'error'=>$result->error, 'message'=>$result->message, 'page'=>'details'));
      $vars['object'] = $result;  
      $app->render('account_details.twig.html', $vars);
    } else {
      $vars = array_merge($vars, array('title'=>'Account', 'error'=>$result->error, 'message'=>$result->message, 'page'=>'details'));
      $vars['object'] = $result;  
      $app->render('account_details.twig.html', $vars);
    } 
  } else {
    $vars = array('title'=>'Error', 'message'=>'API not working');
    $app->render('error.twig.html', $vars);          
  }
});

/** 
* Account mates
* Get a list of current mates
* name, email, nickname, confirmed / unconfirmed, no. of bets
**/
$app->get('/account/mates', 'authenticate', function() use ($app) {
  global $vars;
  $headers = array('Authorization: '.$vars['userkey']);  
  $result = getData(URL_API.'/mates/list', $headers);
  //print_r($result->mates);
  $vars = array('title'=>'Mates', 'mates'=>$result->mates, 'page'=>'mates');
  $app->render('mates.twig.html', $vars);

});

/**
* Form to add a new mate into the account
**/
$app->get('/account/mates/add', 'authenticate', function() use ($app) {
  $vars = array('title'=>'Mates', 'page'=>'mates');
  $app->render('mates_add.twig.html', $vars);  
});

/** 
* Post a new mates details into the account
**/
$app->post('/account/mates/add', 'authenticate', function() use ($app) {
  global $vars;
  $headers = array('Authorization: '.$vars['userkey']);  
  $name = $app->request->post('name');
  $email = $app->request->post('email');
  $vars = array('name'=>$name, 'email'=>$email);
  $result = postData(URL_API.'/mates/add', $vars, $headers);
  if (isset($result)) {
    if ($result->error) {
      $vars = array('title'=>'Mates', 'error'=>$result->error, 'message'=>$result->message, 'page'=>'mates');
      $app->render('mates_add.twig.html', $vars);
    } else {
      $result = getData(URL_API.'/mates/list', $headers);
      $vars = array('title'=>'Mates', 'success'=>true, 'message'=>'Your mate was successfully added', 'mates'=>$result->mates, 'page'=>'mates');
      $app->render('mates.twig.html', $vars);  
    }
  } else {
    $vars = array('title'=>'Error', 'message'=>'API not working');
    $app->render('error.twig.html', $vars);   
  }
});

/**
* Edit a mate
**/
$app->get('/account/mates/edit/:id', 'authenticate', function($mate_id) use ($app) {
  global $vars;
  $headers = array('Authorization: '.$vars['userkey']);  
  $result = getData(URL_API.'/mates/'.$mate_id, $headers);
  //print_r($result);
  if (isset($result)) {
    $vars = array('title'=>'Edit Mate', 'page'=>'mates', 'error'=>$result->error, 'message'=>$result->message);
    $vars['object'] = $result;
    $app->render('mates_add.twig.html', $vars);
  } else {
    $vars = array('title'=>'Error', 'message'=>'API not working');
    $app->render('error.twig.html', $vars);
  }
});

/**
** Edit a mate (post)
**/
$app->post('/account/mates/edit/:id', 'authenticate', function($mate_id) use ($app) {
  global $vars;
  $headers = array('Authorization: '.$vars['userkey']);
  $nickname = $app->request->post('name');
  $email = $app->request->post('email');
  $vars = array('name'=>$nickname, 'email'=>$email);
  $result = postData(URL_API.'/mates/'.$mate_id, $vars, $headers);
  if (isset($result)) {
    if ($result->error) {
      $vars = array('title'=>'Mates', 'error'=>$result->error, 'message'=>$result->message, 'page'=>'mates');
      $app->render('mates_add.twig.html', $vars);
    } else {
      $result = getData(URL_API.'/mates/list', $headers);
      $vars = array('title'=>'Mates', 'success'=>true, 'message'=>'Your mate was successfully updated', 'mates'=>$result->mates, 'page'=>'mates');
      $app->render('mates.twig.html', $vars);  
    }
  } else {
    $vars = array('title'=>'Error', 'message'=>'API not working');
    $app->render('error.twig.html', $vars);   
  }
});


/** 
* Betting Part! How exciting!!
**/

$app->get('/bet/add', 'authenticate', function() use ($app) {
  global $vars;
  $headers = array('Authorization: '.$vars['userkey']);  
  $result = getData(URL_API.'/mates/list', $headers);
  $vars = array('title'=>'Add bet', 'mates'=>$result->mates, 'page'=>'bets');
  $app->render('bet_add.twig.html', $vars);

});

/**
* This is going to recieve the quick bet and then ask then if they want to display more advanced bet options
**/
$app->post('/bet/add', 'authenticate', function() use ($app) {
  global $vars;
  $headers = array('Authorization: '.$vars['userkey']);  
  $description = $app->request->post('description');
  $name_id = $app->request->post('name_id');
  $name = $app->request->post('name');
  $prize = $app->request->post('prize');
  $datedue = date('Y-m-d H:i:s', strtotime($app->request->post('datedue')));
  $vars = array('description'=>$description, 'name_id'=>$name_id, 'name'=>$name, 'prize'=>$prize, 'datedue'=>$datedue);
  $result = postData(URL_API.'/bet/add', $vars, $headers);
  if (isset($result)) {
    if ($result->error) {
      $vars = array('title'=>'Add Bet', 'error'=>$result->error, 'message'=>$result->message, 'page'=>'bets');
      $app->render('bet_add.twig.html', $vars);
    } else {
      $vars = array('title'=>'Add Bet', 'success'=>true, 'message'=>'Your bet was successfully added', 'page'=>'bets');
      $app->render('bet_add.twig.html', $vars);  
    }
  } else {
    $vars = array('title'=>'Error', 'message'=>'API not working');
    $app->render('error.twig.html', $vars);   
  }
});

$app->get('/bet/edit/:id', 'authenticate', function($bet_id) use ($app) {
  global $vars;
  $headers = array('Authorization: '.$vars['userkey']);
  $result = getData(URL_API.'/bet/edit/'.$bet_id);
  if (isset($result)) {
    $vars = array('title'=>'Edit Bet', 'error'=>$result->error, 'message');
    $app->render('bet_add.twig.html', $vars);
  } else {
    $vars = array('title'=>'Error', 'message'=>'API not working');
    $app->render('error.twig.html', $vars);
  }

});

$app->get('/bet/advanced', 'authenticate', function() use ($app) {

});

$app->post('/bet/advanced', 'authenticate', function() use ($app) {

});

$app->get('/bet/list', 'authenticate', function() use ($app) {
  global $vars;
  $headers = array('Authorization: '.$vars['userkey']);
  $result = getData(URL_API.'/bet/list', $headers);
  $vars = array('title'=>'Bet List', 'bets'=>$result->bets, 'page'=>'bets');
  $app->render('bets.twig.html', $vars);
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