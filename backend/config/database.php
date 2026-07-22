<?php

declare(strict_types=1);

namespace App\Config;

use PDO;
use PDOException;
use Exception;

/**
 * Database Connection Management (PDO Singleton / Wrapper)
 */
class Database
{
    private static ?PDO $connection = null;

    /**
     * Get active PDO connection instance
     */
    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            $host = AppConfig::env('DB_HOST', '127.0.0.1');
            $port = AppConfig::env('DB_PORT', '3306');
            $dbname = AppConfig::env('DB_NAME', 'gilgil_lms');
            $username = AppConfig::env('DB_USER', 'root');
            $password = AppConfig::env('DB_PASS', '');
            $charset = AppConfig::env('DB_CHARSET', 'utf8mb4');

            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_TIMEOUT            => 5,
            ];

            try {
                self::$connection = new PDO($dsn, $username, $password, $options);
            } catch (PDOException $e) {
                // Return a clean error if database connection fails
                throw new Exception("Database Connection Error: " . $e->getMessage(), 500);
            }
        }

        return self::$connection;
    }

    /**
     * Check if database is reachable
     */
    public static function testConnection(): array
    {
        try {
            $pdo = self::getConnection();
            $stmt = $pdo->query("SELECT VERSION() AS version");
            $result = $stmt->fetch();
            return [
                'connected' => true,
                'version'   => $result['version'] ?? 'Unknown MySQL/MariaDB',
            ];
        } catch (Exception $e) {
            return [
                'connected' => false,
                'error'     => $e->getMessage(),
            ];
        }
    }
}
