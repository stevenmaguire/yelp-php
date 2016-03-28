<?php namespace Stevenmaguire\Yelp;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use GuzzleHttp\Exception\ClientException;

class Client
{
    /**
     * API host url
     *
     * @var string
     */
    protected $apiHost;

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
     * [$httpClient description]
     *
     * @var [type]
     */
    protected $httpClient;

    /**
     * Create new client
     *
     * @param array $configuration
     */
    public function __construct($configuration = [])
    {
        $this->parseConfiguration($configuration)
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
     * Maps legacy configuration keys to updated keys.
     *
     * @param  array   $configuration
     *
     * @return array
     */
    protected function mapConfiguration(array $configuration)
    {
        array_walk($configuration, function ($value, $key) use (&$configuration) {
            $newKey = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $key))));
            $configuration[$newKey] = $value;
        });

        return $configuration;
    }

    /**
     * Parse configuration using defaults
     *
     * @param  array $configuration
     *
     * @return client
     */
    protected function parseConfiguration($configuration = [])
    {
        $defaults = array(
            'consumerKey' => null,
            'consumerSecret' => null,
            'token' => null,
            'tokenSecret' => null,
            'apiHost' => 'api.yelp.com'
        );

        $configuration = array_merge($defaults, $this->mapConfiguration($configuration));

        array_walk($configuration, [$this, 'setConfig']);

        return $this;
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
     * @throws   Exception
     */
    protected function request($path)
    {
        $url = $this->buildUnsignedUrl($this->apiHost, $path);

        try {
            $response = $this->httpClient->get($url, ['auth' => 'oauth']);
        } catch (ClientException $e) {
            $exception = new Exception($e->getMessage());

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
     * Attempts to set a given value.
     *
     * @param mixed   $value
     * @param string  $key
     *
     * @return Client
     */
    protected function setConfig($value, $key)
    {
        if (property_exists($this, $key)) {
            $this->$key = $value;
        }

        return $this;
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
     * Updates the yelp client's http client to the given http client. Client.
     *
     * @param HttpClient  $client
     *
     * @return  Client
     */
    public function setHttpClient(HttpClient $client)
    {
        $this->httpClient = $client;

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

    /**
     * Retrives the value of a given property from the client.
     *
     * @param  string  $property
     *
     * @return mixed|null
     */
    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }

        return null;
    }
}
