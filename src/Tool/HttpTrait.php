<?php

namespace Stevenmaguire\Yelp\Tool;

use \Exception;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Stevenmaguire\Yelp\Exception\ClientConfigurationException;
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
     * @var \GuzzleHttp\Client
     */
    protected $httpClient;

    /**
     * HTTP scheme
     *
     * @var string
     */
    protected $scheme;

    /**
     * Prepares and appends parameters, if provided, to the given url.
     *
     * @param  string     $url
     * @param  array      $parameters
     * @param  string[]   $options
     *
     * @return string
     */
    protected function appendParametersToUrl($url, array $parameters = array(), array $options = array())
    {
        $url = rtrim($url, '?');
        $queryString = $this->prepareQueryParams($parameters, $options);

        if ($queryString) {
            $uri = new Uri($url);
            $existingQuery = $uri->getQuery();
            $updatedQuery = empty($existingQuery) ? $queryString : $existingQuery . '&' . $queryString;
            $url = (string) $uri->withQuery($updatedQuery);
        }

        return $url;
    }

    /**
     * Flattens given array into comma separated value.
     *
     * @param  mixed   $input
     *
     * @return string|mixed
     */
    private function arrayToCsv($input)
    {
        if (is_array($input)) {
            $input = implode(',', $input);
        }

        return $input;
    }

    /**
     * Coerces given value into boolean and returns string representation
     *
     * @param  boolean   $value
     *
     * @return string
     */
    private function getBoolString($value)
    {
        return (bool) $value ? 'true' : 'false';
    }

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
     * @return Request
     */
    public function getRequest(
        $method,
        $uri,
        array $headers = [],
        $body = null,
        $version = '1.1'
    ) {
        $uri = new Uri($uri);

        if (!$uri->getHost()) {
            $uri = $uri->withHost($this->apiHost);
        }

        if (!$uri->getScheme()) {
            $uri = $uri->withScheme(($this->scheme ?: 'https'));
        }

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
     * @throws Stevenmaguire\Yelp\Exception\HttpException
     */
    public function getResponse(RequestInterface $request)
    {
        try {
            return $this->getHttpClient()->send($request);
        } catch (BadResponseException $e) {
            $exception = new HttpException($e->getMessage());

            throw $exception->setResponseBody((string) $e->getResponse()->getBody());
        }
    }

    /**
     * Provides a hook that handles the response before returning to the consumer.
     *
     * @param ResponseInterface $response
     *
     * @return  ResponseInterface
     */
    abstract protected function handleResponse(ResponseInterface $response);

    /**
     * Updates query params array to apply yelp specific formatting rules.
     *
     * @param  array      $params
     * @param  string[]   $csvParams
     *
     * @return string
     */
    protected function prepareQueryParams($params = [], $csvParams = [])
    {
        array_walk($params, function ($value, $key) use (&$params, $csvParams) {
            if (is_bool($value)) {
                $params[$key] = $this->getBoolString($value);
            }

            if (in_array($key, $csvParams)) {
                $params[$key] = $this->arrayToCsv($value);
            }
        });

        return http_build_query($params);
    }

    /**
     * Makes a request to the Yelp API and returns the response
     *
     * @param    RequestInterface $request
     *
     * @return   stdClass The JSON response from the request
     * @throws   Stevenmaguire\Yelp\Exception\ClientConfigurationException
     * @throws   Stevenmaguire\Yelp\Exception\HttpException
     */
    protected function processRequest(RequestInterface $request)
    {
        $response = $this->handleResponse($this->getResponse($request));

        return json_decode($response->getBody());
    }

    /**
     * Updates the yelp client's http client to the given http client. Client.
     *
     * @param HttpClient  $client
     *
     * @return  mixed
     */
    public function setHttpClient(HttpClient $client)
    {
        $this->httpClient = $client;

        return $this;
    }
}
