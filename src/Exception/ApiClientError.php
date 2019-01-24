<?php

declare(strict_types=1);

namespace Gamez\Personio\Exception;

use Gamez\Personio\Support\JSON;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Throwable;

final class ApiClientError extends RuntimeException implements PersonioException
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ResponseInterface|null
     */
    private $response;

    public function __construct(RequestInterface $request, ?ResponseInterface $response, string $message = null, int $code = null, Throwable $previous = null)
    {
        $this->request = $request;

        if ($response) {
            $this->response = $response;
            $message = $message ?: $response->getReasonPhrase();
            $code = $code ?: $response->getStatusCode();
        } elseif ($previous) {
            $message = $message ?: $previous->getMessage();
            $code = $code ?: $previous->getCode();
        } else {
            $message = $message ?: 'An API error occurred';
            $code = 0;
        }

        parent::__construct($message, $code, $previous);
    }

    public static function fromRequestAndReason(RequestInterface $request, string $reason, Throwable $previous = null): self
    {
        return new self($request, null, $reason, null, $previous);
    }

    public static function fromRequestAndResponse(RequestInterface $request, ResponseInterface $response, Throwable $previous = null): self
    {
        try {
            $data = JSON::decode((string) $response->getBody(), true);
        } catch (\Throwable $e) {
            $data = [];
        }
        $message = $data['error']['message'] ?? null;
        $code = $data['error']['code'] ?? 0;

        return new self($request, $response, $message, $code, $previous);
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }

    public function hasResponse(): bool
    {
        return (bool) $this->response;
    }
}
