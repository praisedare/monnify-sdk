<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Data;

use PraiseDare\Monnify\Exceptions\ValidationException;

/**
 * Data transfer object for transfer authorization operations
 */
class AuthorizationData
{
    public function __construct(
        public readonly string $reference,
        public readonly string $authorizationCode,
    ) {
        $this->validate();
    }

    /**
     * Validate the authorization data
     *
     * @throws ValidationException
     */
    private function validate(): void
    {
        if (empty($this->reference)) {
            throw new ValidationException('Reference is required', 'reference');
        }

        if (empty($this->authorizationCode)) {
            throw new ValidationException('Authorization code is required', 'authorizationCode');
        }
    }

    /**
     * Create AuthorizationData from array
     *
     * @param array{
     *  reference: string,
     *  authorizationCode: string
     * } $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            reference: $data['reference'],
            authorizationCode: $data['authorizationCode']
        );
    }

    /**
     * Convert to array for API request
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'reference' => $this->reference,
            'authorizationCode' => $this->authorizationCode,
        ];
    }
}