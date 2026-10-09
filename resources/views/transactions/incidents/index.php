<?php
$title = 'Laporan Kerusakan & Kehilangan';
$pageTitle = 'Laporan Kerusakan & Kehilangan';
ob_start();
?>

<div class="space-y-6">
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
      <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight"><?= $title ?></h1>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Daftar laporan barang rusak (Damage) atau hilang (Lost) beserta bukti investigasi.</p>
    </div>
    <?php if (\App\Helpers\AuthHelper::hasRole(['ADMIN', 'TEKNISI_NOC'])): ?>
    <a href="<?= url('/incidents/create') ?>" class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-xl shadow-sm text-xs transition-all w-full sm:w-auto">
      <i class="fas fa-plus text-xs"></i> Buat Laporan
    </a>
    <?php endif; ?>
  </div>

  <!-- Toolbar -->
  <?php 
  ob_start(); 
  ?>
  <div>
    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Status</label>
    <select name="status" class="shadow-sm appearance-none border border-slate-300 dark:border-slate-700 rounded-xl w-44 py-2 px-3 text-sm text-slate-700 dark:text-slate-200 dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
      <option value="">Semua Status</option>
      <option value="PENDING" <?php echo (isset($_GET['status']) && $_GET['status'] == 'PENDING') ? 'selected' : ''; ?>>Menunggu Review (Open)</option>
      <option value="RESOLVED" <?php echo (isset($_GET['status']) && $_GET['status'] == 'RESOLVED') ? 'selected' : ''; ?>>Selesai</option>
      <option value="REJECTED" <?php echo (isset($_GET['status']) && $_GET['status'] == 'REJECTED') ? 'selected' : ''; ?>>Ditolak</option>
    </select>
  </div>
  <?php
  $extraFilters = ob_get_clean();
  $toolbarAction = url('/incidents');
  $searchPlaceholder = "Cari Kode / Pelapor...";
  require __DIR__ . '/../../components/table/toolbar.php'; 
  ?>

  <div class="bg-white dark:bg-slate-900 shadow-sm rounded-2xl overflow-hidden border border-slate-200/80 dark:border-slate-800">
    <div class="overflow-x-auto">
      <table class="w-full min-w-[760px] lg:min-w-full table-sticky divide-y divide-slate-200/80 dark:divide-slate-800">
        <thead class="bg-slate-50 dark:bg-slate-800/60">
          <tr>
            <th class="px-4 py-3.5 text-center text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 w-12">No</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Kode & Tipe</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Tanggal & Gudang</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Pelapor</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Status</th>
            <th class="px-4 py-3.5 text-center text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 w-24">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
          <?php if (empty($incidents)): ?>
            <?php
            $colspan = 6;
            $emptyMessage = (isset($_GET['status']) && $_GET['status'] == 'PENDING') ? "Belum ada insiden terbuka." : "Belum ada laporan insiden.";
            require __DIR__ . '/../../components/table/empty_state.php';
            ?>
          <?php else: ?>
            <?php foreach ($incidents as $index => $incident): ?>
            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
              <td class="px-4 py-3.5 text-center text-xs text-slate-400 font-semibold"><?= $offset + $index + 1 ?></td>
              <td class="px-4 py-3.5">
                <a href="<?= url('/incidents/show?id=' . (int)$incident['id']) ?>" class="text-sm font-bold text-slate-900 dark:text-slate-100 hover:text-blue-600 dark:hover:text-blue-400">
                  <?= htmlspecialchars($incident['transaction_code']) ?>
                </a>
                <div class="mt-1">
                  <?php if ($incident['incident_type'] === 'DAMAGE'): ?>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800">
                      <i class="fas fa-wrench text-[9px]"></i> Kerusakan
                    </span>
                  <?php else: ?>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-300 border border-red-200/60 dark:border-red-800">
                      <i class="fas fa-circle-exclamation text-[9px]"></i> Kehilangan
                    </span>
                  <?php endif; ?>
                </div>
              </td>
              <td class="px-4 py-3.5 text-xs text-slate-500 dark:text-slate-400">
                <div><?= date('d M Y, H:i', strtotime($incident['reported_date'])) ?></div>
                <div class="mt-0.5 font-medium text-slate-700 dark:text-slate-300"><?= $incident['warehouse_name'] ? htmlspecialchars($incident['warehouse_name']) : '-' ?></div>
              </td>
              <td class="px-4 py-3.5 text-sm font-semibold text-slate-800 dark:text-slate-200">
                <?= htmlspecialchars($incident['reporter_name']) ?>
              </td>
              <td class="px-4 py-3.5 whitespace-nowrap">
                <?= \App\Helpers\UIHelper::statusBadge($incident['status']) ?>
              </td>
              <td class="px-4 py-3.5 text-center text-sm">
                <?php
                $rowActions = [['type' => 'view', 'url' => url('/incidents/show?id=' . (int)$incident['id']), 'label' => 'Lihat', 'icon' => 'fa-eye']];
                require __DIR__ . '/../../components/table/row_actions.php';
                ?>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <?php 
    $paginationBaseUrl = url('/incidents');
    require __DIR__ . '/../../components/table/pagination.php'; 
    ?>
  </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
