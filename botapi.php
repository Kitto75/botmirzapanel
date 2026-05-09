<?php

/*
|--------------------------------------------------------------------------
| Proxy Settings
|--------------------------------------------------------------------------
| ENABLE:
| true  => use proxy
| false => direct connection
|
| TYPE:
| socks5
| http
*/

$PROXY_CONFIG = [
    'ENABLE'   => true,

    // socks5 or http
    'TYPE'     => 'socks5',

    // proxy host/ip
    'HOST'     => '127.0.0.1',

    // proxy port
    'PORT'     => '1080',

    // optional username/password
    'USERNAME' => '',
    'PASSWORD' => '',
];

function telegram($method, $datas = [])
{
    global $APIKEY, $PROXY_CONFIG;

    $url = "https://api.telegram.org/bot" . $APIKEY . "/" . $method;

    $ch = curl_init();

    /*
    |--------------------------------------------------------------------------
    | Proxy Support
    |--------------------------------------------------------------------------
    */
    if ($PROXY_CONFIG['ENABLE']) {

        curl_setopt(
            $ch,
            CURLOPT_PROXY,
            $PROXY_CONFIG['HOST'] . ':' . $PROXY_CONFIG['PORT']
        );

        // proxy type
        if (strtolower($PROXY_CONFIG['TYPE']) == 'socks5') {

            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);

        } else {

            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
        }

        // proxy auth
        if (
            !empty($PROXY_CONFIG['USERNAME']) &&
            !empty($PROXY_CONFIG['PASSWORD'])
        ) {

            curl_setopt(
                $ch,
                CURLOPT_PROXYUSERPWD,
                $PROXY_CONFIG['USERNAME'] . ':' . $PROXY_CONFIG['PASSWORD']
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Telegram Request
    |--------------------------------------------------------------------------
    */
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $datas);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);

    $result = curl_exec($ch);

    if (curl_error($ch)) {

        error_log(curl_error($ch));

        return false;
    }

    $res = json_decode($result, true);

    if (!$res['ok']) {

        error_log(json_encode($res));
    }

    curl_close($ch);

    return $res;
}

function sendmessage($chat_id,$text,$keyboard,$parse_mode){
    return telegram('sendmessage',[
        'chat_id' => $chat_id,
        'text' => $text,
        'disable_web_page_preview' => true,
        'reply_markup' => $keyboard,
        'parse_mode' => $parse_mode,

    ]);
}

function forwardMessage($chat_id,$message_id,$chat_id_user){
    return telegram('forwardMessage',[
        'from_chat_id'=> $chat_id,
        'message_id'=> $message_id,
        'chat_id'=> $chat_id_user,
    ]);
}

function sendphoto($chat_id,$photoid,$caption,$parse_mode = "HTML"){
    telegram('sendphoto',[
        'chat_id' => $chat_id,
        'photo'=> $photoid,
        'caption'=> $caption,
        'parse_mode' => $parse_mode,
    ]);
}

function sendvideo($chat_id,$videoid,$caption){
    telegram('sendvideo',[
        'chat_id' => $chat_id,
        'video'=> $videoid,
        'caption'=> $caption,
    ]);
}

function Editmessagetext($chat_id, $message_id, $text, $keyboard,$parse_mode = "html"){
    return telegram('editmessagetext', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => $text,
        'reply_markup' => $keyboard,
        'parse_mode'=> $parse_mode
    ]);
}

function deletemessage($chat_id, $message_id){
    telegram('deletemessage', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
    ]);
}

function sendDocument($chat_id, $documentPath, $caption) {
    return telegram('sendDocument',[
        'chat_id' => $chat_id,
        'document' => new CURLFile($documentPath),
        'caption' => $caption,
    ]);
}

#-----------------------------#

$update = json_decode(file_get_contents("php://input"), true);

$from_id = $update['message']['from']['id'] ?? $update['callback_query']['from']['id'] ?? 0;

$Chat_type = $update["message"]["chat"]["type"] ?? $update['callback_query']['message']['chat']['type'] ?? '';

$text = $update["message"]["text"] ?? '';

$text_callback = $update["callback_query"]["message"]["text"] ?? '';

$message_id = $update["message"]["message_id"] ?? $update["callback_query"]["message"]["message_id"] ?? 0;

$photo = $update["message"]["photo"] ?? 0;

$photoid = $photo ? end($photo)["file_id"] : '';

$caption = $update["message"]["caption"] ?? $update['callback_query']['message']["caption"]  ?? '';

$video = $update["message"]["video"] ?? 0;

$videoid = $video ? $video["file_id"] : 0;

$forward_from_id = $update["message"]["reply_to_message"]["forward_from"]["id"] ?? 0;

$datain = $update["callback_query"]["data"] ?? '';

$username = $update['message']['from']['username'] ?? $update['callback_query']['from']['username'] ?? 'NOT_USERNAME';

$user_phone =$update["message"]["contact"]["phone_number"] ?? 0;

$contact_id = $update["message"]["contact"]["user_id"] ?? 0;

$first_name = $update['message']['from']['first_name']  ?? $update["callback_query"]["from"]["first_name"] ?? '';

$callback_query_id = $update["callback_query"]["id"] ?? 0;
