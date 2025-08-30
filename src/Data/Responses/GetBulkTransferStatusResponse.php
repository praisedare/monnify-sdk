<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Data\Responses;

/**
 * Response for getting bulk transfer status
 */
class GetBulkTransferStatusResponse
{
    public function __construct(
        public readonly bool $requestSuccessful,
        public readonly string $responseMessage,
        public readonly string $responseCode,
        public readonly GetBulkTransferStatusResponseBody $responseBody
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
            responseBody: GetBulkTransferStatusResponseBody::fromArray($data['responseBody'])
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
 * Response body for getting bulk transfer status
 */
class GetBulkTransferStatusResponseBody
{
    public function __construct(
        public readonly string $title,
        public readonly float $totalAmount,
        public readonly float $totalFee,
        public readonly string $batchReference,
        public readonly int $totalTransactions,
        public readonly int $failedCount,
        public readonly int $successfulCount,
        public readonly int $pendingCount,
        public readonly string $batchStatus,
        public readonly string $dateCreated
    ) {
    }

    /**
     * Create from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'],
            totalAmount: $data['totalAmount'],
            totalFee: $data['totalFee'],
            batchReference: $data['batchReference'],
            totalTransactions: $data['totalTransactions'],
            failedCount: $data['failedCount'],
            successfulCount: $data['successfulCount'],
            pendingCount: $data['pendingCount'],
            batchStatus: $data['batchStatus'],
            dateCreated: $data['dateCreated']
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'totalAmount' => $this->totalAmount,
            'totalFee' => $this->totalFee,
            'batchReference' => $this->batchReference,
            'totalTransactions' => $this->totalTransactions,
            'failedCount' => $this->failedCount,
            'successfulCount' => $this->successfulCount,
            'pendingCount' => $this->pendingCount,
            'batchStatus' => $this->batchStatus,
            'dateCreated' => $this->dateCreated,
        ];
    }

    /**
     * Check if batch is completed
     */
    public function isCompleted(): bool
    {
        return $this->batchStatus === 'COMPLETED';
    }

    /**
     * Check if batch is pending
     */
    public function isPending(): bool
    {
        return $this->batchStatus === 'PENDING';
    }

    /**
     * Check if batch failed
     */
    public function isFailed(): bool
    {
        return $this->batchStatus === 'FAILED';
    }

    /**
     * Check if batch is awaiting processing
     */
    public function isAwaitingProcessing(): bool
    {
        return $this->batchStatus === 'AWAITING_PROCESSING';
    }

    /**
     * Check if batch is pending authorization
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

    /**
     * Get the completion percentage
     */
    public function getCompletionPercentage(): float
    {
        if ($this->totalTransactions === 0) {
            return 0.0;
        }
        return ($this->successfulCount + $this->failedCount) / $this->totalTransactions * 100;
    }

    /**
     * Check if all transactions are successful
     */
    public function isAllSuccessful(): bool
    {
        return $this->successfulCount === $this->totalTransactions && $this->failedCount === 0 && $this->pendingCount === 0;
    }

    /**
     * Check if any transactions failed
     */
    public function hasFailures(): bool
    {
        return $this->failedCount > 0;
    }

    /**
     * Check if any transactions are still pending
     */
    public function hasPendingTransactions(): bool
    {
        return $this->pendingCount > 0;
    }

    /**
     * Get the success rate percentage
     */
    public function getSuccessRate(): float
    {
        $completedTransactions = $this->successfulCount + $this->failedCount;
        if ($completedTransactions === 0) {
            return 0.0;
        }
        return $this->successfulCount / $completedTransactions * 100;
    }
}