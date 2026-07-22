<?php

declare(strict_types=1);

namespace App\Core;

use App\Config\Database;
use PDO;

/**
 * Base MVC Model
 */
abstract class Model
{
    protected static ?PDO $db = null;

    /**
     * Get active database connection instance
     */
    protected static function getDb(): PDO
    {
        if (self::$db === null) {
            self::$db = Database::getConnection();
        }
        return self::$db;
    }

    /**
     * Fetch all matching rows
     */
    protected static function fetchAll(string $sql, array $params = []): array
    {
        $stmt = self::getDb()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetch single row or null
     */
    protected static function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = self::getDb()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Execute statement (INSERT/UPDATE/DELETE) and return affected rows or last insert ID
     */
    protected static function execute(string $sql, array $params = []): int
    {
        $stmt = self::getDb()->prepare($sql);
        $stmt->execute($params);
        $lastId = (int)self::getDb()->lastInsertId();
        return $lastId > 0 ? $lastId : $stmt->rowCount();
    }
}
