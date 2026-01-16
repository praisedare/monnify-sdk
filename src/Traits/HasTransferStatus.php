<?php

namespace PraiseDare\Monnify\Traits;

use PraiseDare\Monnify\Enums\TransferStatus;

/**
 * @property string $status The status of the transfer. This field is mandatory for any class wishing to use this trait!
 */
trait HasTransferStatus
{

    public function getStatusEnum(): TransferStatus
    {
        return TransferStatus::from($this->status);
    }

    /**
     * Check if transfer is pending authorization (2FA enabled)
     */
    public function isPendingAuthorization(): bool
    {
        return $this->getStatusEnum() == TransferStatus::PENDING_AUTHORIZATION;
    }

    /**
     * Check if transfer was successful (2FA disabled)
     */
    public function isSuccessful(): bool
    {
        return $this->getStatusEnum()->isSuccessful();
    }

    public function isPending(): bool
    {
        return $this->getStatusEnum()->isPending();
    }

    /**
     * Check if transfer failed
     */
    public function isFailed(): bool
    {
        return $this->getStatusEnum()->isFailed();
    }


}
