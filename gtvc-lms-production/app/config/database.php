<?php

declare(strict_types=1);

namespace App\Config;

use PDO;
use PDOException;

/**
 * Database Connection Manager (PDO)
 * Compatible with MySQL 8.x, MariaDB, XAMPP, InfinityFree, Hostinger
 */
class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $host = AppConfig::env('DB_HOST', '127.0.0.1');
            $port = AppConfig::env('DB_PORT', '3306');
            $dbname = AppConfig::env('DB_NAME', 'gilgil_lms');
            $user = AppConfig::env('DB_USER', 'root');
            $pass = AppConfig::env('DB_PASS', '');
            $charset = AppConfig::env('DB_CHARSET', 'utf8mb4');

            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                if (AppConfig::env('APP_DEBUG', true)) {
                    die("Database Connection Error: " . $e->getMessage());
                } else {
                    die("A database error occurred. Please try again later or contact administrator.");
                }
            }
        }

        return self::$instance;
    }
}
