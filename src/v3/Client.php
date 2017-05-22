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
            $this->setHttpClient(new HttpClient());
        }
    }

    /**
     * Fetches results from the Autocomplete API
     *
     * Parameters
     *   text    string  Required. Text to return autocomplete suggestions for.
     *   latitude    decimal Required if want to get autocomplete suggestions for businesses. Latitude of the location to look for business autocomplete suggestions.
     *   longitude   decimal Required if want to get autocomplete suggestions for businesses. Longitude of the location to look for business autocomplete suggestions.
     *   locale  string  Optional. Specify the locale to return the autocomplete suggestions in. See the list of supported locales.
     *
     * @param    array    $parameters
     *
     * @return   stdClass
     * @throws   Stevenmaguire\Yelp\Exception\HttpException
     */
    public function getAutocompleteResults($parameters = [])
    {
        $request = $this->getRequest(
            'GET',
            $this->appendParametersToUrl('/v3/autocomplete', $parameters)
        );

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
        $parameters = [
            'phone' => $phoneNumber
        ];

        $request = $this->getRequest(
            'GET',
            $this->appendParametersToUrl('/v3/businesses/search/phone', $parameters)
        );

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
