<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Data\Payments;

/**
 * Payment Data DTO for payment initialization
 */
class PaymentData
{
    public function __construct(
        public readonly float $amount,
        public readonly string $customerName,
        public readonly string $customerEmail,
        public readonly string $paymentReference,
        public readonly string $redirectUrl,
        public readonly ?string $paymentDescription = null,
        public readonly ?string $currencyCode = null,
        public readonly ?string $contractCode = null,
        public readonly ?array $paymentMethods = null,
        public readonly ?string $customerPhone = null,
        public readonly ?array $metadata = null,
    ) {
    }

    /**
     * Convert to array for API request
     */
    public function toArray(): array
    {
        $data = [
            'amount' => $this->amount,
            'customerName' => $this->customerName,
            'customerEmail' => $this->customerEmail,
            'paymentReference' => $this->paymentReference,
            'paymentDescription' => $this->paymentDescription ?? 'Payment for services',
            'currencyCode' => $this->currencyCode ?? 'NGN',
            'redirectUrl' => $this->redirectUrl,
            'paymentMethods' => $this->paymentMethods ?? ['CARD', 'ACCOUNT_TRANSFER', 'USSD'],
        ];

        if ($this->contractCode !== null) {
            $data['contractCode'] = $this->contractCode;
        }

        if ($this->customerPhone !== null) {
            $data['customerPhone'] = $this->customerPhone;
        }

        if ($this->metadata !== null) {
            $data['metadata'] = $this->metadata;
        }

        return $data;
    }

    /**
     * Create from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            amount: $data['amount'],
            customerName: $data['customerName'],
            customerEmail: $data['customerEmail'],
            paymentReference: $data['paymentReference'],
            redirectUrl: $data['redirectUrl'],
            paymentDescription: $data['paymentDescription'] ?? null,
            currencyCode: $data['currencyCode'] ?? null,
            contractCode: $data['contractCode'] ?? null,
            paymentMethods: $data['paymentMethods'] ?? null,
            customerPhone: $data['customerPhone'] ?? null,
            metadata: $data['metadata'] ?? null,
        );
    }
}