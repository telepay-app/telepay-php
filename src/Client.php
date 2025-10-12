<?php

namespace TelePay;

use TelePay\Exceptions\ApiException;
use TelePay\Http\HttpClient;

final class Client
{
public function __construct(private readonly Config $config, private readonly HttpClient $http) {}

public static function from(Config $config): self
{
$http = HttpClient::make($config->getBaseUri(), $config->getApiKey(), $config->getTimeout());
return new self($config, $http);
}

/** Create a payment transaction. */
public function createTransaction(array $payload): array
{
$res = $this->http->post('/v1/transactions', $payload);
return $this->jsonOrFail($res, 'Create transaction failed');
}

/** Get a transaction by ID/UUID. */
public function getTransaction(string $id): array
{
$res = $this->http->get("/v1/transactions/{$id}");
return $this->jsonOrFail($res, 'Get transaction failed');
}

/** Optional helpers, if supported by your API */
public function refundTransaction(string $id, array $payload = []): array
{
$res = $this->http->post("/v1/transactions/{$id}/refund", $payload);
return $this->jsonOrFail($res, 'Refund failed');
}

public function cancelTransaction(string $id): array
{
$res = $this->http->post("/v1/transactions/{$id}/cancel", []);
return $this->jsonOrFail($res, 'Cancel failed');
}

private function jsonOrFail(\Psr\Http\Message\ResponseInterface $res, string $message): array
{
$code = $res->getStatusCode();
$body = (string) $res->getBody();
$json = json_decode($body, true);
if ($code >= 400) {
throw new ApiException($message, $code, is_array($json) ? $json : null);
}
return is_array($json) ? $json : [];
}
}
