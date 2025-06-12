<?php

namespace AdinanCenci\Psr18;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Client\ClientExceptionInterface;

class RequestException extends ClientException implements RequestExceptionInterface, ClientExceptionInterface
{
    /**
     * The request that caused the exception.
     *
     * @var Psr\Http\Message\RequestInterface
     */
    protected RequestInterface $request;

    /**
     * Sets the request that caused the exception.
     *
     * @param Psr\Http\Message\RequestInterface $request
     *   The request.
     */
    public function setRequest(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * Returns the request that caused the exception.
     *
     * @return Psr\Http\Message\RequestInterface
     *   The request.
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
