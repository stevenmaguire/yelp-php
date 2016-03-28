<?php namespace Stevenmaguire\Yelp\Test;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Mockery as m;
use Psr\Http\Message\ResponseInterface;
use Stevenmaguire\Yelp\Exception;
use Stevenmaguire\Yelp\Client as Yelp;

class YelpTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->client = new Yelp([
            'consumerKey' =>       getenv('YELP_CONSUMER_KEY'),
            'consumerSecret' =>    getenv('YELP_CONSUMER_SECRET'),
            'token' =>              getenv('YELP_ACCESS_TOKEN'),
            'tokenSecret' =>       getenv('YELP_ACCESS_TOKEN_SECRET'),
            'apiHost' =>           'api.yelp.com'
        ]);
    }

    protected function getResponseJson($type)
    {
        return file_get_contents(__DIR__.'/'.$type.'_response.json');
    }

    protected function getHttpClient($path, $status = 200, $payload = null)
    {
        $response = m::mock(ResponseInterface::class);
        $response->shouldReceive('getBody')->andReturn($payload);

        if ($status < 300) {
            $client = m::mock(HttpClient::class);
            $client->shouldReceive('get')->with(m::on(function ($url) use ($path) {
                return strpos($url, $path) > 0;
            }), ['auth' => 'oauth'])->andReturn($response);
        } else {
            $mock = new MockHandler([
                new Response($status, [], $payload),
            ]);

            $handler = HandlerStack::create($mock);
            $client = new HttpClient(['handler' => $handler]);
        }

        return $client;
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

    /**
     * @expectedException Stevenmaguire\Yelp\Exception
     */
    public function test_It_Will_Fail_With_Invalid_OAuth_Credentials()
    {
        $business_id = 'the-motel-bar-chicago';
        $path = '/v2/business/'.urlencode($business_id);
        $response = $this->getResponseJson('error');
        $httpClient = $this->getHttpClient($path, 401, $response);

        $business = $this->client->setHttpClient($httpClient)->getBusiness($business_id);
    }

    public function test_Exceptions_From_Http_Contain_Response_Body()
    {
        $business_id = 'the-motel-bar-chicago';
        $path = '/v2/business/'.urlencode($business_id);
        $response = $this->getResponseJson('error');
        $httpClient = $this->getHttpClient($path, 401, $response);

        try {
            $business = $this->client->setHttpClient($httpClient)->getBusiness($business_id);
        } catch (Exception $e) {
            $this->assertNotNull($e->getResponseBody());
        }
    }

    public function test_It_Can_Find_Business_By_Id()
    {
        $business_id = 'urban-curry-san-francisco';
        $path = '/v2/business/'.urlencode($business_id);
        $response = $this->getResponseJson('business');
        $httpClient = $this->getHttpClient($path, 200, $response);

        $business = $this->client->setHttpClient($httpClient)->getBusiness($business_id);

        $this->assertInstanceOf('stdClass', $business);
        $this->assertEquals($business_id, $business->id);
    }

    public function test_It_Can_Find_Business_By_Id_With_Attributes()
    {
        $attributes = ['actionLinks' => true];
        $business_id = 'urban-curry-san-francisco';
        $path = '/v2/business/'.urlencode($business_id).'?actionLinks=true';
        $response = $this->getResponseJson('business');
        $httpClient = $this->getHttpClient($path, 200, $response);

        $business = $this->client->setHttpClient($httpClient)->getBusiness($business_id, $attributes);

        $this->assertInstanceOf('stdClass', $business);
        $this->assertEquals($business_id, $business->id);
    }

    public function test_It_Can_Search_Bars_In_Chicago()
    {
        $term = 'bars';
        $location = 'Chicago, IL';
        $attributes = ['term' => $term, 'location' => $location];
        $path = '/v2/search/?'.$this->client->buildQueryParams($attributes);
        $response = $this->getResponseJson('search');
        $httpClient = $this->getHttpClient($path, 200, $response);

        $results = $this->client->setHttpClient($httpClient)->search($attributes);

        $this->assertInstanceOf('stdClass', $results);
        $this->assertNotEmpty($results->businesses);
        $this->assertEquals(1, count($results->businesses));
    }

    public function test_It_Can_Search_By_Phone()
    {
        $phone = '(312) 822-2900';
        $attributes = ['phone' => $phone];
        $path = '/v2/phone_search/?'.http_build_query($attributes);
        $response = $this->getResponseJson('search');
        $httpClient = $this->getHttpClient($path, 200, $response);

        $results = $this->client->setHttpClient($httpClient)->searchByPhone($attributes);

        $this->assertInstanceOf('stdClass', $results);
        $this->assertNotEmpty($results->businesses);
        $this->assertEquals(1, count($results->businesses));
    }

    public function test_It_Can_Set_Defaults()
    {
        $default_term = 'stores';
        $default_location = 'Chicago, IL';
        $default_limit = 10;
        $attributes = ['term' => $default_term, 'location' => $default_location, 'limit' => $default_limit];
        $path = '/v2/search/?'.$this->client->buildQueryParams($attributes);
        $response = $this->getResponseJson('search');
        $httpClient = $this->getHttpClient($path, 200, $response);

        $results = $this->client->setDefaultLocation($default_location)
            ->setDefaultTerm($default_term)
            ->setSearchLimit($default_limit)
            ->setHttpClient($httpClient)
            ->search();

        $this->assertInstanceOf('stdClass', $results);
        $this->assertNotEmpty($results->businesses);
        $this->assertEquals(1, count($results->businesses));
    }

    public function test_It_Can_Find_Business_By_Id_With_Special_Characters()
    {
        $business_id = 'xware42-münchen-3';
        $path = '/v2/business/'.urlencode($business_id);
        $response = $this->getResponseJson('business');
        $httpClient = $this->getHttpClient($path, 200, $response);

        $business = $this->client->setHttpClient($httpClient)->getBusiness($business_id);

        $this->assertInstanceOf('stdClass', $business);
        $this->assertNotNull($business->id);
    }
}
