<?php
$pageTitle = 'Cetak Label Barcode';
ob_start();
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="mb-6 fade-in flex flex-wrap items-center justify-between gap-3 no-print">
    <div>
      <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Cetak Label Barcode / QR</h1>
      <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
        <?= count($serials) ?> label — kode barang & nomor seri (Code 128 + QR).
      </p>
    </div>
    <div class="flex gap-2">
      <a href="<?= url('/scan') ?>" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-lg text-sm font-semibold transition-colors flex items-center gap-2">
        <i class="fas fa-qrcode"></i> Scan
      </a>
      <button onclick="window.print()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold transition-colors flex items-center gap-2">
        <i class="fas fa-print"></i> Cetak
      </button>
    </div>
  </div>

  <?php if (isset($_SESSION['error'])): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 no-print">
      <span class="block sm:inline"><?= htmlspecialchars($_SESSION['error']) ?></span>
      <?php unset($_SESSION['error']); ?>
    </div>
  <?php endif; ?>

  <?php if (empty($serials)): ?>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 no-print">
      <?php
      $emptyBare = true;
      $emptyIcon = 'fa-barcode';
      $emptyIconColor = 'text-gray-400 dark:text-gray-500';
      $emptyMessage = 'Tidak ada label yang cocok.';
      $emptySubMessage = 'Gunakan tautan dari halaman Nomor Seri / Master Barang, atau buka halaman Scan.';
      require __DIR__ . '/../components/table/empty_state.php';
      unset($emptyBare);
      ?>
    </div>
    <div class="mt-4 no-print">
      <a href="<?= url('/scan') ?>" class="text-blue-600 dark:text-blue-400 hover:underline text-sm">Buka halaman Scan Barcode</a>
    </div>
  <?php else: ?>
    <div class="labels-grid">
      <?php foreach ($serials as $s): ?>
        <?php
        $labelKode = $s['kode_barang'];
        $labelNama = $s['nama'];
        $labelSn = $s['is_serialized'] && $s['serial_number'] !== '' ? $s['serial_number'] : '';
        $bcText = $labelSn !== '' ? $labelSn : $labelKode;
        require __DIR__ . '/../components/label_card.php';
        ?>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<script src="<?= url('/assets/js/qrcode.min.js') ?>"></script>
<script>
(function () {
 document.querySelectorAll('.qr-box').forEach(function (box) {
  try {
   var qr = qrcode(0, 'M');
   qr.addData(box.getAttribute('data-qr'));
   qr.make();
   box.innerHTML = qr.createSvgTag({ cellSize: 3, margin: 1 });
  } catch (e) {
   box.innerHTML = '<span class="text-[10px] text-gray-400">QR error</span>';
  }
 });
})();
</script>

<style>
.labels-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 12px; }
.qr-box svg { max-width: 84px; height: auto; }
@media print {
 body * { visibility: hidden; }
 .labels-grid, .labels-grid * { visibility: visible; }
 .labels-grid { position: absolute; left: 0; top: 0; width: 100%; display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
 .label-card { border: 1px solid #000; border-radius: 4px; page-break-inside: avoid; }
}
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
