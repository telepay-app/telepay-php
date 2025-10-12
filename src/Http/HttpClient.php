<?php

namespace TelePay\Http;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\HandlerStack;
use Psr\Http\Message\ResponseInterface;

final class HttpClient
{
public function __construct(private readonly Guzzle $guzzle) {}

public static function make(string $baseUri, string $apiKey, int $timeout = 15): self
{
$stack = HandlerStack::create();
$stack->push(RetryMiddleware::exponential());

$guzzle = new Guzzle([
'base_uri' => rtrim($baseUri, '/'),
'timeout' => $timeout,
'headers' => [
'Authorization' => "Bearer {$apiKey}",
'Accept' => 'application/json',
'Content-Type' => 'application/json',
],
'handler' => $stack,
]);

return new self($guzzle);
}

public function get(string $uri, array $query = []): ResponseInterface
{
return $this->guzzle->get($uri, ['query' => $query]);
}

public function post(string $uri, array $json = []): ResponseInterface
{
return $this->guzzle->post($uri, ['json' => $json]);
}
}
