<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Migration;
use Exception;
use PDO;

class InstallController {
    
    private function isInstalled() {
        return file_exists(__DIR__ . '/../../storage/database/installed.lock');
    }

    public function index() {
        if ($this->isInstalled()) {
            echo "System is already installed. Remove storage/database/installed.lock to reinstall.";
            return;
        }

        $config = require __DIR__ . '/../../config/database.php';
        
        // Simple View for Installer
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>PT.BIG Warehouse Installer</title>
            <style>
                body { font-family: sans-serif; background: #f4f4f5; padding: 2rem; }
                .container { max-width: 600px; margin: 0 auto; background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
                h1 { color: #111827; }
                .form-group { margin-bottom: 1rem; }
                label { display: block; font-weight: bold; margin-bottom: 0.5rem; }
                input { width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 4px; }
                button { background: #2563eb; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
                button:hover { background: #1d4ed8; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h1>System Installer</h1>
                <p>Welcome to PT.BIG Warehouse System Installer. Please review the database configuration below.</p>
                <form method='POST' action='/APLIKASI INVENTORI BIGNET/public/install'>
                    <div class='form-group'>
                        <label>Host</label>
                        <input type='text' name='host' value='{$config['host']}' readonly>
                    </div>
                    <div class='form-group'>
                        <label>Database</label>
                        <input type='text' name='database' value='{$config['database']}' readonly>
                    </div>
                    <div class='form-group'>
                        <label>Username</label>
                        <input type='text' name='username' value='{$config['username']}' readonly>
                    </div>
                    <p style='color: #4b5563; font-size: 0.875rem;'>Configuration is loaded from config/database.php</p>
                    <button type='submit'>Run Migrations & Seed</button>
                </form>
            </div>
        </body>
        </html>";
    }

    public function run() {
        if ($this->isInstalled()) {
            echo "System is already installed.";
            return;
        }

        try {
            $migration = new Migration();
            $applied = $migration->migrate();

            // Run seeders here
            $this->runSeeders();

            // Create directories if they don't exist
            $this->createStorageDirs();

            // Lock the installer
            file_put_contents(__DIR__ . '/../../storage/database/installed.lock', 'Installed at: ' . date('Y-m-d H:i:s'));

            echo "<!DOCTYPE html>
            <html>
            <head><title>Install Success</title></head>
            <body style='font-family: sans-serif; padding: 2rem; max-width: 600px; margin: 0 auto; text-align: center;'>
                <h1 style='color: #16a34a;'>Installation Successful!</h1>
                <p>Database schema has been created successfully.</p>";
            if (!empty($applied)) {
                echo "<p>Applied Migrations:</p><ul style='text-align: left;'>";
                foreach ($applied as $m) echo "<li>$m</li>";
                echo "</ul>";
            }
            echo "<a href='/APLIKASI INVENTORI BIGNET/public/login' style='display: inline-block; background: #2563eb; color: white; padding: 0.75rem 1.5rem; text-decoration: none; border-radius: 4px; margin-top: 1rem;'>Go to Login</a>
            </body>
            </html>";

        } catch (Exception $e) {
            echo "<h2>Installation Failed</h2>";
            echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
        }
    }

    private function runSeeders() {
        $pdo = Database::getInstance()->getConnection();

        // Seed Users
        $passwordSuperAdmin = password_hash('superadmin123', PASSWORD_DEFAULT);
        $passwordAdmin = password_hash('admin123', PASSWORD_DEFAULT);
        $passwordTeknisi = password_hash('teknisi123', PASSWORD_DEFAULT);

        $sql = "INSERT IGNORE INTO users (name, email, password_hash, role) VALUES 
                ('Super Admin', 'superadmin@ptbig.com', '$passwordSuperAdmin', 'SUPERADMIN'),
                ('Admin Gudang', 'admin@ptbig.com', '$passwordAdmin', 'ADMIN'),
                ('Teknisi NOC', 'teknisi@ptbig.com', '$passwordTeknisi', 'TEKNISI_NOC')";
        $pdo->exec($sql);
    }

    private function createStorageDirs() {
        $dirs = [
            __DIR__ . '/../../storage/logs',
            __DIR__ . '/../../storage/private_uploads',
            __DIR__ . '/../../storage/imports',
            __DIR__ . '/../../storage/exports',
            __DIR__ . '/../../storage/database',
        ];

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }
}
