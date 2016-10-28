<?php

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

$app->get('/item/:familyId', function (ServerRequestInterface $request, ResponseInterface $response, $args) {
    $items = ItemQuery::create()
            ->filterByFamilyId($familyId)
            ->orderByExpireDate()
            ->find();

	return $response->withJson($items);
});
