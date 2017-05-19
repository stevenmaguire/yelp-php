<?php

namespace Stevenmaguire\Yelp\Tool;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Stevenmaguire\Yelp\Exception\HttpException;

trait HttpTrait
{
    /**
     * API host url
     *
     * @var string
     */
    protected $apiHost;

    /**
     * HTTP client
     *
     * @var GuzzleHttp\Client
     */
    protected $httpClient;

    /**
     * Returns the yelp client's http client to the given http client. Client.
     *
     * @return  GuzzleHttp\Client|null
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * Creates a PSR-7 Request instance.
     *
     * @param  null|string $method HTTP method for the request.
     * @param  null|string $uri URI for the request.
     * @param  array $headers Headers for the message.
     * @param  string|resource|StreamInterface $body Message body.
     * @param  string $version HTTP protocol version.
     *
     * @return GuzzleHttp\Psr7\Request
     */
    public function getRequest(
        $method,
        $uri,
        array $headers = [],
        $body = null,
        $version = '1.1'
    ) {
        return new Request($method, $uri, $headers, $body, $version);
    }

    /**
     * Sends a request instance and returns a response instance.
     *
     * WARNING: This method does not attempt to catch exceptions caused by HTTP
     * errors! It is recommended to wrap this method in a try/catch block.
     *
     * @param  RequestInterface $request
     * @return ResponseInterface
     */
    public function getResponse(RequestInterface $request)
    {
        return $this->getHttpClient()->send($request);
    }

    /**
     * Updates query params array to apply yelp specific formatting rules.
     *
     * @param  array   $params
     *
     * @return string
     */
    protected function prepareQueryParams($params = [])
    {
        array_walk($params, function ($value, $key) use (&$params) {
            if (is_bool($value)) {
                $params[$key] = $value ? 'true' : 'false';
            }
        });

        return http_build_query($params);
    }

    /**
     * Makes a request to the Yelp API and returns the response
     *
     * @param    string $path    The path of the APi after the domain
     *
     * @return   stdClass The JSON response from the request
     * @throws   Stevenmaguire\Yelp\Exception\HttpException
     */
    protected function processRequest(RequestInterface $request)
    {
        try {
            $response = $this->getResponse($request);

            return json_decode($response->getBody());
        } catch (ClientException $e) {
            $exception = new HttpException($e->getMessage());

            throw $exception->setResponseBody($e->getResponse()->getBody());
        }
    }

    /**
     * Updates the yelp client's http client to the given http client. Client.
     *
     * @param GuzzleHttp\Client  $client
     *
     * @return  mixed
     */
    public function setHttpClient(HttpClient $client)
    {
        $this->httpClient = $client;

        return $this;
    }
}
