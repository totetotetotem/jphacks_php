<?php

$app->post('/line', function($request, $response, $args) {
        $url = 'https://api.line.me/v2/bot/message/reply';
        $channel_access_token = getenv("LINE_CHANNEL_ACCESS_TOKEN");
        $headers = array(
            'Content-type: application/json',
            "Authorization: Bearer {$channel_access_token}"
            );

        $this->logger->addDebug(getenv("LINE_CHANNEL_ACCESS_TOKEN"));
        $json_string = file_get_contents('php://input');
        $json_object = json_decode($json_string);

        //var_dump($json_object);
        $this->logger->addDebug("json_string".$json_string);

        $result = "";
        if(isset($json_object->events)) {
        foreach ($json_object->events as $event) {
        $token = $event->replyToken;

        $this->logger->addDebug("token".$token);
        if($event->type === 'message') {
            $post = array(
                    'replyToken' => $token,
                    'messages' => array(
                        array(
                            'type' => 'text',
                            'text' => $event->source->userId
                            )
                        )
                    );
        }

	        $this->logger->addDebug($event);
        if($event->source->type === 'group' && strpos($event->message->text, 'familytoken') !== false) {
            $redis = new Redis();
            $redis->connect("127.0.0.1", 6379);
            $value = $redis->lRange('familyTokens', 0, -1);
            $this->logger->debug($value);
            foreach ($value as $id) {
                if($id === $event->source->groupId) { 
                    $familyToken = explode(':', $event->message->text)[1];
                    $family = \ORM\FamilyQuery::create()->filterByToken($familyToken)->findOne();
                    if($family === null) {
                        $post = array(
                            'replyToken' => $token,
                            'messages' => array(
                                array(
                                    'type' => 'text',
                                    'text' => 'Invalid Token'
                                    )
                                )
                            );
                       break; 
                    }
                    $family->setRoomId($id);
                    $family->save();
                     
                    $post = array(
                            'replyToken' => $token,
                            'messages' => array(
                                array(
                                    'type' => 'text',
                                    'text' => 'アプリとの連携を設定しました'
                                    )
                                )
                            );
                    break;
                }
            }
        }

        if($event->type === 'beacon') {
	        $lineId = $event->source->userId;
	        $this->logger->addDebug("lineId" . $lineId);
	        $user = \ORM\UserQuery::create()->filterByLineId($lineId)->findOne();
	        $item = \ORM\UserItemQuery::create()
		        ->filterByFamilyId((int)$user->getFamilyId())
		        ->orderByExpireDate()
		        ->findOne();

	        if ($item !== null) {
		        $this->logger->addDebug("item" . $item->getItemName());
		        $message_text = 'expire date of ' . $item->getItemName() . ' is ' . $item->getExpireDate()->format('Y-m-d');
		        $this->logger->addDebug("messages" . $message_text);

		        $post = array(
			        'replyToken' => $token,
			        'messages' => array(
				        array(
					        'type' => 'text',
					        'text' => $message_text
				        )
			        )
		        );
	        }
        }

        if($event->type === 'join') {
	        $this->logger->debug($event);
            $redis = new Redis();
            $redis->connect("127.0.0.1", 6379);

            $redis->rPush('familyTokens', $event->source->groupId);

            $post = array(
                    'replyToken' => $token,
                    'messages' => array(
                        array(
                            'type' => 'text',
                            'text' => 'Lineがアプリと連携するために、アプリを操作してアクセストークンをこのチャンネルに入力してください'
                            )
                        )
                    );
        }

        $this->logger->addDebug("post".implode($post));

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($curl);
        curl_close($curl);

        $this->logger->addDebug("result".$result);

        $data =[
            "status" => "OK",
            "result" => $result
                ];

        return $response->withJson($data);


        }
        }
});


