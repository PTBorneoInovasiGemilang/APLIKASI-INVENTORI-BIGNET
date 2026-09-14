<?php
namespace App\Controllers;

use App\Core\Database;
use App\Helpers\AuthHelper;
use PDO;

class UserDeleteController
{
    public function __construct()
    {
        AuthHelper::requireRole('SUPERADMIN');
    }

    public function delete()
    {
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'CSRF validation failed.';
            header('Location: ' . url('/users'));
            exit;
        }

        $targetId = (int)($_POST['id'] ?? 0);
        $actor = AuthHelper::user();
        $actorId = (int)($actor['id'] ?? 0);

        if ($targetId <= 0) {
            $_SESSION['error'] = 'User yang akan dihapus tidak valid.';
            header('Location: ' . url('/users'));
            exit;
        }

        if ($targetId === $actorId) {
            $_SESSION['error'] = 'Anda tidak dapat menghapus akun sendiri. Gunakan akun Superadmin lain.';
            header('Location: ' . url('/users'));
            exit;
        }

        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare('SELECT id, name, email, role, is_active, account_status, photo_path FROM users WHERE id = ?');
        $stmt->execute([$targetId]);
        $target = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$target) {
            $_SESSION['error'] = 'User tidak ditemukan.';
            header('Location: ' . url('/users'));
            exit;
        }

        // Jangan biarkan Superadmin terakhir yang aktif terhapus.
        if ($target['role'] === 'SUPERADMIN' && (int)$target['is_active'] === 1) {
            $countStmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'SUPERADMIN' AND is_active = 1");
            $activeSuperadmins = (int)$countStmt->fetchColumn();
            if ($activeSuperadmins <= 1) {
                $_SESSION['error'] = 'Superadmin aktif terakhir tidak boleh dihapus.';
                header('Location: ' . url('/users'));
                exit;
            }
        }

        /*
         * Audit schema secara runtime terhadap database aktif.
         * User hanya boleh hard-delete bila tidak ada FK RESTRICT/NO ACTION
         * yang masih menunjuk ke users.id. Ini mengikuti schema DB terbaru
         * tanpa meng-hardcode daftar tabel transaksi.
         */
        $fkStmt = $pdo->prepare(
            "SELECT k.TABLE_NAME, k.COLUMN_NAME, k.CONSTRAINT_NAME
             FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE k
             INNER JOIN INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS r
               ON r.CONSTRAINT_SCHEMA = k.CONSTRAINT_SCHEMA
              AND r.CONSTRAINT_NAME = k.CONSTRAINT_NAME
              AND r.TABLE_NAME = k.TABLE_NAME
             WHERE k.CONSTRAINT_SCHEMA = DATABASE()
               AND k.REFERENCED_TABLE_SCHEMA = DATABASE()
               AND k.REFERENCED_TABLE_NAME = 'users'
               AND r.DELETE_RULE IN ('RESTRICT', 'NO ACTION')"
        );
        $fkStmt->execute();

        $blockers = [];
        while ($fk = $fkStmt->fetch(PDO::FETCH_ASSOC)) {
            $table = $fk['TABLE_NAME'];
            $column = $fk['COLUMN_NAME'];
            $countSql = 'SELECT COUNT(*) FROM `' . str_replace('`', '``', $table) . '` WHERE `' . str_replace('`', '``', $column) . '` = ?';
            $countRef = $pdo->prepare($countSql);
            $countRef->execute([$targetId]);
            $count = (int)$countRef->fetchColumn();
            if ($count > 0) {
                $blockers[] = $table . '.' . $column . ' (' . $count . ')';
            }
        }

        if (!empty($blockers)) {
            $display = array_slice($blockers, 0, 5);
            $extra = count($blockers) > 5 ? ' dan relasi lain' : '';
            $_SESSION['error'] = 'User tidak dapat dihapus karena masih dipakai oleh data: ' . implode(', ', $display) . $extra . '. Nonaktifkan akun bila user sudah tidak digunakan.';
            header('Location: ' . url('/users'));
            exit;
        }

        $pdo->beginTransaction();
        try {
            // Catat aksi actor sebelum target user dihapus.
            $auditStmt = $pdo->prepare(
                "INSERT INTO audit_logs (user_id, action, entity, entity_id, ip_address, user_agent)
                 VALUES (?, 'DELETE_USER', 'users', ?, ?, ?)"
            );
            $auditStmt->execute([
                $actorId,
                $targetId,
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);

            $deleteStmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
            $deleteStmt->execute([$targetId]);

            if ($deleteStmt->rowCount() !== 1) {
                throw new \RuntimeException('Tidak ada user yang terhapus.');
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['error'] = 'Gagal menghapus user: ' . $e->getMessage();
            header('Location: ' . url('/users'));
            exit;
        }

        // Bersihkan foto profil milik target jika file masih ada.
        $photoPath = trim((string)($target['photo_path'] ?? ''));
        if ($photoPath !== '' && strpos($photoPath, 'profiles/') === 0) {
            $uploadRoot = realpath(__DIR__ . '/../../storage/private_uploads');
            $candidate = $uploadRoot ? realpath($uploadRoot . DIRECTORY_SEPARATOR . $photoPath) : false;
            if ($uploadRoot && $candidate && strpos($candidate, $uploadRoot . DIRECTORY_SEPARATOR) === 0 && is_file($candidate)) {
                @unlink($candidate);
            }
        }

        $_SESSION['success'] = 'User "' . ($target['name'] ?? $target['email']) . '" berhasil dihapus.';
        header('Location: ' . url('/users'));
        exit;
    }
}
