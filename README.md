# TelePay PHP SDK (Core)


Framework-agnostic SDK to call TelePay API.


## Install
```bash
composer require telepay-app/telepay-php
```


## Usage
```php
use TelePay\Config;
use TelePay\Client;


$config = new Config(
baseUri: 'https://api.telepay.hu',
apiKey: 'your_api_key',
timeout: 15,
);


$client = Client::from($config);


// Create transaction
$tx = $client->createTransaction([
'amount' => 19900,
'currency' => 'HUF',
'order_id' => 'ORD-1234',
'callback_url' => 'https://example.com/api/telepay/webhook',
'return_url' => 'https://example.com/return'
]);


// Get transaction
$tx = $client->getTransaction($tx['id'] ?? 'tx_123');
```


## Exceptions
- `TelePay\\Exceptions\\ApiException` is thrown on non-2xx responses, with `status` and decoded `body`.


## License
MIT
