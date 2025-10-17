<?php

declare(strict_types=1);

namespace Telepay;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\RequestException;
use Telepay\Exceptions\HttpException;
use Telepay\Exceptions\TelepayException;
use Telepay\Http\Signature;

final class Client
{
    private Guzzle $http;
    private string $apiKey;
    private string $secret;
    private string $baseUrl;

    public function __construct(
        string $apiKey,
        string $secret,
        ?string $baseUrl = null,
        ?Guzzle $httpClient = null
    ) {
        $this->apiKey  = $apiKey;
        $this->secret  = $secret;
        $this->baseUrl = rtrim($baseUrl ?? 'https://api.telepay.hu', '/');

        $this->http = $httpClient ?? new Guzzle([
            'base_uri' => $this->baseUrl,
            'timeout'  => 10.0,
        ]);
    }

    /**
     * /v1/transactions – SMS fizetés indítása.
     *
     * @param array       $payload Kötelező mezők: msisdn, description, success_message, currency, cart[…]
     * @param string|null $idempotencyKey Opcionális idempotency
     * @return array      Visszatérési tömb a JSON válaszból
     *
     * @throws TelepayException|HttpException
     */
    public function createTransaction(array $payload, ?string $idempotencyKey = null): array
    {
        $path = '/v1/transactions';
        $method = 'POST';

        // Ugyanaz a JSON megy a body-ba és a signature-be
        $rawBody = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($rawBody === false) {
            throw new TelepayException('JSON encode hiba a payloadnál.');
        }

        $ts = time();
        $signature = Signature::sign($this->secret, $method, $path, $rawBody, $ts);

        $headers = [
            'Content-Type'         => 'application/json',
            'X-Telepay-Key'        => $this->apiKey,
            'X-Telepay-Timestamp'  => (string) $ts,
            'X-Telepay-Signature'  => $signature,
        ];

        if ($idempotencyKey) {
            $headers['X-Idempotency-Key'] = $idempotencyKey;
        }

        try {
            $res = $this->http->request($method, $path, [
                'headers' => $headers,
                // Fontos: raw body megy, NEM az 'json' opció, hogy a signature ugyanarra a raw stringre érvényes legyen
                'body'    => $rawBody,
            ]);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $status = $e->getResponse()->getStatusCode();
                $body   = (string) $e->getResponse()->getBody();
                throw new HttpException($status, $body, "TelePay API HTTP $status");
            }
            throw new TelepayException($e->getMessage(), $e->getCode(), $e);
        }

        $body = (string) $res->getBody();
        $data = json_decode($body, true);

        if ($data === null && $body !== 'null' && $body !== '') {
            throw new TelepayException('Váratlan válasz: nem JSON.');
        }

        return $data ?? [];
    }

     /**
     * /v1/transactions/{id}/refund – Teljes visszatérítés indítása.
     *
     * @param string $transactionId A tranzakció azonosítója
     * @return array
     *
     * @throws TelepayException|HttpException
     */
    public function refundTransaction(string $transactionId): array
    {
        $path = "/v1/transactions/{$transactionId}/refund";
        $method = 'POST';
        $rawBody = ''; // teljes refund esetén üres body

        $ts = time();
        $signature = Signature::sign($this->secret, $method, $path, $rawBody, $ts);

        $headers = [
            'Content-Type'         => 'application/json',
            'X-Telepay-Key'        => $this->apiKey,
            'X-Telepay-Timestamp'  => (string) $ts,
            'X-Telepay-Signature'  => $signature,
        ];

        try {
            $res = $this->http->request($method, $path, [
                'headers' => $headers,
                'body'    => $rawBody, // aláírás és body egyezzen
            ]);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            if ($e->hasResponse()) {
                $status = $e->getResponse()->getStatusCode();
                $body   = (string) $e->getResponse()->getBody();
                throw new HttpException($status, $body, "TelePay API HTTP $status");
            }
            throw new TelepayException($e->getMessage(), $e->getCode(), $e);
        }

        $body = (string) $res->getBody();
        $data = json_decode($body, true);

        if ($data === null && $body !== 'null' && $body !== '') {
            throw new TelepayException('Váratlan válasz: nem JSON.');
        }

        return $data ?? [];
    }
    
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }
}
