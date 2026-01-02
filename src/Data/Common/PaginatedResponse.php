<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Data\Common;

/**
 * Generic response for paginated requests
 *
 * @template T
 */
class PaginatedResponse
{
    /**
     * @param PaginatedResponseBody<T> $responseBody
     */
    public function __construct(
        public readonly bool $requestSuccessful,
        public readonly string $responseMessage,
        public readonly string $responseCode,
        public readonly PaginatedResponseBody $responseBody
    ) {
    }

    /**
     * Create from array
     *
     * @template U
     * @param array $data
     * @param callable(array): U $mapper Function to convert item array to object
     * @param ?\Closure(int): static $pageGenerator Function to navigate to other pages. It will
     * receive the page number to navigate to and should call the original method (e.g. listBulkTransfers)
     * that created this object
     * @return self<U>
     */
    public static function fromArray(array $data, callable $mapper, ?callable $pageGenerator = null): self
    {
        return new self(
            requestSuccessful: $data['requestSuccessful'],
            responseMessage: $data['responseMessage'],
            responseCode: $data['responseCode'],
            responseBody: PaginatedResponseBody::fromArray($data['responseBody'], $mapper, $pageGenerator)
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
