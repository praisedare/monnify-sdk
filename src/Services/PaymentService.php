<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Services;

use PraiseDare\Monnify\Http\Client;
use PraiseDare\Monnify\Exceptions\MonnifyException;
use PraiseDare\Monnify\Data\Payments\PaymentData;
use PraiseDare\Monnify\Data\Payments\PaymentFilterData;
use PraiseDare\Monnify\Data\Payments\Responses\InitiatePaymentResponse;
use PraiseDare\Monnify\Data\Payments\Responses\VerifyPaymentResponse;
use PraiseDare\Monnify\Data\Payments\Responses\GetAllPaymentsResponse;

/**
 * Payment Service for Monnify API
 *
 * Handles all payment-related operations (received payments related actions,
 * not transfer related actions, use the TransferService for that)
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
     * @param PaymentData $data Payment data
     * @return InitiatePaymentResponse Response data
     * @throws MonnifyException
     */
    public function initialize(PaymentData $data): InitiatePaymentResponse
    {
        $this->validatePaymentData($data);

        $payload = $data->toArray();

        // Add contract code if not provided
        if (!isset($payload['contractCode'])) {
            $payload['contractCode'] = $this->client->getConfig()->getContractCode();
        }

        $response = $this->client->post('/api/v1/merchant/transactions/init-transaction', $payload);

        return InitiatePaymentResponse::fromArray($response);
    }

    /**
     * Verify a payment transaction
     *
     * @param string $transactionReference Transaction reference
     * @return VerifyPaymentResponse Response data
     * @throws MonnifyException
     */
    public function verify(string $transactionReference): VerifyPaymentResponse
    {
        if (empty($transactionReference)) {
            throw new MonnifyException('Transaction reference is required', 400, null, 'VALIDATION_ERROR');
        }

        $transactionReference = urlencode($transactionReference);

        $response = $this->client->get("/api/v2/transactions/{$transactionReference}");

        return VerifyPaymentResponse::fromArray($response);
    }

    /**
     * Get transaction status
     *
     * @param string $transactionReference The Monnify transaction reference
     * @return VerifyPaymentResponse Response data
     * @throws MonnifyException
     */
    public function getStatus(string $transactionReference): VerifyPaymentResponse
    {
        return $this->verify($transactionReference);
    }

    /**
     * Get transaction details
     *
     * @param string $transactionReference Transaction reference
     * @return VerifyPaymentResponse Response data
     * @throws MonnifyException
     */
    public function getDetails(string $transactionReference): VerifyPaymentResponse
    {
        return $this->verify($transactionReference);
    }

    /**
     * Get all transactions
     *
     * @param PaymentFilterData|null $filters Filter parameters
     * @return GetAllPaymentsResponse Response data
     * @throws MonnifyException
     */
    public function getAll(?PaymentFilterData $filters = null): GetAllPaymentsResponse
    {
        $endpoint = '/api/v1/transactions/search';

        if ($filters !== null) {
            $queryParams = $filters->toQueryParams();
            if (!empty($queryParams)) {
                $endpoint .= '?' . http_build_query($queryParams);
            }
        }

        $response = $this->client->get($endpoint);

        return GetAllPaymentsResponse::fromArray($response);
    }

    /**
     * Validate payment data
     *
     * @param PaymentData $data Payment data
     * @throws MonnifyException
     */
    private function validatePaymentData(PaymentData $data): void
    {
        // Validate amount
        if ($data->amount <= 0) {
            throw new MonnifyException('Amount must be a positive number', 400, null, 'VALIDATION_ERROR');
        }

        // Validate email
        if (!filter_var($data->customerEmail, FILTER_VALIDATE_EMAIL)) {
            throw new MonnifyException('Invalid email address', 400, null, 'VALIDATION_ERROR');
        }

        // Validate payment reference
        if (strlen($data->paymentReference) > 100) {
            throw new MonnifyException('Payment reference must not exceed 100 characters', 400, null, 'VALIDATION_ERROR');
        }

        // Validate redirect URL
        if (!filter_var($data->redirectUrl, FILTER_VALIDATE_URL)) {
            throw new MonnifyException('Invalid redirect URL', 400, null, 'VALIDATION_ERROR');
        }
    }

    /**
     * Get payment URL from response
     *
     * @param InitiatePaymentResponse $response Payment response
     * @return string
     */
    public function getPaymentUrl(InitiatePaymentResponse $response): string
    {
        return $response->getPaymentUrl();
    }

    /**
     * Get transaction reference from response
     *
     * @param InitiatePaymentResponse $response Payment response
     * @return string
     */
    public function getTransactionReference(InitiatePaymentResponse $response): string
    {
        return $response->getTransactionReference();
    }

    /**
     * Check if payment is successful
     *
     * @param VerifyPaymentResponse $response Payment verification response
     * @return bool
     */
    public function isSuccessful(VerifyPaymentResponse $response): bool
    {
        return $response->isSuccessful();
    }

    /**
     * Check if payment is pending
     *
     * @param VerifyPaymentResponse $response Payment verification response
     * @return bool
     */
    public function isPending(VerifyPaymentResponse $response): bool
    {
        return $response->isPending();
    }

    /**
     * Check if payment failed
     *
     * @param VerifyPaymentResponse $response Payment verification response
     * @return bool
     */
    public function isFailed(VerifyPaymentResponse $response): bool
    {
        return $response->isFailed();
    }

    /**
     * Check if payment expired
     *
     * @param VerifyPaymentResponse $response Payment verification response
     * @return bool
     */
    public function isExpired(VerifyPaymentResponse $response): bool
    {
        return $response->isExpired();
    }

    /**
     * Get payment status from verification response
     *
     * @param VerifyPaymentResponse $response Payment verification response
     * @return string
     */
    public function getPaymentStatus(VerifyPaymentResponse $response): string
    {
        return $response->getPaymentStatus();
    }

    /**
     * Get amount paid from verification response
     *
     * @param VerifyPaymentResponse $response Payment verification response
     * @return float
     */
    public function getAmountPaid(VerifyPaymentResponse $response): float
    {
        return $response->getAmountPaid();
    }

    /**
     * Get total payable from verification response
     *
     * @param VerifyPaymentResponse $response Payment verification response
     * @return float
     */
    public function getTotalPayable(VerifyPaymentResponse $response): float
    {
        return $response->getTotalPayable();
    }
}
