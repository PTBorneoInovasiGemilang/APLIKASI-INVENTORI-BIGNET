<?php
$pageTitle = 'Barang Keluar';
ob_start();
$purposeLabels = ['ISSUE' => 'Pengeluaran', 'LOAN' => 'Peminjaman'];
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4 sm:mb-6">
    <div>
      <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Barang Keluar</h1>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Semua pengeluaran inventory yang dieksekusi Admin.</p>
    </div>
    <?php if (!\App\Helpers\AuthHelper::hasRole('SUPERADMIN')): ?>
      <a href="<?= url('/outgoing/create') ?>" class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-4 rounded-xl shadow-sm text-sm transition-all w-full sm:w-auto">
        <i class="fas fa-plus text-xs"></i> Pengeluaran Baru
      </a>
    <?php endif; ?>
  </div>

  <!-- Toolbar -->
  <?php 
  $toolbarAction = url('/outgoing');
  $searchPlaceholder = 'Cari referensi / diproses oleh...';
  ob_start();
  ?>
  <div>
    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Gudang</label>
    <select name="warehouse_id" class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-full sm:w-44 py-2 px-3 text-sm text-gray-700 dark:text-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
      <option value="0">Semua Gudang</option>
      <?php foreach ($warehouses as $wh): ?>
        <option value="<?= $wh['id'] ?>" <?= (isset($_GET['warehouse_id']) && $_GET['warehouse_id'] == $wh['id']) ? 'selected' : '' ?>>
          <?= htmlspecialchars($wh['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  <?php 
  $extraFilters = ob_get_clean();
  require __DIR__ . '/../../components/table/toolbar.php'; 
  ?>

  <!-- Data Table -->
  <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700">
          <tr>
            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-12">No</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jenis</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Gudang</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Diproses Oleh</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Sumber</th>
            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-24">Aksi</th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
          <?php 
          $no = ($offset ?? 0) + 1;
          foreach ($outgoings as $out):
            $purpose = $out['purpose'] ?? 'ISSUE';
          ?>
          <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
            <td class="px-4 py-3 text-center text-sm text-gray-500 dark:text-gray-400"><?= $no++ ?></td>
            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 whitespace-nowrap"><?= date('d M Y H:i', strtotime($out['created_at'])) ?></td>
            <td class="px-4 py-3 text-sm whitespace-nowrap">
              <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold <?= $purpose === 'LOAN' ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' ?>">
                <?= htmlspecialchars($purposeLabels[$purpose] ?? $purpose) ?>
              </span>
            </td>
            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100"><?= htmlspecialchars($out['warehouse_name']) ?></td>
            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars($out['processor_name']) ?></td>
            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
              <?php if ($purpose === 'LOAN' && !empty($out['loan_id'])): ?>
                <a class="text-blue-600 dark:text-blue-400 hover:underline font-medium" href="<?= url('/loans/show?id=' . (int)$out['loan_id']) ?>">Peminjaman</a>
              <?php elseif (!empty($out['submission_id'])): ?>
                <a class="text-blue-600 dark:text-blue-400 hover:underline font-medium" href="<?= url('/submissions/show?id=' . (int)$out['submission_id']) ?>">Pengajuan</a>
              <?php else: ?>
                Manual
              <?php endif; ?>
            </td>
            <td class="px-4 py-3 text-center text-sm">
              <a href="<?= url('/outgoing/show?id=' . (int)$out['id']) ?>" class="inline-flex items-center text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 font-semibold">
                <i class="fas fa-eye mr-1 text-xs"></i>Lihat
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($outgoings)): ?>
            <?php 
            $colspan = 7;
            $emptyMessage ="Belum ada transaksi Barang Keluar.";
            require __DIR__ . '/../../components/table/empty_state.php';
            ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <?php 
    $paginationBaseUrl = url('/outgoing');
    require __DIR__ . '/../../components/table/pagination.php'; 
    ?>
  </div>
</div>

<?php 
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php'; 
?>