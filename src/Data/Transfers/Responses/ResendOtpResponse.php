<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Data\Transfers\Responses;

/**
 * Response for resending OTP
 */
class ResendOtpResponse
{
    public function __construct(
        public readonly bool $requestSuccessful,
        public readonly string $responseMessage,
        public readonly string $responseCode,
        public readonly ResendOtpResponseBody $responseBody
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
            responseBody: ResendOtpResponseBody::fromArray($data['responseBody'])
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
 * Response body for resending OTP
 */
class ResendOtpResponseBody
{
    public function __construct(
        public readonly string $message
    ) {
    }

    /**
     * Create from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            message: $data['message']
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'message' => $this->message,
        ];
    }

    /**
     * Get the OTP message
     */
    public function getMessage(): string
    {
        return $this->message;
    }
}