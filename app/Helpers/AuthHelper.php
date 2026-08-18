<?php

namespace App\Helpers;

class AuthHelper {
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']) && $_SESSION['authenticated'] === true;
    }

    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            $basePath = url();
            header("Location: " . url('/login'));
            exit;
        }
    }

    public static function hasRole($roles) {
        if (!self::isLoggedIn()) return false;
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        return in_array($_SESSION['role'], $roles);
    }

    public static function requireRole($roles) {
        self::requireLogin();
        if (!self::hasRole($roles)) {
            http_response_code(403);
            echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>403</title><script src="https://cdn.tailwindcss.com"></script></head><body class="bg-gray-100 flex items-center justify-center min-h-screen"><div class="text-center"><h1 class="text-6xl font-bold text-red-300">403</h1><p class="text-xl text-gray-500 mt-4">Akses Ditolak</p><p class="text-sm text-gray-400 mt-2">Anda tidak memiliki izin untuk mengakses halaman ini.</p><a href="' . url('/dashboard') . '" class="mt-6 inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm">Kembali ke Dasbor</a></div></body></html>';
            exit;
        }
    }

    public static function denySuperadminWrite() {
        self::requireLogin();
        if (self::hasRole('SUPERADMIN')) {
            http_response_code(403);
            echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>403</title><script src="https://cdn.tailwindcss.com"></script></head><body class="bg-gray-100 flex items-center justify-center min-h-screen"><div class="text-center"><h1 class="text-6xl font-bold text-red-350">403</h1><p class="text-xl text-gray-500 mt-4">Akses Ditolak</p><p class="text-sm text-gray-400 mt-2">Superadmin hanya memiliki hak akses baca-saja (read-only) untuk modul ini.</p><a href="' . url('/dashboard') . '" class="mt-6 inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm">Kembali ke Dasbor</a></div></body></html>';
            exit;
        }
    }

    public static function user() {
        if (!self::isLoggedIn()) return null;
        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'],
            'role' => $_SESSION['role']
        ];
    }

    public static function getUserPhoto() {
        if (!self::isLoggedIn()) return null;
        try {
            $pdo = \App\Core\Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("SELECT photo_path FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $path = $stmt->fetchColumn();
            return $path ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
