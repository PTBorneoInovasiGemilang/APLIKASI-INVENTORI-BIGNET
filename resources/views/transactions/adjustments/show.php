<?php
$pageTitle = 'Detail Penyesuaian Stok';
ob_start();
?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="mb-6 flex items-center">
    <a href="<?= url('/adjustments') ?>" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 mr-4">
      <i class="fas fa-arrow-left"></i>
    </a>
    <div>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Detail Penyesuaian Stok</h1>
      <p class="text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars($adjustment['transaction_code']) ?></p>
    </div>
  </div>

  <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
      <div>
        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Gudang</p>
        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 mt-0.5"><?= htmlspecialchars($adjustment['warehouse_name']) ?></p>
      </div>
      <div>
        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Diproses Oleh</p>
        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 mt-0.5"><?= htmlspecialchars($adjustment['user_name']) ?></p>
      </div>
      <div>
        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Tanggal</p>
        <p class="text-sm text-gray-700 dark:text-gray-300 mt-0.5"><?= date('d M Y, H:i', strtotime($adjustment['created_at'])) ?></p>
      </div>
      <div>
        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Alasan</p>
        <p class="text-sm font-medium text-blue-600 dark:text-blue-400 mt-0.5"><?= htmlspecialchars($adjustment['reason']) ?></p>
      </div>
    </div>
  </div>

  <div class="overflow-x-auto bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700">
    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
      <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Item yang Disesuaikan</h3>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Barang</th>
            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Stok Lama</th>
            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Stok Baru (Aktual)</th>
            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Selisih</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Serial Number Terkait</th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
          <?php foreach ($items as $item): ?>
          <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
            <td class="px-4 py-3 whitespace-nowrap">
              <div class="text-sm font-medium text-gray-900 dark:text-gray-100"><?= htmlspecialchars($item['nama']) ?></div>
              <div class="text-xs text-gray-500 dark:text-gray-400"><?= htmlspecialchars($item['kode_barang']) ?></div>
            </td>
            <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400"><?= $item['old_quantity'] ?></td>
            <td class="px-4 py-3 whitespace-nowrap text-sm text-center font-bold text-gray-900 dark:text-gray-100"><?= $item['new_quantity'] ?></td>
            <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
              <?php if ($item['difference'] > 0): ?>
                <span class="text-green-600 font-bold">+<?= $item['difference'] ?></span>
              <?php elseif ($item['difference'] < 0): ?>
                <span class="text-red-600 font-bold"><?= $item['difference'] ?></span>
              <?php else: ?>
                <span class="text-gray-500 dark:text-gray-400">0</span>
              <?php endif; ?>
            </td>
            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
              <?php if ($item['is_serialized']): ?>
                <?php if (!empty($item['sn_added'])): ?>
                  <div class="text-green-600 mb-1"><span class="font-semibold text-xs text-gray-500 dark:text-gray-400 uppercase block mb-0.5">SN Bertambah:</span> <span class="font-mono text-xs"><?= htmlspecialchars($item['sn_added']) ?></span></div>
                <?php endif; ?>
                <?php if (!empty($item['sn_removed'])): ?>
                  <div class="text-red-600"><span class="font-semibold text-xs text-gray-500 dark:text-gray-400 uppercase block mb-0.5">SN Berkurang/Rusak:</span> <span class="font-mono text-xs"><?= htmlspecialchars($item['sn_removed']) ?></span></div>
                <?php endif; ?>
              <?php else: ?>
                <span class="text-gray-400 dark:text-gray-500 italic text-xs">Bukan barang serial</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($items)): ?>
            <?php $colspan = 5; $emptyMessage ="Belum ada item dalam penyesuaian ini."; require __DIR__ . '/../../components/table/empty_state.php'; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
