<?php

namespace App\Controllers;

use App\Core\Database;
use App\Helpers\AuthHelper;
use PDO;

class AuthController {
    
    public function showLogin() {
        if (AuthHelper::isLoggedIn()) {
            header("Location: " . url('/dashboard'));
            exit;
        }
        require __DIR__ . '/../../resources/views/auth/login.php';
    }

    public function showRegister() {
        if (AuthHelper::isLoggedIn()) {
            header("Location: " . url('/dashboard'));
            exit;
        }
        require __DIR__ . '/../../resources/views/auth/register.php';
    }

    public function register() {
        try {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $_SESSION['error'] = "Permintaan tidak valid. Silakan coba lagi.";
                header("Location: " . url('/register'));
                exit;
            }

            $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING));
            $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
            $phone = trim(filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING));
            $role = $_POST['role'] ?? '';
            $password = $_POST['password'] ?? '';
            $password_confirmation = $_POST['password_confirmation'] ?? '';

            if (empty($name) || empty($email) || empty($password) || empty($role)) {
                $_SESSION['error'] = "Semua field wajib diisi.";
                header("Location: " . url('/register'));
                exit;
            }

            if (!in_array($role, ['ADMIN', 'TEKNISI_NOC'])) {
                $_SESSION['error'] = "Role yang dipilih tidak valid.";
                header("Location: " . url('/register'));
                exit;
            }

            if ($password !== $password_confirmation) {
                $_SESSION['error'] = "Konfirmasi password tidak cocok.";
                header("Location: " . url('/register'));
                exit;
            }

            if (strlen($password) < 6) {
                $_SESSION['error'] = "Password minimal 6 karakter.";
                header("Location: " . url('/register'));
                exit;
            }

            $pdo = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $_SESSION['error'] = "Email sudah digunakan.";
                header("Location: " . url('/register'));
                exit;
            }

            $photo_path = null;
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../storage/private_uploads/profiles/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                    if ($_FILES['photo']['size'] > 2000000) { // 2MB
                        $_SESSION['error'] = "Ukuran foto terlalu besar (maksimal 2MB).";
                        header("Location: " . url('/register'));
                        exit;
                    }
                    $filename = uniqid('profile_') . '.' . $ext;
                    if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $filename)) {
                        $photo_path = 'profiles/' . $filename;
                    }
                } else {
                    $_SESSION['error'] = "Format foto tidak valid. Gunakan JPG/PNG.";
                    header("Location: " . url('/register'));
                    exit;
                }
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $account_status = 'PENDING_APPROVAL';
            $is_active = 0;

            $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, phone, account_status, is_active, photo_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $hash, $role, $phone, $account_status, $is_active, $photo_path]);
            
            $userId = $pdo->lastInsertId();
            $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id, ip_address, user_agent) VALUES (?, 'PUBLIC_REGISTER', 'users', ?, ?, ?)");
            $auditStmt->execute([$userId, $userId, $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '']);

            \App\Helpers\NotificationHelper::createForRole('SUPERADMIN', 'Pendaftaran baru menunggu persetujuan', 'Akun ' . $email . ' (' . $role . ') mendaftar dan menunggu tinjauan Anda.', 'warning', '/users?status=PENDING_APPROVAL');

            $_SESSION['success'] = "Pendaftaran berhasil! Akun Anda berstatus PENDING dan sedang menunggu persetujuan Superadmin.";
            header("Location: " . url('/login'));
            exit;

        } catch (\Exception $e) {
            error_log("Register Error: " . $e->getMessage());
            $_SESSION['error'] = "Terjadi kesalahan sistem. Silakan coba lagi.";
            header("Location: " . url('/register'));
            exit;
        }
    }

    public function authenticate() {
        try {
            // Basic CSRF check
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $_SESSION['error'] = "Permintaan tidak valid. Silakan coba lagi.";
                header("Location: " . url('/login'));
                exit;
            }

            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'] ?? '';

            $pdo = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                if ($user['account_status'] === 'PENDING_APPROVAL') {
                    $_SESSION['error'] = "Akun Anda sedang menunggu persetujuan Superadmin.";
                    header("Location: " . url('/login'));
                    exit;
                }
                
                if ($user['account_status'] === 'REJECTED') {
                    $_SESSION['error'] = "Pendaftaran akun Anda ditolak. Alasan: " . htmlspecialchars($user['rejection_reason'] ?? '-');
                    header("Location: " . url('/login'));
                    exit;
                }

                if ($user['account_status'] === 'SUSPENDED') {
                    $_SESSION['error'] = "Akun Anda telah ditangguhkan.";
                    header("Location: " . url('/login'));
                    exit;
                }

                if (!$user['is_active'] || $user['account_status'] !== 'APPROVED') {
                    $_SESSION['error'] = "Akun tidak aktif atau belum disetujui. Silakan hubungi administrator.";
                    header("Location: " . url('/login'));
                    exit;
                }

                // Single-device login:
                // satu akun hanya boleh memiliki satu sesi aktif dalam jangka waktu timeout.
                // Jika masih ada sesi aktif di perangkat lain, login kedua ditolak.
                $sessionTimeoutMinutes = max(5, (int)(\App\Helpers\SettingsHelper::get('session_timeout_minutes', '30')));
                $activeToken = bin2hex(random_bytes(32));
                $activeTokenHash = hash('sha256', $activeToken);

                $claimStmt = $pdo->prepare("UPDATE users
                    SET active_session_token_hash = ?,
                        active_session_started_at = NOW(),
                        active_session_last_seen_at = NOW(),
                        last_login_at = NOW()
                    WHERE id = ?
                      AND (
                        active_session_token_hash IS NULL
                        OR active_session_last_seen_at IS NULL
                        OR active_session_last_seen_at < DATE_SUB(NOW(), INTERVAL ? MINUTE)
                      )");
                $claimStmt->execute([$activeTokenHash, (int)$user['id'], $sessionTimeoutMinutes]);

                if ($claimStmt->rowCount() !== 1) {
                    $_SESSION['error'] = "Akun ini sedang digunakan di perangkat lain. Silakan logout dari perangkat sebelumnya terlebih dahulu.";
                    $_SESSION['old_email'] = $email;
                    header("Location: " . url('/login'));
                    exit;
                }

                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['authenticated'] = true;
                $_SESSION['active_session_token'] = $activeToken;
                $_SESSION['session_last_seen_sync'] = time();
                $_SESSION['last_activity'] = time();

                header("Location: " . url('/dashboard'));
                exit;
            }

            $_SESSION['error'] = "Email atau password salah.";
            $_SESSION['old_email'] = $email;
            header("Location: " . url('/login'));
            exit;
        } catch (\Exception $e) {
            error_log("Login Error: " . $e->getMessage());
            $_SESSION['error'] = "Terjadi kesalahan sistem. Silakan coba lagi.";
            header("Location: " . url('/login'));
            exit;
        }
    }

    public function logout() {
        try {
            $userId = (int)($_SESSION['user_id'] ?? 0);
            $activeToken = (string)($_SESSION['active_session_token'] ?? '');

            if ($userId > 0 && $activeToken !== '') {
                $pdo = Database::getInstance()->getConnection();
                $hash = hash('sha256', $activeToken);
                // Jangan menghapus sesi baru milik perangkat lain jika sesi lama melakukan logout.
                $stmt = $pdo->prepare("UPDATE users
                    SET active_session_token_hash = NULL,
                        active_session_started_at = NULL,
                        active_session_last_seen_at = NULL
                    WHERE id = ? AND active_session_token_hash = ?");
                $stmt->execute([$userId, $hash]);
            }
        } catch (\Throwable $e) {
            error_log("Logout session cleanup failed: " . $e->getMessage());
        }

        session_unset();
        session_destroy();
        session_start();
        session_regenerate_id(true);

        header("Location: " . url('/login'));
        exit;
    }

    public function showForgotPassword() {
        // D-016: Internal password recovery — tidak ada alur email/reset-token publik.
        // Lupa password ditangani Superadmin via Users → Reset Password.
        require __DIR__ . '/../../resources/views/auth/forgot_password.php';
    }
}
