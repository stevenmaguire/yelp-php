<?php

namespace Stevenmaguire\Yelp\Test\Tool;

use Psr\Http\Message\ResponseInterface;
use Stevenmaguire\Yelp\Tool\HttpTrait;

class HttpTraitTest extends \PHPUnit_Framework_TestCase
{
    use HttpTrait;

    /**
     * Provides a hook that handles the response before returning to the consumer.
     *
     * @param ResponseInterface $response
     *
     * @return  ResponseInterface
     */
    protected function handleResponse(ResponseInterface $response)
    {
        return $response;
    }

    public function testGetRequestAddsHostWhenNotProvided()
    {
        $this->apiHost = 'foo';

        $request = $this->getRequest('get', '/bar');

        $this->assertContains('foo/bar', (string) $request->getUri());
    }

    public function testGetRequestAddsSchemeWhenNotProvided()
    {
        $this->apiHost = 'foo';

        $request = $this->getRequest('get', '/bar');

        $this->assertContains('https://foo/bar', (string) $request->getUri());
    }

    public function testAppendingParametersToUrl()
    {
        $url = '/foo/bar';
        $parameters = [];
        $result = $this->appendParametersToUrl($url, $parameters);
        $this->assertEquals($url, $result);

        $url = '/foo/bar';
        $parameters = ['foo' => 'bar'];
        $result = $this->appendParametersToUrl($url, $parameters);
        $this->assertEquals($url . '?foo=bar', $result);

        $url = '/foo/bar?';
        $parameters = ['foo' => 'bar'];
        $result = $this->appendParametersToUrl($url, $parameters);
        $this->assertEquals($url . 'foo=bar', $result);

        $url = '/foo/bar?foo=bar';
        $parameters = ['foo2' => 'bar2'];
        $result = $this->appendParametersToUrl($url, $parameters);
        $this->assertEquals($url . '&foo2=bar2', $result);
    }
}
