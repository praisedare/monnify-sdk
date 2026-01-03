<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Data\Transfers;

use PraiseDare\Monnify\Exceptions\ValidationException;

/**
 * Data transfer object for bulk transfer operations
 */
class BulkTransferInitializationData
{
    /**
     * @param TransferInitializationData[] $transactionList
     */
    public function __construct(
        public readonly string $title,
        public readonly string $batchReference,
        public readonly string $narration,
        public readonly string $sourceAccountNumber,
        public readonly array $transactionList,
        public readonly string $currency = 'NGN',
        public readonly string $onValidationFailure = 'CONTINUE',
        /**
         * Used to determine how often Monnify should notify the merchant of progress when processing a batch transfer. The options are and 25, 50, 75 and 100.
         * @var int
         */
        public readonly int $notificationInterval = 25,
    ) {
        $this->validate();
    }

    /**
     * Validate the bulk transfer data
     *
     * @throws ValidationException
     */
    private function validate(): void
    {
        if (empty($this->title)) {
            throw new ValidationException('Title is required', 'title');
        }

        if (empty($this->batchReference)) {
            throw new ValidationException('Batch reference is required', 'batchReference');
        }

        if (empty($this->narration)) {
            throw new ValidationException('Narration is required', 'narration');
        }

        if (empty($this->sourceAccountNumber)) {
            throw new ValidationException('Source account number is required', 'sourceAccountNumber');
        }

        if (empty($this->transactionList) || !is_array($this->transactionList)) {
            throw new ValidationException('Transaction list cannot be null or empty', 'transactions');
        }

        if ($this->notificationInterval % 25
            || $this->notificationInterval < 25
            || $this->notificationInterval > 100
        ) {
            throw new ValidationException('Invalid notification interval. '
                .'Must be a multiple of 25 and be between 25 and 100.');
        }

        foreach ($this->transactionList as $index => $transaction) {
            if (!$transaction instanceof TransferInitializationData) {
                throw new ValidationException("Transaction at index {$index} must be an instance of TransferData", "transactions[{$index}]");
            }
        }
    }

    /**
     * Create BulkTransferData from array
     *
     * @param array{
     *  title: string,
     *  batchReference: string,
     *  narration: string,
     *  sourceAccountNumber: string,
     *  currency?: string,
     *  onValidationFailure?: string,
     *  notificationInterval?: int,
     *  transactionList: array<array{
     *    amount: float,
     *    reference: string,
     *    narration: string,
     *    destinationBankCode: string,
     *    destinationAccountNumber: string,
     *    beneficiaryEmail?: string,
     *    beneficiaryPhone?: string,
     *    metadata?: array<string, mixed>
     *    currency?: string,
     *  }>
     * } $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $transactionList = array_map(
            fn($transaction) => TransferInitializationData::fromArray($transaction, isBulkTransferItem: true),
            $data['transactionList']
        );

        return new self(
            title: $data['title'],
            batchReference: $data['batchReference'],
            narration: $data['narration'],
            sourceAccountNumber: $data['sourceAccountNumber'],
            transactionList: $transactionList,
            onValidationFailure: $data['onValidationFailure'] ?? 'CONTINUE',
            notificationInterval: $data['notificationInterval'] ?? 5
        );
    }

    /**
     * Convert to array for API request
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'batchReference' => $this->batchReference,
            'narration' => $this->narration,
            'sourceAccountNumber' => $this->sourceAccountNumber,
            'currency' => $this->currency,
            'onValidationFailure' => $this->onValidationFailure,
            'notificationInterval' => $this->notificationInterval,
            'transactionList' => array_map(fn(TransferInitializationData $transaction) => $transaction->toArray(), $this->transactionList),
        ];
    }
}