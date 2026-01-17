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
use PraiseDare\Monnify\Data\Transfers\BulkTransferSummary;
use PraiseDare\Monnify\Data\Transfers\Responses\GetBulkTransferStatusResponse;
use PraiseDare\Monnify\Data\Transfers\Responses\GetSingleTransferStatusResponse;
use PraiseDare\Monnify\Data\Transfers\Responses\GetWalletBalanceResponse;
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
            'sourceAccountNumber' => '0987654321'
        ];

        $this->transferService->initiateSingle($transferData);
    }

    #[Test]
    public function it_can_initiate_async_transfer()
    {
        $transferData = new TransferInitializationData(
            amount: intdiv(rand(100, 500), 50) * 50,
            reference: 'ASYNC_TRF_' . time() . '_' . rand(1000, 9999),
            narration: 'Async test transfer',
            destinationBankCode: '058',
            destinationAccountNumber: '0987654321',
            sourceAccountNumber: $this->client->getConfig()->getWalletAccountNumber(),
            currency: 'NGN',
            async: true,
        );

        $result = $this->transferService->initiateSingle($transferData);
        $this->assertInstanceOf(InitiateSingleTransferResponse::class, $result);
        $this->assertTrue($result->requestSuccessful);
    }

    private function generateTransferTransactions(int $howMany)
    {
        $result = [];
        for ($x = 1; $x <= $howMany; ++$x) {
            $result[] = new TransferInitializationData(
                amount: intdiv(rand(100, 900), 10) * 10,
                reference: "TRXF_BULK_{$x}_" . time(),
                narration: 'First transfer',
                destinationBankCode: '033',
                destinationAccountNumber: '2240967' . str_pad((string) (129 + $x), 3, '0', STR_PAD_LEFT),
                currency: 'NGN',
                isBulkTransferItem: true,
            );
        }
        return $result;
    }

    private function createBulkTransferIntializationData(int $numberOfTransfers)
    {
        $transactions = $this->generateTransferTransactions($numberOfTransfers);
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

        return $bulkData;
    }

    #[Test]
    public function it_can_initiate_bulk_transfer()
    {
        $bulkData = $this->createBulkTransferIntializationData(15);
        $batchRef = $bulkData->batchReference;
        $transactions = $bulkData->transactionList;

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
        $amount = intdiv(rand(100, 600), 40) * 40;
        $transferResponse = $this->transferService->initiateSingle($this->getSingleTransferData(amount: $amount));

        $transferDetails = $this->transferService->getSingleTransferStatus($transferResponse->responseBody->reference);
        $this->assertInstanceOf(GetSingleTransferStatusResponse::class, $transferDetails);
        $this->assertEquals($transferResponse->responseBody->amount, $amount);
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
    #[Depends('it_can_initiate_bulk_transfer')]
    public function it_can_get_bulk_transfer_summary(
        InitiateBulkTransferResponse $bulkTransfer
    )
    {
        sleep(2); // wait for monnify to process the transaction.
        $batchReference = $bulkTransfer->responseBody->batchReference;

        $result = $this->transferService->getBulkTransferSummary($batchReference);
        $this->assertInstanceOf(GetBulkTransferStatusResponse::class, $result);
        $this->assertInstanceOf(BulkTransferSummary::class, $result->responseBody);

        $summary = $result->responseBody;
        $this->assertObjectHasProperty('title', $summary);
        $this->assertObjectHasProperty('title', $summary);
        $this->assertObjectHasProperty('totalAmount', $summary);
        $this->assertObjectHasProperty('totalFee', $summary);
        $this->assertObjectHasProperty('batchReference', $summary);
        $this->assertObjectHasProperty('totalTransactionsCount', $summary);
        $this->assertObjectHasProperty('failedCount', $summary);
        $this->assertObjectHasProperty('successfulCount', $summary);
        $this->assertObjectHasProperty('pendingCount', $summary);
        $this->assertObjectHasProperty('pendingAmount', $summary);
        $this->assertObjectHasProperty('failedAmount', $summary);
        $this->assertObjectHasProperty('successfulAmount', $summary);
        $this->assertObjectHasProperty('batchStatus', $summary);
        $this->assertObjectHasProperty('dateCreated', $summary);
    }

    #[Test]
    #[Depends('it_can_initiate_bulk_transfer')]
    public function it_can_get_bulk_transfer_transactions(
        InitiateBulkTransferResponse $bulkTransfer
    )
    {
        sleep(2); // wait for monnify to process the transaction.

        $batchReference = $bulkTransfer->responseBody->batchReference;

        $result = $this->transferService->getBulkTransferTransactions($batchReference, ['pageNo' => 0, 'pageSize' => 5]);
        $this->assertInstanceOf(PaginatedResponse::class, $result);
        $this->assertNotEmpty($result->responseBody->content, 'No transactions listed in bulk transfer');
        $this->assertInstanceOf(TransferDetails::class, $result->responseBody->content[0]);
        $result2 = $this->transferService->getBulkTransferTransactions($batchReference, ['pageNo' => 2, 'pageSize' => 5]);
    }

    #[Test]
    #[Depends('it_can_initiate_bulk_transfer')]
    public function it_can_traverse_bulk_transfer_transactions(
        InitiateBulkTransferResponse $bulkTransferResponse
    )
    {
        $filters = [
            'pageSize' => 5,
            'pageNo' => 0,
        ];
        sleep(2); // wait for monnify to process the transaction.

        $batchReference = $bulkTransferResponse->responseBody->batchReference;
        $result = $this->transferService->getBulkTransferTransactions($batchReference, $filters);
        $this->assertInstanceOf(PaginatedResponse::class, $result);
        $this->assertTrue($result->requestSuccessful);
        $this->assertIsArray($result->responseBody->content);
        $this->assertNotEmpty($result->responseBody->content, 'Received empty list of transfers within Bulk Transfer');
        $this->assertInstanceOf(TransferDetails::class, $result->responseBody->content[0]);

        $this->assertEquals($result->responseBody->pageable->pageNumber, $filters['pageNo']);

        // Page 2
        $filters['pageNo']++;
        $result2 = $this->transferService->getBulkTransferTransactions($batchReference, $filters);
        $this->assertEquals($result2->responseBody->pageable->pageNumber, $filters['pageNo']);
    }

    // TODO: Should move this to a PaginatedResponseTest as it has nothing to do with the TransferService
    #[Test]
    #[Depends('it_can_initiate_bulk_transfer')]
    public function it_can_navigate_between_pages_using_utility_methods(InitiateBulkTransferResponse $bulkTransferResponse)
    {
        sleep(2); // wait for monnify to process the transaction.

        $batchReference = $bulkTransferResponse->responseBody->batchReference;
        $result = $this->transferService->getBulkTransferTransactions($batchReference, ['pageSize' => 5]);
        $this->assertInstanceOf(PaginatedResponse::class, $result);
        $this->assertTrue($result->requestSuccessful);
        $this->assertIsArray($result->responseBody->content);
        $this->assertNotEmpty($result->responseBody->content, 'Received empty page');
        $this->assertEquals($result->responseBody->pageable->pageNumber, 0, 'Should be on page 0 without TransferFilters');
        // return;

        $result2 = $result->responseBody->goToNextPage();
        $this->assertInstanceOf(PaginatedResponse::class, $result2);
        $this->assertEquals(1, $result2->responseBody->pageable->pageNumber, 'Should be on page 1');
        $this->assertIsArray($result2->responseBody->content);
        $this->assertNotEmpty($result2->responseBody->content, 'Received empty page');
    }

    #[Test]
    public function it_can_load_large_number_of_transactions_in_a_single_page()
    {
        $numberOfTransfers = 700;
        $initiateBulkTransferResponse = $this->transferService->initiateBulk($this->createBulkTransferIntializationData($numberOfTransfers));
        $batchReference = $initiateBulkTransferResponse->responseBody->batchReference;
        // dump($batchReference);

        $result = $this->transferService->getBulkTransferTransactions($batchReference, ['pageSize' => $numberOfTransfers]);
        $this->assertEquals($numberOfTransfers, $result->responseBody->pageable->pageSize, "Page Size is not equal to $numberOfTransfers");
        // dump(count($result->responseBody->content));
    }

    #[Test]
    public function it_can_list_bulk_transfers()
    {
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
        $result = $this->transferService->listBulkTransfers();
        $this->assertInstanceOf(PaginatedResponse::class, $result);
        $this->assertTrue($result->requestSuccessful);
        $this->assertIsArray($result->responseBody->content);
        $this->assertNotEmpty($result->responseBody->content, 'Received empty list of bulk transfers');
        $this->assertInstanceOf(BulkTransferDetails::class, $result->responseBody->content[0]);
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
