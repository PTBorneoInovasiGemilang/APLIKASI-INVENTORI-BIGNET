<?php
$pageTitle = 'Terjadi Kesalahan (500)';
ob_start();
?>
<div class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8 fade-in">
    <div class="max-w-md w-full text-center">
        <h1 class="text-9xl font-bold text-red-500 dark:text-red-700">500</h1>
        <p class="mt-4 text-2xl font-bold text-gray-900 dark:text-gray-100">Terjadi Kesalahan Sistem</p>
        <p class="mt-2 text-gray-600 dark:text-gray-400">Maaf, sistem mengalami kesalahan saat memproses permintaan Anda.</p>
        
        <?php if (!empty($errorMessage)): ?>
        <div class="mt-4 p-4 bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400 rounded text-sm text-left font-mono break-words">
            <strong>Error:</strong> <?= htmlspecialchars($errorMessage) ?>
        </div>
        <?php endif; ?>

        <div class="mt-8">
            <a href="javascript:history.back()" class="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-gray-700 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600 mr-2">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>
            <a href="<?= url('/dashboard') ?>" class="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                <i class="fas fa-home mr-2"></i> Dasbor
            </a>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
if (isset($_SESSION['user'])) {
    require __DIR__ . '/../layouts/app.php';
} else {
    echo $content;
}
?>
