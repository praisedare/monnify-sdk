<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Services;

use PraiseDare\Monnify\Http\Client;
use PraiseDare\Monnify\Exceptions\MonnifyException;

/**
 * Refund Service for Monnify API
 *
 * Handles all refund-related operations
 */
class RefundService
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
     * Initiate a refund
     *
     * @param array $data Refund data
     * @return array Response data
     * @throws MonnifyException
     */
    public function initiate(array $data): array
    {
        $this->validateRefundData($data);

        $payload = [
            'transactionReference' => $data['transactionReference'],
            'refundAmount' => $data['refundAmount'],
            'refundReason' => $data['refundReason'] ?? 'Customer request',
            'refundReference' => $data['refundReference'],
        ];

        return $this->client->post('/api/v1/merchant/transactions/refund', $payload);
    }

    /**
     * Get refund status
     *
     * @param string $refundReference Refund reference
     * @return array Response data
     * @throws MonnifyException
     */
    public function getStatus(string $refundReference): array
    {
        if (empty($refundReference)) {
            throw new MonnifyException('Refund reference is required', 400, null, 'VALIDATION_ERROR');
        }

        return $this->client->get("/api/v1/merchant/transactions/refund?refundReference={$refundReference}");
    }

    /**
     * Get all refunds
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

        $endpoint = '/api/v1/merchant/transactions/refund';
        if (!empty($queryParams)) {
            $endpoint .= '?' . implode('&', $queryParams);
        }

        return $this->client->get($endpoint);
    }

    /**
     * Validate refund data
     *
     * @param array $data Refund data
     * @throws MonnifyException
     */
    private function validateRefundData(array $data): void
    {
        $requiredFields = [
            'transactionReference',
            'refundAmount',
            'refundReference'
        ];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new MonnifyException("Field '{$field}' is required", 400, null, 'VALIDATION_ERROR');
            }
        }

        // Validate refund amount
        if (!is_numeric($data['refundAmount']) || $data['refundAmount'] <= 0) {
            throw new MonnifyException('Refund amount must be a positive number', 400, null, 'VALIDATION_ERROR');
        }

        // Validate refund reference
        if (strlen($data['refundReference']) > 100) {
            throw new MonnifyException('Refund reference must not exceed 100 characters', 400, null, 'VALIDATION_ERROR');
        }
    }

    /**
     * Check if refund is successful
     *
     * @param array $response Refund response
     * @return bool
     */
    public function isSuccessful(array $response): bool
    {
        return isset($response['responseBody']['status'])
            && $response['responseBody']['status'] === 'SUCCESSFUL';
    }

    /**
     * Check if refund is pending
     *
     * @param array $response Refund response
     * @return bool
     */
    public function isPending(array $response): bool
    {
        return isset($response['responseBody']['status'])
            && $response['responseBody']['status'] === 'PENDING';
    }

    /**
     * Check if refund failed
     *
     * @param array $response Refund response
     * @return bool
     */
    public function isFailed(array $response): bool
    {
        return isset($response['responseBody']['status'])
            && $response['responseBody']['status'] === 'FAILED';
    }

    /**
     * Get refund reference from response
     *
     * @param array $response Refund response
     * @return string|null
     */
    public function getRefundReference(array $response): ?string
    {
        return $response['responseBody']['refundReference'] ?? null;
    }
}
