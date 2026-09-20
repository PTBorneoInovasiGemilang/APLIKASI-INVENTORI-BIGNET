<?php
$pageTitle = 'Detail Master Barang';
ob_start();
?>

<div class="mb-6 fade-in">
  <div class="flex items-center justify-between">
    <div class="flex items-center gap-4">
      <a href="<?= url('/search') ?>" class="text-gray-500 dark:text-gray-400 hover:text-blue-600 transition-colors">
        <i class="fas fa-arrow-left text-xl"></i>
      </a>
      <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Inventory 360 Detail</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">
          <?= htmlspecialchars($item['kode_barang']) ?> - <?= htmlspecialchars($item['nama']) ?>
          <?php if ($snDetail): ?>
            <span class="text-purple-600 dark:text-purple-400 font-bold ml-2 border-l border-gray-300 dark:border-gray-600 pl-2">SN: <?= htmlspecialchars($snDetail['serial_number']) ?></span>
          <?php endif; ?>
        </p>
      </div>
    </div>
    <div class="flex gap-2">
      <a href="<?= url('/timeline?search=' . urlencode($snDetail ? $snDetail['serial_number'] : $item['kode_barang'])) ?>" class="px-4 py-2 bg-purple-100 text-purple-700 hover:bg-purple-200 dark:bg-purple-900/40 dark:text-purple-400 dark:hover:bg-purple-800/50 rounded-lg text-sm font-semibold transition-colors flex items-center gap-2">
        <i class="fas fa-route"></i> Buka Timeline Penuh
      </a>
      <?php if (\App\Helpers\AuthHelper::hasRole('ADMIN')): ?>
      <a href="<?= url('/items/edit/' . $item['id']) ?>" class="px-4 py-2 bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 rounded-lg text-sm font-semibold transition-colors flex items-center gap-2">
        <i class="fas fa-edit"></i> Edit Barang
      </a>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Panel Panduan -->
<div class="bg-indigo-50 dark:bg-indigo-900/30 border-l-4 border-indigo-500 p-4 rounded-md text-sm text-indigo-700 dark:text-indigo-300 mb-6">
  <p class="font-bold mb-1"><i class="fas fa-info-circle mr-1"></i> Panduan Inventory 360</p>
  <ul class="list-disc pl-5 space-y-1">
    <li><strong>Identitas:</strong> Informasi dasar barang.</li>
    <li><strong>Stock:</strong> Rincian ketersediaan stok di seluruh gudang. Physical (Fisik di gudang), Available (Siap digunakan).</li>
    <li><strong>Usage (Instalasi):</strong> Riwayat penggunaan / pemasangan barang ke pelanggan. 1 SN bisa memiliki banyak siklus pemakaian.</li>
    <li><strong>History:</strong> Jejak transaksi keluar/masuk gudang dan perpindahan tangan antar pengguna.</li>
  </ul>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
  <!-- Identitas Utama -->
  <div class="xl:col-span-1 space-y-6">
    <div class="bg-white dark:bg-gray-800 shadow rounded-xl p-6 border border-gray-100 dark:border-gray-700">
      <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
        <i class="fas fa-box text-blue-500"></i> Identitas Barang
      </h3>
      
      <?php if (!empty($attachments)): ?>
        <div class="mb-6 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 flex justify-center p-2">
          <img src="<?= url('/file?path=' . urlencode($attachments[0]['file_path'])) ?>" alt="Foto" class="max-h-48 object-contain rounded">
        </div>
      <?php endif; ?>

      <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2 xl:grid-cols-1">
        <div class="sm:col-span-1">
          <dt class="text-xs font-semibold text-gray-500 dark:text-gray-400">Kode / SKU</dt>
          <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100 font-mono"><?= htmlspecialchars($item['kode_barang']) ?></dd>
        </div>
        <div class="sm:col-span-1">
          <dt class="text-xs font-semibold text-gray-500 dark:text-gray-400">Kategori & Satuan</dt>
          <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100"><?= htmlspecialchars($item['category_name']) ?> &bull; <?= htmlspecialchars($item['unit_name']) ?></dd>
        </div>
        <div class="sm:col-span-1">
          <dt class="text-xs font-semibold text-gray-500 dark:text-gray-400">Tipe Pelacakan</dt>
          <dd class="mt-1 text-sm">
            <?php if ($item['is_serialized']): ?>
              <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400 border border-purple-200 dark:border-purple-800">SERIALIZED</span>
            <?php else: ?>
              <?= \App\Helpers\UIHelper::badge('BULK', 'INFO') ?>
            <?php endif; ?>
          </dd>
        </div>
        <div class="sm:col-span-1">
          <dt class="text-xs font-semibold text-gray-500 dark:text-gray-400">Status Barang</dt>
          <dd class="mt-1 text-sm">
            <?php if ($item['is_active']): ?>
              <?= \App\Helpers\UIHelper::badge('Aktif', 'SUCCESS') ?>
            <?php else: ?>
              <?= \App\Helpers\UIHelper::badge('Nonaktif', 'DANGER') ?>
              <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">Barang ini tidak tersedia untuk transaksi baru.</p>
            <?php endif; ?>
          </dd>
        </div>
        <div class="sm:col-span-1">
          <dt class="text-xs font-semibold text-gray-500 dark:text-gray-400">Barcode / QR</dt>
          <dd class="mt-2">
            <div class="flex flex-col items-start gap-3 max-w-full">
              <?php
              $bcCode = $snDetail['serial_number'] ?? $item['kode_barang'];
              $labelKode = $item['kode_barang'];
              $labelNama = $item['nama'];
              $labelSn = $snDetail['serial_number'] ?? '';
              $bcText = $bcCode;
              require __DIR__ . '/../../components/label_card.php';
              ?>
              <div class="flex gap-2">
                <button type="button" onclick="openQrModal('<?= htmlspecialchars($bcCode, ENT_QUOTES) ?>')" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline inline-flex items-center gap-1">
                  <i class="fas fa-qrcode"></i> QR
                </button>
                <a href="<?= url('/labels?' . ($snDetail ? 'serial=' . urlencode($snDetail['serial_number']) : 'kode=' . urlencode($item['kode_barang']))) ?>" class="text-xs font-semibold text-purple-600 dark:text-purple-400 hover:underline inline-flex items-center gap-1">
                  <i class="fas fa-print"></i> Cetak Label
                </a>
              </div>
            </div>
          </dd>
        </div>
      </dl>
    </div>

    <?php if ($snDetail): ?>
    <!-- Specific SN Details -->
    <div class="bg-gradient-to-br from-purple-50 to-white dark:from-gray-800 dark:to-gray-900 shadow rounded-xl p-6 border border-purple-100 dark:border-purple-900/50">
      <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
        <i class="fas fa-barcode text-purple-500"></i> Detail Nomor Seri
      </h3>
      <dl class="space-y-4">
        <div>
          <dt class="text-xs font-semibold text-gray-500 dark:text-gray-400">Nomor Seri</dt>
          <dd class="mt-1 text-lg font-bold font-mono text-purple-700 dark:text-purple-400"><?= htmlspecialchars($snDetail['serial_number']) ?></dd>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <dt class="text-xs font-semibold text-gray-500 dark:text-gray-400">Status & Kondisi</dt>
            <dd class="mt-1 text-sm">
              <div class="font-bold text-gray-900 dark:text-gray-100"><?= htmlspecialchars(\App\Helpers\UIHelper::statusLabel($snDetail['status'])) ?></div>
              <div class="text-gray-600 dark:text-gray-300"><?= htmlspecialchars($snDetail['condition_status']) ?></div>
            </dd>
          </div>
          <div>
            <dt class="text-xs font-semibold text-gray-500 dark:text-gray-400">Lokasi / Holder</dt>
            <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">
              <?php if ($snDetail['warehouse_name']): ?>
                <i class="fas fa-warehouse text-gray-400"></i> <?= htmlspecialchars($snDetail['warehouse_name']) ?>
              <?php elseif ($snDetail['holder_name']): ?>
                <i class="fas fa-user text-gray-400"></i> <?= htmlspecialchars($snDetail['holder_name']) ?>
              <?php else: ?>
                -
              <?php endif; ?>
            </dd>
          </div>
        </div>
      </dl>
    </div>
    <?php endif; ?>

    <!-- Ketersediaan Stok (Bulk/Master) -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-xl overflow-hidden border border-gray-100 dark:border-gray-700">
      <div class="p-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
        <h3 class="text-sm font-bold text-gray-800 dark:text-white flex items-center gap-2">
          <i class="fas fa-cubes text-green-500"></i> Stok per Gudang
        </h3>
      </div>
      <?php if (empty($stocks)): ?>
        <?php
        $emptyBare = true;
        $emptyMessage = 'Belum ada stok fisik tercatat';
        $emptySubMessage = 'Stok di setiap gudang/lokasi akan muncul di sini.';
        $icon = 'fa-boxes-stacked';
        require __DIR__ . '/../../components/table/empty_state.php';
        unset($emptyBare, $emptyMessage, $emptySubMessage, $icon);
        ?>
      <?php else: ?>
        <ul class="divide-y divide-gray-100 dark:divide-gray-700">
          <?php foreach ($stocks as $st):
            // Item serialized: fisik = jumlah unit serial yang benar-benar ada di gudang
            // (konsisten dengan halaman Stok); bulk = stocks.quantity.
            $stPhysical = $item['is_serialized'] ? (int)($st['serials_in_wh'] ?? 0) : (int)$st['physical'];
            $stAvailable = $item['is_serialized'] ? (int)($st['avail_serials'] ?? 0) : $stPhysical;
          ?>
          <li class="p-4 hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-700/50">
            <div class="flex justify-between items-center mb-2">
              <span class="font-semibold text-sm text-gray-900 dark:text-gray-100">
                <i class="fas fa-map-marker-alt text-gray-400 mr-1"></i> <?= htmlspecialchars($st['warehouse_name']) ?>
              </span>
              <span class="text-lg font-bold text-gray-900 dark:text-gray-100"><?= number_format($stPhysical) ?></span>
            </div>
            <div class="grid grid-cols-3 gap-2 text-xs text-center mt-2 bg-gray-50 dark:bg-gray-900 p-2 rounded">
              <div>
                <div class="text-gray-500 dark:text-gray-400">Available</div>
                <div class="font-bold text-green-600 dark:text-green-400"><?= number_format($stAvailable) ?></div>
              </div>
              <div class="border-l border-gray-200 dark:border-gray-700">
                <div class="text-gray-500 dark:text-gray-400">Reserved</div>
                <div class="font-bold text-yellow-600 dark:text-yellow-400"><?= number_format($st['reserved']) ?></div>
              </div>
              <div class="border-l border-gray-200 dark:border-gray-700">
                <div class="text-gray-500 dark:text-gray-400">Allocated</div>
                <div class="font-bold text-blue-600 dark:text-blue-400"><?= number_format($st['allocated']) ?></div>
              </div>
            </div>
          </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  </div>

  <!-- Data Dinamis (Usage, History, Serials) -->
  <div class="xl:col-span-2 space-y-6">
    
    <?php if ($snDetail): ?>
    <!-- USAGE CYCLES -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
      <div class="p-4 border-b border-gray-100 dark:border-gray-700">
        <h3 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
          <i class="fas fa-tools text-orange-500"></i> Usage / Instalasi Pelanggan
        </h3>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Riwayat siklus pemakaian / pemasangan ke pelanggan atau site.</p>
      </div>
      <?php if (empty($installations)): ?>
        <div class="p-8 text-center text-gray-500 dark:text-gray-400 text-sm">Barang ini belum pernah dipasang/diinstalasi.</div>
      <?php else: ?>
        <div class="overflow-x-auto">
          <table class="min-w-full text-left text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400">
              <tr>
                <th class="p-4 font-medium">Tanggal</th>
                <th class="p-4 font-medium">Pelanggan / Site</th>
                <th class="p-4 font-medium">Transaksi</th>
                <th class="p-4 font-medium">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
              <?php foreach ($installations as $inst): ?>
              <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                <td class="p-4 text-gray-900 dark:text-gray-200"><?= date('d/m/Y H:i', strtotime($inst['installed_at'])) ?></td>
                <td class="p-4">
                  <div class="font-semibold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($inst['customer_name']) ?></div>
                  <div class="text-xs text-gray-500 dark:text-gray-400"><?= htmlspecialchars($inst['customer_site'] ?? '-') ?></div>
                </td>
                <td class="p-4 font-mono text-blue-600 dark:text-blue-400">
                  <a href="<?= url('/installations/show?id=' . $inst['id']) ?>" class="hover:underline"><?= htmlspecialchars($inst['transaction_code']) ?></a>
                </td>
                <td class="p-4">
                  <span class="px-2 py-1 bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 rounded text-xs"><?= htmlspecialchars(\App\Helpers\UIHelper::statusLabel($inst['status'])) ?></span>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- HISTORY (Mutasi Log) -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
      <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
        <div>
          <h3 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fas fa-history text-indigo-500"></i> Mutasi History 
          </h3>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Jejak pergerakan barang (Masuk, Keluar, Pindah Tangan).</p>
        </div>
      </div>
      <?php if (empty($history)): ?>
        <div class="p-8 text-center text-gray-500 dark:text-gray-400 text-sm">Tidak ada riwayat mutasi.</div>
      <?php else: ?>
        <div class="overflow-x-auto max-h-96 overflow-y-auto">
          <table class="min-w-full text-left text-sm relative">
            <thead class="bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 sticky top-0">
              <tr>
                <th class="p-3 font-medium">Waktu</th>
                <th class="p-3 font-medium text-center">Aksi</th>
                <th class="p-3 font-medium">Oleh</th>
                <?php if ($snDetail): ?>
                  <th class="p-3 font-medium">Dari &rarr; Ke</th>
                <?php else: ?>
                  <th class="p-3 font-medium">Referensi</th>
                  <th class="p-3 font-medium">Stok Sebelum</th>
                  <th class="p-3 font-medium">Perubahan</th>
                  <th class="p-3 font-medium">Stok Sesudah</th>
                <?php endif; ?>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
              <?php foreach ($history as $log): ?>
                <?php if ($snDetail): ?>
                  <!-- Log untuk SN (serial_movements) -->
                  <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <td class="p-3 text-xs text-gray-500 dark:text-gray-400"><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></td>
                    <td class="p-3">
                      <span class="px-2 py-0.5 inline-flex items-center gap-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                        <i class="fas fa-exchange-alt"></i> <?= htmlspecialchars($log['from_status'] ?? '-') ?> &rarr; <?= htmlspecialchars($log['to_status'] ?? '-') ?>
                      </span>
                    </td>
                    <td class="p-3 text-gray-600 dark:text-gray-400"><?= htmlspecialchars($log['user_name'] ?? 'Sistem') ?></td>
                    <td class="p-3 text-xs text-gray-600 dark:text-gray-400">
                      <?php
                      $from = $log['from_warehouse'] ?: $log['from_holder'] ?: '-';
                      $to = $log['to_warehouse'] ?: $log['to_holder'] ?: '-';
                      echo htmlspecialchars($from) . ' &rarr; ' . htmlspecialchars($to);
                      ?>
                    </td>
                  </tr>
                <?php else: ?>
                  <!-- Log untuk Bulk (stock_ledgers) -->
                  <?php
                  $delta = (int)($log['quantity_delta'] ?? 0);
                  $unitName = $item['unit_name'] ?? '';
                  $refCode = $log['ref_code'] ?? null;
                  $refRoute = $log['ref_route'] ?? null;
                  ?>
                  <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <td class="p-3 text-xs text-gray-500 dark:text-gray-400"><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></td>
                    <td class="p-3">
                      <div class="font-semibold text-gray-800 dark:text-gray-200"><?= htmlspecialchars(\App\Helpers\UIHelper::mutationTypeLabel($log['transaction_type'])) ?></div>
                      <?php if ($refCode): ?>
                        <?php if ($refRoute): ?>
                          <a href="<?= url($refRoute) ?>" class="text-xs text-blue-500 hover:underline"><?= htmlspecialchars($refCode) ?></a>
                        <?php else: ?>
                          <span class="text-xs text-gray-500 dark:text-gray-400"><?= htmlspecialchars($refCode) ?></span>
                        <?php endif; ?>
                      <?php endif; ?>
                    </td>
                    <td class="p-3 text-gray-600 dark:text-gray-400"><?= htmlspecialchars($log['user_name'] ?? 'Sistem') ?></td>
                    <td class="p-3 text-sm text-gray-600 dark:text-gray-400 text-right"><?= number_format((int)$log['balance_before']) ?> <?= htmlspecialchars($unitName) ?></td>
                    <td class="p-3 text-sm text-right">
                      <?php if ($delta > 0): ?>
                        <span class="text-green-600 dark:text-green-400 font-bold">+<?= $delta ?> <?= htmlspecialchars($unitName) ?></span>
                      <?php elseif ($delta < 0): ?>
                        <span class="text-red-600 dark:text-red-400 font-bold"><?= $delta ?> <?= htmlspecialchars($unitName) ?></span>
                      <?php else: ?>
                        <span class="text-gray-500 dark:text-gray-400">0</span>
                      <?php endif; ?>
                      <div class="text-xs text-gray-500 dark:text-gray-400">Gudang: <?= htmlspecialchars($log['warehouse_name'] ?? '-') ?></div>
                    </td>
                    <td class="p-3 text-sm font-bold text-gray-800 dark:text-gray-200 text-right"><?= number_format((int)$log['balance_after']) ?> <?= htmlspecialchars($unitName) ?></td>
                  </tr>
                <?php endif; ?>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <?php if (!$snDetail && $item['is_serialized']): ?>
    <!-- ALL SERIALS LIST (For Master Item Context) -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
      <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
        <h3 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
          <i class="fas fa-barcode text-purple-500"></i> Daftar Nomor Seri
        </h3>
        <span class="bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200 text-xs font-semibold px-2.5 py-0.5 rounded-full">
          Total: <?= count($serials) ?>
        </span>
      </div>
      <?php if (empty($serials)): ?>
        <?php
        $emptyBare = true;
        $emptyMessage = 'Belum ada Serial Number terdaftar';
        $emptySubMessage = 'Nomor seri barang ini akan muncul di sini setelah dicatat.';
        $icon = 'fa-hashtag';
        require __DIR__ . '/../../components/table/empty_state.php';
        unset($emptyBare, $emptyMessage, $emptySubMessage, $icon);
        ?>
      <?php else: ?>
        <div class="overflow-x-auto max-h-96 overflow-y-auto">
          <table class="min-w-full text-left text-sm relative">
            <thead class="bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 sticky top-0">
              <tr>
                <th class="p-3 font-medium">SN</th>
                <th class="p-3 font-medium">Status & Kondisi</th>
                <th class="p-3 font-medium">Lokasi / Holder</th>
                <th class="p-3 font-medium text-center">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
              <?php foreach ($serials as $sn): ?>
              <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                <td class="p-3 font-mono font-bold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($sn['serial_number']) ?></td>
                <td class="p-3 text-xs">
                  <div class="font-bold text-gray-800 dark:text-gray-200"><?= htmlspecialchars(\App\Helpers\UIHelper::statusLabel($sn['status'])) ?></div>
                  <div class="text-gray-500 dark:text-gray-400"><?= htmlspecialchars($sn['condition_status']) ?></div>
                </td>
                <td class="p-3 text-gray-600 dark:text-gray-400">
                  <?php if ($sn['warehouse_name']): ?>
                    <i class="fas fa-warehouse text-xs w-4"></i> <?= htmlspecialchars($sn['warehouse_name']) ?>
                  <?php elseif ($sn['holder_name']): ?>
                    <i class="fas fa-user text-xs w-4"></i> <?= htmlspecialchars($sn['holder_name']) ?>
                  <?php else: ?>
                    -
                  <?php endif; ?>
                </td>
                <td class="p-3">
                  <a href="<?= url('/items/show/' . $item['id'] . '?sn_id=' . $sn['id']) ?>" class="text-blue-600 hover:underline font-medium">Buka 360</a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>

  </div>
</div>

<!-- QR Modal -->
<div id="qrModal" class="fixed z-50 inset-0 overflow-y-auto hidden" role="dialog" aria-modal="true" aria-labelledby="qrModalTitle">
  <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
    <div class="fixed inset-0 bg-gray-50 dark:bg-gray-9000 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
    <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:align-middle sm:max-w-sm sm:w-full">
      <div class="px-6 py-5 text-center">
        <h3 id="qrModalTitle" class="text-lg font-bold text-gray-900 dark:text-white mb-3">QR Code</h3>
        <div id="qrModalBody" class="flex justify-center"></div>
        <p id="qrModalText" class="mt-3 text-sm font-mono text-gray-600 dark:text-gray-400 break-all"></p>
      </div>
      <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900/50 flex justify-end gap-2">
        <button type="button" onclick="document.getElementById('qrModal').classList.add('hidden')" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md text-sm font-semibold">Tutup</button>
      </div>
    </div>
  </div>
</div>

<script src="<?= url('/assets/js/qrcode.min.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.qr-box').forEach(function (box) {
    try {
      var qr = qrcode(0, 'M');
      qr.addData(box.getAttribute('data-qr'));
      qr.make();
      box.innerHTML = qr.createSvgTag({ cellSize: 3, margin: 1 });
    } catch (e) {
      box.innerHTML = '<span class="text-[10px] text-gray-400">QR error</span>';
    }
  });
});

function openQrModal(text) {
 var body = document.getElementById('qrModalBody');
 body.innerHTML = '';
 try {
  var qr = qrcode(0, 'M');
  qr.addData(text);
  qr.make();
  body.innerHTML = qr.createSvgTag({ cellSize: 5, margin: 1 });
 } catch (e) {
  body.innerHTML = '<span class="text-sm text-red-500">Gagal membuat QR</span>';
 }
 document.getElementById('qrModalText').textContent = text;
 document.getElementById('qrModal').classList.remove('hidden');
}
document.getElementById('qrModal').addEventListener('click', function (e) {
 if (e.target === this) this.classList.add('hidden');
});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
