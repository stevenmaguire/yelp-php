# Yelp PHP Client - v3 (Fusion)

## Create client explicitly

Yelp API version 3 (Fusion) [requires an OAuth2 access token to authenticate each request](https://www.yelp.com/developers/documentation/v3/authentication). The [oauth2-yelp](https://github.com/stevenmaguire/oauth2-yelp) is available to help obtain an access token.

```php
// Get access token via oauth2-yelp library
$provider = new \Stevenmaguire\OAuth2\Client\Provider\Yelp([
    'clientId'          => '{yelp-client-id}',
    'clientSecret'      => '{yelp-client-secret}'
]);
$accessToken = (string) $provider->getAccessToken('client_credentials');

// Provide the access token to the yelp-php client
$client = new \Stevenmaguire\Yelp\v3\Client(array(
    'accessToken' => $accessToken,
    'apiHost' => 'api.yelp.com' // Optional, default 'api.yelp.com'
));
```

> Prior to December 7, 2017 `accessToken` was required to authenticate requests. Since then, `apiKey` is the preferred authentication method. This library supports both `accessToken` and `apiKey`, prioritizing `apiKey` over `accessToken` if both are provided.
>
> https://www.yelp.com/developers/documentation/v3/authentication#where-is-my-client-secret-going


## Search for businesses

See also [https://www.yelp.com/developers/documentation/v3/business_search](https://www.yelp.com/developers/documentation/v3/business_search)

```php
$parameters = [
    'term' => 'bars',
    'location' => 'Chicago, IL',
    'latitude' => 41.8781,
    'longitude' => 87.6298,
    'radius' => 10,
    'categories' => ['bars', 'french'],
    'locale' => 'en_US',
    'limit' => 10,
    'offset' => 2,
    'sort_by' => 'best_match',
    'price' => '1,2,3',
    'open_now' => true,
    'open_at' => 1234566,
    'attributes' => ['hot_and_new','deals']
];

$results = $client->getBusinessesSearchResults($parameters);
```

## Search for businesses by phone number

See also [https://www.yelp.com/developers/documentation/v3/business_search_phone](https://www.yelp.com/developers/documentation/v3/business_search_phone)

```php
$results = $client->getBusinessesSearchResultsByPhone('312-867-5309');
```

## Retrieve details for a specific business by Yelp business id

See also [https://www.yelp.com/developers/documentation/v3/business](https://www.yelp.com/developers/documentation/v3/business)

```php
$parameters = [
    'locale' => 'en_US',
];

$business = $client->getBusiness('the-motel-bar-chicago', $parameters);
```

## Retrieve reviews for a specific business by Yelp business id

See also [https://www.yelp.com/developers/documentation/v3/business_reviews](https://www.yelp.com/developers/documentation/v3/business_reviews)

```php
$parameters = [
    'locale' => 'en_US',
];

$reviews = $client->getBusinessReviews('the-motel-bar-chicago', $parameters);
```

## Retrieve autocomplete suggestions

See also [https://www.yelp.com/developers/documentation/v3/autocomplete](https://www.yelp.com/developers/documentation/v3/autocomplete)

```php
$parameters = [
    'text' => 'Mot',
    'latitude' => 41.8781,
    'longitude' => 87.6298,
    'locale' => 'en_US'
];

$results = $client->getAutocompleteResults($parameters);
```

## Search for transactions by type

See also [https://www.yelp.com/developers/documentation/v3/transactions_search](https://www.yelp.com/developers/documentation/v3/transactions_search)

```php
$parameters = [
    'latitude' => 41.8781,
    'longitude' => 87.6298,
    'location' => 'Chicago, IL'
];

$results = $client->getTransactionsSearchResultsByType('delivery', $parameters);
```

## Exceptions

If the API request results in an Http error, the client will throw a `Stevenmaguire\Yelp\Exception\HttpException` that includes the response body, as a string, from the Yelp API.

```php
try {
    $business = $client->getBusiness('the-motel-bar-chicago');
} catch (\Stevenmaguire\Yelp\Exception\HttpException $e) {
    $responseBody = $e->getResponseBody(); // string from Http request
    $responseBodyObject = json_decode($responseBody);
}
```
