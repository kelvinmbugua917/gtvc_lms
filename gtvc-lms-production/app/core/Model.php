<?php

declare(strict_types=1);

namespace App\Core;

use App\Config\Database;
use PDO;

/**
 * Base Database Model with Prepared Statements and Helper Methods
 */
abstract class Model
{
    protected PDO $db;
    protected string $table;
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Find record by ID
     */
    public function find(int|string $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Fetch all records
     */
    public function all(string $orderBy = 'created_at', string $direction = 'DESC'): array
    {
        $allowedDirections = ['ASC', 'DESC'];
        $direction = in_array(strtoupper($direction), $allowedDirections, true) ? strtoupper($direction) : 'DESC';
        
        // Ensure column name is alphanumeric + underscore to prevent SQL injection
        $orderBy = preg_replace('/[^a-zA-Z0-9_]/', '', $orderBy);

        $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY {$orderBy} {$direction}");
        return $stmt->fetchAll();
    }

    /**
     * Insert new record and return lastInsertId
     */
    public function create(array $data): string|bool
    {
        $keys = array_keys($data);
        $fields = implode(', ', array_map(fn($k) => "`{$k}`", $keys));
        $placeholders = implode(', ', array_map(fn($k) => ":{$k}", $keys));

        $sql = "INSERT INTO {$this->table} ({$fields}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);

        return $this->db->lastInsertId();
    }

    /**
     * Update existing record
     */
    public function update(int|string $id, array $data): bool
    {
        $fields = implode(', ', array_map(fn($k) => "`{$k}` = :{$k}", array_keys($data)));
        $sql = "UPDATE {$this->table} SET {$fields} WHERE {$this->primaryKey} = :_primary_id";

        $data['_primary_id'] = $id;
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    /**
     * Delete record by ID
     */
    public function delete(int|string $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id");
        return $stmt->execute(['id' => $id]);
    }
}
