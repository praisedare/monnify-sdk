<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Exceptions;

/**
 * Validation Exception for Monnify SDK
 *
 * Thrown when data validation fails in data transfer objects
 */
class ValidationException extends MonnifyException
{
    public function __construct(string $message, ?string $field = null)
    {
        $fullMessage = $field ? "Field '{$field}': {$message}" : $message;
        parent::__construct(
            message: $fullMessage,
            code: 400,
            previous: null,
            errorCode: 'VALIDATION_ERROR'
        );
    }
}