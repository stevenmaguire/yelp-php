<?php namespace Stevenmaguire\Yelp\Exception;

class HttpException extends \Exception
{
    /**
     * Response body
     *
     * @var string
     */
    protected $responseBody;

    /**
     * Set exception response body from Http request
     *
     * @param string $body
     *
     * @return  Stevenmaguire\Yelp\Exception
     */
    public function setResponseBody($body = null)
    {
        $this->responseBody = $body;

        return $this;
    }

    /**
     * Get exception response body
     *
     * @return string
     */
    public function getResponseBody()
    {
        return $this->responseBody;
    }
}
