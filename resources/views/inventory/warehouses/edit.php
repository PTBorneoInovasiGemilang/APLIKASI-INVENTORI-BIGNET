<?php
$pageTitle = 'Ubah Gudang';
ob_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 flex items-center">
        <a href="<?= url('/warehouses') ?>" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 mr-4">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Ubah Gudang: <?= htmlspecialchars($warehouse['name']) ?></h1>
        </div>
    </div>

    <div class="overflow-x-auto bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700">
        <form action="<?= url('/warehouses/update/' . $warehouse['id']) ?>" method="POST" class="p-6">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Gudang</label>
                <input type="text" name="name" value="<?= htmlspecialchars($warehouse['name']) ?>" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Alamat</label>
                <textarea name="address" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"><?= htmlspecialchars($warehouse['address']) ?></textarea>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Deskripsi</label>
                <textarea name="description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"><?= htmlspecialchars($warehouse['description']) ?></textarea>
            </div>
            
            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" <?= $warehouse['is_active'] ? 'checked' : '' ?> class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded">
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Aktif</span>
                </label>
            </div>

            <div class="flex justify-end gap-4 mt-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                <a href="<?= url('/warehouses') ?>" class="bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 py-2 px-4 rounded-md shadow-sm text-sm font-medium transition-colors">Batal</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-6 rounded-md shadow-sm text-sm font-medium transition-colors">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
