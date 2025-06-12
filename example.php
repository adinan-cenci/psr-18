<?php

use AdinanCenci\Psr18\Client;
use AdinanCenci\Psr17\ResponseFactory;
use AdinanCenci\Psr17\StreamFactory;
use AdinanCenci\Psr17\RequestFactory;

if (file_exists('vendor/autoload.php')) {
    require 'vendor/autoload.php';
} else {
    echo 'error: autoload not found.';
}

$responseFactory = new ResponseFactory();
$streamFactory   = new StreamFactory();
$requestFactory  = new RequestFactory();
$client          = new Client($responseFactory, $streamFactory);

$request = $requestFactory->createRequest('GET', 'https://api.thecatapi.com/v1/images/search?size=med&mime_types=jpg&format=json&has_breeds=true&order=RANDOM&page=0&limit=1');

try {
    $response = $client->sendRequest($request);
} catch (\Exception $e) {
    echo get_class($e) . PHP_EOL;
    echo $e->getMessage();
    die();
}

echo $response->getBody() . PHP_EOL;
echo $response->getHeaderLine('security') . PHP_EOL;
