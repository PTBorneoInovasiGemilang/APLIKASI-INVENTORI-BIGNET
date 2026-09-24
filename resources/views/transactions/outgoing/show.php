<?php
$pageTitle = 'Detail Barang Keluar';
ob_start();
?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="mb-6 flex items-center justify-between">
    <div class="flex items-center">
      <a href="<?= url('/outgoing') ?>" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 mr-4">
        <i class="fas fa-arrow-left"></i>
      </a>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Detail Barang Keluar: <?= htmlspecialchars($outgoing['transaction_code']) ?></h1>
    </div>
    <?= \App\Helpers\UIHelper::badge(htmlspecialchars($outgoing['status']), 'SUCCESS') ?>
  </div>

  <?php if (isset($_SESSION['success'])): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
      <span class="block sm:inline"><?= htmlspecialchars($_SESSION['success']) ?></span>
      <?php unset($_SESSION['success']); ?>
    </div>
  <?php endif; ?>
  <?php if (isset($_SESSION['error'])): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
      <span class="block sm:inline"><?= htmlspecialchars($_SESSION['error']) ?></span>
      <?php unset($_SESSION['error']); ?>
    </div>
  <?php endif; ?>

  <!-- Info transaksi -->
  <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6 border border-gray-200 dark:border-gray-700">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
        <p class="text-sm text-gray-500 dark:text-gray-400">Kode Transaksi</p>
        <p class="font-medium text-gray-900 dark:text-gray-100"><?= htmlspecialchars($outgoing['transaction_code']) ?></p>
      </div>
      <div>
        <p class="text-sm text-gray-500 dark:text-gray-400">Gudang Asal</p>
        <p class="font-medium text-gray-900 dark:text-gray-100"><?= htmlspecialchars($outgoing['warehouse_name']) ?></p>
      </div>
      <div>
        <p class="text-sm text-gray-500 dark:text-gray-400">Referensi Pengajuan</p>
        <p class="font-medium text-gray-900 dark:text-gray-100">
          <?php if ($outgoing['submission_id']): ?>
            <a href="<?= url('/submissions/show?id=' . (int)$outgoing['submission_id']) ?>" class="text-blue-600 dark:text-blue-400 hover:underline"><?= htmlspecialchars($outgoing['submission_code'] ?: '#' . (int)$outgoing['submission_id']) ?></a>
          <?php else: ?>
            -
          <?php endif; ?>
        </p>
      </div>
      <div>
        <p class="text-sm text-gray-500 dark:text-gray-400">Diproses Oleh</p>
        <p class="font-medium text-gray-900 dark:text-gray-100"><?= htmlspecialchars($outgoing['processor_name']) ?></p>
      </div>
      <div>
        <p class="text-sm text-gray-500 dark:text-gray-400">Waktu</p>
        <p class="font-medium text-gray-900 dark:text-gray-100"><?= htmlspecialchars($outgoing['created_at']) ?></p>
      </div>
      <div>
        <p class="text-sm text-gray-500 dark:text-gray-400">Total Item</p>
        <p class="font-medium text-gray-900 dark:text-gray-100"><?= (int)$outgoing['total_items'] ?> barang</p>
      </div>
      <div class="md:col-span-2">
        <p class="text-sm text-gray-500 dark:text-gray-400">Catatan</p>
        <p class="text-gray-900 dark:text-gray-100"><?= nl2br(htmlspecialchars($outgoing['notes'] ?? '-')) ?></p>
      </div>
    </div>
  </div>


  <!-- Evidence Foto Barang Keluar -->
  <?php
  $evidenceItems = $attachments ?? [];
  $evidenceCanUpload = count($evidenceItems) < 3 && \App\Helpers\AuthHelper::hasRole('ADMIN');
  $evidenceUploadAction = '/outgoing/evidence/upload';
  $evidenceReferenceId = $outgoing['id'];
  $evidenceTitle = 'Foto Bukti Barang Keluar';
  $evidenceEmptyMessage = 'Belum ada foto bukti untuk transaksi ini.';
  $evidenceAccept = '.jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp';
  $evidenceFormatsText = 'JPG/PNG/WEBP, maks 5MB';
  require __DIR__ . '/../../components/evidence_list.php';
  ?>

  <!-- Item -->
  <div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6 border border-gray-200 dark:border-gray-700">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
      <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100"><i class="fas fa-boxes mr-2 text-blue-500"></i> Item Barang</h2>
    </div>
    <?php if (empty($items)): ?>
      <div class="p-6">
        <?php
        $emptyBare = true;
        $emptyMessage = 'Belum ada item tercatat';
        $emptySubMessage = 'Item barang keluar akan muncul di sini.';
        $icon = 'fa-boxes';
        require __DIR__ . '/../../components/table/empty_state.php';
        unset($emptyBare, $emptyMessage, $emptySubMessage, $icon);
        ?>
      </div>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">No</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kode</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama Barang</th>
              <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Satuan</th>
              <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Qty</th>
              <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Serial</th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
            <?php $no = 1; foreach ($items as $item): ?>
              <tr>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center"><?= $no++ ?></td>
                <td class="px-4 py-3 whitespace-nowrap text-sm font-mono text-gray-900 dark:text-gray-100"><?= htmlspecialchars($item['kode_barang']) ?></td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300"><?= htmlspecialchars($item['nama']) ?></td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center"><?= htmlspecialchars($item['unit_name'] ?? '-') ?></td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300 text-center font-semibold"><?= (int)$item['quantity'] ?></td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center"><?= $item['is_serialized'] ? '<i class="fas fa-hashtag text-blue-400"></i> Ya' : '<span class="text-gray-400">-</span>' ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

  <!-- SN dikeluarkan -->
  <div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6 border border-gray-200 dark:border-gray-700">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
      <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100"><i class="fas fa-barcode mr-2 text-green-500"></i> Nomor Seri Dikeluarkan</h2>
    </div>
    <?php if (empty($serialMovements)): ?>
      <div class="p-6">
        <?php
        $emptyBare = true;
        $emptyMessage = 'Tidak ada nomor seri pada transaksi ini';
        $emptySubMessage = 'Barang non-serialized atau transaksi lama tanpa pencatatan SN.';
        $icon = 'fa-barcode';
        require __DIR__ . '/../../components/table/empty_state.php';
        unset($emptyBare, $emptyMessage, $emptySubMessage, $icon);
        ?>
      </div>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">No</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Serial Number</th>
              <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pemegang</th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
            <?php $no = 1; foreach ($serialMovements as $sm): ?>
              <tr>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center"><?= $no++ ?></td>
                <td class="px-4 py-3 whitespace-nowrap text-sm font-mono text-gray-900 dark:text-gray-100"><?= htmlspecialchars($sm['serial_number']) ?></td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-center"><?= \App\Helpers\UIHelper::badge($sm['status'], 'INFO') ?></td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300"><?= htmlspecialchars($sm['holder_name'] ?: '-') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

  <!-- Timeline & Audit -->
  <div class="bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
      <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100"><i class="fas fa-history mr-2 text-purple-500"></i> Timeline & Audit</h2>
    </div>
    <?php if (empty($timeline)): ?>
      <div class="p-6">
        <?php
        $emptyBare = true;
        $emptyMessage = 'Belum ada riwayat tercatat';
        $emptySubMessage = 'Riwayat transaksi ini akan muncul di sini.';
        $icon = 'fa-history';
        require __DIR__ . '/../../components/table/empty_state.php';
        unset($emptyBare, $emptyMessage, $emptySubMessage, $icon);
        ?>
      </div>
    <?php else: ?>
      <div class="p-6">
        <div class="relative border-l-2 border-gray-200 dark:border-gray-700 ml-3">
          <?php foreach ($timeline as $t): ?>
            <div class="mb-6 ml-6 relative">
              <span class="absolute -left-[26px] flex items-center justify-center w-6 h-6 bg-white dark:bg-gray-800 rounded-full border-2 <?= $t['action'] === 'SERIAL_MOVEMENT' ? 'border-green-500 text-green-500' : 'border-blue-500 text-blue-500' ?>">
                <i class="fas <?= $t['action'] === 'SERIAL_MOVEMENT' ? 'fa-arrows-rotate' : 'fa-circle-check' ?> text-[10px]"></i>
              </span>
              <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                <?= htmlspecialchars(str_replace('_', ' ', $t['action'])) ?>
                <?php if (!empty($t['created_at'])): ?><span class="text-xs font-normal text-gray-400"><?= htmlspecialchars($t['created_at']) ?></span><?php endif; ?>
              </p>
              <p class="text-xs text-gray-600 dark:text-gray-400"><?= htmlspecialchars($t['new_data'] ?: '-') ?> — <?= htmlspecialchars($t['actor'] ?: '-') ?></p>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
