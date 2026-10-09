<?php
$pageTitle = 'Laporan Posisi Stok';
ob_start();
?>

<?php
$reportLogoUrl = url('/logo');
$reportCompany = \App\Helpers\SettingsHelper::get('company_name', 'PT. BIG');
$reportAppName = \App\Helpers\SettingsHelper::get('app_name', 'Sistem Gudang');
?>
<div class="report-print-header">
  <?php if ($reportLogoUrl): ?>
    <img class="report-print-logo" src="<?= htmlspecialchars($reportLogoUrl) ?>" alt="Logo Aplikasi">
  <?php endif; ?>
  <div>
    <div class="report-print-company"><?= htmlspecialchars($reportCompany) ?></div>
    <div class="report-print-system"><?= htmlspecialchars($reportAppName) ?></div>
    <div class="report-print-title"><?= htmlspecialchars($pageTitle) ?></div>
    <div style="font-size:10px;color:#374151;margin-top:3px;">
      Gudang: <?= $warehouseId > 0 ? htmlspecialchars(($warehouses[array_search($warehouseId, array_column($warehouses, 'id'))]['name'] ?? 'Dipilih')) : 'Semua Gudang' ?>
    </div>
    <div style="font-size:10px;color:#374151;margin-top:2px;">
      <?= $search !== '' ? 'Pencarian: ' . htmlspecialchars($search) . ' | ' : '' ?>Dicetak: <?= date('d/m/Y H:i') ?>
    </div>
  </div>
</div>

<div class="mb-6 flex flex-col gap-2 lg:flex-row lg:items-end lg:justify-between">
  <div>
    <div class="flex items-center gap-3">
      <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-300">
        <i class="fas fa-boxes-stacked text-lg"></i>
      </span>
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Laporan Posisi Stok</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Posisi inventory saat ini per gudang.</p>
      </div>
    </div>
  </div>
  <button type="button" onclick="window.print()" class="inline-flex items-center justify-center rounded-xl bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-900 dark:bg-gray-700 dark:hover:bg-gray-600">
    <i class="fas fa-print mr-2"></i> Cetak
  </button>
</div>

<form method="get" class="mb-6 rounded-2xl border border-gray-200 bg-white p-4 sm:p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800 print:hidden">
  <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
    <div>
      <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-gray-600 dark:text-gray-400">Pencarian</label>
      <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari barang atau gudang..." class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
    </div>
    <div>
      <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-gray-600 dark:text-gray-400">Gudang</label>
      <select name="warehouse_id" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
        <option value="0">Semua Gudang</option>
        <?php foreach ($warehouses as $wh): ?>
          <option value="<?= (int)$wh['id'] ?>" <?= $warehouseId === (int)$wh['id'] ? 'selected' : '' ?>><?= htmlspecialchars($wh['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="flex items-end gap-2">
      <button class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700"><i class="fas fa-search mr-2"></i>Cari</button>
      <a href="<?= url('/reports/stock-position') ?>" class="inline-flex items-center justify-center rounded-xl bg-gray-100 px-5 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"><i class="fas fa-rotate-left mr-2"></i>Atur Ulang</a>
    </div>
  </div>
</form>

<div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-5">
<?php
$cards = [
  ['label'=>'Stok Fisik','value'=>$totals['physical'],'tone'=>'green','icon'=>'fa-boxes-stacked','help'=>'Berada di gudang'],
  ['label'=>'Tersedia','value'=>$totals['available'],'tone'=>'blue','icon'=>'fa-circle-check','help'=>'Siap digunakan'],
  ['label'=>'Dipinjam','value'=>$totals['borrowed'],'tone'=>'amber','icon'=>'fa-hand-holding','help'=>'Dalam custody teknisi'],
  ['label'=>'Dalam Pengiriman','value'=>$totals['in_transit'],'tone'=>'indigo','icon'=>'fa-truck','help'=>'Belum diterima tujuan'],
  ['label'=>'Total Aset','value'=>$totals['asset'],'tone'=>'slate','icon'=>'fa-layer-group','help'=>'Fisik + dipinjam + dikirim'],
];
$tone = [
  'green'=>['wrap'=>'bg-green-50 dark:bg-green-900/20','icon'=>'bg-green-100 text-green-600 dark:bg-green-900/40 dark:text-green-300','value'=>'text-green-700 dark:text-green-300'],
  'blue'=>['wrap'=>'bg-blue-50 dark:bg-blue-900/20','icon'=>'bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300','value'=>'text-blue-700 dark:text-blue-300'],
  'amber'=>['wrap'=>'bg-amber-50 dark:bg-amber-900/20','icon'=>'bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-300','value'=>'text-amber-700 dark:text-amber-300'],
  'indigo'=>['wrap'=>'bg-indigo-50 dark:bg-indigo-900/20','icon'=>'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-300','value'=>'text-indigo-700 dark:text-indigo-300'],
  'slate'=>['wrap'=>'bg-slate-50 dark:bg-slate-900/30','icon'=>'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300','value'=>'text-slate-700 dark:text-slate-300'],
];
foreach($cards as $card):
  $t = $tone[$card['tone']];
?>
  <div class="rounded-2xl border border-gray-200 <?= $t['wrap'] ?> p-4 shadow-sm dark:border-gray-700">
    <div class="flex items-start justify-between gap-3">
      <div>
        <div class="text-[11px] font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400"><?= htmlspecialchars($card['label']) ?></div>
        <div class="mt-1 text-2xl font-black <?= $t['value'] ?>"><?= number_format((int)$card['value'],0,',','.') ?></div>
        <div class="mt-1 text-[11px] text-gray-500 dark:text-gray-400"><?= htmlspecialchars($card['help']) ?></div>
      </div>
      <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl <?= $t['icon'] ?>"><i class="fas <?= htmlspecialchars($card['icon']) ?>"></i></span>
    </div>
  </div>
<?php endforeach; ?>
</div>

<div class="overflow-x-auto rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
  <table class="min-w-[1100px] w-full divide-y divide-gray-200 dark:divide-gray-700">
    <thead class="bg-gray-50 dark:bg-gray-700">
      <tr>
        <th class="w-12 px-4 py-3 text-center text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">No</th>
        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Gudang</th>
        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Kode Barang</th>
        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Nama Barang</th>
        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Satuan</th>
        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Stok Fisik</th>
        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Tersedia</th>
        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Dipinjam</th>
        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Dalam Pengiriman</th>
        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Total Aset</th>
        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Status</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
      <?php if(empty($data)): ?>
        <tr><td colspan="11" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">Tidak ada stok yang sesuai dengan filter.</td></tr>
      <?php else: ?>
        <?php foreach($data as $i=>$row): ?>
          <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
            <td class="px-4 py-3 text-center text-sm text-gray-500 dark:text-gray-400"><?= $i+1 ?></td>
            <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($row['warehouse_name']) ?></td>
            <td class="px-4 py-3 font-mono text-xs text-gray-600 dark:text-gray-300"><?= htmlspecialchars($row['kode_barang']) ?></td>
            <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white"><?= htmlspecialchars($row['item_name']) ?></td>
            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300"><?= htmlspecialchars($row['unit_name'] ?: 'unit') ?></td>
            <td class="px-4 py-3 text-right text-sm font-bold text-green-700 dark:text-green-300"><?= number_format((int)$row['physical_quantity']) ?></td>
            <td class="px-4 py-3 text-right text-sm font-bold text-blue-700 dark:text-blue-300"><?= number_format((int)$row['available_quantity']) ?></td>
            <td class="px-4 py-3 text-right text-sm font-bold text-amber-700 dark:text-amber-300"><?= number_format((int)$row['borrowed_quantity']) ?></td>
            <td class="px-4 py-3 text-right text-sm font-bold text-indigo-700 dark:text-indigo-300"><?= number_format((int)$row['in_transit_quantity']) ?></td>
            <td class="px-4 py-3 text-right text-sm font-black text-gray-900 dark:text-white"><?= number_format((int)$row['total_asset_quantity']) ?></td>
            <td class="px-4 py-3 text-sm"><span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700 dark:bg-gray-700 dark:text-gray-200"><?= htmlspecialchars($row['stock_status']) ?></span></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<div class="mt-4 text-sm text-gray-500 dark:text-gray-400">
  Menampilkan <?= number_format(count($data),0,',','.') ?> baris stok saat ini.
</div>

<style>
.report-print-header {
  display: none;
}

@media print {
  .print\:hidden, nav, aside, header { display:none !important; }

  .report-print-header {
    display: flex !important;
    align-items: center;
    gap: 16px;
    border-bottom: 1px solid #9ca3af;
    padding-bottom: 12px;
    margin-bottom: 14px;
  }

  .report-print-header .report-print-logo {
    width: 64px;
    height: 48px;
    object-fit: contain;
    object-position: left center;
  }

  .report-print-header .report-print-company {
    font-size: 18px;
    font-weight: 700;
    color: #111827;
    line-height: 1.15;
  }

  .report-print-header .report-print-system {
    font-size: 10px;
    color: #4b5563;
    margin-top: 2px;
  }

  .report-print-header .report-print-title {
    font-size: 15px;
    font-weight: 700;
    color: #111827;
    margin-top: 8px;
  }

  body { background:#fff !important; }
  .dark body { background:#fff !important; color:#111827 !important; }
}
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>