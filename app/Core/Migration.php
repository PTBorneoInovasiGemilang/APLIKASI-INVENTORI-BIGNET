<?php

namespace App\Core;

use Exception;
use PDO;

class Migration {
    private $pdo;
    private $migrationsDir;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
        $this->migrationsDir = __DIR__ . '/../../database/migrations/';
    }

    public function migrate() {
        $this->createMigrationsTable();
        $appliedMigrations = $this->getAppliedMigrations();
        
        $files = scandir($this->migrationsDir);
        $newMigrations = [];

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            if (!in_array($file, $appliedMigrations)) {
                $this->applyMigration($file);
                $newMigrations[] = $file;
            }
        }

        return $newMigrations;
    }

    private function createMigrationsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS schema_migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $this->pdo->exec($sql);
    }

    private function getAppliedMigrations() {
        $stmt = $this->pdo->query("SELECT migration FROM schema_migrations");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function applyMigration($file) {
        $filePath = $this->migrationsDir . $file;
        $className = pathinfo($file, PATHINFO_FILENAME);
        // Remove numbers from the beginning e.g., 001_create_users -> CreateUsers
        $className = preg_replace('/^[0-9]+_/', '', $className);
        $className = str_replace('_', ' ', $className);
        $className = str_replace(' ', '', ucwords($className));

        require_once $filePath;
        
        if (!class_exists($className)) {
             throw new Exception("Migration class $className not found in $file");
        }

        $migration = new $className();
        
        // Execute the up method
        $migration->up($this->pdo);

        // Record it
        $stmt = $this->pdo->prepare("INSERT INTO schema_migrations (migration) VALUES (?)");
        $stmt->execute([$file]);
    }
}
