<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Data\Responses;

/**
 * Response for listing single transfers
 */
class ListSingleTransfersResponse
{
    public function __construct(
        public readonly bool $requestSuccessful,
        public readonly string $responseMessage,
        public readonly string $responseCode,
        public readonly ListSingleTransfersResponseBody $responseBody
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
            responseBody: ListSingleTransfersResponseBody::fromArray($data['responseBody'])
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
 * Response body for listing single transfers
 */
class ListSingleTransfersResponseBody
{
    /**
     * @param TransferItem[] $content
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
        $content = array_map(fn($item) => TransferItem::fromArray($item), $data['content']);

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
     * Get all transfers
     * @return TransferItem[]
     */
    public function getTransfers(): array
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
 * Individual transfer item in the list
 */
class TransferItem
{
    public function __construct(
        public readonly float $amount,
        public readonly ?string $reference,
        public readonly ?string $narration,
        public readonly string $currency,
        public readonly float $fee,
        public readonly bool $twoFaEnabled,
        public readonly string $status,
        public readonly ?string $transactionDescription,
        public readonly ?string $transactionReference,
        public readonly string $createdOn,
        public readonly ?string $sourceAccountNumber,
        public readonly ?string $destinationAccountNumber,
        /**
         * Null when the transfer recipient could not be resolved.
         */
        public readonly ?string $destinationAccountName,
        public readonly ?string $destinationBankCode,
        public readonly ?string $destinationBankName
    ) {
    }

    /**
     * Create from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            amount: $data['amount'],
            reference: @$data['reference'],
            narration: @$data['narration'],
            currency: $data['currency'],
            fee: $data['fee'],
            twoFaEnabled: $data['twoFaEnabled'],
            status: $data['status'],
            transactionDescription: @$data['transactionDescription'],
            transactionReference: $data['transactionReference'],
            createdOn: $data['createdOn'],
            sourceAccountNumber: @$data['sourceAccountNumber'],
            destinationAccountNumber: @$data['destinationAccountNumber'],
            destinationAccountName: @$data['destinationAccountName'],
            destinationBankCode: @$data['destinationBankCode'],
            destinationBankName: @$data['destinationBankName']
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'reference' => $this->reference,
            'narration' => $this->narration,
            'currency' => $this->currency,
            'fee' => $this->fee,
            'twoFaEnabled' => $this->twoFaEnabled,
            'status' => $this->status,
            'transactionDescription' => $this->transactionDescription,
            'transactionReference' => $this->transactionReference,
            'createdOn' => $this->createdOn,
            'sourceAccountNumber' => $this->sourceAccountNumber,
            'destinationAccountNumber' => $this->destinationAccountNumber,
            'destinationAccountName' => $this->destinationAccountName,
            'destinationBankCode' => $this->destinationBankCode,
            'destinationBankName' => $this->destinationBankName,
        ];
    }

    /**
     * Check if transfer was successful
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'SUCCESS';
    }

    /**
     * Check if transfer is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'PENDING';
    }

    /**
     * Check if transfer failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'FAILED';
    }

    /**
     * Check if 2FA was enabled for this transfer
     */
    public function isTwoFaEnabled(): bool
    {
        return $this->twoFaEnabled;
    }

    /**
     * Get the net amount (amount minus fee)
     */
    public function getNetAmount(): float
    {
        return $this->amount - $this->fee;
    }
}

/**
 * Pagination information
 */
class Pageable
{
    public function __construct(
        public readonly Sort $sort,
        public readonly int $pageSize,
        public readonly int $pageNumber,
        public readonly int $offset,
        public readonly bool $unpaged,
        public readonly bool $paged
    ) {
    }

    /**
     * Create from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            sort: Sort::fromArray($data['sort']),
            pageSize: $data['pageSize'],
            pageNumber: $data['pageNumber'],
            offset: $data['offset'],
            unpaged: $data['unpaged'],
            paged: $data['paged']
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'sort' => $this->sort->toArray(),
            'pageSize' => $this->pageSize,
            'pageNumber' => $this->pageNumber,
            'offset' => $this->offset,
            'unpaged' => $this->unpaged,
            'paged' => $this->paged,
        ];
    }
}

/**
 * Sort information
 */
class Sort
{
    public function __construct(
        public readonly bool $sorted,
        public readonly bool $unsorted,
        public readonly bool $empty
    ) {
    }

    /**
     * Create from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            sorted: $data['sorted'],
            unsorted: $data['unsorted'],
            empty: $data['empty']
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'sorted' => $this->sorted,
            'unsorted' => $this->unsorted,
            'empty' => $this->empty,
        ];
    }
}