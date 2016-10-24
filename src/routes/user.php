<?php

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

$app->post('/user/add', function (ServerRequestInterface $request, ResponseInterface $response, $args) {
	transaction(function () {
		$family = new \ORM\Family();
		$family->save();

		$user = new \ORM\User();
		$user->setAccessToken(sha1(mt_rand() . uniqid(gethostname(), true)))
			->setFamilyId($family->getFamilyId())
			->save();
	});

	return get_renderer()->render($response);
});
