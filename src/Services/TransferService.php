<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Services;

use PraiseDare\Monnify\Http\Client;
use PraiseDare\Monnify\Exceptions\MonnifyException;

/**
 * Transfer Service for Monnify API
 *
 * Handles all transfer/disbursement-related operations
 */
class TransferService
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
     * Initiate a single transfer
     *
     * @param array{
     *  amount: float,
     *  reference: string,
     *  narration: string,
     *  destinationBankCode: string,
     *  destinationAccountNumber: string,
     *  destinationAccountName: string,
     *  currency: string,
     *  sourceAccountNumber: string,
     *  beneficiaryEmail?: string,
     *  beneficiaryPhone?: string,
     *  metadata?: array<string, mixed>
     * } $data Transfer data
     * @return array Response data
     * @throws MonnifyException
     */
    public function initiateSingle(array $data): array
    {
        $this->validateSingleTransferData($data);

        $payload = [
            'amount' => $data['amount'],
            'reference' => $data['reference'],
            'narration' => $data['narration'],
            'destinationBankCode' => $data['destinationBankCode'],
            'destinationAccountNumber' => $data['destinationAccountNumber'],
            'destinationAccountName' => $data['destinationAccountName'],
            'currency' => $data['currency'] ?? 'NGN',
            'sourceAccountNumber' => $data['sourceAccountNumber'],
        ];

        // Add optional fields if provided
        if (isset($data['beneficiaryEmail'])) {
            $payload['beneficiaryEmail'] = $data['beneficiaryEmail'];
        }

        if (isset($data['beneficiaryPhone'])) {
            $payload['beneficiaryPhone'] = $data['beneficiaryPhone'];
        }

        if (isset($data['metadata'])) {
            $payload['metadata'] = $data['metadata'];
        }

        return $this->client->post('/api/v1/disbursements/single', $payload);
    }

    /**
     * Initiate an asynchronous transfer
     *
     * @param array{
     *  amount: float,
     *  reference: string,
     *  narration: string,
     *  destinationBankCode: string,
     *  destinationAccountNumber: string,
     *  destinationAccountName: string,
     *  currency: string,
     *  sourceAccountNumber: string,
     *  beneficiaryEmail?: string,
     *  beneficiaryPhone?: string,
     *  metadata?: array<string, mixed>
     * } $data Transfer data
     * @return array Response data
     * @throws MonnifyException
     */
    public function initiateAsync(array $data): array
    {
        $this->validateSingleTransferData($data);

        $payload = [
            'amount' => $data['amount'],
            'reference' => $data['reference'],
            'narration' => $data['narration'],
            'destinationBankCode' => $data['destinationBankCode'],
            'destinationAccountNumber' => $data['destinationAccountNumber'],
            'destinationAccountName' => $data['destinationAccountName'],
            'currency' => $data['currency'] ?? 'NGN',
            'sourceAccountNumber' => $data['sourceAccountNumber'],
        ];

        // Add optional fields if provided
        if (isset($data['beneficiaryEmail'])) {
            $payload['beneficiaryEmail'] = $data['beneficiaryEmail'];
        }

        if (isset($data['beneficiaryPhone'])) {
            $payload['beneficiaryPhone'] = $data['beneficiaryPhone'];
        }

        if (isset($data['metadata'])) {
            $payload['metadata'] = $data['metadata'];
        }

        return $this->client->post('/api/v1/disbursements/single/async', $payload);
    }

    /**
     * Initiate a bulk transfer
     *
     * @param array{
     *  title: string,
     *  batchReference: string,
     *  narration: string,
     *  sourceAccountNumber: string,
     *  currency: string,
     *  onValidationFailure: string,
     *  notificationInterval: int,
     *  transactions: array<array{
     *    amount: float,
     *    reference: string,
     *    narration: string,
     *    destinationBankCode: string,
     *    destinationAccountNumber: string,
     *    destinationAccountName: string,
     *    beneficiaryEmail?: string,
     *    beneficiaryPhone?: string,
     *    metadata?: array<string, mixed>
     *  }>
     * } $data Bulk transfer data
     * @return array Response data
     * @throws MonnifyException
     */
    public function initiateBulk(array $data): array
    {
        $this->validateBulkTransferData($data);

        $payload = [
            'title' => $data['title'],
            'batchReference' => $data['batchReference'],
            'narration' => $data['narration'],
            'sourceAccountNumber' => $data['sourceAccountNumber'],
            'currency' => $data['currency'] ?? 'NGN',
            'onValidationFailure' => $data['onValidationFailure'] ?? 'CONTINUE',
            'notificationInterval' => $data['notificationInterval'] ?? 5,
            'transactions' => $data['transactions'],
        ];

        return $this->client->post('/api/v1/disbursements/bulk', $payload);
    }

    /**
     * Authorize a single transfer
     *
     * @param array{
     *  reference: string,
     *  authorizationCode: string
     * } $data Authorization data
     * @return array Response data
     * @throws MonnifyException
     */
    public function authorizeSingle(array $data): array
    {
        if (empty($data['reference'])) {
            throw new MonnifyException('Reference is required', 400, null, 'VALIDATION_ERROR');
        }

        if (empty($data['authorizationCode'])) {
            throw new MonnifyException('Authorization code is required', 400, null, 'VALIDATION_ERROR');
        }

        $payload = [
            'reference' => $data['reference'],
            'authorizationCode' => $data['authorizationCode'],
        ];

        return $this->client->post('/api/v1/disbursements/single/authorize', $payload);
    }

    /**
     * Authorize a bulk transfer
     *
     * @param array{
     *  batchReference: string,
     *  authorizationCode: string
     * } $data Authorization data
     * @return array Response data
     * @throws MonnifyException
     */
    public function authorizeBulk(array $data): array
    {
        if (empty($data['batchReference'])) {
            throw new MonnifyException('Batch reference is required', 400, null, 'VALIDATION_ERROR');
        }

        if (empty($data['authorizationCode'])) {
            throw new MonnifyException('Authorization code is required', 400, null, 'VALIDATION_ERROR');
        }

        $payload = [
            'batchReference' => $data['batchReference'],
            'authorizationCode' => $data['authorizationCode'],
        ];

        return $this->client->post('/api/v1/disbursements/bulk/authorize', $payload);
    }

    /**
     * Resend OTP for transfer authorization
     *
     * @param string $reference Transfer reference
     * @return array Response data
     * @throws MonnifyException
     */
    public function resendOtp(string $reference): array
    {
        if (empty($reference)) {
            throw new MonnifyException('Reference is required', 400, null, 'VALIDATION_ERROR');
        }

        $payload = [
            'reference' => $reference,
        ];

        return $this->client->post('/api/v1/disbursements/single/resend-otp', $payload);
    }

    /**
     * Get single transfer status
     *
     * @param string $reference Transfer reference
     * @return array Response data
     * @throws MonnifyException
     */
    public function getSingleTransferStatus(string $reference): array
    {
        if (empty($reference)) {
            throw new MonnifyException('Reference is required', 400, null, 'VALIDATION_ERROR');
        }

        return $this->client->get("/api/v1/disbursements/single/{$reference}");
    }

    /**
     * List all single transfers
     *
     * @param array{
     *  page?: int,
     *  size?: int,
     *  from?: string,
     *  to?: string,
     *  status?: string
     * } $filters Optional filters
     * @return array Response data
     * @throws MonnifyException
     */
    public function listSingleTransfers(array $filters = []): array
    {
        $queryParams = [];

        if (isset($filters['page'])) {
            $queryParams['page'] = $filters['page'];
        }

        if (isset($filters['size'])) {
            $queryParams['size'] = $filters['size'];
        }

        if (isset($filters['from'])) {
            $queryParams['from'] = $filters['from'];
        }

        if (isset($filters['to'])) {
            $queryParams['to'] = $filters['to'];
        }

        if (isset($filters['status'])) {
            $queryParams['status'] = $filters['status'];
        }

        $queryString = !empty($queryParams) ? '?' . http_build_query($queryParams) : '';

        return $this->client->get("/api/v1/disbursements/single{$queryString}");
    }

    /**
     * Get bulk transfer transactions
     *
     * @param string $batchReference Batch reference
     * @return array Response data
     * @throws MonnifyException
     */
    public function getBulkTransferTransactions(string $batchReference): array
    {
        if (empty($batchReference)) {
            throw new MonnifyException('Batch reference is required', 400, null, 'VALIDATION_ERROR');
        }

        return $this->client->get("/api/v1/disbursements/bulk/{$batchReference}/transactions");
    }

    /**
     * Get bulk transfer status
     *
     * @param string $batchReference Batch reference
     * @return array Response data
     * @throws MonnifyException
     */
    public function getBulkTransferStatus(string $batchReference): array
    {
        if (empty($batchReference)) {
            throw new MonnifyException('Batch reference is required', 400, null, 'VALIDATION_ERROR');
        }

        return $this->client->get("/api/v1/disbursements/bulk/{$batchReference}");
    }

    /**
     * Search disbursement transactions
     *
     * @param array{
     *  page?: int,
     *  size?: int,
     *  from?: string,
     *  to?: string,
     *  status?: string,
     *  reference?: string,
     *  destinationAccountNumber?: string,
     *  destinationBankCode?: string
     * } $filters Search filters
     * @return array Response data
     * @throws MonnifyException
     */
    public function searchDisbursements(array $filters = []): array
    {
        $queryParams = [];

        if (isset($filters['page'])) {
            $queryParams['page'] = $filters['page'];
        }

        if (isset($filters['size'])) {
            $queryParams['size'] = $filters['size'];
        }

        if (isset($filters['from'])) {
            $queryParams['from'] = $filters['from'];
        }

        if (isset($filters['to'])) {
            $queryParams['to'] = $filters['to'];
        }

        if (isset($filters['status'])) {
            $queryParams['status'] = $filters['status'];
        }

        if (isset($filters['reference'])) {
            $queryParams['reference'] = $filters['reference'];
        }

        if (isset($filters['destinationAccountNumber'])) {
            $queryParams['destinationAccountNumber'] = $filters['destinationAccountNumber'];
        }

        if (isset($filters['destinationBankCode'])) {
            $queryParams['destinationBankCode'] = $filters['destinationBankCode'];
        }

        $queryString = !empty($queryParams) ? '?' . http_build_query($queryParams) : '';

        return $this->client->get("/api/v1/disbursements/search{$queryString}");
    }

    /**
     * Get wallet balance
     *
     * @param string $accountNumber Account number
     * @return array Response data
     * @throws MonnifyException
     */
    public function getWalletBalance(string $accountNumber): array
    {
        if (empty($accountNumber)) {
            throw new MonnifyException('Account number is required', 400, null, 'VALIDATION_ERROR');
        }

        return $this->client->get("/api/v1/disbursements/wallet-balance?accountNumber={$accountNumber}");
    }

    /**
     * Validate single transfer data
     *
     * @param array $data Transfer data
     * @throws MonnifyException
     */
    private function validateSingleTransferData(array $data): void
    {
        $requiredFields = [
            'amount', 'reference', 'narration', 'destinationBankCode',
            'destinationAccountNumber', 'destinationAccountName', 'sourceAccountNumber'
        ];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new MonnifyException("Field '{$field}' is required", 400, null, 'VALIDATION_ERROR');
            }
        }

        if ($data['amount'] <= 0) {
            throw new MonnifyException('Amount must be greater than 0', 400, null, 'VALIDATION_ERROR');
        }
    }

    /**
     * Validate bulk transfer data
     *
     * @param array $data Bulk transfer data
     * @throws MonnifyException
     */
    private function validateBulkTransferData(array $data): void
    {
        $requiredFields = [
            'title', 'batchReference', 'narration', 'sourceAccountNumber', 'transactions'
        ];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new MonnifyException("Field '{$field}' is required", 400, null, 'VALIDATION_ERROR');
            }
        }

        if (empty($data['transactions']) || !is_array($data['transactions'])) {
            throw new MonnifyException('Transactions must be a non-empty array', 400, null, 'VALIDATION_ERROR');
        }

        foreach ($data['transactions'] as $index => $transaction) {
            $this->validateSingleTransferData($transaction);
        }
    }
}