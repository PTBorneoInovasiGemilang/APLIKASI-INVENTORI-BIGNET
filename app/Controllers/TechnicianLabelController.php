<?php

namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Core\Database;
use PDO;

/**
 * Label printing entry point for the user's own serials.
 * TEKNISI_NOC is restricted to serials currently assigned to that technician.
 * SUPERADMIN/ADMIN may print the requested serial IDs without the ownership filter.
 */
class TechnicianLabelController
{
    public function __construct()
    {
        AuthHelper::requireRole(['SUPERADMIN', 'ADMIN', 'TEKNISI_NOC']);
    }

    public function index()
    {
        $serialIds = trim($_GET['serial_ids'] ?? '');
        if ($serialIds === '') {
            header('Location: ' . url('/serials'));
            exit;
        }

        $ids = array_values(array_unique(array_filter(array_map('intval', explode(',', $serialIds)))));
        if (empty($ids)) {
            header('Location: ' . url('/serials'));
            exit;
        }

        $pdo = Database::getInstance()->getConnection();
        $user = AuthHelper::user();

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $params = $ids;
        $ownershipSql = '';

        if (($user['role'] ?? '') === 'TEKNISI_NOC') {
            $ownershipSql = ' AND sn.current_holder_id = ?';
            $params[] = (int)$user['id'];
        }

        $stmt = $pdo->prepare("SELECT sn.id, sn.serial_number, m.kode_barang, m.nama, m.is_serialized
                               FROM serial_numbers sn
                               JOIN master_items m ON sn.master_item_id = m.id
                               WHERE sn.id IN ($placeholders) $ownershipSql
                               ORDER BY sn.id DESC");
        $stmt->execute($params);
        $serials = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($serials)) {
            $_SESSION['error'] = 'Tidak ada nomor seri yang dapat dicetak untuk akun Anda.';
            header('Location: ' . url('/serials'));
            exit;
        }

        $pageTitle = 'Cetak Label Barcode';
        require __DIR__ . '/../../resources/views/labels/index.php';
    }
}
