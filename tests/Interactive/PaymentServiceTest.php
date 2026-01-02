<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Tests\Interactive;

use PHPUnit\Framework\Attributes\Test;
use PraiseDare\Monnify\Services\PaymentService;
use PraiseDare\Monnify\Http\Client;
use PraiseDare\Monnify\Config\Config;
use PraiseDare\Monnify\Data\Payments\PaymentData;
use PraiseDare\Monnify\Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    private PaymentService $paymentService;
    private Client $client;
    private Config $config;

    protected function setUp(): void
    {
        parent::setup();

        $this->client = $this->monnify->getClient();
        $this->paymentService = $this->monnify->payment();
    }

    #[Test]
    public function it_can_verify_successful_payment_transaction(): void
    {
        $uniqueRef = 'PAY_INTERACTIVE_' . time() . '_' . rand(1000, 9999);

        $paymentData = new PaymentData(
            amount: 200.00,
            customerName: 'Interactive User',
            customerEmail: 'interactive@example.com',
            paymentReference: $uniqueRef,
            redirectUrl: 'https://google.com',
            paymentDescription: 'Interactive Payment Test',
            currencyCode: 'NGN'
        );

        $response = $this->paymentService->initialize($paymentData);

        $this->assertTrue($response->requestSuccessful);
        $paymentUrl = $response->getPaymentUrl();
        $transactionReference = $response->getTransactionReference();

        fwrite(STDOUT, "\n\n--------------------------------------------------\n");
        fwrite(STDOUT, "PAYMENT URL: " . $paymentUrl . "\n");
        fwrite(STDOUT, "--------------------------------------------------\n\n");

        readline('This test is interactive. Please visit the URL and complete the payment (i.e. choose successful card), then press Enter...');

        $verifyResponse = $this->paymentService->getStatus($transactionReference);
        // dump($verifyResponse);

        $this->assertTrue($verifyResponse->requestSuccessful);
        $this->assertTrue($verifyResponse->isSuccessful(), 'Payment not successful');
        $this->assertEquals('PAID', $verifyResponse->getPaymentStatus(), 'Payment status does not equals `PAID`');
    }

    #[Test]
    public function it_can_verify_failed_payment_transaction(): void
    {
        $this->markTestSkipped('Making a test payment fail does not work reliably on Monnify');
        $uniqueRef = 'PAY_INTERACTIVE_FAIL_' . time() . '_' . rand(1000, 9999);

        $paymentData = new PaymentData(
            amount: 200.00,
            customerName: 'Interactive Fail User',
            customerEmail: 'interactive_fail@example.com',
            paymentReference: $uniqueRef,
            redirectUrl: 'https://google.com',
            paymentDescription: 'Interactive Failed Payment Test',
            currencyCode: 'NGN'
        );

        $response = $this->paymentService->initialize($paymentData);

        $this->assertTrue($response->requestSuccessful);
        $paymentUrl = $response->getPaymentUrl();
        $transactionReference = $response->getTransactionReference();

        fwrite(STDOUT, "\n\n--------------------------------------------------\n");
        fwrite(STDOUT, "PAYMENT URL: " . $paymentUrl . "\n");
        fwrite(STDOUT, "--------------------------------------------------\n\n");

        readline('This test is interactive. Please visit the URL and CANCEL or FAIL the payment (e.g. choose card and cancel), then press Enter...');

        $verifyResponse = $this->paymentService->getStatus($transactionReference);
        // dump($verifyResponse);

        $this->assertTrue($verifyResponse->requestSuccessful);
        // Depending on how they "fail" it, it might be FAILED, CANCELLED or PENDING (if abandoned)
        // For this test we'll check if it's NOT successful (PAID)
        $this->assertNotEquals('PAID', $verifyResponse->getPaymentStatus(), 'Payment status should not be PAID');
        $this->assertTrue($verifyResponse->isFailed(), 'Payment should not be successful');
    }
}
