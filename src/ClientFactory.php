<?php namespace Stevenmaguire\Yelp;

class ClientFactory
{
    public static function makeWith(array $options = array(), $version = null)
    {
        if (is_null($version)) {
            $version = Version::TWO;
        }

        switch ($version) {
            case Version::TWO:
                return new v2\Client($options);
            case Version::THREE:
                return new v3\Client($options);
            default:
                throw new Exception\InvalidVersionException;
        }
    }
}
