<?php

namespace Stevenmaguire\Yelp\v3;

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
    }

    /**
     * Fetches results from the Autocomplete API
     *
     * @param    array    $parameters
     *
     * @return   stdClass
     * @throws   Stevenmaguire\Yelp\Exception\HttpException
     */
    public function getAutocompleteResults($parameters = [])
    {
        $request = $this->getRequest('GET', '/v3/autocomplete');

        return $this->processRequest($request);
    }

    /**
     * Fetches a specific business by id.
     *
     * @param    string    $businessId
     * @param    array    $parameters
     *
     * @return   stdClass
     * @throws   Stevenmaguire\Yelp\Exception\HttpException
     */
    public function getBusiness($businessId, $parameters = [])
    {
        $request = $this->getRequest('GET', '/v3/businesses/'.$businessId);

        return $this->processRequest($request);
    }

    /**
     * Fetches reviews for a specific business by id.
     *
     * @param    string    $businessId
     * @param    array    $parameters
     *
     * @return   stdClass
     * @throws   Stevenmaguire\Yelp\Exception\HttpException
     */
    public function getBusinessReviews($businessId, $parameters = [])
    {
        $request = $this->getRequest('GET', '/v3/businesses/'.$businessId.'/reviews');

        return $this->processRequest($request);
    }

    /**
     * Fetches results from the Business Search API
     *
     * @param    array    $parameters
     *
     * @return   stdClass
     * @throws   Stevenmaguire\Yelp\Exception\HttpException
     */
    public function getBusinessesSearchResults($parameters = [])
    {
        $request = $this->getRequest('GET', '/v3/businesses/search');

        return $this->processRequest($request);
    }

    /**
     * Fetches results from the Business Search API by Phone
     *
     * @param    string    $phoneNumber
     *
     * @return   stdClass
     * @throws   Stevenmaguire\Yelp\Exception\HttpException
     */
    public function getBusinessesSearchResultsByPhone($phoneNumber)
    {
        $request = $this->getRequest('GET', '/v3/businesses/search/phone');

        return $this->processRequest($request);
    }

    /**
     * Fetches results from the Business Search API by Phone
     *
     * @param    string    $type
     * @param    array     $parameters
     *
     * @return   stdClass
     * @throws   Stevenmaguire\Yelp\Exception\HttpException
     */
    public function getTransactionsSearchResultsByType($type, $parameters = [])
    {
        $request = $this->getRequest('GET', '/v3/transactions/'.$type.'/search');

        return $this->processRequest($request);
    }
}
