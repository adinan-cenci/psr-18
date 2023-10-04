<?php 
namespace AdinanCenci\Psr18;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class Requisition 
{
    protected RequestInterface $request;

    protected ResponseFactoryInterface $responseFactory;

    protected StreamFactoryInterface $streamFactory;

    protected \CurlHandle $curl;

    public function __construct(RequestInterface $request, ResponseFactoryInterface $responseFactory, StreamFactoryInterface $streamFactory) 
    {
        $this->request         = $request;
        $this->responseFactory = $responseFactory;
        $this->streamFactory   = $streamFactory;
    }

    public function execute() : ResponseInterface 
    {
        $this->curl = $this->buildCurl();
        $content  = $this->executeCurl();
        $response = $this->parseContent($content);

        curl_close($this->curl);

        return $response;
    }

    protected function buildCurl() : \CurlHandle
    {
        $curl = curl_init();

        $options = [
            CURLOPT_PORT           => $this->getPort(),
            CURLOPT_HTTP_VERSION   => $this->request->getProtocolVersion(),
            CURLOPT_POSTFIELDS     => (string) $this->request->getBody(),
            CURLOPT_URL            => (string) $this->request->getUri(), //$this->request->getRequestTarget(),
            CURLOPT_HTTPHEADER     => $this->getHeadersArray($this->request),
            CURLOPT_CUSTOMREQUEST  => $this->request->getMethod(),
            CURLOPT_HEADER         => true, // include headers in the output.
            CURLOPT_RETURNTRANSFER => true, // return transfer
        ];

        curl_setopt_array($curl, $options);

        return $curl;
    }

    protected function getPort() : int
    {
        $uri = $this->request->getUri();
        if ($uri->getPort() != null) {
            return (int) $uri->getPort();
        }

        return $uri->getScheme() == 'https'
            ? 443
            : 80;
    }

    protected function executeCurl() : string
    {
        $content = curl_exec($this->curl);

        $errNo = curl_errno($this->curl);
        $errMsg = curl_error($this->curl);

        if ($errNo) {
            $exception = new RequestException($errMsg, $errNo);
            $exception->setRequest($this->request);
            throw $exception;
        }

        return $content;
    }

    protected function parseContent(string $content) : ResponseInterface
    {
        $headerSize = curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);
        $headerPart = substr($content, 0, $headerSize);
        $bodyPart   = substr($content, $headerSize);

        $code       = (int) curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
        $body       = $this->streamFactory->createStream($bodyPart);

        $headers    = $this->parseHeaders($headerPart, $reasonPhrase);

        $response   = $this->responseFactory->createResponse($code, $reasonPhrase);
        $response   = $this->setHeaders($response, $headers);
        $response   = $response->withBody($body);

        return $response;
    }

    protected function parseHeaders(string $headerPart, ?string &$reasonPhrase = '') : array
    {
        $lines = explode("\r\n", $headerPart);
        $codeAndPhrase = array_shift($lines);

        $reasonPhrase = $codeAndPhrase;

        $headers = [];
        foreach ($lines as $line) {
            if (preg_match('/([a-zA-Z\d]+): ?(.*)/', $line, $matches)) {
                $headers[] = [
                    $matches[1],
                    $matches[2]
                ];
            } else {
                // something went wrong.
            }
        }

        return $headers;
    }

    protected function setHeaders(ResponseInterface $response, array $headers) : ResponseInterface
    {
        foreach ($headers as $header) {
            $response = $response->hasHeader($header[0])
                ? $response->withAddedHeader($header[0], $header[1])
                : $response->withHeader($header[0], $header[1]);
        }

        return $response;
    }

    protected function getHeadersArray(RequestInterface $request) : array
    {
        $array = [];

        foreach ($request->getHeaders() as $headerName => $value) {
            if (is_string($value)) {
                $array[] = $headerName . ': ' . $value;
                continue;
            }

            if (! $this->containFields($value)) {
                $array[] = $headerName . ': ' . implode(' ', $value);
                continue;
            }

            foreach ($value as $v) {
                $array[] = $headerName . ': ' . $v;
            }
        }

        return $array;
    }

    protected function containFields(array $header) : bool
    {
        foreach ($header as $h) {
            if (substr_count($h, '=') || substr_count($h, ';')) {
                return true;
            }
        }

        return false;
    }
}
