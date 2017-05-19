<?php

namespace Stevenmaguire\Yelp\v2;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
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
     * Search path
     *
     * @var string
     */
    protected $searchPath = '/v2/search/';

    /**
     * Business path
     *
     * @var string
     */
    protected $businessPath = '/v2/business/';

    /**
     * Phone search path
     *
     * @var string
     */
    protected $phoneSearchPath = '/v2/phone_search/';

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
            'apiHost' => 'api.yelp.com'
        );

        $this->parseConfiguration($options, $defaults)
            ->createHttpClient();
    }

    /**
     * Build query string params using defaults
     *
     * @param  array $attributes
     *
     * @return string
     */
    public function buildQueryParams($attributes = [])
    {
        $defaults = array(
            'term' => $this->defaultTerm,
            'location' => $this->defaultLocation,
            'limit' => $this->searchLimit
        );
        $attributes = array_merge($defaults, $attributes);

        return $this->prepareQueryParams($attributes);
    }

    /**
     * Build unsigned url
     *
     * @param  string   $host
     * @param  string   $path
     *
     * @return string   Unsigned url
     */
    protected function buildUnsignedUrl($host, $path)
    {
        return "http://" . $host . $path;
    }

    /**
     * Builds and sets a preferred http client.
     *
     * @return Client
     */
    protected function createHttpClient()
    {
        $stack = HandlerStack::create();

        $middleware = new Oauth1([
            'consumer_key'    => $this->consumerKey,
            'consumer_secret' => $this->consumerSecret,
            'token'           => $this->token,
            'token_secret'    => $this->tokenSecret
        ]);

        $stack->push($middleware);

        $client = new HttpClient([
            'handler' => $stack
        ]);

        return $this->setHttpClient($client);
    }

    /**
     * Query the Business API by business id
     *
     * @param    string   $businessId      The ID of the business to query
     * @param    array    $attributes      Optional attributes to include in query string
     *
     * @return   stdClass                   The JSON response from the request
     */
    public function getBusiness($businessId, $attributes = [])
    {
        $businessPath = $this->businessPath . urlencode($businessId) . "?" . $this->prepareQueryParams($attributes);

        return $this->request($businessPath);
    }

    /**
     * Makes a request to the Yelp API and returns the response
     *
     * @param    string $path    The path of the APi after the domain
     *
     * @return   stdClass The JSON response from the request
     * @throws   Stevenmaguire\Yelp\Exception\HttpException
     */
    protected function request($path)
    {
        $url = $this->buildUnsignedUrl($this->apiHost, $path);

        try {
            $response = $this->getHttpClient()->get($url, ['auth' => 'oauth']);
        } catch (ClientException $e) {
            $exception = new HttpException($e->getMessage());

            throw $exception->setResponseBody($e->getResponse()->getBody());
        }

        return json_decode($response->getBody());
    }

    /**
     * Query the Search API by a search term and location
     *
     * @param    array    $attributes   Query attributes
     *
     * @return   stdClass               The JSON response from the request
     */
    public function search($attributes = [])
    {
        $query_string = $this->buildQueryParams($attributes);
        $searchPath = $this->searchPath . "?" . $query_string;

        return $this->request($searchPath);
    }

    /**
     * Search for businesses by phone number
     *
     * @see https://www.yelp.com/developers/documentation/v2/phone_search
     *
     * @param    array    $attributes   Query attributes
     *
     * @return   stdClass               The JSON response from the request
     */
    public function searchByPhone($attributes = [])
    {
        $searchPath = $this->phoneSearchPath . "?" . $this->prepareQueryParams($attributes);

        return $this->request($searchPath);
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
