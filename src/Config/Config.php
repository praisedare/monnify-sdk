<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Config;

/**
 * Configuration class for Monnify SDK
 *
 * Handles all configuration settings and validation
 */
class Config
{
    private string $secretKey;
    private string $apiKey;
    private string $contractCode;
    private string $environment;
    private int $timeout;
    private bool $verifySsl;
    private string $baseUrl;

    /**
     * Constructor
     *
     * @param array $config Configuration array
     */
    public function __construct(array $config = [])
    {
        $this->secretKey = $config['secret_key'] ?? '';
        $this->apiKey = $config['api_key'] ?? '';
        $this->contractCode = $config['contract_code'] ?? '';
        $this->environment = $config['environment'] ?? 'sandbox';
        $this->timeout = $config['timeout'] ?? 30;
        $this->verifySsl = $config['verify_ssl'] ?? true;

        $this->validate();
        $this->setBaseUrl();
    }

    /**
     * Validate configuration
     *
     * @throws \InvalidArgumentException
     */
    private function validate(): void
    {
        if (empty($this->secretKey)) {
            throw new \InvalidArgumentException('Secret key is required');
        }

        if (empty($this->apiKey)) {
            throw new \InvalidArgumentException('API key is required');
        }

        if (empty($this->contractCode)) {
            throw new \InvalidArgumentException('Contract code is required');
        }

        if (!in_array($this->environment, ['sandbox', 'live'])) {
            throw new \InvalidArgumentException('Environment must be either "sandbox" or "live"');
        }

        if ($this->timeout < 1) {
            throw new \InvalidArgumentException('Timeout must be at least 1 second');
        }
    }

    /**
     * Set base URL based on environment
     */
    private function setBaseUrl(): void
    {
        $this->baseUrl = $this->environment === 'live'
            ? 'https://api.monnify.com'
            : 'https://sandbox.monnify.com';
    }

    /**
     * Get secret key
     *
     * @return string
     */
    public function getSecretKey(): string
    {
        return $this->secretKey;
    }

    /**
     * Get API key
     *
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * Get contract code
     *
     * @return string
     */
    public function getContractCode(): string
    {
        return $this->contractCode;
    }

    /**
     * Get environment
     *
     * @return string
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * Get timeout
     *
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * Get SSL verification setting
     *
     * @return bool
     */
    public function getVerifySsl(): bool
    {
        return $this->verifySsl;
    }

    /**
     * Get base URL
     *
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Get all configuration as array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'secret_key' => $this->secretKey,
            'api_key' => $this->apiKey,
            'contract_code' => $this->contractCode,
            'environment' => $this->environment,
            'timeout' => $this->timeout,
            'verify_ssl' => $this->verifySsl,
            'base_url' => $this->baseUrl,
        ];
    }
}
