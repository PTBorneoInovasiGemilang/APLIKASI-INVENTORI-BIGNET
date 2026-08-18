<?php
$pageTitle = 'Detail Instalasi';
ob_start();
?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 flex items-center">
        <a href="<?= url('/installations') ?>" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 mr-4">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Detail Instalasi</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars($installation['transaction_code']) ?></p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
        <div class="flex flex-wrap justify-between items-start gap-4 mb-4">
            <div class="flex-1 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Pelanggan</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 mt-0.5"><?= htmlspecialchars($installation['customer_name'] ?: $installation['customer_site']) ?></p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Lokasi / Site</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 mt-0.5"><?= htmlspecialchars($installation['customer_site']) ?></p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Teknisi</p>
                    <p class="text-sm text-gray-700 dark:text-gray-300 mt-0.5"><?= htmlspecialchars($installation['technician_name']) ?></p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Tanggal Instalasi</p>
                    <p class="text-sm text-gray-700 dark:text-gray-300 mt-0.5"><?= date('d M Y, H:i', strtotime($installation['installed_at'])) ?></p>
                </div>
                <?php if ($installation['reference_code']): ?>
                <div>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Referensi Transaksi</p>
                    <p class="text-sm text-blue-600 dark:text-blue-400 mt-0.5 font-medium"><?= htmlspecialchars($installation['reference_code']) ?></p>
                </div>
                <?php endif; ?>
            </div>
            <div>
                <?= \App\Helpers\UIHelper::badge($installation['status'], 'INFO') ?>
            </div>
        </div>
        
        <?php if ($installation['notes']): ?>
            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1">Catatan</p>
                <p class="text-sm text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-700 p-3 rounded"><?= nl2br(htmlspecialchars($installation['notes'])) ?></p>
            </div>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="overflow-x-auto bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Barang yang Dipasang</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Barang</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Qty</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Serial Number</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <?php foreach ($items as $item): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100"><?= htmlspecialchars($item['nama']) ?></div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400"><?= htmlspecialchars($item['kode_barang']) ?></div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-center font-bold text-gray-900 dark:text-gray-100"><?= $item['quantity'] ?></td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                    <?php if ($item['is_serialized']): ?>
                                        <div class="max-w-xs break-words font-mono text-xs">
                                            <?= htmlspecialchars($item['sn_list_display'] ?? '-') ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-gray-400 dark:text-gray-500 italic text-xs">Bukan barang serial</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($items)): ?>
                                <?php $colspan = 3; $emptyMessage = "Belum ada barang dalam instalasi ini."; require __DIR__ . '/../../components/table/empty_state.php'; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div>
            <div class="overflow-x-auto bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Foto Bukti Instalasi</h3>
                </div>
                <div class="p-6">
                    <?php if ($installation['photo_path']): ?>
                        <a href="<?= url('/' . $installation['photo_path']) ?>" target="_blank" class="block rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 hover:opacity-90 transition-opacity">
                            <img src="<?= url('/' . $installation['photo_path']) ?>" alt="Foto Instalasi" class="w-full h-auto object-cover max-h-64">
                        </a>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 text-center">Klik gambar untuk memperbesar</p>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <i class="fas fa-image text-4xl text-gray-300 dark:text-gray-600 mb-3"></i>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Tidak ada foto instalasi</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
