<?php
$pageTitle = 'Barang Terpasang';
ob_start();
use App\Helpers\AuthHelper;
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 fade-in">
  <div class="flex justify-between items-center mb-6 border-b border-gray-200 dark:border-gray-800 pb-4">
    <div>
      <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($pageTitle) ?></h1>
      <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Daftar semua barang yang telah Anda pasang di lokasi pelanggan.</p>
    </div>
  </div>

  <?php if (empty($items)): ?>
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-12 text-center border border-gray-200 dark:border-gray-700">
      <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-500 mb-4">
        <i class="fas fa-tools text-2xl"></i>
      </div>
      <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">Belum ada barang terpasang.</h3>
      <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto mb-6">Anda belum mencatat instalasi barang apa pun.</p>
    </div>
  <?php else: ?>
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
      <div class="overflow-x-auto">
        <table class="min-w-full mobile-card-table divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Barang</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kuantitas</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Lokasi / Pelanggan</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ref Instalasi</th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            <?php foreach ($items as $item): ?>
              <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                <td data-label="Tanggal" class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                  <?= date('d M Y, H:i', strtotime($item['installed_at'])) ?>
                </td>
                <td data-label="Barang" class="px-4 py-3 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900 dark:text-gray-100"><?= htmlspecialchars($item['item_name']) ?></div>
                  <div class="text-xs text-gray-500 dark:text-gray-400"><?= htmlspecialchars($item['kode_barang']) ?></div>
                </td>
                <td data-label="Kuantitas" class="px-4 py-3 whitespace-nowrap text-sm font-bold text-gray-700 dark:text-gray-300">
                  <?= number_format($item['quantity']) ?>
                </td>
                <td data-label="Lokasi / Pelanggan" class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                  <?= htmlspecialchars($item['customer_site']) ?>
                </td>
                <td data-label="Ref" class="px-4 py-3 whitespace-nowrap text-sm text-blue-600 dark:text-blue-400 font-medium">
                  <a href="<?= url('/installations/show?id=' . $item['installation_id']) ?>"><?= htmlspecialchars($item['transaction_code']) ?></a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
