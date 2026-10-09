<?php
$title = 'Pengajuan Barang';
$pageTitle = 'Pengajuan Barang';
ob_start();

$currentStatus = $_GET['status'] ?? '';
$currentSearch = trim($_GET['search'] ?? '');
?>

<div class="space-y-6">

  <!-- Header Page & Action Button -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
      <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Pengajuan Barang</h1>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
        Permintaan barang dari teknisi, review persetujuan, dan riwayat pemenuhan gudang.
      </p>
    </div>
    <?php if (\App\Helpers\AuthHelper::hasRole('TEKNISI_NOC')): ?>
      <a href="<?= url('/submissions/create') ?>" class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-xl shadow-sm text-xs transition-all w-full sm:w-auto">
        <i class="fas fa-plus text-xs"></i>
        <span>Buat Pengajuan Baru</span>
      </a>
    <?php endif; ?>
  </div>

  <!-- KPI Summary Cards (Disafa SaaS Style) -->
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-800 p-3.5 sm:p-5 flex flex-col justify-between">
      <div class="flex items-center justify-between gap-1.5">
        <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Total Pengajuan</span>
        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-300 flex items-center justify-center shrink-0">
          <i class="fas fa-clipboard-list text-xs sm:text-sm"></i>
        </div>
      </div>
      <div class="mt-2 sm:mt-3">
        <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-slate-100"><?= number_format($stats['total'] ?? 0) ?></p>
        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Semua data</p>
      </div>
    </div>

    <a href="<?= url('/submissions?status=PENDING') ?>" class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border <?= ($stats['pending'] ?? 0) > 0 ? 'border-amber-300 dark:border-amber-700/60 ring-2 ring-amber-100 dark:ring-amber-950/30' : 'border-slate-200/80 dark:border-slate-800' ?> p-3.5 sm:p-5 flex flex-col justify-between hover:shadow-md transition-all group">
      <div class="flex items-center justify-between gap-1.5">
        <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Menunggu Review</span>
        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg <?= ($stats['pending'] ?? 0) > 0 ? 'bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-300' : 'bg-slate-50 text-slate-400 dark:bg-slate-800' ?> flex items-center justify-center shrink-0">
          <i class="fas fa-clock text-xs sm:text-sm"></i>
        </div>
      </div>
      <div class="mt-2 sm:mt-3">
        <p class="text-2xl sm:text-3xl font-extrabold <?= ($stats['pending'] ?? 0) > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-900 dark:text-slate-100' ?>"><?= number_format($stats['pending'] ?? 0) ?></p>
        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5"><?= ($stats['pending'] ?? 0) > 0 ? 'Perlu tindakan' : 'Selesai diproses' ?></p>
      </div>
    </a>

    <a href="<?= url('/submissions?status=APPROVED') ?>" class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-800 p-3.5 sm:p-5 flex flex-col justify-between hover:shadow-md transition-all group">
      <div class="flex items-center justify-between gap-1.5">
        <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Disetujui</span>
        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-300 flex items-center justify-center shrink-0">
          <i class="fas fa-circle-check text-xs sm:text-sm"></i>
        </div>
      </div>
      <div class="mt-2 sm:mt-3">
        <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-slate-100"><?= number_format($stats['approved'] ?? 0) ?></p>
        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Siap dikeluarkan</p>
      </div>
    </a>

    <a href="<?= url('/submissions?status=COMPLETED') ?>" class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-800 p-3.5 sm:p-5 flex flex-col justify-between hover:shadow-md transition-all group">
      <div class="flex items-center justify-between gap-1.5">
        <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Telah Selesai</span>
        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-300 flex items-center justify-center shrink-0">
          <i class="fas fa-boxes-packing text-xs sm:text-sm"></i>
        </div>
      </div>
      <div class="mt-2 sm:mt-3">
        <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-slate-100"><?= number_format($stats['completed'] ?? 0) ?></p>
        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Barang diserahkan</p>
      </div>
    </a>
  </div>

  <!-- Segmented Quick Filter Pills -->
  <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none text-xs">
    <a href="<?= url('/submissions') ?>" class="px-3.5 py-1.5 rounded-xl font-semibold whitespace-nowrap transition-all <?= empty($currentStatus) ? 'bg-blue-600 text-white shadow-sm' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200/80 dark:border-slate-700' ?>">
      Semua Pengajuan
    </a>
    <a href="<?= url('/submissions?status=PENDING') ?>" class="px-3.5 py-1.5 rounded-xl font-semibold whitespace-nowrap transition-all flex items-center gap-1.5 <?= $currentStatus === 'PENDING' ? 'bg-amber-600 text-white shadow-sm' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200/80 dark:border-slate-700' ?>">
      <i class="fas fa-clock text-[10px]"></i>
      <span>Menunggu Review</span>
      <?php if (($stats['pending'] ?? 0) > 0): ?>
        <span class="inline-flex items-center justify-center px-1.5 py-0.2 rounded-full text-[10px] <?= $currentStatus === 'PENDING' ? 'bg-amber-700 text-white' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300' ?>"><?= $stats['pending'] ?></span>
      <?php endif; ?>
    </a>
    <a href="<?= url('/submissions?status=APPROVED') ?>" class="px-3.5 py-1.5 rounded-xl font-semibold whitespace-nowrap transition-all flex items-center gap-1.5 <?= $currentStatus === 'APPROVED' ? 'bg-blue-600 text-white shadow-sm' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200/80 dark:border-slate-700' ?>">
      <i class="fas fa-circle-check text-[10px]"></i>
      <span>Disetujui</span>
    </a>
    <a href="<?= url('/submissions?status=PARTIAL_APPROVED') ?>" class="px-3.5 py-1.5 rounded-xl font-semibold whitespace-nowrap transition-all flex items-center gap-1.5 <?= $currentStatus === 'PARTIAL_APPROVED' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200/80 dark:border-slate-700' ?>">
      <i class="fas fa-adjust text-[10px]"></i>
      <span>Disetujui Sebagian</span>
    </a>
    <a href="<?= url('/submissions?status=COMPLETED') ?>" class="px-3.5 py-1.5 rounded-xl font-semibold whitespace-nowrap transition-all flex items-center gap-1.5 <?= $currentStatus === 'COMPLETED' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200/80 dark:border-slate-700' ?>">
      <i class="fas fa-boxes-packing text-[10px]"></i>
      <span>Selesai</span>
    </a>
    <a href="<?= url('/submissions?status=REJECTED') ?>" class="px-3.5 py-1.5 rounded-xl font-semibold whitespace-nowrap transition-all flex items-center gap-1.5 <?= $currentStatus === 'REJECTED' ? 'bg-red-600 text-white shadow-sm' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200/80 dark:border-slate-700' ?>">
      <i class="fas fa-times-circle text-[10px]"></i>
      <span>Ditolak</span>
    </a>
  </div>

  <!-- Detailed Filter & Search Toolbar (Disafa Style) -->
  <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-800 p-4 sm:p-5">
    <form method="GET" action="<?= url('/submissions') ?>" class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 items-end">
      <!-- Search Input -->
      <div>
        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1.5">Pencarian</label>
        <div class="relative">
          <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
            <i class="fas fa-search text-xs"></i>
          </span>
          <input type="text" name="search" value="<?= htmlspecialchars($currentSearch) ?>" placeholder="Cari kode pengajuan..." class="w-full pl-10 pr-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
        </div>
      </div>

      <!-- Status Select (Clean native dropdown without overlapping absolute icon) -->
      <div>
        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1.5">Status Pengajuan</label>
        <select name="status" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
          <option value="">Semua Status</option>
          <option value="PENDING" <?= $currentStatus === 'PENDING' ? 'selected' : '' ?>>Menunggu Review</option>
          <option value="APPROVED" <?= $currentStatus === 'APPROVED' ? 'selected' : '' ?>>Disetujui</option>
          <option value="PARTIAL_APPROVED" <?= $currentStatus === 'PARTIAL_APPROVED' ? 'selected' : '' ?>>Disetujui Sebagian</option>
          <option value="COMPLETED" <?= $currentStatus === 'COMPLETED' ? 'selected' : '' ?>>Selesai</option>
          <option value="REJECTED" <?= $currentStatus === 'REJECTED' ? 'selected' : '' ?>>Ditolak</option>
        </select>
      </div>

      <!-- Action Buttons -->
      <div class="flex items-center gap-2">
        <button type="submit" class="flex-1 inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-4 rounded-xl shadow-sm text-sm transition-all min-h-[40px]">
          <i class="fas fa-search text-xs"></i>
          <span>Terapkan</span>
        </button>
        <a href="<?= url('/submissions') ?>" class="inline-flex items-center justify-center gap-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold py-2.5 px-3.5 rounded-xl text-sm transition-all min-h-[40px]" title="Reset Filter">
          <i class="fas fa-rotate-left text-xs"></i>
          <span>Reset</span>
        </a>
      </div>
    </form>
  </div>

  <!-- Data Table Container (Clean Modern SaaS Table) -->
  <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-800 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full min-w-[760px] lg:min-w-full table-sticky divide-y divide-slate-200/80 dark:divide-slate-800">
        <thead class="bg-slate-50 dark:bg-slate-800/60">
          <tr>
            <th class="w-12 px-4 py-3.5 text-center text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">No</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Kode & Tanggal</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Diajukan Oleh</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Tipe Kebutuhan</th>
            <th class="px-4 py-3.5 text-center text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Status</th>
            <th class="w-28 px-4 py-3.5 text-center text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Aksi</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
        <?php
        $no = ($offset ?? 0) + 1;
        foreach ($submissions as $sub):
        ?>
          <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
            <!-- No -->
            <td class="px-4 py-3.5 text-center text-xs font-semibold text-slate-400">
              <?= $no++ ?>
            </td>

            <!-- Kode & Tanggal -->
            <td class="px-4 py-3.5 align-top">
              <a href="<?= url('/submissions/show?id=' . (int)$sub['id']) ?>" class="text-sm font-bold text-blue-600 dark:text-blue-400 hover:underline">
                <?= htmlspecialchars($sub['submission_code']) ?>
              </a>
              <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5 flex items-center gap-1">
                <i class="fas fa-calendar-alt text-[10px]"></i>
                <span><?= date('d M Y, H:i', strtotime($sub['created_at'])) ?></span>
              </p>
            </td>

            <!-- Diajukan Oleh -->
            <td class="px-4 py-3.5 align-top">
              <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-full bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400 font-bold text-xs flex items-center justify-center shrink-0">
                  <?= strtoupper(substr($sub['submitter_name'] ?? 'U', 0, 1)) ?>
                </div>
                <div class="min-w-0">
                  <p class="text-sm font-bold text-slate-900 dark:text-slate-100 truncate"><?= htmlspecialchars($sub['submitter_name']) ?></p>
                  <p class="text-[11px] text-slate-400 dark:text-slate-500 truncate">Teknisi Lapangan</p>
                </div>
              </div>
            </td>

            <!-- Tipe Kebutuhan -->
            <td class="px-4 py-3.5 align-top">
              <?php if (($sub['submission_type'] ?? 'STOCK') === 'MANUAL'): ?>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800">
                  <i class="fas fa-wrench text-[9px]"></i> Kebutuhan Khusus / Manual
                </span>
              <?php else: ?>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                  <i class="fas fa-boxes-stacked text-[9px]"></i> Pengambilan Stok
                </span>
              <?php endif; ?>
            </td>

            <!-- Status Badge -->
            <td class="px-4 py-3.5 text-center align-top">
              <?= \App\Helpers\UIHelper::statusBadge($sub['status']) ?>
            </td>

            <!-- Aksi -->
            <?php
            $rowActions = [['type' => 'view', 'url' => url('/submissions/show?id=' . (int)$sub['id']), 'label' => 'Lihat', 'icon' => 'fa-eye']];
            if (\App\Helpers\AuthHelper::hasRole('TEKNISI_NOC') && $sub['status'] === 'PENDING') {
              $rowActions[] = ['type' => 'link', 'url' => url('/submissions/edit?id=' . (int)$sub['id']), 'label' => 'Edit', 'icon' => 'fa-edit'];
              $rowActions[] = [
                'type' => 'form',
                'action' => url('/submissions/delete'),
                'fields' => ['id' => (int)$sub['id']],
                'label' => 'Hapus',
                'icon' => 'fa-trash',
                'danger' => true,
                'confirm' => [
                  'title' => 'Hapus Pengajuan?',
                  'message' => 'Pengajuan yang belum diproses akan dihapus permanen.',
                  'description' => 'Aksi ini tidak dapat dibatalkan.'
                ]
              ];
            }
            require __DIR__ . '/../../components/table/row_actions.php';
            ?>
          </tr>
        <?php endforeach; ?>

        <?php if (empty($submissions)):
          $colspan = 6;
          if ($currentStatus === 'PENDING') {
            $emptyMessage = 'Tidak ada pengajuan yang menunggu review.';
            $emptySubMessage = 'Semua pengajuan telah diproses oleh Admin.';
          } else {
            $emptyMessage = 'Belum ada data pengajuan.';
            $emptySubMessage = 'Pengajuan barang akan muncul di sini setelah dibuat.';
          }
          require __DIR__ . '/../../components/table/empty_state.php';
        endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php
    $paginationBaseUrl = url('/submissions');
    require __DIR__ . '/../../components/table/pagination.php';
    ?>
  </div>

</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
