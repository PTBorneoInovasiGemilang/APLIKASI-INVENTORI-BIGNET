<?php
$title = 'Barang Masuk';
$pageTitle = 'Barang Masuk';
ob_start();
?>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4 sm:mb-6">
  <div>
    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Barang Masuk</h1>
    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Penerimaan barang baru dari pengadaan ke gudang penyimpanan.</p>
  </div>
  <?php if (!\App\Helpers\AuthHelper::hasRole('SUPERADMIN')): ?>
  <a href="<?php echo url('/incoming/create'); ?>" class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-4 rounded-xl shadow-sm text-sm transition-all w-full sm:w-auto">
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
  <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Gudang</label>
  <select name="warehouse_id" class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-40 py-2 px-3 text-sm text-gray-700 dark:text-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
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

<div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
  <div class="overflow-x-auto">
  <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
    <thead class="bg-gray-50 dark:bg-gray-700">
      <tr>
        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-12">No</th>
        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kode Transaksi</th>
        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Barang Masuk</th>
        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Gudang Tujuan</th>
        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Penerima</th>
        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-24">Aksi</th>
      </tr>
    </thead>
    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
      <?php 
      $no = ($offset ?? 0) + 1;
      foreach ($incomings as $inc): 
      ?>
      <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center"><?= $no++ ?></td>
        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100"><?php echo htmlspecialchars($inc['transaction_code']); ?></td>
        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300 min-w-[220px]">
          <?php
          $incId = (int)$inc['id'];
          $items = $incomingItems[$incId] ?? [];
          if (!empty($items)):
            if (count($items) === 1):
              $it = $items[0];
              ?>
              <div class="font-semibold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($it['nama']) ?></div>
              <div class="text-xs text-gray-500 dark:text-gray-400"><?= (int)$it['quantity'] ?> <?= htmlspecialchars($it['unit_name'] ?? '') ?></div>
            <?php else:
              $sum = $incomingSummary[$incId] ?? ['jenis' => count($items), 'total_qty' => 0];
              ?>
              <div class="font-semibold text-gray-900 dark:text-gray-100"><?= (int)$sum['jenis'] ?> jenis barang &middot; <?= (int)$sum['total_qty'] ?> item</div>
              <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 space-y-0.5">
                <?php foreach ($items as $idx => $it): ?>
                  <div><?= htmlspecialchars($it['nama']) ?> — <?= (int)$it['quantity'] ?> <?= htmlspecialchars($it['unit_name'] ?? '') ?></div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          <?php else: ?>
            <span class="text-gray-400 dark:text-gray-500">-</span>
          <?php endif; ?>
        </td>
        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><?php echo date('Y-m-d H:i', strtotime($inc['created_at'])); ?></td>
        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($inc['warehouse_name']); ?></td>
        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($inc['receiver_name']); ?></td>
        <td class="px-4 py-3 text-center text-sm">
          <a href="<?php echo url('/incoming/show?id=' . $inc['id']); ?>" class="inline-flex items-center text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 font-semibold">
            <i class="fas fa-eye mr-1 text-xs"></i>Lihat
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

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
