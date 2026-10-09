<?php
$pageTitle = 'Penyesuaian Stok';
ob_start();
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<div class="space-y-6">
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
      <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Penyesuaian Stok (Opname)</h1>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Kelola penyesuaian stok gudang secara manual atau hasil audit opname.</p>
    </div>
    
    <?php if (!\App\Helpers\AuthHelper::hasRole('SUPERADMIN')): ?>
    <a href="<?= url('/adjustments/create') ?>" class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-xl shadow-sm text-xs transition-all w-full sm:w-auto">
      <i class="fas fa-plus text-xs"></i> Penyesuaian Baru
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

  <?php
  $toolbarAction = url('/adjustments');
  $searchPlaceholder ="Cari Kode Transaksi...";
  require __DIR__ . '/../../components/table/toolbar.php';
  ?>

  <div class="bg-white dark:bg-slate-900 shadow-sm rounded-2xl overflow-hidden border border-slate-200/80 dark:border-slate-800">
    <div class="overflow-x-auto">
      <table class="w-full min-w-[760px] lg:min-w-full table-sticky divide-y divide-slate-200/80 dark:divide-slate-800">
        <thead class="bg-slate-50 dark:bg-slate-800/60">
          <tr>
            <th class="px-4 py-3.5 text-center text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 w-12">No</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Tanggal</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Kode</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Gudang</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Diproses Oleh</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Alasan</th>
            <th class="px-4 py-3.5 text-center text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 w-24">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
          <?php 
          $no = $offset + 1;
          foreach ($adjustments as $adj): 
          ?>
          <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
            <td class="px-4 py-3.5 whitespace-nowrap text-xs text-center text-slate-400 font-semibold"><?= $no++ ?></td>
            <td class="px-4 py-3.5 whitespace-nowrap text-xs text-slate-500 dark:text-slate-400"><?php echo date('d M Y, H:i', strtotime($adj['created_at'])); ?></td>
            <td class="px-4 py-3.5 whitespace-nowrap text-sm font-bold text-slate-900 dark:text-slate-100"><?php echo htmlspecialchars($adj['transaction_code']); ?></td>
            <td class="px-4 py-3.5 whitespace-nowrap text-sm font-semibold text-slate-800 dark:text-slate-200"><?php echo htmlspecialchars($adj['warehouse_name']); ?></td>
            <td class="px-4 py-3.5 whitespace-nowrap text-sm text-slate-700 dark:text-slate-300"><?php echo htmlspecialchars($adj['user_name']); ?></td>
            <td class="px-4 py-3.5 text-xs text-slate-500 dark:text-slate-400"><?php echo htmlspecialchars($adj['reason'] ?? '-'); ?></td>
            <?php
            $rowActions = [['type' => 'view', 'url' => url('/adjustments/show?id=' . (int)$adj['id']), 'label' => 'Lihat', 'icon' => 'fa-eye', 'title' => 'Detail Penyesuaian']];
            require __DIR__ . '/../../components/table/row_actions.php';
            ?>
          </tr>
          <?php endforeach; ?>
          
          <?php if (empty($adjustments)): ?>
            <?php
            $colspan = 7;
            $emptyMessage ="Belum ada transaksi penyesuaian stok.";
            require __DIR__ . '/../../components/table/empty_state.php';
            ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    
    <?php 
    $paginationBaseUrl = url('/adjustments');
    require __DIR__ . '/../../components/table/pagination.php';
    ?>
  </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
