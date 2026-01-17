<?php

namespace PraiseDare\Monnify\TokenStores;

use PDO;
use PraiseDare\Monnify\Contracts\TokenStoreInterface;

class SqliteTokenStore implements TokenStoreInterface
{
    private $pdo;

    public function __construct(string $path)
    {
        $this->pdo = new PDO("sqlite:$path");
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // Optimize for concurrency
        $this->pdo->exec("PRAGMA journal_mode=WAL;");
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS auth (id INTEGER PRIMARY KEY, token TEXT, expires_at INTEGER)");
    }

    public function getToken(callable $refreshCallback): string
    {
        // SQLite "IMMEDIATE" transaction handles the queueing of concurrent requests
        $inTransaction = false;

        try {
            $this->pdo->exec('BEGIN IMMEDIATE TRANSACTION');
            $inTransaction = true;

            $row = $this->pdo->query("SELECT token, expires_at FROM auth WHERE id = 1")->fetch(PDO::FETCH_ASSOC);

            if ($row && time() < ($row['expires_at'] - 60)) {
                $this->pdo->exec('COMMIT');
                return $row['token'];
            }

            $res = $refreshCallback();
            $stmt = $this->pdo->prepare("REPLACE INTO auth (id, token, expires_at) VALUES (1, ?, ?)");
            $stmt->execute([$res['token'], time() + $res['expires_in']]);

            $this->pdo->exec('COMMIT');
            return $res['token'];
        } catch (\Exception $e) {
            if ($inTransaction())
                $this->pdo->exec('ROLLBACK');
            throw $e;
        }
    }
}
