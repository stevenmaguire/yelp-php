# Yelp PHP Client

[![Latest Version](https://img.shields.io/github/release/stevenmaguire/yelp-php.svg?style=flat-square)](https://github.com/stevenmaguire/yelp-php/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/stevenmaguire/yelp-php/master.svg?style=flat-square&1)](https://travis-ci.org/stevenmaguire/yelp-php)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/stevenmaguire/yelp-php.svg?style=flat-square)](https://scrutinizer-ci.com/g/stevenmaguire/yelp-php/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/stevenmaguire/yelp-php.svg?style=flat-square)](https://scrutinizer-ci.com/g/stevenmaguire/yelp-php)
[![Total Downloads](https://img.shields.io/packagist/dt/stevenmaguire/yelp-php.svg?style=flat-square)](https://packagist.org/packages/stevenmaguire/yelp-php)

A PHP client for authenticating with Yelp using OAuth 1 and consuming the search API.

## Install

Via Composer

``` bash
$ composer require stevenmaguire/yelp-php
```

## Usage

This package currently supports `v2` and `v3` (Fusion) of the Yelp API. Each version of the Yelp API maps to a different client, as the APIs are very different. Each client has separate documentation; links provided below.

### Create client

Each version of the Yelp API maps to a different client, as the APIs are very different. A client factory is available to create appropriate clients.

```php
    // v2 Client
    $options = array(
        'consumerKey' => 'YOUR COSUMER KEY',
        'consumerSecret' => 'YOUR CONSUMER SECRET',
        'token' => 'YOUR TOKEN',
        'tokenSecret' => 'YOUR TOKEN SECRET',
        'apiHost' => 'api.yelp.com' // Optional, default 'api.yelp.com'
    );

    $client = new Stevenmaguire\Yelp\ClientFactory::makeWith(
        $options,
        Stevenmaguire\Yelp\Version::TWO
    );

    // v3 Client
    $options = array(
        'accessToken' => 'YOUR ACCESS TOKEN',
        'apiHost' => 'api.yelp.com' // Optional, default 'api.yelp.com'
    );

    $client = new Stevenmaguire\Yelp\ClientFactory::makeWith(
        $options,
        Stevenmaguire\Yelp\Version::THREE
    );
```

Version | Constant | Documentation
--------|----------|--------------
v2 | `Stevenmaguire\Yelp\Version::TWO` | [API-GUIDE-v2.md](API-GUIDE-v2.md)
v3 | `Stevenmaguire\Yelp\Version::THREE` | [API-GUIDE-v3.md](API-GUIDE-v3.md)

### Exceptions

If the API request results in an Http error, the client will throw a `Stevenmaguire\Yelp\Exception\HttpException` that includes the response body, as a string, from the Yelp API.

```php
$responseBody = $e->getResponseBody(); // string from Http request
$responseBodyObject = json_decode($responseBody);
```

## Testing

``` bash
$ ./vendor/bin/phpunit
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Steven Maguire](https://github.com/stevenmaguire)
- [All Contributors](https://github.com/stevenmaguire/yelp-php/contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
