<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Services;

use PraiseDare\Monnify\Http\Client;
use PraiseDare\Monnify\Exceptions\MonnifyException;

/**
 * Bank Service for Monnify API
 *
 * Handles all bank-related operations
 */
class BankService
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
     * Get all banks
     *
     * @return array Response data
     * @throws MonnifyException
     */
    public function getAll(): array
    {
        return $this->client->get('/api/v1/banks');
    }

    /**
     * Get banks by country
     *
     * @param string $countryCode Country code (e.g., 'NG' for Nigeria)
     * @return array Response data
     * @throws MonnifyException
     */
    public function getByCountry(string $countryCode): array
    {
        if (empty($countryCode)) {
            throw new MonnifyException('Country code is required', 400, null, 'VALIDATION_ERROR');
        }

        return $this->client->get("/api/v1/banks?countryCode={$countryCode}");
    }

    /**
     * Verify bank account
     *
     * @param string $accountNumber Account number
     * @param string $bankCode Bank code
     * @return array Response data
     * @throws MonnifyException
     */
    public function verifyAccount(string $accountNumber, string $bankCode): array
    {
        if (empty($accountNumber)) {
            throw new MonnifyException('Account number is required', 400, null, 'VALIDATION_ERROR');
        }

        if (empty($bankCode)) {
            throw new MonnifyException('Bank code is required', 400, null, 'VALIDATION_ERROR');
        }

        $payload = [
            'accountNumber' => $accountNumber,
            'bankCode' => $bankCode,
        ];

        return $this->client->post('/api/v1/disbursements/account/validate', $payload);
    }

    /**
     * Get bank by code
     *
     * @param string $bankCode Bank code
     * @return array|null Bank data or null if not found
     * @throws MonnifyException
     */
    public function getByCode(string $bankCode): ?array
    {
        if (empty($bankCode)) {
            throw new MonnifyException('Bank code is required', 400, null, 'VALIDATION_ERROR');
        }

        $banks = $this->getAll();

        if (!isset($banks['responseBody'])) {
            return null;
        }

        foreach ($banks['responseBody'] as $bank) {
            if ($bank['code'] === $bankCode) {
                return $bank;
            }
        }

        return null;
    }

    /**
     * Get bank by name
     *
     * @param string $bankName Bank name
     * @return array|null Bank data or null if not found
     * @throws MonnifyException
     */
    public function getByName(string $bankName): ?array
    {
        if (empty($bankName)) {
            throw new MonnifyException('Bank name is required', 400, null, 'VALIDATION_ERROR');
        }

        $banks = $this->getAll();

        if (!isset($banks['responseBody'])) {
            return null;
        }

        foreach ($banks['responseBody'] as $bank) {
            if (stripos($bank['name'], $bankName) !== false) {
                return $bank;
            }
        }

        return null;
    }

    /**
     * Get account holder name
     *
     * @param string $accountNumber Account number
     * @param string $bankCode Bank code
     * @return string|null Account holder name or null if not found
     * @throws MonnifyException
     */
    public function getAccountHolderName(string $accountNumber, string $bankCode): ?string
    {
        $response = $this->verifyAccount($accountNumber, $bankCode);

        return $response['responseBody']['accountName'] ?? null;
    }

    /**
     * Check if account is valid
     *
     * @param string $accountNumber Account number
     * @param string $bankCode Bank code
     * @return bool
     * @throws MonnifyException
     */
    public function isAccountValid(string $accountNumber, string $bankCode): bool
    {
        try {
            $response = $this->verifyAccount($accountNumber, $bankCode);
            return isset($response['responseBody']['accountName']) && !empty($response['responseBody']['accountName']);
        } catch (MonnifyException $e) {
            return false;
        }
    }
}
