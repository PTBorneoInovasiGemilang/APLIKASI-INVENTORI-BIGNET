<?php

namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Core\Database;
use PDO;

class ImportExportController {
    
    public function __construct() {
        AuthHelper::requireRole(['SUPERADMIN', 'ADMIN']);
    }

    public function index() {
        require __DIR__ . '/../../resources/views/import_export/index.php';
    }

    public function exportItems() {
        $pdo = Database::getInstance()->getConnection();
        
        $sql = "SELECT m.kode_barang, m.nama, c.name as category, u.name as unit, 
                IF(m.is_serialized, 'Yes', 'No') as is_serialized, m.stok_minimum, 
                IF(m.is_active, 'Active', 'Inactive') as status
                FROM master_items m 
                LEFT JOIN item_categories c ON m.item_category_id = c.id 
                LEFT JOIN units u ON m.unit_id = u.id";
                
        $stmt = $pdo->query($sql);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=master_items_' . date('Ymd_His') . '.csv');

        $output = fopen('php://output', 'w');
        // Add UTF-8 BOM for Excel compatibility
        fwrite($output, "\xEF\xBB\xBF");
        
        // Header
        fputcsv($output, ['Kode Barang', 'Nama', 'Category', 'Unit', 'Is Serialized', 'Min Stock', 'Status'], ';');

        // Rows
        foreach ($items as $item) {
            fputcsv($output, $item, ';');
        }
        
        fclose($output);
        exit;
    }

    public function templateItems() {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=template_import_master_items.csv');
        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF");
        // Header: tanda * = wajib, (opsional) = boleh kosong
        fputcsv($output, ['Kode Barang (opsional, kosongkan untuk auto)', 'Nama Barang *', 'ID Kategori *', 'ID Satuan *', 'Serialized * (1=berseri / 0=tidak)', 'Min Stok (default 0)', 'Status * (1=aktif / 0=nonaktif)'], ';');
        fputcsv($output, ['', 'Contoh Barang 1', '1', '1', '0', '10', '1'], ';');
        fputcsv($output, ['BRG-000001', 'Contoh Barang 2 (kode ditentukan manual)', '2', '2', '1', '5', '1'], ';');
        // Instruksi: 2 baris terakhir dimulai dengan # agar tidak diimpor sebagai data
        fputcsv($output, ['# PANDUAN:', '# 1) Pemisah kolom = titik koma (;).', '# 2) Encoding = UTF-8 (BOM otomatis).', '# 3) Nama * dan ID Kategori/Satuan * wajib diisi.', '# 4) ID Kategori & ID Satuan = angka dari halaman Master Data (Kategori / Satuan).', '# 5) Kode Barang boleh dikosongkan — sistem akan membuat otomatis (format KODEKATEGORI-000001).', '# 6) Serialized & Status hanya menerima angka 0 atau 1.', '# 7) Baris contoh di atas hanya ilustrasi — hapus sebelum mengunggah.'], ';');
        fclose($output);
        exit;
    }

    public function exportCategories() {
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->query("SELECT id, code, name, description, is_active FROM item_categories");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=categories_' . date('Ymd_His') . '.csv');
        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, ['ID', 'Code', 'Name', 'Description', 'Is Active (1/0)'], ';');
        foreach ($categories as $c) {
            fputcsv($output, $c, ';');
        }
        fclose($output);
        exit;
    }

    public function exportWarehouses() {
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->query("SELECT id, code, name, address, description, is_active FROM warehouses");
        $warehouses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=warehouses_' . date('Ymd_His') . '.csv');
        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, ['ID', 'Kode', 'Nama Gudang', 'Alamat', 'Deskripsi', 'Is Active (1/0)'], ';');
        foreach ($warehouses as $w) {
            fputcsv($output, $w, ';');
        }
        fclose($output);
        exit;
    }

    public function exportSerials() {
        $pdo = Database::getInstance()->getConnection();
        $sql = "SELECT sn.serial_number, m.kode_barang, m.nama as item_name, w.name as warehouse, u.name as holder, sn.status, sn.condition_status
                FROM serial_numbers sn 
                JOIN master_items m ON sn.master_item_id = m.id 
                LEFT JOIN warehouses w ON sn.current_warehouse_id = w.id 
                LEFT JOIN users u ON sn.current_holder_id = u.id";
        $stmt = $pdo->query($sql);
        $serials = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=serials_' . date('Ymd_His') . '.csv');
        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, ['Serial Number', 'Kode Barang', 'Nama Barang', 'Gudang', 'Holder', 'Status', 'Condition'], ';');
        foreach ($serials as $s) {
            fputcsv($output, $s, ';');
        }
        fclose($output);
        exit;
    }

    public function importItems() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = "Invalid method.";
            header("Location: " . url('/import-export'));
            exit;
        }
        if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = "CSRF validation failed.";
            header("Location: " . url('/import-export'));
            exit;
        }

        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = "File upload failed.";
            header("Location: " . url('/import-export'));
            exit;
        }

        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, "r");
        
        if ($handle === false) {
            $_SESSION['error'] = "Cannot read file.";
            header("Location: " . url('/import-export'));
            exit;
        }

        $pdo = Database::getInstance()->getConnection();
        $user_id = AuthHelper::user()['id'];

        $header = fgetcsv($handle, 1000, ";"); // Skip header
        
        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        // Cache ID kategori & satuan yang valid (baris: ID => ada/tidak)
        $validCat = [];
        $validUnit = [];
        $checkCat = $pdo->prepare("SELECT id FROM item_categories WHERE id = ?");
        $checkUnit = $pdo->prepare("SELECT id FROM units WHERE id = ?");

        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO master_items (kode_barang, nama, item_category_id, unit_id, is_serialized, stok_minimum, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");

            // Generator kode otomatis: cari kode terakhir per kategori (pola sama dengan MasterItemController)
            $stmtMax = $pdo->prepare("SELECT kode_barang FROM master_items WHERE kode_barang LIKE ? ORDER BY id DESC LIMIT 1 FOR UPDATE");
            $stmtCatCode = $pdo->prepare("SELECT code FROM item_categories WHERE id = ?");

            $autoKode = function ($catId) use ($pdo, $stmtMax, $stmtCatCode) {
                $stmtCatCode->execute([$catId]);
                $catCode = $stmtCatCode->fetchColumn();
                if (!$catCode) $catCode = 'BRG';
                $stmtMax->execute([$catCode . '-%']);
                $lastCode = $stmtMax->fetchColumn();
                if ($lastCode) {
                    $lastNum = (int)str_replace($catCode . '-', '', $lastCode);
                    $newNum = str_pad($lastNum + 1, 6, '0', STR_PAD_LEFT);
                } else {
                    $newNum = '000001';
                }
                return $catCode . '-' . $newNum;
            };

            $rowIdx = 1;
            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                $rowIdx++;
                $raw = array_map('trim', $data);

                // Lewati baris panduan (#...) agar template bisa berisi instruksi
                if (isset($raw[0]) && str_starts_with($raw[0], '#')) {
                    continue;
                }
                // Lewati baris kosong
                if (count(array_filter($raw)) === 0) {
                    continue;
                }

                if (count($raw) < 7) {
                    $errorCount++;
                    $errors[] = "Baris $rowIdx: jumlah kolom kurang (harus 7 kolom sesuai template).";
                    continue;
                }

                $kode = $raw[0];
                $nama = $raw[1];
                $cat_id = (int)$raw[2];
                $unit_id = (int)$raw[3];
                $is_ser_raw = $raw[4];
                $min_st = (int)$raw[5];
                $status_raw = $raw[6];

                if (empty($nama)) {
                    $errorCount++;
                    $errors[] = "Baris $rowIdx, kolom Nama: wajib diisi.";
                    continue;
                }

                if (empty($raw[2]) || (int)$raw[2] <= 0) {
                    $errorCount++;
                    $errors[] = "Baris $rowIdx, kolom ID Kategori: wajib diisi angka > 0.";
                    continue;
                }
                if (!isset($validCat[$cat_id])) {
                    $checkCat->execute([$cat_id]);
                    $validCat[$cat_id] = (bool)$checkCat->fetchColumn();
                }
                if (!$validCat[$cat_id]) {
                    $errorCount++;
                    $errors[] = "Baris $rowIdx, kolom ID Kategori: ID $cat_id tidak ditemukan di Master Kategori.";
                    continue;
                }

                if (empty($raw[3]) || (int)$raw[3] <= 0) {
                    $errorCount++;
                    $errors[] = "Baris $rowIdx, kolom ID Satuan: wajib diisi angka > 0.";
                    continue;
                }
                if (!isset($validUnit[$unit_id])) {
                    $checkUnit->execute([$unit_id]);
                    $validUnit[$unit_id] = (bool)$checkUnit->fetchColumn();
                }
                if (!$validUnit[$unit_id]) {
                    $errorCount++;
                    $errors[] = "Baris $rowIdx, kolom ID Satuan: ID $unit_id tidak ditemukan di Master Satuan.";
                    continue;
                }

                if (!in_array($is_ser_raw, ['0', '1'], true)) {
                    $errorCount++;
                    $errors[] = "Baris $rowIdx, kolom Serialized: hanya menerima 0 atau 1.";
                    continue;
                }
                if (!in_array($status_raw, ['0', '1'], true)) {
                    $errorCount++;
                    $errors[] = "Baris $rowIdx, kolom Status: hanya menerima 0 atau 1.";
                    continue;
                }

                $is_ser = (int)$is_ser_raw;
                $status = (int)$status_raw;

                // Kode otomatis bila kosong (sesuai template: "Kosongkan jika auto")
                if ($kode === '') {
                    $kode = $autoKode($cat_id);
                }

                try {
                    $stmt->execute([$kode, $nama, $cat_id, $unit_id, $is_ser, $min_st, $status]);
                    $successCount++;
                } catch (\PDOException $e) {
                    $errorCount++;
                    if (strpos($e->getMessage(), 'Duplicate') !== false || strpos($e->getMessage(), 'duplicate') !== false) {
                        $errors[] = "Baris $rowIdx, kolom Kode Barang: kode \"$kode\" sudah digunakan.";
                    } else {
                        $errors[] = "Baris $rowIdx: kesalahan database — " . $e->getMessage();
                    }
                }
            }
            if ($errorCount > 0) {
                $pdo->rollBack();
                $successCount = 0;
                $_SESSION['error'] = "Import dibatalkan. Terdapat " . $errorCount . " baris bermasalah — tidak ada data yang disimpan (semua-atau-tidak sama sekali).";
            } else {
                $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, new_data) VALUES (?, 'IMPORT', 'master_items', ?)");
                $auditStmt->execute([$user_id, json_encode(['note' => "Imported $successCount items."])]);
                $pdo->commit();
                $_SESSION['success'] = "Import selesai. $successCount barang berhasil ditambahkan.";
            }
        } catch (\Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Fatal Import Error: " . $e->getMessage();
            fclose($handle);
            header("Location: " . url('/import-export'));
            exit;
        }

        fclose($handle);

        $_SESSION['import_result'] = [
            'success' => $successCount,
            'error' => $errorCount,
            'error_details' => $errors
        ];

        header("Location: " . url('/import-export'));
        exit;
    }
}
