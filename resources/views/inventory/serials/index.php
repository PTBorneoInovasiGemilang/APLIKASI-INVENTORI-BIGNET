<?php
$pageTitle = 'Nomor Seri';
$title = 'Nomor Seri';
ob_start();
?>

<div class="flex flex-wrap justify-between items-center gap-3 mb-6">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Nomor Seri</h1>
    <?php if (!empty($serials)): ?>
    <a href="<?= url('/labels?serial_ids=' . implode(',', array_column($serials, 'id'))) ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-semibold transition-colors shadow-sm">
        <i class="fas fa-print"></i> Cetak Semua Label (<?= count($serials) ?>)
    </a>
    <?php endif; ?>
</div>

<?php
$toolbarAction = url('/serials');
$searchPlaceholder = "Cari SN / Nama Barang...";
ob_start();
?>
<div>
    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Status</label>
    <select name="status" class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-36 py-2 px-3 text-sm text-gray-700 dark:text-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="">Semua Status</option>
        <option value="AVAILABLE" <?php echo (isset($_GET['status']) && $_GET['status'] === 'AVAILABLE') ? 'selected' : ''; ?>>Tersedia</option>
        <option value="BORROWED" <?php echo (isset($_GET['status']) && $_GET['status'] === 'BORROWED') ? 'selected' : ''; ?>>Dipinjam</option>
        <option value="INSTALLED" <?php echo (isset($_GET['status']) && $_GET['status'] === 'INSTALLED') ? 'selected' : ''; ?>>Terpasang</option>
        <option value="IN_TRANSIT" <?php echo (isset($_GET['status']) && $_GET['status'] === 'IN_TRANSIT') ? 'selected' : ''; ?>>Dalam Perjalanan</option>
        <option value="REPAIR" <?php echo (isset($_GET['status']) && $_GET['status'] === 'REPAIR') ? 'selected' : ''; ?>>Perbaikan</option>
        <option value="BROKEN" <?php echo (isset($_GET['status']) && $_GET['status'] === 'BROKEN') ? 'selected' : ''; ?>>Rusak</option>
    </select>
</div>
<?php
$extraFilters = ob_get_clean();
require __DIR__ . '/../../components/table/toolbar.php';
?>

<div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[1000px] table-sticky mobile-card-table divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900/50">
                <tr>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider w-12">No</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">No. Seri</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Barang</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Gudang</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Pemegang</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <?php 
                $no = ($offset ?? 0) + 1;
                foreach ($serials as $sn): 
                ?>
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <td data-label="No" class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center"><?= $no++ ?></td>
                    <td data-label="No. Seri" class="px-4 py-3 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-gray-100"><?php echo htmlspecialchars($sn['serial_number']); ?></td>
                    <td data-label="Barang" class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        <?php echo htmlspecialchars($sn['item_name']); ?>
                        <div class="text-xs text-gray-400"><?php echo htmlspecialchars($sn['kode_barang']); ?></div>
                    </td>
                    <td data-label="Gudang" class="px-4 py-3 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($sn['warehouse_name'] ?? '-'); ?></td>
                    <td data-label="Pemegang" class="px-4 py-3 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($sn['holder_name'] ?? '-'); ?></td>
                    <td data-label="Status" class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                        <?php 
                        $st = $sn['status'];
                        $badgeType = 'NEUTRAL';
                        if ($st === 'AVAILABLE') $badgeType = 'SUCCESS';
                        else if ($st === 'BORROWED') $badgeType = 'WARNING';
                        else if ($st === 'INSTALLED') $badgeType = 'INFO';
                        else if ($st === 'IN_TRANSIT') $badgeType = 'WARNING';
                        else if ($st === 'BROKEN' || $st === 'REPAIR') $badgeType = 'DANGER';
                        
                        echo \App\Helpers\UIHelper::badge($st, $badgeType);
                        ?>
                    </td>
                    <?php
                    $rowActions = [
                        ['type' => 'view', 'url' => url('/timeline?type=SN&ref_id=' . (int)$sn['id']), 'label' => 'Lihat Perjalanan', 'icon' => 'fa-route', 'title' => 'Lihat Perjalanan Serial'],
                        ['type' => 'link', 'url' => url('/labels?serial=' . urlencode($sn['serial_number'])), 'label' => 'Label', 'icon' => 'fa-barcode', 'title' => 'Cetak label barcode/QR']
                    ];
                    require __DIR__ . '/../../components/table/row_actions.php';
                    ?>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($serials)): ?>
                    <?php 
                    $colspan = 7;
                    $emptyMessage = "Belum ada nomor seri.";
                    require __DIR__ . '/../../components/table/empty_state.php';
                    ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php 
    $paginationBaseUrl = url('/serials');
    require __DIR__ . '/../../components/table/pagination.php'; 
    ?>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
