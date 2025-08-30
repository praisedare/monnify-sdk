<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Data\Transfers\Responses;

/**
 * Response for initiating a bulk transfer
 */
class InitiateBulkTransferResponse
{
    public function __construct(
        public readonly bool $requestSuccessful,
        public readonly string $responseMessage,
        public readonly string $responseCode,
        public readonly InitiateBulkTransferResponseBody $responseBody
    ) {
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
            responseBody: InitiateBulkTransferResponseBody::fromArray($data['responseBody'])
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
            'responseBody' => $this->responseBody->toArray(),
        ];
    }
}

/**
 * Response body for initiating a bulk transfer
 */
class InitiateBulkTransferResponseBody
{
    public function __construct(
        public readonly float $totalAmount,
        public readonly float $totalFee,
        public readonly string $batchReference,
        public readonly string $batchStatus,
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