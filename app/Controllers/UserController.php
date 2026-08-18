<?php
namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Core\Database;
use PDO;

class UserController {
    public function __construct() {
        AuthHelper::requireRole('SUPERADMIN');
    }

    public function index() {
        $pdo = Database::getInstance()->getConnection();

        $search = trim($_GET['search'] ?? '');
        $status = $_GET['status'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $where = [];
        $params = [];

        if ($search !== '') {
            $where[] = "(name LIKE ? OR email LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if ($status !== '') {
            $where[] = "account_status = ?";
            $params[] = $status;
        }

        $whereSql = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : '';

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM users $whereSql");
        $countStmt->execute($params);
        $totalItems = (int)$countStmt->fetchColumn();
        $totalPages = max(1, (int)ceil($totalItems / $limit));

        $stmt = $pdo->prepare("SELECT * FROM users $whereSql ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
        $stmt->execute($params);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pageTitle = 'User Management';
        require __DIR__ . '/../../resources/views/users/index.php';
    }

    public function create() {
        $pageTitle = 'Create User';
        require __DIR__ . '/../../resources/views/users/create.php';
    }

    public function store() {
        if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = "CSRF validation failed";
            header("Location: " . url('/users'));
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'TEKNISI_NOC';
        $phone = trim($_POST['phone'] ?? '');

        if (empty($name) || empty($email) || empty($password)) {
            $_SESSION['error'] = "Nama, email, dan password wajib diisi.";
            header("Location: " . url('/users/create'));
            exit;
        }

        $pdo = Database::getInstance()->getConnection();
        
        // Check email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $_SESSION['error'] = "Email sudah terdaftar.";
            header("Location: " . url('/users/create'));
            exit;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        // D-015: Superadmin sudah melakukan verifikasi saat membuat user → langsung APPROVED + ACTIVE.
        // Tidak ada loop approval ulang (berbeda dengan registrasi publik yang tetap PENDING).
        $account_status = 'APPROVED';
        $is_active = 1;
        $photo_path = null;

        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../storage/private_uploads/profiles/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $filename = uniqid('profile_') . '.' . $ext;
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $filename)) {
                    $photo_path = 'profiles/' . $filename;
                }
            }
        }

        $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, phone, account_status, is_active, photo_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $hash, $role, $phone, $account_status, $is_active, $photo_path]);

        $newId = $pdo->lastInsertId();
        $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id, ip_address, user_agent) VALUES (?, 'SUPERADMIN_CREATE_USER', 'users', ?, ?, ?)");
        $auditStmt->execute([AuthHelper::user()['id'], $newId, $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '']);

        $_SESSION['success'] = "User berhasil dibuat dan langsung AKTIF (APPROVED). Tidak perlu persetujuan ulang.";
        header("Location: " . url('/users'));
        exit;
    }

    public function edit($id) {
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $_SESSION['error'] = "User not found.";
            header("Location: " . url('/users'));
            exit;
        }

        $pageTitle = 'Edit User';
        require __DIR__ . '/../../resources/views/users/edit.php';
    }

    public function update($id) {
        if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = "CSRF validation failed";
            header("Location: " . url('/users'));
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'TEKNISI_NOC';
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($name) || empty($email)) {
            $_SESSION['error'] = "Name and email are required.";
            header("Location: " . url('/users/edit/' . $id));
            exit;
        }

        $pdo = Database::getInstance()->getConnection();
        
        // Fetch current user data first (termasuk status akun agar bisa dipertahankan)
        $fetchStmt = $pdo->prepare("SELECT photo_path, is_active, account_status FROM users WHERE id = ?");
        $fetchStmt->execute([$id]);
        $currentUser = $fetchStmt->fetch(PDO::FETCH_ASSOC);
        if (!$currentUser) {
            $_SESSION['error'] = "User not found.";
            header("Location: " . url('/users'));
            exit;
        }

        // Status akun DIpertahankan dari nilai existing — perubahan password/nama/email/role/
        // profil TIDAK boleh mengubah status user menjadi Nonaktif. Status hanya berubah bila
        // Superadmin mengubah field "Status Akun" secara eksplisit di form edit.
        // (Sebelumnya: `isset($_POST['is_active']) ? 1 : 0` — form edit tidak punya field
        //  is_active, sehingga setiap simpan mengubah user menjadi Nonaktif.)
        $is_active = (int)$currentUser['is_active'];
        $account_status = $currentUser['account_status'];
        if (array_key_exists('status', $_POST)) {
            $requestedStatus = $_POST['status'] == '1' ? 1 : 0;
            if ((int)$currentUser['is_active'] !== $requestedStatus) {
                if ($requestedStatus === 0 && (int)$id === (int)AuthHelper::user()['id']) {
                    $_SESSION['error'] = "Anda tidak dapat menonaktifkan akun sendiri.";
                    header("Location: " . url('/users/edit/' . $id));
                    exit;
                }
                $is_active = $requestedStatus;
                $account_status = $requestedStatus === 1 ? 'APPROVED' : 'SUSPENDED';
            }
        }

        // Check email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $id]);
        if ($stmt->fetch()) {
            $_SESSION['error'] = "Email already used by another user.";
            header("Location: " . url('/users/edit/' . $id));
            exit;
        }

        $photo_path = $currentUser['photo_path'];
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../storage/private_uploads/profiles/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $mimeType = mime_content_type($_FILES['photo']['tmp_name']);
                if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'])) {
                    $_SESSION['error'] = "Format foto tidak valid. Gunakan JPG/PNG/WEBP.";
                    header("Location: " . url('/users/edit/' . $id));
                    exit;
                }
                if ($_FILES['photo']['size'] > 2000000) {
                    $_SESSION['error'] = "Ukuran foto terlalu besar (maksimal 2MB).";
                    header("Location: " . url('/users/edit/' . $id));
                    exit;
                }
                $filename = uniqid('profile_' . $id . '_') . '.' . $ext;
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $filename)) {
                    $photo_path = 'profiles/' . $filename;
                }
            } else {
                $_SESSION['error'] = "Ekstensi foto tidak valid. Gunakan JPG/PNG/WEBP.";
                header("Location: " . url('/users/edit/' . $id));
                exit;
            }
        }

        if (!empty($password)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, role = ?, phone = ?, is_active = ?, account_status = ?, password_hash = ?, photo_path = ? WHERE id = ?");
            $stmt->execute([$name, $email, $role, $phone, $is_active, $account_status, $hash, $photo_path, $id]);
            
            $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id, ip_address, user_agent) VALUES (?, 'ADMIN_PASSWORD_RESET', 'users', ?, ?, ?)");
            $auditStmt->execute([AuthHelper::user()['id'], $id, $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '']);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, role = ?, phone = ?, is_active = ?, account_status = ?, photo_path = ? WHERE id = ?");
            $stmt->execute([$name, $email, $role, $phone, $is_active, $account_status, $photo_path, $id]);
        }

        $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id, ip_address, user_agent) VALUES (?, 'UPDATE', 'users', ?, ?, ?)");
        $auditStmt->execute([AuthHelper::user()['id'], $id, $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '']);

        $_SESSION['success'] = "User updated successfully.";
        header("Location: " . url('/users'));
        exit;
    }

    public function show($id) {
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $_SESSION['error'] = "User not found.";
            header("Location: " . url('/users'));
            exit;
        }

        // Account history: audit entries targeting this user + this user's own actions
        $auditStmt = $pdo->prepare("SELECT a.*, u.name AS actor_name FROM audit_logs a LEFT JOIN users u ON u.id = a.user_id WHERE (a.entity = 'users' AND a.entity_id = ?) OR a.user_id = ? ORDER BY a.created_at DESC LIMIT 25");
        $auditStmt->execute([$id, $id]);
        $auditHistory = $auditStmt->fetchAll(PDO::FETCH_ASSOC);

        $pageTitle = 'User Detail';
        require __DIR__ . '/../../resources/views/users/show.php';
    }

    public function toggleActive() {
        AuthHelper::requireRole('SUPERADMIN');
        if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = "CSRF validation failed";
            header("Location: " . url('/users'));
            exit;
        }

        $id = (int)$_POST['id'];
        $pdo = Database::getInstance()->getConnection();

        $stmt = $pdo->prepare("SELECT id, account_status, is_active, role FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            $_SESSION['error'] = "User not found.";
            header("Location: " . url('/users'));
            exit;
        }

        // Prevent deactivating yourself
        if ((int)$user['id'] === (int)AuthHelper::user()['id'] && $user['is_active'] == 1) {
            $_SESSION['error'] = "Anda tidak dapat menonaktifkan akun sendiri.";
            header("Location: " . url('/users/show/' . $id));
            exit;
        }

        if ($user['is_active'] == 1) {
            // Deactivate
            $upd = $pdo->prepare("UPDATE users SET is_active = 0, account_status = 'SUSPENDED' WHERE id = ?");
            $upd->execute([$id]);
            $action = 'DEACTIVATE_USER';
            $msg = "User dinonaktifkan.";
        } else {
            // Activate
            $upd = $pdo->prepare("UPDATE users SET is_active = 1, account_status = 'APPROVED' WHERE id = ?");
            $upd->execute([$id]);
            $action = 'ACTIVATE_USER';
            $msg = "User diaktifkan.";
        }

        $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id, ip_address, user_agent) VALUES (?, ?, 'users', ?, ?, ?)");
        $auditStmt->execute([AuthHelper::user()['id'], $action, $id, $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '']);
        \App\Helpers\NotificationHelper::createForUsers([$id], $user['is_active'] == 1 ? 'Akun Anda dinonaktifkan' : 'Akun Anda diaktifkan kembali', $user['is_active'] == 1 ? 'Akun Anda telah dinonaktifkan oleh Superadmin.' : 'Akun Anda telah diaktifkan kembali.', $user['is_active'] == 1 ? 'warning' : 'success', '/login');

        $_SESSION['success'] = $msg;
        header("Location: " . url('/users/show/' . $id));
        exit;
    }

    public function resetPassword() {
        AuthHelper::requireRole('SUPERADMIN');
        if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = "CSRF validation failed";
            header("Location: " . url('/users'));
            exit;
        }

        $id = (int)$_POST['id'];
        $pdo = Database::getInstance()->getConnection();

        $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            $_SESSION['error'] = "User not found.";
            header("Location: " . url('/users'));
            exit;
        }

        // Generate temporary password (dev mode: shown once in flash)
        $tempPassword = substr(bin2hex(random_bytes(6)), 0, 10);
        $hash = password_hash($tempPassword, PASSWORD_DEFAULT);
        $upd = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $upd->execute([$hash, $id]);

        $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id, ip_address, user_agent) VALUES (?, 'ADMIN_PASSWORD_RESET', 'users', ?, ?, ?)");
        $auditStmt->execute([AuthHelper::user()['id'], $id, $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '']);

        \App\Helpers\NotificationHelper::createForUsers([$id], 'Password di-reset oleh Superadmin', 'Kata sandi akun Anda telah di-reset. Gunakan kata sandi sementara yang diberikan Superadmin untuk masuk.', 'warning', '/login');

        $_SESSION['success'] = "Password berhasil di-reset. Password sementara (ditampilkan sekali): " . $tempPassword;
        header("Location: " . url('/users/show/' . $id));
        exit;
    }

    public function approve() {
        AuthHelper::requireRole('SUPERADMIN');
        if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = "CSRF validation failed";
            header("Location: " . url('/users'));
            exit;
        }

        $id = (int)$_POST['id'];
        $pdo = Database::getInstance()->getConnection();
        
        $stmt = $pdo->prepare("UPDATE users SET account_status = 'APPROVED', is_active = 1, rejection_reason = NULL WHERE id = ? AND account_status = 'PENDING_APPROVAL'");
        if ($stmt->execute([$id])) {
            $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id, ip_address, user_agent) VALUES (?, 'APPROVE_USER', 'users', ?, ?, ?)");
            $auditStmt->execute([AuthHelper::user()['id'], $id, $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '']);
            \App\Helpers\NotificationHelper::createForUsers([$id], 'Akun Anda disetujui', 'Akun Anda telah disetujui dan aktif. Silakan login.', 'success', '/login');
            $_SESSION['success'] = "User approved and activated.";
        } else {
            $_SESSION['error'] = "Failed to approve user.";
        }
        
        $back = ($_SERVER['HTTP_REFERER'] ?? '') && str_contains($_SERVER['HTTP_REFERER'], '/users/show/') ? '/users/show/' . $id : '/users';
        header("Location: " . url($back));
        exit;
    }

    public function reject() {
        AuthHelper::requireRole('SUPERADMIN');
        if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = "CSRF validation failed";
            header("Location: " . url('/users'));
            exit;
        }

        $id = (int)$_POST['id'];
        $reason = trim($_POST['rejection_reason'] ?? '');
        
        if (empty($reason)) {
            $_SESSION['error'] = "Alasan penolakan wajib diisi.";
            header("Location: " . url('/users'));
            exit;
        }

        $pdo = Database::getInstance()->getConnection();
        
        $stmt = $pdo->prepare("UPDATE users SET account_status = 'REJECTED', is_active = 0, rejection_reason = ? WHERE id = ? AND account_status = 'PENDING_APPROVAL'");
        if ($stmt->execute([$reason, $id])) {
            $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id, ip_address, user_agent) VALUES (?, 'REJECT_USER', 'users', ?, ?, ?)");
            $auditStmt->execute([AuthHelper::user()['id'], $id, $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '']);
            \App\Helpers\NotificationHelper::createForUsers([$id], 'Pendaftaran akun ditolak', 'Pendaftaran akun Anda ditolak. Alasan: ' . ($reason ?: '-'), 'danger', null);
            $_SESSION['success'] = "User registration rejected.";
        } else {
            $_SESSION['error'] = "Failed to reject user.";
        }
        
        $back = ($_SERVER['HTTP_REFERER'] ?? '') && str_contains($_SERVER['HTTP_REFERER'], '/users/show/') ? '/users/show/' . $id : '/users';
        header("Location: " . url($back));
        exit;
    }
}
