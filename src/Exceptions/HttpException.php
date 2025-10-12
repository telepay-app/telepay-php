<?php

declare(strict_types=1);

namespace Telepay\Exceptions;

class HttpException extends TelepayException
{
    public function __construct(
        public readonly int $statusCode,
        public readonly ?string $responseBody = null,
        string $message = 'HTTP error'
    ) {
        parent::__construct($message, $statusCode);
    }
}
