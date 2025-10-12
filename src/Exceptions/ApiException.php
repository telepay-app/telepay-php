<?php

namespace TelePay\Exceptions;

use RuntimeException;

class ApiException extends RuntimeException
{
public function __construct(string $message, public readonly int $status = 0, public readonly ?array $body = null)
{
parent::__construct($message, $status);
}
}
