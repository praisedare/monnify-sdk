<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Tests;

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use PraiseDare\Monnify\Services\TransferService;
use PraiseDare\Monnify\Http\Client;
use PraiseDare\Monnify\Config\Config;
use PraiseDare\Monnify\Data\Common\PaginatedResponse;
use PraiseDare\Monnify\Data\Transfers\BulkTransferDetails;
use PraiseDare\Monnify\Data\Transfers\BulkTransferInitializationData;
use PraiseDare\Monnify\Data\Transfers\Responses\GetSingleTransferStatusResponse;
use PraiseDare\Monnify\Data\Transfers\Responses\GetWalletBalanceResponse;
use PraiseDare\Monnify\Data\Transfers\Responses\InitiateAsyncTransferResponse;
use PraiseDare\Monnify\Data\Transfers\Responses\InitiateBulkTransferResponse;
use PraiseDare\Monnify\Data\Transfers\Responses\InitiateSingleTransferResponse;
use PraiseDare\Monnify\Data\Transfers\TransferDetails;
use PraiseDare\Monnify\Data\Transfers\TransferInitializationData;
use PraiseDare\Monnify\Data\Transfers\TransferFilterData;
use PraiseDare\Monnify\Exceptions\MonnifyException;

class TransferServiceTest extends TestCase
{
    private TransferService $transferService;
    private Client $client;
    private Config $config;

    protected function setUp(): void
    {
        parent::setup();

        $this->config = $this->monnify->getConfig();
        $this->client = $this->monnify->getClient();
        $this->transferService = $this->monnify->transfer();
    }

    public function accountNumbers()
    {
        return [
            [
                'bankCode' => '033',
                'accountNumber' => '2240967130',
            ],
            [
                'bankCode' => '305',
                'accountNumber' => '6468343228',
            ],
            [
                'bankCode' => '044',
                'accountNumber' => '3840012889',
            ],
        ];
    }

    public function generateRandomAccount()
    {
        return [
            'destinationBankCode' => str_pad((string) rand(0, 999), 3, '0', STR_PAD_LEFT),
            'destinationAccountNumber' => str_pad((string) rand(0, 9_000), 5, '0', STR_PAD_LEFT) . str_pad((string) rand(0, 9_000), 5, '0', STR_PAD_LEFT),
        ];
    }

    public function getSingleTransferData(float $amount = 200, string $bankCode = '033', string $accountNumber = '2240967130')
    {
        return new TransferInitializationData(
            amount: $amount,
            reference: 'TRF_TEST_' . time() . '_' . rand(1000, 9999),
            narration: 'Test transfer',
            destinationBankCode: $bankCode,
            destinationAccountNumber: $accountNumber,
            destinationAccountName: 'Praise Dare',
            sourceAccountNumber: $this->client->getConfig()->getWalletAccountNumber(), // Wallet Account
            currency: 'NGN',
        );
    }

    #[Test]
    public function it_can_initiate_single_transfer_with_valid_data()
    {
        $transferData = $this->getSingleTransferData(amount: intdiv(rand(100, 800), 50) * 50);

        $result = $this->transferService->initiateSingle($transferData);
        $this->assertInstanceOf(InitiateSingleTransferResponse::class, $result);
        $this->assertTrue($result->requestSuccessful);
        $this->assertEquals($transferData->reference, $result->responseBody->reference);
    }

    #[Test]
    public function it_throws_exception_for_missing_required_field()
    {
        $this->expectException(MonnifyException::class);
        $this->expectExceptionMessage("amount");

        $transferData = new TransferInitializationData(
            amount: 0,
            reference: 'TRF_TEST_001',
            narration: 'Test transfer',
            destinationBankCode: '044',
            destinationAccountNumber: '1234567890',
            destinationAccountName: 'Test User',
            sourceAccountNumber: '0987654321'
        );

        $this->transferService->initiateSingle($transferData);
    }

    #[Test]
    public function it_throws_exception_for_invalid_amount()
    {
        $this->expectException(MonnifyException::class);
        $this->expectExceptionMessage('Amount must be greater than 0');

        $transferData = [
            'amount' => 0,
            'reference' => 'TRF_TEST_001',
            'narration' => 'Test transfer',
            'destinationBankCode' => '044',
            'destinationAccountNumber' => '1234567890',
            'destinationAccountName' => 'Test User',
            'sourceAccountNumber' => '0987654321'
        ];

        $this->transferService->initiateSingle($transferData);
    }

    #[Test]
    public function it_can_initiate_async_transfer()
    {
        $transferData = [
            'amount' => 200.00,
            'reference' => 'ASYNC_TRF_' . time() . '_' . rand(1000, 9999),
            'narration' => 'Async test transfer',
            'destinationBankCode' => '058',
            'destinationAccountNumber' => '0987654321',
            'destinationAccountName' => 'Async User',
            'sourceAccountNumber' => $this->client->getConfig()->getWalletAccountNumber(),
            'currency' => 'NGN',
            'async' => true,
        ];

        $result = $this->transferService->initiateSingle($transferData);
        // dump($result);
        $this->isInstanceOf(InitiateAsyncTransferResponse::class);
        $this->assertTrue($result->requestSuccessful);
    }

    #[Test]
    public function it_can_initiate_bulk_transfer()
    {
        $transactions = array_map(fn($x) => new TransferInitializationData(
            amount: [$m = rand(100, 200), $m -= $m % 10][1],
            reference: "TRXF_BULK_{$x}_" . time(),
            narration: 'First transfer',
            destinationAccountName: 'User One',
            destinationBankCode: str_pad((string) rand(0, 999), 3, '0', STR_PAD_LEFT),
            destinationAccountNumber: str_pad((string) rand(0, 9_000), 5, '0', STR_PAD_LEFT) . str_pad((string) rand(0, 9_000), 5, '0', STR_PAD_LEFT),
            currency: 'NGN',
            isBulkTransferItem: true,
        ), range(1, 5));
        $bulkData = new BulkTransferInitializationData(
            title: 'Test Bulk Transfer',
            batchReference: $batchRef = 'XBATCH_' . time() . '_' . rand(1000, 9999),
            narration: 'Bulk test transfer',
            sourceAccountNumber: $this->client->getConfig()->getWalletAccountNumber(),
            currency: 'NGN',
            transactionList: $transactions,
            onValidationFailure: 'CONTINUE',
            notificationInterval: 25,
        );

        $result = $this->transferService->initiateBulk($bulkData);
        $this->assertInstanceOf(InitiateBulkTransferResponse::class, $result);
        $this->assertTrue($result->requestSuccessful);
        $this->assertEquals($result->responseBody->batchReference, $batchRef, 'Batch references do not match');
        $this->assertEquals($result->responseBody->totalAmount, array_sum(array_column($transactions, 'amount')), 'Total amount of bulk transfer response does not match total amount transferred');
        $this->assertEquals($result->responseBody->totalTransactions, count($transactions));
        return $result;
    }

    #[Test]
    public function it_can_initiate_bulk_transfer_with_array()
    {
        $transactions = array_map(fn($x) => [
            'amount' => [$m = rand(100, 200), $m -= $m % 10][1],
            'reference' => "TRXF_BULK_{$x}_" . time(),
            'narration' => 'First transfer',
            'destinationAccountName' => 'User One',
            'destinationBankCode' => str_pad((string) rand(0, 999), 3, '0', STR_PAD_LEFT),
            'destinationAccountNumber' => str_pad((string) rand(0, 9_000), 5, '0', STR_PAD_LEFT) . str_pad((string) rand(0, 9_000), 5, '0', STR_PAD_LEFT),
            'currency' => 'NGN',
            'isBulkTransferItem' => true,
        ], range(1, 5));
        $bulkData = [
            'title' => 'Test Bulk Transfer',
            'batchReference' => $batchRef = 'XBATCH_' . time() . '_' . rand(1000, 9999),
            'narration' => 'Bulk test transfer',
            'sourceAccountNumber' => $this->client->getConfig()->getWalletAccountNumber(),
            'currency' => 'NGN',
            'transactionList' => $transactions,
            'onValidationFailure' => 'CONTINUE',
            'notificationInterval' => 25,
        ];

        $result = $this->transferService->initiateBulk($bulkData);
        $this->assertInstanceOf(InitiateBulkTransferResponse::class, $result);
        $this->assertTrue($result->requestSuccessful);
        $this->assertEquals($result->responseBody->batchReference, $batchRef, 'Batch references do not match');
        $this->assertEquals($result->responseBody->totalAmount, array_sum(array_column($transactions, 'amount')), 'Total amount of bulk transfer response does not match total amount transferred');
        $this->assertEquals($result->responseBody->totalTransactions, count($transactions));
        return $result;
    }

    #[Test]
    public function it_throws_exception_for_bulk_transfer_with_empty_transactions()
    {
        $this->expectException(MonnifyException::class);
        $this->expectExceptionMessage('Transaction list cannot be null or empty');

        $bulkData = [
            'title' => 'Test Bulk Transfer',
            'batchReference' => 'BATCH_001',
            'narration' => 'Bulk test transfer',
            'sourceAccountNumber' => $this->monnify->getConfig()->getWalletAccountNumber(),
            'currency' => 'NGN',
            'transactionList' => []
        ];

        $this->transferService->initiateBulk($bulkData);
    }

    #[Test]
    public function it_throws_exception_for_authorize_single_transfer_with_missing_reference()
    {
        $this->expectException(MonnifyException::class);
        $this->expectExceptionMessage('Reference is required');

        $authData = [
            'reference' => '',
            'authorizationCode' => '123456'
        ];

        $this->transferService->authorizeSingle($authData);
    }

    public function testAuthorizeBulkTransfer()
    {
        $this->markTestSkipped('Not implemented, and not of pressing importance since programmatic use will require that authorizations for transfers be disabled.');
    }

    #[Test]
    public function it_throws_exception_for_resend_otp_with_empty_reference()
    {
        $this->expectException(MonnifyException::class);
        $this->expectExceptionMessage('Reference is required');

        $this->transferService->resendOtp('');
    }

    #[Test]
    public function it_can_get_single_transfer_status()
    {
        // We need a valid reference. In a real test we might create one first.
        // For now, we'll try with a dummy one and expect a "not found" or similar error,
        // which proves we hit the API.
        $transferResponse = $this->transferService->initiateSingle($this->getSingleTransferData(amount: 300));

        $transferDetails = $this->transferService->getSingleTransferStatus($transferResponse->responseBody->reference);
        $this->assertInstanceOf(GetSingleTransferStatusResponse::class, $transferDetails);
        $this->assertEquals($transferResponse->responseBody->amount, 300);
    }

    #[Test]
    public function it_can_list_single_transfers()
    {
        $filters = [
            'pageSize' => $size = rand(5, 10),
            'pageNo' => $page = 1,
        ];

        $result = $this->transferService->listSingleTransfers(TransferFilterData::fromArray($filters));
        $this->assertInstanceOf(PaginatedResponse::class, $result);
        $this->assertTrue($result->requestSuccessful);
        $this->assertIsArray($result->responseBody->content);
        $this->assertCount($size, $result->responseBody->content);
        $this->assertInstanceOf(TransferDetails::class, $result->responseBody->content[0]);
    }

    #[Test]
    public function it_can_list_single_transfers_without_filters()
    {
        $result = $this->transferService->listSingleTransfers();
        $this->assertInstanceOf(PaginatedResponse::class, $result);
        $this->assertTrue($result->requestSuccessful);
        $this->assertIsArray($result->responseBody->content);
        $this->assertInstanceOf(TransferDetails::class, $result->responseBody->content[0]);
    }

    #[Test]
    public function it_can_get_bulk_transfer_status()
    {
        $this->markTestSkipped('Monnify has removed the API for getting details of a single batch transfer');
    }

    #[Test]
    #[Depends('it_can_initiate_bulk_transfer')]
    public function it_can_get_bulk_transfer_transactions(InitiateBulkTransferResponse $bulkTransfer)
    {
        $batchReference = $bulkTransfer->responseBody->batchReference;

        $result = $this->transferService->getBulkTransferTransactions($batchReference);
        $this->assertInstanceOf(PaginatedResponse::class, $result);
        $this->assertNotEmpty($result->responseBody->content, 'No transactions listed in bulk transfer');
        $this->assertInstanceOf(TransferDetails::class, $result->responseBody->content[0]);
    }

    #[Test]
    public function it_can_list_bulk_transfers()
    {
        $this->markTestSkipped('Monnify has removed the endpoint for listing bulk transfers');

        $filters = [
            'pageSize' => $size = rand(5, 10),
            'pageNo' => $page = 1,
        ];

        $result = $this->transferService->listBulkTransfers(TransferFilterData::fromArray($filters));
        $this->assertInstanceOf(PaginatedResponse::class, $result);
        $this->assertTrue($result->requestSuccessful);
        $this->assertIsArray($result->responseBody->content);
        $this->assertNotEmpty($result->responseBody->content, 'Received empty list of bulk transfers');
        $this->assertInstanceOf(BulkTransferDetails::class, $result->responseBody->content[0]);
    }

    #[Test]
    public function it_can_list_bulk_transfers_without_filters()
    {
        $this->markTestSkipped('Monnify has removed the endpoint for listing bulk transfers');

        // $result = $this->transferService->listSingleTransfers();
        // $this->assertInstanceOf(ListSingleTransfersResponse::class, $result);
        // $this->assertTrue($result->requestSuccessful);
        // $this->assertIsArray($result->responseBody->content);
    }

    #[Test]
    public function it_can_search_disbursements()
    {
        $this->markTestSkipped('API endpoint for this action is unreliable and unstable.');

        $filters = [
            'page' => 0,
            'size' => 20,
            'from' => date('U', strtotime('-30 days')),
            'to' => date('U'),
        ];

        $result = $this->transferService->searchDisbursements($filters);
        $this->assertIsArray($result);
        $this->assertTrue($result['requestSuccessful']);
    }

    #[Test]
    public function it_can_get_wallet_balance()
    {
        $result = $this->transferService->getWalletBalance();
        $this->assertTrue($result->requestSuccessful, 'Wallet Balance request unsuccessful ' . $result->responseMessage);
        $this->assertInstanceOf(GetWalletBalanceResponse::class, $result);
        $this->assertObjectHasProperty('availableBalance', $result->responseBody);
        $this->assertObjectHasProperty('ledgerBalance', $result->responseBody);
        $this->assertIsNumeric($result->responseBody->availableBalance);
        $this->assertIsNumeric($result->responseBody->ledgerBalance);
    }

}