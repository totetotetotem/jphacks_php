<?php

require __DIR__ . '/../vendor/autoload.php';

// session_start();

define('APP_ROOT_PATH', __DIR__ . '/..');

// Instantiate the app
$settings = require __DIR__ . '/../src/settings.php';
$app = new \Slim\App($settings);

// Set up dependencies
require __DIR__ . '/../src/dependencies.php';

// Load common functions
require __DIR__ . '/../src/utility.php';

// Register middleware
require __DIR__ . '/../src/middleware.php';

function execute(callable $callable)
{
	global $app;
	$app->get('/__batch__', $callable);
	try {
		$req = new \Slim\Http\Request(
			'GET', new \Slim\Http\Uri('', 'localhost', null, '/__batch__'), new \Slim\Http\Headers(), [], [],
			new \Slim\Http\RequestBody());
		$res = new \Slim\Http\Response();
		$app->process($req, $res);
	} catch (Exception $e) {
		$logger = get_logger_from_container($app->getContainer());
		show_exception($logger, $e);
	}
}
