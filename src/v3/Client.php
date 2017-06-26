<?php

namespace Stevenmaguire\Yelp\v3;

use GuzzleHttp\Client as HttpClient;
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
     * Creates new client
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $defaults = [
            'accessToken' => null,
            'apiHost' => 'api.yelp.com'
        ];

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
        return new HttpClient([
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken,
            ]
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
        $request = $this->getRequest('GET', $path);

        return $this->processRequest($request);
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
        $request = $this->getRequest('GET', $path);

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
        $request = $this->getRequest('GET', $path);

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
        $request = $this->getRequest('GET', $path);

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
        $request = $this->getRequest('GET', $path);

        return $this->processRequest($request);
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
        $request = $this->getRequest('GET', $path);

        return $this->processRequest($request);
    }
}
