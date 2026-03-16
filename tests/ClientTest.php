<?php

namespace AdinanCenci\Psr18\Tests;

use AdinanCenci\Psr17\RequestFactory;
use AdinanCenci\Psr17\ResponseFactory;
use AdinanCenci\Psr17\StreamFactory;
use AdinanCenci\Psr18\Client;

class ClientTest extends Base
{
    public function testGetRequest()
    {
        $request = $this
            ->getRequestFactory()
            ->createRequest('GET', 'https://api.thecatapi.com/v1/images/search?size=med&mime_types=jpg&format=json&has_breeds=true&order=RANDOM&page=0&limit=1');

        $respose = $this
            ->getClient()
            ->sendRequest($request);

        $this->assertEquals(200, $respose->getStatusCode());
        $this->assertTrue((bool) json_decode((string) $respose->getBody()));
    }
}
