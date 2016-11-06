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
		$today = new DateTime('today');
		$day_after_tomorrow = new DateTime('+2 day');
		$day_after_tomorrow->modify('midnight');
		$query = \ORM\UserItemQuery::create()
			->filterByFamilyId($family->getFamilyId())
			->filterByExpireDate(['min' => $today, 'max' => $day_after_tomorrow])
			->filterByExpirePushDoneFlag(false);
		$items = $query->find();
		if (count($items) == 0) {
			continue;
		}
		$text = '賞味期限が近づいています！: ';
		foreach ($items as $index => $item) {
			if ($index >= 5) {
				$text .= sprintf(' 他%d品', count($items) - $index);
				break;
			} else if ($index !== 0) {
				$text .= ', ';
			}
			$text .= $item->getItemName();
		}
		$query->update(['ExpirePushDoneFlag' => true]);

		$post = [
			'to' => $family->getRoomId(),
			'messages' => [
				['type' => 'text', 'text' => $text]]];

		$this->logger->debug('sending push', ['fid' => $family->getFamilyId(), 'post' => $post]);
		$curl = curl_init(LINE_API_PUSH_MESSAGE);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, [
			'Content-Type: application/json',
			'Authorization:	Bearer ' . LINE_CHANNEL_ACCESS_TOKEN]);
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($curl);
		$this->logger->debug($result, ['fid' => $family->getFamilyId(), 'status' => curl_getinfo($curl, CURLINFO_HTTP_CODE)]);
	}
});
