<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Data\Common;

/**
 * Generic response body for paginated data
 *
 * @template T
 */
class PaginatedResponseBody
{
    /**
     * @param T[] $content
     */
    public function __construct(
        public readonly array $content,
        public readonly Pageable $pageable,
        public readonly int $totalPages,
        public readonly bool $last,
        public readonly int $totalElements,
        public readonly Sort $sort,
        public readonly bool $first,
        public readonly int $numberOfElements,
        public readonly int $size,
        public readonly int $number,
        public readonly bool $empty
    ) {
    }

    /**
     * Create from array
     *
     * @template U
     * @param array $data
     * @param callable(array): U $mapper Function to convert item array to object
     * @return self<U>
     */
    public static function fromArray(array $data, callable $mapper): self
    {
        $content = array_map($mapper, $data['content']);

        return new self(
            content: $content,
            pageable: Pageable::fromArray($data['pageable']),
            totalPages: $data['totalPages'],
            last: $data['last'],
            totalElements: $data['totalElements'],
            sort: Sort::fromArray($data['sort']),
            first: $data['first'],
            numberOfElements: $data['numberOfElements'],
            size: $data['size'],
            number: $data['number'],
            empty: $data['empty']
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'content' => array_map(fn($item) => method_exists($item, 'toArray') ? $item->toArray() : (array) $item, $this->content),
            'pageable' => $this->pageable->toArray(),
            'totalPages' => $this->totalPages,
            'last' => $this->last,
            'totalElements' => $this->totalElements,
            'sort' => $this->sort->toArray(),
            'first' => $this->first,
            'numberOfElements' => $this->numberOfElements,
            'size' => $this->size,
            'number' => $this->number,
            'empty' => $this->empty,
        ];
    }

    /**
     * Get the content items
     * @return T[]
     */
    public function getItems(): array
    {
        return $this->content;
    }

    /**
     * Check if this is the first page
     */
    public function isFirstPage(): bool
    {
        return $this->first;
    }

    /**
     * Check if this is the last page
     */
    public function isLastPage(): bool
    {
        return $this->last;
    }

    /**
     * Check if there are more pages
     */
    public function hasNextPage(): bool
    {
        return !$this->last;
    }

    /**
     * Check if there are previous pages
     */
    public function hasPreviousPage(): bool
    {
        return !$this->first;
    }
}
