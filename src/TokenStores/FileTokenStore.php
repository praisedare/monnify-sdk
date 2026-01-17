<?php

namespace PraiseDare\Monnify\TokenStores;

use PraiseDare\Monnify\Contracts\TokenStoreInterface;
use PraiseDare\Monnify\Utilities\AtomicFileWriter;

class FileTokenStore implements TokenStoreInterface
{
    /**
     * How close we're allowed to get to the expiry time of the token.
     */
    private $buffer = 60;

    public function __construct(private string $path)
    {}

    /**
     * @param callable(): array{token: string, expires_in: int} $refreshCallback
     * A callback function to fetch a new token.
     */
    public function getToken(callable $refreshCallback): string
    {
        try {
            // Step 1: Shared lock for reading
            // The lock file is not the actual file where the token will be
            // written to. It is only used to signify intent to read from/write
            // to the actual auth file.  If locks were acquired on the file
            // itself, the atomic writing strategy being used would cause the
            // acquired locks prior to the token re-write to be on "ghost" file
            // pointers, as the original file would have been replaced.
            $lockFile = $this->path . '.lock';
            $lockHandle = fopen($lockFile, 'c+');
            flock($lockHandle, LOCK_SH);

            $data = $this->readExistingData();
            if ($this->isValid($data)) {
                return $data['token'];
            }

            // Step 2: Upgrade to Exclusive lock for writing
            flock($lockHandle, LOCK_UN);
            flock($lockHandle, LOCK_EX);

            // Double-check (Race condition protection)
            $data = $this->readExistingData();
            if ($this->isValid($data)) {
                return $data['token'];
            }

            // Step 3: Refresh
            $res = $refreshCallback();
            $newData = [
                'token' => $res['token'],
                'expires_at' => time() + $res['expires_in']
            ];

            AtomicFileWriter::write(
                $this->path,
                json_encode($newData)
            );

            return $newData['token'];
        } finally {
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
        }
    }

    /**
     * @return array{token: string, expires_at: int}|false The token data
     */
    private function readExistingData(): array|false
    {
        // Check if file exists first to avoid PHP warnings
        if (!file_exists($this->path)) {
            return false;
        }

        $content = @file_get_contents($this->path);

        // If file is unreadable or empty, return empty array
        if ($content === false || empty($content)) {
            return false;
        }

        $data = json_decode($content, true);

        // Ensure we always return an array, even if JSON is invalid
        return is_array($data)
            ? $data
            : false;
    }

    /**
     * @param array{token: string, expires_at: int}|false $d The token data
     */
    private function isValid($d): bool
    {
        return $d !== false
            && isset($d['token'], $d['expires_at'])
            && time() < ($d['expires_at'] - $this->buffer);
    }
}
