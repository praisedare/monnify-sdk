<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Data\Transfers\Responses;

use PraiseDare\Monnify\Data\MonnifyResponse;

/**
 * Response for authorizing a bulk transfer
 * @property ?AuthorizeBulkTransferResponseBody $responseBody
 */
class AuthorizeBulkTransferResponse extends MonnifyResponse
{
    public function __construct(
        bool $requestSuccessful,
        string $responseMessage,
        string $responseCode,
        ?AuthorizeBulkTransferResponseBody $responseBody
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
                ? AuthorizeBulkTransferResponseBody::fromArray($data['responseBody'])
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
 * Response body for authorizing a bulk transfer
 */
class AuthorizeBulkTransferResponseBody
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
            totalTransactions: $data['totalTransactions'],
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
     * Check if batch is awaiting processing after authorization
     */
    public function isAwaitingProcessing(): bool
    {
        return $this->batchStatus === 'AWAITING_PROCESSING';
    }

    /**
     * Get the net amount (total amount minus fees)
     */
    public function getNetAmount(): float
    {
        return $this->totalAmount - $this->totalFee;
    }
}