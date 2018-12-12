<?php

namespace Stevenmaguire\Yelp\v2;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Stevenmaguire\Yelp\Contract\Http as HttpContract;
use Stevenmaguire\Yelp\Exception\HttpException;
use Stevenmaguire\Yelp\Tool\ConfigurationTrait;
use Stevenmaguire\Yelp\Tool\HttpTrait;

class Client implements HttpContract
{
    use ConfigurationTrait,
        HttpTrait;

    /**
     * Consumer key
     *
     * @var string
     */
    protected $consumerKey;

    /**
     * Consumer secret
     *
     * @var string
     */
    protected $consumerSecret;

    /**
     * Access token
     *
     * @var string
     */
    protected $token;

    /**
     * Access token secret
     *
     * @var string
     */
    protected $tokenSecret;

    /**
     * Default search term
     *
     * @var string
     */
    protected $defaultTerm = 'bar';

    /**
     * Default location
     *
     * @var string
     */
    protected $defaultLocation = 'Chicago, IL';

    /**
     * Default search limit
     *
     * @var integer
     */
    protected $searchLimit = 3;

    /**
     * Create new client
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $defaults = array(
            'consumerKey' => null,
            'consumerSecret' => null,
            'token' => null,
            'tokenSecret' => null,
            'apiHost' => 'api.yelp.com',
            // 'scheme' => 'http'
        );

        $this->parseConfiguration($options, $defaults);

        if (!$this->getHttpClient()) {
            $this->setHttpClient($this->createDefaultHttpClient());
        }
    }

    /**
     * Creates default http client with appropriate authorization configuration.
     *
     * @return HttpClient
     */
    public function createDefaultHttpClient()
    {
        $stack = HandlerStack::create();

        $middleware = new Oauth1([
            'consumer_key'    => $this->consumerKey,
            'consumer_secret' => $this->consumerSecret,
            'token'           => $this->token,
            'token_secret'    => $this->tokenSecret
        ]);

        $stack->push($middleware);

        return new HttpClient([
            'handler' => $stack
        ]);
    }

    /**
     * Fetches a specific business by id.
     *
     * @param    string    $businessId
     * @param    array     $parameters
     *
     * @return   stdClass
     * @throws   Stevenmaguire\Yelp\Exception\HttpException
     */
    public function getBusiness($businessId, $parameters = [])
    {
        $path = $this->appendParametersToUrl('/v2/business/'.$businessId, $parameters);
        $request = $this->getRequest('GET', $path);

        return $this->processRequest($request);
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
            return $this->getHttpClient()->get((string) $request->getUri(), ['auth' => 'oauth']);
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
    protected function handleResponse(ResponseInterface $response)
    {
        return $response;
    }

    /**
     * Fetches results from the Business Search API.
     *
     * @param    array    $parameters
     *
     * @return   stdClass
     * @throws   Stevenmaguire\Yelp\Exception\HttpException
     */
    public function search($parameters = [])
    {
        $parameters = array_merge([
            'term' => $this->defaultTerm,
            'location' => $this->defaultLocation,
            'limit' => $this->searchLimit
        ], $parameters);

        $path = $this->appendParametersToUrl('/v2/search', $parameters);
        $request = $this->getRequest('GET', $path);

        return $this->processRequest($request);
    }

    /**
     * Fetches results from the Business Search API by Phone.
     *
     * @param    array    $parameters
     *
     * @return   stdClass
     * @throws   Stevenmaguire\Yelp\Exception\HttpException
     */
    public function searchByPhone($parameters = [])
    {
        $path = $this->appendParametersToUrl('/v2/phone_search', $parameters);
        $request = $this->getRequest('GET', $path);

        return $this->processRequest($request);
    }

    /**
     * Set default location
     *
     * @param string $location
     *
     * @return Client
     */
    public function setDefaultLocation($location)
    {
        $this->defaultLocation = $location;

        return $this;
    }

    /**
     * Set default term
     *
     * @param string $term
     *
     * @return Client
     */
    public function setDefaultTerm($term)
    {
        $this->defaultTerm = $term;

        return $this;
    }

    /**
     * Set search limit
     *
     * @param integer $limit
     *
     * @return Client
     */
    public function setSearchLimit($limit)
    {
        if (is_int($limit)) {
            $this->searchLimit = $limit;
        }

        return $this;
    }
}
