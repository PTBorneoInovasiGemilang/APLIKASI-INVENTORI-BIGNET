<?php
$pageTitle = 'Peminjaman';
ob_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Peminjaman Barang</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Kelola peminjaman barang dan nomor seri oleh teknisi.</p>
        </div>
        
        <?php if (\App\Helpers\AuthHelper::hasRole(['TEKNISI_NOC'])): ?>
        <a href="<?= url('/loans/create') ?>" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow whitespace-nowrap inline-flex items-center">
            <i class="fas fa-plus mr-2"></i> Peminjaman Baru
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
    $toolbarAction = url('/loans');
    $searchPlaceholder = "Cari Kode / Ref...";
    ob_start();
    ?>
    <div>
        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Status</label>
        <select name="status" class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-40 py-2 px-3 text-sm text-gray-700 dark:text-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Semua Status</option>
            <option value="BORROWED" <?php echo (isset($_GET['status']) && $_GET['status'] === 'BORROWED') ? 'selected' : ''; ?>>Dipinjam</option>
            <option value="PARTIAL_RETURNED" <?php echo (isset($_GET['status']) && $_GET['status'] === 'PARTIAL_RETURNED') ? 'selected' : ''; ?>>Sebagian</option>
            <option value="RETURNED" <?php echo (isset($_GET['status']) && $_GET['status'] === 'RETURNED') ? 'selected' : ''; ?>>Dikembalikan</option>
        </select>
    </div>
    <?php
    $extraFilters = ob_get_clean();
    require __DIR__ . '/../../components/table/toolbar.php';
    ?>

    <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1100px] table-sticky mobile-card-table divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-12">No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kode</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tipe</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Peminjam</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Gudang</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jatuh Tempo</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <?php 
                    $no = $offset + 1;
                    foreach ($loans as $ln): 
                    ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td data-label="No" class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400"><?= $no++ ?></td>
                        <td data-label="Tanggal" class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><?php echo date('d M Y', strtotime($ln['loan_date'])); ?></td>
                        <td data-label="Kode" class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                            <?php echo htmlspecialchars($ln['transaction_code']); ?>
                            <?php if ($ln['submission_code']): ?>
                                <div class="text-xs font-normal text-blue-500 mt-1"><?= htmlspecialchars($ln['submission_code']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td data-label="Tipe" class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            <span class="px-2 py-1 rounded-full text-xs font-bold bg-gray-200 text-gray-800 dark:bg-gray-600 dark:text-gray-200">
                                <?php echo htmlspecialchars($ln['loan_type'] ?? 'INTERNAL'); ?>
                            </span>
                        </td>
                        <td data-label="Peminjam" class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($ln['borrower_name']); ?></td>
                        <td data-label="Gudang" class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($ln['warehouse_name']); ?></td>
                        <td data-label="Jatuh Tempo" class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            <?php 
                            if ($ln['due_date']) {
                                $due = strtotime($ln['due_date']);
                                $isOverdue = time() > $due && $ln['status'] !== 'RETURNED';
                                echo '<span class="'.($isOverdue ? 'text-red-600 font-bold' : '').'">'.date('d M Y', $due).'</span>';
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td data-label="Status" class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            <?php if ($ln['status'] === 'BORROWED'): ?>
                                <?= \App\Helpers\UIHelper::badge('Dipinjam', 'WARNING') ?>
                            <?php elseif ($ln['status'] === 'PARTIAL_RETURNED'): ?>
                                <?= \App\Helpers\UIHelper::badge('Kembali Sebagian', 'INFO') ?>
                            <?php elseif ($ln['status'] === 'RETURNED'): ?>
                                <?= \App\Helpers\UIHelper::badge('Dikembalikan', 'SUCCESS') ?>
                            <?php else: ?>
                                <?= \App\Helpers\UIHelper::badge($ln['status'], 'NEUTRAL') ?>
                            <?php endif; ?>
                        </td>
                        <?php
                        $rowActions = [['type' => 'view', 'url' => url('/loans/show?id=' . (int)$ln['id']), 'label' => 'Lihat', 'icon' => 'fa-eye', 'title' => 'Detail Peminjaman']];
                        require __DIR__ . '/../../components/table/row_actions.php';
                        ?>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($loans)): ?>
                        <?php
                        $colspan = 10;
                        $emptyMessage = "Belum ada transaksi peminjaman.";
                        require __DIR__ . '/../../components/table/empty_state.php';
                        ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php 
        $paginationBaseUrl = url('/loans');
        require __DIR__ . '/../../components/table/pagination.php';
        ?>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
