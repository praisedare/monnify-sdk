<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Data\Transfers\Responses;

use PraiseDare\Monnify\Data\MonnifyResponse;

/**
 * Response for authorizing a single transfer
 * @property ?AuthorizeSingleTransferResponseBody $responseBody
 */
class AuthorizeSingleTransferResponse extends MonnifyResponse
{
    public function __construct(
        bool $requestSuccessful,
        string $responseMessage,
        string $responseCode,
        ?AuthorizeSingleTransferResponseBody $responseBody
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
                ? AuthorizeSingleTransferResponseBody::fromArray($data['responseBody'])
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