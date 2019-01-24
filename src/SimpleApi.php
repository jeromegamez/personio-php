<?php

declare(strict_types=1);

namespace Gamez\Personio;

use Gamez\Personio\Api\ApiClient;
use Gamez\Personio\Support\JSON;

final class SimpleApi
{
    /**
     * @var ApiClient
     */
    private $client;

    private function __construct()
    {
    }

    public static function withApiClient(ApiClient $apiClient): self
    {
        $that = new self();
        $that->client = $apiClient;

        return $that;
    }

    public function getEmployees(): array
    {
        return $this->get('company/employees');
    }

    public function getEmployee($id): array
    {
        return $this->get("company/employees/{$id}");
    }

    public function getAttendances(array $params = null): array
    {
        return $this->get('company/attendances', $params);
    }

    public function createAttendance(array $data): array
    {
        $response = $this->client->post('company/attendances', $data);

        return JSON::decode((string) $response->getBody(), true);
    }

    public function deleteAttendance($id): void
    {
        $this->client->delete("company/attendances/{$id}");
    }

    public function getTimeOffTypes(array $params = null): array
    {
        return $this->get('company/time-off-types', $params);
    }

    public function getTimeOffs(array $params = null): array
    {
        return $this->get('company/time-offs', $params);
    }

    public function getTimeOff($id): array
    {
        return $this->get("company/time-offs/{$id}");
    }

    public function createTimeOff(array $data): array
    {
        $response = $this->client->post('company/time-offs', $data);

        return JSON::decode((string) $response->getBody(), true);
    }

    public function deleteTimeOff($id): void
    {
        $this->client->delete("company/time-offs/{$id}");
    }

    private function get(string $endpoint, array $params = null): array
    {
        $response = $this->client->get($endpoint, $params);

        return JSON::decode((string) $response->getBody(), true);
    }
}
