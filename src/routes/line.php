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


        if($event->type === 'beacon') {
            $userId = $event->source->userId;
            $user = \ORM\UserQuery::create()->findByLineId($userId)->findPk(1);
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
