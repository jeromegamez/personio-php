<?php

declare(strict_types=1);

namespace Gamez\Personio\Api;

use Gamez\Personio\Exception\ApiClientError;
use Gamez\Personio\Support\JSON;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class HttpApiClient implements ApiClient
{
    private const BASE_URL = 'https://api.personio.de/v1/';

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var RequestFactoryInterface
     */
    private $requestFactory;

    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $clientSecret;

    /**
     * @var string|null
     */
    private $token;

    private function __construct()
    {
    }

    public static function with(string $clientId, string $clientSecret, ClientInterface $client, RequestFactoryInterface $requestFactory): self
    {
        $that = new self();
        $that->client = $client;
        $that->requestFactory = $requestFactory;
        $that->clientId = $clientId;
        $that->clientSecret = $clientSecret;

        return $that;
    }

    public function head(string $endpoint, array $params = null): ResponseInterface
    {
        return $this->request('HEAD', $endpoint, $params);
    }

    public function get(string $endpoint, array $params = null): ResponseInterface
    {
        return $this->request('GET', $endpoint, $params);
    }

    public function post($endpoint, array $data = null): ResponseInterface
    {
        return $this->request('POST', $endpoint, $data);
    }

    public function patch(string $endpoint, array $data = null): ResponseInterface
    {
        return $this->request('PATCH', $endpoint, $data);
    }

    public function delete(string $endpoint, array $params = null): ResponseInterface
    {
        return $this->request('DELETE', $endpoint, $params);
    }

    private function request(string $method, string $endpoint, array $params = null, array $data = null): ResponseInterface
    {
        $url = $this->createUrl($endpoint, $params);

        $headers = [
            'Accept' => 'application/json',
            'User-Agent' => self::USER_AGENT,
        ];

        $body = '';
        if (!empty($data)) {
            $body = JSON::encode($data);
            $headers['Content-Type'] = 'application/json';
        }

        $request = $this->createRequest($method, $url, $headers, $body);
        $request = $this->authenticateRequest($request);

        $response = $this->sendRequest($request);

        if ($header = $response->getHeaderLine('Authorization')) {
            $this->token = preg_replace('/^bearer /i', '', $header);
        }

        return $response;
    }

    private function createUrl(string $endpoint, array $params = null): string
    {
        $url = self::BASE_URL.$endpoint;

        if (!empty($params)) {
            $url .= '?'.http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        }

        return $url;
    }

    private function createRequest(string $method, string $url, array $headers = null, string $body = null): RequestInterface
    {
        $headers = $headers ?: [];
        $body = $body ?: '';

        $request = $this->requestFactory->createRequest($method, $url);
        $request->getBody()->write($body);

        foreach ($headers as $name => $value) {
            $request = $request->withAddedHeader((string) $name, $value);
        }

        return $request;
    }

    private function authenticateRequest(RequestInterface $request): RequestInterface
    {
        $token = $this->token ?: $this->fetchToken();

        return $request->withHeader('Authorization', 'Bearer '.$token);
    }

    private function fetchToken(): string
    {
        $url = $this->createUrl('auth', ['client_id' => $this->clientId, 'client_secret' => $this->clientSecret]);
        $request = $this->createRequest('POST', $url);

        $response = $this->sendRequest($request);

        $data = JSON::decode((string) $response->getBody(), true);

        if (!($data['success'] ?? false)) {
            throw ApiClientError::fromRequestAndReason($request, 'Unable to fetch authorization token.');
        }

        if (!($token = $data['data']['token'] ?? '')) {
            throw ApiClientError::fromRequestAndReason($request, 'Unable to get token from authorization response');
        }

        return $token;
    }

    private function sendRequest(RequestInterface $request): ResponseInterface
    {
        try {
            $response = $this->client->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw ApiClientError::fromRequestAndReason($request, "Unable to send request to send {$request->getMethod()} request to {$request->getUri()}", $e);
        }

        if ($response->getStatusCode() >= 400) {
            throw ApiClientError::fromRequestAndResponse($request, $response);
        }

        $data = JSON::decode((string) $response->getBody(), true);
        $success = $data['success'] ?? false;

        if (!$success) {
            throw ApiClientError::fromRequestAndResponse($request, $response);
        }

        return $response;
    }
}
