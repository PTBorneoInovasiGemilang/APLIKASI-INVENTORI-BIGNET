<?php
$pageTitle = 'Detail Peminjaman';
ob_start();
?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 flex items-center">
        <a href="<?= url('/loans') ?>" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 mr-4">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Detail Peminjaman</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                <?= htmlspecialchars($loan['transaction_code']) ?>
                <?php if ($loan['submission_code']): ?>
                    (Ref: <?= htmlspecialchars($loan['submission_code']) ?>)
                <?php endif; ?>
            </p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 border-b dark:border-gray-700 pb-2">Informasi Peminjaman</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Peminjam</p>
                <p class="font-medium text-gray-900 dark:text-gray-100"><?= htmlspecialchars($loan['borrower_name']) ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Gudang</p>
                <p class="font-medium text-gray-900 dark:text-gray-100"><?= htmlspecialchars($loan['warehouse_name']) ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Tipe Peminjaman</p>
                <p class="font-medium text-gray-900 dark:text-gray-100"><?= htmlspecialchars($loan['loan_type'] ?? 'INTERNAL') ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Tanggal Pinjam</p>
                <p class="font-medium text-gray-900 dark:text-gray-100"><?= date('d M Y H:i', strtotime($loan['loan_date'])) ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Tanggal Jatuh Tempo</p>
                <p class="font-medium text-gray-900 dark:text-gray-100">
                    <?php 
                    if ($loan['due_date']) {
                        echo date('d M Y', strtotime($loan['due_date']));
                    } else {
                        echo '-';
                    }
                    ?>
                </p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                <p class="font-medium text-gray-900 dark:text-gray-100"><?= htmlspecialchars(\App\Helpers\UIHelper::statusLabel($loan['status'])) ?></p>
            </div>
            <div class="md:col-span-2">
                <p class="text-sm text-gray-500 dark:text-gray-400">Catatan</p>
                <p class="font-medium text-gray-900 dark:text-gray-100"><?= nl2br(htmlspecialchars($loan['notes'] ?: '-')) ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 border-b dark:border-gray-700 pb-2">Daftar Barang</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Barang</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kuantitas</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Dikembalikan</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Serial Numbers</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                            <?= htmlspecialchars($item['nama']) ?>
                            <div class="text-xs text-gray-500 dark:text-gray-400 font-normal"><?= htmlspecialchars($item['kode_barang']) ?></div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-center text-sm font-bold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($item['quantity']) ?></td>
                        <td class="px-4 py-3 whitespace-nowrap text-center text-sm text-green-600 dark:text-green-400 font-bold"><?= htmlspecialchars($item['returned_quantity']) ?></td>
                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                            <?php if ($item['is_serialized'] && !empty($item['serial_numbers'])): ?>
                                <ul class="list-disc list-inside">
                                    <?php foreach ($item['serial_numbers'] as $sn): ?>
                                        <li><?= htmlspecialchars($sn) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php elseif ($item['is_serialized']): ?>
                                <span class="italic">Belum ada Serial Number</span>
                            <?php else: ?>
                                <span class="italic text-gray-400 dark:text-gray-500">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($items)): ?>
                        <?php $colspan = 4; $emptyMessage = "Belum ada barang dalam peminjaman ini."; require __DIR__ . '/../../components/table/empty_state.php'; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../components/audit_timeline.php'; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
