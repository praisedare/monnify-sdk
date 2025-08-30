<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Services;

use PraiseDare\Monnify\Http\Client;
use PraiseDare\Monnify\Exceptions\MonnifyException;
use PraiseDare\Monnify\Data\Transfers\{
    TransferData,
    BulkTransferData,
    AuthorizationData,
    BulkAuthorizationData,
    TransferFilterData,
};
use PraiseDare\Monnify\Data\Transfers\Responses\{
    InitiateSingleTransferResponse,
    InitiateAsyncTransferResponse,
    InitiateBulkTransferResponse,
    AuthorizeSingleTransferResponse,
    AuthorizeBulkTransferResponse,
    ResendOtpResponse,
    GetSingleTransferStatusResponse,
    ListSingleTransfersResponse,
    GetBulkTransferTransactionsResponse,
    GetBulkTransferStatusResponse,
    SearchDisbursementsResponse,
    GetWalletBalanceResponse,
};

/**
 * Transfer Service for Monnify API
 *
 * Handles all transfer/disbursement-related operations
 */
class TransferService
{
    private Client $client;

    const BASE_PATH = '/api/v2/disbursements';

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
     * Initiate a single transfer
     *
     * @param TransferData|array{
     *  amount: float,
     *  reference: string,
     *  narration: string,
     *  destinationBankCode: string,
     *  destinationAccountNumber: string,
     *  destinationAccountName: string,
     *  currency: string,
     *  sourceAccountNumber: string,
     *  beneficiaryEmail?: string,
     *  beneficiaryPhone?: string,
     *  metadata?: array<string, mixed>
     * } $data Transfer data
     * @return InitiateSingleTransferResponse Response data
     * @throws MonnifyException
     */
    public function initiateSingle(TransferData|array $data): InitiateSingleTransferResponse
    {
        if (is_array($data)) {
            $data = TransferData::fromArray($data);
        }

        $response = $this->client->post(self::BASE_PATH . '/single', $data->toArray());
        return InitiateSingleTransferResponse::fromArray($response);
    }

    /**
     * Initiate an asynchronous transfer
     *
     * @param TransferData|array{
     *  amount: float,
     *  reference: string,
     *  narration: string,
     *  destinationBankCode: string,
     *  destinationAccountNumber: string,
     *  destinationAccountName: string,
     *  currency: string,
     *  sourceAccountNumber: string,
     *  beneficiaryEmail?: string,
     *  beneficiaryPhone?: string,
     *  metadata?: array<string, mixed>
     * } $data Transfer data
     * @return InitiateAsyncTransferResponse Response data
     * @throws MonnifyException
     */
    public function initiateAsync(TransferData|array $data): InitiateAsyncTransferResponse
    {
        if (is_array($data)) {
            $data = TransferData::fromArray([...$data, 'async' => true]);
        }

        $response = $this->client->post(self::BASE_PATH . '/single', $data->toArray());
        return InitiateAsyncTransferResponse::fromArray($response);
    }

    /**
     * Initiate a bulk transfer
     *
     * @param BulkTransferData|array{
     *  title: string,
     *  batchReference: string,
     *  narration: string,
     *  sourceAccountNumber: string,
     *  currency: string,
     *  onValidationFailure: string,
     *  notificationInterval: int,
     *  transactionList: array<array{
     *    amount: float,
     *    reference: string,
     *    narration: string,
     *    destinationBankCode: string,
     *    destinationAccountNumber: string,
     *    destinationAccountName: string,
     *    beneficiaryEmail?: string,
     *    beneficiaryPhone?: string,
     *    metadata?: array<string, mixed>
     *  }>
     * } $data Bulk transfer data
     * @return InitiateBulkTransferResponse Response data
     * @throws MonnifyException
     */
    public function initiateBulk(BulkTransferData|array $data): InitiateBulkTransferResponse
    {
        if (is_array($data)) {
            $data = BulkTransferData::fromArray($data);
        }

        $response = $this->client->post(self::BASE_PATH . '/batch', $data->toArray());
        return InitiateBulkTransferResponse::fromArray($response);
    }

    /**
     * Authorize a single transfer
     *
     * @param AuthorizationData|array{
     *  reference: string,
     *  authorizationCode: string
     * } $data Authorization data
     * @return AuthorizeSingleTransferResponse Response data
     * @throws MonnifyException
     */
    public function authorizeSingle(AuthorizationData|array $data): AuthorizeSingleTransferResponse
    {
        if (is_array($data)) {
            $data = AuthorizationData::fromArray($data);
        }

        $response = $this->client->post(self::BASE_PATH . '/single/validate-otp', $data->toArray());
        return AuthorizeSingleTransferResponse::fromArray($response);
    }

    /**
     * Authorize a bulk transfer
     *
     * @param BulkAuthorizationData|array{
     *  batchReference: string,
     *  authorizationCode: string
     * } $data Authorization data
     * @return AuthorizeBulkTransferResponse Response data
     * @throws MonnifyException
     */
    public function authorizeBulk(BulkAuthorizationData|array $data): AuthorizeBulkTransferResponse
    {
        if (is_array($data)) {
            $data = BulkAuthorizationData::fromArray($data);
        }

        $response = $this->client->post(self::BASE_PATH . '/batch/validate-otp', $data->toArray());
        return AuthorizeBulkTransferResponse::fromArray($response);
    }

    /**
     * Resend OTP for transfer authorization
     *
     * @param string $reference Transfer reference
     * @return ResendOtpResponse Response data
     * @throws MonnifyException
     */
    public function resendOtp(string $reference): ResendOtpResponse
    {
        if (empty($reference)) {
            throw new MonnifyException('Reference is required', 400, null, 'VALIDATION_ERROR');
        }

        $payload = [
            'reference' => $reference,
        ];

        $response = $this->client->post(self::BASE_PATH . '/single/resend-otp', $payload);
        return ResendOtpResponse::fromArray($response);
    }

    /**
     * Get single transfer status
     *
     * @param string $reference Transfer reference
     * @return GetSingleTransferStatusResponse Response data
     * @throws MonnifyException
     */
    public function getSingleTransferStatus(string $reference): GetSingleTransferStatusResponse
    {
        if (empty($reference)) {
            throw new MonnifyException('Reference is required', 400, null, 'VALIDATION_ERROR');
        }

        $response = $this->client->get(self::BASE_PATH . "/single/summary?reference={$reference}");
        return GetSingleTransferStatusResponse::fromArray($response);
    }

    /**
     * List all single transfers
     *
     * @param TransferFilterData|array{
     *  page?: int,
     *  size?: int,
     *  from?: string,
     *  to?: string,
     *  status?: string
     * }|null $filters Optional filters
     * @return ListSingleTransfersResponse Response data
     * @throws MonnifyException
     */
    public function listSingleTransfers(TransferFilterData|array|null $filters = null): ListSingleTransfersResponse
    {
        if (is_array($filters)) {
            $filters = TransferFilterData::fromArray($filters);
        }

        $queryString = $filters ? $filters->toQueryString() : '';
        $response = $this->client->get(self::BASE_PATH . "/single/transactions{$queryString}");
        return ListSingleTransfersResponse::fromArray($response);
    }

    /**
     * Get bulk transfer transactions
     *
     * @param string $batchReference Batch reference
     * @return GetBulkTransferTransactionsResponse Response data
     * @throws MonnifyException
     */
    public function getBulkTransferTransactions(string $batchReference): GetBulkTransferTransactionsResponse
    {
        if (empty($batchReference)) {
            throw new MonnifyException('Batch reference is required', 400, null, 'VALIDATION_ERROR');
        }

        $response = $this->client->get(self::BASE_PATH . "/bulk/{$batchReference}/transactions");
        return GetBulkTransferTransactionsResponse::fromArray($response);
    }

    /**
     * Get bulk transfer status
     *
     * @param string $batchReference Batch reference
     * @return GetBulkTransferStatusResponse Response data
     * @throws MonnifyException
     */
    public function getBulkTransferStatus(string $batchReference): GetBulkTransferStatusResponse
    {
        if (empty($batchReference)) {
            throw new MonnifyException('Batch reference is required', 400, null, 'VALIDATION_ERROR');
        }

        $response = $this->client->get(self::BASE_PATH . "/bulk/{$batchReference}");
        return GetBulkTransferStatusResponse::fromArray($response);
    }

    /**
     * Search disbursement transactions
     *
     * @param TransferFilterData|array{
     *  page?: int,
     *  size?: int,
     *  from?: string,
     *  to?: string,
     *  status?: string,
     *  reference?: string,
     *  destinationAccountNumber?: string,
     *  destinationBankCode?: string
     * }|null $filters Search filters
     * @return SearchDisbursementsResponse Response data
     * @throws MonnifyException
     */
    public function searchDisbursements(TransferFilterData|array|null $filters = null): SearchDisbursementsResponse
    {
        if (is_array($filters)) {
            $filters = TransferFilterData::fromArray($filters);
        }

        $queryString = $filters ? $filters->toQueryString() : '';
        $response = $this->client->get(self::BASE_PATH . "/search{$queryString}");
        return SearchDisbursementsResponse::fromArray($response);
    }

    /**
     * Get wallet balance
     *
     * @param string $accountNumber Account number
     * @return GetWalletBalanceResponse Response data
     * @throws MonnifyException
     */
    public function getWalletBalance(string $accountNumber): GetWalletBalanceResponse
    {
        if (empty($accountNumber)) {
            throw new MonnifyException('Account number is required', 400, null, 'VALIDATION_ERROR');
        }

        $response = $this->client->get(self::BASE_PATH . "/wallet-balance?accountNumber={$accountNumber}");
        return GetWalletBalanceResponse::fromArray($response);
    }


}