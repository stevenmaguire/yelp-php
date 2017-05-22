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
     * @var GuzzleHttp\Client
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
        $queryString = $this->prepareQueryParams($parameters, $options);

        if ($queryString) {
            if (strpos($url, '?') !== false) {
                $url .= '&' . $queryString;
            } else {
                $url .= '?' . $queryString;
            }
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
        $uriComponents = parse_url($uri);

        if (!isset($uriComponents['host'])) {
            $uriComponents['host'] = $this->apiHost;
        }

        if (!isset($uriComponents['scheme'])) {
            $uriComponents['scheme'] = 'https';
        }

        $uri = (string) Uri::fromParts($uriComponents);

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
     * @return string|null
     */
    protected function prepareQueryParams($params = [], $options = [])
    {
        array_walk($params, function ($value, $key) use (&$params) {
            if (is_bool($value)) {
                $params[$key] = $value ? 'true' : 'false';
            }
            if (isset($options['to_csv'])) {
                if (!is_array($options['to_csv'])) {
                     $options['to_csv'] = explode(',', $options['to_csv']);
                }

                if (in_array($key, $options['to_csv']) && is_array($value)) {
                    $params[$key] = implode(',', $value);
                }
            }
        });

        $queryString = http_build_query($params);

        if (strlen($queryString)) {
            return $queryString;
        }

        return null;
    }

    /**
     * Makes a request to the Yelp API and returns the response
     *
     * @param    string $path    The path of the APi after the domain
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
