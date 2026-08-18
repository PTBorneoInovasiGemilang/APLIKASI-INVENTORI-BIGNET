<?php
$pageTitle = 'Transfer Gudang';
ob_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Transfer Gudang</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Kelola transfer stok internal antar gudang.</p>
        </div>
        
        <?php if (!\App\Helpers\AuthHelper::hasRole('SUPERADMIN')): ?>
        <a href="<?= url('/transfers/create') ?>" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow whitespace-nowrap inline-flex items-center">
            <i class="fas fa-plus mr-2"></i> Transfer Baru
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
    $toolbarAction = url('/transfers');
    $searchPlaceholder = "Cari Kode Transaksi...";
    ob_start();
    ?>
    <div>
        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Status</label>
        <select name="status" class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-40 py-2 px-3 text-sm text-gray-700 dark:text-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Semua Status</option>
            <option value="DRAFT" <?php echo (isset($_GET['status']) && $_GET['status'] === 'DRAFT') ? 'selected' : ''; ?>><?= htmlspecialchars(\App\Helpers\UIHelper::statusLabel('DRAFT')) ?></option>
            <option value="IN_TRANSIT" <?php echo (isset($_GET['status']) && $_GET['status'] === 'IN_TRANSIT') ? 'selected' : ''; ?>><?= htmlspecialchars(\App\Helpers\UIHelper::statusLabel('IN_TRANSIT')) ?></option>
            <option value="PARTIAL_RECEIVED" <?php echo (isset($_GET['status']) && $_GET['status'] === 'PARTIAL_RECEIVED') ? 'selected' : ''; ?>><?= htmlspecialchars(\App\Helpers\UIHelper::statusLabel('PARTIAL_RECEIVED')) ?></option>
            <option value="COMPLETED" <?php echo (isset($_GET['status']) && $_GET['status'] === 'COMPLETED') ? 'selected' : ''; ?>><?= htmlspecialchars(\App\Helpers\UIHelper::statusLabel('COMPLETED')) ?></option>
        </select>
    </div>
    <?php
    $extraFilters = ob_get_clean();
    require __DIR__ . '/../../components/table/toolbar.php';
    ?>

    <div class="overflow-x-auto bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1000px] table-sticky divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-12">No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kode</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Dari Gudang</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ke Gudang</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pengirim</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <?php 
                    $no = $offset + 1;
                    foreach ($transfers as $trf): 
                    ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400"><?= $no++ ?></td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><?php echo date('d M Y H:i', strtotime($trf['sent_at'])); ?></td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100"><?php echo htmlspecialchars($trf['transaction_code']); ?></td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($trf['source_warehouse']); ?></td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($trf['dest_warehouse']); ?></td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($trf['sender_name']); ?></td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                            <?php
                            $badgeType = 'NEUTRAL';
                            if ($trf['status'] === 'IN_TRANSIT') $badgeType = 'INFO';
                            if ($trf['status'] === 'COMPLETED') $badgeType = 'SUCCESS';
                            ?>
                            <?= \App\Helpers\UIHelper::badge($trf['status'], $badgeType) ?>
                        </td>
                        <?php
                        $rowActions = [];
                        $rowActions[] = ['type' => 'view', 'url' => url('/transfers/show?id=' . (int)$trf['id']), 'label' => 'Lihat', 'icon' => 'fa-eye', 'title' => 'Detail Transfer'];
                        if ($trf['status'] === 'IN_TRANSIT' && \App\Helpers\AuthHelper::hasRole('ADMIN')) {
                            // Terima hanya untuk ADMIN (operasional); SUPERADMIN read-only.
                            $rowActions[] = [
                                'type' => 'form', 'action' => url('/transfers/receive'),
                                'fields' => ['csrf_token' => $_SESSION['csrf_token'] ?? '', 'transfer_id' => (int)$trf['id']],
                                'label' => 'Terima', 'icon' => 'fa-check-circle',
                                'confirm' => [
                                    'title' => 'Terima Transfer',
                                    'message' => 'Apakah Anda yakin telah menerima semua barang untuk transfer ini?',
                                    'type' => 'warning', 'icon' => 'fa-check-circle', 'confirm' => 'Terima', 'loading' => 'Memproses...'
                                ]
                            ];
                        }
                        require __DIR__ . '/../../components/table/row_actions.php';
                        ?>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($transfers)): ?>
                        <?php
                        $colspan = 8;
                        $emptyMessage = "Belum ada transaksi transfer.";
                        require __DIR__ . '/../../components/table/empty_state.php';
                        ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php 
        $paginationBaseUrl = url('/transfers');
        require __DIR__ . '/../../components/table/pagination.php';
        ?>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
