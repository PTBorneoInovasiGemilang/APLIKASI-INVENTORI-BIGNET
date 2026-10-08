<?php
$pageTitle = 'Akses Ditolak (403)';
ob_start();
?>
<div class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8 fade-in">
  <div class="max-w-md w-full text-center">
    <h1 class="text-9xl font-bold text-red-300 dark:text-red-900/50">403</h1>
    <p class="mt-4 text-2xl font-bold text-gray-900 dark:text-gray-100">Akses Ditolak</p>
    <p class="mt-2 text-gray-600 dark:text-gray-400">Anda tidak memiliki izin untuk mengakses halaman atau berkas ini.</p>
    
    <div class="mt-8">
      <a href="<?= url('/dashboard') ?>" class="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
        <i class="fas fa-home mr-2"></i> Kembali ke Dasbor
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
