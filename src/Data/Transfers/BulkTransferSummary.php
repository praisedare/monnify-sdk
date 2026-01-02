<?php

namespace PraiseDare\Monnify\Data\Transfers;

/**
 * Response body for getting bulk transfer status
 */
class BulkTransferSummary
{
    public function __construct(
        public readonly string $title,
        public readonly float $totalAmount,
        public readonly float $totalFee,
        public readonly string $batchReference,
        public readonly int $totalTransactionsCount,
        public readonly int $failedCount,
        public readonly int $successfulCount,
        public readonly int $pendingCount,
        public readonly float $pendingAmount,
        public readonly float $failedAmount,
        public readonly float $successfulAmount,
        public readonly string $batchStatus,
        public readonly string $dateCreated
    ) {
    }

    /**
     * Create from array
     */
    public static function fromArray(array $data): self
    {
        return new self(...$data);
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
            'totalTransactionsCount' => $this->totalTransactionsCount,
            'failedCount' => $this->failedCount,
            'successfulCount' => $this->successfulCount,
            'pendingCount' => $this->pendingCount,
            'pendingAmount' => $this->pendingAmount,
            'failedAmount' => $this->failedAmount,
            'successfulAmount' => $this->successfulAmount,
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
        if ($this->totalTransactionsCount === 0) {
            return 0.0;
        }
        return ($this->successfulCount + $this->failedCount) / $this->totalTransactionsCount * 100;
    }

    /**
     * Check if all transactions are successful
     */
    public function isAllSuccessful(): bool
    {
        return $this->successfulCount === $this->totalTransactionsCount && $this->failedCount === 0 && $this->pendingCount === 0;
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