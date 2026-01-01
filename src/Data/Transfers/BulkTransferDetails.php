<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Data\Transfers;

class BulkTransferDetails
{
    public function __construct(
        public int $totalAmount,
        public float $totalFee,
        public string $batchReference,
        public string $batchStatus,
        public int $totalTransactionsCount,
        public string $dateCreated,
    )
    {}

    /**
     * @var array{totalAmount: int, totalFee: float, batchReference: string, batchStatus: string, totalTransactionsCount: int, dateCreated: string} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(...$data);
    }
}