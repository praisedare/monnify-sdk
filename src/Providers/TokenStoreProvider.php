<?php

namespace PraiseDare\Monnify\Providers;

use PraiseDare\Monnify\Contracts\TokenStoreInterface;
use PraiseDare\Monnify\TokenStores\FileTokenStore;
use PraiseDare\Monnify\TokenStores\SqliteTokenStore;

class TokenStoreProvider
{
    const SQLITE_DB_FILE = '/auth.sqlite';
    const JSON_FILE = '/auth.json';

    public static function create($storageDir): TokenStoreInterface
    {
        if (!file_exists($storageDir)) {
            if (!mkdir($storageDir, recursive: true))
                throw new \Exception('Failed to create storage directory for token storage.');
        }

        $sqlitePath = $storageDir . self::SQLITE_DB_FILE;
        $filePath = $storageDir . self::JSON_FILE;

        // // Check if SQLite extension is loaded and driver is available
        if (extension_loaded('pdo_sqlite') && in_array('sqlite', \PDO::getAvailableDrivers())) {
            return new SqliteTokenStore($sqlitePath);
        }

        // Fallback to FileStore
        return new FileTokenStore($filePath);
    }
}
