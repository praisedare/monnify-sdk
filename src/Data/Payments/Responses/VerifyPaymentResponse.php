<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Data\Payments\Responses;

/**
 * Response DTO for payment verification
 */
class VerifyPaymentResponse
{
    public function __construct(
        public readonly bool $requestSuccessful,
        public readonly string $responseMessage,
        public readonly string $responseCode,
        public readonly VerifyPaymentResponseBody $responseBody,
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
            responseBody: VerifyPaymentResponseBody::fromArray($data['responseBody']),
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

    /**
     * Check if payment is successful
     */
    public function isSuccessful(): bool
    {
        return $this->responseBody->paymentStatus === 'PAID';
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return $this->responseBody->paymentStatus === 'PENDING';
    }

    /**
     * Check if payment failed
     */
    public function isFailed(): bool
    {
        return $this->responseBody->paymentStatus === 'FAILED';
    }

    /**
     * Check if payment expired
     */
    public function isExpired(): bool
    {
        return $this->responseBody->paymentStatus === 'EXPIRED';
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus(): string
    {
        return $this->responseBody->paymentStatus;
    }

    /**
     * Get transaction reference
     */
    public function getTransactionReference(): string
    {
        return $this->responseBody->transactionReference;
    }

    /**
     * Get payment reference
     */
    public function getPaymentReference(): string
    {
        return $this->responseBody->paymentReference;
    }

    /**
     * Get amount paid
     */
    public function getAmountPaid(): float
    {
        return (float) $this->responseBody->amountPaid;
    }

    /**
     * Get total payable
     */
    public function getTotalPayable(): float
    {
        return (float) $this->responseBody->totalPayable;
    }
}

/**
 * Response body DTO for payment verification
 */
class VerifyPaymentResponseBody
{
    public function __construct(
        public readonly string $transactionReference,
        public readonly string $paymentReference,
        public readonly string $amountPaid,
        public readonly string $totalPayable,
        public readonly ?string $settlementAmount,
        public readonly ?string $paidOn,
        public readonly string $paymentStatus,
        public readonly string $paymentDescription,
        public readonly string $currency,
        public readonly ?string $paymentMethod,
        public readonly ?ProductDetails $product,
        public readonly ?CardDetails $cardDetails,
        public readonly ?AccountDetails $accountDetails,
        public readonly array $accountPayments,
        public readonly CustomerDetails $customer,
        public readonly array $metaData,
    ) {
    }

    /**
     * Create from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            transactionReference: $data['transactionReference'],
            paymentReference: $data['paymentReference'],
            amountPaid: $data['amountPaid'],
            totalPayable: $data['totalPayable'],
            settlementAmount: $data['settlementAmount'] ?? null,
            paidOn: $data['paidOn'] ?? null,
            paymentStatus: $data['paymentStatus'],
            paymentDescription: $data['paymentDescription'],
            currency: $data['currency'],
            paymentMethod: @$data['paymentMethod'],
            product: @$data['product'] ? ProductDetails::fromArray($data['product']) : null,
            cardDetails: isset($data['cardDetails']) ? CardDetails::fromArray($data['cardDetails']) : null,
            accountDetails: isset($data['accountDetails']) ? AccountDetails::fromArray($data['accountDetails']) : null,
            accountPayments: array_map(fn($payment) => AccountPaymentDetails::fromArray($payment), $data['accountPayments'] ?? []),
            customer: CustomerDetails::fromArray($data['customer']),
            metaData: $data['metaData'] ?? [],
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'transactionReference' => $this->transactionReference,
            'paymentReference' => $this->paymentReference,
            'amountPaid' => $this->amountPaid,
            'totalPayable' => $this->totalPayable,
            'settlementAmount' => $this->settlementAmount,
            'paidOn' => $this->paidOn,
            'paymentStatus' => $this->paymentStatus,
            'paymentDescription' => $this->paymentDescription,
            'currency' => $this->currency,
            'paymentMethod' => $this->paymentMethod,
            'product' => $this->product?->toArray(),
            'cardDetails' => $this->cardDetails?->toArray(),
            'accountDetails' => $this->accountDetails?->toArray(),
            'accountPayments' => array_map(fn($payment) => $payment->toArray(), $this->accountPayments),
            'customer' => $this->customer->toArray(),
            'metaData' => $this->metaData,
        ];
    }
}

/**
 * Product details DTO
 */
class ProductDetails
{
    public function __construct(
        public readonly string $type,
        public readonly string $reference,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            type: $data['type'],
            reference: $data['reference'],
        );
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'reference' => $this->reference,
        ];
    }
}

/**
 * Card details DTO
 */
class CardDetails
{
    public function __construct(
        public readonly string $cardType,
        public readonly string $last4,
        public readonly string $expMonth,
        public readonly string $expYear,
        public readonly string $bin,
        public readonly ?string $bankCode,
        public readonly ?string $bankName,
        public readonly bool $reusable,
        public readonly ?string $countryCode,
        public readonly ?string $cardToken,
        public readonly bool $supportsTokenization,
        public readonly string $maskedPan,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            cardType: $data['cardType'],
            last4: $data['last4'],
            expMonth: $data['expMonth'],
            expYear: $data['expYear'],
            bin: $data['bin'],
            bankCode: $data['bankCode'] ?? null,
            bankName: $data['bankName'] ?? null,
            reusable: $data['reusable'],
            countryCode: $data['countryCode'] ?? null,
            cardToken: $data['cardToken'] ?? null,
            supportsTokenization: $data['supportsTokenization'],
            maskedPan: $data['maskedPan'],
        );
    }

    public function toArray(): array
    {
        return [
            'cardType' => $this->cardType,
            'last4' => $this->last4,
            'expMonth' => $this->expMonth,
            'expYear' => $this->expYear,
            'bin' => $this->bin,
            'bankCode' => $this->bankCode,
            'bankName' => $this->bankName,
            'reusable' => $this->reusable,
            'countryCode' => $this->countryCode,
            'cardToken' => $this->cardToken,
            'supportsTokenization' => $this->supportsTokenization,
            'maskedPan' => $this->maskedPan,
        ];
    }
}

/**
 * Account details DTO
 */
class AccountDetails
{
    public function __construct(
        public readonly string $accountName,
        public readonly string $accountNumber,
        public readonly string $bankCode,
        public readonly string $amountPaid,
        public readonly string $sessionId,
        public readonly string $destinationAccountNumber,
        public readonly ?string $destinationAccountName,
        public readonly string $destinationBankCode,
        public readonly string $destinationBankName,
        public readonly ?string $bankName,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            accountName: $data['accountName'],
            accountNumber: $data['accountNumber'],
            bankCode: $data['bankCode'],
            amountPaid: $data['amountPaid'],
            sessionId: $data['sessionId'],
            destinationAccountNumber: $data['destinationAccountNumber'],
            destinationAccountName: $data['destinationAccountName'] ?? null,
            destinationBankCode: $data['destinationBankCode'],
            destinationBankName: $data['destinationBankName'],
            bankName: $data['bankName'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'accountName' => $this->accountName,
            'accountNumber' => $this->accountNumber,
            'bankCode' => $this->bankCode,
            'amountPaid' => $this->amountPaid,
            'sessionId' => $this->sessionId,
            'destinationAccountNumber' => $this->destinationAccountNumber,
            'destinationAccountName' => $this->destinationAccountName,
            'destinationBankCode' => $this->destinationBankCode,
            'destinationBankName' => $this->destinationBankName,
            'bankName' => $this->bankName,
        ];
    }
}

/**
 * Account payment details DTO
 */
class AccountPaymentDetails
{
    public function __construct(
        public readonly string $accountName,
        public readonly string $accountNumber,
        public readonly string $bankCode,
        public readonly string $amountPaid,
        public readonly string $sessionId,
        public readonly string $destinationAccountNumber,
        public readonly ?string $destinationAccountName,
        public readonly string $destinationBankCode,
        public readonly string $destinationBankName,
        public readonly ?string $bankName,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            accountName: $data['accountName'],
            accountNumber: $data['accountNumber'],
            bankCode: $data['bankCode'],
            amountPaid: $data['amountPaid'],
            sessionId: $data['sessionId'],
            destinationAccountNumber: $data['destinationAccountNumber'],
            destinationAccountName: $data['destinationAccountName'] ?? null,
            destinationBankCode: $data['destinationBankCode'],
            destinationBankName: $data['destinationBankName'],
            bankName: $data['bankName'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'accountName' => $this->accountName,
            'accountNumber' => $this->accountNumber,
            'bankCode' => $this->bankCode,
            'amountPaid' => $this->amountPaid,
            'sessionId' => $this->sessionId,
            'destinationAccountNumber' => $this->destinationAccountNumber,
            'destinationAccountName' => $this->destinationAccountName,
            'destinationBankCode' => $this->destinationBankCode,
            'destinationBankName' => $this->destinationBankName,
            'bankName' => $this->bankName,
        ];
    }
}

/**
 * Customer details DTO
 */
class CustomerDetails
{
    public function __construct(
        public readonly string $email,
        public readonly string $name,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'],
            name: $data['name'],
        );
    }

    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'name' => $this->name,
        ];
    }
}