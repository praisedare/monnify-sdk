<?php

declare(strict_types=1);

namespace PraiseDare\Monnify\Tests;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use PraiseDare\Monnify\Monnify;
use PHPUnit\Framework\TestCase as BaseTest;

/**
 * Basic test class for Monnify SDK
 */
class TestCase extends BaseTest
{
    protected Monnify $monnify;
    protected Logger $logger;

    protected function setUp(): void
    {
        if (!isset($this->logger)) {
            $this->logger = new Logger('testing');
            $file = __DIR__.'/../logs/' . (new \DateTime)->format('Y-m-d') .'.log';
            $this->logger->pushHandler(new StreamHandler($file));
        }

        $creds = require __DIR__ . '/../ignored/creds.php';
        $this->monnify = new Monnify([
            'secret_key' => $creds['secret_key'],
            'api_key' => $creds['api_key'],
            'contract_code' => $creds['contract_code'],
            'environment' => $creds['environment'],
            'wallet_account_number' => $creds['wallet_account_number'],
        ]);
    }

}