<?php 
namespace AdinanCenci\Psr18;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class Client implements ClientInterface
{
    protected ResponseFactoryInterface $responseFactory;

    protected StreamFactoryInterface $streamFactory;

    public function __construct(ResponseFactoryInterface $responseFactory, StreamFactoryInterface $streamFactory) 
    {
        $this->responseFactory = $responseFactory;
        $this->streamFactory   = $streamFactory;
    }

    public function sendRequest(RequestInterface $request) : ResponseInterface 
    {
        $requisition = new Requisition($request, $this->responseFactory, $this->streamFactory);
        return $requisition->execute();
    }
}
