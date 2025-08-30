<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Data\Transfers\Responses;

/**
 * Response for searching disbursements
 */
class SearchDisbursementsResponse
{
    public function __construct(
        public readonly bool $requestSuccessful,
        public readonly string $responseMessage,
        public readonly string $responseCode,
        public readonly SearchDisbursementsResponseBody $responseBody
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
            responseBody: SearchDisbursementsResponseBody::fromArray($data['responseBody'])
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
 * Response body for searching disbursements
 */
class SearchDisbursementsResponseBody
{
    /**
     * @param DisbursementTransaction[] $content
     */
    public function __construct(
        public readonly array $content,
        public readonly Pageable $pageable,
        public readonly int $totalElements,
        public readonly int $totalPages,
        public readonly bool $last,
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
        $content = array_map(fn($item) => DisbursementTransaction::fromArray($item), $data['content']);

        return new self(
            content: $content,
            pageable: Pageable::fromArray($data['pageable']),
            totalElements: $data['totalElements'],
            totalPages: $data['totalPages'],
            last: $data['last'],
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
            'totalElements' => $this->totalElements,
            'last' => $this->last,
            'totalPages' => $this->totalPages,
            'sort' => $this->sort->toArray(),
            'first' => $this->first,
            'numberOfElements' => $this->numberOfElements,
            'size' => $this->size,
            'number' => $this->number,
            'empty' => $this->empty,
        ];
    }

    /**
     * Get all disbursement transactions
     * @return DisbursementTransaction[]
     */
    public function getTransactions(): array
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
 * Individual disbursement transaction
 */
class DisbursementTransaction
{
    public function __construct(
        public readonly float $amount,
        public readonly string $reference,
        public readonly string $narration,
        public readonly string $currency,
        public readonly float $fee,
        public readonly bool $twoFaEnabled,
        public readonly string $status,
        public readonly string $transactionDescription,
        public readonly string $transactionReference,
        public readonly string $createdOn,
        public readonly string $sourceAccountNumber,
        public readonly string $destinationAccountNumber,
        public readonly string $destinationAccountName,
        public readonly string $destinationBankCode,
        public readonly string $destinationBankName
    ) {
    }

    /**
     * Create from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            amount: $data['amount'],
            reference: $data['reference'],
            narration: $data['narration'],
            currency: $data['currency'],
            fee: $data['fee'],
            twoFaEnabled: $data['twoFaEnabled'],
            status: $data['status'],
            transactionDescription: $data['transactionDescription'],
            transactionReference: $data['transactionReference'],
            createdOn: $data['createdOn'],
            sourceAccountNumber: $data['sourceAccountNumber'],
            destinationAccountNumber: $data['destinationAccountNumber'],
            destinationAccountName: $data['destinationAccountName'],
            destinationBankCode: $data['destinationBankCode'],
            destinationBankName: $data['destinationBankName']
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
     * Check if transaction was successful
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'SUCCESS';
    }

    /**
     * Check if transaction is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'PENDING';
    }

    /**
     * Check if transaction failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'FAILED';
    }

    /**
     * Check if transaction expired
     */
    public function isExpired(): bool
    {
        return $this->status === 'EXPIRED';
    }

    /**
     * Check if transaction is pending authorization
     */
    public function isPendingAuthorization(): bool
    {
        return $this->status === 'PENDING_AUTHORIZATION';
    }

    /**
     * Check if 2FA was enabled for this transaction
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

    /**
     * Check if transaction is in a terminal state (won't change)
     */
    public function isTerminal(): bool
    {
        return in_array($this->status, ['SUCCESS', 'FAILED', 'EXPIRED']);
    }

    /**
     * Check if transaction is in a non-terminal state (can still change)
     */
    public function isNonTerminal(): bool
    {
        return in_array($this->status, ['PENDING', 'PENDING_AUTHORIZATION']);
    }
}