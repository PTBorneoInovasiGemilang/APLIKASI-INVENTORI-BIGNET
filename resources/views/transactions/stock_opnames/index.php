<?php
$title = 'Stock Opname';
$pageTitle = 'Stock Opname';
ob_start();
?>

<div class="space-y-6">
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
      <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight"><?= $title ?></h1>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Audit fisik stok gudang, verifikasi selisih (variance), dan penyesuaian otomatis.</p>
    </div>
    <?php if (\App\Helpers\AuthHelper::hasRole(['ADMIN'])): ?>
    <a href="<?= url('/opnames/create') ?>" class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-xl shadow-sm text-xs transition-all w-full sm:w-auto">
      <i class="fas fa-plus text-xs"></i> Buat Stock Opname
    </a>
    <?php endif; ?>
  </div>

  <!-- Toolbar -->
  <?php 
  $toolbarAction = url('/opnames');
  $searchPlaceholder = "Cari Kode / Gudang...";
  require __DIR__ . '/../../components/table/toolbar.php'; 
  ?>

  <div class="bg-white dark:bg-slate-900 shadow-sm rounded-2xl overflow-hidden border border-slate-200/80 dark:border-slate-800">
    <div class="overflow-x-auto">
      <table class="w-full min-w-[760px] lg:min-w-full table-sticky divide-y divide-slate-200/80 dark:divide-slate-800">
        <thead class="bg-slate-50 dark:bg-slate-800/60">
          <tr>
            <th class="px-4 py-3.5 text-center text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 w-12">No</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Kode Opname</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Gudang</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Tanggal / Oleh</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Status</th>
            <th class="px-4 py-3.5 text-center text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 w-24">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
          <?php if (empty($opnames)): ?>
            <?php
            $colspan = 6;
            $emptyMessage = "Belum ada catatan stock opname.";
            require __DIR__ . '/../../components/table/empty_state.php';
            ?>
          <?php else: ?>
            <?php foreach ($opnames as $index => $opname): ?>
            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
              <td class="px-4 py-3.5 text-center text-xs text-slate-400 font-semibold"><?= $offset + $index + 1 ?></td>
              <td class="px-4 py-3.5 font-bold text-sm text-slate-900 dark:text-slate-100">
                <?= htmlspecialchars($opname['transaction_code']) ?>
              </td>
              <td class="px-4 py-3.5 text-sm font-semibold text-slate-800 dark:text-slate-200">
                <?= htmlspecialchars($opname['warehouse_name']) ?>
              </td>
              <td class="px-4 py-3.5 text-xs text-slate-500 dark:text-slate-400">
                <div><?= date('d M Y, H:i', strtotime($opname['opname_date'])) ?></div>
                <div class="mt-0.5 text-slate-600 dark:text-slate-300">Oleh: <strong><?= htmlspecialchars($opname['conducted_by_name']) ?></strong></div>
              </td>
              <td class="px-4 py-3.5 whitespace-nowrap">
                <?= \App\Helpers\UIHelper::statusBadge($opname['status']) ?>
              </td>
              <td class="px-4 py-3.5 text-center text-sm">
                <?php
                $rowActions = [['type' => 'view', 'url' => url('/opnames/show?id=' . (int)$opname['id']), 'label' => 'Lihat', 'icon' => 'fa-eye']];
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
    $paginationBaseUrl = url('/opnames');
    require __DIR__ . '/../../components/table/pagination.php'; 
    ?>
  </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
