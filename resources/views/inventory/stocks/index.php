<?php
$pageTitle = 'Stok Barang';
$title = 'Stok Barang';
ob_start();

$currentFilter = $_GET['filter'] ?? '';
$currentTracking = $_GET['tracking'] ?? '';
$currentWarehouseId = (int)($_GET['warehouse_id'] ?? 0);
$currentCategoryId = (int)($_GET['category_id'] ?? 0);
$currentSearch = trim($_GET['search'] ?? '');
?>

<div class="space-y-6">

  <!-- Header Page & Action Button -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
      <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Stok Barang</h1>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
        Monitoring posisi fisik, ketersediaan, peminjaman, dan transfer stok antar gudang.
      </p>
    </div>
    <div class="flex items-center gap-2">
      <a href="<?= url('/reports/stock-position') ?>" class="inline-flex items-center justify-center gap-2 bg-white hover:bg-slate-50 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/90 dark:border-slate-700 font-semibold py-2 px-4 rounded-xl shadow-sm text-xs transition-all">
        <i class="fas fa-chart-pie text-blue-500 text-xs"></i>
        <span>Laporan Posisi Stok</span>
      </a>
      <a href="<?= url('/serials') ?>" class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-xl shadow-sm text-xs transition-all">
        <i class="fas fa-barcode text-xs"></i>
        <span>Nomor Seri</span>
      </a>
    </div>
  </div>

  <!-- KPI Summary Cards (Disafa SaaS Style) -->
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-800 p-4 sm:p-5 flex items-center">
      <div class="p-3 rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-300 shrink-0">
        <i class="fas fa-boxes-stacked text-base sm:text-lg"></i>
      </div>
      <div class="ml-3 sm:ml-4 min-w-0">
        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider truncate">Total Baris</p>
        <p class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-slate-100"><?= number_format($stats['total_items']) ?></p>
        <p class="text-[10px] text-slate-400 truncate mt-0.5">Katalog stok aktif</p>
      </div>
    </div>

    <a href="<?= url('/stocks?filter=low_stock' . ($currentWarehouseId > 0 ? '&warehouse_id=' . $currentWarehouseId : '')) ?>" class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border <?= $stats['low_stock'] > 0 ? 'border-amber-300 dark:border-amber-700/60 ring-2 ring-amber-100 dark:ring-amber-950/30' : 'border-slate-200/80 dark:border-slate-800' ?> p-4 sm:p-5 flex items-center hover:shadow-md transition-all group">
      <div class="p-3 rounded-xl <?= $stats['low_stock'] > 0 ? 'bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-300' : 'bg-slate-50 text-slate-400 dark:bg-slate-800' ?> shrink-0">
        <i class="fas fa-triangle-exclamation text-base sm:text-lg"></i>
      </div>
      <div class="ml-3 sm:ml-4 min-w-0">
        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider truncate">Stok Rendah</p>
        <p class="text-xl sm:text-2xl font-extrabold <?= $stats['low_stock'] > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-900 dark:text-slate-100' ?>"><?= number_format($stats['low_stock']) ?></p>
        <p class="text-[10px] text-slate-400 truncate mt-0.5">Perlu pengadaan</p>
      </div>
    </a>

    <a href="<?= url('/stocks?filter=borrowed' . ($currentWarehouseId > 0 ? '&warehouse_id=' . $currentWarehouseId : '')) ?>" class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-800 p-4 sm:p-5 flex items-center hover:shadow-md transition-all group">
      <div class="p-3 rounded-xl bg-orange-50 text-orange-600 dark:bg-orange-950/40 dark:text-orange-300 shrink-0">
        <i class="fas fa-hand-holding text-base sm:text-lg"></i>
      </div>
      <div class="ml-3 sm:ml-4 min-w-0">
        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider truncate">Sedang Dipinjam</p>
        <p class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-slate-100"><?= number_format($stats['active_loans']) ?></p>
        <p class="text-[10px] text-slate-400 truncate mt-0.5">Dalam custody teknisi</p>
      </div>
    </a>

    <a href="<?= url('/stocks?filter=in_transit' . ($currentWarehouseId > 0 ? '&warehouse_id=' . $currentWarehouseId : '')) ?>" class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-800 p-4 sm:p-5 flex items-center hover:shadow-md transition-all group">
      <div class="p-3 rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-300 shrink-0">
        <i class="fas fa-truck text-base sm:text-lg"></i>
      </div>
      <div class="ml-3 sm:ml-4 min-w-0">
        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider truncate">Dalam Transfer</p>
        <p class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-slate-100"><?= number_format($stats['in_transit']) ?></p>
        <p class="text-[10px] text-slate-400 truncate mt-0.5">Sedang dikirim</p>
      </div>
    </a>
  </div>

  <!-- Segmented Quick Filter Pills -->
  <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none text-xs">
    <a href="<?= url('/stocks' . ($currentWarehouseId > 0 ? '?warehouse_id=' . $currentWarehouseId : '')) ?>" class="px-3.5 py-1.5 rounded-xl font-semibold whitespace-nowrap transition-all <?= empty($currentFilter) && empty($currentTracking) ? 'bg-blue-600 text-white shadow-sm' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200/80 dark:border-slate-700' ?>">
      Semua Stok
    </a>
    <a href="<?= url('/stocks?filter=low_stock' . ($currentWarehouseId > 0 ? '&warehouse_id=' . $currentWarehouseId : '')) ?>" class="px-3.5 py-1.5 rounded-xl font-semibold whitespace-nowrap transition-all flex items-center gap-1.5 <?= $currentFilter === 'low_stock' ? 'bg-amber-600 text-white shadow-sm' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200/80 dark:border-slate-700' ?>">
      <i class="fas fa-triangle-exclamation text-[10px]"></i>
      <span>Stok Rendah</span>
      <?php if ($stats['low_stock'] > 0): ?>
        <span class="inline-flex items-center justify-center px-1.5 py-0.2 rounded-full text-[10px] <?= $currentFilter === 'low_stock' ? 'bg-amber-700 text-white' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300' ?>"><?= $stats['low_stock'] ?></span>
      <?php endif; ?>
    </a>
    <a href="<?= url('/stocks?filter=borrowed' . ($currentWarehouseId > 0 ? '&warehouse_id=' . $currentWarehouseId : '')) ?>" class="px-3.5 py-1.5 rounded-xl font-semibold whitespace-nowrap transition-all flex items-center gap-1.5 <?= $currentFilter === 'borrowed' ? 'bg-orange-600 text-white shadow-sm' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200/80 dark:border-slate-700' ?>">
      <i class="fas fa-hand-holding text-[10px]"></i>
      <span>Sedang Dipinjam</span>
    </a>
    <a href="<?= url('/stocks?filter=in_transit' . ($currentWarehouseId > 0 ? '&warehouse_id=' . $currentWarehouseId : '')) ?>" class="px-3.5 py-1.5 rounded-xl font-semibold whitespace-nowrap transition-all flex items-center gap-1.5 <?= $currentFilter === 'in_transit' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200/80 dark:border-slate-700' ?>">
      <i class="fas fa-truck text-[10px]"></i>
      <span>Dalam Pengiriman</span>
    </a>
    <a href="<?= url('/stocks?tracking=serialized' . ($currentWarehouseId > 0 ? '&warehouse_id=' . $currentWarehouseId : '')) ?>" class="px-3.5 py-1.5 rounded-xl font-semibold whitespace-nowrap transition-all flex items-center gap-1.5 <?= $currentTracking === 'serialized' ? 'bg-purple-600 text-white shadow-sm' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200/80 dark:border-slate-700' ?>">
      <i class="fas fa-barcode text-[10px]"></i>
      <span>Pakai Serial</span>
    </a>
    <a href="<?= url('/stocks?tracking=non_serialized' . ($currentWarehouseId > 0 ? '&warehouse_id=' . $currentWarehouseId : '')) ?>" class="px-3.5 py-1.5 rounded-xl font-semibold whitespace-nowrap transition-all flex items-center gap-1.5 <?= $currentTracking === 'non_serialized' ? 'bg-slate-700 text-white shadow-sm' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200/80 dark:border-slate-700' ?>">
      <i class="fas fa-cubes text-[10px]"></i>
      <span>Non-Serial (Bulk)</span>
    </a>
  </div>

  <!-- Detailed Filter & Search Toolbar (Disafa Style) -->
  <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-800 p-4 sm:p-5">
    <form method="GET" action="<?= url('/stocks') ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 items-end">
      <?php if (!empty($currentFilter)): ?>
        <input type="hidden" name="filter" value="<?= htmlspecialchars($currentFilter) ?>">
      <?php endif; ?>

      <!-- Search Input -->
      <div>
        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1.5">Pencarian</label>
        <div class="relative">
          <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
            <i class="fas fa-search text-xs"></i>
          </span>
          <input type="text" name="search" value="<?= htmlspecialchars($currentSearch) ?>" placeholder="Cari nama atau kode barang..." class="w-full pl-9 pr-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
        </div>
      </div>

      <!-- Warehouse Select -->
      <div>
        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1.5">Gudang</label>
        <div class="relative">
          <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
            <i class="fas fa-warehouse text-xs"></i>
          </span>
          <select name="warehouse_id" class="w-full pl-9 pr-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
            <option value="0">Semua Gudang</option>
            <?php foreach ($warehouses as $wh): ?>
              <option value="<?= (int)$wh['id'] ?>" <?= $currentWarehouseId === (int)$wh['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($wh['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <!-- Category Select -->
      <div>
        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1.5">Kategori</label>
        <div class="relative">
          <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
            <i class="fas fa-tags text-xs"></i>
          </span>
          <select name="category_id" class="w-full pl-9 pr-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
            <option value="0">Semua Kategori</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= (int)$cat['id'] ?>" <?= $currentCategoryId === (int)$cat['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <!-- Submit & Reset Buttons -->
      <div class="flex items-center gap-2">
        <button type="submit" class="flex-1 inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-xl shadow-sm text-sm transition-all">
          <i class="fas fa-search text-xs"></i>
          <span>Terapkan</span>
        </button>
        <a href="<?= url('/stocks') ?>" class="inline-flex items-center justify-center gap-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold py-2 px-3.5 rounded-xl text-sm transition-all" title="Reset Filter">
          <i class="fas fa-rotate-left text-xs"></i>
          <span>Reset</span>
        </a>
      </div>
    </form>
  </div>

  <!-- Data Table Container (Clean Modern SaaS Table) -->
  <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-800 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full min-w-[760px] lg:min-w-full divide-y divide-slate-200/80 dark:divide-slate-800">
        <thead class="bg-slate-50 dark:bg-slate-800/60">
          <tr>
            <th class="w-12 px-4 py-3.5 text-center text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">No</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Gudang</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Barang</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Pelacakan</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 min-w-[280px]">Ketersediaan Stok</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Status</th>
            <th class="w-28 px-4 py-3.5 text-center text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Aksi</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
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
          <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
            <!-- No -->
            <td class="px-4 py-3.5 text-center text-xs font-semibold text-slate-400">
              <?= $no++ ?>
            </td>

            <!-- Gudang & Kategori -->
            <td class="px-4 py-3.5 align-top">
              <div class="flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xs shrink-0">
                  <i class="fas fa-warehouse text-[11px]"></i>
                </span>
                <div class="min-w-0">
                  <p class="text-sm font-bold text-slate-900 dark:text-slate-100 truncate"><?= htmlspecialchars($stock['warehouse_name']) ?></p>
                  <p class="text-[11px] text-slate-400 dark:text-slate-500 truncate"><?= htmlspecialchars($stock['category_name'] ?? 'Tanpa kategori') ?></p>
                </div>
              </div>
            </td>

            <!-- Barang -->
            <td class="px-4 py-3.5 align-top">
              <div class="min-w-0">
                <p class="text-sm font-bold text-slate-900 dark:text-slate-100 break-words"><?= htmlspecialchars($stock['item_name']) ?></p>
                <div class="flex items-center gap-2 mt-0.5 text-[11px] text-slate-400 dark:text-slate-500">
                  <span>Satuan: <strong class="text-slate-600 dark:text-slate-300"><?= htmlspecialchars($unit) ?></strong></span>
                  <?php if ((int)$stock['stok_minimum'] > 0): ?>
                    <span>· Min: <?= number_format((int)$stock['stok_minimum']) ?></span>
                  <?php endif; ?>
                </div>
              </div>
            </td>

            <!-- Pelacakan -->
            <td class="px-4 py-3.5 align-top">
              <?php if ((int)$stock['is_serialized'] === 1): ?>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300 border border-purple-200/60 dark:border-purple-800">
                  <i class="fas fa-barcode text-[9px]"></i> Serial
                </span>
              <?php else: ?>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                  <i class="fas fa-cubes text-[9px]"></i> Non-Serial
                </span>
              <?php endif; ?>
            </td>

            <!-- Rincian Ketersediaan Stok (Modern Card Breakdown) -->
            <td class="px-4 py-3.5 align-top">
              <div class="space-y-2">
                <!-- Available Counter & Progress -->
                <div class="flex items-baseline justify-between gap-3">
                  <div class="flex items-baseline gap-1.5">
                    <span class="text-lg font-black text-slate-900 dark:text-slate-100"><?= number_format($available) ?></span>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400"><?= htmlspecialchars($unit) ?> tersedia</span>
                  </div>
                  <span class="text-[11px] font-extrabold text-blue-600 dark:text-blue-400"><?= $availablePercent ?>%</span>
                </div>

                <!-- Progress Bar -->
                <div class="h-1.5 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                  <div class="h-full rounded-full <?= $availablePercent > 50 ? 'bg-emerald-500' : ($availablePercent > 20 ? 'bg-blue-600' : 'bg-amber-500') ?> transition-all duration-300" style="width: <?= $availablePercent ?>%"></div>
                </div>

                <!-- Metric Chips (Fisik / Dipinjam / Dikirim) -->
                <div class="flex flex-wrap items-center gap-1.5 text-[11px]">
                  <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-50 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700" title="Stok fisik di gudang">
                    <span class="text-slate-400">Fisik:</span> <strong class="font-bold text-slate-800 dark:text-slate-200"><?= number_format($physical) ?></strong>
                  </span>
                  <?php if ($borrowed > 0): ?>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-orange-50 text-orange-700 dark:bg-orange-950/40 dark:text-orange-300 border border-orange-200/60 dark:border-orange-800" title="Sedang dipinjam teknisi">
                      <span class="text-orange-400">Dipinjam:</span> <strong class="font-bold"><?= number_format($borrowed) ?></strong>
                    </span>
                  <?php endif; ?>
                  <?php if ($inTransit > 0): ?>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-800" title="Dalam proses transfer antar gudang">
                      <span class="text-indigo-400">Transfer:</span> <strong class="font-bold"><?= number_format($inTransit) ?></strong>
                    </span>
                  <?php endif; ?>
                </div>
              </div>
            </td>

            <!-- Kondisi / Status Badge -->
            <td class="px-4 py-3.5 align-top">
              <div class="flex flex-col gap-1 w-fit">
                <?php if (!$stock['is_active']): ?>
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-300 border border-red-200/60 dark:border-red-800">
                    Nonaktif
                  </span>
                <?php else: ?>
                  <?php if ($isLow): ?>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800">
                      <i class="fas fa-triangle-exclamation text-[9px]"></i> Stok Rendah
                    </span>
                  <?php elseif ($available > 0): ?>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800">
                      <i class="fas fa-circle-check text-[9px]"></i> Tersedia
                    </span>
                  <?php else: ?>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                      Habis
                    </span>
                  <?php endif; ?>
                <?php endif; ?>
              </div>
            </td>

            <!-- Aksi -->
            <?php
            $rowActions = [[
              'type' => 'view',
              'url' => url('/items/show/' . (int)$stock['master_item_id'] . '?warehouse_id=' . (int)$stock['warehouse_id']),
              'label' => 'Detail 360',
              'icon' => 'fa-eye',
              'title' => 'Detail Inventory 360'
            ], [
              'type' => 'link',
              'url' => url('/items/incoming-evidence/' . (int)$stock['master_item_id'] . '?warehouse_id=' . (int)$stock['warehouse_id']),
              'label' => 'Foto Barang Masuk',
              'icon' => 'fa-camera'
            ]];
            require __DIR__ . '/../../components/table/row_actions.php';
            ?>
          </tr>
        <?php endforeach; ?>

        <?php if (empty($stocks)):
          $colspan = 7;
          if ($currentFilter === 'low_stock') {
            $emptyMessage = 'Tidak ada barang dengan stok rendah.';
            $emptySubMessage = 'Semua stok berada di atas batas minimum.';
          } elseif ($currentFilter === 'borrowed') {
            $emptyMessage = 'Tidak ada barang yang sedang dipinjam.';
            $emptySubMessage = 'Semua barang sedang berada di dalam gudang.';
          } elseif ($currentFilter === 'in_transit') {
            $emptyMessage = 'Tidak ada transfer barang yang sedang berjalan.';
            $emptySubMessage = 'Tidak ada pengiriman antar gudang yang aktif.';
          } else {
            $emptyMessage = 'Tidak ada data stok yang sesuai dengan filter.';
            $emptySubMessage = 'Coba atur ulang filter atau kata kunci pencarian Anda.';
          }
          require __DIR__ . '/../../components/table/empty_state.php';
        endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php
    $paginationBaseUrl = url('/stocks');
    require __DIR__ . '/../../components/table/pagination.php';
    ?>
  </div>

</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
