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
				'user' => [
					'access_token' => $user->getAccessToken(),
					'family_id' => $user->getFamilyId()],
				'family' => [
					'token' => $family->getToken()]];
		});
		return get_renderer()->render($response, $data);
	})->add(new RequestValidateMiddleware());
});
