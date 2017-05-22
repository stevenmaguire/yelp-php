<?php

namespace Stevenmaguire\Yelp\Tool;

use \Exception;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
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
     * Prepares and appends parameters, if provided, to the given url.
     *
     * @param  string  $url
     * @param  array   $parameters
     * @param  array   $options
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
        $uri = new Uri($uri);

        if (!$uri->getHost()) {
            $uri = $uri->withHost($this->apiHost);
        }

        if (!$uri->getScheme()) {
            $uri = $uri->withScheme('https');
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

            throw $exception->setResponseBody($e->getResponse()->getBody());
        }
    }

    /**
     * Updates query params array to apply yelp specific formatting rules.
     *
     * @param  array   $params
     * @param  array   $options
     *
     * @return string
     */
    protected function prepareQueryParams($params = [], $csvParams = [])
    {
        $updateParam = function ($value, $key) use (&$params, $csvParams) {
            if (is_bool($value)) {
                $params[$key] = $value ? 'true' : 'false';
            }

            if (in_array($key, $csvParams) && is_array($value)) {
                $params[$key] = implode(',', $value);
            }
        };

        array_walk($params, $updateParam);

        return http_build_query($params);
    }

    /**
     * Makes a request to the Yelp API and returns the response
     *
     * @param    Psr\Http\Message\RequestInterface $request
     *
     * @return   stdClass The JSON response from the request
     * @throws   Stevenmaguire\Yelp\Exception\ClientConfigurationException
     * @throws   Stevenmaguire\Yelp\Exception\HttpException
     */
    protected function processRequest(RequestInterface $request)
    {
        $response = $this->getResponse($request);

        return json_decode($response->getBody());
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
