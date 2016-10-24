<?php

$app->post('/user/add', function (\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response, $args) {
	transaction(function () {
		$family = new \ORM\Family();
		$family->save();

		$user = new \ORM\User();
		$user->setAccessToken(sha1(mt_rand() . uniqid(gethostname(), true)))
			->setFamilyId($family->getFamilyId())
			->save();
	});
});
