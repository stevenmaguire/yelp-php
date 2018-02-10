<?php

namespace Stevenmaguire\Yelp\Contract;

use GuzzleHttp\Client as HttpClient;
use Psr\Http\Message\RequestInterface;

interface Http
{
    /**
     * Creates default http client with appropriate authorization configuration.
     *
     * @return \GuzzleHttp\Client
     */
    public function createDefaultHttpClient();

    /**
     * Returns the yelp client's http client to the given http client. Client.
     *
     * @return  \GuzzleHttp\Client|null
     */
    public function getHttpClient();

    /**
     * Creates a PSR-7 Request instance.
     *
     * @param  null|string $method HTTP method for the request.
     * @param  null|string $uri URI for the request.
     * @param  array $headers Headers for the message.
     * @param  string|resource|StreamInterface $body Message body.
     * @param  string $version HTTP protocol version.
     *
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getRequest(
        $method,
        $uri,
        array $headers = [],
        $body = null,
        $version = '1.1'
    );

    /**
     * Sends a request instance and returns a response instance.
     *
     * WARNING: This method does not attempt to catch exceptions caused by HTTP
     * errors! It is recommended to wrap this method in a try/catch block.
     *
     * @param  \Psr\Http\Message\RequestInterface $request
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getResponse(RequestInterface $request);

    /**
     * Updates the yelp client's http client to the given http client. Client.
     *
     * @param \GuzzleHttp\Client  $client
     *
     * @return  mixed
     */
    public function setHttpClient(HttpClient $client);
}
