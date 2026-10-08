<?php
$pageTitle = 'Barang Keluar';
ob_start();
$purposeLabels = ['ISSUE' => 'Pengeluaran', 'LOAN' => 'Peminjaman'];
?>

<div class="space-y-6">
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
      <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Barang Keluar</h1>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Semua pengeluaran inventory yang dieksekusi Admin.</p>
    </div>
    <?php if (!\App\Helpers\AuthHelper::hasRole('SUPERADMIN')): ?>
      <a href="<?= url('/outgoing/create') ?>" class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-xl shadow-sm text-xs transition-all w-full sm:w-auto">
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
    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Gudang</label>
    <select name="warehouse_id" class="shadow-sm appearance-none border border-slate-300 dark:border-slate-700 rounded-xl w-full sm:w-44 py-2 px-3 text-sm text-slate-700 dark:text-slate-200 dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
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
  <div class="bg-white dark:bg-slate-900 shadow-sm rounded-2xl overflow-hidden border border-slate-200/80 dark:border-slate-800">
    <div class="overflow-x-auto">
      <table class="w-full min-w-[760px] lg:min-w-full divide-y divide-slate-200/80 dark:divide-slate-800">
        <thead class="bg-slate-50 dark:bg-slate-800/60">
          <tr>
            <th class="px-4 py-3.5 text-center text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 w-12">No</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Tanggal</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Jenis</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Gudang</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Diproses Oleh</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Sumber</th>
            <th class="px-4 py-3.5 text-center text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 w-24">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
          <?php 
          $no = ($offset ?? 0) + 1;
          foreach ($outgoings as $out):
            $purpose = $out['purpose'] ?? 'ISSUE';
          ?>
          <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
            <td class="px-4 py-3.5 text-center text-xs font-semibold text-slate-400"><?= $no++ ?></td>
            <td class="px-4 py-3.5 text-xs text-slate-500 dark:text-slate-400 whitespace-nowrap"><?= date('d M Y, H:i', strtotime($out['created_at'])) ?></td>
            <td class="px-4 py-3.5 text-xs whitespace-nowrap">
              <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-semibold <?= $purpose === 'LOAN' ? 'bg-orange-50 text-orange-700 dark:bg-orange-950/40 dark:text-orange-300 border border-orange-200/60 dark:border-orange-800' : 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 border border-blue-200/60 dark:border-blue-800' ?>">
                <?= htmlspecialchars($purposeLabels[$purpose] ?? $purpose) ?>
              </span>
            </td>
            <td class="px-4 py-3.5 text-sm font-semibold text-slate-900 dark:text-slate-100"><?= htmlspecialchars($out['warehouse_name']) ?></td>
            <td class="px-4 py-3.5 text-sm text-slate-600 dark:text-slate-300"><?= htmlspecialchars($out['processor_name']) ?></td>
            <td class="px-4 py-3.5 text-xs text-slate-500 dark:text-slate-400">
              <?php if ($purpose === 'LOAN' && !empty($out['loan_id'])): ?>
                <a class="text-blue-600 dark:text-blue-400 hover:underline font-semibold" href="<?= url('/loans/show?id=' . (int)$out['loan_id']) ?>">Peminjaman</a>
              <?php elseif (!empty($out['submission_id'])): ?>
                <a class="text-blue-600 dark:text-blue-400 hover:underline font-semibold" href="<?= url('/submissions/show?id=' . (int)$out['submission_id']) ?>">Pengajuan</a>
              <?php else: ?>
                Manual
              <?php endif; ?>
            </td>
            <td class="px-4 py-3.5 text-center text-sm">
              <a href="<?= url('/outgoing/show?id=' . (int)$out['id']) ?>" class="inline-flex items-center gap-1.5 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-semibold text-xs py-1 px-2.5 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-950/40 transition-colors">
                <i class="fas fa-eye text-xs"></i> Lihat
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
