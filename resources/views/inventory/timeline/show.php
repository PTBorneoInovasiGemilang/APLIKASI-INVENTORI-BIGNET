<?php
$title = 'Detail Perjalanan Barang';
$pageTitle = 'Detail Perjalanan Barang';

// Page Guide terpusat (shared component + PageGuideHelper)
$guide = \App\Helpers\PageGuideHelper::forPath('/timeline');
$guide['sections'][] = [
  'icon' => 'fa-route',
  'title' => 'Level 2 — Detail Perjalanan',
  'items' => [
    'Riwayat mutasi diurutkan dari paling awal hingga paling akhir (bawah).',
    'Ref (Referensi) menunjuk dokumen transaksi yang memicu pergerakan ini.',
    'SN menampilkan perubahan status; Bulk menampilkan fluktuasi quantity per gudang.',
  ],
];

ob_start();
?>

<div class="mb-6 flex justify-between items-end">
  <div>
    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Detail Perjalanan Barang</h1>
    <p class="text-gray-600 dark:text-gray-400">Lacak riwayat transaksi dan pergerakan.</p>
  </div>
  <a href="<?= url('/timeline') ?>" class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-4 py-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
    <i class="fas fa-arrow-left mr-2"></i> Kembali
  </a>
</div>

<?php if (!$item_detail): ?>
  <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-red-100 dark:border-red-800 p-6 text-center">
    <div class="text-red-500 mb-2"><i class="fas fa-exclamation-triangle text-3xl"></i></div>
    <h2 class="text-lg font-bold text-red-700 dark:text-red-400">Data Tidak Ditemukan</h2>
    <p class="text-gray-600 dark:text-gray-400">Barang yang Anda cari tidak tersedia atau Anda tidak memiliki akses.</p>
  </div>
<?php else: ?>

  <!-- Informasi Barang -->
  <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div>
        <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Tipe</div>
        <div class="font-semibold text-gray-900 dark:text-gray-100">
          <?php if ($type === 'SN'): ?>
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200">SERIALIZED</span>
          <?php else: ?>
            <?= \App\Helpers\UIHelper::badge('BULK', 'INFO') ?>
          <?php endif; ?>
        </div>
      </div>
      <div>
        <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Identitas / SN</div>
        <div class="font-bold text-gray-900 dark:text-gray-100 text-lg"><?= htmlspecialchars($item_detail['identifier']) ?></div>
      </div>
      <div>
        <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Nama Barang</div>
        <div class="font-medium text-gray-900 dark:text-gray-100"><?= htmlspecialchars($item_detail['item_name']) ?></div>
        <div class="text-xs text-gray-500 dark:text-gray-400"><?= htmlspecialchars($item_detail['kode_barang']) ?></div>
      </div>
      <div>
        <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Lokasi / Status Saat Ini</div>
        <div class="font-medium text-gray-900 dark:text-gray-100">
          <?php if ($item_detail['location']): ?>
            <i class="fas fa-building text-gray-400 w-4"></i> <?= htmlspecialchars($item_detail['location']) ?>
          <?php endif; ?>
          <?php if ($item_detail['holder']): ?>
            <i class="fas fa-user text-gray-400 w-4"></i> <?= htmlspecialchars($item_detail['holder']) ?>
          <?php endif; ?>
        </div>
        <div class="text-xs mt-1">
          <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
            <?= htmlspecialchars(\App\Helpers\UIHelper::statusLabel($item_detail['status'])) ?> (Qty: <?= number_format($item_detail['quantity']) ?>)
          </span>
        </div>
      </div>
    </div>
  </div>

  <!-- Timeline Pergerakan -->
  <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-6">Riwayat Mutasi</h3>
    
    <?php if (empty($timeline)): ?>
      <?php
      $emptyBare = true;
      $emptyMessage = 'Belum ada riwayat pergerakan';
      $emptySubMessage = 'Mutasi barang ini akan muncul di sini setelah ada aktivitas.';
      $icon = 'fa-arrows-rotate';
      require __DIR__ . '/../../components/table/empty_state.php';
      unset($emptyBare, $emptyMessage, $emptySubMessage, $icon);
      ?>
    <?php else: ?>
      <div class="relative border-l-2 border-blue-100 dark:border-blue-900 ml-3 md:ml-4">
        <?php foreach ($timeline as $index => $log): ?>
          <div class="mb-8 ml-6 relative">
            <span class="absolute -left-[35px] flex items-center justify-center w-8 h-8 bg-blue-500 rounded-full ring-4 ring-white dark:ring-gray-800 text-white">
              <?php if ($type === 'SN'): ?>
                <?php if ($log['to_status'] === 'AVAILABLE'): ?>
                  <i class="fas fa-download text-xs"></i>
                <?php elseif ($log['to_status'] === 'INSTALLED'): ?>
                  <i class="fas fa-tools text-xs"></i>
                <?php elseif ($log['to_status'] === 'USED' || $log['to_status'] === 'LOANED'): ?>
                  <i class="fas fa-handshake text-xs"></i>
                <?php elseif ($log['to_status'] === 'REPAIR'): ?>
                  <i class="fas fa-wrench text-xs"></i>
                <?php elseif ($log['to_status'] === 'BROKEN'): ?>
                  <i class="fas fa-times-circle text-xs"></i>
                <?php else: ?>
                  <i class="fas fa-exchange-alt text-xs"></i>
                <?php endif; ?>
              <?php else: ?>
                <?php if ($log['transaction_type'] === 'INCOMING' || $log['transaction_type'] === 'RETURN' || $log['transaction_type'] === 'TRANSFER_IN'): ?>
                  <i class="fas fa-arrow-down text-xs"></i>
                <?php else: ?>
                  <i class="fas fa-arrow-up text-xs"></i>
                <?php endif; ?>
              <?php endif; ?>
            </span>
            
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-100 dark:border-gray-700 shadow-sm">
              <div class="flex justify-between items-start mb-2">
                <div>
                  <h4 class="text-md font-bold text-gray-900 dark:text-gray-100">
                    <?php if ($type === 'SN'): ?>
                      <?= htmlspecialchars($log['from_status'] ?: 'NEW') ?> &rarr; <?= htmlspecialchars($log['to_status']) ?>
                    <?php else: ?>
                      <?= htmlspecialchars($log['transaction_type']) ?> 
                      <span class="text-sm font-normal text-gray-500 dark:text-gray-400">(Qty: <?= number_format(abs($log['quantity_delta'])) ?>)</span>
                    <?php endif; ?>
                  </h4>
                  <div class="text-sm text-gray-500 dark:text-gray-400">
                    Oleh: <span class="font-medium"><?= htmlspecialchars($log['user_name'] ?? 'System') ?></span> pada <?= date('d M Y H:i', strtotime($log['created_at'])) ?>
                  </div>
                </div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-gray-200">
                  Ref: <?= htmlspecialchars($type === 'SN' ? $log['reference_type'] : $log['reference_type']) ?> 
                  (#<?= htmlspecialchars($log['reference_id']) ?>)
                </span>
              </div>
              
              <div class="text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 p-3 rounded border border-gray-200 dark:border-gray-700 mt-2">
                <?php if ($type === 'SN'): ?>
                  <div class="grid grid-cols-2 gap-2">
                    <div>
                      <div class="text-xs text-gray-500 dark:text-gray-400">Dari:</div>
                      <div class="font-medium">
                        <?= htmlspecialchars($log['from_warehouse'] ?: $log['from_holder'] ?: 'Unknown/Supplier') ?>
                      </div>
                    </div>
                    <div>
                      <div class="text-xs text-gray-500 dark:text-gray-400">Ke:</div>
                      <div class="font-medium">
                        <?= htmlspecialchars($log['to_warehouse'] ?: $log['to_holder'] ?: 'N/A') ?>
                      </div>
                    </div>
                  </div>
                <?php else: ?>
                  <div class="font-medium">
                    Stok Sesudah: <?= number_format($log['balance_after']) ?>
                  </div>
                <?php endif; ?>
                
                <?php if (!empty($log['notes'])): ?>
                  <div class="mt-2 pt-2 border-t border-gray-100 dark:border-gray-700 text-gray-600 dark:text-gray-400 italic">
                   "<?= htmlspecialchars($log['notes']) ?>"
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

<?php endif; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
