<?php

namespace Stevenmaguire\Yelp\v3;

class RateLimit
{
    /**
     * Created at, ISO 8601 format
     *
     * @var string
     */
    public $createdAt;

    /**
     * Daily limit
     *
     * @var integer
     */
    public $dailyLimit;

    /**
     * Remaining
     *
     * @var integer
     */
    public $remaining;

    /**
     * Reset time
     *
     * @var string
     */
    public $resetTime;

    /**
     * Creates a new rate limit instance, setting the createdAt attribute.
     */
    public function __construct()
    {
        $this->createdAt = (string) date('c');
    }
}
