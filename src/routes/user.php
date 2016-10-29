<?php

use middleware\RequestValidateMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

$app->group('/user', function () {
	$this->post('/add', function (ServerRequestInterface $request, ResponseInterface $response, $args) {
		$data = transaction(function () {
			$family = new \ORM\Family();
			$family->setToken(sha1(mt_rand() . uniqid(gethostname(), true)))
				->save();

			$user = new \ORM\User();
			$user->setAccessToken(sha1(mt_rand() . uniqid(gethostname(), true)))
				->setFamilyId($family->getFamilyId())
				->save();

			return [
				'user' => $user->format_as_response(),
				'family' => $family->format_as_response()];
		});
		return get_renderer()->render($response, $data);
	})->add(new RequestValidateMiddleware(null, true));
});
