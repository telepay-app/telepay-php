<?php

namespace TelePay\Http;

use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class RetryMiddleware
{
/**
* Exponential backoff retry middleware for 429/5xx.
*/
public static function exponential(array $retryOnStatuses = [429,500,502,503,504], int $maxRetries = 6): callable
{
return Middleware::retry(
function (int $retries, RequestInterface $request, ?ResponseInterface $response = null): bool {
if ($retries >= 6) return false;
if ($response === null) return false;
return in_array($response->getStatusCode(), [429,500,502,503,504], true);
},
function (int $retries): int {
// 2^n * 250ms (0.25s, 0.5s, 1s, 2s, 4s, 8s)
return (int) (pow(2, $retries) * 250);
}
);
}
}
