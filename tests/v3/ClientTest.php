<?php

namespace Stevenmaguire\Yelp\Test\v3;

use Eloquent\Phony\Phpunit\Phony;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Response;
use Mockery as m;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Stevenmaguire\Yelp\Exception\HttpException;
use Stevenmaguire\Yelp\v3\Client as Yelp;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->client = new Yelp([
            'accessToken' =>       'mock_access_token',
            'apiHost' =>           'api.yelp.com'
        ]);
    }

    protected function getResponseJson($type)
    {
        return file_get_contents(__DIR__.'/'.$type.'_response.json');
    }

    public function testConfigurationMapper()
    {
        $config = [
            'accessToken' => uniqid(),
            'apiHost' => uniqid()
        ];

        $client = new Yelp($config);
        $this->assertEquals($config['accessToken'], $client->accessToken);
        $this->assertEquals($config['apiHost'], $client->apiHost);
        $this->assertNull($client->{uniqid()});
    }

    public function testClientCanBeConfiguredWithHttpClient()
    {
        $httpClient = Phony::mock(HttpClient::class)->get();

        $client = new Yelp([
            'accessToken' =>       'mock_access_token',
            'apiHost' =>           'api.yelp.com',
            'httpClient' =>         $httpClient
        ]);

        $this->assertEquals($httpClient, $client->getHttpClient());
    }

    public function testGetAutocompleteResults()
    {
        $path = '/v3/autocomplete';
        $payload = $this->getResponseJson('autocomplete');

        $parameters = [
            'text' => 'foo',
            'latitude' => 1.0000,
            'longitude' => 1.0000,
            'locale' => 'bar'
        ];

        $stream = Phony::mock(StreamInterface::class);
        $stream->__toString->returns($payload);

        $response = Phony::mock(ResponseInterface::class);
        $response->getBody->returns($stream->get());

        $httpClient = Phony::mock(HttpClient::class);
        $httpClient->send->returns($response->get());

        $results = $this->client->setHttpClient($httpClient->get())
            ->getAutocompleteResults($parameters);

        $this->assertEquals(json_decode($payload), $results);

        Phony::inOrder(
            $httpClient->send->calledWith(
                $this->callback(function ($request) use ($path, $parameters) {
                    $queryString = http_build_query($parameters);
                    return $request->getMethod() === 'GET'
                        && strpos((string) $request->getUri(), $path) !== false
                        && ($queryString && strpos((string) $request->getUri(), $queryString) !== false);
                })
            ),
            $response->getBody->called(),
            $stream->__toString->called()
        );
    }

    public function testGetBusiness()
    {
        $businessId = 'foo';
        $path = '/v3/businesses/'.$businessId;
        $payload = $this->getResponseJson('business');

        // locale  string  Optional. Specify the locale to return the business information in. See the list of supported locales.

        $stream = Phony::mock(StreamInterface::class);
        $stream->__toString->returns($payload);

        $response = Phony::mock(ResponseInterface::class);
        $response->getBody->returns($stream->get());

        $httpClient = Phony::mock(HttpClient::class);
        $httpClient->send->returns($response->get());

        $results = $this->client->setHttpClient($httpClient->get())
            ->getBusiness($businessId);

        $this->assertEquals(json_decode($payload), $results);

        Phony::inOrder(
            $httpClient->send->calledWith(
                $this->callback(function ($request) use ($path) {
                    return $request->getMethod() === 'GET'
                        && strpos((string) $request->getUri(), $path) !== false;
                })
            ),
            $response->getBody->called(),
            $stream->__toString->called()
        );
    }

    public function testGetBusinessReviews()
    {
        $businessId = 'foo';
        $path = '/v3/businesses/'.$businessId.'/reviews';
        $payload = $this->getResponseJson('business_reviews');

        // locale  string  Optional. Specify the locale to return the business information in. See the list of supported locales.

        $stream = Phony::mock(StreamInterface::class);
        $stream->__toString->returns($payload);

        $response = Phony::mock(ResponseInterface::class);
        $response->getBody->returns($stream->get());

        $httpClient = Phony::mock(HttpClient::class);
        $httpClient->send->returns($response->get());

        $results = $this->client->setHttpClient($httpClient->get())
            ->getBusinessReviews($businessId);

        $this->assertEquals(json_decode($payload), $results);

        Phony::inOrder(
            $httpClient->send->calledWith(
                $this->callback(function ($request) use ($path) {
                    return $request->getMethod() === 'GET'
                        && strpos((string) $request->getUri(), $path) !== false;
                })
            ),
            $response->getBody->called(),
            $stream->__toString->called()
        );
    }

    public function testGetBusinessesSearchResults()
    {
        $path = '/v3/businesses/search';
        $payload = $this->getResponseJson('business_search');

        // term    string  Optional. Search term (e.g. "food", "restaurants"). If term isn’t included we search everything. The term keyword also accepts business names such as "Starbucks".
        // location    string  Required if either latitude or longitude is not provided. Specifies the combination of "address, neighborhood, city, state or zip, optional country" to be used when searching for businesses.
        // latitude    decimal Required if location is not provided. Latitude of the location you want to search nearby.
        // longitude   decimal Required if location is not provided. Longitude of the location you want to search nearby.
        // radius  int Optional. Search radius in meters. If the value is too large, a AREA_TOO_LARGE error may be returned. The max value is 40000 meters (25 miles).
        // categories  string  Optional. Categories to filter the search results with. See the list of supported categories. The category filter can be a list of comma delimited categories. For example, "bars,french" will filter by Bars and French. The category identifier should be used (for example "discgolf", not "Disc Golf").
        // locale  string  Optional. Specify the locale to return the business information in. See the list of supported locales.
        // limit   int Optional. Number of business results to return. By default, it will return 20. Maximum is 50.
        // offset  int Optional. Offset the list of returned business results by this amount.
        // sort_by string  Optional. Sort the results by one of the these modes: best_match, rating, review_count or distance. By default it's best_match. The rating sort is not strictly sorted by the rating value, but by an adjusted rating value that takes into account the number of ratings, similar to a bayesian average. This is so a business with 1 rating of 5 stars doesn’t immediately jump to the top.
        // price   string  Optional. Pricing levels to filter the search result with: 1 = $, 2 = $$, 3 = $$$, 4 = $$$$. The price filter can be a list of comma delimited pricing levels. For example, "1, 2, 3" will filter the results to show the ones that are $, $$, or $$$.
        // open_now    boolean Optional. Default to false. When set to true, only return the businesses open now. Notice that open_at and open_now cannot be used together.
        // open_at int Optional. An integer represending the Unix time in the same timezone of the search location. If specified, it will return business open at the given time. Notice that open_at and open_now cannot be used together.
        // attributes  string

        $stream = Phony::mock(StreamInterface::class);
        $stream->__toString->returns($payload);

        $response = Phony::mock(ResponseInterface::class);
        $response->getBody->returns($stream->get());

        $httpClient = Phony::mock(HttpClient::class);
        $httpClient->send->returns($response->get());

        $results = $this->client->setHttpClient($httpClient->get())
            ->getBusinessesSearchResults();

        $this->assertEquals(json_decode($payload), $results);

        Phony::inOrder(
            $httpClient->send->calledWith(
                $this->callback(function ($request) use ($path) {
                    return $request->getMethod() === 'GET'
                        && strpos((string) $request->getUri(), $path) !== false;
                })
            ),
            $response->getBody->called(),
            $stream->__toString->called()
        );
    }

    public function testGetBusinessesSearchResultsByPhone()
    {
        $phone = 'foo-bar';
        $path = '/v3/businesses/search/phone';
        $payload = $this->getResponseJson('business_search_by_phone');

        $stream = Phony::mock(StreamInterface::class);
        $stream->__toString->returns($payload);

        $response = Phony::mock(ResponseInterface::class);
        $response->getBody->returns($stream->get());

        $httpClient = Phony::mock(HttpClient::class);
        $httpClient->send->returns($response->get());

        $results = $this->client->setHttpClient($httpClient->get())
            ->getBusinessesSearchResultsByPhone($phone);

        $this->assertEquals(json_decode($payload), $results);

        Phony::inOrder(
            $httpClient->send->calledWith(
                $this->callback(function ($request) use ($path, $phone) {
                    $queryString = http_build_query(['phone' => $phone]);
                    return $request->getMethod() === 'GET'
                        && strpos((string) $request->getUri(), $path) !== false
                        && strpos((string) $request->getUri(), $queryString) !== false;
                })
            ),
            $response->getBody->called(),
            $stream->__toString->called()
        );
    }

    public function testGetTransactionsSearchResultsByType()
    {
        $type = 'foo';
        $path = '/v3/transactions/'.$type.'/search';
        $payload = $this->getResponseJson('business_search_by_phone');

        // latitude    decimal Required when location isn't provided. Latitude of the location you want to deliver to.
        // longitude   decimal Required when location isn't provided. Longitude of the location you want to deliver to.
        // location    string  Required when latitude and longitude aren't provided. Address of the location you want to deliver to.

        $stream = Phony::mock(StreamInterface::class);
        $stream->__toString->returns($payload);

        $response = Phony::mock(ResponseInterface::class);
        $response->getBody->returns($stream->get());

        $httpClient = Phony::mock(HttpClient::class);
        $httpClient->send->returns($response->get());

        $results = $this->client->setHttpClient($httpClient->get())
            ->getTransactionsSearchResultsByType($type);

        $this->assertEquals(json_decode($payload), $results);

        Phony::inOrder(
            $httpClient->send->calledWith(
                $this->callback(function ($request) use ($path) {
                    return $request->getMethod() === 'GET'
                        && strpos((string) $request->getUri(), $path) !== false;
                })
            ),
            $response->getBody->called(),
            $stream->__toString->called()
        );
    }

    /**
     * @expectedException Stevenmaguire\Yelp\Exception\HttpException
     */
    public function testClientRaisesExceptionWhenHttpRequestFails()
    {
        $businessId = 'foo';
        $path = '/v3/businesses/'.$businessId;
        $payload = $this->getResponseJson('error');

        // locale  string  Optional. Specify the locale to return the business information in. See the list of supported locales.

        $stream = Phony::mock(StreamInterface::class);
        $stream->__toString->returns($payload);

        $response = Phony::mock(ResponseInterface::class);
        $response->getBody->returns($stream->get());

        $request = Phony::mock(RequestInterface::class);

        $httpClient = Phony::mock(HttpClient::class);
        $httpClient->send->throws(new BadResponseException(
            'test exception',
            $request->get(),
            $response->get()
        ));

        $business = $this->client->setHttpClient($httpClient->get())
            ->getBusiness($businessId);

        Phony::inOrder(
            $httpClient->send->calledWith(
                $this->callback(function ($request) use ($path) {
                    return $request->getMethod() === 'GET'
                        && strpos((string) $request->getUri(), $path) !== false;
                })
            ),
            $response->getBody->called(),
            $stream->__toString->called()
        );
    }
}
