<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/generated-conf/config.php';
$fp = fopen('php://stdin', 'r');

/*
 * 検索用文字列&検索用文字列&...&&英語&...&&賞味期限までの日数
 * を標準入力から読み込んでデータベースに登録する
 */
while (($buf = fgets($fp)) !== false) {
	list($yomi, $english, $days) = explode('&&', $buf);

	$ys = explode('&', $yomi);
	$item = \ORM\ItemMasterQuery::create()
		->filterByItemName($ys[0])
		->findOne();
	if (!$item) {
		$item = new \ORM\ItemMaster();
		$item->setItemName($ys[0])
			->setDefaultExpireDays($days)
			->save();
	}

	foreach (explode('&', $yomi) as $y) {
		$found = \ORM\ItemSearchQuery::create()
			->filterBySearchWord($y)
			->exists();
		if (!$found) {
			$search = new \ORM\ItemSearch();
			$search->setItemId($item->getItemId())
				->setSearchWord($y)
				->save();
		}
	}
}
