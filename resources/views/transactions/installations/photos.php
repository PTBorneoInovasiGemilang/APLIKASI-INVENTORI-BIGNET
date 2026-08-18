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
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-shadow">
                    <a href="<?= url('/' . $photo['photo_path']) ?>" target="_blank" class="block aspect-video bg-gray-100 dark:bg-gray-900 overflow-hidden group">
                        <img src="<?= url('/' . $photo['photo_path']) ?>" alt="Foto Instalasi" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                    </a>
                    <div class="p-4">
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1 flex justify-between">
                            <span><?= date('d M Y', strtotime($photo['installed_at'])) ?></span>
                            <a href="<?= url('/installations/show?id=' . $photo['id']) ?>" class="text-blue-600 dark:text-blue-400 font-medium hover:underline"><?= htmlspecialchars($photo['transaction_code']) ?></a>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100 mb-2 truncate" title="<?= htmlspecialchars($photo['customer_site']) ?>"><?= htmlspecialchars($photo['customer_site']) ?></h3>
                        <?php if ($photo['notes']): ?>
                            <p class="text-xs text-gray-600 dark:text-gray-400 line-clamp-2"><?= htmlspecialchars($photo['notes']) ?></p>
                        <?php endif; ?>
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
