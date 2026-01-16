<?php

namespace PraiseDare\Monnify\Enums;

enum TransferStatus: string
{
    /**
     * This response is gotten when transaction is still pending.
     */
    case PENDING = 'PENDING';
    /**
     * This response is gotten when transaction is still pending.
     */
    case AWAITING_PROCESSING = 'AWAITING_PROCESSING';
    /**
     * This response is gotten when transaction is still pending.
     */
    case IN_PROGRESS = 'IN_PROGRESS';
    /**
     * This response is gotten if the merchant has 2FA enabled and authorization is needed for the disbursement to be initiated.
     */
    case PENDING_AUTHORIZATION = 'PENDING_AUTHORIZATION';
    /**
     * This is very rare, but it happens when Monnify failed to send the OTP.
     */
    case OTP_EMAIL_DISPATCH_FAILED = 'OTP_EMAIL_DISPATCH_FAILED';
    /**
     * This response is gotten if the disbursement was successful.
     */
    case SUCCESS = 'SUCCESS';
    /**
     * This response is gotten if the disbursement was successful.
     */
    case COMPLETED = 'COMPLETED';
    /**
     * This response is gotten if disbursement was reversed.
     */
    case REVERSED = 'REVERSED';
    /**
     * This response is gotten when disbursement was not successful.
     */
    case FAILED = 'FAILED';
    /**
     * A batch transaction has an expiry time. Once the transaction time has elapsed, you will get an EXPIRED response.
     */
    case EXPIRED = 'EXPIRED';

    public static function pendingStatuses(): array
    {
        return [
            self::PENDING,
            self::PENDING_AUTHORIZATION,
            self::AWAITING_PROCESSING,
            self::IN_PROGRESS,
        ];
    }

    public static function successfulStatuses(): array
    {
        return [
            self::SUCCESS,
            self::COMPLETED,
        ];
    }

    public static function failedStatuses(): array
    {
        return [
            self::OTP_EMAIL_DISPATCH_FAILED,
            self::REVERSED,
            self::FAILED,
            self::EXPIRED,
        ];
    }
    public function isPending()
    {
        return in_array($this, self::pendingStatuses());
    }

    public function isSuccessful()
    {
        return in_array($this, self::successfulStatuses());
    }

    public function isFailed()
    {
        return in_array($this, self::failedStatuses());
    }

}
