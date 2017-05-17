# Yelp PHP Client - v2

## Create client explicitly

```php
    $client = new Stevenmaguire\Yelp\v2\Client(array(
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
