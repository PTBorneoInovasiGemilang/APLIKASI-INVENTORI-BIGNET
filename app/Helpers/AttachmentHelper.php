<?php
namespace App\Helpers;

use App\Core\Database;
use PDO;

/**
 * Bukti/attachment untuk workflow (incident, instalasi, opname, return, dll).
 * File disimpan di storage/private_uploads/evidence (bukan public root).
 */
class AttachmentHelper {

    public const ALLOWED_MIME = [
        'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp',
        'application/pdf' => 'pdf',
    ];
    public const MAX_SIZE = 5 * 1024 * 1024; // 5 MB

    /**
     * Validasi + simpan upload ke secure storage, insert row, kembalikan id.
     */
    public static function store(string $referenceType, int $referenceId, array $file, int $uploadedBy): ?int {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }
        if ($file['size'] > self::MAX_SIZE) {
            throw new \RuntimeException('Ukuran file maksimal 5MB.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']) ?: '';
        if (!isset(self::ALLOWED_MIME[$mime])) {
            throw new \RuntimeException('Tipe file tidak diizinkan. Gunakan JPG/PNG/WEBP/PDF.');
        }

        $ext = self::ALLOWED_MIME[$mime];
        $dir = __DIR__ . '/../../storage/private_uploads/evidence/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $filename = uniqid('ev_' . $referenceType . '_') . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], $dir . $filename)) {
            throw new \RuntimeException('Gagal menyimpan file.');
        }

        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare("INSERT INTO attachments (reference_type, reference_id, file_path, original_name, mime_type, file_size, uploaded_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $referenceType, $referenceId, 'evidence/' . $filename,
            basename($file['name']), $mime, (int)$file['size'], $uploadedBy,
        ]);
        return (int)$pdo->lastInsertId();
    }

    /**
     * Daftar attachment untuk referensi, lengkap metadata uploader.
     */
    public static function forReference(string $referenceType, int $referenceId): array {
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare("SELECT a.*, u.name AS uploader_name FROM attachments a LEFT JOIN users u ON u.id = a.uploaded_by WHERE a.reference_type = ? AND a.reference_id = ? ORDER BY a.created_at DESC, a.id DESC");
        $stmt->execute([$referenceType, $referenceId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Authorization: uploader sendiri, atau SUPERADMIN/ADMIN.
     */
    public static function canView(array $attachment, ?array $user = null): bool {
        $user = $user ?? AuthHelper::user();
        if (!$user) return false;
        if (in_array($user['role'], ['SUPERADMIN', 'ADMIN'])) return true;
        return (int)$attachment['uploaded_by'] === (int)$user['id'];
    }

    public static function canDelete(array $attachment, ?array $user = null): bool {
        $user = $user ?? AuthHelper::user();
        if (!$user) return false;
        return in_array($user['role'], ['SUPERADMIN', 'ADMIN']) || (int)$attachment['uploaded_by'] === (int)$user['id'];
    }
}
