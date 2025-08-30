<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Data;

use PraiseDare\Monnify\Exceptions\ValidationException;

/**
 * Data transfer object for bulk transfer operations
 */
class BulkTransferData
{
    /**
     * @param TransferData[] $transactionList
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
         * Used to determine how often Monnify should notify the merchant of progress when processing a batch transfer. The options are 10, 20, 50 and 100 and they represent percentages. i.e. 20 means notify me at intervals of 20% (20%, 40%, 60%, 80% ,100%).
         * @var int
         */
        public readonly int $notificationInterval = 5,
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
            throw new ValidationException('Transactions must be a non-empty array', 'transactions');
        }

        foreach ($this->transactionList as $index => $transaction) {
            if (!$transaction instanceof TransferData) {
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
     *    destinationAccountName: string,
     *    beneficiaryEmail?: string,
     *    beneficiaryPhone?: string,
     *    metadata?: array<string, mixed>
     *  }>
     * } $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $transactionList = array_map(
            fn($transaction) => TransferData::fromArray($transaction),
            $data['transactionList']
        );

        return new self(
            title: $data['title'],
            batchReference: $data['batchReference'],
            narration: $data['narration'],
            sourceAccountNumber: $data['sourceAccountNumber'],
            transactionList: $transactionList,
            currency: $data['currency'] ?? 'NGN',
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
            'transactionList' => array_map(fn(TransferData $transaction) => $transaction->toArray(), $this->transactionList),
        ];
    }
}