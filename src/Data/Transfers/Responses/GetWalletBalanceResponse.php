<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Data\Transfers\Responses;

/**
 * Response for getting wallet balance
 */
class GetWalletBalanceResponse
{
    public function __construct(
        public readonly bool $requestSuccessful,
        public readonly string $responseMessage,
        public readonly string $responseCode,
        public readonly GetWalletBalanceResponseBody $responseBody
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
            responseBody: GetWalletBalanceResponseBody::fromArray($data['responseBody'])
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
 * Response body for getting wallet balance
 */
class GetWalletBalanceResponseBody
{
    public function __construct(
        public readonly float $availableBalance,
        public readonly float $ledgerBalance
    ) {
    }

    /**
     * Create from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            availableBalance: $data['availableBalance'],
            ledgerBalance: $data['ledgerBalance']
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'availableBalance' => $this->availableBalance,
            'ledgerBalance' => $this->ledgerBalance,
        ];
    }

    /**
     * Get the available balance
     */
    public function getAvailableBalance(): float
    {
        return $this->availableBalance;
    }

    /**
     * Get the ledger balance
     */
    public function getLedgerBalance(): float
    {
        return $this->ledgerBalance;
    }

    /**
     * Check if wallet has sufficient balance for a given amount
     */
    public function hasSufficientBalance(float $amount): bool
    {
        return $this->availableBalance >= $amount;
    }

    /**
     * Get the difference between ledger and available balance
     * This represents pending transactions or holds
     */
    public function getPendingAmount(): float
    {
        return $this->ledgerBalance - $this->availableBalance;
    }

    /**
     * Check if there are any pending transactions
     */
    public function hasPendingTransactions(): bool
    {
        return $this->getPendingAmount() > 0;
    }

    /**
     * Get the percentage of available balance compared to ledger balance
     */
    public function getAvailablePercentage(): float
    {
        if ($this->ledgerBalance === 0.0) {
            return 0.0;
        }
        return ($this->availableBalance / $this->ledgerBalance) * 100;
    }
}