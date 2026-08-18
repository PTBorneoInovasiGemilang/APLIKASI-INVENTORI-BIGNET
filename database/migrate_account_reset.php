<?php
/**
 * Migration: Account Management — Reset Password
 * Menambahkan tabel password_reset_tokens untuk alur Lupa Kata Sandi → Reset Password.
 *
 * Jalankan: php database/migrate_account_reset.php
 * Idempotent (CREATE TABLE IF NOT EXISTS) — aman dijalankan ulang.
 */

$config = require __DIR__ . '/../config/database.php';
$dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
$pdo = new PDO($dsn, $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$sql = "
CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token_hash VARCHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_reset_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

$pdo->exec($sql);
echo "password_reset_tokens table ready.\n";
