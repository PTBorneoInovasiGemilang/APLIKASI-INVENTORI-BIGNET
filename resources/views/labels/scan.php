<?php
$pageTitle = 'Scan Barcode / QR';
ob_start();
?>
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 fade-in">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Scan Barcode / QR</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Arahkan scanner (atau ketik manual) kode barang / nomor seri untuk membuka detail.</p>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            <span class="block sm:inline"><?= htmlspecialchars($_SESSION['error']) ?></span>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <form method="POST" action="<?= url('/scan') ?>" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Kode Barang / Nomor Seri</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-barcode text-gray-400"></i>
                    </div>
                    <input type="text" id="scanInput" name="q" autofocus autocomplete="off" placeholder="Scan / ketik di sini, lalu Enter..."
                        class="w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-md text-base text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    Scanner genggam otomatis mengetik dan menekan Enter. Di ponsel, gunakan keyboard manual.
                </p>
            </div>
            <button type="submit" class="w-full sm:w-auto px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-md font-semibold transition-colors text-sm">
                <i class="fas fa-search mr-2"></i> Cari
            </button>
        </form>
    </div>

    <div class="mt-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-5">
        <h3 class="text-sm font-semibold text-gray-800 dark:text-white uppercase tracking-wide mb-3">Cetak Label</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">Buka lembar label dari halaman Nomor Seri (ikon barcode per baris) atau Master Barang (label per item).</p>
        <a href="<?= url('/serials') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-lg text-sm font-semibold transition-colors">
            <i class="fas fa-list"></i> Daftar Nomor Seri
        </a>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
