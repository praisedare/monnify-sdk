<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Tests;

use PraiseDare\Monnify\Monnify;
use PraiseDare\Monnify\Exceptions\MonnifyException;

/**
 * Basic test class for Monnify SDK
 */
class MonnifyTest extends TestCase
{

    public function testMonnifyInitialization(): void
    {
        $this->assertInstanceOf(Monnify::class, $this->monnify);
        $this->assertTrue($this->monnify->isSandbox());
        $this->assertFalse($this->monnify->isLive());
    }

    public function testPaymentServiceAccess(): void
    {
        $paymentService = $this->monnify->payment();
        $this->assertInstanceOf(\PraiseDare\Monnify\Services\PaymentService::class, $paymentService);
    }

    public function testRefundServiceAccess(): void
    {
        $refundService = $this->monnify->refund();
        $this->assertInstanceOf(\PraiseDare\Monnify\Services\RefundService::class, $refundService);
    }

    public function testSettlementServiceAccess(): void
    {
        $settlementService = $this->monnify->settlement();
        $this->assertInstanceOf(\PraiseDare\Monnify\Services\SettlementService::class, $settlementService);
    }

    public function testWebhookServiceAccess(): void
    {
        $webhookService = $this->monnify->webhook();
        $this->assertInstanceOf(\PraiseDare\Monnify\Services\WebhookService::class, $webhookService);
    }

    public function testBankServiceAccess(): void
    {
        $bankService = $this->monnify->bank();
        $this->assertInstanceOf(\PraiseDare\Monnify\Services\BankService::class, $bankService);
    }

    public function testCustomerServiceAccess(): void
    {
        $customerService = $this->monnify->customer();
        $this->assertInstanceOf(\PraiseDare\Monnify\Services\CustomerService::class, $customerService);
    }

    public function testConfigurationValidation(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Monnify([
            'environment' => 'invalid_environment',
        ]);
    }

    public function testMissingRequiredConfiguration(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Monnify([]);
    }
}
