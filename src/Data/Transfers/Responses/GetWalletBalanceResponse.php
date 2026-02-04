<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Data\Transfers\Responses;

use PraiseDare\Monnify\Data\MonnifyResponse;

/**
 * Response for getting wallet balance
 */
class GetWalletBalanceResponse extends MonnifyResponse
{
    public function __construct(
        bool $requestSuccessful,
        string $responseMessage,
        string $responseCode,
        ?GetWalletBalanceResponseBody $responseBody
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
                ? GetWalletBalanceResponseBody::fromArray($data['responseBody'])
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