<?php

namespace AdinanCenci\Psr18;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class Requisition
{
    /**
     * The request to be made.
     *
     * @var Psr\Http\Message\RequestInterface
     */
    protected RequestInterface $request;

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
     * The curl handler.
     *
     * @var \CurlHandle
     */
    protected \CurlHandle $curl;

    /**
     * Constructor.
     *
     * @param Psr\Http\Message\RequestInterface $request
     *   The request to be made.
     * @param Psr\Http\Message\ResponseFactoryInterface $responseFactory
     *   PSR response factory.
     * @param Psr\Http\Message\StreamFactoryInterface $streamFactory
     *   PSR stream factory.
     */
    public function __construct(
        RequestInterface $request,
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory
    ) {
        $this->request         = $request;
        $this->responseFactory = $responseFactory;
        $this->streamFactory   = $streamFactory;
    }

    /**
     * Makes the request.
     *
     * And return the response.
     *
     * @return Psr\Http\Message\ResponseInterface
     *   The response object.
     */
    public function execute(): ResponseInterface
    {
        $this->curl = $this->buildCurl();
        $content  = $this->executeCurl();
        $response = $this->parseContent($content);

        curl_close($this->curl);

        return $response;
    }

    /**
     * Builds the curl object out of the request.
     *
     * @return \CurlHandle
     *   The curl handle.
     */
    protected function buildCurl(): \CurlHandle
    {
        $curl = curl_init();

        $options = [
            CURLOPT_PORT           => $this->getPort(),
            CURLOPT_HTTP_VERSION   => $this->getCurlHttpVersion($this->request->getProtocolVersion()),
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

    /**
     * Returns the port for the request.
     *
     * If not specified in the request object it uses the defaults one,
     * depending on the schema.
     *
     * @return int
     *   The port.
     */
    protected function getPort(): int
    {
        $uri = $this->request->getUri();
        if ($uri->getPort() != null) {
            return (int) $uri->getPort();
        }

        return $uri->getScheme() == 'https'
            ? 443
            : 80;
    }

    /**
     * Executes the curl handle and returns the content received.
     *
     * @return string
     *   The response.
     */
    protected function executeCurl(): string
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

    /**
     * Parses the curl response into a PSR response.
     *
     * @param string $content
     *   The response from curl.
     *
     * @return Psr\Http\Message\ResponseInterface
     *   The response object.
     */
    protected function parseContent(string $content): ResponseInterface
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

    /**
     * Parses a header line into its name and value.
     *
     * @p
     */
    protected function parseHeaders(string $headerPart, ?string &$reasonPhrase = ''): array
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

    /**
     * Adds headers to response.
     *
     * @param Psr\Http\Message\ResponseInterface $response
     *   The response.
     * @param array $headers
     *   The headers to add to the response.
     *
     * @return Psr\Http\Message\ResponseInterface
     *   The new response with the headers added.
     */
    protected function setHeaders(ResponseInterface $response, array $headers): ResponseInterface
    {
        foreach ($headers as $header) {
            $response = $response->hasHeader($header[0])
                ? $response->withAddedHeader($header[0], $header[1])
                : $response->withHeader($header[0], $header[1]);
        }

        return $response;
    }

    /**
     * Returns the request's headers a flat array of strings.
     *
     * @param Psr\Http\Message\RequestInterface $request
     *   The request.
     *
     * @return array
     *   The headers as a single dimensional array.
     */
    protected function getHeadersArray(RequestInterface $request): array
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

    /**
     * Checks if the values of a header contain fields.
     *
     * @param array $header
     *   Header's values.
     *
     * @return bool
     *   True if there are filds in the header's values.
     */
    protected function containFields(array $header): bool
    {
        foreach ($header as $h) {
            if (substr_count($h, '=') || substr_count($h, ';')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return the correct CRL constant given a http version.
     *
     * @param string $version
     *   Http version.
     *
     * @return int
     *   The corresponding curl option.
     */
    protected function getCurlHttpVersion(string $version): int
    {
        switch ($version) {
            case '1':
            case '1.0':
                return CURL_HTTP_VERSION_1_0;
                break;
            case '1.1':
                return CURL_HTTP_VERSION_1_1;
                break;
            case '2':
            case '2.0':
                return CURL_HTTP_VERSION_2;
                break;
            case '3':
            case '3.0':
                return CURL_HTTP_VERSION_3;
                break;
            default:
                return CURL_HTTP_VERSION_NONE;
                break;
        }
    }
}
