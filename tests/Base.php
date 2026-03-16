<?php

namespace AdinanCenci\Psr18\Tests;

use AdinanCenci\Psr17\RequestFactory;
use AdinanCenci\Psr17\ResponseFactory;
use AdinanCenci\Psr17\StreamFactory;
use AdinanCenci\Psr18\Client;
use PHPUnit\Framework\TestCase;

abstract class Base extends TestCase
{
    public function getRequestFactory()
    {
        return new RequestFactory();
    }

    public function getClient()
    {
        return new Client(new ResponseFactory(), new StreamFactory());
    }
}
