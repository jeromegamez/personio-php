<?php

declare(strict_types=1);

namespace Gamez\Personio\Exception;

use Throwable;

final class RuntimeError extends \RuntimeException implements PersonioException
{
    public static function because(string $reason, Throwable $previous = null): self
    {
        $code = $previous ? $previous->getCode() : 0;

        return new self($reason, $code, $previous);
    }
}
