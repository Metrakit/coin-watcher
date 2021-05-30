<?php

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

function callTelegram($crypto, $price, $isSup)
{
    $telegramKey = $_ENV['TELEGRAM_KEY'];
    $chatId = $_ENV['CHAT_ID'];
    $text = "ISSOU !!! C'est l'heure de refill. $crypto est à $price.";
    if ($isSup) {
        $text = "ISSOU !!! Allez on vends ! $crypto est à $price.";
    }
    $client = new Client();
    $request = new Request('GET', "https://api.telegram.org/bot$telegramKey/sendMessage?chat_id=$chatId&text=$text");
    return $client->send($request, ['timeout' => 10]);
}
