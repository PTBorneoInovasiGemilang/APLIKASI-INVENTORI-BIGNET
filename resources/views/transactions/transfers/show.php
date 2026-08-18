<?php
$pageTitle = 'Detail Transfer';
ob_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$dispatchPhotos = array_values(array_filter($attachments ?? [], fn($a) => $a['reference_type'] === 'transfer_dispatch'));
$receivePhotos = array_values(array_filter($attachments ?? [], fn($a) => $a['reference_type'] === 'transfer_receive'));
?>

<div class="max-w-5xl mx-auto">
    <div class="mb-6 flex items-center">
        <a href="<?= url('/transfers') ?>" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 mr-4"><i class="fas fa-arrow-left"></i></a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Detail Transfer</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars($transfer['transaction_code']) ?></p>
        </div>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"><span class="block sm:inline"><?= htmlspecialchars($_SESSION['error']) ?></span><?php unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
        <div class="flex flex-wrap justify-between items-start gap-4 mb-4">
            <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Gudang Asal</p><p class="text-sm font-semibold text-gray-900 dark:text-gray-100 mt-0.5"><?= htmlspecialchars($transfer['source_warehouse']) ?></p></div>
                <div><p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Gudang Tujuan</p><p class="text-sm font-semibold text-gray-900 dark:text-gray-100 mt-0.5"><?= htmlspecialchars($transfer['dest_warehouse']) ?></p></div>
                <div><p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Dikirim Oleh</p><p class="text-sm text-gray-700 dark:text-gray-300 mt-0.5"><?= htmlspecialchars($transfer['sender_name']) ?></p></div>
                <div><p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Tanggal Kirim</p><p class="text-sm text-gray-700 dark:text-gray-300 mt-0.5"><?= date('d M Y, H:i', strtotime($transfer['sent_at'])) ?></p></div>
                <?php if ($transfer['receiver_name']): ?><div><p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Diterima Oleh</p><p class="text-sm text-gray-700 dark:text-gray-300 mt-0.5"><?= htmlspecialchars($transfer['receiver_name']) ?></p></div><?php endif; ?>
                <?php if ($transfer['received_at']): ?><div><p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Tanggal Terima</p><p class="text-sm text-gray-700 dark:text-gray-300 mt-0.5"><?= date('d M Y, H:i', strtotime($transfer['received_at'])) ?></p></div><?php endif; ?>
            </div>
            <div><?php $badgeType = $transfer['status'] === 'IN_TRANSIT' ? 'INFO' : ($transfer['status'] === 'COMPLETED' ? 'SUCCESS' : 'NEUTRAL'); ?><?= \App\Helpers\UIHelper::badge($transfer['status'], $badgeType) ?></div>
        </div>
        <?php if ($transfer['notes']): ?>
            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700"><p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1">Catatan</p><p class="text-sm text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-700 p-3 rounded"><?= nl2br(htmlspecialchars($transfer['notes'])) ?></p></div>
        <?php endif; ?>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
        <div class="flex items-center justify-between mb-4"><h3 class="text-lg font-semibold text-gray-800 dark:text-white">Bukti Foto</h3><span class="text-xs text-gray-500 dark:text-gray-400">Foto disimpan sebagai lampiran privat.</span></div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">Bukti Pengiriman</p>
                <?php if ($dispatchPhotos): ?>
                    <div class="grid grid-cols-2 gap-3">
                        <?php foreach ($dispatchPhotos as $photo): ?>
                            <a href="<?= url('/file?path=' . rawurlencode($photo['file_path'])) ?>" target="_blank" rel="noopener" class="block rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                                <img src="<?= url('/file?path=' . rawurlencode($photo['file_path'])) ?>" alt="Bukti pengiriman" class="w-full h-40 object-cover">
                                <div class="p-2 text-xs text-gray-600 dark:text-gray-300 truncate"><?= htmlspecialchars($photo['original_name']) ?></div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?><p class="text-sm text-gray-500 dark:text-gray-400">Belum ada foto bukti pengiriman.</p><?php endif; ?>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">Bukti Penerimaan</p>
                <?php if ($receivePhotos): ?>
                    <div class="grid grid-cols-2 gap-3">
                        <?php foreach ($receivePhotos as $photo): ?>
                            <a href="<?= url('/file?path=' . rawurlencode($photo['file_path'])) ?>" target="_blank" rel="noopener" class="block rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                                <img src="<?= url('/file?path=' . rawurlencode($photo['file_path'])) ?>" alt="Bukti penerimaan" class="w-full h-40 object-cover">
                                <div class="p-2 text-xs text-gray-600 dark:text-gray-300 truncate"><?= htmlspecialchars($photo['original_name']) ?></div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?><p class="text-sm text-gray-500 dark:text-gray-400">Belum ada foto bukti penerimaan.</p><?php endif; ?>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700"><h3 class="text-lg font-semibold text-gray-800 dark:text-white">Barang yang Ditransfer</h3></div>
        <form action="<?= url('/transfers/receive') ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="id" value="<?= $transfer['id'] ?>">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50"><tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Barang</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Qty Dikirim</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Qty Diterima</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Status</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <?php foreach ($items as $item): $remaining = $item['quantity_sent'] - $item['quantity_received']; ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3"><div class="text-sm font-medium text-gray-900 dark:text-gray-100"><?= htmlspecialchars($item['nama']) ?></div><div class="text-xs text-gray-500 dark:text-gray-400"><?= htmlspecialchars($item['kode_barang']) ?></div><?php if ($item['is_serialized']): ?><div class="mt-1 inline-flex px-2 py-0.5 rounded text-xs font-semibold bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">Serialized</div><?php endif; ?></td>
                            <td class="px-4 py-3 text-sm text-center font-bold text-gray-900 dark:text-gray-100"><?= $item['quantity_sent'] ?></td>
                            <td class="px-4 py-3 text-sm text-center text-gray-700 dark:text-gray-300 align-top">
                                <div class="mb-1 font-medium">Sdh Diterima: <?= $item['quantity_received'] ?></div>
                                <?php if ($transfer['status'] === 'IN_TRANSIT' && $remaining > 0): ?>
                                    <div class="mt-2 text-left"><label class="block text-xs font-semibold mb-1">Terima Qty Baru (Maks <?= $remaining ?>)</label><input type="number" name="receive_qtys[<?= $item['id'] ?>]" min="0" max="<?= $remaining ?>" value="<?= $remaining ?>" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm qty-input" data-item-id="<?= $item['id'] ?>" onkeyup="validateReceiveSn(<?= $item['id'] ?>)" onchange="validateReceiveSn(<?= $item['id'] ?>)"></div>
                                    <?php if ($item['is_serialized']): ?><div class="mt-2 text-left"><label class="block text-xs font-semibold mb-1">Serial Number Diterima</label><textarea name="receive_sns[<?= $item['id'] ?>]" id="receive-sn-<?= $item['id'] ?>" rows="2" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" placeholder="SN..." onkeyup="validateReceiveSn(<?= $item['id'] ?>)" onchange="validateReceiveSn(<?= $item['id'] ?>)" required></textarea><div id="sn-counter-<?= $item['id'] ?>" class="text-xs font-bold text-red-500 mt-1">SN Diisi: 0 / <?= $remaining ?></div></div><?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-sm text-center align-top"><?php if ($remaining <= 0): ?><span class="text-green-600 dark:text-green-400 font-semibold mt-2 block"><i class="fas fa-check-circle mr-1"></i>Lengkap</span><?php else: ?><span class="text-orange-600 dark:text-orange-400 font-medium mt-2 block">Sisa: <?= $remaining ?></span><?php endif; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($items)): ?><?php $colspan = 4; $emptyMessage = "Belum ada barang dalam transfer ini."; require __DIR__ . '/../../components/table/empty_state.php'; ?><?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($transfer['status'] === 'IN_TRANSIT' && !\App\Helpers\AuthHelper::hasRole('SUPERADMIN')): ?>
            <div class="p-6 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Catatan Penerimaan <span class="font-normal text-gray-400">(Opsional)</span></label><textarea name="notes_receive" rows="2" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm"></textarea></div>
                    <div><label for="receive_photo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Foto Bukti Penerimaan <span class="font-normal text-gray-400">(Opsional)</span></label><input id="receive_photo" name="receive_photo" type="file" accept="image/jpeg,image/png,image/webp" class="block w-full text-sm text-gray-700 dark:text-gray-200 file:mr-3 file:rounded-md file:border-0 file:bg-green-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-green-700"><p class="mt-1 text-xs text-gray-500 dark:text-gray-400">JPG, PNG, atau WEBP. Maksimal 5 MB.</p></div>
                </div>
                <div class="flex justify-end"><button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2.5 px-6 rounded-lg shadow-sm text-sm" data-confirm="Apakah Anda yakin akan menyimpan penerimaan transfer ini?" data-confirm-title="Simpan Penerimaan" data-confirm-type="warning" data-confirm-icon="fa-check" data-confirm-confirm="Simpan" data-confirm-loading="Menyimpan..."><i class="fas fa-check mr-2"></i>Simpan Penerimaan</button></div>
            </div>
            <script>
                function validateReceiveSn(itemId) {
                    const qtyInput = document.querySelector(`input[name="receive_qtys[${itemId}]"]`);
                    const snInput = document.getElementById(`receive-sn-${itemId}`);
                    const counter = document.getElementById(`sn-counter-${itemId}`);
                    if (!qtyInput || !snInput || !counter) return;
                    const expectedQty = parseInt(qtyInput.value) || 0;
                    if (expectedQty === 0) snInput.removeAttribute('required'); else snInput.setAttribute('required', 'required');
                    const actualQty = snInput.value.split(/[\n,]+/).map(s => s.trim()).filter(Boolean).length;
                    counter.innerText = `SN Diisi: ${actualQty} / ${expectedQty}`;
                    counter.className = actualQty === expectedQty ? 'text-xs font-bold text-green-500 mt-1' : (actualQty > expectedQty ? 'text-xs font-bold text-red-500 mt-1' : 'text-xs font-bold text-orange-500 mt-1');
                    if (actualQty > expectedQty) counter.innerText += ' (Kelebihan!)'; else if (actualQty < expectedQty) counter.innerText += ' (Kurang!)';
                }
                document.querySelectorAll('.qty-input').forEach(input => validateReceiveSn(input.dataset.itemId));
            </script>
            <?php endif; ?>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
