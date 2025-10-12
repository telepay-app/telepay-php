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
    baseUrl: 'https://api.telepay.hu' // sandbox: https://api.sandbox.telepay.hu
);

$payload = [
  'msisdn' => '+36301234567',
  'description' => 'XYZ termék neve',
  'success_message' => 'Köszönjük a vásárlást!',
  'currency' => 'HUF',
  'cart' => [
    ['name' => 'Teszt termék', 'price' => 3000, 'quantity' => 1]
  ]
];

$response = $client->createTransaction($payload, idempotencyKey: 'order-12345');
print_r($response);
```
