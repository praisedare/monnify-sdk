<?php

declare(strict_types=1);

namespace PraiseDare\Monnify;

use Illuminate\Routing\Router;
use PraiseDare\Monnify\Services\PaymentService;
use PraiseDare\Monnify\Services\RefundService;
use PraiseDare\Monnify\Services\SettlementService;
use PraiseDare\Monnify\Services\WebhookService;
use PraiseDare\Monnify\Services\BankService;
use PraiseDare\Monnify\Services\CustomerService;
use PraiseDare\Monnify\Config\Config;
use PraiseDare\Monnify\Http\Client;
use PraiseDare\Monnify\Http\Controller\WebhookController;

/**
 * Main Monnify SDK class
 *
 * This class serves as the entry point for all Monnify operations.
 * It provides access to different service classes for various API endpoints.
 */
class Monnify
{
    private Config $config;
    private Client $client;
    private PaymentService $paymentService;
    private RefundService $refundService;
    private SettlementService $settlementService;
    private WebhookService $webhookService;
    private BankService $bankService;
    private CustomerService $customerService;

    /**
     * Constructor
     *
     * @param array $config Configuration array
     */
    public function __construct(array $config = [])
    {
        $this->config = new Config($config);
        $this->client = new Client($this->config);

        // Initialize services
        $this->paymentService = new PaymentService($this->client);
        $this->refundService = new RefundService($this->client);
        $this->settlementService = new SettlementService($this->client);
        $this->webhookService = new WebhookService($this->config);
        $this->bankService = new BankService($this->client);
        $this->customerService = new CustomerService($this->client);
    }

    /**
     * Get payment service
     *
     * @return PaymentService
     */
    public function payment(): PaymentService
    {
        return $this->paymentService;
    }

    /**
     * Get refund service
     *
     * @return RefundService
     */
    public function refund(): RefundService
    {
        return $this->refundService;
    }

    /**
     * Get settlement service
     *
     * @return SettlementService
     */
    public function settlement(): SettlementService
    {
        return $this->settlementService;
    }

    /**
     * Get webhook service
     *
     * @return WebhookService
     */
    public function webhook(): WebhookService
    {
        return $this->webhookService;
    }

    /**
     * Get bank service
     *
     * @return BankService
     */
    public function bank(): BankService
    {
        return $this->bankService;
    }

    /**
     * Get customer service
     *
     * @return CustomerService
     */
    public function customer(): CustomerService
    {
        return $this->customerService;
    }

    /**
     * Get configuration
     *
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * Get HTTP client
     *
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * Check if SDK is configured for live environment
     *
     * @return bool
     */
    public function isLive(): bool
    {
        return $this->config->getEnvironment() === 'live';
    }

    /**
     * Check if SDK is configured for sandbox environment
     *
     * @return bool
     */
    public function isSandbox(): bool
    {
        return $this->config->getEnvironment() === 'sandbox';
    }

    /**
     * Register webhook routes
     */
    public function registerWebhookRoutes(Router $router, string $url = '/monnify/webhook'): void
    {
        $router->post($url, WebhookController::class);
    }
}
