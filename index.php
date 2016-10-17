<?php
require 'vendor/autoload.php';

date_default_timezone_set('Asia/Tokyo');

$app = new Slim\App();

$app->get('/', function ($request, $response, $args) {
    $response->getBody()->write("hoge");
    return $response;
});

$app->post('/deploy', function ($request, $response, $args) use ($app) {
    $params = $request->params();
    $token = getenv('SLACK_TOKEN');
    if($token == $params['token']) {
        shell_exec('./deploy.sh');
    }
});
$app->run();
