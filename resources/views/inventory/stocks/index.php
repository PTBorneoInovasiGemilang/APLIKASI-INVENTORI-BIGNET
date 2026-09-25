<?php
$pageTitle = 'Stok Barang';
$title = 'Stok Barang';
ob_start();
?>

<div class="mb-6 flex flex-col gap-2 lg:flex-row lg:items-end lg:justify-between">
  <div>
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Stok Barang</h1>
    <p class="mt-1 max-w-3xl text-sm text-gray-500 dark:text-gray-400">
      Pantau kondisi barang per gudang. Lihat jumlah yang siap digunakan, sedang dipinjam, dan sedang dikirim.
      Klik <strong>Lihat</strong> untuk membuka detail barang dan nomor seri.
    </p>
  </div>
</div>

<?php
$toolbarAction = url('/stocks');
$searchPlaceholder = 'Cari nama barang...';
ob_start();
?>
<div>
  <label class="mb-1 block text-xs font-bold text-gray-700 dark:text-gray-300">Gudang</label>
  <select name="warehouse_id" class="w-48 rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
    <option value="0">Semua Gudang</option>
    <?php foreach ($warehouses as $wh): ?>
      <option value="<?= (int)$wh['id'] ?>" <?= isset($_GET['warehouse_id']) && (int)$_GET['warehouse_id'] === (int)$wh['id'] ? 'selected' : '' ?>>
        <?= htmlspecialchars($wh['name']) ?>
      </option>
    <?php endforeach; ?>
  </select>
</div>

<div>
  <label class="mb-1 block text-xs font-bold text-gray-700 dark:text-gray-300">Kategori</label>
  <select name="category_id" class="w-48 rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
    <option value="0">Semua Kategori</option>
    <?php foreach ($categories as $cat): ?>
      <option value="<?= (int)$cat['id'] ?>" <?= isset($_GET['category_id']) && (int)$_GET['category_id'] === (int)$cat['id'] ? 'selected' : '' ?>>
        <?= htmlspecialchars($cat['name']) ?>
      </option>
    <?php endforeach; ?>
  </select>
</div>

<div>
  <label class="mb-1 block text-xs font-bold text-gray-700 dark:text-gray-300">Pelacakan</label>
  <select name="tracking" class="w-48 rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
    <option value="">Semua Barang</option>
    <option value="serialized" <?= ($_GET['tracking'] ?? '') === 'serialized' ? 'selected' : '' ?>>Pakai Nomor Seri</option>
    <option value="non_serialized" <?= ($_GET['tracking'] ?? '') === 'non_serialized' ? 'selected' : '' ?>>Tanpa Nomor Seri</option>
  </select>
</div>

<div>
  <label class="mb-1 block text-xs font-bold text-gray-700 dark:text-gray-300">Pergerakan</label>
  <select name="filter" class="w-48 rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
    <option value="">Semua Kondisi</option>
    <option value="low_stock" <?= ($_GET['filter'] ?? '') === 'low_stock' ? 'selected' : '' ?>>Stok Rendah</option>
    <option value="borrowed" <?= ($_GET['filter'] ?? '') === 'borrowed' ? 'selected' : '' ?>>Sedang Dipinjam</option>
    <option value="in_transit" <?= ($_GET['filter'] ?? '') === 'in_transit' ? 'selected' : '' ?>>Dalam Pengiriman</option>
  </select>
</div>
<?php
$extraFilters = ob_get_clean();
require __DIR__ . '/../../components/table/toolbar.php';
?>

<div class="overflow-x-auto rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
  <table class="w-full min-w-[800px] lg:min-w-full divide-y divide-gray-200 dark:divide-gray-700">
    <thead class="bg-gray-50 dark:bg-gray-700">
      <tr>
        <th class="w-12 px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">No</th>
        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Gudang</th>
        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Barang</th>
        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Pelacakan</th>
        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Ringkasan Stok</th>
        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Kondisi</th>
        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Aksi</th>
      </tr>
    </thead>

    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
    <?php
    $no = ($offset ?? 0) + 1;
    foreach ($stocks as $stock):
      $physical = (int)$stock['physical_quantity'];
      $available = (int)$stock['available_quantity'];
      $borrowed = (int)$stock['borrowed_quantity'];
      $inTransit = (int)$stock['in_transit_quantity'];
      $isLow = !empty($stock['is_low_stock']);
      $unit = $stock['unit_name'] ?: 'unit';
      $availablePercent = $physical > 0 ? min(100, round(($available / $physical) * 100)) : 0;
    ?>
      <tr class="group transition hover:bg-gray-50 dark:hover:bg-gray-700/40">
        <td class="px-4 py-4 text-center text-sm text-gray-500 dark:text-gray-400"><?= $no++ ?></td>

        <td class="px-4 py-4 align-top">
          <div class="text-sm font-semibold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($stock['warehouse_name']) ?></div>
          <div class="mt-1 text-xs text-gray-500 dark:text-gray-400"><?= htmlspecialchars($stock['category_name'] ?? 'Tanpa kategori') ?></div>
        </td>

        <td class="px-4 py-4 align-top">
          <div class="text-sm font-semibold text-gray-900 dark:text-white"><?= htmlspecialchars($stock['item_name']) ?></div>
          <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Satuan: <?= htmlspecialchars($unit) ?></div>
        </td>

        <td class="px-4 py-4 align-top">
          <?php if ((int)$stock['is_serialized'] === 1): ?>
            <?= \App\Helpers\UIHelper::badge('Pakai Nomor Seri', 'INFO') ?>
          <?php else: ?>
            <?= \App\Helpers\UIHelper::badge('Tanpa Nomor Seri', 'NEUTRAL') ?>
          <?php endif; ?>
        </td>

        <td class="px-4 py-4 align-top">
          <div class="min-w-[290px] max-w-[430px]">
            <div class="flex items-start justify-between gap-4">
              <div>
                <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                  <?= number_format($available) ?> <?= htmlspecialchars($unit) ?>
                </div>
                <div class="mt-0.5 text-xs font-medium text-gray-500 dark:text-gray-400">
                  tersedia dan siap digunakan
                </div>
              </div>
              <div class="rounded-full bg-green-50 px-2.5 py-1 text-xs font-bold text-green-700 dark:bg-green-950/30 dark:text-green-300">
                <?= $availablePercent ?>%
              </div>
            </div>

            <div class="mt-3 h-2.5 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700" title="<?= $availablePercent ?>% dari total barang tersedia">
              <div class="h-full rounded-full bg-green-500 transition-all" style="width: <?= $availablePercent ?>%"></div>
            </div>

            <div class="mt-3 grid grid-cols-3 gap-2">
              <div class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 dark:border-gray-700 dark:bg-gray-900/40">
                <div class="text-sm font-bold text-gray-900 dark:text-gray-100"><?= number_format($physical) ?></div>
                <div class="mt-0.5 text-[10px] font-medium text-gray-500 dark:text-gray-400">Total</div>
              </div>
              <div class="rounded-xl border border-orange-100 bg-orange-50 px-3 py-2.5 dark:border-orange-900/30 dark:bg-orange-950/20">
                <div class="text-sm font-bold text-orange-600 dark:text-orange-400"><?= number_format($borrowed) ?></div>
                <div class="mt-0.5 text-[10px] font-medium text-gray-500 dark:text-gray-400">Dipinjam</div>
              </div>
              <div class="rounded-xl border border-blue-100 bg-blue-50 px-3 py-2.5 dark:border-blue-900/30 dark:bg-blue-950/20">
                <div class="text-sm font-bold text-blue-600 dark:text-blue-400"><?= number_format($inTransit) ?></div>
                <div class="mt-0.5 text-[10px] font-medium text-gray-500 dark:text-gray-400">Dikirim</div>
              </div>
            </div>
          </div>
        </td>

        <td class="px-4 py-4 align-top">
          <div class="flex max-w-[180px] flex-wrap gap-1.5">
            <?php if (!$stock['is_active']): ?>
              <?= \App\Helpers\UIHelper::badge('Nonaktif', 'DANGER') ?>
            <?php else: ?>
              <?= $available > 0 ? \App\Helpers\UIHelper::badge('Stok Tersedia', 'SUCCESS') : \App\Helpers\UIHelper::badge('Tidak Ada Stok', 'NEUTRAL') ?>
              <?php if ($borrowed > 0): ?>
                <?= \App\Helpers\UIHelper::badge('Sedang Dipinjam', 'INFO') ?>
              <?php endif; ?>
              <?php if ($inTransit > 0): ?>
                <?= \App\Helpers\UIHelper::badge('Dalam Pengiriman', 'INFO') ?>
              <?php endif; ?>
              <?php if ($isLow): ?>
                <?= \App\Helpers\UIHelper::badge('Stok Rendah', 'WARNING') ?>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        </td>

        <?php
        $rowActions = [[
          'type' => 'view',
          'url' => url('/items/show/' . (int)$stock['master_item_id'] . '?warehouse_id=' . (int)$stock['warehouse_id']),
          'label' => 'Lihat',
          'icon' => 'fa-eye',
          'title' => 'Detail Inventory 360'
        ], [
          'type' => 'link',
          'url' => url('/items/incoming-evidence/' . (int)$stock['master_item_id'] . '?warehouse_id=' . (int)$stock['warehouse_id']),
          'label' => 'Riwayat Foto Masuk',
          'icon' => 'fa-camera'
        ]];
        require __DIR__ . '/../../components/table/row_actions.php';
        ?>
      </tr>
    <?php endforeach; ?>

    <?php if (empty($stocks)):
      $colspan = 7;
      if (($_GET['filter'] ?? '') === 'low_stock') {
        $emptyMessage = 'Belum ada barang dengan stok rendah.';
      } elseif (($_GET['filter'] ?? '') === 'borrowed') {
        $emptyMessage = 'Belum ada barang yang sedang dipinjam.';
      } elseif (($_GET['filter'] ?? '') === 'in_transit') {
        $emptyMessage = 'Belum ada barang yang sedang dalam pengiriman.';
      } else {
        $emptyMessage = 'Tidak ada catatan stok yang sesuai dengan filter.';
      }
      require __DIR__ . '/../../components/table/empty_state.php';
    endif; ?>
    </tbody>
  </table>

  <?php $paginationBaseUrl = url('/stocks'); require __DIR__ . '/../../components/table/pagination.php'; ?>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
