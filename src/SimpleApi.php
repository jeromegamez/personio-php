<?php

declare(strict_types=1);

namespace Gamez\Personio;

use Gamez\Personio\Api\ApiClient;
use Gamez\Personio\Support\JSON;

final class SimpleApi
{
    private ApiClient $client;

    private function __construct()
    {
    }

    public static function withApiClient(ApiClient $apiClient): self
    {
        $that = new self();
        $that->client = $apiClient;

        return $that;
    }

    /**
     * @return array{
     *     success: bool,
     *     data: array{
     *          type: string,
     *          attributes: array<string, mixed>
     *     }
     * }
     */
    public function getEmployees(): array
    {
        return $this->get('company/employees');
    }

    /**
     * @param int|numeric-string $id
     *
     * @return array{
     *     success: bool,
     *     data: array{
     *          type: string,
     *          attributes: array<string, mixed>
     *     }
     * }
     */
    public function getEmployee($id): array
    {
        return $this->get("company/employees/$id");
    }

    /**
     * @param array<string, mixed>|null $params
     *
     * @return array{
     *     success: bool,
     *     data: array{
     *          type: string,
     *          attributes: array<string, mixed>
     *     }
     * }
     */
    public function getAttendances(array $params = null): array
    {
        return $this->get('company/attendances', $params);
    }

    /**
     * @param array{
     *     attendances: array<string, mixed>
     * } $data
     *
     * @return array{
     *     success: bool,
     *     data: array{
     *          type: string,
     *          attributes: array<string, mixed>
     *     }
     * }
     */
    public function createAttendance(array $data): array
    {
        $response = $this->client->post('company/attendances', $data);

        return JSON::decode((string) $response->getBody(), true);
    }

    /**
     * @param int|numeric-string $id
     */
    public function deleteAttendance($id): void
    {
        $this->client->delete("company/attendances/$id");
    }

    /**
     * @param array<string, mixed>|null $params
     *
     * @return array{
     *     success: bool,
     *     data: array{
     *          type: string,
     *          attributes: array<string, mixed>
     *     }
     * }
     */
    public function getTimeOffTypes(array $params = null): array
    {
        return $this->get('company/time-off-types', $params);
    }

    /**
     * @param array<string, mixed>|null $params
     *
     * @return array{
     *     success: bool,
     *     data: array{
     *          type: string,
     *          attributes: array<string, mixed>
     *     }
     * }
     */
    public function getTimeOffs(array $params = null): array
    {
        return $this->get('company/time-offs', $params);
    }

    /**
     * @param int|numeric-string $id
     *
     * @return array{
     *     success: bool,
     *     data: array{
     *          type: string,
     *          attributes: array<string, mixed>
     *     }
     * }
     */
    public function getTimeOff($id): array
    {
        return $this->get("company/time-offs/$id");
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array{
     *     success: bool,
     *     data: array{
     *          type: string,
     *          attributes: array<string, mixed>
     *     }
     * }
     */
    public function createTimeOff(array $data): array
    {
        $response = $this->client->post('company/time-offs', $data);

        return JSON::decode((string) $response->getBody(), true);
    }

    /**
     * @param int|numeric-string $id
     */
    public function deleteTimeOff($id): void
    {
        $this->client->delete("company/time-offs/$id");
    }

    /**
     * @param array<string, mixed>|null $params
     *
     * @return array{
     *     success: bool,
     *     data: array{
     *          type: string,
     *          attributes: array<string, mixed>
     *     }
     * }
     */
    private function get(string $endpoint, array $params = null): array
    {
        $response = $this->client->get($endpoint, $params);

        return JSON::decode((string) $response->getBody(), true);
    }
}
