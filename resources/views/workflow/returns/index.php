<?php
$pageTitle = 'Pengembalian Barang';
ob_start();
$role = \App\Helpers\AuthHelper::user()['role'];
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Pengembalian Barang</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Pengembalian barang pinjaman diproses Admin setelah pemeriksaan kondisi.</p>
        </div>
        <?php if ($role === 'TEKNISI_NOC'): ?>
            <a href="<?= url('/returns/create') ?>" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow inline-flex items-center">
                <i class="fas fa-plus mr-2"></i>Ajukan Pengembalian
            </a>
        <?php endif; ?>
    </div>

    <!-- Toolbar -->
    <?php 
    $toolbarAction = url('/returns');
    $searchPlaceholder = 'Cari nama teknisi / gudang...';
    ob_start();
    ?>
    <div>
        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Status</label>
        <select name="status" class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-full sm:w-44 py-2 px-3 text-sm text-gray-700 dark:text-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Semua Status</option>
            <option value="PENDING" <?= (isset($_GET['status']) && $_GET['status'] === 'PENDING') ? 'selected' : '' ?>>Menunggu Proses</option>
            <option value="COMPLETED" <?= (isset($_GET['status']) && $_GET['status'] === 'COMPLETED') ? 'selected' : '' ?>>Selesai</option>
        </select>
    </div>
    <?php 
    $extraFilters = ob_get_clean();
    require __DIR__ . '/../../components/table/toolbar.php'; 
    ?>

    <!-- Data Table -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-12">No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Teknisi</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Gudang Tujuan</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <?php 
                    $no = ($offset ?? 0) + 1;
                    foreach ($returns as $r):
                    ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="px-4 py-3 text-center text-sm text-gray-500 dark:text-gray-400"><?= $no++ ?></td>
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 whitespace-nowrap"><?= date('d M Y H:i', strtotime($r['created_at'])) ?></td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100"><?= htmlspecialchars($r['technician_name']) ?></td>
                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars($r['warehouse_name']) ?></td>
                        <td class="px-4 py-3 text-sm whitespace-nowrap">
                            <?= \App\Helpers\UIHelper::statusBadge($r['status']) ?>
                        </td>
                        <td class="px-4 py-3 text-center text-sm">
                            <a href="<?= url('/returns/show?id=' . (int)$r['id']) ?>" class="inline-flex items-center text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 font-semibold">
                                <i class="fas fa-eye mr-1 text-xs"></i>Lihat
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($returns)): ?>
                        <?php 
                        $colspan = 6;
                        $emptyMessage = "Belum ada pengembalian.";
                        require __DIR__ . '/../../components/table/empty_state.php';
                        ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php 
        $paginationBaseUrl = url('/returns');
        require __DIR__ . '/../../components/table/pagination.php'; 
        ?>
    </div>
</div>

<?php 
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php'; 
?>