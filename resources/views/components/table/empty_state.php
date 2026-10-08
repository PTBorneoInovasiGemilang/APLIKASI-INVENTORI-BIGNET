<?php
/**
 * REUSABLE EMPTY STATE COMPONENT (shared pattern — jangan copy-paste)
 *
 * Dua mode:
 * 1. ROW (default): untuk di dalam <tbody> tabel -> <tr><td colspan>...
 *   Variabel: $colspan (int, wajib), $emptyMessage, $emptySubMessage, $icon, $emptyIconColor
 * 2. PANEL: untuk blok di luar tabel (search no-result, report kosong, timeline kosong).
 *   Aktifkan dengan $emptyPanel = true.
 * 3. BARE: untuk area di dalam kartu/panel yang sudah punya container sendiri
 *   (timeline show, audit timeline, detail blocks). Aktifkan dengan $emptyBare = true —
 *   hanya blok terpusat tanpa kartu luar, agar tidak menimbulkan kartu bersarang.
 *   Variabel opsional: $emptyMessage, $emptySubMessage, $icon, $emptyIconColor
 *
 * Konsisten light/dark/mobile; ikon + judul + deskripsi terpusat horizontal & vertikal.
 */

$emptyMessage = $emptyMessage ?? 'Belum ada data.';
$emptySubMessage = $emptySubMessage ?? 'Data akan muncul di sini setelah ditambahkan atau diproses.';
$icon = $icon ?? 'fa-folder-open';
$emptyIconColor = $emptyIconColor ?? 'bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500';
$emptyPanel = $emptyPanel ?? false;
$emptyBare = $emptyBare ?? false;
$colspan = $colspan ?? 1; // fallback aman: jangan pernah render colspan="0"

$inner = function () use ($icon, $emptyIconColor, $emptyMessage, $emptySubMessage) {
  ?>
  <div class="flex flex-col items-center justify-center mx-auto max-w-sm sm:max-w-md text-center py-6 sm:py-8 px-4">
    <div class="inline-flex items-center justify-center w-12 h-12 sm:w-14 sm:h-14 rounded-full <?= htmlspecialchars($emptyIconColor) ?> mb-3 border border-gray-200 dark:border-gray-600 shrink-0">
      <i class="fas <?= htmlspecialchars($icon) ?> text-lg sm:text-xl"></i>
    </div>
    <h3 class="text-sm sm:text-base font-semibold text-gray-900 dark:text-gray-100 mb-1 leading-snug break-normal"><?= htmlspecialchars($emptyMessage) ?></h3>
    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 max-w-xs sm:max-w-sm mx-auto leading-relaxed break-normal">
      <?= htmlspecialchars($emptySubMessage) ?>
    </p>
  </div>
  <?php
};

if ($emptyBare): ?>
  <?php $inner(); ?>
<?php elseif ($emptyPanel): ?>
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-8 sm:p-12 text-center mt-6">
  <?php $inner(); ?>
</div>
<?php else: ?>
<tr>
  <td colspan="<?= (int)$colspan ?>" class="px-4 py-8 sm:py-16 text-center bg-white dark:bg-gray-800 align-middle">
    <?php $inner(); ?>
  </td>
</tr>
<?php endif; ?>
