# telepay-app/telepay-php

Minimal PHP kliens a TelePay Transaction API-hoz.

## Telepítés

```bash
composer require telepay-app/telepay-php
```


## Usage
```php
use Telepay\Client;

$client = new Client(
    apiKey: 'PUBLIC_API_KEY',
    secret: 'SECRET',
);

// 💳 Új tranzakció létrehozása
$payload = [
  'msisdn' => '+36301234567',
  'description' => 'XYZ termék neve',
  'success_message' => 'Köszönjük a vásárlást!',
  'currency' => 'HUF',
  'cart' => [
    ['name' => 'Teszt termék', 'price' => 3000, 'quantity' => 1]
  ]
];

$response = $client->createTransaction($payload);
print_r($response);

// 🔎 Tranzakció lekérdezése (a visszatérítésekkel együtt)
$tx = $client->getTransaction('TRANSACTION_UUID');
// $tx['status'], $tx['refunded'] (bool), $tx['refunds'] (tömb)

// 🧾 Teljes refund indítása
// A válaszban: ['queued' => true, 'transaction_id' => '...', 'refund_id' => '...']
$response = $client->refundTransaction('TRANSACTION_UUID');
print_r($response);
```

### `getTransaction()` válasz (példa)
```json
{
  "id": "3b035ac3-...",
  "type": "payment",
  "status": "completed",
  "amount": 4990,
  "currency": "HUF",
  "order_id": "2026-0019253",
  "provider_tx_id": "650446811",
  "refunded": true,
  "refunds": [
    {
      "id": "a14b8b0b-...",
      "type": "refund",
      "status": "completed",
      "amount": 4990,
      "currency": "HUF",
      "provider_tx_id": "650447207",
      "parent_id": "3b035ac3-..."
    }
  ]
}
```

## Webhookok

A TelePay aláírt webhookot küld a beállított URL-re. Az aláírás a **nyers** body-ra
megy (`Telepay-Signature: t=<ts>, v1=<hmac>`), a HMAC kulcs az app secretje.

```php
use Telepay\Webhooks\Verifier;
use Telepay\Exceptions\SignatureVerificationException;

$rawBody = file_get_contents('php://input');
$sigHeader = $_SERVER['HTTP_TELEPAY_SIGNATURE'] ?? '';

try {
    Verifier::assertValid($sigHeader, $rawBody, 'WEBHOOK_SECRET'); // dob, ha érvénytelen
} catch (SignatureVerificationException $e) {
    http_response_code(400);
    exit;
}

$event = json_decode($rawBody, true);

switch ($event['event'] ?? null) {
    case 'transaction.updated':
        // státuszváltás (pl. completed/failed/expired) – $event['id'], $event['status']
        break;

    case 'transaction.refunded':
        // VISSZATÉRÍTÉS – $event['id'] az EREDETI tranzakció,
        // $event['refund'] = ['id','amount','currency','status','provider_tx_id']
        $refund = $event['refund'];
        // ... rendelés visszatérítettre állítása ...
        break;
}
```

### Események
- **`transaction.updated`** — a fizetés státusza változott (pl. `completed`, `failed`, `cancelled`, `expired`).
- **`transaction.refunded`** — visszatérítés történt. Az `id` az EREDETI tranzakció uuid-ja; a `refund` objektum tartalmazza a refund adatait. *(Az eredeti tranzakció státusza `completed` marad — a visszatérítésről innen, illetve a `getTransaction()` `refunds` tömbjéből értesülsz.)*
