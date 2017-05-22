# Yelp PHP Client - v2

## Create client explicitly

```php
$client = new \Stevenmaguire\Yelp\v2\Client(array(
    'consumerKey' => 'YOUR COSUMER KEY',
    'consumerSecret' => 'YOUR CONSUMER SECRET',
    'token' => 'YOUR TOKEN',
    'tokenSecret' => 'YOUR TOKEN SECRET',
    'apiHost' => 'api.yelp.com' // Optional, default 'api.yelp.com'
));
```

## Search by keyword and location

```php
$results = $client->search(array('term' => 'Sushi', 'location' => 'Chicago, IL'));
```

## Search by phone number

```php
$results = $client->searchByPhone(array('phone' => '867-5309'));
```

## Locate details for a specific business by Yelp business id

```php
$business = $client->getBusiness('union-chicago-3');
```

You may include [action links](http://engineeringblog.yelp.com/2015/07/yelp-api-now-returns-action-links.html) in your results by passing additional parameters with your request.

```php
$resultsWithActionLinks = $client->getBusiness('union-chicago-3', [
    'actionLinks' => true
]);
```

## Configure defaults

```php
$client->setDefaultLocation('Chicago, IL')  // default location for all searches if location not provided
    ->setDefaultTerm('Sushi')               // default keyword for all searches if term not provided
    ->setSearchLimit(20);                   // number of records to return
```

## Exceptions

If the API request results in an Http error, the client will throw a `\Stevenmaguire\Yelp\Exception\HttpException` that includes the response body, as a string, from the Yelp API.

```php
try {
    $business = $client->getBusiness('union-chicago-3');
} catch (\Stevenmaguire\Yelp\Exception\HttpException $e) {
    $responseBody = $e->getResponseBody(); // string from Http request
    $responseBodyObject = json_decode($responseBody);
}
```
