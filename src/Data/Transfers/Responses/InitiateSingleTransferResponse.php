<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Data\Transfers\Responses;

/**
 * Response for initiating a single transfer
 */
class InitiateSingleTransferResponse
{
    public function __construct(
        public readonly bool $requestSuccessful,
        public readonly string $responseMessage,
        public readonly string $responseCode,
        public readonly InitiateSingleTransferResponseBody $responseBody
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
            responseBody: InitiateSingleTransferResponseBody::fromArray($data['responseBody'])
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
 * Response body for initiating a single transfer
 */
class InitiateSingleTransferResponseBody
{
    public function __construct(
        public readonly float $amount,
        public readonly string $reference,
        public readonly string $status,
        public readonly string $dateCreated,
        public readonly float $totalFee,
        public readonly ?string $sessionId,
        public readonly ?string $destinationAccountName,
        public readonly string $destinationBankName,
        public readonly string $destinationAccountNumber,
        public readonly string $destinationBankCode,
        public readonly ?string $comment = null
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
            sessionId: $data['sessionId'] ?? null,
            destinationAccountName: $data['destinationAccountName'] ?? null,
            destinationBankName: $data['destinationBankName'],
            destinationAccountNumber: $data['destinationAccountNumber'],
            destinationBankCode: $data['destinationBankCode'],
            comment: $data['comment'] ?? null
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
            'sessionId' => $this->sessionId,
            'destinationAccountName' => $this->destinationAccountName,
            'destinationBankName' => $this->destinationBankName,
            'destinationAccountNumber' => $this->destinationAccountNumber,
            'destinationBankCode' => $this->destinationBankCode,
            'comment' => $this->comment,
        ];
    }

    /**
     * Check if transfer was successful (2FA disabled)
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'SUCCESS';
    }

    /**
     * Check if transfer is pending authorization (2FA enabled)
     */
    public function isPendingAuthorization(): bool
    {
        return $this->status === 'PENDING_AUTHORIZATION';
    }

    /**
     * Get session ID if available (only present for successful transfers)
     */
    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    /**
     * Check if transfer failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'FAILED';
    }

    /**
     * Get failure comment if transfer failed
     */
    public function getFailureComment(): ?string
    {
        return $this->comment;
    }
}