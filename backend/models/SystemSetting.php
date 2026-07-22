<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

class SystemSetting extends Model
{
    public static function getAll(): array
    {
        $db = self::getDb();
        $stmt = $db->query("SELECT * FROM `system_settings` ORDER BY category, setting_key");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getByKey(string $key): ?string
    {
        $db = self::getDb();
        $stmt = $db->prepare("SELECT setting_value FROM `system_settings` WHERE setting_key = :key");
        $stmt->execute(['key' => $key]);
        $val = $stmt->fetchColumn();
        return $val !== false ? (string)$val : null;
    }

    public static function setKey(string $key, string $value, string $category = 'general', ?string $description = null): bool
    {
        $db = self::getDb();
        $stmt = $db->prepare("
            INSERT INTO `system_settings` (`setting_key`, `setting_value`, `category`, `description`, `updated_at`)
            VALUES (:key, :value, :category, :description, NOW())
            ON DUPLICATE KEY UPDATE 
                `setting_value` = VALUES(`setting_value`),
                `category` = VALUES(`category`),
                `description` = VALUES(`description`),
                `updated_at` = NOW()
        ");
        return $stmt->execute([
            'key' => $key,
            'value' => $value,
            'category' => $category,
            'description' => $description
        ]);
    }
}
