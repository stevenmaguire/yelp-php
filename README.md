# Yelp PHP Client

[![Latest Version](https://img.shields.io/github/release/stevenmaguire/yelp-php.svg?style=flat-square)](https://github.com/stevenmaguire/yelp-php/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/stevenmaguire/yelp-php/master.svg?style=flat-square&1)](https://travis-ci.org/stevenmaguire/yelp-php)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/stevenmaguire/yelp-php.svg?style=flat-square)](https://scrutinizer-ci.com/g/stevenmaguire/yelp-php/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/stevenmaguire/yelp-php.svg?style=flat-square)](https://scrutinizer-ci.com/g/stevenmaguire/yelp-php)
[![Total Downloads](https://img.shields.io/packagist/dt/stevenmaguire/yelp-php.svg?style=flat-square)](https://packagist.org/packages/stevenmaguire/yelp-php)

A PHP client for authenticating with Yelp using OAuth and consuming the API.

## Install

Via Composer

``` bash
$ composer require stevenmaguire/yelp-php
```

## Usage

This package currently supports `v2` and `v3` (Fusion) of the Yelp API. Each version of the Yelp API maps to a different client, as the APIs are very different. Each client has separate documentation; links provided below.

### Create client

Each version of the Yelp API maps to a different client, as the APIs are very different. A client factory is available to create appropriate clients.

#### v2 Client Example

```php
$options = array(
    'consumerKey' => 'YOUR COSUMER KEY',
    'consumerSecret' => 'YOUR CONSUMER SECRET',
    'token' => 'YOUR TOKEN',
    'tokenSecret' => 'YOUR TOKEN SECRET',
    'apiHost' => 'api.yelp.com' // Optional, default 'api.yelp.com'
);

$client = \Stevenmaguire\Yelp\ClientFactory::makeWith(
    $options,
    \Stevenmaguire\Yelp\Version::TWO
);
```

#### v3 Client Example

```php
$options = array(
    'accessToken' => 'YOUR ACCESS TOKEN', // Required, unless apiKey is provided
    'apiHost' => 'api.yelp.com', // Optional, default 'api.yelp.com',
    'apiKey' => 'YOUR ACCESS TOKEN', // Required, unless accessToken is provided
);

$client = \Stevenmaguire\Yelp\ClientFactory::makeWith(
    $options,
    \Stevenmaguire\Yelp\Version::THREE
);
```

> Prior to December 7, 2017 `accessToken` was required to authenticate requests. Since then, `apiKey` is the preferred authentication method. This library supports both `accessToken` and `apiKey`, prioritizing `apiKey` over `accessToken` if both are provided.
>
> https://www.yelp.com/developers/documentation/v3/authentication#where-is-my-client-secret-going

Version | Constant | Documentation
--------|----------|--------------
v2 | `Stevenmaguire\Yelp\Version::TWO` | [API-GUIDE-v2.md](API-GUIDE-v2.md)
v3 | `Stevenmaguire\Yelp\Version::THREE` | [API-GUIDE-v3.md](API-GUIDE-v3.md)

##### Get Rate Limit data from most recent request

For the v3 client, [rate limiting data](https://www.yelp.com/developers/documentation/v3/rate_limiting) is avaiable after a recent request.

```php
// $latestRateLimit will be null if an http request hasn't been successfully completed.
$latestRateLimit = $client->getRateLimit();

// The maximum number of calls you can make per day
$latestDailyLimit = $latestRateLimit->dailyLimit;

// The number of calls remaining within the current day
$latestRemaining = $latestRateLimit->remaining;

// The time at which the current rate limiting window will expire as an ISO 8601 timestamp
$latestResetTime = $latestRateLimit->resetTime;

// The time at which the current rate limiting data was observed as an ISO 8601 timestamp
$latestCreatedAt = $latestRateLimit->createdAt;
```

### Exceptions

If the API request results in an Http error, the client will throw a `Stevenmaguire\Yelp\Exception\HttpException` that includes the response body, as a string, from the Yelp API.

```php
$responseBody = $e->getResponseBody(); // string from Http request
$responseBodyObject = json_decode($responseBody);
```

### Advanced usage

Both the [v3 client](API-GUIDE-v3.md) and the [v2 client](API-GUIDE-v2.md) expose some public methods that allow overiding default behavior by providing alternative HTTP clients and requests.

```php
$client = new \Stevenmaguire\Yelp\v3\Client(array(
    'accessToken' => $accessToken,
));

// Create a new guzzle http client
$specialHttpClient = new \GuzzleHttp\Client([
    // ... some special configuration
]);

// Update the yelp client with the new guzzle http client
// then get business data
$business = $client->setHttpClient($specialHttpClient)
    ->getBusiness('the-motel-bar-chicago');

// Create request for other yelp API resource not supported by yelp-php
$request = $client->getRequest('GET', '/v3/some-future-endpoint');

// Send that request
$response = $client->getResponse($request);

// See the contents
echo $response->getBody();
```

## Upgrading with Yelp API v2 support from `yelp-php 1.x` to  `yelp-php 2.x`

```php
// same options for all
$options = array(
    'consumerKey' => 'YOUR COSUMER KEY',
    'consumerSecret' => 'YOUR CONSUMER SECRET',
    'token' => 'YOUR TOKEN',
    'tokenSecret' => 'YOUR TOKEN SECRET',
    'apiHost' => 'api.yelp.com' // Optional, default 'api.yelp.com'
);


// yelp-php 1.x
$client = new Stevenmaguire\Yelp\Client($options);

// yelp-php 2.x - option 1
$client = \Stevenmaguire\Yelp\ClientFactory::makeWith(
    $options,
    \Stevenmaguire\Yelp\Version::TWO
);

// yelp-php 2.x - option 2
$client = new \Stevenmaguire\Yelp\v2\Client($options);
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
