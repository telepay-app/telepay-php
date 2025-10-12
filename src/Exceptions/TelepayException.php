<?php

declare(strict_types=1);

namespace Telepay\Exceptions;

use RuntimeException;

class TelepayException extends RuntimeException
{
    public function __construct(string $message, int $code = 0, ?\Throwable $prev = null)
    {
        parent::__construct($message, $code, $prev);
    }
}
