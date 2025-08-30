<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PraiseDare\Monnify\Data\Transfers\BulkTransferData;
use PraiseDare\Monnify\Data\Transfers\TransferData;
use PraiseDare\Monnify\Monnify;


// Initialize Monnify SDK
$creds = require __DIR__ . '/../ignored/creds.php';
$monnify = new Monnify($creds);

// Example 1: Initiate a single transfer
$example1 = (function () use ($monnify, $creds) {
    try {
        $result = $monnify->transfer()->initiateSingle(new TransferData(
            amount: 1000000.00,
            reference: 'TRF_' . uniqid(),
            narration: 'TEST: Payment for services',
            destinationBankCode: '050', // First Bank
            destinationAccountNumber: '3840012889',
            destinationAccountName: 'PRAISE TEMITOPE DARE',
            currency: 'NGN',
            sourceAccountNumber: $creds['wallet_account_number'],
            beneficiaryEmail: 'praisedare27@gmail.com',
            beneficiaryPhone: '+2348162142531',
            metadata: [
                'customer_id' => '12345',
                'order_id' => 'ORD_001'
            ]
        ));
        echo "Single Transfer Initiated: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
    } catch (Exception $e) {
        echo "Error initiating single transfer: " . $e->getMessage() . "\n";
    }
});
$example1();

// Example 2: Initiate an asynchronous transfer
$example2 = (function () use ($monnify, $creds) {
    try {
        $asyncTransferData = [
            'amount' => 500.00,
            'reference' => 'ASYNC_TRF_' . uniqid(),
            'narration' => 'Async payment',
            'destinationBankCode' => '058', // GT Bank
            'destinationAccountNumber' => '0987654321',
            'destinationAccountName' => 'Jane Smith',
            'currency' => 'NGN',
            'sourceAccountNumber' => '1234567890',
            'beneficiaryEmail' => 'jane@example.com'
        ];

        $result = $monnify->transfer()->initiateAsync($asyncTransferData);
        echo "Async Transfer Initiated: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
    } catch (Exception $e) {
        echo "Error initiating async transfer: " . $e->getMessage() . "\n";
    }
});
$example2();

// Example 3: Initiate a bulk transfer
$example3 = (function () use ($monnify, $creds) {
    try {
        $bulkTransferData = new BulkTransferData(
            title: 'Bulk Payment for Employees',
            batchReference: 'BATCH_' . uniqid(),
            narration: 'Salary payment for March 2024',
            sourceAccountNumber: $creds['wallet_account_number'],
            currency: 'NGN',
            onValidationFailure: 'CONTINUE',
            notificationInterval: 25,
            transactionList: [
                new TransferData(
                    amount: 5000.00,
                    reference: 'EMP_001_' . uniqid(),
                    narration: 'Salary for Employee 1',
                    destinationBankCode: '044',
                    destinationAccountNumber: '1111111111',
                    destinationAccountName: 'Employee One',
                    beneficiaryEmail: 'emp1@company.com',
                    sourceAccountNumber: $creds['wallet_account_number'],
                ),
                new TransferData(
                    amount: 47000.00,
                    reference: 'EMP_002_' . uniqid(),
                    narration: 'Salary for Employee 2',
                    destinationBankCode: '058',
                    destinationAccountNumber: '2222222222',
                    destinationAccountName: 'Employee Two',
                    beneficiaryEmail: 'emp2@company.com',
                    sourceAccountNumber: $creds['wallet_account_number'],
                ),
            ]
        );

        $result = $monnify->transfer()->initiateBulk($bulkTransferData);
        echo "Bulk Transfer Initiated: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
    } catch (Exception $e) {
        echo "Error initiating bulk transfer: " . $e->getMessage() . "\n";
    }
});
$example3();

// Example 4: Authorize a single transfer
$example4 = (function () use ($monnify, $creds) {
    try {
        $authorizationData = [
            'reference' => 'MFDS44620250821123435000137NU1C04',
            'authorizationCode' => '123456' // OTP received via SMS/Email
        ];

        $result = $monnify->transfer()->authorizeSingle($authorizationData);
        echo "Single Transfer Authorized: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
    } catch (Exception $e) {
        echo "Error authorizing single transfer: " . $e->getMessage() . "\n";
    }
});
$example4();

// Example 5: Authorize a bulk transfer
$example5 = (function () use ($monnify, $creds) {
    try {
        $bulkAuthorizationData = [
            'reference' => 'BATCH_REFERENCE_HERE',
            'authorizationCode' => '123456' // OTP received via SMS/Email
        ];

        $result = $monnify->transfer()->authorizeBulk($bulkAuthorizationData);
        echo "Bulk Transfer Authorized: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
    } catch (Exception $e) {
        echo "Error authorizing bulk transfer: " . $e->getMessage() . "\n";
    }
});
$example5();

// Example 6: Resend OTP
$example6 = (function () use ($monnify, $creds) {
    try {
        $reference = 'TRF_REFERENCE_HERE';
        $result = $monnify->transfer()->resendOtp($reference);
        echo "OTP Resent: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
    } catch (Exception $e) {
        echo "Error resending OTP: " . $e->getMessage() . "\n";
    }
});
$example6();

// Example 7: Get single transfer status
$example7 = (function () use ($monnify, $creds) {
    try {
        $reference = 'TRF_REFERENCE_HERE';
        $result = $monnify->transfer()->getSingleTransferStatus($reference);
        echo "Single Transfer Status: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
    } catch (Exception $e) {
        echo "Error getting transfer status: " . $e->getMessage() . "\n";
    }
});
$example7();

// Example 8: List all single transfers
$example8 = (function () use ($monnify, $creds) {
    try {
        $filters = [
            'page' => 0,
            'size' => 10,
            'from' => '2024-01-01',
            'to' => '2024-12-31',
            'status' => 'SUCCESSFUL'
        ];

        $result = $monnify->transfer()->listSingleTransfers($filters);
        echo "Single Transfers List: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
    } catch (Exception $e) {
        echo "Error listing transfers: " . $e->getMessage() . "\n";
    }
});
$example8();

// Example 9: Get bulk transfer transactions
$example9 = (function () use ($monnify, $creds) {
    try {
        $batchReference = 'BATCH_REFERENCE_HERE';
        $result = $monnify->transfer()->getBulkTransferTransactions($batchReference);
        echo "Bulk Transfer Transactions: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
    } catch (Exception $e) {
        echo "Error getting bulk transfer transactions: " . $e->getMessage() . "\n";
    }
});
// $example9();

// Example 10: Get bulk transfer status
$example10 = (function () use ($monnify, $creds) {
    try {
        $batchReference = 'BATCH_REFERENCE_HERE';
        $result = $monnify->transfer()->getBulkTransferStatus($batchReference);
        echo "Bulk Transfer Status: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
    } catch (Exception $e) {
        echo "Error getting bulk transfer status: " . $e->getMessage() . "\n";
    }
});
// $example10();

// Example 11: Search disbursement transactions
$example11 = (function () use ($monnify, $creds) {
    try {
        $searchFilters = [
            'page' => 0,
            'size' => 20,
            'from' => '2024-01-01',
            'to' => '2024-12-31',
            'status' => 'SUCCESSFUL',
            'reference' => 'TRF_',
            'destinationAccountNumber' => '1234567890',
            'destinationBankCode' => '044'
        ];

        $result = $monnify->transfer()->searchDisbursements($searchFilters);
        echo "Disbursement Search Results: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
    } catch (Exception $e) {
        echo "Error searching disbursements: " . $e->getMessage() . "\n";
    }
});
// $example11();

// Example 12: Get wallet balance
$example12 = (function () use ($monnify, $creds) {
    try {
        $accountNumber = 'your_account_number';
        $result = $monnify->transfer()->getWalletBalance($accountNumber);
        echo "Wallet Balance: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
    } catch (Exception $e) {
        echo "Error getting wallet balance: " . $e->getMessage() . "\n";
    }
});
// $example12();

echo "\nTransfer examples completed!\n";
