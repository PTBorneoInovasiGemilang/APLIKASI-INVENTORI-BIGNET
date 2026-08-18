<?php
$pageTitle = 'Barang Keluar';
$title = 'Barang Keluar';
ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Barang Keluar</h1>
    <?php if (!\App\Helpers\AuthHelper::hasRole('SUPERADMIN')): ?>
    <a href="<?php echo url('/outgoing/create'); ?>" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow transition-colors">
        <i class="fas fa-plus mr-2"></i> Pengeluaran Baru
    </a>
    <?php endif; ?>
</div>

    <?php
    $toolbarAction = url('/outgoing');
    $searchPlaceholder = "Cari Kode / Ref...";
    ob_start();
    ?>
    <div>
        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Gudang</label>
        <select name="warehouse_id" class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-40 py-2 px-3 text-sm text-gray-700 dark:text-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="0">Semua Gudang</option>
            <?php foreach ($warehouses as $wh): ?>
                <option value="<?php echo $wh['id']; ?>" <?php echo (isset($_GET['warehouse_id']) && $_GET['warehouse_id'] == $wh['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($wh['name']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php
    $extraFilters = ob_get_clean();
    require __DIR__ . '/../../components/table/toolbar.php';
    ?>

<div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[1000px] table-sticky divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900/50">
                <tr>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-12">No</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kode Transaksi</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Gudang</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Diproses Oleh</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ref Pengajuan</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
            </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            <?php 
            $no = $offset + 1;
            foreach ($outgoings as $out): 
            ?>
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400"><?= $no++ ?></td>
                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100"><?php echo htmlspecialchars($out['transaction_code']); ?></td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><?php echo date('Y-m-d H:i', strtotime($out['created_at'])); ?></td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($out['warehouse_name']); ?></td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($out['processor_name']); ?></td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                    <?php if ($out['submission_code']): ?>
                        <a href="<?= url('/submissions/show?id=' . $out['submission_id']) ?>" class="text-blue-600 hover:underline"><?= htmlspecialchars($out['submission_code']) ?></a>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <?php
                $rowActions = [['type' => 'view', 'url' => url('/outgoing/show?id=' . (int)$out['id']), 'label' => 'Lihat', 'icon' => 'fa-eye', 'title' => 'Detail Barang Keluar']];
                require __DIR__ . '/../../components/table/row_actions.php';
                ?>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($outgoings)): ?>
                <?php
                $colspan = 7;
                $emptyMessage = "Belum ada transaksi barang keluar.";
                require __DIR__ . '/../../components/table/empty_state.php';
                ?>
            <?php endif; ?>
        </tbody>
        </table>
    </div>
    
    <?php 
    $paginationBaseUrl = url('/outgoing');
    require __DIR__ . '/../../components/table/pagination.php';
    ?>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
