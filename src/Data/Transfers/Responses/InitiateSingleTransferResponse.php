<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Data\Transfers\Responses;

use PraiseDare\Monnify\Traits\HasTransferStatus;
use PraiseDare\Monnify\Data\MonnifyResponse;

/**
 * Response for initiating a single transfer
 */
class InitiateSingleTransferResponse extends MonnifyResponse
{
    public function __construct(
        bool $requestSuccessful,
        string $responseMessage,
        string $responseCode,
        ?InitiateSingleTransferResponseBody $responseBody
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
                ? InitiateSingleTransferResponseBody::fromArray($data['responseBody'])
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
 * Response body for initiating a single transfer
 */
class InitiateSingleTransferResponseBody
{
    use HasTransferStatus;

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
     * Get session ID if available (only present for successful transfers)
     */
    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    /**
     * Get failure comment if transfer failed
     */
    public function getFailureComment(): ?string
    {
        return $this->comment;
    }
}