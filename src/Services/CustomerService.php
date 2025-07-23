<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Services;

use PraiseDare\Monnify\Http\Client;
use PraiseDare\Monnify\Exceptions\MonnifyException;

/**
 * Customer Service for Monnify API
 *
 * Handles all customer-related operations
 */
class CustomerService
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
     * Create a customer
     *
     * @param array $data Customer data
     * @return array Response data
     * @throws MonnifyException
     */
    public function create(array $data): array
    {
        $this->validateCustomerData($data);

        $payload = [
            'email' => $data['email'],
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
        ];

        return $this->client->post('/api/v1/customers', $payload);
    }

    /**
     * Get customer by email
     *
     * @param string $email Customer email
     * @return array Response data
     * @throws MonnifyException
     */
    public function getByEmail(string $email): array
    {
        if (empty($email)) {
            throw new MonnifyException('Email is required', 400, null, 'VALIDATION_ERROR');
        }

        return $this->client->get("/api/v1/customers?email={$email}");
    }

    /**
     * Get customer by ID
     *
     * @param string $customerId Customer ID
     * @return array Response data
     * @throws MonnifyException
     */
    public function getById(string $customerId): array
    {
        if (empty($customerId)) {
            throw new MonnifyException('Customer ID is required', 400, null, 'VALIDATION_ERROR');
        }

        return $this->client->get("/api/v1/customers/{$customerId}");
    }

    /**
     * Update customer
     *
     * @param string $customerId Customer ID
     * @param array $data Customer data
     * @return array Response data
     * @throws MonnifyException
     */
    public function update(string $customerId, array $data): array
    {
        if (empty($customerId)) {
            throw new MonnifyException('Customer ID is required', 400, null, 'VALIDATION_ERROR');
        }

        $this->validateCustomerData($data, false);

        $payload = [];

        if (isset($data['name'])) {
            $payload['name'] = $data['name'];
        }

        if (isset($data['phone'])) {
            $payload['phone'] = $data['phone'];
        }

        return $this->client->put("/api/v1/customers/{$customerId}", $payload);
    }

    /**
     * Delete customer
     *
     * @param string $customerId Customer ID
     * @return array Response data
     * @throws MonnifyException
     */
    public function delete(string $customerId): array
    {
        if (empty($customerId)) {
            throw new MonnifyException('Customer ID is required', 400, null, 'VALIDATION_ERROR');
        }

        return $this->client->delete("/api/v1/customers/{$customerId}");
    }

    /**
     * Get all customers
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

        $endpoint = '/api/v1/customers';
        if (!empty($queryParams)) {
            $endpoint .= '?' . implode('&', $queryParams);
        }

        return $this->client->get($endpoint);
    }

    /**
     * Validate customer data
     *
     * @param array $data Customer data
     * @param bool $isCreate Whether this is for creating a customer
     * @throws MonnifyException
     */
    private function validateCustomerData(array $data, bool $isCreate = true): void
    {
        if ($isCreate) {
            $requiredFields = ['email', 'name'];

            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    throw new MonnifyException("Field '{$field}' is required", 400, null, 'VALIDATION_ERROR');
                }
            }

            // Validate email
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new MonnifyException('Invalid email address', 400, null, 'VALIDATION_ERROR');
            }
        }

        // Validate name if provided
        if (isset($data['name']) && empty($data['name'])) {
            throw new MonnifyException('Name cannot be empty', 400, null, 'VALIDATION_ERROR');
        }

        // Validate phone if provided
        if (isset($data['phone']) && !empty($data['phone'])) {
            if (!preg_match('/^\+?[1-9]\d{1,14}$/', $data['phone'])) {
                throw new MonnifyException('Invalid phone number', 400, null, 'VALIDATION_ERROR');
            }
        }
    }

    /**
     * Check if customer exists
     *
     * @param string $email Customer email
     * @return bool
     */
    public function exists(string $email): bool
    {
        try {
            $response = $this->getByEmail($email);
            return isset($response['responseBody']) && !empty($response['responseBody']);
        } catch (MonnifyException $e) {
            return false;
        }
    }
}
