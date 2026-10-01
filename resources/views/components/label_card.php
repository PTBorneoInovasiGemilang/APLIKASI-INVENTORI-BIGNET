<?php
// Shared component for printing and viewing Asset Labels.
// Required variables:
// $labelKode: string
// $labelNama: string
// $labelSn: string (can be empty string)
// $bcText: string (barcode/QR payload)

$faviconUrl = setting('favicon_path') ? url('/file?path=' . urlencode(setting('favicon_path'))) : '';
?>
<div class="label-card flex flex-col items-center justify-between p-3 bg-white dark:bg-gray-800 border border-gray-400 border-dashed rounded-lg text-center w-full h-full min-h-[170px] max-w-[280px] mx-auto box-border overflow-hidden">
  <!-- Header -->
  <div class="flex items-center justify-center gap-2 mb-2 w-full">
    <?php if ($faviconUrl): ?>
      <img src="<?= htmlspecialchars($faviconUrl) ?>" alt="Logo" class="w-6 h-6 object-contain flex-shrink-0">
    <?php else: ?>
      <div class="w-6 h-6 bg-gray-200 rounded-full flex-shrink-0 flex items-center justify-center"><i class="fas fa-box text-xs text-gray-500 dark:text-gray-400"></i></div>
    <?php endif; ?>
    <div class="flex flex-col text-left leading-tight">
      <span class="text-[12px] font-extrabold text-gray-900 dark:text-gray-100 tracking-wide">BIG</span>
      <span class="text-[10px] font-medium text-gray-600 dark:text-gray-300">Sistem Gudang</span>
    </div>
  </div>

  <!-- Identitas Barang -->
  <div class="mb-2 w-full px-1">
    <div class="text-[12px] font-bold text-gray-900 dark:text-gray-100 uppercase truncate" title="<?= htmlspecialchars($labelKode) ?>"><?= htmlspecialchars($labelKode) ?></div>
    <div class="text-[11px] text-gray-700 dark:text-gray-200 truncate" title="<?= htmlspecialchars($labelNama) ?>"><?= htmlspecialchars($labelNama) ?></div>
    <?php if (!empty($labelSn)): ?>
      <div class="text-[11px] font-mono text-gray-800 dark:text-gray-100 font-bold mt-0.5 truncate" title="<?= htmlspecialchars($labelSn) ?>">SN: <?= htmlspecialchars($labelSn) ?></div>
    <?php endif; ?>
  </div>

  <!-- Barcode Code 128 -->
  <div class="flex justify-center w-full mb-1 overflow-hidden px-1">
    <?php echo \App\Helpers\BarcodeHelper::code128($bcText, 16, 1); ?>
  </div>


  <!-- Footer Kepemilikan -->
  <div class="text-[8.5px] font-bold text-gray-500 dark:text-gray-400 uppercase mt-auto tracking-tight border-t border-gray-200 dark:border-gray-700 pt-1.5 w-full">
    MILIK PT BORNEO INOVASI GEMILANG
  </div>
</div>
