<?php

namespace AdinanCenci\Psr18;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class Client implements ClientInterface
{
    /**
     * PSR response factory.
     *
     * @var Psr\Http\Message\ResponseFactoryInterface
     */
    protected ResponseFactoryInterface $responseFactory;

    /**
     * PSR stream factory.
     *
     * @var Psr\Http\Message\StreamFactoryInterface
     */
    protected StreamFactoryInterface $streamFactory;

    /**
     * Constructor.
     *
     * @param Psr\Http\Message\ResponseFactoryInterface $responseFactory
     *   PSR response factory.
     * @var Psr\Http\Message\StreamFactoryInterface $streamFactory
     *   PSR stream factory.
     */
    public function __construct(ResponseFactoryInterface $responseFactory, StreamFactoryInterface $streamFactory)
    {
        $this->responseFactory = $responseFactory;
        $this->streamFactory   = $streamFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $requisition = new Requisition($request, $this->responseFactory, $this->streamFactory);
        return $requisition->execute();
    }
}
