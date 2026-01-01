<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Data\Common;

/**
 * Sort information
 */
class Sort
{
    public function __construct(
        public readonly bool $sorted,
        public readonly bool $unsorted,
        public readonly bool $empty
    ) {
    }

    /**
     * Create from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            sorted: $data['sorted'],
            unsorted: $data['unsorted'],
            empty: $data['empty']
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'sorted' => $this->sorted,
            'unsorted' => $this->unsorted,
            'empty' => $this->empty,
        ];
    }
}
