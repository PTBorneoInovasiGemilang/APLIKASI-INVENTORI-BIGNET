<?php

namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Core\Database;
use PDO;

class ProfileController {
    
    public function __construct() {
        AuthHelper::requireLogin();
    }

    public function show() {
        $pdo = Database::getInstance()->getConnection();
        $user_id = AuthHelper::user()['id'];
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $profile = $stmt->fetch();

        require __DIR__ . '/../../resources/views/profile/show.php';
    }

    public function uploadPhoto() {
        if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = "CSRF validation failed.";
            header("Location: " . url('/profile'));
            exit;
        }

        $user_id = AuthHelper::user()['id'];
        
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['photo'];
            
            if ($file['size'] > 2 * 1024 * 1024) {
                $_SESSION['error'] = "Max 2MB limit exceeded.";
                header("Location: " . url('/profile'));
                exit;
            }

            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($ext, $allowedExts)) {
                $_SESSION['error'] = "Invalid file extension.";
                header("Location: " . url('/profile'));
                exit;
            }

            $mimeType = mime_content_type($file['tmp_name']);
            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
            if (!in_array($mimeType, $allowedMimes)) {
                $_SESSION['error'] = "Invalid file type.";
                header("Location: " . url('/profile'));
                exit;
            }

            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'profile_' . $user_id . '_' . time() . '.' . $ext;
            
            $uploadDir = __DIR__ . '/../../storage/private_uploads/profiles/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $destination = $uploadDir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $pdo = Database::getInstance()->getConnection();
                
                // Remove old photo if exists
                $stmt = $pdo->prepare("SELECT photo_path FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $old = $stmt->fetchColumn();
                if ($old && file_exists(__DIR__ . '/../../storage/private_uploads/' . $old)) {
                    unlink(__DIR__ . '/../../storage/private_uploads/' . $old);
                }

                $path = 'profiles/' . $filename;
                $upd = $pdo->prepare("UPDATE users SET photo_path = ? WHERE id = ?");
                $upd->execute([$path, $user_id]);
            }
        }
        $_SESSION['success'] = "Photo uploaded successfully.";
        header("Location: " . url('/profile'));
        exit;
    }
    public function update() {
        if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = "Invalid request.";
            header("Location: " . url('/profile'));
            exit;
        }

        $user_id = AuthHelper::user()['id'];
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $currentPassword = $_POST['current_password'] ?? '';
        $passwordConfirmation = $_POST['password_confirmation'] ?? '';

        if (empty($name)) {
            $_SESSION['error'] = "Nama wajib diisi.";
            header("Location: " . url('/profile'));
            exit;
        }

        $pdo = Database::getInstance()->getConnection();
        
        if (!empty($password)) {
            // Verifikasi password saat ini
            $checkStmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
            $checkStmt->execute([$user_id]);
            $currentHash = $checkStmt->fetchColumn();

            if (empty($currentPassword) || !$currentHash || !password_verify($currentPassword, $currentHash)) {
                $_SESSION['error'] = "Password saat ini salah. Password tidak diubah.";
                header("Location: " . url('/profile'));
                exit;
            }

            if (strlen($password) < 6) {
                $_SESSION['error'] = "Password minimal 6 karakter.";
                header("Location: " . url('/profile'));
                exit;
            }

            if ($password !== $passwordConfirmation) {
                $_SESSION['error'] = "Konfirmasi password tidak cocok.";
                header("Location: " . url('/profile'));
                exit;
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, password_hash = ? WHERE id = ?");
            $stmt->execute([$name, $phone, $hash, $user_id]);
            
            // Audit Log
            $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id, ip_address, user_agent) VALUES (?, 'PASSWORD_CHANGE', 'users', ?, ?, ?)");
            $auditStmt->execute([$user_id, $user_id, $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '']);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
            $stmt->execute([$name, $phone, $user_id]);
        }

        // Update session
        $_SESSION['user_name'] = $name;

        $_SESSION['success'] = "Profile updated successfully.";
        header("Location: " . url('/profile'));
        exit;
    }
}
