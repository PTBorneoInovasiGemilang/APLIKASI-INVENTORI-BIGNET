<?php
$title = 'Barang Masuk';
$pageTitle = 'Barang Masuk';
ob_start();
?>

<div class="space-y-6">
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
      <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Barang Masuk</h1>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Penerimaan barang baru dari pengadaan ke gudang penyimpanan.</p>
    </div>
    <?php if (!\App\Helpers\AuthHelper::hasRole('SUPERADMIN')): ?>
    <a href="<?php echo url('/incoming/create'); ?>" class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-xl shadow-sm text-xs transition-all w-full sm:w-auto">
      <i class="fas fa-plus text-xs"></i> Tambah Barang Masuk
    </a>
    <?php endif; ?>
  </div>

  <?php
  $toolbarAction = url('/incoming');
  $searchPlaceholder ="Cari Kode Transaksi...";
  ob_start();
  ?>
  <div>
    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Gudang</label>
    <select name="warehouse_id" class="shadow-sm appearance-none border border-slate-300 dark:border-slate-700 rounded-xl w-40 py-2 px-3 text-sm text-slate-700 dark:text-slate-200 dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
      <option value="0">Semua Gudang</option>
      <?php foreach ($warehouses as $wh): ?>
        <option value="<?php echo $wh['id']; ?>" <?php echo (isset($_GET['warehouse_id']) && $_GET['warehouse_id'] == $wh['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($wh['name']); ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <?php
  $extraFilters = ob_get_clean();
  require __DIR__ . '/../../components/table/toolbar.php';
  ?>

  <div class="bg-white dark:bg-slate-900 shadow-sm rounded-2xl overflow-hidden border border-slate-200/80 dark:border-slate-800">
    <div class="overflow-x-auto">
      <table class="w-full min-w-[760px] lg:min-w-full divide-y divide-slate-200/80 dark:divide-slate-800">
        <thead class="bg-slate-50 dark:bg-slate-800/60">
          <tr>
            <th class="px-4 py-3.5 text-center text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 w-12">No</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Kode Transaksi</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 min-w-[220px]">Barang Masuk</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Tanggal</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Gudang Tujuan</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Penerima</th>
            <th class="px-4 py-3.5 text-center text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 w-24">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
          <?php 
          $no = ($offset ?? 0) + 1;
          foreach ($incomings as $inc): 
          ?>
          <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
            <td class="px-4 py-3.5 whitespace-nowrap text-xs text-slate-400 text-center font-semibold"><?= $no++ ?></td>
            <td class="px-4 py-3.5 whitespace-nowrap text-sm font-bold text-slate-900 dark:text-slate-100"><?php echo htmlspecialchars($inc['transaction_code']); ?></td>
            <td class="px-4 py-3.5 text-sm text-slate-700 dark:text-slate-300">
              <?php
              $incId = (int)$inc['id'];
              $items = $incomingItems[$incId] ?? [];
              if (!empty($items)):
                if (count($items) === 1):
                  $it = $items[0];
                  ?>
                  <div class="font-bold text-slate-900 dark:text-slate-100"><?= htmlspecialchars($it['nama']) ?></div>
                  <div class="text-xs text-slate-500 dark:text-slate-400"><?= (int)$it['quantity'] ?> <?= htmlspecialchars($it['unit_name'] ?? '') ?></div>
                <?php else:
                  $sum = $incomingSummary[$incId] ?? ['jenis' => count($items), 'total_qty' => 0];
                  ?>
                  <div class="font-bold text-slate-900 dark:text-slate-100"><?= (int)$sum['jenis'] ?> jenis barang &middot; <?= (int)$sum['total_qty'] ?> item</div>
                  <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 space-y-0.5">
                    <?php foreach ($items as $idx => $it): ?>
                      <div><?= htmlspecialchars($it['nama']) ?> — <?= (int)$it['quantity'] ?> <?= htmlspecialchars($it['unit_name'] ?? '') ?></div>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              <?php else: ?>
                <span class="text-slate-400 dark:text-slate-500">-</span>
              <?php endif; ?>
            </td>
            <td class="px-4 py-3.5 whitespace-nowrap text-xs text-slate-500 dark:text-slate-400"><?php echo date('d M Y, H:i', strtotime($inc['created_at'])); ?></td>
            <td class="px-4 py-3.5 whitespace-nowrap text-sm font-semibold text-slate-800 dark:text-slate-200"><?php echo htmlspecialchars($inc['warehouse_name']); ?></td>
            <td class="px-4 py-3.5 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300"><?php echo htmlspecialchars($inc['receiver_name']); ?></td>
            <td class="px-4 py-3.5 text-center text-sm">
              <a href="<?php echo url('/incoming/show?id=' . $inc['id']); ?>" class="inline-flex items-center gap-1.5 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-semibold text-xs py-1 px-2.5 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-950/40 transition-colors">
                <i class="fas fa-eye text-xs"></i> Lihat
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($incomings)): ?>
            <?php 
            $colspan = 7;
            $emptyMessage ="Belum ada transaksi barang masuk.";
            require __DIR__ . '/../../components/table/empty_state.php';
            ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    
    <?php 
    $paginationBaseUrl = url('/incoming');
    require __DIR__ . '/../../components/table/pagination.php'; 
    ?>
  </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
