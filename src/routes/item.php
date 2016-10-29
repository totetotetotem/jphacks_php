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
