<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Data\Transfers;

use PraiseDare\Monnify\Exceptions\ValidationException;

/**
 * Data transfer object for single transfer operations
 */
class TransferInitializationData
{
    public function __construct(
        public readonly float $amount,
        public readonly string $reference,
        public readonly string $narration,
        public readonly string $destinationBankCode,
        public readonly string $destinationAccountNumber,
        public readonly string $destinationAccountName,
        /**
         * Nullable if the transfer is part of a bulk transfer.
         */
        public readonly ?string $sourceAccountNumber = null,
        public readonly string $currency = 'NGN',
        public readonly ?string $beneficiaryEmail = null,
        public readonly ?string $beneficiaryPhone = null,
        public readonly ?array $metadata = null,
        /**
         * If true, then a sourceAccountNumber isn't required as it would have
         * already been set in the parent bulk transfer.
         */
        public bool $isBulkTransferItem = false,
    ) {
        $this->validate();
    }

    /**
     * Validate the transfer data
     *
     * @throws ValidationException
     */
    private function validate(): void
    {
        if ($this->amount <= 0) {
            throw new ValidationException('Amount must be greater than 0', 'amount');
        }

        if (empty($this->reference)) {
            throw new ValidationException('Reference is required', 'reference');
        }

        if (empty($this->narration)) {
            throw new ValidationException('Narration is required', 'narration');
        }

        if (empty($this->destinationBankCode)) {
            throw new ValidationException('Destination bank code is required', 'destinationBankCode');
        }

        if (empty($this->destinationAccountNumber)) {
            throw new ValidationException('Destination account number is required', 'destinationAccountNumber');
        }

        if (empty($this->destinationAccountName)) {
            throw new ValidationException('Destination account name is required', 'destinationAccountName');
        }

        if (!$this->isBulkTransferItem && empty($this->sourceAccountNumber)) {
            throw new ValidationException('Source account number is required for single transfers', 'sourceAccountNumber');
        }
    }

    /**
     * Create TransferData from array
     *
     * @param array{
     *  amount: float,
     *  reference: string,
     *  narration: string,
     *  destinationBankCode: string,
     *  destinationAccountNumber: string,
     *  destinationAccountName: string,
     *  sourceAccountNumber: string,
     *  currency?: string,
     *  beneficiaryEmail?: string,
     *  beneficiaryPhone?: string,
     *  metadata?: array<string, mixed>
     * } $data
     * @return self
     */
    public static function fromArray(array $data, $isBulkTransferItem = false): self
    {
        return new self(
            amount: $data['amount'],
            reference: $data['reference'],
            narration: $data['narration'],
            destinationBankCode: $data['destinationBankCode'],
            destinationAccountNumber: $data['destinationAccountNumber'],
            destinationAccountName: $data['destinationAccountName'],
            sourceAccountNumber: $data['sourceAccountNumber'],
            currency: $data['currency'] ?? 'NGN',
            beneficiaryEmail: $data['beneficiaryEmail'] ?? null,
            beneficiaryPhone: $data['beneficiaryPhone'] ?? null,
            metadata: $data['metadata'] ?? null,
            isBulkTransferItem: $isBulkTransferItem,
        );
    }

    /**
     * Convert to array for API request
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [
            'amount' => $this->amount,
            'reference' => $this->reference,
            'narration' => $this->narration,
            'destinationBankCode' => $this->destinationBankCode,
            'destinationAccountNumber' => $this->destinationAccountNumber,
            'destinationAccountName' => $this->destinationAccountName,
            'currency' => $this->currency,
        ];

        if ($this->beneficiaryEmail !== null) {
            $payload['beneficiaryEmail'] = $this->beneficiaryEmail;
        }

        if ($this->beneficiaryPhone !== null) {
            $payload['beneficiaryPhone'] = $this->beneficiaryPhone;
        }

        if ($this->metadata !== null) {
            $payload['metadata'] = $this->metadata;
        }

        if ($this->sourceAccountNumber !== null) {
            $payload['sourceAccountNumber'] = $this->sourceAccountNumber;
        }

        return $payload;
    }
}