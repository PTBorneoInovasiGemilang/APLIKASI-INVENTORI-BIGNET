<?php
$title = 'Detail Stock Opname';
$pageTitle = 'Detail Stock Opname';
ob_start();
?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100"><?= $title ?></h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Review dan persetujuan selisih stok fisik vs sistem.</p>
    </div>
    <a href="<?= url('/opnames') ?>" class="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
        &larr; Kembali
    </a>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="mb-4 p-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
        <?= $_SESSION['success']; unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
    <div class="mb-4 p-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Kolom Kiri: Detail & Item -->
    <div class="lg:col-span-2 space-y-6">
        
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex justify-between items-start mb-4 pb-4 border-b dark:border-gray-700">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white"><?= htmlspecialchars($opname['transaction_code']) ?></h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Gudang: <strong><?= htmlspecialchars($opname['warehouse_name']) ?></strong></p>
                </div>
                <div>
                    <?php 
                        $statusClass = [
                            'DRAFT' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
                            'REVIEW' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
                            'APPROVED' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                            'REJECTED' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
                        ];
                        $sc = $statusClass[$opname['status']] ?? 'bg-gray-100 text-gray-800';
                    ?>
                    <span class="px-3 py-1 rounded-full text-sm font-medium <?= $sc ?>">
                        <?= htmlspecialchars(\App\Helpers\UIHelper::statusLabel($opname['status'])) ?>
                    </span>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <span class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Pelaksana Opname</span>
                    <span class="text-sm font-semibold text-gray-900 dark:text-white"><?= htmlspecialchars($opname['conducted_by_name']) ?></span>
                </div>
                <div>
                    <span class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Tanggal Pelaksanaan</span>
                    <span class="text-sm font-semibold text-gray-900 dark:text-white"><?= date('d M Y H:i', strtotime($opname['opname_date'])) ?></span>
                </div>
                <?php if ($opname['adjustment_transaction_code']): ?>
                <div class="col-span-2">
                    <span class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Adjustment Reference</span>
                    <a href="<?= url('/adjustments/show?code=' . urlencode($opname['adjustment_transaction_code'])) ?>" class="text-sm font-semibold text-blue-600 hover:underline"><?= $opname['adjustment_transaction_code'] ?></a>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if ($opname['notes']): ?>
            <div>
                <span class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-1">Catatan / Resolusi</span>
                <p class="text-sm text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700 p-3 rounded border border-gray-200 dark:border-gray-600"><?= nl2br(htmlspecialchars($opname['notes'])) ?></p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Daftar Barang Selisih -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-4 py-3 border-b dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Daftar Selisih Stok (Variance)</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                        <tr>
                            <th class="px-4 py-3">Kode Barang</th>
                            <th class="px-4 py-3 text-center">Sistem</th>
                            <th class="px-4 py-3 text-center">Fisik</th>
                            <th class="px-4 py-3 text-center">Selisih</th>
                            <th class="px-4 py-3">Alasan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                <?= htmlspecialchars($item['kode_barang']) ?> - <?= htmlspecialchars($item['item_name']) ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?= $item['system_quantity'] ?>
                            </td>
                            <td class="px-4 py-3 text-center font-bold">
                                <?= $item['physical_quantity'] ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?php if ($item['variance'] > 0): ?>
                                    <span class="text-green-600 font-bold">+<?= $item['variance'] ?></span>
                                <?php else: ?>
                                    <span class="text-red-600 font-bold"><?= $item['variance'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-xs"><?= htmlspecialchars($item['reason']) ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($items)): ?>
                            <?php $colspan = 5; $emptyMessage = "Belum ada item dalam opname ini."; require __DIR__ . '/../../components/table/empty_state.php'; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Aksi & Timeline -->
    <div class="space-y-6">
        
        <?php if ($opname['status'] === 'REVIEW' && \App\Helpers\AuthHelper::hasRole(['SUPERADMIN'])): ?>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Review Opname</h3>
            <form action="<?= url('/opnames/process') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <input type="hidden" name="opname_id" value="<?= $opname['id'] ?>">
                
                <div class="mb-4">
                    <label for="action" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Keputusan Review</label>
                    <select name="action" id="action" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                        <option value="">Pilih Keputusan...</option>
                        <option value="APPROVED">Setujui & Terapkan Penyesuaian (APPROVED)</option>
                        <option value="REJECTED">Tolak Hasil Opname</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="resolution_notes" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Catatan Reviewer</label>
                    <textarea name="resolution_notes" id="resolution_notes" required rows="2" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="Alasan disetujui / ditolak..."></textarea>
                </div>
                
                <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 p-3 rounded mb-4 text-xs text-blue-800 dark:text-blue-300">
                    <strong>Penting:</strong> Menyetujui (APPROVE) laporan ini akan secara otomatis memicu transaksi <strong>Stock Adjustment</strong> untuk memperbarui kuantitas di sistem agar sama persis dengan angka fisik.
                </div>
                
                <button type="submit" class="w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
                            data-confirm="Apakah Anda yakin dengan keputusan ini? Menyetujui akan otomatis memicu Stock Adjustment sesuai hasil opname."
                            data-confirm-title="Simpan Keputusan"
                            data-confirm-type="warning"
                            data-confirm-icon="fa-gavel"
                            data-confirm-confirm="Simpan"
                            data-confirm-loading="Menyimpan...">
                    Simpan Keputusan
                </button>
            </form>
        </div>
        <?php endif; ?>

        <!-- Timeline Log -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Audit Log & Timeline</h3>
            <ol class="relative border-l border-gray-200 dark:border-gray-700 ml-3">
                <?php foreach ($timeline as $log): ?>
                <li class="mb-6 ml-6">
                    <span class="absolute flex items-center justify-center w-6 h-6 bg-blue-100 rounded-full -left-3 ring-8 ring-white dark:ring-gray-800 dark:bg-blue-900">
                        <svg class="w-3 h-3 text-blue-800 dark:text-blue-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path></svg>
                    </span>
                    <h3 class="flex items-center mb-1 text-sm font-semibold text-gray-900 dark:text-white"><?= htmlspecialchars($log['action']) ?></h3>
                    <time class="block mb-2 text-xs font-normal leading-none text-gray-400 dark:text-gray-500"><?= date('d M Y H:i', strtotime($log['created_at'])) ?> - <?= htmlspecialchars($log['user_name']) ?></time>
                    <p class="text-sm font-normal text-gray-500 dark:text-gray-400"><?= htmlspecialchars($log['description']) ?></p>
                </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
