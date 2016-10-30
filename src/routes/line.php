<?php

$app->post('/line', function($request, $response, $args) {

        function api_post_request($token, $message) {
        $url = 'https://api.line.me/v2/bot/message/reply';
        $channel_access_token = getenv("LINE_CHANNEL_ACCESS_TOKEN");
        $headers = array(
            'Content-type: application/json',
            "Authorization: Bearer {$channel_access_token}"
            );

        $post = array(
            'replyToken' => $token,
            'messages' => array(
                array(
                    'type' => 'text',
                    'text' => $message
                    )
                )
            );

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
        if($event->source->type === 'room' && strpos($event->message->text, 'familytoken') !== false) {
            $redis = new Redis();
            $redis.connect("127.0.0.1", 6379);
            $value = $redis->lRange('familyTokens', 0, -1);
            foreach ($value as $id) {
                if($id === $event->source->roomId) { 
                    $familyToken = explode(':', $event->message->text)[1];
                    /*
                    $family = \ORM\FamilyQuery()::create()->filterByToken($familyToken);
                    $family->setLineRoomId($id);
                    $family->save();
                        */
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
            $this->logger->addDebug("lineId".$lineId);
            $user = \ORM\UserQuery::create()->filterByLineId($lineId)->findOne();
            $item = \ORM\ItemQuery::create()
                ->findByUserId($user->getUserId())
                ->orderByExpireDate()
                ->findPk(1);

            $post = array(
                'replyToken' => $token,
                'messages' => array(
                    'type' => 'text',
                    'text' => $item->getItemName().'の賞味期限があと'.$item->getExpireDate().'です！'
                )
            );
        }

        if($event->type == 'join') {
            $redis = new Redis();
            $redis.connect("127.0.0.1", 6379);

            $redis.rPush('familyTokens', $event->source->roomId);
            
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
        return $result;
        }

        $this->logger->addDebug(getenv("LINE_CHANNEL_ACCESS_TOKEN"));
        $json_string = file_get_contents('php://input');
        $json_object = json_decode($json_string);

        var_dump($json_object);
        $this->logger->addDebug("json_string".$json_string);

        $result = "";
        if(isset($json_object->events)) {
            foreach ($json_object->events as $event) {
                if('message' == $event->type) {
                    $result = api_post_request($event->replyToken, $event->message->text);
                }else if('beacon' == $event->type) {
                    api_post_request($event->replyToken, $event);
                }
            }
        }

        $data =[
            "status" => "OK",
            "result" => $result
                ];

        return $response->withJson($data);

});
