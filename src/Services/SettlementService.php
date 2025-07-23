<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Services;

use PraiseDare\Monnify\Http\Client;
use PraiseDare\Monnify\Exceptions\MonnifyException;

/**
 * Settlement Service for Monnify API
 *
 * Handles all settlement-related operations
 */
class SettlementService
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
     * Get settlement accounts
     *
     * @return array Response data
     * @throws MonnifyException
     */
    public function getAccounts(): array
    {
        return $this->client->get('/api/v1/merchant/settlements');
    }

    /**
     * Get settlement transactions
     *
     * @param array $filters Filter parameters
     * @return array Response data
     * @throws MonnifyException
     */
    public function getTransactions(array $filters = []): array
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

        if (isset($filters['accountNumber'])) {
            $queryParams[] = "accountNumber={$filters['accountNumber']}";
        }

        $endpoint = '/api/v1/merchant/settlements/transactions';
        if (!empty($queryParams)) {
            $endpoint .= '?' . implode('&', $queryParams);
        }

        return $this->client->get($endpoint);
    }

    /**
     * Get settlement summary
     *
     * @param array $filters Filter parameters
     * @return array Response data
     * @throws MonnifyException
     */
    public function getSummary(array $filters = []): array
    {
        $queryParams = [];

        if (isset($filters['fromDate'])) {
            $queryParams[] = "fromDate={$filters['fromDate']}";
        }

        if (isset($filters['toDate'])) {
            $queryParams[] = "toDate={$filters['toDate']}";
        }

        if (isset($filters['accountNumber'])) {
            $queryParams[] = "accountNumber={$filters['accountNumber']}";
        }

        $endpoint = '/api/v1/merchant/settlements/summary';
        if (!empty($queryParams)) {
            $endpoint .= '?' . implode('&', $queryParams);
        }

        return $this->client->get($endpoint);
    }
}
