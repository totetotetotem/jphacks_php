<?php
// Routes

$app->get('/[{name}]', function ($request, $response, $args) {
        // Sample log message
        $this->logger->info("Slim-Skeleton '/' route");

        // Render index view
        return $this->renderer->render($response, 'index.phtml', $args);
        });

$app->post('/line', function($request, $response, $args) {
        $json_string = file_get_contents('php://input');
        $json_object = json_decode($json_string);

        foreach ($json_object->events as $event) {
        if('message' == $event->type) {
        api_post_request($event->replyToken, $event->messge->text);
        }else if('beacon' == $event->type) {
        /* ここで賞味期限の近い食べ物のリストをとってきてメッセージを組み立てる */
        api_post_request($event->replyToken, 'Beacon event');
        }
        }

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

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_exec($curl);
        }
        });
