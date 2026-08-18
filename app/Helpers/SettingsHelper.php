<?php
namespace App\Helpers;

use App\Core\Database;
use PDO;

class SettingsHelper {
    private static $cache = null;

    public static function load() {
        if (self::$cache === null) {
            try {
                $pdo = Database::getInstance()->getConnection();
                $stmt = $pdo->query("SELECT `key`, `value` FROM settings");
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                self::$cache = [];
                foreach ($rows as $r) {
                    self::$cache[$r['key']] = $r['value'];
                }
            } catch (\Exception $e) {
                self::$cache = [];
            }
        }
    }

    public static function get($key, $default = '') {
        self::load();
        return self::$cache[$key] ?? $default;
    }

    public static function set($key, $value) {
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = ?");
        $stmt->execute([$key, $value, $value]);
        self::$cache[$key] = $value;
    }
}
