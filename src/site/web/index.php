<?php
ini_set('display_errors', 1);
require_once '../include/Config.php';
require_once '../include/Functions.php';
require '../libs/vendor/autoload.php';

$logWriter = new \Slim\LogWriter(fopen(LOG_LOCATION, 'a'));

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
    'cache' => dirname(__FILE__) . '/cache'
);
$view->parserExtensions = array(
	new \Slim\Views\TwigExtension(),
);

/**
* Home page
**/
$app->get('/', function() use ($app, $view) {
	$app->render('index.twig.html');
});

$app->get('/login', function() use ($app, $view) {
    $app->render('login.twig.html');
});

$app->run();