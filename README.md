# Monnify PHP SDK

A comprehensive PHP SDK for integrating with the Monnify Payment Gateway. This SDK provides a clean, object-oriented interface for all Monnify API operations including payment initiation, verification, refunds, and webhook handling.

## Features

- 🚀 **Easy Integration**: Simple and intuitive API
- 🔒 **Secure**: Built-in security features and validation
- 📦 **Complete Coverage**: All Monnify API endpoints supported
- 🧪 **Well Tested**: Comprehensive test suite
- 📚 **Well Documented**: Clear documentation and examples
- 🔄 **Webhook Support**: Built-in webhook verification and handling
- 💰 **Multiple Payment Methods**: Card, Bank Transfer, USSD, etc.

## Installation

```bash
composer require praisedare/monnify-sdk
```

## Quick Start

### 1. Initialize the SDK

```php
use PraiseDare\Monnify\Monnify;

$monnify = new Monnify([
    'secret_key' => 'your_secret_key',
    'public_key' => 'your_public_key',
    'contract_code' => 'your_contract_code',
    'environment' => 'sandbox', // or 'live'
]);
```

### 2. Initialize a Payment

```php
$paymentData = [
    'amount' => 1000.00,
    'customerName' => 'John Doe',
    'customerEmail' => 'john@example.com',
    'paymentReference' => 'TXN-' . uniqid(),
    'paymentDescription' => 'Payment for services',
    'currencyCode' => 'NGN',
    'contractCode' => 'your_contract_code',
    'redirectUrl' => 'https://yourwebsite.com/callback',
    'paymentMethods' => ['CARD', 'ACCOUNT_TRANSFER', 'USSD']
];

$response = $monnify->payment()->initialize($paymentData);
```

### 3. Verify Payment

```php
$transactionReference = 'MNFY|20240101|123456789';
$response = $monnify->payment()->verify($transactionReference);
```

### 4. Handle Webhooks

```php
$webhookData = $request->getContent();
$signature = $request->header('MNFY-SIGNATURE');

if ($monnify->webhook()->verify($webhookData, $signature)) {
    $payload = json_decode($webhookData, true);
    // Process the webhook data
}
```

## Configuration

### Environment Variables

```env
MONNIFY_SECRET_KEY=your_secret_key
MONNIFY_PUBLIC_KEY=your_public_key
MONNIFY_CONTRACT_CODE=your_contract_code
MONNIFY_ENVIRONMENT=sandbox
```

### Configuration Array

```php
$config = [
    'secret_key' => env('MONNIFY_SECRET_KEY'),
    'public_key' => env('MONNIFY_PUBLIC_KEY'),
    'contract_code' => env('MONNIFY_CONTRACT_CODE'),
    'environment' => env('MONNIFY_ENVIRONMENT', 'sandbox'),
    'timeout' => 30, // HTTP timeout in seconds
    'verify_ssl' => true, // SSL verification
];
```

## API Reference

### Payment Methods

#### Initialize Payment
```php
$response = $monnify->payment()->initialize([
    'amount' => 1000.00,
    'customerName' => 'John Doe',
    'customerEmail' => 'john@example.com',
    'paymentReference' => 'TXN-' . uniqid(),
    'paymentDescription' => 'Payment for services',
    'currencyCode' => 'NGN',
    'contractCode' => 'your_contract_code',
    'redirectUrl' => 'https://yourwebsite.com/callback',
    'paymentMethods' => ['CARD', 'ACCOUNT_TRANSFER', 'USSD']
]);
```

#### Verify Payment
```php
$response = $monnify->payment()->verify('MNFY|20240101|123456789');
```

#### Get Transaction Status
```php
$response = $monnify->payment()->getStatus('MNFY|20240101|123456789');
```

### Refund Methods

#### Initiate Refund
```php
$response = $monnify->refund()->initiate([
    'transactionReference' => 'MNFY|20240101|123456789',
    'refundAmount' => 500.00,
    'refundReason' => 'Customer request',
    'refundReference' => 'REF-' . uniqid()
]);
```

#### Get Refund Status
```php
$response = $monnify->refund()->getStatus('REF-' . uniqid());
```

### Settlement Methods

#### Get Settlement Accounts
```php
$response = $monnify->settlement()->getAccounts();
```

#### Get Settlement Transactions
```php
$response = $monnify->settlement()->getTransactions([
    'page' => 1,
    'size' => 20,
    'fromDate' => '2024-01-01',
    'toDate' => '2024-01-31'
]);
```

### Webhook Methods

#### Verify Webhook
```php
$isValid = $monnify->webhook()->verify($webhookData, $signature);
```

#### Parse Webhook
```php
$payload = $monnify->webhook()->parse($webhookData);
```

## Error Handling

```php
try {
    $response = $monnify->payment()->initialize($paymentData);
} catch (PraiseDare\Monnify\Exceptions\MonnifyException $e) {
    // Handle Monnify-specific errors
    echo "Error: " . $e->getMessage();
    echo "Code: " . $e->getCode();
} catch (Exception $e) {
    // Handle general errors
    echo "General Error: " . $e->getMessage();
}
```

## Response Format

All API responses follow a consistent format:

```php
[
    'success' => true,
    'data' => [
        // Response data
    ],
    'message' => 'Operation successful',
    'code' => 'SUCCESS'
]
```

## Testing

```bash
# Run tests
composer test

# Run tests with coverage
composer test-coverage

# Run static analysis
composer phpstan
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Run the test suite
6. Submit a pull request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

For support, please contact:
- Email: praisedare27@gmail.com
- Documentation: [Monnify API Docs](https://docs.monnify.com)

## Changelog

### v1.0.0
- Initial release
- Payment initialization and verification
- Refund functionality
- Settlement management
- Webhook handling
- Comprehensive error handling
