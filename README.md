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

### Create client

```php
    $client = new Stevenmaguire\Yelp\Client(array(
        'consumerKey' => 'YOUR COSUMER KEY',
        'consumerSecret' => 'YOUR CONSUMER SECRET',
        'token' => 'YOUR TOKEN',
        'tokenSecret' => 'YOUR TOKEN SECRET',
        'apiHost' => 'api.yelp.com' // Optional, default 'api.yelp.com'
    ));
```

### Search by keyword and location

```php
$results = $client->search(array('term' => 'Sushi', 'location' => 'Chicago, IL'));
```

### Search by phone number

```php
$results = $client->searchByPhone(array('phone' => '867-5309'));
```

### Locate details for a specific business by Yelp business id

```php
$results = $client->getBusiness('union-chicago-3');
```

You may include [action links](http://engineeringblog.yelp.com/2015/07/yelp-api-now-returns-action-links.html) in your results by passing additional parameters with your request.

```php
$resultsWithActionLinks = $client->getBusiness('union-chicago-3', [
    'actionLinks' => true
]);
```

### Configure defaults

```php
$client->setDefaultLocation('Chicago, IL')  // default location for all searches if location not provided
    ->setDefaultTerm('Sushi')               // default keyword for all searches if term not provided
    ->setSearchLimit(20);                   // number of records to return
```

### Exceptions

If the API request results in an Http error, the client will throw a `Stevenmaguire\Yelp\Exception` that includes the response body, as a string, from the Yelp API.

```php
$responseBody = $e->getResponseBody(); // string from Http request
$responseBodyObject = json_decode($responseBody);
```

## Testing

``` bash
$ phpunit
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Steven Maguire](https://github.com/stevenmaguire)
- [All Contributors](https://github.com/stevenmaguire/yelp-php/contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
