<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Data\Transfers;

/**
 * Single Transfer Data
 */
class TransferDetails
{
    /**
     * @param string $reference The user-supplied reference
     * @param string $transactionReference The monnify-generated reference for the transaction
     * @param ?string $destinationAccountName Null if the account could not be resolved
     * @param ?string $destinationBankName Null if the account could not be resolved
     */
    public function __construct(
        public readonly float $amount,
        public readonly string $reference,
        public readonly string $narration,
        public readonly string $currency,
        public readonly float $fee,
        public readonly bool $twoFaEnabled,
        public readonly string $status,
        public readonly string $transactionDescription,
        public readonly string $transactionReference,
        public readonly string $destinationBankCode,
        public readonly string $sourceAccountNumber,
        public readonly string $destinationAccountNumber,
        public readonly ?string $destinationAccountName,
        public readonly ?string $destinationBankName,
        public readonly string $createdOn
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
            narration: $data['narration'],
            currency: $data['currency'],
            fee: $data['fee'],
            twoFaEnabled: $data['twoFaEnabled'],
            status: $data['status'],
            sourceAccountNumber: $data['sourceAccountNumber'],
            transactionDescription: $data['transactionDescription'],
            transactionReference: $data['transactionReference'],
            destinationBankCode: $data['destinationBankCode'],
            destinationAccountNumber: $data['destinationAccountNumber'],
            destinationAccountName: $data['destinationAccountName'] ?? null,
            destinationBankName: $data['destinationBankName'],
            createdOn: $data['createdOn']
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
            'narration' => $this->narration,
            'currency' => $this->currency,
            'fee' => $this->fee,
            'twoFaEnabled' => $this->twoFaEnabled,
            'status' => $this->status,
            'transactionDescription' => $this->transactionDescription,
            'transactionReference' => $this->transactionReference,
            'destinationBankCode' => $this->destinationBankCode,
            'destinationAccountNumber' => $this->destinationAccountNumber,
            'destinationAccountName' => $this->destinationAccountName,
            'destinationBankName' => $this->destinationBankName,
            'createdOn' => $this->createdOn,
        ];
    }

    /**
     * Check if transfer was successful
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'SUCCESS';
    }

    /**
     * Check if transfer is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'PENDING';
    }

    /**
     * Check if transfer failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'FAILED';
    }

    /**
     * Check if 2FA was enabled for this transfer
     */
    public function isTwoFaEnabled(): bool
    {
        return $this->twoFaEnabled;
    }

    /**
     * Get the net amount (amount minus fee)
     */
    public function getNetAmount(): float
    {
        return $this->amount - $this->fee;
    }
}