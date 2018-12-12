<?php

namespace Stevenmaguire\Yelp\Test\v3;

use Eloquent\Phony\Phpunit\Phony;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Response;
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
            'apiHost' =>           'api.yelp.com',
            'apiKey' =>            'mock_api_key',
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
            'apiHost' => uniqid(),
            'apiKey' => uniqid()
        ];

        $client = new Yelp($config);
        $this->assertEquals($config['accessToken'], $client->accessToken);
        $this->assertEquals($config['apiHost'], $client->apiHost);
        $this->assertEquals($config['apiKey'], $client->apiKey);
        $this->assertNull($client->{uniqid()});
    }

    public function testClientCanBeConfiguredWithHttpClient()
    {
        $httpClient = Phony::mock(HttpClient::class)->get();

        $client = new Yelp([
            'accessToken' =>       'mock_access_token',
            'apiHost' =>           'api.yelp.com',
            'apiKey' =>            'mock_api_key',
            'httpClient' =>         $httpClient
        ]);

        $this->assertEquals($httpClient, $client->getHttpClient());
    }

    public function testDefaultClientIncludesAccessToken()
    {
        $client = new Yelp([
            'accessToken' =>       'mock_access_token',
            'apiHost' =>           'api.yelp.com'
        ]);

        $this->assertContains(
            'mock_access_token',
            $client->getHttpClient()->getConfig()['headers']['Authorization']
        );
    }

    public function testDefaultClientIncludesApiKey()
    {
        $client = new Yelp([
            'apiHost' =>           'api.yelp.com',
            'apiKey' =>            'mock_api_key',
        ]);

        $this->assertContains(
            'mock_api_key',
            $client->getHttpClient()->getConfig()['headers']['Authorization']
        );
    }

    public function testApiKeyIsPreferredOverAccessToken()
    {
        $client = new Yelp([
            'accessToken' =>       'mock_access_token',
            'apiHost' =>           'api.yelp.com',
            'apiKey' =>            'mock_api_key',
        ]);

        $this->assertContains(
            'mock_api_key',
            $client->getHttpClient()->getConfig()['headers']['Authorization']
        );
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
        $dailyLimit = rand();
        $remaining = rand();
        $resetTime = uniqid();

        $parameters = [
            'locale' => 'bar'
        ];

        $stream = Phony::mock(StreamInterface::class);
        $stream->__toString->returns($payload);

        $response = Phony::mock(ResponseInterface::class);

        $response->getBody->returns($stream->get());
        $response->getHeaderLine->with('RateLimit-DailyLimit')->returns($dailyLimit);
        $response->getHeaderLine->with('RateLimit-Remaining')->returns($remaining);
        $response->getHeaderLine->with('RateLimit-ResetTime')->returns($resetTime);

        $httpClient = Phony::mock(HttpClient::class);
        $httpClient->send->returns($response->get());

        $results = $this->client->setHttpClient($httpClient->get())
            ->getBusiness($businessId, $parameters);

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
            $response->getHeaderLine->calledWith('RateLimit-DailyLimit'),
            $response->getHeaderLine->calledWith('RateLimit-Remaining'),
            $response->getHeaderLine->calledWith('RateLimit-ResetTime'),
            $response->getBody->called(),
            $stream->__toString->called()
        );

        $this->assertEquals($dailyLimit, $this->client->getRateLimit()->dailyLimit);
        $this->assertEquals($remaining, $this->client->getRateLimit()->remaining);
        $this->assertEquals($resetTime, $this->client->getRateLimit()->resetTime);
    }

    public function testGetBusinessReviews()
    {
        $businessId = 'foo';
        $path = '/v3/businesses/'.$businessId.'/reviews';
        $payload = $this->getResponseJson('business_reviews');
        $parameters = [
            'locale' => 'bar'
        ];

        $stream = Phony::mock(StreamInterface::class);
        $stream->__toString->returns($payload);

        $response = Phony::mock(ResponseInterface::class);
        $response->getBody->returns($stream->get());

        $httpClient = Phony::mock(HttpClient::class);
        $httpClient->send->returns($response->get());

        $results = $this->client->setHttpClient($httpClient->get())
            ->getBusinessReviews($businessId, $parameters);

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

    public function testGetBusinessesSearchResults()
    {
        $path = '/v3/businesses/search';
        $payload = $this->getResponseJson('business_search');

        $parameters = [
            'term' => 'foo',
            'location' => 'bar',
            'latitude' => 1.0000,
            'longitude' => 1.0000,
            'radius' => 10,
            'categories' => ['bars', 'french'],
            'locale' => 'bar',
            'limit' => 10,
            'offset' => 2,
            'sort_by' => 'best_match',
            'price' => '1,2,3',
            'open_now' => true,
            'open_at' => 1234566,
            'attributes' => ['hot_and_new','deals']
        ];

        $stream = Phony::mock(StreamInterface::class);
        $stream->__toString->returns($payload);

        $response = Phony::mock(ResponseInterface::class);
        $response->getBody->returns($stream->get());

        $httpClient = Phony::mock(HttpClient::class);
        $httpClient->send->returns($response->get());

        $results = $this->client->setHttpClient($httpClient->get())
            ->getBusinessesSearchResults($parameters);

        $this->assertEquals(json_decode($payload), $results);

        Phony::inOrder(
            $httpClient->send->calledWith(
                $this->callback(function ($request) use ($path, $parameters) {
                    $parameters['open_now'] = 'true';
                    $parameters['categories'] = implode(',', $parameters['categories']);
                    $parameters['attributes'] = implode(',', $parameters['attributes']);
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

        $parameters = [
            'latitude' => 1.0000,
            'longitude' => 1.0000,
            'location' => 'bar'
        ];

        $stream = Phony::mock(StreamInterface::class);
        $stream->__toString->returns($payload);

        $response = Phony::mock(ResponseInterface::class);
        $response->getBody->returns($stream->get());

        $httpClient = Phony::mock(HttpClient::class);
        $httpClient->send->returns($response->get());

        $results = $this->client->setHttpClient($httpClient->get())
            ->getTransactionsSearchResultsByType($type, $parameters);

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
