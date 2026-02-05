<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Http;

use PraiseDare\Monnify\Config\Config;
use PraiseDare\Monnify\Exceptions\MonnifyException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use PraiseDare\Monnify\Contracts\TokenStoreInterface;
use PraiseDare\Monnify\Exceptions\ProtocolException;
use PraiseDare\Monnify\Providers\TokenStoreProvider;

/**
 * HTTP Client for Monnify API
 *
 * Handles all HTTP requests to Monnify API with authentication and error handling
 */
class Client
{
    private Config $config;
    private GuzzleClient $httpClient;
    private TokenStoreInterface $tokenStore;

    const AUTH_ENDPOINT = '/api/v1/auth/login';
    const FALLTHROUGH_STATUS_CODES = [404];

    /**
     * Constructor
     *
     * @param Config $config Configuration object
     */
    public function __construct(Config $config)
    {
        $this->config = $config;

        static $tokenStore = TokenStoreProvider::create(
            $this->getConfig()->getPackageRootDirectory() . '/storage'
        );
        $this->tokenStore = $tokenStore;

        $this->httpClient = new GuzzleClient([
            'base_uri' => $config->getBaseUrl(),
            'timeout' => $config->getTimeout(),
            'verify' => $config->getVerifySsl(),
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'http_errors' => false,
        ]);
    }

    /**
     * Make a GET request
     *
     * @param string $endpoint API endpoint
     * @param array $query Query parameters
     * @param array $headers Additional headers
     * @return array Response data
     * @throws MonnifyException
     */
    public function get(string $endpoint, array $query = [], array $headers = []): array
    {
        return $this->request('GET', $endpoint, [], $headers, $query);
    }

    /**
     * Make a POST request
     *
     * @param string $endpoint API endpoint
     * @param array $data Request data
     * @param array $headers Additional headers
     * @return array Response data
     * @throws MonnifyException
     */
    public function post(string $endpoint, array $data = [], array $headers = []): array
    {
        return $this->request('POST', $endpoint, $data, $headers);
    }

    /**
     * Make a PUT request
     *
     * @param string $endpoint API endpoint
     * @param array $data Request data
     * @param array $headers Additional headers
     * @return array Response data
     * @throws MonnifyException
     */
    public function put(string $endpoint, array $data = [], array $headers = []): array
    {
        return $this->request('PUT', $endpoint, $data, $headers);
    }

    /**
     * Make a DELETE request
     *
     * @param string $endpoint API endpoint
     * @param array $headers Additional headers
     * @return array Response data
     * @throws MonnifyException
     */
    public function delete(string $endpoint, array $headers = []): array
    {
        return $this->request('DELETE', $endpoint, [], $headers);
    }

    /**
     * Make an authenticated request
     *
     * @param string $method HTTP method
     * @param string $endpoint API endpoint
     * @param array $data Request data
     * @param array $headers Additional headers
     * @param array $query Query parameters
     * @return array Response data
     * @throws MonnifyException
     */
    public function request(string $method, string $endpoint, array $data = [], array $headers = [], array $query = []): array
    {
        // Add authentication header if needed
        if ($this->requiresAuth($endpoint)) {
            $headers['Authorization'] = 'Bearer ' . $this->getAccessToken();
        }

        $options = [
            'headers' => $headers,
        ];

        if (!empty($data)) {
            $options['json'] = $data;
        }

        if (!empty($query)) {
            $options['query'] = $query;
        }

        try {
            $response = $this->httpClient->request($method, $endpoint, $options);
            $statusCode = $response->getStatusCode();
            $responseData = json_decode($response->getBody()->getContents(), true);

            return $this->handleResponse($responseData, $statusCode);
        } catch (RequestException $e) {
            throw $this->handleRequestException($e);
        } catch (MonnifyException $m) {
            throw $m;
        } catch (\Exception $e) {
            // dump($e);
            throw new MonnifyException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Check if endpoint requires authentication
     *
     * @param string $endpoint API endpoint
     * @return bool
     */
    private function requiresAuth(string $endpoint): bool
    {
        // All endpoints require authentication except the auth endpoint itself
        return $endpoint !== self::AUTH_ENDPOINT;
    }

    /**
     * Get access token for authentication
     *
     * @return string
     * @throws MonnifyException
     */
    private function getAccessToken(): string
    {
        return $this->tokenStore->getToken($this->authenticate(...));
    }

    /**
     * Authenticate with Monnify API
     *
     * @throws MonnifyException
     */
    private function authenticate(): array
    {
        $credentials = base64_encode($this->config->getApiKey() . ':' . $this->config->getSecretKey());

        try {
            // According to Monnify docs, we need to make a request to get an access token
            // The endpoint might vary, but let's try the standard approach
            $response = $this->httpClient->post(self::AUTH_ENDPOINT, [
                'headers' => [
                    'Authorization' => 'Basic ' . $credentials,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            // Check for access token in response
            if (isset($data['responseBody']['accessToken'])) {
                return [
                    'token' => $data['responseBody']['accessToken'],
                    'expires_in' => $data['responseBody']['expiresIn'],
                ];
            } else {
                // If no access token found, we might need to use Basic Auth for all requests
                // For now, let's throw an exception
                throw new MonnifyException('Failed to obtain access token from response: ' . json_encode($data));
            }
        } catch (RequestException $e) {
            throw $this->handleRequestException($e);
        }
    }

    /**
     * Handle API response.
     *
     * Throws exceptions in cases where the response is deemed a "failure" which the application
     * should not see, e.g. authentication error, validation error, e.t.c. Other "failure" cases
     * such as a 404 will be allowed to pass through so that the consuming code can react
     * appropriately.
     *
     * @param array{requestSuccessful: bool, responseCode: string, responseMessage: string}|array{error: string, error_description: string} $responseData Response data
     * @return array Processed response
     * @throws MonnifyException
     */
    private function handleResponse(array $responseData, int $statusCode): array
    {

        // 1. Check for required structure
        // NOTE: Route-level 404s will not have this structure and cause a protocol error.
        // A resource-level 404 will have this structure and be handled in the next step.
        if (!isset($responseData['requestSuccessful'])
            || !array_key_exists('responseMessage', $responseData)
            || !array_key_exists('responseCode', $responseData)
        ) {
            throw new ProtocolException(
                'Unexpected Response Structure: ' . json_encode($responseData),
                $statusCode
            );
        }

        // 2. Check for specific HTTP error codes that must always throw
        if ($statusCode >= 400 && !in_array($statusCode, self::FALLTHROUGH_STATUS_CODES)) {
            $message = (isset($responseData['error'])
                    ? "{$responseData['error']}: {$responseData['error_description']}"
                    : null)
                ?? $responseData['responseMessage']
                ?? 'API request failed'
                ;
            $code = $responseData['responseCode'] ?? 'HTTP_' . $statusCode;
            throw new MonnifyException($message, $statusCode, null, $code);
        }

        return $responseData;
    }

    /**
     * Handle request exceptions
     *
     * @param RequestException $e Request exception
     * @return MonnifyException
     */
    private function handleRequestException(RequestException $e): MonnifyException
    {
        $response = $e->getResponse();

        if ($response) {
            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                return new MonnifyException(
                    $data['error_description'] ?? $data['responseMessage'] ?? $e->getMessage(),
                    $statusCode,
                    $e,
                    $data['responseCode'] ?? 'HTTP_' . $statusCode
                );
            }

            return new MonnifyException('HTTP request failed: ' . $e->getMessage(), $statusCode, $e);
        }

        return new MonnifyException('Network error: ' . $e->getMessage(), 0, $e);
    }

    /**
     * Get configuration
     *
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }
}
