<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Data\Responses;

/**
 * Response for initiating an asynchronous transfer
 */
class InitiateAsyncTransferResponse
{
    public function __construct(
        public readonly bool $requestSuccessful,
        public readonly string $responseMessage,
        public readonly string $responseCode,
        public readonly InitiateAsyncTransferResponseBody $responseBody
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
            responseBody: InitiateAsyncTransferResponseBody::fromArray($data['responseBody'])
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
 * Response body for initiating an asynchronous transfer
 */
class InitiateAsyncTransferResponseBody
{
    public function __construct(
        public readonly float $amount,
        public readonly string $reference,
        public readonly string $status,
        public readonly string $dateCreated,
        public readonly float $totalFee,
        public readonly string $destinationAccountName,
        public readonly string $destinationBankName,
        public readonly string $destinationAccountNumber,
        public readonly string $destinationBankCode
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
            status: $data['status'],
            dateCreated: $data['dateCreated'],
            totalFee: $data['totalFee'],
            destinationAccountName: $data['destinationAccountName'],
            destinationBankName: $data['destinationBankName'],
            destinationAccountNumber: $data['destinationAccountNumber'],
            destinationBankCode: $data['destinationBankCode']
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
            'status' => $this->status,
            'dateCreated' => $this->dateCreated,
            'totalFee' => $this->totalFee,
            'destinationAccountName' => $this->destinationAccountName,
            'destinationBankName' => $this->destinationBankName,
            'destinationAccountNumber' => $this->destinationAccountNumber,
            'destinationBankCode' => $this->destinationBankCode,
        ];
    }

    /**
     * Check if transfer is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'PENDING';
    }
}