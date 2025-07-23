<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Exceptions;

use Exception;

/**
 * Monnify Exception class
 *
 * Handles all Monnify-specific exceptions with error codes and messages
 */
class MonnifyException extends Exception
{
    private ?string $errorCode;

    /**
     * Constructor
     *
     * @param string $message Error message
     * @param int $code HTTP status code
     * @param Exception|null $previous Previous exception
     * @param string|null $errorCode Monnify error code
     */
    public function __construct(
        string $message = '',
        int $code = 0,
        ?Exception $previous = null,
        ?string $errorCode = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->errorCode = $errorCode;
    }

    /**
     * Get Monnify error code
     *
     * @return string|null
     */
    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    /**
     * Check if this is an authentication error
     *
     * @return bool
     */
    public function isAuthenticationError(): bool
    {
        return $this->errorCode === 'AUTH_FAILED' || $this->getCode() === 401;
    }

    /**
     * Check if this is a validation error
     *
     * @return bool
     */
    public function isValidationError(): bool
    {
        return $this->errorCode === 'VALIDATION_ERROR' || $this->getCode() === 400;
    }

    /**
     * Check if this is a not found error
     *
     * @return bool
     */
    public function isNotFoundError(): bool
    {
        return $this->errorCode === 'NOT_FOUND' || $this->getCode() === 404;
    }

    /**
     * Check if this is a server error
     *
     * @return bool
     */
    public function isServerError(): bool
    {
        return $this->getCode() >= 500;
    }

    /**
     * Get error type
     *
     * @return string
     */
    public function getErrorType(): string
    {
        if ($this->isAuthenticationError()) {
            return 'authentication';
        }

        if ($this->isValidationError()) {
            return 'validation';
        }

        if ($this->isNotFoundError()) {
            return 'not_found';
        }

        if ($this->isServerError()) {
            return 'server';
        }

        return 'general';
    }

    /**
     * Get formatted error message
     *
     * @return string
     */
    public function getFormattedMessage(): string
    {
        $message = $this->getMessage();

        if ($this->errorCode) {
            $message = "[{$this->errorCode}] {$message}";
        }

        return $message;
    }
}
