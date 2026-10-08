<?php
$pageTitle = 'Cetak Label Massal';
ob_start();
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$search = trim($_GET['search'] ?? '');
$categoryId = (int)($_GET['category_id'] ?? 0);
$warehouseId = (int)($_GET['warehouse_id'] ?? 0);
$status = trim($_GET['status'] ?? '');
?>
<div class="space-y-6 no-print">
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div class="flex items-center gap-3">
      <a href="<?= url('/serials') ?>" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
        <i class="fas fa-arrow-left"></i>
      </a>
      <div>
        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Cetak Label Massal</h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Pilih nomor seri dan/atau item non-serialized → satu lembar cetak A4.</p>
      </div>
    </div>
  </div>

  <?php if (isset($_SESSION['error'])): ?>
    <div class="bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 p-4 rounded-md mb-6 shadow-sm">
      <div class="flex items-center">
        <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
        <p class="text-sm text-red-700 dark:text-red-400 font-medium"><?= htmlspecialchars($_SESSION['error']) ?></p>
      </div>
    </div>
    <?php unset($_SESSION['error']); ?>
  <?php endif; ?>

  <!-- Filter -->
  <form method="GET" action="<?= url('/labels/create') ?>" class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 mb-6 border border-gray-200 dark:border-gray-700">
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
      <div>
        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Pencarian</label>
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Kode barang, nama barang, atau serial..." class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div>
        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Kategori</label>
        <select name="category_id" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md py-2 px-3 text-sm">
          <option value="">Semua Kategori</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= (int)$cat['id'] ?>" <?= $categoryId === (int)$cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Gudang</label>
        <select name="warehouse_id" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md py-2 px-3 text-sm">
          <option value="">Semua Gudang</option>
          <?php foreach ($warehouses as $wh): ?>
            <option value="<?= (int)$wh['id'] ?>" <?= $warehouseId === (int)$wh['id'] ? 'selected' : '' ?>><?= htmlspecialchars($wh['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Status SN</label>
        <select name="status" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md py-2 px-3 text-sm">
          <option value="">Semua Status</option>
          <?php foreach (['AVAILABLE', 'INSTALLED', 'IN_USE', 'IN_TRANSIT', 'BROKEN', 'REPAIR', 'LOST'] as $st): ?>
            <option value="<?= $st ?>" <?= $status === $st ? 'selected' : '' ?>><?= htmlspecialchars(\App\Helpers\UIHelper::statusLabel($st)) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="flex gap-2">
        <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white font-bold py-2 px-4 rounded shadow transition-colors text-sm">
          <i class="fas fa-filter mr-1"></i> Filter
        </button>
        <a href="<?= url('/labels/create') ?>" class="bg-gray-200 hover:bg-gray-300 text-gray-800 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200 font-bold py-2 px-4 rounded shadow transition-colors text-sm">
          <i class="fas fa-undo mr-1"></i> Reset
        </a>
      </div>
    </div>
  </form>

  <form method="POST" action="<?= url('/labels/print') ?>" id="printForm">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

    <!-- Serialized SN -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700 mb-6">
      <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex flex-wrap items-center justify-between gap-2">
        <div>
          <h2 class="font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
            <i class="fas fa-barcode text-blue-500"></i> Nomor Seri (1 SN = 1 Label)
          </h2>
          <p class="text-xs text-gray-500 dark:text-gray-400"><?= count($serials) ?> SN ditemukan (maks 500 tampil)</p>
        </div>
        <div class="flex gap-2">
          <button type="button" onclick="toggleAllSerials(true)" class="text-xs bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 font-semibold px-3 py-1.5 rounded border border-blue-200 dark:border-blue-800">Pilih Semua Tampil</button>
          <button type="button" onclick="toggleAllSerials(false)" class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold px-3 py-1.5 rounded border border-gray-200 dark:border-gray-600">Bersihkan</button>
        </div>
      </div>
      <?php if (empty($serials)): ?>
        <div class="p-6">
          <?php
          $emptyPanel = true;
          $emptyMessage = 'Tidak ada nomor seri yang cocok';
          $emptySubMessage = 'Ubah filter atau gunakan kata kunci lain.';
          $icon = 'fa-barcode';
          require __DIR__ . '/../components/table/empty_state.php';
          unset($emptyPanel, $emptyMessage, $emptySubMessage, $icon);
          ?>
        </div>
      <?php else: ?>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th class="px-4 py-3 w-10"><input type="checkbox" id="serialSelectAll" class="rounded"></th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Serial</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kode</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama Barang</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Gudang</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
              <?php foreach ($serials as $sn): ?>
                <tr>
                  <td class="px-4 py-3 whitespace-nowrap text-center">
                    <input type="checkbox" name="serial_ids[]" value="<?= (int)$sn['id'] ?>" class="serial-cb rounded" data-serial="<?= htmlspecialchars($sn['serial_number']) ?>">
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm font-mono text-gray-900 dark:text-gray-100"><?= htmlspecialchars($sn['serial_number']) ?></td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars($sn['kode_barang']) ?></td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300"><?= htmlspecialchars($sn['nama']) ?></td>
                  <td class="px-4 py-3 whitespace-nowrap text-center">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300"><?= htmlspecialchars(\App\Helpers\UIHelper::statusLabel($sn['status'])) ?></span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars($sn['warehouse'] ?: '-') ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <!-- Non-serialized bulk -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700 mb-6">
      <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
        <h2 class="font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
          <i class="fas fa-boxes text-green-500"></i> Item Non-Serialized (pilih jumlah label)
        </h2>
        <p class="text-xs text-gray-500 dark:text-gray-400"><?= count($bulkItems) ?> item ditemukan (maks 200 tampil)</p>
      </div>
      <?php if (empty($bulkItems)): ?>
        <div class="p-6">
          <?php
          $emptyPanel = true;
          $emptyMessage = 'Tidak ada item non-serialized yang cocok';
          $emptySubMessage = 'Ubah filter atau gunakan kata kunci lain.';
          $icon = 'fa-boxes';
          require __DIR__ . '/../components/table/empty_state.php';
          unset($emptyPanel, $emptyMessage, $emptySubMessage, $icon);
          ?>
        </div>
      <?php else: ?>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th class="px-4 py-3 w-10"><input type="checkbox" id="bulkSelectAll" class="rounded"></th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kode</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama Barang</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kategori</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jumlah Label</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
              <?php foreach ($bulkItems as $bi): ?>
                <tr>
                  <td class="px-4 py-3 whitespace-nowrap text-center">
                    <input type="checkbox" class="bulk-cb rounded" data-kode="<?= htmlspecialchars($bi['kode_barang']) ?>">
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm font-mono text-gray-900 dark:text-gray-100"><?= htmlspecialchars($bi['kode_barang']) ?></td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300"><?= htmlspecialchars($bi['nama']) ?></td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars($bi['category'] ?: '-') ?></td>
                  <td class="px-4 py-3 whitespace-nowrap text-center">
                    <input type="hidden" name="bulk[]" class="bulk-hidden" value="">
                    <input type="number" min="1" max="100" value="1" class="bulk-qty w-20 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md py-1 px-2 text-sm text-center" disabled>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <!-- Sticky action bar -->
    <div class="sticky bottom-4 bg-white dark:bg-gray-800 shadow-lg rounded-lg border border-gray-200 dark:border-gray-700 px-4 py-3 flex flex-wrap items-center justify-between gap-3 z-10">
      <div class="text-sm text-gray-700 dark:text-gray-300">
        <span class="font-bold text-blue-600 dark:text-blue-400" id="selectedCount">0</span> label akan dicetak
      </div>
      <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-lg shadow transition-colors text-sm flex items-center gap-2">
        <i class="fas fa-print"></i> Cetak Label
      </button>
    </div>
  </form>
</div>

<script>
(function () {
  function recount() {
    var serialCount = document.querySelectorAll('.serial-cb:checked').length;
    var bulkTotal = 0;
    document.querySelectorAll('.bulk-cb').forEach(function (cb) {
      if (cb.checked) {
        var qty = parseInt(cb.closest('tr').querySelector('.bulk-qty').value, 10) || 1;
        bulkTotal += qty;
      }
    });
    var total = serialCount + bulkTotal;
    document.getElementById('selectedCount').textContent = total;
    return total;
  }
  function syncBulk(row, checked) {
    var qtyInput = row.querySelector('.bulk-qty');
    var hidden = row.querySelector('.bulk-hidden');
    if (checked) {
      var qty = parseInt(qtyInput.value, 10) || 1;
      hidden.value = row.querySelector('.bulk-cb').getAttribute('data-kode') + ':' + qty;
      qtyInput.disabled = false;
    } else {
      hidden.value = '';
      qtyInput.disabled = true;
    }
    recount();
  }
  document.querySelectorAll('.serial-cb').forEach(function (cb) {
    cb.addEventListener('change', function () {
      document.getElementById('serialSelectAll').checked =
        document.querySelectorAll('.serial-cb').length > 0 &&
        document.querySelectorAll('.serial-cb:checked').length === document.querySelectorAll('.serial-cb').length;
      recount();
    });
  });
  var sa = document.getElementById('serialSelectAll');
  if (sa) sa.addEventListener('change', function () { toggleAllSerials(sa.checked); });
  document.querySelectorAll('.bulk-cb').forEach(function (cb) {
    cb.addEventListener('change', function () { syncBulk(cb.closest('tr'), cb.checked); });
  });
  document.querySelectorAll('.bulk-qty').forEach(function (q) {
    q.addEventListener('input', function () {
      var row = q.closest('tr');
      var cb = row.querySelector('.bulk-cb');
      if (cb.checked) {
        var qty = parseInt(q.value, 10) || 1;
        if (qty < 1) qty = 1;
        if (qty > 100) qty = 100;
        q.value = qty;
        row.querySelector('.bulk-hidden').value = cb.getAttribute('data-kode') + ':' + qty;
      }
      recount();
    });
  });
  var ba = document.getElementById('bulkSelectAll');
  if (ba) ba.addEventListener('change', function () {
    document.querySelectorAll('.bulk-cb').forEach(function (cb) {
      cb.checked = ba.checked;
      syncBulk(cb.closest('tr'), cb.checked);
    });
  });
  window.toggleAllSerials = function (on) {
    document.querySelectorAll('.serial-cb').forEach(function (cb) { cb.checked = on; });
    if (sa) sa.checked = on;
    recount();
  };
  // Validasi: minimal 1 label sebelum submit
  document.getElementById('printForm').addEventListener('submit', function (e) {
    if (recount() === 0) {
      e.preventDefault();
      AppToast.warning('Tidak ada label yang dipilih. Centang minimal 1 nomor seri atau item.');
    }
  });
})();
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
