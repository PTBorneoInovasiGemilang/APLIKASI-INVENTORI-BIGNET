<?php
$pageTitle = 'Detail Peminjaman';
ob_start();
?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="mb-6 flex items-center">
    <a href="<?= url('/loans') ?>" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 mr-4">
      <i class="fas fa-arrow-left"></i>
    </a>
    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Detail Peminjaman</h1>
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
        <p class="font-medium text-gray-900 dark:text-gray-100"><?= $loan['due_date'] ? date('d M Y', strtotime($loan['due_date'])) : '-' ?></p>
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
    <div class="flex justify-between items-center mb-4 border-b dark:border-gray-700 pb-2">
      <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Daftar Barang</h2>
      <?php if (!empty($loan['submission_code'])): ?>
        <span class="text-xs text-gray-500 dark:text-gray-400">Terkait pengajuan</span>
      <?php endif; ?>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Sumber</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Barang</th>
            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Kuantitas</th>
            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Dikembalikan</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Serial Number</th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
          <?php foreach ($items as $item): ?>
          <tr>
            <td class="px-4 py-3 text-sm">
              <?php if (($item['source_type'] ?? 'STOCK') === 'MANUAL'): ?>
                <span class="inline-flex px-2 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-semibold">Manual</span>
              <?php else: ?>
                <span class="inline-flex px-2 py-1 rounded-full bg-blue-100 text-blue-800 text-xs font-semibold">Dari Stok</span>
              <?php endif; ?>
            </td>
            <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">
              <?= htmlspecialchars($item['nama']) ?>
              <?php if (($item['source_type'] ?? 'STOCK') === 'MANUAL' && !empty($item['specification'])): ?>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1"><?= htmlspecialchars($item['specification']) ?></div>
              <?php endif; ?>
            </td>
            <td class="px-4 py-3 text-center text-sm font-bold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($item['quantity']) ?><?= !empty($item['unit']) ? ' ' . htmlspecialchars($item['unit']) : '' ?></td>
            <td class="px-4 py-3 text-center text-sm text-green-600 dark:text-green-400 font-bold"><?= htmlspecialchars($item['returned_quantity']) ?></td>
            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
              <?php if (($item['source_type'] ?? 'STOCK') === 'STOCK' && !empty($item['is_serialized']) && !empty($item['serial_numbers'])): ?>
                <ul class="list-disc list-inside">
                  <?php foreach ($item['serial_numbers'] as $sn): ?>
                    <li><?= htmlspecialchars($sn) ?></li>
                  <?php endforeach; ?>
                </ul>
              <?php elseif (($item['source_type'] ?? 'STOCK') === 'STOCK' && !empty($item['is_serialized'])): ?>
                <span class="italic">Belum ada Serial Number</span>
              <?php else: ?>
                <span class="italic">-</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($items)): ?>
            <?php $colspan = 5; $emptyMessage = 'Belum ada barang dalam peminjaman ini.'; require __DIR__ . '/../../components/table/empty_state.php'; ?>
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
