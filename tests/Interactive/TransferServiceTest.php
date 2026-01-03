<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Tests\Interactive;

use PHPUnit\Framework\Attributes\Test;
use PraiseDare\Monnify\Services\TransferService;
use PraiseDare\Monnify\Http\Client;
use PraiseDare\Monnify\Config\Config;
use PraiseDare\Monnify\Tests\TestCase;

class TransferServiceTest extends TestCase
{
    private TransferService $transferService;
    private Client $client;
    private Config $config;

    protected function setUp(): void
    {
        parent::setup();

        $this->client = $this->monnify->getClient();
        $this->transferService = $this->monnify->transfer();
    }

    #[Test]
    public function it_can_authorize_single_transfer()
    {
        $this->markTestSkipped('My account currently has OTP authorization disabled, and enabling it requires contacting support, so I\'m skipping for now.');
        $transferData = [
            'amount' => 200.00,
            'reference' => 'TRF_AUTH_' . time() . '_' . rand(1000, 9999),
            'narration' => 'Transfer for Auth Test',
            'destinationBankCode' => '058',
            'destinationAccountNumber' => '0123456789',
            'sourceAccountNumber' => $this->client->getConfig()->getWalletAccountNumber(),
            'currency' => 'NGN',
        ];

        fwrite(STDOUT, "\n\n--------------------------------------------------\n");
        fwrite(STDOUT, "Initiating Transfer...\n");

        $initResponse = $this->transferService->initiateSingle($transferData);

        fwrite(STDOUT, "Initiated Transfer Reference: " . $initResponse->responseBody->reference . "\n");
        fwrite(STDOUT, "Status: " . ($initResponse->responseBody->status ?? 'UNKNOWN') . "\n");
        fwrite(STDOUT, "--------------------------------------------------\n\n");

        $otp = readline('If this transfer requires OTP, please enter it here (or press Enter to skip/cancel): ');

        if (empty($otp)) {
            $this->markTestSkipped('No OTP provided, skipping authorization test.');
        }

        $authData = [
            'reference' => $initResponse->responseBody->reference,
            'authorizationCode' => $otp
        ];

        $authResponse = $this->transferService->authorizeSingle($authData);

        $this->assertTrue($authResponse['success']);
        fwrite(STDOUT, "Authorization Response: " . json_encode($authResponse) . "\n");
    }

    #[Test]
    public function it_can_resend_otp()
    {
        $this->markTestSkipped('My account currently has OTP authorization disabled, and enabling it requires contacting support, so I\'m skipping for now.');
        fwrite(STDOUT, "\n\n--------------------------------------------------\n");
        $reference = readline('Enter a Transfer Reference to resend OTP for (or press Enter to skip): ');

        if (empty($reference)) {
            $this->markTestSkipped('No reference provided, skipping resend OTP test.');
        }

        try {
            $response = $this->transferService->resendOtp($reference);
            $this->assertTrue($response['requestSuccessful']);
            fwrite(STDOUT, "Resend OTP Response: " . json_encode($response) . "\n");
        } catch (\Exception $e) {
            fwrite(STDOUT, "Error resending OTP: " . $e->getMessage() . "\n");
            throw $e;
        }
    }
}
