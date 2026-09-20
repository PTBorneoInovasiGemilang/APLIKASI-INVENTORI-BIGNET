<?php
$pageTitle = 'Instalasi';
ob_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Instalasi</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Kelola instalasi lapangan dan barang yang digunakan.</p>
        </div>
        
        <?php if (\App\Helpers\AuthHelper::hasRole(['TEKNISI_NOC'])): ?>
        <a href="<?= url('/installations/create') ?>" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow text-sm transition-colors">
            <i class="fas fa-plus mr-2"></i> Buat Instalasi
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
    $toolbarAction = url('/installations');
    $searchPlaceholder = "Cari Kode / Pelanggan...";
    require __DIR__ . '/../../components/table/toolbar.php';
    ?>

    <div class="overflow-x-auto bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[800px] lg:min-w-full table-sticky mobile-card-table divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-12">No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal Instalasi</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kode</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pelanggan</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Lokasi</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Teknisi</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <?php 
                    $no = $offset + 1;
                    foreach ($installations as $inst): 
                    ?>
                    <?php
                        $siteParts = explode(' - ', $inst['customer_site']);
                        $custName = $siteParts[0] ?? $inst['customer_site'];
                        $locName = $siteParts[1] ?? '-';
                        
                        // Indonesian Date formatting helper function or inline
                        $timestamp = strtotime($inst['installed_at']);
                        $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                        $formattedDate = date('d ', $timestamp) . $months[date('n', $timestamp) - 1] . date(' Y, H:i', $timestamp);
                    ?>
                    <tr class="hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-700/50 transition-colors">
                        <td data-label="No" class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400"><?= $no++ ?></td>
                        <td data-label="Tanggal" class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><?= $formattedDate ?></td>
                        <td data-label="Kode" class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100"><?php echo htmlspecialchars($inst['transaction_code']); ?></td>
                        <td data-label="Pelanggan" class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars($custName) ?></td>
                        <td data-label="Lokasi" class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars($locName) ?></td>
                        <td data-label="Teknisi" class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($inst['technician_name']); ?></td>
                        <td data-label="Status" class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            <?= \App\Helpers\UIHelper::badge($inst['status'], 'INFO') ?>
                        </td>
                        <?php
                        $rowActions = [['type' => 'view', 'url' => url('/installations/show?id=' . (int)$inst['id']), 'label' => 'Lihat', 'icon' => 'fa-eye', 'title' => 'Detail Instalasi']];
                        require __DIR__ . '/../../components/table/row_actions.php';
                        ?>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($installations)): ?>
                        <?php
                        $colspan = 8;
                        $emptyMessage = "Belum ada transaksi instalasi.";
                        require __DIR__ . '/../../components/table/empty_state.php';
                        ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php 
        $paginationBaseUrl = url('/installations');
        require __DIR__ . '/../../components/table/pagination.php';
        ?>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
