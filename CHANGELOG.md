# Changelog

## Unreleased

* The signatures of the following methods have changed
  * `GuzzleApiClient::with(string $clientId, string $clientSecret, GuzzleClientInterface $client = null)`
    + Removed the `$options` parameter. If you want to modify the behaviour of the underlying GuzzleHTTP client,
      configure it directly.  
  * `HttpApiClient::with(string $clientId, string $clientSecret, ClientInterface $client, RequestFactoryInterface $requestFactory)
    + Removed the `$options` parameter. If you want to modify the behaviour of the underlying HTTP client,
      configure it directly.

## 1.0 - 2019-01-24

Initial release
