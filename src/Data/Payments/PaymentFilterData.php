<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Data\Payments;

use PraiseDare\Monnify\Exceptions\MonnifyException;

/**
 * Payment Filter Data DTO for filtering transactions
 */
class PaymentFilterData
{
    public function __construct(
        /**
         * The page no. to display. Min value is 1
         */
        public readonly ?int $page = null,
        /**
         * Page size (number of items displayed per page)
         */
        public readonly ?int $size = null,
        public readonly ?string $fromDate = null,
        public readonly ?string $toDate = null,
        public readonly ?string $status = null,
    ) {
        if (isset($page) && $page < 1)
            throw new MonnifyException('Page number cannot be less than 1', 422, null, null);
    }

    /**
     * Convert to query parameters array
     */
    public function toQueryParams(): array
    {
        $params = [];

        if ($this->page !== null) {
            $params['page'] = (string) $this->page;
        }

        if ($this->size !== null) {
            $params['size'] = (string) $this->size;
        }

        if ($this->fromDate !== null) {
            $params['fromDate'] = $this->fromDate;
        }

        if ($this->toDate !== null) {
            $params['toDate'] = $this->toDate;
        }

        if ($this->status !== null) {
            $params['status'] = $this->status;
        }

        return $params;
    }

    /**
     * Create from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            page: isset($data['page']) ? (int) $data['page'] : null,
            size: isset($data['size']) ? (int) $data['size'] : null,
            fromDate: $data['fromDate'] ?? null,
            toDate: $data['toDate'] ?? null,
            status: $data['status'] ?? null,
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'page' => $this->page,
            'size' => $this->size,
            'fromDate' => $this->fromDate,
            'toDate' => $this->toDate,
            'status' => $this->status,
        ];
    }
}
