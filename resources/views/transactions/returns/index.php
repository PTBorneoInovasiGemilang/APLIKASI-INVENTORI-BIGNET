<?php
$pageTitle = 'Pengembalian Barang';
ob_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Pengembalian Barang</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Kelola pengembalian barang dari peminjaman.</p>
        </div>
        
        <?php if (\App\Helpers\AuthHelper::hasRole(['TEKNISI_NOC'])): ?>
        <a href="<?= url('/returns/create') ?>" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow whitespace-nowrap inline-flex items-center">
            <i class="fas fa-plus mr-2"></i> Ajukan Pengembalian
        </a>
        <?php endif; ?>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
            <span class="block sm:inline"><?= htmlspecialchars($_SESSION['success']) ?></span>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            <span class="block sm:inline"><?= htmlspecialchars($_SESSION['error']) ?></span>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <?php
    $toolbarAction = url('/returns');
    $searchPlaceholder = "Cari Kode / Ref...";
    require __DIR__ . '/../../components/table/toolbar.php';
    ?>

    <div class="overflow-x-auto bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1000px] table-sticky mobile-card-table divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-12">No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kode Transaksi</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Teknisi (Pengembali)</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Gudang</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ref Peminjaman</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <?php 
                    $no = $offset + 1;
                    foreach ($returns as $ret): 
                    ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td data-label="No" class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400"><?= $no++ ?></td>
                        <td data-label="Tanggal" class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><?php echo date('d M Y', strtotime($ret['created_at'])); ?></td>
                        <td data-label="Kode" class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100"><?php echo htmlspecialchars($ret['transaction_code']); ?></td>
                        <td data-label="Pengembali" class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($ret['technician_name'] ?? '-'); ?></td>
                        <td data-label="Gudang" class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($ret['warehouse_name']); ?></td>
                        <td data-label="Ref Pinjam" class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($ret['loan_code'] ?? '-'); ?></td>
                        <td data-label="Status" class="px-4 py-3 whitespace-nowrap text-sm">
                            <?php if ($ret['status'] === 'PENDING'): ?>
                                <?= \App\Helpers\UIHelper::badge('Menunggu', 'WARNING') ?>
                            <?php elseif ($ret['status'] === 'COMPLETED'): ?>
                                <?= \App\Helpers\UIHelper::badge('Selesai', 'SUCCESS') ?>
                            <?php else: ?>
                                <?= \App\Helpers\UIHelper::badge($ret['status'], 'NEUTRAL') ?>
                            <?php endif; ?>
                        </td>
                        <?php
                        $rowActions = [['type' => 'view', 'url' => url('/returns/show?id=' . (int)$ret['id']), 'label' => 'Lihat', 'icon' => 'fa-eye', 'title' => 'Detail Pengembalian']];
                        require __DIR__ . '/../../components/table/row_actions.php';
                        ?>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($returns)): ?>
                        <?php
                        $colspan = 8;
                        $emptyMessage = "Belum ada transaksi pengembalian.";
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
