<?php
$pageTitle = 'Pencarian Barang';
ob_start();
?>

<div class="mb-6 fade-in">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Pencarian Barang</h1>
    <p class="text-gray-600 dark:text-gray-400">Temukan barang secara spesifik menggunakan berbagai filter dan kata kunci.</p>
</div>

<!-- Panel Panduan -->
<div class="bg-blue-50 dark:bg-blue-900/30 border-l-4 border-blue-500 p-4 rounded-md text-sm text-blue-700 dark:text-blue-300 mb-6">
    <p class="font-bold mb-1"><i class="fas fa-info-circle mr-1"></i> Panduan Pencarian Barang</p>
    <ul class="list-disc pl-5 space-y-1">
        <li><strong>Fungsi Search:</strong> Mencari berdasar nama, kode, SN, barcode, QR, nama gudang, pemegang, hingga nomor referensi transaksi.</li>
        <li><strong>Tipe Barang:</strong> 
            <ul class="list-circle pl-5">
                <li><span class="font-semibold text-purple-600 dark:text-purple-400">SERIALIZED</span>: Barang yang dilacak satuan (punya Nomor Seri/SN unik).</li>
                <li><span class="font-semibold text-blue-600 dark:text-blue-400">BULK</span>: Barang curah yang dilacak berdasarkan total kuantitas per gudang.</li>
            </ul>
        </li>
        <li><strong>Stok:</strong> Physical (fisik), Reserved (dipesan), Allocated (dialokasikan untuk instalasi), Available (tersedia digunakan).</li>
        <li>Klik tombol <strong>Lihat</strong> pada baris barang untuk membuka halaman <strong>Inventory 360</strong> yang menampilkan data riwayat lengkap, foto, dan mutasi.</li>
    </ul>
</div>

<!-- Scan Barcode -->
<div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6 p-4 border border-gray-200 dark:border-gray-700">
    <div class="flex items-center justify-between mb-3">
        <div class="flex items-center gap-2">
            <i class="fas fa-barcode text-blue-600 dark:text-blue-400 text-lg"></i>
            <h2 class="text-sm font-bold text-gray-800 dark:text-white uppercase tracking-wide">Scan Barcode</h2>
        </div>
        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-green-600 dark:text-green-400">
            <span class="relative flex h-2.5 w-2.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span>
            </span>
            Scanner siap
        </span>
    </div>
    <form id="scanForm" method="GET" action="<?= url('/search') ?>" class="flex flex-wrap gap-3 items-center">
        <div class="relative flex-grow min-w-[240px]">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-qrcode text-gray-400"></i>
            </div>
            <input type="text" id="scanInput" name="barcode" value="<?= htmlspecialchars($barcode) ?>" placeholder="Scan / fokus barcode..." autocomplete="off" class="w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow">
        </div>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md shadow transition-colors text-sm">
            <i class="fas fa-barcode mr-1"></i> Cari
        </button>
    </form>
    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Gunakan scanner barcode. Setelah barcode dipindai (biasanya diakhiri ENTER), hasil akan dicari otomatis tanpa klik tombol Cari. Barcode = <strong>Kode Barang</strong> atau <strong>Nomor Seri</strong>.</p>
</div>

<!-- Search Form -->
<div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6 p-4 border border-gray-200 dark:border-gray-700">
    <form method="GET" action="<?= url('/search') ?>" class="flex flex-wrap gap-4 items-end">
        <div class="flex-grow w-full md:w-auto">
            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Kata Kunci (Cari...)</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Nama, Kode, SN, Transaksi..." class="w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow" autocomplete="off">
            </div>
        </div>
        
        <div>
            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Tipe</label>
            <select name="tipe" class="w-32 border border-gray-300 dark:border-gray-600 rounded-md py-2 px-3 text-sm text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">Semua Tipe</option>
                <option value="BULK" <?= $type === 'BULK' ? 'selected' : '' ?>>BULK</option>
                <option value="SERIALIZED" <?= $type === 'SERIALIZED' ? 'selected' : '' ?>>SERIALIZED</option>
            </select>
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Kategori</label>
            <select name="kategori" class="w-40 border border-gray-300 dark:border-gray-600 rounded-md py-2 px-3 text-sm text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">Semua Kategori</option>
                <?php foreach($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $category_id == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Gudang</label>
            <select name="gudang" class="w-40 border border-gray-300 dark:border-gray-600 rounded-md py-2 px-3 text-sm text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">Semua Gudang</option>
                <?php foreach($warehouses as $wh): ?>
                    <option value="<?= $wh['id'] ?>" <?= $warehouse_id == $wh['id'] ? 'selected' : '' ?>><?= htmlspecialchars($wh['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Status</label>
            <select name="status" class="w-36 border border-gray-300 dark:border-gray-600 rounded-md py-2 px-3 text-sm text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">Semua Status</option>
                <option value="AVAILABLE" <?= $status === 'AVAILABLE' ? 'selected' : '' ?>>Tersedia</option>
                <option value="BORROWED" <?= $status === 'BORROWED' ? 'selected' : '' ?>>Dipinjam</option>
                <option value="INSTALLED" <?= $status === 'INSTALLED' ? 'selected' : '' ?>>Terpasang</option>
                <option value="IN_TRANSIT" <?= $status === 'IN_TRANSIT' ? 'selected' : '' ?>>Dalam Perjalanan</option>
                <option value="REPAIR" <?= $status === 'REPAIR' ? 'selected' : '' ?>>Perbaikan</option>
                <option value="BROKEN" <?= $status === 'BROKEN' ? 'selected' : '' ?>>Rusak</option>
            </select>
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Kondisi</label>
            <select name="kondisi" class="w-36 border border-gray-300 dark:border-gray-600 rounded-md py-2 px-3 text-sm text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">Semua Kondisi</option>
                <option value="BAIK" <?= $condition === 'BAIK' ? 'selected' : '' ?>>BAIK</option>
                <option value="RUSAK_RINGAN" <?= $condition === 'RUSAK_RINGAN' ? 'selected' : '' ?>>RUSAK_RINGAN</option>
                <option value="RUSAK_BERAT" <?= $condition === 'RUSAK_BERAT' ? 'selected' : '' ?>>RUSAK_BERAT</option>
                <option value="PERLU_PERBAIKAN" <?= $condition === 'PERLU_PERBAIKAN' ? 'selected' : '' ?>>PERLU_PERBAIKAN</option>
                <option value="TIDAK_LENGKAP" <?= $condition === 'TIDAK_LENGKAP' ? 'selected' : '' ?>>TIDAK_LENGKAP</option>
                <option value="HILANG" <?= $condition === 'HILANG' ? 'selected' : '' ?>>HILANG</option>
            </select>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md shadow transition-colors text-sm">
                Cari
            </button>
            <a href="<?= url('/search') ?>" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-bold py-2 px-4 rounded-md shadow transition-colors text-sm">
                Reset
            </a>
        </div>
    </form>
</div>

<!-- Results -->
<div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
    <!-- Live Search loading indicator -->
    <div id="searchLoading" class="hidden p-8 text-center text-gray-500 dark:text-gray-400">
        <i class="fas fa-spinner fa-spin text-2xl"></i>
        <p class="mt-2 text-sm">Mencari...</p>
    </div>
    <!-- Live Search error state -->
    <div id="searchError" class="hidden p-8 text-center">
        <i class="fas fa-exclamation-triangle text-2xl text-red-500"></i>
        <p class="mt-2 text-sm text-red-600 dark:text-red-400">Terjadi kesalahan saat mencari. Coba lagi.</p>
    </div>

    <div id="searchResults">
    <?php if (empty($results)): ?>
        <?php if ($barcode !== ''): ?>
            <!-- Hasil scan barcode tidak ditemukan -->
            <div class="py-16 px-4 text-center">
                <i class="fas fa-barcode text-4xl text-gray-300 dark:text-gray-600"></i>
                <h3 class="mt-3 text-lg font-semibold text-gray-700 dark:text-gray-300">Barcode tidak ditemukan.</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tidak ada barang/serial dengan barcode &quot;<?= htmlspecialchars($barcode) ?>&quot;.</p>
            </div>
            <div class="text-center -mt-4 mb-4">
                <a href="<?= url('/search') ?>" class="inline-block text-blue-600 dark:text-blue-400 hover:underline text-sm font-medium">Reset</a>
            </div>
        <?php elseif ($q !== '' || $type !== '' || $category_id !== '' || $warehouse_id !== '' || $status !== '' || $condition !== ''): ?>
            <!-- Filter applied, but no results -->
            <?php
            $emptyPanel = true;
            $icon = 'fa-search';
            $emptyMessage = "Tidak ada barang yang cocok dengan pencarian.";
            $emptySubMessage = "Silakan ubah kata kunci atau hapus filter untuk melihat hasil.";
            require __DIR__ . '/../components/table/empty_state.php';
            unset($emptyPanel, $icon, $emptyMessage, $emptySubMessage);
            ?>
            <div class="text-center -mt-4 mb-4">
                <a href="<?= url('/search') ?>" class="inline-block text-blue-600 dark:text-blue-400 hover:underline text-sm font-medium">Reset Filter</a>
            </div>
        <?php else: ?>
            <!-- Default list empty (unlikely unless db is completely empty) -->
            <?php
            $emptyPanel = true;
            $icon = 'fa-box-open';
            $emptyMessage = "Belum ada barang di sistem.";
            $emptySubMessage = "Data barang akan tampil di sini setelah ditambahkan.";
            require __DIR__ . '/../components/table/empty_state.php';
            unset($emptyPanel, $icon, $emptyMessage, $emptySubMessage);
            ?>
        <?php endif; ?>
    <?php else: ?>
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Daftar Barang</h2>
            <span id="searchCount" class="bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300 text-xs font-semibold px-2.5 py-1 rounded-full border border-blue-200 dark:border-blue-700/50">
                <?= number_format($total) ?> Ditemukan
            </span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full min-w-[800px] lg:min-w-full table-sticky divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-12">No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tipe</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kode Barang</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama Barang</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">SN / SKU</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Lokasi</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pemegang</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status & Kondisi</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Stok</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <?php 
                    $no = ($offset ?? 0) + 1;
                    foreach ($results as $item): 
                    ?>
                    <tr class="hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 text-center"><?= $no++ ?></td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <?php if ($item['type'] === 'SERIALIZED'): ?>
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400 border border-purple-200 dark:border-purple-800">SERIALIZED</span>
                            <?php else: ?>
                                <?= \App\Helpers\UIHelper::badge('BULK', 'INFO') ?>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100"><?= htmlspecialchars($item['kode_barang']) ?></td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300 font-semibold"><?= htmlspecialchars($item['nama_barang']) ?></td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 font-mono"><?= htmlspecialchars($item['sn_sku']) ?></td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                            <?= $item['lokasi'] ? '<i class="fas fa-map-marker-alt text-gray-400 w-4"></i> ' . htmlspecialchars($item['lokasi']) : '-' ?>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                            <?= $item['pemegang'] !== '-' && $item['pemegang'] !== null ? '<i class="fas fa-user text-gray-400 w-4"></i> ' . htmlspecialchars($item['pemegang']) : '-' ?>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-xs">
                            <?php 
                            $st = $item['status'];
                            $badgeType = 'NEUTRAL';
                            if ($st === 'AVAILABLE') $badgeType = 'SUCCESS';
                            if ($st === 'BORROWED') $badgeType = 'WARNING';
                            if ($st === 'INSTALLED') $badgeType = 'INFO';
                            if ($st === 'IN_TRANSIT') $color = 'bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-300';
                            
                            $cond = $item['condition_status'];
                            $condColor = 'text-green-600 dark:text-green-400';
                            if ($cond === 'RUSAK_BERAT' || $cond === 'HILANG') $condColor = 'text-red-600 dark:text-red-400';
                            elseif ($cond !== 'BAIK') $condColor = 'text-yellow-600 dark:text-yellow-400';
                            ?>
                            <div class="mb-1"><?= \App\Helpers\UIHelper::badge($st, $badgeType) ?></div>
                            <div class="font-semibold <?= $condColor ?>"><i class="fas fa-heartbeat text-[10px]"></i> <?= htmlspecialchars(\App\Helpers\UIHelper::statusLabel($cond)) ?></div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-600 dark:text-gray-400 space-y-0.5">
                            <div class="font-bold text-sm text-gray-900 dark:text-gray-100"><?= number_format($item['physical_stock']) ?> Physical</div>
                            <div class="text-blue-600 dark:text-blue-400"><?= number_format($item['available']) ?> Available</div>
                            <div><?= number_format($item['reserved']) ?> Reserved</div>
                            <div><?= number_format($item['allocated']) ?> Allocated</div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                            <a href="<?= url('/items/show/' . $item['master_item_id'] . ($item['type'] === 'SERIALIZED' ? '?sn_id=' . $item['ref_id'] : '?warehouse_id=' . $item['ref_id'])) ?>" 
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-semibold text-blue-600 hover:text-white hover:bg-blue-600 dark:text-blue-400 dark:hover:bg-blue-500 dark:hover:text-white rounded-lg border border-blue-600 dark:border-blue-500 transition-colors">
                                <i class="fas fa-eye"></i> Lihat
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total > $limit): ?>
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <div class="text-sm text-gray-700 dark:text-gray-400">
                Menampilkan halaman <?= $page ?> dari <?= ceil($total / $limit) ?> (Total: <?= $total ?> data)
            </div>
            <div class="flex gap-1">
                <?php 
                    $queryParams = $_GET;
                    unset($queryParams['page']);
                    $queryString = http_build_query($queryParams);
                    $baseUrl = url('/search') . ($queryString ? '?' . $queryString . '&' : '?');
                    $totalPages = ceil($total / $limit);
                ?>
                <?php if ($page > 1): ?>
                    <a href="<?= $baseUrl . 'page=' . ($page - 1) ?>" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-white rounded border border-gray-300 dark:border-gray-600 text-sm">Prev</a>
                <?php endif; ?>
                
                <?php 
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    for ($i = $startPage; $i <= $endPage; $i++): 
                ?>
                    <a href="<?= $baseUrl . 'page=' . $i ?>" class="px-3 py-1 <?= $i === $page ? 'bg-blue-600 text-white border-blue-600' : 'bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-white border-gray-300 dark:border-gray-600' ?> rounded border text-sm"><?= $i ?></a>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="<?= $baseUrl . 'page=' . ($page + 1) ?>" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-white rounded border border-gray-300 dark:border-gray-600 text-sm">Next</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>
    </div><!-- /searchResults -->
</div>

<script>
(function () {
  const $ = (sel, root) => (root || document).querySelector(sel);
  const input = $('input[name="q"]');
  const form = $('form[action*="/search"]');
  const results = $('#searchResults');
  const loading = $('#searchLoading');
  const errorBox = $('#searchError');
  if (!input || !results) return;

  let debounceTimer = null;
  let inflight = 0;
  let latestKey = 0;

  function collectParams() {
    const params = new URLSearchParams();
    params.set('q', input.value.trim());
    ['tipe', 'kategori', 'gudang', 'status', 'kondisi'].forEach(name => {
      const el = form.querySelector('select[name="' + name + '"]');
      if (el && el.value) params.set(name, el.value);
    });
    params.set('page', '1');
    params.set('format', 'json');
    return params;
  }

  function statusBadge(status) {
    const map = { AVAILABLE: 'SUCCESS', BORROWED: 'WARNING', INSTALLED: 'INFO', IN_TRANSIT: 'ORANGE', REPAIR: 'WARNING', BROKEN: 'DANGER' };
    const colors = {
      SUCCESS: 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-400',
      WARNING: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-400',
      INFO: 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-400',
      ORANGE: 'bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-300',
      DANGER: 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-400',
      NEUTRAL: 'bg-gray-100 text-gray-700 dark:bg-gray-700/50 dark:text-gray-300'
    };
    const c = colors[map[status] || 'NEUTRAL'];
    return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold ' + c + '">' + (status || '-') + '</span>';
  }

  function condClass(cond) {
    if (cond === 'RUSAK_BERAT' || cond === 'HILANG') return 'text-red-600 dark:text-red-400';
    if (cond !== 'BAIK') return 'text-yellow-600 dark:text-yellow-400';
    return 'text-green-600 dark:text-green-400';
  }

  function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  }

  function render(data) {
    if (!data.results.length) {
      results.innerHTML = '<div class="py-16 px-4 text-center"><i class="fas fa-search text-4xl text-gray-300 dark:text-gray-600"></i><h3 class="mt-3 text-lg font-semibold text-gray-700 dark:text-gray-300">Tidak ada barang yang cocok dengan pencarian.</h3><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Silakan ubah kata kunci atau hapus filter untuk melihat hasil.</p></div>' +
        '<div class="text-center pb-6"><a href="' + esc(window.location.pathname) + '" class="inline-block text-blue-600 dark:text-blue-400 hover:underline text-sm font-medium">Reset Filter</a></div>';
      return;
    }
    const rows = data.results.map((r, i) => {
      const serialized = r.type === 'SERIALIZED';
      return '<tr class="hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-700/50 transition-colors">' +
        '<td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 text-center">' + (i + 1) + '</td>' +
        '<td class="px-4 py-3 whitespace-nowrap">' + (serialized
          ? '<span class="px-2 py-0.5 text-[10px] font-bold rounded bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400 border border-purple-200 dark:border-purple-800">SERIALIZED</span>'
          : '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-400">BULK</span>') + '</td>' +
        '<td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">' + esc(r.kode_barang) + '</td>' +
        '<td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300 font-semibold">' + esc(r.nama_barang) + '</td>' +
        '<td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 font-mono">' + esc(r.sn_sku) + '</td>' +
        '<td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">' + (r.lokasi ? '<i class="fas fa-map-marker-alt text-gray-400 w-4"></i> ' + esc(r.lokasi) : '-') + '</td>' +
        '<td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">' + (r.pemegang && r.pemegang !== '-' ? '<i class="fas fa-user text-gray-400 w-4"></i> ' + esc(r.pemegang) : '-') + '</td>' +
        '<td class="px-4 py-3 whitespace-nowrap text-xs"><div class="mb-1">' + statusBadge(r.status) + '</div><div class="font-semibold ' + condClass(r.condition_status) + '"><i class="fas fa-heartbeat text-[10px]"></i> ' + esc(r.condition_status) + '</div></td>' +
        '<td class="px-4 py-3 whitespace-nowrap text-xs text-gray-600 dark:text-gray-400 space-y-0.5"><div class="font-bold text-sm text-gray-900 dark:text-gray-100">' + esc(r.physical_stock) + ' Physical</div><div class="text-blue-600 dark:text-blue-400">' + esc(r.available) + ' Available</div><div>' + esc(r.reserved) + ' Reserved</div><div>' + esc(r.allocated) + ' Allocated</div></td>' +
        '<td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium"><a href="' + esc(r.aksi_url) + '" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-semibold text-blue-600 hover:text-white hover:bg-blue-600 dark:text-blue-400 dark:hover:bg-blue-500 dark:hover:text-white rounded-lg border border-blue-600 dark:border-blue-500 transition-colors"><i class="fas fa-eye"></i> Lihat</a></td>' +
        '</tr>';
    }).join('');
    results.innerHTML =
      '<div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center"><h2 class="text-lg font-semibold text-gray-800 dark:text-white">Daftar Barang</h2>' +
      '<span class="bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300 text-xs font-semibold px-2.5 py-1 rounded-full border border-blue-200 dark:border-blue-700/50">' + data.total.toLocaleString('id-ID') + ' Ditemukan</span></div>' +
      '<div class="overflow-x-auto"><table class="w-full min-w-[800px] lg:min-w-full table-sticky divide-y divide-gray-200 dark:divide-gray-700">' +
      '<thead class="bg-gray-50 dark:bg-gray-900/50"><tr>' +
      '<th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-12">No</th>' +
      '<th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tipe</th>' +
      '<th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kode Barang</th>' +
      '<th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama Barang</th>' +
      '<th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">SN / SKU</th>' +
      '<th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Lokasi</th>' +
      '<th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pemegang</th>' +
      '<th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status & Kondisi</th>' +
      '<th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Stok</th>' +
      '<th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>' +
      '</tr></thead><tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">' + rows + '</tbody></table></div>';
  }

  async function liveSearch() {
    const params = collectParams();
    const key = ++latestKey;
    loading.classList.remove('hidden');
    errorBox.classList.add('hidden');
    inflight++;
    try {
      const res = await fetch('/search?' + params.toString(), { headers: { 'Accept': 'application/json' } });
      if (!res.ok) throw new Error('HTTP ' + res.status);
      const data = await res.json();
      if (key !== latestKey) return; // stale response
      render(data);
      // keep URL in sync (without format)
      params.delete('format');
      history.replaceState(null, '', '/search?' + params.toString());
    } catch (e) {
      if (key === latestKey) {
        errorBox.classList.remove('hidden');
      }
    } finally {
      inflight--;
      if (inflight === 0) loading.classList.add('hidden');
    }
  }

  input.addEventListener('input', function () {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(liveSearch, 350);
  });
  form.querySelectorAll('select').forEach(sel => {
    sel.addEventListener('change', function () {
      clearTimeout(debounceTimer);
      liveSearch();
    });
  });

  // ---- Scan Barcode (Admin & Superadmin) ----
  // Scanner USB/Bluetooth berperilaku seperti keyboard: mengisi input lalu mengirim ENTER.
  // ENTER (atau submit form) memicu pencarian exact barcode TANPA klik tombol Cari manual.
  const scanInput = $('#scanInput');
  const scanForm = $('#scanForm');
  let scanLatest = 0;

  function renderScanNotFound(val) {
    results.innerHTML =
      '<div class="py-16 px-4 text-center">' +
      '<i class="fas fa-barcode text-4xl text-gray-300 dark:text-gray-600"></i>' +
      '<h3 class="mt-3 text-lg font-semibold text-gray-700 dark:text-gray-300">Barcode tidak ditemukan.</h3>' +
      '<p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tidak ada barang/serial dengan barcode &quot;' + esc(val) + '&quot;.</p>' +
      '</div>' +
      '<div class="text-center pb-6"><a href="' + esc(window.location.pathname) + '" class="inline-block text-blue-600 dark:text-blue-400 hover:underline text-sm font-medium">Reset</a></div>';
  }

  async function scanBarcode() {
    const val = scanInput.value.trim();
    if (!val) { scanInput.focus(); return; } // barcode kosong -> jangan request
    const key = ++scanLatest;
    const params = new URLSearchParams();
    params.set('barcode', val);
    params.set('format', 'json');
    loading.classList.remove('hidden');
    errorBox.classList.add('hidden');
    inflight++;
    try {
      const res = await fetch('/search?' + params.toString(), { headers: { 'Accept': 'application/json' } });
      if (!res.ok) throw new Error('HTTP ' + res.status);
      const data = await res.json();
      if (key !== scanLatest) return; // hasil scan lama diabaikan (scan cepat berurutan)
      scanInput.value = ''; // siapkan scan berikutnya
      scanInput.focus();
      if (data.results.length) {
        render(data);
      } else {
        renderScanNotFound(val);
      }
      params.delete('format');
      history.replaceState(null, '', '/search?' + params.toString());
    } catch (e) {
      if (key === scanLatest) {
        errorBox.classList.remove('hidden');
        scanInput.focus();
      }
    } finally {
      inflight--;
      if (inflight === 0) loading.classList.add('hidden');
    }
  }

  if (scanInput) {
    // Fokus otomatis ke input scan saat halaman dibuka -> langsung scan tanpa klik field.
    scanInput.focus();
    if (scanForm) {
      scanForm.addEventListener('submit', function (e) {
        e.preventDefault();
        scanBarcode();
      });
    }
    scanInput.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        scanBarcode();
      }
    });
  }
})();
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
