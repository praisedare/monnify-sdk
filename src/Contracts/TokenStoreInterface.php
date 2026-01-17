<?php

namespace PraiseDare\Monnify\Contracts;

interface TokenStoreInterface {
    /**
     * @param callable $refreshCallback Should return ['token' => string, 'expires_in' => int]
     */
    public function getToken(callable $refreshCallback): string;
}
