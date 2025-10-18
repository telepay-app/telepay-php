<?php

declare(strict_types=1);

namespace Telepay\Webhooks;

use Telepay\Exceptions\SignatureVerificationException;

final class Verifier
{
    /**
     * Ellenőrzi a Telepay-Signature fejlécet a nyers (raw) request body-ra.
     *
     * @param string $signatureHeader A "Telepay-Signature" fejléc ("t=..., v1=...").
     * @param string $rawBody         A NYERS request body (decode nélkül!).
     * @param string $secret          A Webhook secret (az app secretje).
     * @param int    $toleranceSec    Időablak (másodpercben), pl. 300.
     * @return bool                   true, ha érvényes az aláírás.
     */
    public static function verify(string $signatureHeader, string $rawBody, string $secret, int $toleranceSec = 300): bool
    {
        [$ts, $sig] = self::parseHeader($signatureHeader);

        // időbélyeg ellenőrzés
        if (abs(time() - (int) $ts) > $toleranceSec) {
            return false;
        }

        $expected = hash_hmac('sha256', $ts . '.' . $rawBody, $secret);
        return hash_equals($expected, $sig);
    }

    /**
     * Ugyanaz mint verify(), de invalid esetben SignatureVerificationException-t dob.
     */
    public static function assertValid(string $signatureHeader, string $rawBody, string $secret, int $toleranceSec = 300): void
    {
        [$ts, $sig] = self::parseHeader($signatureHeader);

        if (!is_numeric($ts)) {
            throw new SignatureVerificationException('Invalid timestamp in signature header.');
        }
        if (abs(time() - (int) $ts) > $toleranceSec) {
            throw new SignatureVerificationException('Signature timestamp outside tolerance window.');
        }

        $expected = hash_hmac('sha256', $ts . '.' . $rawBody, $secret);
        if (!hash_equals($expected, $sig)) {
            throw new SignatureVerificationException('Signature mismatch.');
        }
    }

    /**
     * Kinyeri a t és v1 értékeket a "t=..., v1=..." fejlécből.
     *
     * @return array{0:string,1:string} [timestamp, signature]
     */
    private static function parseHeader(string $header): array
    {
        // t=..., v1=...
        $parts = array_filter(array_map('trim', explode(',', $header)));
        $map = [];
        foreach ($parts as $p) {
            if (str_contains($p, '=')) {
                [$k, $v] = array_map('trim', explode('=', $p, 2));
                $map[$k] = $v;
            }
        }

        $ts  = $map['t']  ?? '';
        $sig = $map['v1'] ?? '';
        return [$ts, $sig];
    }
}
