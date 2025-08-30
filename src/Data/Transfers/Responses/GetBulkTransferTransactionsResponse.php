<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Data\Transfers\Responses;

/**
 * Response for getting bulk transfer transactions
 */
class GetBulkTransferTransactionsResponse
{
    public function __construct(
        public readonly bool $requestSuccessful,
        public readonly string $responseMessage,
        public readonly string $responseCode,
        public readonly GetBulkTransferTransactionsResponseBody $responseBody
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
            responseBody: GetBulkTransferTransactionsResponseBody::fromArray($data['responseBody'])
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
 * Response body for getting bulk transfer transactions
 */
class GetBulkTransferTransactionsResponseBody
{
    /**
     * @param BulkTransferBatch[] $content
     */
    public function __construct(
        public readonly array $content,
        public readonly Pageable $pageable,
        public readonly int $totalPages,
        public readonly bool $last,
        public readonly int $totalElements,
        public readonly Sort $sort,
        public readonly bool $first,
        public readonly int $numberOfElements,
        public readonly int $size,
        public readonly int $number,
        public readonly bool $empty
    ) {
    }

    /**
     * Create from array
     */
    public static function fromArray(array $data): self
    {
        $content = array_map(fn($item) => BulkTransferBatch::fromArray($item), $data['content']);

        return new self(
            content: $content,
            pageable: Pageable::fromArray($data['pageable']),
            totalPages: $data['totalPages'],
            last: $data['last'],
            totalElements: $data['totalElements'],
            sort: Sort::fromArray($data['sort']),
            first: $data['first'],
            numberOfElements: $data['numberOfElements'],
            size: $data['size'],
            number: $data['number'],
            empty: $data['empty']
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'content' => array_map(fn($item) => $item->toArray(), $this->content),
            'pageable' => $this->pageable->toArray(),
            'totalPages' => $this->totalPages,
            'last' => $this->last,
            'totalElements' => $this->totalElements,
            'sort' => $this->sort->toArray(),
            'first' => $this->first,
            'numberOfElements' => $this->numberOfElements,
            'size' => $this->size,
            'number' => $this->number,
            'empty' => $this->empty,
        ];
    }

    /**
     * Get all bulk transfer batches
     * @return BulkTransferBatch[]
     */
    public function getBatches(): array
    {
        return $this->content;
    }

    /**
     * Check if this is the first page
     */
    public function isFirstPage(): bool
    {
        return $this->first;
    }

    /**
     * Check if this is the last page
     */
    public function isLastPage(): bool
    {
        return $this->last;
    }

    /**
     * Check if there are more pages
     */
    public function hasNextPage(): bool
    {
        return !$this->last;
    }

    /**
     * Check if there are previous pages
     */
    public function hasPreviousPage(): bool
    {
        return !$this->first;
    }
}

/**
 * Individual bulk transfer batch
 */
class BulkTransferBatch
{
    public function __construct(
        public readonly float $totalAmount,
        public readonly float $totalFee,
        public readonly string $batchReference,
        public readonly string $batchStatus,
        public readonly int $totalTransactionsCount,
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
            totalTransactionsCount: $data['totalTransactionsCount'],
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
            'totalTransactionsCount' => $this->totalTransactionsCount,
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
     * Get the average amount per transaction
     */
    public function getAverageAmountPerTransaction(): float
    {
        if ($this->totalTransactionsCount === 0) {
            return 0.0;
        }
        return $this->totalAmount / $this->totalTransactionsCount;
    }
}