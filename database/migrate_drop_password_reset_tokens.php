<?php
/**
 * Migration: D-016 — Internal Password Recovery
 * Menghapus tabel password_reset_tokens yang menjadi dead code setelah alur
 * email/reset-token publik dihapus (AuthController sendResetLink/showResetPassword/resetPassword
 * dan view reset_password.php dihapus; route POST /forgot-password & /reset-password dihapus).
 *
 * Lupa password kini ditangani Superadmin (Users → Reset Password) sesuai D-016.
 *
 * Jalankan: php database/migrate_drop_password_reset_tokens.php
 * Idempotent (DROP TABLE IF EXISTS) — aman dijalankan ulang.
 */

$config = require __DIR__ . '/../config/database.php';
$dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
$pdo = new PDO($dsn, $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$pdo->exec("DROP TABLE IF EXISTS password_reset_tokens");

echo "[OK] password_reset_tokens dropped (dead code per D-016).\n";
