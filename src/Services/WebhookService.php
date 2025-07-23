<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Services;

use PraiseDare\Monnify\Config\Config;
use PraiseDare\Monnify\Exceptions\MonnifyException;

/**
 * Webhook Service for Monnify API
 *
 * Handles webhook verification and parsing
 */
class WebhookService
{
    private Config $config;

    /**
     * Constructor
     *
     * @param Config $config Configuration object
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Verify webhook signature
     *
     * @param string $webhookData Raw webhook data
     * @param string $signature Webhook signature
     * @return bool
     * @throws MonnifyException
     */
    public function verify(string $webhookData, string $signature): bool
    {
        if (empty($webhookData)) {
            throw new MonnifyException('Webhook data is required', 400, null, 'VALIDATION_ERROR');
        }

        if (empty($signature)) {
            throw new MonnifyException('Webhook signature is required', 400, null, 'VALIDATION_ERROR');
        }

        $expectedSignature = hash_hmac('sha512', $webhookData, $this->config->getSecretKey());

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Parse webhook data
     *
     * @param string $webhookData Raw webhook data
     * @return array Parsed webhook data
     * @throws MonnifyException
     */
    public function parse(string $webhookData): array
    {
        if (empty($webhookData)) {
            throw new MonnifyException('Webhook data is required', 400, null, 'VALIDATION_ERROR');
        }

        $data = json_decode($webhookData, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new MonnifyException('Invalid JSON in webhook data', 400, null, 'VALIDATION_ERROR');
        }

        return $data;
    }

    /**
     * Verify and parse webhook
     *
     * @param string $webhookData Raw webhook data
     * @param string $signature Webhook signature
     * @return array Parsed webhook data
     * @throws MonnifyException
     */
    public function verifyAndParse(string $webhookData, string $signature): array
    {
        if (!$this->verify($webhookData, $signature)) {
            throw new MonnifyException('Invalid webhook signature', 400, null, 'INVALID_SIGNATURE');
        }

        return $this->parse($webhookData);
    }

    /**
     * Get webhook event type
     *
     * @param array $webhookData Parsed webhook data
     * @return string|null
     */
    public function getEventType(array $webhookData): ?string
    {
        return $webhookData['eventType'] ?? null;
    }

    /**
     * Get transaction reference from webhook
     *
     * @param array $webhookData Parsed webhook data
     * @return string|null
     */
    public function getTransactionReference(array $webhookData): ?string
    {
        return $webhookData['eventData']['transactionReference'] ?? null;
    }

    /**
     * Get payment status from webhook
     *
     * @param array $webhookData Parsed webhook data
     * @return string|null
     */
    public function getPaymentStatus(array $webhookData): ?string
    {
        return $webhookData['eventData']['paymentStatus'] ?? null;
    }

    /**
     * Check if webhook is for successful payment
     *
     * @param array $webhookData Parsed webhook data
     * @return bool
     */
    public function isSuccessfulPayment(array $webhookData): bool
    {
        return $this->getEventType($webhookData) === 'SUCCESSFUL_TRANSACTION'
            && $this->getPaymentStatus($webhookData) === 'PAID';
    }

    /**
     * Check if webhook is for failed payment
     *
     * @param array $webhookData Parsed webhook data
     * @return bool
     */
    public function isFailedPayment(array $webhookData): bool
    {
        return $this->getEventType($webhookData) === 'FAILED_TRANSACTION'
            && $this->getPaymentStatus($webhookData) === 'FAILED';
    }

    /**
     * Check if webhook is for pending payment
     *
     * @param array $webhookData Parsed webhook data
     * @return bool
     */
    public function isPendingPayment(array $webhookData): bool
    {
        return $this->getEventType($webhookData) === 'PENDING_TRANSACTION'
            && $this->getPaymentStatus($webhookData) === 'PENDING';
    }
}
