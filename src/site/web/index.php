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
    global $template_array;
    $app->view()->appendData($template_array);
});

/**
* Home page
**/
$app->get('/', function() use ($app) {
	$app->render('index.twig.html');
});

$app->get('/login', function() use ($app) {
    // Check to see if they are logged in or not first
    if (isset($_SESSION[SESSION_VAR.'userid']) && $_SESSION[SESSION_VAR.'userid']>0) {
        header('location:'.URL_HOST.'/account');
        exit;
    } else {
        $vars = array('title'=>'Login');
        $app->render('login.twig.html', $vars);
    }
});

$app->post('/login', function() use ($app) {
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
            // Cookie set
            if ($app->request->post('remember')=='remember-me') {
                //setcookie();
            }
            // Redirect to account
            header('location:'.URL_HOST.'/account');
            exit;        
        } else {
            $vars = array('title'=>'Login', 'error'=>1, 'message'=>$result->message);
            $app->render('login.twig.html', $vars);
        }
    } else {
        $vars = array('title'=>'Error', 'message'=>'API not working');
        $app->render('error.twig.html', $vars);
    }
});

$app->get('/forgotten-login', function() use ($app) {

});

$app->get('/account', function() use ($app) {
    if (isset($_SESSION[SESSION_VAR.'userid']) && $_SESSION[SESSION_VAR.'userid']>0) {
        $vars = array('title'=>'Account');
        $app->render('account.twig.html', $vars);
    } else {
        $vars = array('title'=>'Login', 'error'=>1, 'message'=>'You need to be logged in to view this page');
        $app->render('login.twig.html', $vars);
    }
});

$app->get('/register', function() use ($app) {
    $vars = array('title'=>'Register');
    $app->render('register.twig.html', $vars);
});

$app->post('/register', function() use ($app) {
    $name = $app->request->post('name');
    $email = $app->request->post('email');
    $password = $app->request->post('password');
    $username = $app->request->post('username');

    $vars = array('name'=>$name, 'email'=>$email, 'password'=>$password, 'username'=>$username);
    $result = postData(URL_API.'/register', $vars);
    if (isset($result)) {
        if (!$result->error) {
            $_SESSION[SESSION_VAR.'username'] = $result->username;
            $_SESSION[SESSION_VAR.'userid'] = $result->id;
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

$app->run();