# Personio SDK for PHP

Interact with [Personio](https://www.personio.de) from your PHP application.

[![Current version](https://img.shields.io/packagist/v/gamez/personio.svg)](https://packagist.org/packages/gamez/personio)
![Supported PHP version](https://img.shields.io/packagist/php-v/gamez/personio.svg)
[![Build Status](https://travis-ci.com/jeromegamez/personio-php.svg?branch=2.x)](https://travis-ci.com/jeromegamez/personio-php)

---

* [Requirements](#requirements)
* [Installation](#installation)
* [Usage](#usage)
  * [Basic API client](#basic-api-client)
  * [Simple API](#simple-api)
  * [Catching errors](#catching-errors)
  * [Caching HTTP requests](#caching-http-requests)
  * [Creating your own API client](#creating-your-own-api-client)
* [License](#license)

---

## Requirements

- A Client ID and Client Secret (You can generate them at https://xxx.personio.de/configuration/api/credentials)

---

## Installation

In order to use this library, you need a [PSR-18 HTTP Client](https://packagist.org/providers/psr/http-client-implementation), and a
[PSR-17 HTTP Message Factory](https://packagist.org/providers/psr/http-factory-implementation).

### Example using `kriswallsmith/buzz` and `nyholm/psr7`

```bash
composer require kriswallsmith/buzz nyholm/psr7 gamez/personio
```

```php
$requestFactory = new \Nyholm\Psr7\Factory\Psr17Factory();
$httpClient = new \Buzz\Client\FileGetContents($requestFactory);
```

### Example using `guzzlehttp/guzzle` and `laminas/laminas-diactoros`

```bash
composer require guzzlehttp/guzzle laminas/laminas-diactoros gamez/personio
```

```php
$requestFactory = new \Laminas\Diactoros\RequestFactory();
$httpClient = new \GuzzleHttp\Client();
```

---

## Usage

### Basic API client

Once you have created an HTTP Client and Request Factory as described in the installation section,
you can create an API client with them:

```php
use Gamez\Personio\Api\HttpApiClient;

$clientId = 'xxx';
$clientSecret = 'xxx';

/**
 * @var \Psr\Http\Message\RequestFactoryInterface $requestFactory
 * @var \Psr\Http\Client\ClientInterface $httpClient
 */
$apiClient = HttpApiClient::with($clientId, $clientSecret, $httpClient, $requestFactory);
```

This API client allows you to make authenticated HTTP requests to the API of your Personio account -
see [Personio's REST API documentation](https://developer.personio.de/reference) for the endpoints you can use.

### Simple API

[`Gamez\Personio\SimpleApi`](./src/SimpleApi.php) is the easiest and fastest way to access the data in your 
Personio account. Its methods are named after the [available REST API endpoints](https://developer.personio.de/v1.0/reference) 
and return arrays of data. You can inspect the available methods by looking at the
[source code of the `Gamez\Personio\SimpleApi` class](./src/SimpleApi.php) or by using the 
autocompletion features of your IDE.

The Simple API doesn't get in your way when accessing the Personio API, but it doesn't provide additional 
features either. It will, for example, not tell you if you used a wrong query parameter or invalid
field value, so you will have to rely on the returned API responses.

For information on which query parameters and field values are allowed, see 
[Personio Developer Hub](https://developer.personio.de/v1.0/reference).

### Catching errors

All exceptions thrown by this library implement the `\Gamez\Personio\Exception\PersonioException` interface.
Exceptions thrown while using an API Client will throw a `\Gamez\Personio\Exception\ApiClientError`.

```php
<?php 

use Gamez\Personio\Exception\ApiClientError;
use Gamez\Personio\Exception\PersonioException;

try {
    /** @var \Gamez\Personio\Api\ApiClient $apiClient */
    $result = $apiClient->get('nice-try');
} catch (ApiClientError $e) {
    $message = "Something went wrong while accessing {$e->getRequest()->getUri()}";

    if ($response = $e->getResponse()) {
        $message .= " ({$response->getStatusCode()})";
    }

    $message .= ' : '.$e->getMessage();

    exit($message);
} catch (PersonioException $e) {
    exit('Something not API related went really wrong: '.$e->getMessage());
}
```

### Caching HTTP requests

To cache HTTP requests to the API, you can add a caching middleware/plugin to the HTTP client
before injecting it into the API client instance. See the documentation of the respective
component for instructions on how to do that.

* Guzzle: [kevinrob/guzzle-cache-middleware](https://github.com/Kevinrob/guzzle-cache-middleware)
* HTTPlug: [Cache Plugin](http://docs.php-http.org/en/latest/plugins/cache.html)

### Creating your own API client

If you want to create your own API client, implement the `\Gamez\Personio\Api\ApiClient` interface
and use your implementation.

## License

`gamez/personio` is licensed under the [MIT License](LICENSE).

Your use of Personio is governed by the [Terms of Service for Personio](https://www.personio.com/gtc/).
