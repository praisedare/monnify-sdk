<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Services;

use PraiseDare\Monnify\Http\Client;
use PraiseDare\Monnify\Exceptions\MonnifyException;

/**
 * Payment Service for Monnify API
 *
 * Handles all payment-related operations
 */
class PaymentService
{
    private Client $client;

    /**
     * Constructor
     *
     * @param Client $client HTTP client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Initialize a payment transaction
     *
     * @param array{
     *  amount: float,
     *  customerName: string,
     *  customerEmail: string,
     *  paymentReference: string,
     *  paymentDescription?: string,
     *  currencyCode?: string,
     *  contractCode?: string,
     *  redirectUrl: string,
     *  paymentMethods?: array<string>,
     *  customerPhone?: string,
     *  metadata?: array<string, mixed>
     * } $data Payment data
     * @return array Response data
     * @throws MonnifyException
     */
    public function initialize(array $data): array
    {
        $this->validatePaymentData($data);

        $payload = [
            'amount' => $data['amount'],
            'customerName' => $data['customerName'],
            'customerEmail' => $data['customerEmail'],
            'paymentReference' => $data['paymentReference'],
            'paymentDescription' => $data['paymentDescription'] ?? 'Payment for services',
            'currencyCode' => $data['currencyCode'] ?? 'NGN',
            'contractCode' => $data['contractCode'] ?? $this->client->getConfig()->getContractCode(),
            'redirectUrl' => $data['redirectUrl'],
            'paymentMethods' => $data['paymentMethods'] ?? ['CARD', 'ACCOUNT_TRANSFER', 'USSD'],
        ];

        // Add optional fields if provided
        if (isset($data['customerPhone'])) {
            $payload['customerPhone'] = $data['customerPhone'];
        }

        if (isset($data['metadata'])) {
            $payload['metadata'] = $data['metadata'];
        }

        return $this->client->post('/api/v1/merchant/transactions/init-transaction', $payload);
    }

    /**
     * Verify a payment transaction
     *
     * @param string $transactionReference Transaction reference
     * @return array Response data
     * @throws MonnifyException
     */
    public function verify(string $transactionReference): array
    {
        if (empty($transactionReference)) {
            throw new MonnifyException('Transaction reference is required', 400, null, 'VALIDATION_ERROR');
        }

        return $this->client->get("/api/v1/merchant/transactions/query?paymentReference={$transactionReference}");
    }

    /**
     * Get transaction status
     *
     * @param string $transactionReference Transaction reference
     * @return array Response data
     * @throws MonnifyException
     */
    public function getStatus(string $transactionReference): array
    {
        return $this->verify($transactionReference);
    }

    /**
     * Get transaction details
     *
     * @param string $transactionReference Transaction reference
     * @return array Response data
     * @throws MonnifyException
     */
    public function getDetails(string $transactionReference): array
    {
        return $this->verify($transactionReference);
    }

    /**
     * Get all transactions
     *
     * @param array $filters Filter parameters
     * @return array Response data
     * @throws MonnifyException
     */
    public function getAll(array $filters = []): array
    {
        $queryParams = [];

        if (isset($filters['page'])) {
            $queryParams[] = "page={$filters['page']}";
        }

        if (isset($filters['size'])) {
            $queryParams[] = "size={$filters['size']}";
        }

        if (isset($filters['fromDate'])) {
            $queryParams[] = "fromDate={$filters['fromDate']}";
        }

        if (isset($filters['toDate'])) {
            $queryParams[] = "toDate={$filters['toDate']}";
        }

        if (isset($filters['status'])) {
            $queryParams[] = "status={$filters['status']}";
        }

        $endpoint = '/api/v1/merchant/transactions';
        if (!empty($queryParams)) {
            $endpoint .= '?' . implode('&', $queryParams);
        }

        return $this->client->get($endpoint);
    }

    /**
     * Validate payment data
     *
     * @param array $data Payment data
     * @throws MonnifyException
     */
    private function validatePaymentData(array $data): void
    {
        $requiredFields = [
            'amount',
            'customerName',
            'customerEmail',
            'paymentReference',
            'redirectUrl'
        ];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new MonnifyException("Field '{$field}' is required", 400, null, 'VALIDATION_ERROR');
            }
        }

        // Validate amount
        if (!is_numeric($data['amount']) || $data['amount'] <= 0) {
            throw new MonnifyException('Amount must be a positive number', 400, null, 'VALIDATION_ERROR');
        }

        // Validate email
        if (!filter_var($data['customerEmail'], FILTER_VALIDATE_EMAIL)) {
            throw new MonnifyException('Invalid email address', 400, null, 'VALIDATION_ERROR');
        }

        // Validate payment reference
        if (strlen($data['paymentReference']) > 100) {
            throw new MonnifyException('Payment reference must not exceed 100 characters', 400, null, 'VALIDATION_ERROR');
        }

        // Validate redirect URL
        if (!filter_var($data['redirectUrl'], FILTER_VALIDATE_URL)) {
            throw new MonnifyException('Invalid redirect URL', 400, null, 'VALIDATION_ERROR');
        }
    }

    /**
     * Check if payment is successful
     *
     * @param array $response Payment response
     * @return bool
     */
    public function isSuccessful(array $response): bool
    {
        return isset($response['responseBody']['paymentStatus'])
            && $response['responseBody']['paymentStatus'] === 'PAID';
    }

    /**
     * Check if payment is pending
     *
     * @param array $response Payment response
     * @return bool
     */
    public function isPending(array $response): bool
    {
        return isset($response['responseBody']['paymentStatus'])
            && $response['responseBody']['paymentStatus'] === 'PENDING';
    }

    /**
     * Check if payment failed
     *
     * @param array $response Payment response
     * @return bool
     */
    public function isFailed(array $response): bool
    {
        return isset($response['responseBody']['paymentStatus'])
            && $response['responseBody']['paymentStatus'] === 'FAILED';
    }

    /**
     * Get payment URL from response
     *
     * @param array $response Payment response
     * @return string|null
     */
    public function getPaymentUrl(array $response): ?string
    {
        return $response['responseBody']['checkoutUrl'] ?? null;
    }

    /**
     * Get transaction reference from response
     *
     * @param array $response Payment response
     * @return string|null
     */
    public function getTransactionReference(array $response): ?string
    {
        return $response['responseBody']['transactionReference'] ?? null;
    }
}
