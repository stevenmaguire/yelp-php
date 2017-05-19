<?php

namespace Stevenmaguire\Yelp\Test\v3;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Response;
use Mockery as m;
use Psr\Http\Message\ResponseInterface;
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

    protected function getHttpClient($path, $status = 200, $payload = null)
    {
        // $response = m::mock(ResponseInterface::class);
        // $response->shouldReceive('getBody')->andReturn($payload);

        // if ($status < 300) {
        //     $client = m::mock(HttpClient::class);
        //     $client->shouldReceive('get')->with(m::on(function ($url) use ($path) {
        //         return strpos($url, $path) > 0;
        //     }), ['auth' => 'oauth'])->andReturn($response);
        // } else {
        //     $mock = new MockHandler([
        //         new Response($status, [], $payload),
        //     ]);

        //     $handler = HandlerStack::create($mock);
        //     $client = new HttpClient(['handler' => $handler]);
        // }

        // return $client;
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

    public function testGetAutocompleteResults()
    {
        // text    string  Required. Text to return autocomplete suggestions for.
        // latitude    decimal Required if want to get autocomplete suggestions for businesses. Latitude of the location to look for business autocomplete suggestions.
        // longitude   decimal Required if want to get autocomplete suggestions for businesses. Longitude of the location to look for business autocomplete suggestions.
        // locale  string  Optional. Specify the locale to return the autocomplete suggestions in. See the list of supported locales.
    }

    public function testGetBusiness()
    {
        // locale  string  Optional. Specify the locale to return the business information in. See the list of supported locales.
    }

    public function testGetBusinessReviews()
    {
        // locale  string  Optional. Specify the locale to return the business information in. See the list of supported locales.
    }

    public function testGetBusinessesSearchResults()
    {
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
    }

    public function testGetBusinessesSearchResultsByPhone()
    {
        // phone    string  Required. Phone number of the business you want to search for. It must start with + and include the country code, like +14159083801.
    }

    public function testGetTransactionsSearchResultsByType()
    {
        // latitude    decimal Required when location isn't provided. Latitude of the location you want to deliver to.
        // longitude   decimal Required when location isn't provided. Longitude of the location you want to deliver to.
        // location    string  Required when latitude and longitude aren't provided. Address of the location you want to deliver to.
    }
}
