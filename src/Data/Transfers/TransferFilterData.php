<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Data\Transfers;

/**
 * Data transfer object for transfer filter operations
 */
class TransferFilterData
{
    public function __construct(
        public readonly int $pageNo = 1,
        public readonly int $pageSize = 10,
        public readonly ?string $from = null,
        public readonly ?string $startDate = null,
        public readonly ?string $endDate = null,
        public readonly ?string $transactionReference = null,
        public readonly ?string $sourceAccountNumber = null,
        public readonly ?int $amountFrom = null,
        public readonly ?int $amountTo = null,
    ) {
        assert($pageNo >= 1, '$pageNo must be at least 1');
        assert($pageSize >= 5, '$pageNo must be at least 5');
    }

    /**
     * Create TransferFilterData from array
     *
     * @param array{
     *  pageNo: int,
     *  pageSize: int,
     *  from?: string,
     *  startDate?: string,
     *  endDate?: string,
     *  transactionReference?: string,
     *  sourceAccountNumber?: string,
     *  amountFrom?: int,
     *  amountTo?: int,
     * } $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            pageNo: $data['pageNo'],
            pageSize: $data['pageSize'],
            startDate: $data['startDate'] ?? null,
            endDate: $data['endDate'] ?? null,
            transactionReference: $data['transactionReference'] ?? null,
            sourceAccountNumber: $data['sourceAccountNumber'] ?? null,
            amountFrom: $data['amountFrom'] ?? null,
            amountTo: $data['amountTo'] ?? null,
        );
    }

    /**
     * Convert to array for API request
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $params = [];

        if ($this->pageNo !== null) {
            $params['pageNo'] = $this->pageNo;
        }

        if ($this->pageSize !== null) {
            $params['pageSize'] = $this->pageSize;
        }

        if ($this->from !== null) {
            $params['from'] = $this->from;
        }

        if ($this->startDate !== null) {
            $params['startDate'] = $this->startDate;
        }

        if ($this->endDate !== null) {
            $params['endDate'] = $this->endDate;
        }

        if ($this->transactionReference !== null) {
            $params['transactionReference'] = $this->transactionReference;
        }

        if ($this->sourceAccountNumber !== null) {
            $params['sourceAccountNumber'] = $this->sourceAccountNumber;
        }

        if ($this->amountFrom !== null) {
            $params['amountFrom'] = $this->amountFrom;
        }

        if ($this->amountTo !== null) {
            $params['amountTo'] = $this->amountTo;
        }

        return $params;
    }

    /**
     * Build query string for API request
     */
    public function toQueryString(): string
    {
        $params = $this->toArray();
        return !empty($params) ? '?' . http_build_query($params) : '';
    }
}