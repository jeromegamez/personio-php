# Personio SDK for PHP

Interact with [Personio](https://www.personio.de) from your PHP application.

[![Current version](https://img.shields.io/packagist/v/gamez/personio.svg)](https://packagist.org/packages/gamez/personio)
[![Supported PHP version](https://img.shields.io/packagist/php-v/gamez/personio.svg)]()
[![Build Status](https://travis-ci.org/gamez/personio-php.svg?branch=master)](https://travis-ci.org/gamez/personio-php)

This library comes with out of the box support for [Guzzle](http://docs.guzzlephp.org/en/stable/)
and for [HTTP clients implementing PSR-18](https://packagist.org/providers/psr/http-client-implementation).

* [Requirements](#requirements)
* [Installation](#installation)
* [Setup](#setup)
  * [Creating an API client based on Guzzle](#creating-an-api-client-based-on-guzzle)
  * [Creating an API client based on a PSR-18 HTTP Client](#creating-an-api-client-based-on-a-psr-18-http-client)
  * [Creating your own API client](#creating-your-own-api-client)
  * [Caching HTTP requests](#caching-http-requests)
* [Usage](#usage)
  * [Simple API](#simple-api)
  * [Catching errors](#catching-errors)
* [Roadmap](#roadmap)

## Requirements

- A Client ID and Client Secret from (You can generate them at https://xxx.personio.de/configuration/api/credentials)

---

## Installation

```bash
composer require gamez/personio
```

---

## Setup

### Creating an API client based on Guzzle

```bash
composer require guzzlehttp/guzzle
``` 

```php
<?php
// a file in the same directory in which you perfomed the composer command(s)
require 'vendor/autoload.php';

use Gamez\Personio\Api\GuzzleApiClient;

$clientId = 'xxx';
$clientSecret = 'xxx';

$apiClient = GuzzleApiClient::with($clientId, $clientSecret);
```

### Creating an API client based on a PSR-18 HTTP Client

To be able to use a PSR-18 HTTP client, you need a 
[PSR-17 HTTP Factory](https://packagist.org/providers/psr/http-factory-implementation) as well.

If your application already has a PSR-18 HTTP client and a PSR-17 HTTP factory, skip the following
`composer require` step. Otherwise, I recommend using 

* [`nyholm/psr7`](https://github.com/Nyholm/psr7) as your PSR-17 HTTP factory
* [A HTTPlug client/adapter](http://docs.php-http.org/en/latest/clients.html) as your PSR-18 HTTP client. 

> **Note**: HTTPlug is currently in the process of making all clients and adapters ready for PSR-18. At the
> time of this writing, the only released client/adapter implementing PSR-18 is the guzzle6-adapter.
> For the following example I am using a feature branch of the cURL client so that it doesn't seem
> as if you could have taken the Guzzle API Client anyways.

```bash
composer require "php-http/curl-client:dev-issue-41-psr-18 as 2.0" nyholm/psr7
```

```php
<?php
// a file in the same directory in which you perfomed the composer command(s)
require 'vendor/autoload.php';

use Gamez\Personio\Api\HttpApiClient;
use Http\Client\Curl\Client as CurlClient;
use Nyholm\Psr7\Factory\Psr17Factory;

$accountName = 'xxx';
$apiKey = 'xxx';

$psr17Factory = new Psr17Factory();
$curlClient = new CurlClient($psr17Factory, $psr17Factory);

$apiClient = HttpApiClient::with($accountName, $apiKey, $curlClient, $psr17Factory);
```

### Creating your own API client

If you want to create your own API client, implement the `\Gamez\Personio\Api\ApiClient` interface
and use your implementation.

### Caching HTTP requests

To cache HTTP requests to the API, you can add a caching middleware/plugin to the HTTP client
before injecting it into the API client instance. See the documentation of the respective
component for instructions on how to do that.

* Guzzle: [kevinrob/guzzle-cache-middleware](https://github.com/Kevinrob/guzzle-cache-middleware)
* HTTPlug: [Cache Plugin](http://docs.php-http.org/en/latest/plugins/cache.html)

---

## Usage

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

---

## Roadmap

* Tests
* Interfaces and value objects
* CLI tool
* Better documentation

---
