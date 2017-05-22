<?php

namespace Stevenmaguire\Yelp\Test\Tool;

use Stevenmaguire\Yelp\Tool\HttpTrait;

class HttpTraitTest extends \PHPUnit_Framework_TestCase
{
    use HttpTrait;

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

    public function testPreparingQueryParams()
    {
        $parameters = [
            'foo' => 'bar',
            'foo2' => 'bar2,bar3,bar4',
            'foo3' => ['bar2', 'bar3', 'bar4'],
        ];
        $options = [
            'to_csv' => 'foo2,foo3'
        ];

        $result = $this->prepareQueryParams($parameters, $options);
        $this->assertEquals(http_build_query([
            'foo' => 'bar',
            'foo2' => 'bar2,bar3,bar4',
            'foo3' => 'bar2,bar3,bar4',
        ]), $result);

        $options = [
            'to_csv' => ['foo2' ,'foo3']
        ];

        $result = $this->prepareQueryParams($parameters, $options);
        $this->assertEquals(http_build_query([
            'foo' => 'bar',
            'foo2' => 'bar2,bar3,bar4',
            'foo3' => 'bar2,bar3,bar4',
        ]), $result);
    }
}
