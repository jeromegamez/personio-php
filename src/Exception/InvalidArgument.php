<?php

declare(strict_types=1);

namespace Gamez\Personio\Exception;

use Throwable;

final class InvalidArgument extends \InvalidArgumentException implements PersonioException
{
    public static function because($reason, Throwable $previous = null): self
    {
        $code = $previous ? $previous->getCode() : 0;

        return new self($reason, $code, $previous);
    }
}
