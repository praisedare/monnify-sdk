<?php

namespace PraiseDare\Monnify\Scripts;

class SetupStorage
{
    public static function init()
    {
        $dir = __DIR__ . '/../../storage';

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            echo "Storage directory created.\n";
        }

        // Initialize SQLite if possible
        if (extension_loaded('pdo_sqlite')) {
            try {
                new \PDO("sqlite:$dir/auth.sqlite");
                echo "SQLite database initialized successfully.\n";
            } catch (\Exception $e) {
                echo "SQLite available but failed to initialize: " . $e->getMessage() . "\n";
            }
        } else {
            echo "SQLite not found. Library will use FileStore fallback.\n";
        }
    }
}
