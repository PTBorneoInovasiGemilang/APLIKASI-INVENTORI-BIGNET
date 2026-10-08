<?php
$pageTitle = 'Barang Terpasang';
ob_start();
?>

<div class="space-y-6">
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
      <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight"><?= htmlspecialchars($pageTitle) ?></h1>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Daftar semua barang yang telah Anda pasang di lokasi pelanggan.</p>
    </div>
  </div>

  <?php if (empty($items)): ?>
    <div class="bg-white dark:bg-slate-900 shadow-sm rounded-2xl p-10 text-center border border-slate-200/80 dark:border-slate-800">
      <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 mb-3">
        <i class="fas fa-tools text-xl"></i>
      </div>
      <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 mb-1">Belum ada barang terpasang.</h3>
      <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto">Anda belum mencatat instalasi barang apa pun.</p>
    </div>
  <?php else: ?>
    <div class="bg-white dark:bg-slate-900 shadow-sm rounded-2xl overflow-hidden border border-slate-200/80 dark:border-slate-800">
      <div class="overflow-x-auto">
        <table class="w-full min-w-[700px] lg:min-w-full divide-y divide-slate-200/80 dark:divide-slate-800">
          <thead class="bg-slate-50 dark:bg-slate-800/60">
            <tr>
              <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Tanggal</th>
              <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Barang</th>
              <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Kuantitas</th>
              <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Lokasi / Pelanggan</th>
              <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Ref Instalasi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <?php foreach ($items as $item): ?>
              <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                <td class="px-4 py-3.5 whitespace-nowrap text-xs text-slate-500 dark:text-slate-400">
                  <?= date('d M Y, H:i', strtotime($item['installed_at'])) ?>
                </td>
                <td class="px-4 py-3.5 whitespace-nowrap">
                  <div class="text-sm font-bold text-slate-900 dark:text-slate-100"><?= htmlspecialchars($item['item_name']) ?></div>
                  <div class="text-xs text-slate-400"><?= htmlspecialchars($item['kode_barang']) ?></div>
                </td>
                <td class="px-4 py-3.5 whitespace-nowrap text-sm font-bold text-slate-800 dark:text-slate-200">
                  <?= number_format($item['quantity']) ?>
                </td>
                <td class="px-4 py-3.5 whitespace-nowrap text-sm text-slate-700 dark:text-slate-300">
                  <?= htmlspecialchars($item['customer_site']) ?>
                </td>
                <td class="px-4 py-3.5 whitespace-nowrap text-xs">
                  <a href="<?= url('/installations/show?id=' . (int)$item['installation_id']) ?>" class="text-blue-600 dark:text-blue-400 hover:underline font-semibold">
                    <?= htmlspecialchars($item['transaction_code']) ?>
                  </a>
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
