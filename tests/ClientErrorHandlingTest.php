<?php

namespace PraiseDare\Monnify\Tests;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PraiseDare\Monnify\Config\Config;
use PraiseDare\Monnify\Exceptions\MonnifyException;
use PraiseDare\Monnify\Http\Client;
use PraiseDare\Monnify\Contracts\TokenStoreInterface;
use PraiseDare\Monnify\Exceptions\ProtocolException;

class ClientErrorHandlingTest extends TestCase
{
    #[Test]
    public function handles_application_level_failure_without_throwing()
    {
        // 404 with Valid Struct -> Should NOT throw
        $errorBody = json_encode([
            'requestSuccessful' => false,
            'responseMessage' => 'Transaction not found',
            'responseCode' => 'NOT_FOUND',
        ]);

        $mock = new MockHandler([
            new Response(404, ['Content-Type' => 'application/json'], $errorBody),
        ]);

        $client = $this->createClientWithMocks($mock);

        $response = $client->get('/api/v1/some-endpoint');

        $this->assertIsArray($response);
        $this->assertFalse($response['requestSuccessful']);
        $this->assertEquals('NOT_FOUND', $response['responseCode']);
    }

    #[Test]
    public function throws_exception_on_auth_error()
    {
        // 401 Unauthorized -> Should throw even with valid struct
        $errorBody = json_encode([
            'requestSuccessful' => false,
            'responseMessage' => 'Unauthorized',
            'responseCode' => 'UNAUTHORIZED',
            'responseBody' => null
        ]);

        $mock = new MockHandler([
            new Response(401, ['Content-Type' => 'application/json'], $errorBody),
        ]);

        $client = $this->createClientWithMocks($mock);

        $this->expectException(MonnifyException::class);
        $this->expectExceptionCode(401);
        $client->get('/api/v1/some-endpoint');
    }

    #[Test]
    public function throws_exception_on_validation_error()
    {
        // 422 Unprocessable Entity -> Should throw
        $errorBody = json_encode([
            'requestSuccessful' => false,
            'responseMessage' => 'Validation Failed',
            'responseCode' => 'VALIDATION_ERROR',
            'responseBody' => null
        ]);

        $mock = new MockHandler([
            new Response(422, ['Content-Type' => 'application/json'], $errorBody),
        ]);

        $client = $this->createClientWithMocks($mock);

        $this->expectException(MonnifyException::class);
        $this->expectExceptionCode(422);
        $client->get('/api/v1/some-endpoint');
    }

    #[Test]
    public function throws_exception_on_server_error()
    {
         // 500 Server Error -> Should throw
         $errorBody = json_encode([
            'requestSuccessful' => false,
            'responseMessage' => 'System error',
            'responseCode' => 'SYSTEM_ERROR',
            'responseBody' => null
        ]);

        $mock = new MockHandler([
            new Response(500, [], $errorBody),
        ]);

        $client = $this->createClientWithMocks($mock);

        $this->expectException(MonnifyException::class);
        $this->expectExceptionCode(500);
        $client->get('/api/v1/some-endpoint');
    }

    #[Test]
    public function throws_exception_on_invalid_structure()
    {
        // 200 OK but Missing 'requestSuccessful'
        $invalidBody = json_encode([
            'message' => 'Success', // Wrong key
            'code' => '00',
            'data' => []
        ]);

        $mock = new MockHandler([
            new Response(200, [], $invalidBody),
        ]);

        $client = $this->createClientWithMocks($mock);

        $this->expectException(ProtocolException::class);
        $this->expectExceptionMessage('Unexpected Response Structure');

        $client->get('/api/v1/some-endpoint');
    }


    private function createClientWithMocks(MockHandler $mockHandler): Client
    {
        $config = new Config([
            'api_key' => 'test',
            'secret_key' => 'test',
            'contract_code' => 'test',
            'environment' => 'sandbox',
            'wallet_account_number' => '0000000000'
        ]);

        $client = new Client($config);

        // 1. Inject Mock TokenStore
        $mockTokenStore = $this->createMock(TokenStoreInterface::class);
        $mockTokenStore->method('getToken')->willReturn('mock-access-token');

        $reflection = new \ReflectionClass($client);

        $tokenStoreProp = $reflection->getProperty('tokenStore');
        $tokenStoreProp->setAccessible(true);
        $tokenStoreProp->setValue($client, $mockTokenStore);

        // 2. Inject Mock Guzzle Client
        $handlerStack = HandlerStack::create($mockHandler);
        $guzzleClient = new GuzzleClient(['handler' => $handlerStack, 'http_errors' => false]);

        $httpClientProp = $reflection->getProperty('httpClient');
        $httpClientProp->setAccessible(true);
        $httpClientProp->setValue($client, $guzzleClient);

        return $client;
    }

}
