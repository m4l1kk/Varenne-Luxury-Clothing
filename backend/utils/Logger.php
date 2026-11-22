<?php
// Writes one line per event to backend/logs/info.log or backend/logs/error.log.
// The logs folder is git-ignored. Example line:
//   [2026-11-20 10:15:02] ERROR GET /products/1/prices External API failure (Frankfurter): timeout

class Logger
{
    public static function info(string $message): void
    {
        self::write('info.log', 'INFO', $message);
    }

    public static function error(string $message): void
    {
        self::write('error.log', 'ERROR', $message);
    }

    private static function write(string $file, string $level, string $message): void
    {
        $line = sprintf("[%s] %s %s%s", date('Y-m-d H:i:s'), $level, self::requestLine(), $message);
        file_put_contents(__DIR__ . '/../logs/' . $file, $line . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    // "GET /products/1/prices " when called during a web request, empty in the command line.
    private static function requestLine(): string
    {
        if (!isset($_SERVER['REQUEST_METHOD']) || !class_exists('Flight')) {
            return '';
        }
        return Flight::request()->method . ' ' . Flight::request()->url . ' ';
    }
}
