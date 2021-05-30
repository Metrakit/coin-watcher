<?php

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

function callBitquery($coinAddress, $pairAddress)
{
    $query = "{
      ethereum(network: bsc) {
        dexTrades(
          baseCurrency: {is: \"$coinAddress\"}
          quoteCurrency: {is: \"$pairAddress\"}
          options: {desc: [\"block.height\", \"transaction.index\"], limit: 1}
        ) {
          block {
            height
            timestamp {
              time(format: \"%Y-%m-%d %H:%M:%S\")
            }
          }
          transaction {
            index
          }
          baseCurrency {
            symbol
          }
          quoteCurrency {
            symbol
          }
          quotePrice
        }
      }
    }";
    $bitqueryKey = $_ENV['BITQUERY_KEY'];
    $body = [
        'query' => $query
    ];
    $client = new Client();
    $headers = ['X-API-KEY' => $bitqueryKey, 'Content-Type' => 'application/json'];
    $request = new Request('POST', 'https://graphql.bitquery.io', $headers, json_encode($body));
    $response = $client->send($request, ['timeout' => 10]);
    return json_decode($response->getBody()->getContents());
}
