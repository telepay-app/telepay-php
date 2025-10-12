<?php

declare(strict_types=1);

namespace Telepay\Http;

final class Signature
{
    /**
     * Aláírás formátuma:
     *   "{$timestamp}\n{$method}\n{$path}\n{$rawBody}"
     * HMAC-SHA256 hex, kulcs: secret.
     */
    public static function sign(
        string $secret,
        string $method,
        string $path,
        string $rawBody,
        int $timestamp
    ): string {
        $toSign = $timestamp . "\n" . strtoupper($method) . "\n" . $path . "\n" . $rawBody;
        return hash_hmac('sha256', $toSign, $secret);
    }
}
