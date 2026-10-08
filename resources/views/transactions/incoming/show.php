<?php
$title = 'Detail Barang Masuk - ' . htmlspecialchars($incoming['transaction_code']);
$pageTitle = 'Detail Barang Masuk';
ob_start();
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<div class="mb-6 flex items-center justify-between">
  <a href="<?php echo url('/incoming'); ?>" class="text-blue-600 dark:text-blue-400 hover:underline inline-flex items-center">
    <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar Barang Masuk
  </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <!-- Kiri: Info Utama & Item -->
  <div class="lg:col-span-2 space-y-6">
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 border border-gray-200 dark:border-gray-700">
      <div class="flex justify-between items-start mb-4">
        <div>
          <h2 class="text-2xl font-bold text-gray-800 dark:text-white"><?php echo htmlspecialchars($incoming['transaction_code']); ?></h2>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Tanggal: <?php echo date('d M Y, H:i', strtotime($incoming['created_at'])); ?></p>
        </div>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
        <div>
          <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide font-semibold">Gudang Tujuan</p>
          <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">
            <i class="fas fa-warehouse mr-1 text-gray-400"></i> <?php echo htmlspecialchars($incoming['warehouse_name']); ?>
          </p>
        </div>
        <div>
          <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide font-semibold">Penerima</p>
          <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">
            <i class="fas fa-user-check mr-1 text-gray-400"></i> <?php echo htmlspecialchars($incoming['receiver_name']); ?>
          </p>
        </div>
        <div>
          <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide font-semibold">Pemasok</p>
          <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">
            <i class="fas fa-truck mr-1 text-gray-400"></i> <?php echo htmlspecialchars($incoming['supplier_info'] ?: '-'); ?>
          </p>
        </div>
        <div>
          <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide font-semibold">Catatan</p>
          <p class="text-sm text-gray-800 dark:text-gray-200 mt-1 italic">
            <?php echo nl2br(htmlspecialchars($incoming['notes'] ?: 'Tidak ada catatan')); ?>
          </p>
        </div>
      </div>
    </div>
    
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
      <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
        <h3 class="text-lg font-bold text-gray-800 dark:text-white">Daftar Barang</h3>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-12">No</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Barang</th>
              <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-32">Kuantitas</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Serial Number</th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            <?php $no = 1; foreach ($items as $item): ?>
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center" data-label="No"><?= $no++ ?></td>
              <td class="px-6 py-4 text-sm" data-label="Barang">
                <div class="font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($item['kode_barang']); ?></div>
                <div class="text-gray-600 dark:text-gray-300"><?php echo htmlspecialchars($item['nama']); ?></div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white text-center font-semibold" data-label="Kuantitas">
                <?php echo (int)$item['quantity']; ?> <?php echo htmlspecialchars($item['unit_name']); ?>
              </td>
              <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400" data-label="Serial Number">
                <?php if ($item['is_serialized']): ?>
                  <?php $sns = $itemSerials[$item['id']] ?? []; ?>
                  <?php if (count($sns) > 0): ?>
                    <div class="flex flex-wrap gap-1">
                      <?php foreach ($sns as $sn): ?>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-600">
                          <?php echo htmlspecialchars($sn['serial_number']); ?>
                        </span>
                      <?php endforeach; ?>
                    </div>
                  <?php else: ?>
                    <span class="text-gray-400 italic">Data Serial Number tidak ditemukan</span>
                  <?php endif; ?>
                <?php else: ?>
                  <span class="text-gray-400 italic">- (Bukan barang serial)</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  
  <!-- Kanan: Bukti & Timeline -->
  <div class="space-y-6">
    <!-- Komponen Lampiran Bukti -->
    <?php
    $evidenceItems = $attachments;
    $evidenceCanUpload = true; // Admin bisa unggah susulan
    $evidenceUploadAction = '/incoming/evidence/upload';
    $evidenceReferenceId = $incoming['id'];
    $evidenceTitle = 'Foto Bukti Barang Masuk';
    $evidenceEmptyMessage = 'Belum ada bukti yang dilampirkan untuk transaksi ini.';
    require __DIR__ . '/../../components/evidence_list.php';
    ?>
    
    <!-- Timeline -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
      <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 flex justify-between items-center">
        <h3 class="text-sm font-semibold text-gray-800 dark:text-white uppercase tracking-wide">
          <i class="fas fa-history mr-2 text-gray-400"></i> Riwayat Aktivitas
        </h3>
      </div>
      <div class="p-4">
        <ul class="space-y-4">
          <?php foreach ($timeline as $idx => $t): ?>
          <li class="relative pl-6 pb-4 border-l border-gray-200 dark:border-gray-700 last:border-0 last:pb-0">
            <span class="absolute left-[-5px] top-1 w-2.5 h-2.5 rounded-full <?php echo $idx === 0 ? 'bg-blue-500' : 'bg-gray-300 dark:bg-gray-600'; ?>"></span>
            <div class="text-sm">
              <p class="font-semibold text-gray-900 dark:text-gray-100">
                <?php echo htmlspecialchars($t['action']); ?>
              </p>
              <p class="text-gray-500 dark:text-gray-400 text-xs mt-0.5">
                Oleh: <?php echo htmlspecialchars($t['user_name']); ?> &middot; <?php echo date('d M Y H:i', strtotime($t['created_at'])); ?>
              </p>
              <?php if (!empty($t['new_data'])): ?>
                <?php 
                $nd = json_decode($t['new_data'], true); 
                if (isset($nd['description'])):
                ?>
                  <p class="text-gray-600 dark:text-gray-300 mt-1 italic text-xs"><?php echo htmlspecialchars($nd['description']); ?></p>
                <?php endif; ?>
              <?php endif; ?>
            </div>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
