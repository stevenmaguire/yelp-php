<?php

namespace Stevenmaguire\Yelp\v3;

use GuzzleHttp\Client as HttpClient;
use Psr\Http\Message\ResponseInterface;
use Stevenmaguire\Yelp\Contract\Http as HttpContract;
use Stevenmaguire\Yelp\Tool\ConfigurationTrait;
use Stevenmaguire\Yelp\Tool\HttpTrait;

class Client implements HttpContract
{
    use ConfigurationTrait,
        HttpTrait;

    /**
     * Access token
     *
     * @var string
     */
    protected $accessToken;

    /**
     * Api key
     *
     * @var string
     */
    protected $apiKey;

    /**
     * Rate limit
     *
     * @var RateLimit|null
     */
    protected $rateLimit;

    /**
     * Creates new client
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $defaults = [
            'accessToken' => null,
            'apiHost' => 'api.yelp.com',
            'apiKey' => null,
        ];

        $this->parseConfiguration($options, $defaults);

        if (!$this->getHttpClient()) {
            $this->setHttpClient($this->createDefaultHttpClient());
        }
    }

    /**
     * Creates default http client with appropriate authorization configuration.
     *
     * @return \GuzzleHttp\Client
     */
    public function createDefaultHttpClient()
    {
        return new HttpClient([
            'headers' => $this->getDefaultHeaders()
        ]);
    }

    /**
     * Fetches results from the Autocomplete API.
     *
     * @param    array    $parameters
     *
     * @return   stdClass
     * @throws   Stevenmaguire\Yelp\Exception\HttpException
     * @link     https://www.yelp.com/developers/documentation/v3/autocomplete
     */
    public function getAutocompleteResults($parameters = [])
    {
        $path = $this->appendParametersToUrl('/v3/autocomplete', $parameters);
        $request = $this->getRequest('GET', $path, $this->getDefaultHeaders());

        return $this->processRequest($request);
    }

    /**
     * Returns the api key, if available, otherwise returns access token.
     *
     * @return string|null
     * @link https://www.yelp.com/developers/documentation/v3/authentication#where-is-my-client-secret-going
     */
    private function getBearerToken()
    {
        if ($this->apiKey) {
            return $this->apiKey;
        }

        return $this->accessToken;
    }

    /**
     * Fetches a specific business by id.
     *
     * @param    string    $businessId
     * @param    array     $parameters
     *
     * @return   stdClass
     * @throws   Stevenmaguire\Yelp\Exception\HttpException
     * @link     https://www.yelp.com/developers/documentation/v3/business
     */
    public function getBusiness($businessId, $parameters = [])
    {
        $path = $this->appendParametersToUrl('/v3/businesses/'.$businessId, $parameters);
        $request = $this->getRequest('GET', $path, $this->getDefaultHeaders());

        return $this->processRequest($request);
    }

    /**
     * Fetches reviews for a specific business by id.
     *
     * @param    string    $businessId
     * @param    array     $parameters
     *
     * @return   stdClass
     * @throws   Stevenmaguire\Yelp\Exception\HttpException
     * @link     https://www.yelp.com/developers/documentation/v3/business_reviews
     */
    public function getBusinessReviews($businessId, $parameters = [])
    {
        $path = $this->appendParametersToUrl('/v3/businesses/'.$businessId.'/reviews', $parameters);
        $request = $this->getRequest('GET', $path, $this->getDefaultHeaders());

        return $this->processRequest($request);
    }

    /**
     * Fetches results from the Business Search API.
     *
     * @param    array    $parameters
     *
     * @return   stdClass
     * @throws   Stevenmaguire\Yelp\Exception\HttpException
     * @link     https://www.yelp.com/developers/documentation/v3/business_search
     */
    public function getBusinessesSearchResults($parameters = [])
    {
        $csvParams = ['attributes', 'categories', 'price'];

        $path = $this->appendParametersToUrl('/v3/businesses/search', $parameters, $csvParams);
        $request = $this->getRequest('GET', $path, $this->getDefaultHeaders());

        return $this->processRequest($request);
    }

    /**
     * Fetches results from the Business Search API by Phone.
     *
     * @param    string    $phoneNumber
     *
     * @return   stdClass
     * @throws   Stevenmaguire\Yelp\Exception\HttpException
     * @link     https://www.yelp.com/developers/documentation/v3/business_search_phone
     */
    public function getBusinessesSearchResultsByPhone($phoneNumber)
    {
        $parameters = [
            'phone' => $phoneNumber
        ];

        $path = $this->appendParametersToUrl('/v3/businesses/search/phone', $parameters);
        $request = $this->getRequest('GET', $path, $this->getDefaultHeaders());

        return $this->processRequest($request);
    }

    /**
     * Builds and returns default headers, specifically including the Authorization
     * header used for authenticating HTTP requests to Yelp.
     *
     * @return array
     */
    protected function getDefaultHeaders()
    {
        return [
            'Authorization' => 'Bearer ' . $this->getBearerToken(),
        ];
    }

    /**
     * Returns the latest rate limit metrics, absorbed from the HTTP headers of
     * the most recent HTTP request to the Yelp v3 service.
     *
     * @return RateLimit|null
     *
     * @see https://www.yelp.com/developers/documentation/v3/rate_limiting
     */
    public function getRateLimit()
    {
        return $this->rateLimit;
    }

    /**
     * Fetches results from the Business Search API by Type.
     *
     * @param    string    $type
     * @param    array     $parameters
     *
     * @return   stdClass
     * @throws   Stevenmaguire\Yelp\Exception\HttpException
     * @link     https://www.yelp.com/developers/documentation/v3/transactions_search
     */
    public function getTransactionsSearchResultsByType($type, $parameters = [])
    {
        $path = $this->appendParametersToUrl('/v3/transactions/'.$type.'/search', $parameters);
        $request = $this->getRequest('GET', $path, $this->getDefaultHeaders());

        return $this->processRequest($request);
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
        $this->rateLimit = new RateLimit;
        $this->rateLimit->dailyLimit = (integer) $response->getHeaderLine('RateLimit-DailyLimit');
        $this->rateLimit->remaining = (integer) $response->getHeaderLine('RateLimit-Remaining');
        $this->rateLimit->resetTime = $response->getHeaderLine('RateLimit-ResetTime');

        return $response;
    }
}
