<?php

declare(strict_types=1);

namespace Gamez\Personio\Api;

use Gamez\Personio\Exception\ApiClientError;
use Gamez\Personio\Exception\InvalidArgument;
use Psr\Http\Message\ResponseInterface;

interface ApiClient
{
    public const USER_AGENT = 'gamez/personio (https://github.com/jeromegamez/personio-php)';

    /**
     * Perform a HEAD request to an endpoint with optional query parameters.
     *
     * @param array<string, mixed>|null $params
     *
     * @throws ApiClientError
     * @throws InvalidArgument
     */
    public function head(string $endpoint, array $params = null): ResponseInterface;

    /**
     * Perform a GET request to an endpoint with optional query parameters.
     *
     * @param array<string, mixed>|null $params
     *
     * @throws ApiClientError
     * @throws InvalidArgument
     */
    public function get(string $endpoint, array $params = null): ResponseInterface;

    /**
     * Perform a POST request to an endpoint with optional data.
     *
     * @param array<string, mixed>|null $data
     *
     * @throws ApiClientError
     * @throws InvalidArgument
     */
    public function post(string $endpoint, array $data = null): ResponseInterface;

    /**
     * Perform a PATCH request to an endpoint with optional data.
     *
     * @param array<string, mixed>|null $data
     *
     * @throws ApiClientError
     * @throws InvalidArgument
     */
    public function patch(string $endpoint, array $data = null): ResponseInterface;

    /**
     * Perform a DELETE request to an endpoint with optional query parameters.
     *
     * @param array<string, mixed>|null $params
     *
     * @throws ApiClientError
     * @throws InvalidArgument
     */
    public function delete(string $endpoint, array $params = null): ResponseInterface;
}
