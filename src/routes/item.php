<?php

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

$app->group('/item', function () {
	$this->post('/candidates', function (ServerRequestInterface $request, ResponseInterface $response, $args) {
		$names = $request->getParsedBody()['query']['name'];

		$data = [];
		foreach ($names as $name) {
			$item = \ORM\ItemMasterQuery::create()
				->useItemSearchQuery()
				->filterBySearchWord(preg_replace('|\s|u', '', $name))
				->endUse()
				->findOne();
			if ($item !== null) {
				$datum = $item->format_as_response();
				$datum['original_name'] = $name;
				$data[] = $datum;
			}
		}

		get_renderer()->render($response, ['item_master' => $data]);
	})->add(new \middleware\RequestValidateMiddleware());
});

$app->get('/item/{familyId}', function (ServerRequestInterface $request, ResponseInterface $response, $args) {
    //TODO decほどじゃないけどトークンを比較すべき

    $family = \ORM\FamilyQuery::create()->filterByFamilyId((int)$args['familyId'])->findPk(1);

    $query = \ORM\ItemQuery::create()
            ->orderByExpireDate();

    $items = $family->getItems($query);

    $json = $items->toJson();

    return $response->getBody()->write($json);
});

$app->post('/item/{itemId}/dec', function (ServerRequestInterface $request, ResponseInterface $response, $args) {
    //TODO トークンと比較してきちんとユーザー確かめる
    //TODO ひとつずつのデクリメントじゃなくて、クライアントでちょっとは蓄えてからポストしてまとめて減らす
    transaction(function() {
	/*
	$item = \ORM\ItemQuery::create()->filterByItemId((int)$args['itemId'])->findPk(1);
        $data = (int)$item->getCountOfItem()
        $item->setCountOfItem((string)($data - 1))
             ->save();
	*/
    });

    return  get_renderer()->render($response);
});
