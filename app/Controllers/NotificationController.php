<?php
namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Helpers\NotificationHelper;

class NotificationController {

    public function __construct() {
        // Semua role yang sudah login boleh melihat notifikasinya sendiri.
        AuthHelper::requireLogin();
    }

    public function index() {
        $userId = (int)AuthHelper::user()['id'];
        $notifications = NotificationHelper::recent($userId, 50);
        $pageTitle = 'Notifikasi';
        require __DIR__ . '/../../resources/views/notifications/index.php';
    }

    public function markRead() {
        if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = "CSRF validation failed";
            header("Location: " . url('/notifications'));
            exit;
        }
        $userId = (int)AuthHelper::user()['id'];
        $id = (int)($_POST['id'] ?? 0);
        NotificationHelper::markRead($userId, $id);
        header("Location: " . url('/notifications'));
        exit;
    }

    public function markAllRead() {
        if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = "CSRF validation failed";
            header("Location: " . url('/notifications'));
            exit;
        }
        $userId = (int)AuthHelper::user()['id'];
        NotificationHelper::markAllRead($userId);
        $_SESSION['success'] = "Semua notifikasi ditandai sudah dibaca.";
        header("Location: " . url('/notifications'));
        exit;
    }
}
