<?php

namespace Stevenmaguire\Yelp\Test;

use Stevenmaguire\Yelp\ClientFactory;
use Stevenmaguire\Yelp\Version;
use Stevenmaguire\Yelp\v2\Client as VersionTwoClient;
use Stevenmaguire\Yelp\v3\Client as VersionThreeClient;

class ClientFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testVersionTwoClientCreatedWhenNoVersionProvided()
    {
        $options = [
            'foo' => 'bar'
        ];

        $client = ClientFactory::makeWith($options);

        $this->assertInstanceOf(VersionTwoClient::class, $client);
    }

    public function testVersionTwoClientCreatedWhenVersionTwoProvided()
    {
        $options = [
            'foo' => 'bar'
        ];

        $client = ClientFactory::makeWith($options, Version::TWO);

        $this->assertInstanceOf(VersionTwoClient::class, $client);
    }

    public function testVersionThreeClientCreatedWhenVersionThreeProvided()
    {
        $options = [
            'foo' => 'bar'
        ];

        $client = ClientFactory::makeWith($options, Version::THREE);

        $this->assertInstanceOf(VersionThreeClient::class, $client);
    }

    /**
     * @expectedException Stevenmaguire\Yelp\Exception\InvalidVersionException
     */
    public function testExceptionThrownWhenInvalidVersionProvided()
    {
        $options = [
            'foo' => 'bar'
        ];

        $client = ClientFactory::makeWith($options, 'foo');
    }
}
