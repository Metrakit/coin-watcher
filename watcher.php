<?php
require 'vendor/autoload.php';
require 'telegram.php';
require 'bitquery.php';
require 'sqlite.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$coinlite = new CoinLite();

//$coinlite->insertSomeCoins();

$coins = $coinlite->getEnabledCoins();

foreach ($coins as $coin) {
    $bitquery = callBitquery($coin['coin_address'], $coin['pair_address']);
    if (sizeof($bitquery->data->ethereum->dexTrades) > 0) {
        $price = $bitquery->data->ethereum->dexTrades[0]->quotePrice;
        $crypto = $bitquery->data->ethereum->dexTrades[0]->baseCurrency->symbol;
        if (! $coin['is_sup'] && $price <= $coin['price']) {
            callTelegram($crypto, $price, false);
            $coinlite->disableCoin($coin['id']);
        }
        if ($coin['is_sup'] && $price >= $coin['price']) {
            callTelegram($crypto, $price, true);
            $coinlite->disableCoin($coin['id']);
        }
    } else {
        echo 'COIN NOT FOUND : ' . $coin['coin_name'];
    }
}


