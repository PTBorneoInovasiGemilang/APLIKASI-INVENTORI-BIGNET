<?php
namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Helpers\AttachmentHelper;
use App\Core\Database;
use PDO;

class EvidenceController {

    public function __construct() {
        AuthHelper::requireLogin();
    }

    public function view($id) {
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare("SELECT * FROM attachments WHERE id = ?");
        $stmt->execute([(int)$id]);
        $att = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$att || !AttachmentHelper::canView($att)) {
            http_response_code(403);
            require __DIR__ . '/../../resources/views/errors/403.php';
            exit;
        }

        $fullPath = realpath(__DIR__ . '/../../storage/private_uploads/' . $att['file_path']);
        if ($fullPath === false || strpos($fullPath, realpath(__DIR__ . '/../../storage/private_uploads/')) !== 0 || !is_file($fullPath)) {
            http_response_code(404);
            echo 'File tidak ditemukan.';
            exit;
        }

        header('Content-Type: ' . $att['mime_type']);
        header('Content-Length: ' . filesize($fullPath));
        header('Content-Disposition: inline; filename="' . basename($att['original_name']) . '"');
        readfile($fullPath);
        exit;
    }

    public function delete() {
        if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = "CSRF validation failed";
            header("Location: " . url('/dashboard'));
            exit;
        }
        $id = (int)($_POST['id'] ?? 0);
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare("SELECT * FROM attachments WHERE id = ?");
        $stmt->execute([$id]);
        $att = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$att || !AttachmentHelper::canDelete($att)) {
            http_response_code(403);
            require __DIR__ . '/../../resources/views/errors/403.php';
            exit;
        }

        $fullPath = realpath(__DIR__ . '/../../storage/private_uploads/' . $att['file_path']);
        if ($fullPath !== false && strpos($fullPath, realpath(__DIR__ . '/../../storage/private_uploads/')) === 0 && is_file($fullPath)) {
            @unlink($fullPath);
        }
        $pdo->prepare("DELETE FROM attachments WHERE id = ?")->execute([$id]);

        $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id, ip_address, user_agent) VALUES (?, 'DELETE_EVIDENCE', 'attachments', ?, ?, ?)");
        $audit->execute([AuthHelper::user()['id'], $id, $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '']);

        $_SESSION['success'] = "Bukti dihapus.";
        $back = $_SERVER['HTTP_REFERER'] ?? url('/dashboard');
        header("Location: " . $back);
        exit;
    }
}
