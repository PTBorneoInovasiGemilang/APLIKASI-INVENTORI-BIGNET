<?php
namespace App\Helpers;

use App\Core\Database;
use PDO;

class NotificationHelper {

    /**
     * Buat notifikasi untuk daftar user (user-isolated, bukan broadcast).
     */
    public static function createForUsers(array $userIds, string $title, string $message = '', string $type = 'info', ?string $sourceRoute = null): void {
        $userIds = array_values(array_unique(array_map('intval', array_filter($userIds))));
        if (empty($userIds)) return;

        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, source_route) VALUES (?, ?, ?, ?, ?)");
        foreach ($userIds as $uid) {
            $stmt->execute([$uid, $title, $message, $type, $sourceRoute]);
        }
    }

    /**
     * Buat notifikasi untuk semua user dengan role tertentu (mis. SUPERADMIN).
     */
    public static function createForRole(string $role, string $title, string $message = '', string $type = 'info', ?string $sourceRoute = null): void {
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare("SELECT id FROM users WHERE role = ? AND account_status = 'APPROVED' AND is_active = 1");
        $stmt->execute([$role]);
        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
        self::createForUsers($ids, $title, $message, $type, $sourceRoute);
    }

    public static function unreadCount(int $userId): int {
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND read_at IS NULL");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Daftar notifikasi milik user (isolation via WHERE user_id).
     */
    public static function recent(int $userId, int $limit = 50): array {
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC, id DESC LIMIT " . (int)$limit);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Tandai satu notifikasi dibaca — hanya milik user itu.
     */
    public static function markRead(int $userId, int $id): bool {
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare("UPDATE notifications SET read_at = NOW() WHERE id = ? AND user_id = ? AND read_at IS NULL");
        return $stmt->execute([$id, $userId]);
    }

    /**
     * Tandai semua notifikasi user dibaca.
     */
    public static function markAllRead(int $userId): void {
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare("UPDATE notifications SET read_at = NOW() WHERE user_id = ? AND read_at IS NULL");
        $stmt->execute([$userId]);
    }

    /* =========== DEDUP variants (prevent duplicate on double-submit) =========== */

    /**
     * createForUsersOnce — same as createForUsers tapi skip jika sudah ada
     * notifikasi unread dengan title+source_route yang sama untuk user tsb.
     * Dedup key: (user_id, title, source_route) WHERE read_at IS NULL.
     */
    public static function createForUsersOnce(array $userIds, string $title, string $message = '', string $type = 'info', ?string $sourceRoute = null): void {
        $userIds = array_values(array_unique(array_map('intval', array_filter($userIds))));
        if (empty($userIds)) return;

        $pdo = Database::getInstance()->getConnection();
        $chk = $pdo->prepare("SELECT id FROM notifications WHERE user_id = ? AND title = ? AND source_route = ? AND read_at IS NULL LIMIT 1");
        $ins = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, source_route) VALUES (?, ?, ?, ?, ?)");
        foreach ($userIds as $uid) {
            $chk->execute([$uid, $title, $sourceRoute]);
            if ($chk->fetch()) continue;
            $ins->execute([$uid, $title, $message, $type, $sourceRoute]);
        }
    }

    /**
     * createForRoleOnce — same as createForRole tapi dedup per-user.
     */
    public static function createForRoleOnce(string $role, string $title, string $message = '', string $type = 'info', ?string $sourceRoute = null): void {
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare("SELECT id FROM users WHERE role = ? AND account_status = 'APPROVED' AND is_active = 1");
        $stmt->execute([$role]);
        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
        self::createForUsersOnce($ids, $title, $message, $type, $sourceRoute);
    }
}
