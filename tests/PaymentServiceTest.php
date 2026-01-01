<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Tests;

use PHPUnit\Framework\Attributes\Test;
use PraiseDare\Monnify\Services\PaymentService;
use PraiseDare\Monnify\Http\Client;
use PraiseDare\Monnify\Config\Config;
use PraiseDare\Monnify\Data\Payments\PaymentData;
use PraiseDare\Monnify\Data\Payments\PaymentFilterData;
use PraiseDare\Monnify\Data\Payments\Responses\InitiatePaymentResponse;
use PraiseDare\Monnify\Data\Payments\Responses\VerifyPaymentResponse;
use PraiseDare\Monnify\Data\Payments\Responses\GetAllPaymentsResponse;
use PraiseDare\Monnify\Exceptions\MonnifyException;

class PaymentServiceTest extends TestCase
{
    private PaymentService $paymentService;
    private Client $client;
    private Config $config;
    private static array $createdPayments = [];

    protected function setUp(): void
    {
        parent::setup();

        $this->client = $this->monnify->getClient();
        $this->paymentService = $this->monnify->payment();
    }

    public static function tearDownAfterClass(): void
    {
        // Clean up any test data if needed
        // Note: Monnify doesn't typically allow deletion of transactions
        // but we can log the created payment references for manual cleanup
        if (!empty(self::$createdPayments)) {
            self::$logger->info("Created test payments (for manual verification/cleanup):\n", ['payments' => self::$createdPayments]);
        }
    }

    #[Test]
    public function it_can_initialize_payment_with_valid_data(): void
    {
        $uniqueRef = 'PAY_TEST_' . time() . '_' . rand(1000, 9999);

        $paymentData = new PaymentData(
            amount: 1000.00,
            customerName: 'John Doe',
            customerEmail: 'john.doe@example.com',
            paymentReference: $uniqueRef,
            redirectUrl: 'https://example.com/callback',
            paymentDescription: 'Test payment for SDK',
            currencyCode: 'NGN'
        );

        $response = $this->paymentService->initialize($paymentData);

        $this->assertInstanceOf(InitiatePaymentResponse::class, $response);
        $this->assertTrue($response->requestSuccessful);
        $this->assertNotEmpty($response->getTransactionReference());
        $this->assertEquals($uniqueRef, $response->getPaymentReference());
        $this->assertStringContainsString('monnify.com', $response->getPaymentUrl());

        // Store for cleanup/verification
        self::$createdPayments[] = [
            'paymentReference' => $response->getPaymentReference(),
            'transactionReference' => $response->getTransactionReference()
        ];

        // Test helper methods
        $this->assertEquals($response->getPaymentUrl(), $this->paymentService->getPaymentUrl($response));
        $this->assertEquals($response->getTransactionReference(), $this->paymentService->getTransactionReference($response));
    }

    #[Test]
    public function it_adds_contract_code_when_not_provided(): void
    {
        $uniqueRef = 'PAY_NO_CONTRACT_' . time() . '_' . rand(1000, 9999);

        $paymentData = new PaymentData(
            amount: 500.00,
            customerName: 'Jane Smith',
            customerEmail: 'jane.smith@example.com',
            paymentReference: $uniqueRef,
            redirectUrl: 'https://example.com/callback'
            // No contract code provided - should use default from config
        );

        $response = $this->paymentService->initialize($paymentData);

        $this->assertInstanceOf(InitiatePaymentResponse::class, $response);
        $this->assertTrue($response->requestSuccessful);
        $this->assertEquals($uniqueRef, $response->getPaymentReference());

        // Store for cleanup/verification
        self::$createdPayments[] = [
            'paymentReference' => $response->getPaymentReference(),
            'transactionReference' => $response->getTransactionReference()
        ];
    }

    #[Test]
    public function it_validates_amount_must_be_positive(): void
    {
        $paymentData = new PaymentData(
            amount: -100.00,
            customerName: 'John Doe',
            customerEmail: 'john@example.com',
            paymentReference: 'PAY_NEGATIVE_' . time(),
            redirectUrl: 'https://example.com/callback'
        );

        $this->expectException(MonnifyException::class);
        $this->expectExceptionMessage('Amount must be a positive number');

        $this->paymentService->initialize($paymentData);
    }

    #[Test]
    public function it_validates_email_format(): void
    {
        $paymentData = new PaymentData(
            amount: 1000.00,
            customerName: 'John Doe',
            customerEmail: 'invalid-email',
            paymentReference: 'PAY_INVALID_EMAIL_' . time(),
            redirectUrl: 'https://example.com/callback'
        );

        $this->expectException(MonnifyException::class);
        $this->expectExceptionMessage('Invalid email address');

        $this->paymentService->initialize($paymentData);
    }

    #[Test]
    public function it_validates_payment_reference_length(): void
    {
        $paymentData = new PaymentData(
            amount: 1000.00,
            customerName: 'John Doe',
            customerEmail: 'john@example.com',
            paymentReference: str_repeat('A', 101), // 101 characters
            redirectUrl: 'https://example.com/callback'
        );

        $this->expectException(MonnifyException::class);
        $this->expectExceptionMessage('Payment reference must not exceed 100 characters');

        $this->paymentService->initialize($paymentData);
    }

    #[Test]
    public function it_validates_redirect_url_format(): void
    {
        $paymentData = new PaymentData(
            amount: 1000.00,
            customerName: 'John Doe',
            customerEmail: 'john@example.com',
            paymentReference: 'PAY_INVALID_URL_' . time(),
            redirectUrl: 'invalid-url'
        );

        $this->expectException(MonnifyException::class);
        $this->expectExceptionMessage('Invalid redirect URL');

        $this->paymentService->initialize($paymentData);
    }

    #[Test]
    public function it_can_verify_payment_with_valid_reference(): void
    {
        // First create a payment to verify
        $uniqueRef = 'PAY_VERIFY_' . time() . '_' . rand(1000, 9999);

        $paymentData = new PaymentData(
            amount: 750.00,
            customerName: 'Verify Test User',
            customerEmail: 'verify@example.com',
            paymentReference: $uniqueRef,
            redirectUrl: 'https://example.com/callback',
            paymentDescription: 'Payment for verification test'
        );

        $initResponse = $this->paymentService->initialize($paymentData);
        $transactionReference = $initResponse->getTransactionReference();

        // Store for cleanup
        self::$createdPayments[] = $p = [
            'paymentReference' => $initResponse->getPaymentReference(),
            'transactionReference' => $transactionReference
        ];
        $this->logger->debug('payment refs', $p);

        // In sandbox environment, transactions might not be immediately available
        // So we'll test with a slight delay and handle potential 404s gracefully
        sleep(2); // Wait 2 seconds for transaction to be indexed

        $response = $this->paymentService->getStatus($initResponse->getTransactionReference());

        dump($initResponse->getPaymentUrl());
        $this->assertInstanceOf(VerifyPaymentResponse::class, $response);
        $this->assertTrue($response->requestSuccessful);
        $this->assertEquals($transactionReference, $response->getTransactionReference());
        $this->assertEquals($uniqueRef, $response->getPaymentReference());
        $this->assertIsFloat($response->getAmountPaid());
        $this->assertIsFloat($response->getTotalPayable());
        $this->assertIsString($response->getPaymentStatus());

        // Test status helper methods
        $this->assertIsBool($response->isSuccessful());
        $this->assertIsBool($response->isPending());
        $this->assertIsBool($response->isFailed());
        $this->assertIsBool($response->isExpired());
    }



    #[Test]
    public function it_throws_exception_for_empty_transaction_reference(): void
    {
        $this->expectException(MonnifyException::class);
        $this->expectExceptionMessage('Transaction reference is required');

        $this->paymentService->getStatus('');
    }

    #[Test]
    public function it_can_get_status_using_verify(): void
    {
        // Create a payment first
        $uniqueRef = 'PAY_STATUS_' . time() . '_' . rand(1000, 9999);

        $paymentData = new PaymentData(
            amount: 300.00,
            customerName: 'Status Test User',
            customerEmail: 'status@example.com',
            paymentReference: $uniqueRef,
            redirectUrl: 'https://example.com/callback'
        );

        $initResponse = $this->paymentService->initialize($paymentData);
        $transactionReference = $initResponse->getTransactionReference();

        // Store for cleanup
        self::$createdPayments[] = [
            'paymentReference' => $initResponse->getPaymentReference(),
            'transactionReference' => $transactionReference
        ];

        // Wait for transaction to be indexed
        sleep(2);

        try {
            // Test getStatus method (should work same as verify)
            $response = $this->paymentService->getStatus($transactionReference);

            $this->assertInstanceOf(VerifyPaymentResponse::class, $response);
            $this->assertTrue($response->requestSuccessful);
            $this->assertEquals($transactionReference, $response->getTransactionReference());

            // Test helper methods work
            $status = $this->paymentService->getPaymentStatus($response);
            $this->assertIsString($status);
            $this->assertContains($status, ['PENDING', 'PAID', 'FAILED', 'EXPIRED']);
        } catch (MonnifyException $e) {
            dump('got error in it_can_get_status_using_verify: ' . $e->getMessage());
            $this->logger->error('it_can_get_status_using_verify', ['exception' => $e]);
            // var_dump($e);
            $this->assertStringContainsString('Could not find transaction', $e->getMessage());
            $this->markTestSkipped('Transaction not immediately available in sandbox - this is expected behavior');
        }
    }

    #[Test]
    public function it_can_get_details_using_verify(): void
    {
        // Create a payment first
        $uniqueRef = 'PAY_DETAILS_' . time() . '_' . rand(1000, 9999);

        $paymentData = new PaymentData(
            amount: 450.00,
            customerName: 'Details Test User',
            customerEmail: 'details@example.com',
            paymentReference: $uniqueRef,
            redirectUrl: 'https://example.com/callback'
        );

        $initResponse = $this->paymentService->initialize($paymentData);
        $transactionReference = $initResponse->getTransactionReference();

        // Store for cleanup
        self::$createdPayments[] = [
            'paymentReference' => $initResponse->getPaymentReference(),
            'transactionReference' => $transactionReference
        ];

        // Wait for transaction to be indexed
        sleep(2);

        try {
            // Test getDetails method (should work same as verify)
            $response = $this->paymentService->getDetails($transactionReference);

            $this->assertInstanceOf(VerifyPaymentResponse::class, $response);
            $this->assertTrue($response->requestSuccessful);
            $this->assertEquals($transactionReference, $response->getTransactionReference());

            // Test amount helper methods
            $amountPaid = $this->paymentService->getAmountPaid($response);
            $totalPayable = $this->paymentService->getTotalPayable($response);
            $this->assertIsFloat($amountPaid);
            $this->assertIsFloat($totalPayable);
        } catch (MonnifyException $e) {
            $this->logger->error('it_can_get_details_using_verify', ['exception' => $e]);
            // var_dump($e);
            $this->assertStringContainsString('Could not find transaction', $e->getMessage());
            $this->markTestSkipped('Transaction not immediately available in sandbox - this is expected behavior');
        }
    }

    #[Test]
    public function it_can_get_all_payments_without_filters(): void
    {
        $response = $this->paymentService->getAll();

        $this->assertInstanceOf(GetAllPaymentsResponse::class, $response);
        $this->assertTrue($response->requestSuccessful);
        $this->assertIsInt($response->getTotalElements());
        $this->assertIsInt($response->getTotalPages());
        $this->assertIsBool($response->isFirst());
        $this->assertIsBool($response->isLast());
        $this->assertIsBool($response->isEmpty());
        $this->assertIsInt($response->getPageNumber());
        $this->assertIsInt($response->getPageSize());
        $this->assertIsInt($response->getNumberOfElements());

        $transactions = $response->getTransactions();
        $this->assertIsArray($transactions);

        // If there are transactions, test their structure
        if (!empty($transactions)) {
            $firstTransaction = $transactions[0];
            // These fields may be null in some responses, so we test accordingly
            $this->assertTrue(is_string($firstTransaction->transactionReference) || is_null($firstTransaction->transactionReference));
            $this->assertTrue(is_string($firstTransaction->paymentReference) || is_null($firstTransaction->paymentReference));
            $this->assertTrue(is_float($firstTransaction->amount) || is_null($firstTransaction->amount));
            $this->assertTrue(is_string($firstTransaction->paymentStatus) || is_null($firstTransaction->paymentStatus));
            $this->assertIsBool($firstTransaction->isSuccessful());
            $this->assertIsBool($firstTransaction->isPending());
            $this->assertIsBool($firstTransaction->isFailed());
            $this->assertIsBool($firstTransaction->isExpired());
        }
    }

    #[Test]
    public function it_can_get_all_payments_with_filters(): void
    {
        $testResponse = function(int $page, int $size) {
            $filters = new PaymentFilterData(
                page: $page,
                size: $size,
                fromDate: date('Y-m-d', strtotime('-30 days')),
                toDate: date('Y-m-d'),
                status: 'PENDING'
            );

            $response = $this->paymentService->getAll($filters);

            $this->assertInstanceOf(GetAllPaymentsResponse::class, $response);
            $this->assertTrue($response->requestSuccessful);
            $this->assertEquals($size, $response->getPageSize());
            $this->assertCount($size, $response->getTransactions(), "There should only be $size transactions per page");
            $this->assertEquals($page, $response->getPageNumber());

            // Verify filter serialization works
            $queryParams = $filters->toQueryParams();
            $this->assertArrayHasKey('page', $queryParams);
            $this->assertArrayHasKey('size', $queryParams);
            $this->assertArrayHasKey('fromDate', $queryParams);
            $this->assertArrayHasKey('toDate', $queryParams);
            $this->assertArrayHasKey('status', $queryParams);
            $this->assertEquals($page, $queryParams['page']);
            $this->assertEquals($size, $queryParams['size']);
            $this->assertEquals('PENDING', $queryParams['status']);

            return $response;
        };

        $oldResponse = $testResponse(page: 5, size: 5);
        // $newResponse = $testResponse(page: 1, size: 5);
        // Try again with different params
        // Total pages should differ when page size changes
        $newResponse = $testResponse(page: 1, size: 10);
        $this->assertLessThan($newResponse->getPageSize(), $oldResponse->getPageSize());
    }

    #[Test]
    public function it_can_test_dto_serialization_with_real_data(): void
    {
        // Test PaymentData serialization
        $paymentData = new PaymentData(
            amount: 1500.50,
            customerName: 'DTO Test User',
            customerEmail: 'dto@example.com',
            paymentReference: 'PAY_DTO_' . time(),
            redirectUrl: 'https://example.com/return',
            paymentDescription: 'DTO serialization test',
            currencyCode: 'NGN',
            paymentMethods: ['CARD', 'ACCOUNT_TRANSFER'],
            customerPhone: '+2348123456789',
            metadata: ['test' => 'value', 'environment' => 'testing']
        );

        $array = $paymentData->toArray();
        $this->assertEquals(1500.50, $array['amount']);
        $this->assertEquals('DTO Test User', $array['customerName']);
        $this->assertEquals('dto@example.com', $array['customerEmail']);
        $this->assertEquals(['CARD', 'ACCOUNT_TRANSFER'], $array['paymentMethods']);
        $this->assertEquals('+2348123456789', $array['customerPhone']);
        $this->assertEquals(['test' => 'value', 'environment' => 'testing'], $array['metadata']);

        // Test deserialization
        $reconstructed = PaymentData::fromArray($array);
        $this->assertEquals($paymentData->amount, $reconstructed->amount);
        $this->assertEquals($paymentData->customerName, $reconstructed->customerName);
        $this->assertEquals($paymentData->customerEmail, $reconstructed->customerEmail);
        $this->assertEquals($paymentData->metadata, $reconstructed->metadata);
    }

    #[Test]
    public function it_can_test_filter_data_serialization(): void
    {
        // Test with all parameters
        $filterData = new PaymentFilterData(
            page: 2,
            size: 50,
            fromDate: '2023-01-01',
            toDate: '2023-12-31',
            status: 'PAID'
        );

        $queryParams = $filterData->toQueryParams();
        $this->assertEquals('2', $queryParams['page']);
        $this->assertEquals('50', $queryParams['size']);
        $this->assertEquals('2023-01-01', $queryParams['fromDate']);
        $this->assertEquals('2023-12-31', $queryParams['toDate']);
        $this->assertEquals('PAID', $queryParams['status']);

        $array = $filterData->toArray();
        $this->assertEquals(2, $array['page']);
        $this->assertEquals(50, $array['size']);
        $this->assertEquals('2023-01-01', $array['fromDate']);
        $this->assertEquals('2023-12-31', $array['toDate']);
        $this->assertEquals('PAID', $array['status']);

        // Test with null values
        $emptyFilter = new PaymentFilterData();
        $emptyQueryParams = $emptyFilter->toQueryParams();
        $this->assertEmpty($emptyQueryParams);

        $emptyArray = $emptyFilter->toArray();
        $this->assertNull($emptyArray['page']);
        $this->assertNull($emptyArray['size']);
        $this->assertNull($emptyArray['fromDate']);
        $this->assertNull($emptyArray['toDate']);
        $this->assertNull($emptyArray['status']);
    }

    #[Test]
    public function it_can_test_real_api_response_serialization(): void
    {
        // Create a real payment to test response serialization
        $uniqueRef = 'PAY_SERIALIZATION_' . time() . '_' . rand(1000, 9999);

        $paymentData = new PaymentData(
            amount: 850.00,
            customerName: 'Serialization Test User',
            customerEmail: 'serialization@example.com',
            paymentReference: $uniqueRef,
            redirectUrl: 'https://example.com/callback',
            paymentDescription: 'Response serialization test'
        );

        $response = $this->paymentService->initialize($paymentData);

        // Store for cleanup
        self::$createdPayments[] = [
            'paymentReference' => $response->getPaymentReference(),
            'transactionReference' => $response->getTransactionReference()
        ];

        // Test response serialization
        $serialized = $response->toArray();
        $this->assertIsArray($serialized);
        $this->assertArrayHasKey('requestSuccessful', $serialized);
        $this->assertArrayHasKey('responseMessage', $serialized);
        $this->assertArrayHasKey('responseCode', $serialized);
        $this->assertArrayHasKey('responseBody', $serialized);

        $responseBody = $serialized['responseBody'];
        $this->assertArrayHasKey('transactionReference', $responseBody);
        $this->assertArrayHasKey('paymentReference', $responseBody);
        $this->assertArrayHasKey('checkoutUrl', $responseBody);

        // Test that we can recreate the response from the serialized data
        $recreated = InitiatePaymentResponse::fromArray($serialized);
        $this->assertEquals($response->requestSuccessful, $recreated->requestSuccessful);
        $this->assertEquals($response->getTransactionReference(), $recreated->getTransactionReference());
        $this->assertEquals($response->getPaymentReference(), $recreated->getPaymentReference());
        $this->assertEquals($response->getPaymentUrl(), $recreated->getPaymentUrl());
    }

    #[Test]
    public function it_handles_payment_data_serialization(): void
    {
        $paymentData = new PaymentData(
            amount: 1500.50,
            customerName: 'Jane Smith',
            customerEmail: 'jane@example.com',
            paymentReference: 'PAY_REF_002',
            redirectUrl: 'https://example.com/return',
            paymentDescription: 'Custom payment description',
            currencyCode: 'USD',
            contractCode: 'CUSTOM_CONTRACT',
            paymentMethods: ['CARD'],
            customerPhone: '+2348123456789',
            metadata: ['key' => 'value']
        );

        $array = $paymentData->toArray();

        $this->assertEquals(1500.50, $array['amount']);
        $this->assertEquals('Jane Smith', $array['customerName']);
        $this->assertEquals('jane@example.com', $array['customerEmail']);
        $this->assertEquals('PAY_REF_002', $array['paymentReference']);
        $this->assertEquals('https://example.com/return', $array['redirectUrl']);
        $this->assertEquals('Custom payment description', $array['paymentDescription']);
        $this->assertEquals('USD', $array['currencyCode']);
        $this->assertEquals('CUSTOM_CONTRACT', $array['contractCode']);
        $this->assertEquals(['CARD'], $array['paymentMethods']);
        $this->assertEquals('+2348123456789', $array['customerPhone']);
        $this->assertEquals(['key' => 'value'], $array['metadata']);

        // Test deserialization
        $reconstructed = PaymentData::fromArray($array);
        $this->assertEquals($paymentData->amount, $reconstructed->amount);
        $this->assertEquals($paymentData->customerName, $reconstructed->customerName);
        $this->assertEquals($paymentData->customerEmail, $reconstructed->customerEmail);
    }

    #[Test]
    public function it_handles_payment_filter_data_serialization(): void
    {
        $filterData = new PaymentFilterData(
            page: 2,
            size: 50,
            fromDate: '2023-01-01',
            toDate: '2023-12-31',
            status: 'PAID'
        );

        $queryParams = $filterData->toQueryParams();

        $this->assertEquals('2', $queryParams['page']);
        $this->assertEquals('50', $queryParams['size']);
        $this->assertEquals('2023-01-01', $queryParams['fromDate']);
        $this->assertEquals('2023-12-31', $queryParams['toDate']);
        $this->assertEquals('PAID', $queryParams['status']);

        // Test with null values
        $emptyFilter = new PaymentFilterData();
        $emptyQueryParams = $emptyFilter->toQueryParams();
        $this->assertEmpty($emptyQueryParams);
    }

    #[Test]
    public function it_can_verify_existing_transaction_from_getall_results(): void
    {
        // Get existing transactions
        $allPaymentsResponse = $this->paymentService->getAll();

        if ($allPaymentsResponse->isEmpty()) {
            $this->markTestSkipped('No existing transactions found to test verify functionality');
            return;
        }

        $transactions = $allPaymentsResponse->getTransactions();
        $existingTransaction = null;

        // Find a transaction with a valid transaction reference
        foreach ($transactions as $transaction) {
            if ($transaction->transactionReference !== null && $transaction->paymentReference !== null) {
                $existingTransaction = $transaction;
                break;
            }
        }

        if ($existingTransaction === null) {
            $this->markTestSkipped('No transaction with valid references found to test verify functionality');
            return;
        }

        // Now try to verify this existing transaction
        try {
            $verifyResponse = $this->paymentService->getStatus($existingTransaction->transactionReference);

            $this->assertInstanceOf(VerifyPaymentResponse::class, $verifyResponse);
            $this->assertTrue($verifyResponse->requestSuccessful);
            $this->assertEquals($existingTransaction->transactionReference, $verifyResponse->getTransactionReference());
            $this->assertEquals($existingTransaction->paymentReference, $verifyResponse->getPaymentReference());

            // Test all status helper methods
            $this->assertIsBool($verifyResponse->isSuccessful());
            $this->assertIsBool($verifyResponse->isPending());
            $this->assertIsBool($verifyResponse->isFailed());
            $this->assertIsBool($verifyResponse->isExpired());

            // Test service helper methods
            $this->assertIsBool($this->paymentService->isSuccessful($verifyResponse));
            $this->assertIsBool($this->paymentService->isPending($verifyResponse));
            $this->assertIsBool($this->paymentService->isFailed($verifyResponse));
            $this->assertIsBool($this->paymentService->isExpired($verifyResponse));

            // Test amount methods
            $this->assertIsFloat($this->paymentService->getAmountPaid($verifyResponse));
            $this->assertIsFloat($this->paymentService->getTotalPayable($verifyResponse));
            $this->assertIsString($this->paymentService->getPaymentStatus($verifyResponse));

            // Verify the response can be serialized and deserialized
            $serialized = $verifyResponse->toArray();
            $this->assertIsArray($serialized);
            $recreated = VerifyPaymentResponse::fromArray($serialized);
            $this->assertEquals($verifyResponse->getTransactionReference(), $recreated->getTransactionReference());

        } catch (MonnifyException $e) {
            $this->logger->error("Caught error: " . $e->getMessage(),);
            // var_dump($e);
            // Even existing transactions might not be queryable individually in sandbox
            $this->assertStringContainsString('Could not find transaction', $e->getMessage());
            $this->markTestSkipped('Existing transaction not queryable individually - this can happen in sandbox');
        }
    }

    #[Test]
    public function it_handles_response_dto_serialization(): void
    {
        $responseData = [
            'requestSuccessful' => true,
            'responseMessage' => 'success',
            'responseCode' => '0',
            'responseBody' => [
                'transactionReference' => 'TXN_REF_001',
                'paymentReference' => 'PAY_REF_001',
                'merchantName' => 'Test Merchant',
                'apiKey' => 'test_api_key',
                'enabledPaymentMethod' => ['CARD', 'ACCOUNT_TRANSFER'],
                'checkoutUrl' => 'https://checkout.monnify.com/test'
            ]
        ];

        $response = InitiatePaymentResponse::fromArray($responseData);
        $serialized = $response->toArray();

        $this->assertEquals($responseData['requestSuccessful'], $serialized['requestSuccessful']);
        $this->assertEquals($responseData['responseMessage'], $serialized['responseMessage']);
        $this->assertEquals($responseData['responseCode'], $serialized['responseCode']);
        $this->assertEquals($responseData['responseBody']['transactionReference'],
                           $serialized['responseBody']['transactionReference']);
    }
}
