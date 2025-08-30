<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Data\Responses;

/**
 * Response for authorizing a single transfer
 */
class AuthorizeSingleTransferResponse
{
    public function __construct(
        public readonly bool $requestSuccessful,
        public readonly string $responseMessage,
        public readonly string $responseCode,
        public readonly AuthorizeSingleTransferResponseBody $responseBody
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
            responseBody: AuthorizeSingleTransferResponseBody::fromArray($data['responseBody'])
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
 * Response body for authorizing a single transfer
 */
class AuthorizeSingleTransferResponseBody
{
    public function __construct(
        public readonly float $amount,
        public readonly string $reference,
        public readonly string $status,
        public readonly string $dateCreated
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
            dateCreated: $data['dateCreated']
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
        ];
    }

    /**
     * Check if authorization was successful
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'SUCCESS';
    }
}