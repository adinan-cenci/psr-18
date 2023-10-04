<?php 
use AdinanCenci\Psr18\Client;
use AdinanCenci\Psr17\ResponseFactory;
use AdinanCenci\Psr17\StreamFactory;
use AdinanCenci\Psr17\RequestFactory;

require 'vendor/autoload.php';

$responseFactory = new ResponseFactory();
$streamFactory   = new StreamFactory();
$requestFactory  = new RequestFactory();
$client          = new Client($responseFactory, $streamFactory);

$request = $requestFactory->createRequest('GET', 'https://swapi.dev/api/people/1/');

try {
    $response = $client->sendRequest($request);
} catch(\Exception $e) {
    echo $e->getMessage();
    die();
}

echo $response->getBody();
echo '<hr>';
echo $response->getHeaderLine('security');

