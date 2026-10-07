<?php
$pageTitle = 'Foto Instalasi';
ob_start();
use App\Helpers\AuthHelper;
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 fade-in">
  <div class="flex justify-between items-center mb-6 border-b border-gray-200 dark:border-gray-800 pb-4">
    <div>
      <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($pageTitle) ?></h1>
      <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Galeri foto bukti pemasangan barang di lokasi pelanggan.</p>
    </div>
  </div>

  <?php if (empty($photos)): ?>
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-12 text-center border border-gray-200 dark:border-gray-700">
      <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-500 mb-4">
        <i class="fas fa-camera text-2xl"></i>
      </div>
      <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">Belum ada foto instalasi.</h3>
      <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto mb-6">Instalasi yang memiliki bukti foto akan tampil di sini.</p>
    </div>
  <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
      <?php foreach ($photos as $photo): ?>
        <?php $photoUrl = url('/file?path=' . rawurlencode($photo['photo_path'])); ?>
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-shadow">
          <a href="<?= htmlspecialchars($photoUrl) ?>" data-image-preview data-image-title="<?= htmlspecialchars($photo['customer_site']) ?>" class="block aspect-video bg-gray-100 dark:bg-gray-900 overflow-hidden group">
            <img src="<?= htmlspecialchars($photoUrl) ?>" alt="Foto instalasi di <?= htmlspecialchars($photo['customer_site']) ?>" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" loading="lazy">
          </a>
          <div class="p-4">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">
              <?= date('d M Y, H:i', strtotime($photo['installed_at'])) ?>
            </div>
            <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100 mb-2" title="<?= htmlspecialchars($photo['customer_site']) ?>">
              <?= htmlspecialchars($photo['customer_site']) ?>
            </h3>
            <?php if (!empty($photo['notes'])): ?>
              <p class="text-xs text-gray-600 dark:text-gray-400 line-clamp-2 mb-3"><?= htmlspecialchars($photo['notes']) ?></p>
            <?php endif; ?>
            <a href="<?= url('/installations/show?id=' . (int)$photo['id']) ?>" class="inline-flex items-center text-sm text-blue-600 dark:text-blue-400 font-medium hover:underline">
              <i class="fas fa-eye mr-1"></i> Lihat Detail Instalasi
            </a>
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