<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Data\Transfers\Responses;

use PraiseDare\Monnify\Data\Transfers\TransferDetails;
use PraiseDare\Monnify\Data\MonnifyResponse;

/**
 * Response for getting single transfer status
 */
class GetSingleTransferStatusResponse extends MonnifyResponse
{
    public function __construct(
        bool $requestSuccessful,
        string $responseMessage,
        string $responseCode,
        ?TransferDetails $responseBody
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
                ? TransferDetails::fromArray($data['responseBody'])
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
