<?php
define('LINE_CHANNEL_ACCESS_TOKEN', getenv('LINE_CHANNEL_ACCESS_TOKEN'));
define('LINE_API_PUSH_MESSAGE', 'https://api.line.me/v2/bot/message/push');
require_once __DIR__ . '/bootstrap.php';

// loggerとか用意するの面倒なのでバッチをSlimにのっけちゃう……
execute(function () {
	$families = \ORM\FamilyQuery::create()
		->setFormatter(\ORM\FamilyQuery::FORMAT_ON_DEMAND)
		->find();
	foreach ($families as $family) {
		$this->logger->debug('sending push', ['fid' => $family->getFamilyId()]);

		$items = \ORM\UserItemQuery::create()
			->filterByFamilyId($family->getFamilyId())
			->filterByExpireDate(['min' => time(), 'max' => time() + 2 * 24 * 60 * 60])
			->filterByExpirePushDoneFlag(false)
			->find();
		if (count($items) == 0) {
			continue;
		}
		$text = '賞味期限が近づいています！: ';
		foreach ($items as $index => $item) {
			if ($index >= 5) {
				$text .= sprintf(' 他%d品', count($items));
				break;
			} else if ($index !== 0) {
				$text .= ', ';
			}
			$text .= $item->getItemName();
		}

		$post = [
			'to' => $family->getRoomId(),
			'message' => [
				['type' => 'text', 'text' => $text]]];

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, [
			'Content-Type: application/json',
			'Authorization:	Bearer ' . LINE_API_PUSH_MESSAGE]);
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($curl);
		$this->logger->debug($result, ['fid' => $family->getFamilyId()]);
	}
});
