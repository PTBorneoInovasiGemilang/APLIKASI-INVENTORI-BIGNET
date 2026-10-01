<?php
$pageTitle = 'Detail Pengembalian';
ob_start();
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="mb-6 flex items-center justify-between">
    <div class="flex items-center">
      <a href="<?= url('/returns') ?>" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 mr-4">
        <i class="fas fa-arrow-left"></i>
      </a>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Detail Pengembalian: <?= htmlspecialchars($return['transaction_code']) ?></h1>
    </div>
    
    <?php if ($return['status'] === 'PENDING'): ?>
      <?= \App\Helpers\UIHelper::badge('Menunggu Proses', 'WARNING') ?>
    <?php elseif ($return['status'] === 'COMPLETED'): ?>
      <?= \App\Helpers\UIHelper::badge('Selesai', 'SUCCESS') ?>
    <?php else: ?>
      <?= \App\Helpers\UIHelper::badge($return['status'], 'DANGER') ?>
    <?php endif; ?>
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

  <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
        <p class="text-sm text-gray-500 dark:text-gray-400">Teknisi (Pengembali)</p>
        <p class="font-medium text-gray-900 dark:text-gray-100"><?= htmlspecialchars($return['technician_name'] ?? '-') ?></p>
      </div>
      <div>
        <p class="text-sm text-gray-500 dark:text-gray-400">Gudang Tujuan</p>
        <p class="font-medium text-gray-900 dark:text-gray-100"><?= htmlspecialchars($return['warehouse_name']) ?></p>
      </div>
      <div>
        <p class="text-sm text-gray-500 dark:text-gray-400">Referensi Peminjaman</p>
        <p class="font-medium text-gray-900 dark:text-gray-100"><?= htmlspecialchars($return['loan_code'] ?? '-') ?></p>
      </div>
      <div>
        <p class="text-sm text-gray-500 dark:text-gray-400">Diproses Oleh</p>
        <p class="font-medium text-gray-900 dark:text-gray-100"><?= htmlspecialchars($return['processor_name'] ?? '-') ?></p>
      </div>
      <div class="md:col-span-2">
        <p class="text-sm text-gray-500 dark:text-gray-400">Catatan</p>
        <p class="text-gray-900 dark:text-gray-100"><?= nl2br(htmlspecialchars($return['notes'] ?? '-')) ?></p>
      </div>
    </div>
  </div>

  <?php $canProcess = $return['status'] === 'PENDING' && \App\Helpers\AuthHelper::hasRole(['ADMIN']); ?>
  <?php if ($canProcess): ?>
  <form action="<?= url('/returns/process') ?>" method="POST">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    <input type="hidden" name="id" value="<?= $return['id'] ?>">
  <?php endif; ?>

    <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden mb-6">
      <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Daftar Barang</h2>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Barang</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kuantitas</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Serial Number</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kondisi</th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            <?php foreach ($items as $item): ?>
            <tr>
              <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                <?= htmlspecialchars($item['nama']) ?><?php if (trim((string)($item['kode_barang'] ?? '')) !== ''): ?> (<?= htmlspecialchars($item['kode_barang']) ?>)<?php endif; ?>
              </td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                <?= $item['quantity'] ?>
              </td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                <?= $item['serial_number'] ? htmlspecialchars($item['serial_number']) : '-' ?>
              </td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                <?php if ($return['status'] === 'PENDING' && \App\Helpers\AuthHelper::hasRole(['ADMIN'])): ?>
                  <select name="conditions[<?= $item['id'] ?>]" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    <option value="BAIK">Baik</option>
                    <option value="RUSAK_RINGAN">Rusak Ringan</option>
                    <option value="RUSAK_BERAT">Rusak Berat</option>
                    <option value="PERLU_PERBAIKAN">Perlu Perbaikan</option>
                    <option value="TIDAK_LENGKAP">Tidak Lengkap</option>
                    <option value="HILANG">Hilang</option>
                  </select>
                <?php else: ?>
                  <?= htmlspecialchars(\App\Helpers\UIHelper::statusLabel($item['condition_status'])) ?>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($items)): ?>
              <?php $colspan = 4; $emptyMessage ="Belum ada barang dalam pengembalian ini."; require __DIR__ . '/../../components/table/empty_state.php'; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <?php if ($canProcess): ?>
    <div class="flex justify-end mt-6">
      <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow">
        Setujui & Tandai Sudah Dikembalikan
      </button>
    </div>
  </form>
  <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
