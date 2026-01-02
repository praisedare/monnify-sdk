<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Data\Transfers;

class BulkTransferDetails
{
    public function __construct(
        public float $totalAmount,
        public float $totalFee,
        public string $batchReference,
        public string $transactionBatchReference,
        public string $batchStatus,
        public int $totalTransactionsCount,
        public string $dateCreated,
    )
    {}

    /**
     * @var array{
     *  totalAmount: int,
     *  totalFee: float,
     *  batchReference: string,
     *  transactionBatchReference: string,
     *  batchStatus: string,
     *  totalTransactionsCount: int,
     *  dateCreated: string
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(...$data);
    }
}