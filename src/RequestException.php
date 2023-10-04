<?php 
namespace AdinanCenci\Psr18;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Client\ClientExceptionInterface;

class RequestException extends ClientException implements RequestExceptionInterface, ClientExceptionInterface
{
    protected RequestInterface $request;

    public function setRequest(RequestInterface $request) 
    {
        $this->request = $request;
    }

    public function getRequest(): RequestInterface 
    {
        return $this->request;
    }
}
