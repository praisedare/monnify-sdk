<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Data;

use PraiseDare\Monnify\Exceptions\ValidationException;

/**
 * Data transfer object for bulk transfer authorization operations
 */
class BulkAuthorizationData
{
    public function __construct(
        public readonly string $reference,
        public readonly string $authorizationCode,
    ) {
        $this->validate();
    }

    /**
     * Validate the bulk authorization data
     *
     * @throws ValidationException
     */
    private function validate(): void
    {
        if (empty($this->reference)) {
            throw new ValidationException('Batch reference is required', 'batchReference');
        }

        if (empty($this->authorizationCode)) {
            throw new ValidationException('Authorization code is required', 'authorizationCode');
        }
    }

    /**
     * Create BulkAuthorizationData from array
     *
     * @param array{
     *  batchReference: string,
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