<?php
$pageTitle = 'Foto Instalasi';
ob_start();
?>

<div class="space-y-6">
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
      <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight"><?= htmlspecialchars($pageTitle) ?></h1>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Galeri foto bukti pemasangan barang di lokasi pelanggan.</p>
    </div>
  </div>

  <?php if (empty($photos)): ?>
    <div class="bg-white dark:bg-slate-900 shadow-sm rounded-2xl p-10 text-center border border-slate-200/80 dark:border-slate-800">
      <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 mb-3">
        <i class="fas fa-camera text-xl"></i>
      </div>
      <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 mb-1">Belum ada foto instalasi.</h3>
      <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto">Instalasi yang memiliki bukti foto akan tampil di sini.</p>
    </div>
  <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-5">
      <?php foreach ($photos as $photo): ?>
        <?php $photoUrl = url('/file?path=' . rawurlencode($photo['photo_path'])); ?>
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm overflow-hidden border border-slate-200/80 dark:border-slate-800 hover:shadow-md transition-all flex flex-col">
          <a href="<?= htmlspecialchars($photoUrl) ?>" data-image-preview data-image-title="<?= htmlspecialchars($photo['customer_site']) ?>" class="block aspect-video bg-slate-100 dark:bg-slate-800 overflow-hidden group relative">
            <img src="<?= htmlspecialchars($photoUrl) ?>" alt="Foto instalasi di <?= htmlspecialchars($photo['customer_site']) ?>" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" loading="lazy">
          </a>
          <div class="p-4 flex-1 flex flex-col justify-between">
            <div>
              <div class="text-[11px] text-slate-400 dark:text-slate-500 mb-1">
                <?= date('d M Y, H:i', strtotime($photo['installed_at'])) ?>
              </div>
              <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100 mb-1.5 break-words line-clamp-1" title="<?= htmlspecialchars($photo['customer_site']) ?>">
                <?= htmlspecialchars($photo['customer_site']) ?>
              </h3>
              <?php if (!empty($photo['notes'])): ?>
                <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 mb-3"><?= htmlspecialchars($photo['notes']) ?></p>
              <?php endif; ?>
            </div>
            <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
              <a href="<?= url('/installations/show?id=' . (int)$photo['id']) ?>" class="inline-flex items-center gap-1.5 text-xs text-blue-600 dark:text-blue-400 font-bold hover:underline">
                <i class="fas fa-eye text-[10px]"></i> Lihat Detail
              </a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
