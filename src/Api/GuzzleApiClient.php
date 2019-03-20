<?php

declare(strict_types=1);

namespace Gamez\Personio\Api;

use Gamez\Personio\Exception\ApiClientError;
use Gamez\Personio\Support\JSON;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use function GuzzleHttp\default_user_agent;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class GuzzleApiClient implements ApiClient
{
    private const BASE_URL = 'https://api.personio.de/v1/';

    /**
     * @var GuzzleClientInterface
     */
    private $client;

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

    public static function with(string $clientId, string $clientSecret, GuzzleClientInterface $client = null): self
    {
        $that = new self();
        $that->client = $client ?: new GuzzleClient();
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

    public function post(string $endpoint, array $data = null): ResponseInterface
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
            'User-Agent' => implode(' ', [self::USER_AGENT, default_user_agent()]),
        ];

        $body = '';
        if (!empty($data)) {
            $body = JSON::encode($data);
            $headers['Content-Type'] = 'application/json';
        }

        $request = $this->createRequest($method, $url, $headers, $body);
        $request = $this->authenticateRequest($request);

        $response = $this->sendRequest($request);

        if ($response->getStatusCode() >= 400) {
            throw ApiClientError::fromRequestAndResponse($request, $response);
        }

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

        $request = new Request($method, $url);
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

        if (!($token = $data['data']['token'] ?? '')) {
            throw ApiClientError::fromRequestAndReason($request, 'Unable to get token from authorization response');
        }

        return $token;
    }

    private function sendRequest(RequestInterface $request): ResponseInterface
    {
        try {
            $response = $this->client->send($request);
        } catch (ConnectException $e) {
            throw ApiClientError::fromRequestAndReason($e->getRequest(), "Unable to connect to API: {$e->getMessage()}", $e);
        } catch (RequestException $e) {
            if ($response = $e->getResponse()) {
                throw ApiClientError::fromRequestAndResponse($e->getRequest(), $response);
            }
            throw ApiClientError::fromRequestAndReason($e->getRequest(), 'The API returned an error');
        } catch (GuzzleException $e) {
            throw ApiClientError::fromRequestAndReason($request, 'The API returned an error');
        }

        $data = JSON::decode((string) $response->getBody(), true);
        $success = $data['success'] ?? false;

        if (!$success) {
            throw ApiClientError::fromRequestAndResponse($request, $response);
        }

        return $response;
    }
}
