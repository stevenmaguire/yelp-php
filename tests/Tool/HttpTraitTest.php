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
}
