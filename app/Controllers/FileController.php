<?php

namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Core\Database;
use PDO;

class FileController {
    
    public function __construct() {
        AuthHelper::requireLogin();
    }

    public function serve() {
        $path = $_GET['path'] ?? '';
        if (empty($path)) {
            $_SESSION['error'] = "File not specified.";
            header("Location: " . url('/dashboard'));
            exit;
        }

        // Evidence lama (workflow Damage/Lost) disimpan di public/uploads/evidence/
        if (strpos($path, 'uploads/evidence/') === 0) {
            $baseDir = __DIR__ . '/../../public/';
        } else {
            $baseDir = __DIR__ . '/../../storage/private_uploads/';
        }
        $fullPath = $baseDir . $path;

        $realFullPath = realpath($fullPath);
        $realBaseDir = realpath($baseDir);
        
        // Security check to prevent directory traversal
        if (strpos($path, '..') !== false || $realFullPath === false || strpos($realFullPath, $realBaseDir) !== 0) {
            $_SESSION['error'] = "Forbidden access or file not found.";
            header("Location: " . url('/dashboard'));
            exit;
        }
        
        // Authorization check for profiles
        if (strpos($path, 'profiles/') === 0) {
            $user = AuthHelper::user();
            if (!in_array($user['role'], ['SUPERADMIN', 'ADMIN'])) {
                $pdo = Database::getInstance()->getConnection();
                $stmt = $pdo->prepare("SELECT id FROM users WHERE photo_path = ? AND id = ?");
                $stmt->execute([$path, $user['id']]);
                if (!$stmt->fetch()) {
                    $_SESSION['error'] = "Anda tidak memiliki izin untuk melihat foto profil ini.";
                    header("Location: " . url('/dashboard'));
                    exit;
                }
            }
        }
        
        if (!file_exists($fullPath)) {
            $_SESSION['error'] = "File not found.";
            header("Location: " . url('/dashboard'));
            exit;
        }

        $mimeType = mime_content_type($fullPath);
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($fullPath));
        
        // Output file
        readfile($fullPath);
        exit;
    }
}
