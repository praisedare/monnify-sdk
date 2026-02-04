<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Data\Transfers\Responses;

use PraiseDare\Monnify\Data\MonnifyResponse;

/**
 * Response for initiating a bulk transfer
 */
class InitiateBulkTransferResponse extends MonnifyResponse
{
    public function __construct(
        bool $requestSuccessful,
        string $responseMessage,
        string $responseCode,
        ?InitiateBulkTransferResponseBody $responseBody
    ) {
        parent::__construct($requestSuccessful, $responseMessage, $responseCode, $responseBody);
    }

    /**
     * Create from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            requestSuccessful: $data['requestSuccessful'],
            responseMessage: $data['responseMessage'],
            responseCode: $data['responseCode'],
            responseBody: isset($data['responseBody']) && is_array($data['responseBody'])
                ? InitiateBulkTransferResponseBody::fromArray($data['responseBody'])
                : null
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'requestSuccessful' => $this->requestSuccessful,
            'responseMessage' => $this->responseMessage,
            'responseCode' => $this->responseCode,
            'responseBody' => $this->responseBody?->toArray(),
        ];
    }
}

/**
 * Response body for initiating a bulk transfer
 */
class InitiateBulkTransferResponseBody
{
    public function __construct(
        /**
         * Total amount transferred.
         */
        public readonly float $totalAmount,
        /**
         * Gross fee charged for the transfers
         */
        public readonly float $totalFee,
        /**
         * The reference of the batch transaction
         */
        public readonly string $batchReference,
        public readonly string $batchStatus,
        /** The number of transactions in the bulk transfer */
        public readonly int $totalTransactions,
        public readonly string $dateCreated
    ) {
    }

    /**
     * Create from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            totalAmount: $data['totalAmount'],
            totalFee: $data['totalFee'],
            batchReference: $data['batchReference'],
            batchStatus: $data['batchStatus'],
            totalTransactions: $data['totalTransactionsCount'],
            dateCreated: $data['dateCreated']
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'totalAmount' => $this->totalAmount,
            'totalFee' => $this->totalFee,
            'batchReference' => $this->batchReference,
            'batchStatus' => $this->batchStatus,
            'totalTransactions' => $this->totalTransactions,
            'dateCreated' => $this->dateCreated,
        ];
    }

    /**
     * Check if batch is completed (2FA disabled)
     */
    public function isCompleted(): bool
    {
        return $this->batchStatus === 'COMPLETED';
    }

    /**
     * Check if batch is pending authorization (2FA enabled)
     */
    public function isPendingAuthorization(): bool
    {
        return $this->batchStatus === 'PENDING_AUTHORIZATION';
    }

    /**
     * Get the net amount (total amount minus fees)
     */
    public function getNetAmount(): float
    {
        return $this->totalAmount - $this->totalFee;
    }
}