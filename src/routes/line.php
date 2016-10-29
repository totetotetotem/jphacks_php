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
        if($event->type == 'message') {
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

        if($event->type == 'beacon') {
            $lineId = $event->source->userId;
            $this->logger->addDebug("lineId".$lineId);
            $user = \ORM\UserQuery::create()->filterByLineId($lineId)->findOne();
            $item = \ORM\ItemQuery::create()
                ->filterByFamilyId((int)$user->getFamilyId())
                ->orderByExpireDate()
                ->findOne();


            $this->logger->addDebug("item".$item->getItemName());
            $message_text = 'expire date of '.$item->getItemName().'is '.$item->getExpireDate()->format('Y-m-d');
            $this->logger->addDebug("messages".$message_text);
 
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


