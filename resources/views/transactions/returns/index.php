<?php
$pageTitle = 'Pengembalian Barang';
$role = \App\Helpers\AuthHelper::user()['role'];
ob_start();
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pendingCount = 0;
$completedCount = 0;
foreach ($returns as $summaryRow) {
  if (($summaryRow['status'] ?? '') === 'PENDING') $pendingCount++;
  if (($summaryRow['status'] ?? '') === 'COMPLETED') $completedCount++;
}
?>

<div class="space-y-6">
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
      <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Pengembalian Barang</h1>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
        Kelola pengembalian barang pinjaman teknisi dan verifikasi kondisi fisik saat kembali.
      </p>
    </div>
    <?php if ($role === 'TEKNISI_NOC'): ?>
      <a href="<?= url('/returns/create') ?>" class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-xl shadow-sm text-xs transition-all w-full sm:w-auto">
        <i class="fas fa-undo text-xs"></i> Ajukan Pengembalian
      </a>
    <?php endif; ?>
  </div>

  <?php if (isset($_SESSION['success'])): ?>
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-200 shadow-sm">
      <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
    </div>
  <?php endif; ?>
  <?php if (isset($_SESSION['error'])): ?>
    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-200 shadow-sm">
      <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
    </div>
  <?php endif; ?>

  <!-- Summary Cards (Disafa Style) -->
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border <?= $pendingCount > 0 ? 'border-amber-300 dark:border-amber-700/60 ring-2 ring-amber-100 dark:ring-amber-950/30' : 'border-slate-200/80 dark:border-slate-800' ?> p-4 sm:p-5 flex items-center">
      <div class="p-3 rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-300 shrink-0">
        <i class="fas fa-clock text-base sm:text-lg"></i>
      </div>
      <div class="ml-3 sm:ml-4 min-w-0">
        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider truncate">Menunggu Pemeriksaan</p>
        <p class="text-xl sm:text-2xl font-extrabold <?= $pendingCount > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-900 dark:text-slate-100' ?>"><?= $pendingCount ?></p>
      </div>
    </div>
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-800 p-4 sm:p-5 flex items-center">
      <div class="p-3 rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-300 shrink-0">
        <i class="fas fa-circle-check text-base sm:text-lg"></i>
      </div>
      <div class="ml-3 sm:ml-4 min-w-0">
        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider truncate">Selesai Diproses</p>
        <p class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-slate-100"><?= $completedCount ?></p>
      </div>
    </div>
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-800 p-4 sm:p-5 flex items-center">
      <div class="p-3 rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-300 shrink-0">
        <i class="fas fa-info-circle text-base sm:text-lg"></i>
      </div>
      <div class="ml-3 sm:ml-4 min-w-0">
        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider truncate">Proses Admin</p>
        <p class="text-xs text-slate-600 dark:text-slate-300 mt-1 font-medium">Kondisi fisik diperiksa sebelum stok kembali ke gudang.</p>
      </div>
    </div>
  </div>

  <?php
  $toolbarAction = url('/returns');
  $searchPlaceholder ="Cari nama teknisi / gudang...";
  require __DIR__ . '/../../components/table/toolbar.php';
  ?>

  <div class="bg-white dark:bg-slate-900 shadow-sm rounded-2xl overflow-hidden border border-slate-200/80 dark:border-slate-800">
    <div class="overflow-x-auto">
      <table class="w-full min-w-[760px] lg:min-w-full divide-y divide-slate-200/80 dark:divide-slate-800">
        <thead class="bg-slate-50 dark:bg-slate-800/60">
          <tr>
            <th class="px-4 py-3.5 text-center text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 w-12">No</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Tanggal</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Teknisi</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Gudang Pengembalian</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Status</th>
            <th class="px-4 py-3.5 text-center text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 w-24">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
          <?php $no = $offset + 1; foreach ($returns as $ret): ?>
            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
              <td class="px-4 py-3.5 text-xs text-center text-slate-400 font-semibold"><?= $no++ ?></td>
              <td class="px-4 py-3.5 text-xs text-slate-500 dark:text-slate-400 whitespace-nowrap"><?= date('d M Y, H:i', strtotime($ret['created_at'])) ?></td>
              <td class="px-4 py-3.5 text-sm font-bold text-slate-900 dark:text-slate-100">
                <?= htmlspecialchars($ret['technician_name'] ?? '-') ?>
              </td>
              <td class="px-4 py-3.5 text-sm font-semibold text-slate-800 dark:text-slate-200">
                <?= htmlspecialchars($ret['warehouse_name']) ?>
              </td>
              <td class="px-4 py-3.5 whitespace-nowrap">
                <?php if ($ret['status'] === 'PENDING'): ?>
                  <?= \App\Helpers\UIHelper::badge('Menunggu Pemeriksaan', 'WARNING') ?>
                <?php elseif ($ret['status'] === 'COMPLETED'): ?>
                  <?= \App\Helpers\UIHelper::badge('Selesai', 'SUCCESS') ?>
                <?php else: ?>
                  <?= \App\Helpers\UIHelper::badge($ret['status'], 'DANGER') ?>
                <?php endif; ?>
              </td>
              <td class="px-4 py-3.5 text-center text-sm">
                <a href="<?= url('/returns/show?id=' . $ret['id']) ?>" class="inline-flex items-center gap-1.5 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-semibold text-xs py-1 px-2.5 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-950/40 transition-colors">
                  <i class="fas fa-eye text-xs"></i> <?= ($ret['status'] === 'PENDING' && \App\Helpers\AuthHelper::hasRole(['ADMIN'])) ? 'Proses' : 'Lihat' ?>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($returns)): ?>
            <?php 
            $colspan = 6;
            $emptyMessage ="Belum ada data pengembalian.";
            require __DIR__ . '/../../components/table/empty_state.php';
            ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

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
