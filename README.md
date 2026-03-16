# HTTP Client

This is my attempt to implement PSR-18.

## Instantiating

```php
use AdinanCenci\Psr18\Client;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

$client = new Client($responseFactory, $streamFactory);
```

## Requests

```php
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;

$request = $requestFactory->createRequest('GET', 'https://api.thecatapi.com/v1/images/search?size=med&mime_types=jpg&format=json&has_breeds=true&order=RANDOM&page=0&limit=1')

$response = $client->sendRequest($request);
```

## License
MIT
