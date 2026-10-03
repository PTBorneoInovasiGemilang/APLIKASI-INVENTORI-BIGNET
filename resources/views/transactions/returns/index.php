<?php
$pageTitle = 'Pengembalian Barang';
$role = \App\Helpers\AuthHelper::user()['role'];
ob_start();
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
    <div>
      <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Pengembalian Barang</h1>
      <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
        Kelola barang yang diajukan untuk dikembalikan dan periksa hasil pemeriksaannya.
      </p>
    </div>
    <?php if ($role === 'TEKNISI_NOC'): ?>
      <a href="<?= url('/returns/create') ?>" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow whitespace-nowrap inline-flex items-center">
        <i class="fas fa-undo mr-2"></i> Ajukan Pengembalian
      </a>
    <?php endif; ?>
  </div>

  <?php if (isset($_SESSION['success'])): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
      <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
    </div>
  <?php endif; ?>
  <?php if (isset($_SESSION['error'])): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
      <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
    </div>
  <?php endif; ?>

  <?php
  $toolbarAction = url('/returns');
  $searchPlaceholder ="Cari nama teknisi / gudang...";
  require __DIR__ . '/../../components/table/toolbar.php';
  ?>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
    <?php
    $pendingCount = 0;
    $completedCount = 0;
    foreach ($returns as $summaryRow) {
      if (($summaryRow['status'] ?? '') === 'PENDING') $pendingCount++;
      if (($summaryRow['status'] ?? '') === 'COMPLETED') $completedCount++;
    }
    ?>
    <div class="bg-yellow-50 dark:bg-yellow-950/20 border border-yellow-200 dark:border-yellow-900/40 rounded-xl p-4">
      <div class="text-xs font-semibold text-yellow-700 dark:text-yellow-300 uppercase">Menunggu Pemeriksaan</div>
      <div class="text-2xl font-bold text-yellow-900 dark:text-yellow-100 mt-1"><?= $pendingCount ?></div>
    </div>
    <div class="bg-green-50 dark:bg-green-950/20 border border-green-200 dark:border-green-900/40 rounded-xl p-4">
      <div class="text-xs font-semibold text-green-700 dark:text-green-300 uppercase">Selesai Diproses</div>
      <div class="text-2xl font-bold text-green-900 dark:text-green-100 mt-1"><?= $completedCount ?></div>
    </div>
    <div class="bg-blue-50 dark:bg-blue-950/20 border border-blue-200 dark:border-blue-900/40 rounded-xl p-4">
      <div class="text-xs font-semibold text-blue-700 dark:text-blue-300 uppercase">Informasi</div>
      <div class="text-sm text-blue-800 dark:text-blue-200 mt-1">Admin memeriksa kondisi sebelum pengembalian dinyatakan selesai.</div>
    </div>
  </div>

  <div class="overflow-x-auto bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700">
    <table class="w-full min-w-[800px] lg:min-w-full mobile-card-table divide-y divide-gray-200 dark:divide-gray-700">
      <thead class="bg-gray-50 dark:bg-gray-700">
        <tr>
          <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase w-12">No</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Tanggal</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Teknisi</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Gudang Pengembalian</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Status</th>
          <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Aksi</th>
        </tr>
      </thead>
      <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
        <?php $no = $offset + 1; foreach ($returns as $ret): ?>
          <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
            <td data-label="No" class="px-4 py-4 text-sm text-center text-gray-500 dark:text-gray-400"><?= $no++ ?></td>
            <td data-label="Tanggal" class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap"><?= date('d M Y', strtotime($ret['created_at'])) ?></td>
            <td data-label="Teknisi" class="px-4 py-4 text-sm font-semibold text-gray-900 dark:text-gray-100">
              <?= htmlspecialchars($ret['technician_name'] ?? '-') ?>
            </td>
            <td data-label="Gudang Pengembalian" class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300">
              <?= htmlspecialchars($ret['warehouse_name'] ?? '-') ?>
            </td>
            <td data-label="Status" class="px-4 py-4">
              <?php if ($ret['status'] === 'PENDING'): ?>
                <?= \App\Helpers\UIHelper::badge('Menunggu Pemeriksaan', 'WARNING') ?>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Barang belum dinyatakan diterima.</div>
              <?php elseif ($ret['status'] === 'COMPLETED'): ?>
                <?= \App\Helpers\UIHelper::badge('Selesai', 'SUCCESS') ?>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Sudah diproses Admin.</div>
              <?php else: ?>
                <?= \App\Helpers\UIHelper::badge($ret['status'], 'NEUTRAL') ?>
              <?php endif; ?>
            </td>
            <td data-label="Aksi" class="px-4 py-4 text-center">
              <?php
              $isPending = ($ret['status'] ?? '') === 'PENDING';
              $rowActions = [[
                'type' => 'view',
                'url' => url('/returns/show?id=' . (int)$ret['id']),
                'label' => $isPending && in_array($role, ['ADMIN','SUPERADMIN'], true) ? 'Approve' : 'Lihat',
                'icon' => $isPending && in_array($role, ['ADMIN','SUPERADMIN'], true) ? 'fa-clipboard-check' : 'fa-eye',
                'title' => $isPending ? 'Periksa Pengembalian' : 'Detail Pengembalian'
              ]];
              require __DIR__ . '/../../components/table/row_actions.php';
              ?>
            </td>
          </tr>
        <?php endforeach; ?>

        <?php if (empty($returns)): ?>
          <?php
          $colspan = 6;
          $emptyMessage ="Belum ada pengajuan pengembalian.";
          require __DIR__ . '/../../components/table/empty_state.php';
          ?>
        <?php endif; ?>
      </tbody>
    </table>
    <?php
    $paginationBaseUrl = url('/returns');
    require __DIR__ . '/../../components/table/pagination.php';
    ?>
  </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>