<?php 
namespace AdinanCenci\Psr18;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Client\ClientExceptionInterface;

class ClientException extends \Exception implements ClientExceptionInterface 
{
    
}
