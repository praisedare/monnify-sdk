<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Data\Payments\Responses;

/**
 * Response DTO for getting all payments
 */
class GetAllPaymentsResponse
{
    public function __construct(
        public readonly bool $requestSuccessful,
        public readonly string $responseMessage,
        public readonly string $responseCode,
        public readonly GetAllPaymentsResponseBody $responseBody,
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
            responseBody: GetAllPaymentsResponseBody::fromArray($data['responseBody']),
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

    /**
     * Get transactions
     */
    public function getTransactions(): array
    {
        return $this->responseBody->content;
    }

    /**
     * Get total number of elements
     */
    public function getTotalElements(): int
    {
        return $this->responseBody->totalElements;
    }

    /**
     * Get total number of pages
     */
    public function getTotalPages(): int
    {
        return $this->responseBody->totalPages;
    }

    /**
     * Check if this is the first page
     */
    public function isFirst(): bool
    {
        return $this->responseBody->first;
    }

    /**
     * Check if this is the last page
     */
    public function isLast(): bool
    {
        return $this->responseBody->last;
    }

    /**
     * Get current page number
     */
    public function getPageNumber(): int
    {
        return $this->responseBody->number;
    }

    /**
     * Get page size
     */
    public function getPageSize(): int
    {
        return $this->responseBody->size;
    }

    /**
     * Get number of elements in current page
     */
    public function getNumberOfElements(): int
    {
        return $this->responseBody->numberOfElements;
    }

    /**
     * Check if result is empty
     */
    public function isEmpty(): bool
    {
        return $this->responseBody->empty;
    }
}

/**
 * Response body DTO for getting all payments
 */
class GetAllPaymentsResponseBody
{
    public function __construct(
        public readonly array $content,
        public readonly bool $last,
        public readonly int $totalElements,
        public readonly int $totalPages,
        public readonly bool $first,
        public readonly int $numberOfElements,
        public readonly int $size,
        public readonly int $number,
        public readonly bool $empty,
    ) {
    }

    /**
     * Create from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            content: array_map(fn($transaction) => PaymentTransactionSummary::fromArray($transaction), $data['content']),
            last: $data['last'],
            totalElements: $data['totalElements'],
            totalPages: $data['totalPages'],
            first: $data['first'],
            numberOfElements: $data['numberOfElements'],
            size: $data['size'],
            number: $data['number'],
            empty: $data['empty'],
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'content' => array_map(fn($transaction) => $transaction->toArray(), $this->content),
            'last' => $this->last,
            'totalElements' => $this->totalElements,
            'totalPages' => $this->totalPages,
            'first' => $this->first,
            'numberOfElements' => $this->numberOfElements,
            'size' => $this->size,
            'number' => $this->number,
            'empty' => $this->empty,
        ];
    }
}

/**
 * Payment transaction summary DTO
 */
class PaymentTransactionSummary
{
    public function __construct(
        public readonly ?string $paymentMethod,
        public readonly ?string $createdOn,
        public readonly ?float $amount,
        public readonly bool $flagged,
        public readonly ?string $currencyCode,
        public readonly ?string $completedOn,
        public readonly ?string $paymentDescription,
        public readonly ?string $paymentStatus,
        public readonly ?string $transactionReference,
        public readonly ?string $paymentReference,
        public readonly ?string $merchantCode,
        public readonly ?string $merchantName,
        public readonly ?float $fee = null,
        public readonly ?float $payableAmount = null,
    ) {
    }

    /**
     * Create from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            paymentMethod: @$data['paymentMethod'],
            createdOn: @$data['createdOn'],
            amount: (float) @$data['amount'],
            flagged: (bool) @$data['flagged'],
            currencyCode: @$data['currencyCode'],
            completedOn: @$data['completedOn'],
            paymentDescription: @$data['paymentDescription'],
            paymentStatus: @$data['paymentStatus'],
            transactionReference: @$data['transactionReference'],
            paymentReference: @$data['paymentReference'],
            merchantCode: @$data['merchantCode'],
            merchantName: @$data['merchantName'],
            fee: (float) @$data['fee'],
            payableAmount: (float) @$data['payableAmount'],
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'paymentMethod' => $this->paymentMethod,
            'createdOn' => $this->createdOn,
            'amount' => $this->amount,
            'flagged' => $this->flagged,
            'currencyCode' => $this->currencyCode,
            'completedOn' => $this->completedOn,
            'paymentDescription' => $this->paymentDescription,
            'paymentStatus' => $this->paymentStatus,
            'transactionReference' => $this->transactionReference,
            'paymentReference' => $this->paymentReference,
            'merchantCode' => $this->merchantCode,
            'merchantName' => $this->merchantName,
            'fee' => $this->fee,
            'payableAmount' => $this->payableAmount,
        ];
    }

    /**
     * Check if payment is successful
     */
    public function isSuccessful(): bool
    {
        return $this->paymentStatus === 'PAID';
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return $this->paymentStatus === 'PENDING';
    }

    /**
     * Check if payment failed
     */
    public function isFailed(): bool
    {
        return $this->paymentStatus === 'FAILED';
    }

    /**
     * Check if payment expired
     */
    public function isExpired(): bool
    {
        return $this->paymentStatus === 'EXPIRED';
    }

    /**
     * Check if transaction is flagged
     */
    public function isFlagged(): bool
    {
        return $this->flagged;
    }
}
