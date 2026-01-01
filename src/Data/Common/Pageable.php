<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Data\Common;

/**
 * Pagination information
 */
class Pageable
{
    public function __construct(
        public readonly Sort $sort,
        public readonly int $pageSize,
        public readonly int $pageNumber,
        public readonly int $offset,
        public readonly bool $unpaged,
        public readonly bool $paged
    ) {
    }

    /**
     * Create from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            sort: Sort::fromArray($data['sort']),
            pageSize: $data['pageSize'],
            pageNumber: $data['pageNumber'],
            offset: $data['offset'],
            unpaged: $data['unpaged'],
            paged: $data['paged']
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'sort' => $this->sort->toArray(),
            'pageSize' => $this->pageSize,
            'pageNumber' => $this->pageNumber,
            'offset' => $this->offset,
            'unpaged' => $this->unpaged,
            'paged' => $this->paged,
        ];
    }
}
