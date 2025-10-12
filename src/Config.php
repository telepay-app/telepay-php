<?php

namespace TelePay;

final class Config
{
public function __construct(
private readonly string $baseUri,
private readonly string $apiKey,
private readonly int $timeout = 15,
) {}

public function getBaseUri(): string { return $this->baseUri; }
public function getApiKey(): string { return $this->apiKey; }
public function getTimeout(): int { return $this->timeout; }
}
