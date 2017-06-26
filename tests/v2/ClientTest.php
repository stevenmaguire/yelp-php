<?php

namespace Stevenmaguire\Yelp\Test\v2;

use Eloquent\Phony\Phpunit\Phony;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Stevenmaguire\Yelp\Exception\HttpException;
use Stevenmaguire\Yelp\v2\Client as Yelp;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->client = new Yelp([
            'consumerKey' => 'consumer_key',
            'consumerSecret' => 'consumer_secret',
            'token' => 'access_token',
            'tokenSecret' => 'token_secret',
            'apiHost' => 'api.yelp.com'
        ]);
    }

    protected function getResponseJson($type)
    {
        return file_get_contents(__DIR__.'/'.$type.'_response.json');
    }

    public function testConfigurationMapper()
    {
        $config = [
            'consumer_key' =>       uniqid(),
            'consumer_secret' =>    uniqid(),
            'token' =>              uniqid(),
            'token_secret' =>       uniqid(),
            'api_host' =>           uniqid()
        ];

        $client = new Yelp($config);

        $this->assertEquals($config['consumer_key'], $client->consumerKey);
        $this->assertEquals($config['consumer_secret'], $client->consumerSecret);
        $this->assertEquals($config['token'], $client->token);
        $this->assertEquals($config['token_secret'], $client->tokenSecret);
        $this->assertEquals($config['api_host'], $client->apiHost);
        $this->assertNull($client->{uniqid()});
    }

    public function testGetBusiness()
    {
        $businessId = 'foo';
        $path = '/v2/business/'.$businessId;
        $payload = $this->getResponseJson('business');

        $parameters = [
            'actionLinks' => true
        ];

        $stream = Phony::mock(StreamInterface::class);
        $stream->__toString->returns($payload);

        $response = Phony::mock(ResponseInterface::class);
        $response->getBody->returns($stream->get());

        $httpClient = Phony::mock(HttpClient::class);
        $httpClient->get->returns($response->get());

        $results = $this->client->setHttpClient($httpClient->get())
            ->getBusiness($businessId, $parameters);

        $this->assertEquals(json_decode($payload), $results);

        Phony::inOrder(
            $httpClient->get->calledWith(
                $this->callback(function ($url) use ($path, $parameters) {
                    $parameters['actionLinks'] = 'true';
                    $queryString = http_build_query($parameters);
                    return strpos($url, $path) !== false
                        && ($queryString && strpos($url, $queryString) !== false);
                }),
                $this->callback(function ($config) {
                    return $config == ['auth' => 'oauth'];
                })
            ),
            $response->getBody->called(),
            $stream->__toString->called()
        );
    }

    public function testGetBusinessesSearchResults()
    {
        $path = '/v2/search';
        $payload = $this->getResponseJson('search');

        $term = 'bars';
        $location = 'Chicago, IL';
        $parameters = [
            'term' => $term,
            'location' => $location
        ];

        $stream = Phony::mock(StreamInterface::class);
        $stream->__toString->returns($payload);

        $response = Phony::mock(ResponseInterface::class);
        $response->getBody->returns($stream->get());

        $httpClient = Phony::mock(HttpClient::class);
        $httpClient->get->returns($response->get());

        $results = $this->client->setHttpClient($httpClient->get())
            ->search($parameters);

        $this->assertEquals(json_decode($payload), $results);

        Phony::inOrder(
            $httpClient->get->calledWith(
                $this->callback(function ($url) use ($path, $parameters) {
                    $queryString = http_build_query($parameters);
                    return strpos($url, $path) !== false
                        && ($queryString && strpos($url, $queryString) !== false);
                }),
                $this->callback(function ($config) {
                    return $config == ['auth' => 'oauth'];
                })
            ),
            $response->getBody->called(),
            $stream->__toString->called()
        );
    }

    public function testItCanSetSearchDefaults()
    {
        $defaults = [
            'term' => 'stores',
            'location' => 'Chicago, IL',
            'limit' => 10
        ];

        // new here
        $path = '/v2/search';
        $payload = $this->getResponseJson('search');

        $term = 'bars';
        $location = 'Chicago, IL';

        $stream = Phony::mock(StreamInterface::class);
        $stream->__toString->returns($payload);

        $response = Phony::mock(ResponseInterface::class);
        $response->getBody->returns($stream->get());

        $httpClient = Phony::mock(HttpClient::class);
        $httpClient->get->returns($response->get());

        $results = $this->client->setDefaultLocation($defaults['location'])
            ->setDefaultTerm($defaults['term'])
            ->setSearchLimit($defaults['limit'])
            ->setHttpClient($httpClient->get())
            ->search();

        $this->assertEquals(json_decode($payload), $results);

        Phony::inOrder(
            $httpClient->get->calledWith(
                $this->callback(function ($url) use ($path, $defaults) {
                    $queryString = http_build_query($defaults);
                    return strpos($url, $path) !== false
                        && ($queryString && strpos($url, $queryString) !== false);
                }),
                $this->callback(function ($config) {
                    return $config == ['auth' => 'oauth'];
                })
            ),
            $response->getBody->called(),
            $stream->__toString->called()
        );
    }

    public function testGetBusinessesSearchResultsByPhone()
    {
        $phone = 'foo-bar';
        $path = '/v2/phone_search';
        $payload = $this->getResponseJson('search');

        $parameters = [
            'phone' => $phone
        ];

        $stream = Phony::mock(StreamInterface::class);
        $stream->__toString->returns($payload);

        $response = Phony::mock(ResponseInterface::class);
        $response->getBody->returns($stream->get());

        $httpClient = Phony::mock(HttpClient::class);
        $httpClient->get->returns($response->get());

        $results = $this->client->setHttpClient($httpClient->get())
            ->searchByPhone($parameters);

        $this->assertEquals(json_decode($payload), $results);

        Phony::inOrder(
            $httpClient->get->calledWith(
                $this->callback(function ($url) use ($path, $parameters) {
                    $queryString = http_build_query($parameters);
                    return strpos($url, $path) !== false
                        && ($queryString && strpos($url, $queryString) !== false);
                }),
                $this->callback(function ($config) {
                    return $config == ['auth' => 'oauth'];
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
        $path = '/v2/businesses/'.$businessId;
        $payload = $this->getResponseJson('error');

        $stream = Phony::mock(StreamInterface::class);
        $stream->__toString->returns($payload);

        $response = Phony::mock(ResponseInterface::class);
        $response->getBody->returns($stream->get());

        $request = Phony::mock(RequestInterface::class);

        $httpClient = Phony::mock(HttpClient::class);
        $httpClient->get->throws(new BadResponseException(
            'test exception',
            $request->get(),
            $response->get()
        ));

        try {
            $business = $this->client->setHttpClient($httpClient->get())
                ->getBusiness($businessId);
        } catch (\Stevenmaguire\Yelp\Exception\HttpException $e) {
            $this->assertEquals($payload, $e->getResponseBody());
            throw $e;
        }

        Phony::inOrder(
            $httpClient->get->calledWith(
                $this->callback(function ($url) use ($path) {
                    return strpos($url, $path) !== false;
                }),
                $this->callback(function ($config) {
                    return $config == ['auth' => 'oauth'];
                })
            ),
            $response->getBody->called(),
            $stream->__toString->called()
        );
    }
}
