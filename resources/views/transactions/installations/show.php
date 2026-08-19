<?php
$pageTitle = 'Detail Instalasi';
ob_start();
?>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 flex items-center gap-3">
        <a href="<?= url('/installations') ?>" class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-600 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200" aria-label="Kembali">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Detail Instalasi</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Informasi pemasangan barang di lapangan</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-6 mb-6 border border-gray-200 dark:border-gray-700">
        <div class="flex flex-wrap justify-between items-start gap-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 flex-1">
                <div>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Pelanggan</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 mt-1"><?= htmlspecialchars($installation['customer_name'] ?: $installation['customer_site']) ?></p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Lokasi / Site</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 mt-1"><?= htmlspecialchars($installation['customer_site']) ?></p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Teknisi</p>
                    <p class="text-sm text-gray-700 dark:text-gray-300 mt-1"><?= htmlspecialchars($installation['technician_name']) ?></p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Tanggal Instalasi</p>
                    <p class="text-sm text-gray-700 dark:text-gray-300 mt-1"><?= date('d M Y, H:i', strtotime($installation['installed_at'])) ?></p>
                </div>
            </div>
            <div class="shrink-0">
                <?= \App\Helpers\UIHelper::badge($installation['status'], 'INFO') ?>
            </div>
        </div>

        <?php if (!empty($installation['reference_code'])): ?>
            <div class="mt-5 pt-4 border-t border-gray-200 dark:border-gray-700">
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Referensi Pengeluaran</p>
                <p class="text-sm text-gray-700 dark:text-gray-300 mt-1">Terkait transaksi pengeluaran sebelumnya.</p>
            </div>
        <?php endif; ?>

        <?php if (!empty($installation['notes'])): ?>
            <div class="mt-5 pt-4 border-t border-gray-200 dark:border-gray-700">
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Catatan</p>
                <div class="text-sm text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/70 p-4 rounded-lg">
                    <?= nl2br(htmlspecialchars($installation['notes'])) ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Barang yang Dipasang</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Barang dan nomor seri yang tercatat pada instalasi ini</p>
                        </div>
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                            <?= count($items) ?> item
                        </span>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Barang</th>
                                <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-24">Qty</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nomor Seri</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <?php foreach ($items as $item): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-5 py-4 align-top">
                                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($item['nama']) ?></div>
                                </td>
                                <td class="px-5 py-4 align-top text-sm text-center font-bold text-gray-900 dark:text-gray-100"><?= (int)$item['quantity'] ?></td>
                                <td class="px-5 py-4 align-top text-sm text-gray-700 dark:text-gray-300">
                                    <?php if ($item['is_serialized']): ?>
                                        <div class="flex flex-wrap gap-2">
                                            <?php foreach (array_filter(array_map('trim', explode(',', $item['sn_list_display'] ?? ''))) as $serial): ?>
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-blue-50 text-blue-700 border border-blue-100 dark:bg-blue-900/30 dark:text-blue-200 dark:border-blue-800 text-xs font-mono font-semibold">
                                                    <?= htmlspecialchars($serial) ?>
                                                </span>
                                            <?php endforeach; ?>
                                            <?php if (empty($item['sn_list_display'])): ?>
                                                <span class="text-gray-400 dark:text-gray-500 italic text-xs">Nomor seri tidak tercatat</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-gray-400 dark:text-gray-500 italic text-xs">Barang non-serial</span>
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
            <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Foto Bukti Instalasi</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Evidence pemasangan di lapangan</p>
                </div>
                <div class="p-5">
                    <?php if (!empty($installation['photo_path'])): ?>
                        <?php $photoUrl = url('/file?path=' . rawurlencode($installation['photo_path'])); ?>
                        <a href="<?= htmlspecialchars($photoUrl) ?>" target="_blank" rel="noopener" class="block rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40 hover:opacity-95 transition-opacity">
                            <img src="<?= htmlspecialchars($photoUrl) ?>" alt="Foto Bukti Instalasi" class="w-full h-72 object-cover" loading="lazy">
                        </a>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 text-center">Klik foto untuk membuka ukuran penuh</p>
                    <?php else: ?>
                        <div class="text-center py-12 rounded-lg border border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900/30">
                            <i class="fas fa-image text-4xl text-gray-300 dark:text-gray-600 mb-3"></i>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Belum ada foto instalasi</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Foto bukti akan tampil di sini setelah diunggah.</p>
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