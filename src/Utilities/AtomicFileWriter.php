<?php

namespace PraiseDare\Monnify\Utilities;

final class AtomicFileWriter
{
    public static function write(string $path, string $contents): void
    {
        $dir = dirname($path);

        if (!is_dir($dir) || !is_writable($dir)) {
            throw new \RuntimeException("Directory is not writable: {$dir}");
        }

        $tmp = tempnam($dir, '.tmp_');
        if ($tmp === false) {
            throw new \RuntimeException('Failed to create temp file');
        }

        try {
            $h = fopen($tmp, 'wb');
            if (!$h) {
                throw new \RuntimeException('Failed to open temp file');
            }

            fwrite($h, $contents);
            fflush($h);

            // Just to be extra sure the content has been written to the file
            fsync($h);

            fclose($h);

            // ATOMIC on POSIX
            rename($tmp, $path);
        } finally {
            @unlink($tmp);
        }
    }
}
