<?php
// DIC configuration

$container = $app->getContainer();

// view renderer
require_once __DIR__ . '/middleware/JsonRenderer.php';
$container['renderer'] = function ($c) {
	return new middleware\JsonRenderer();
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

// propel
require_once __DIR__ . '/../db/generated-conf/config.php';
