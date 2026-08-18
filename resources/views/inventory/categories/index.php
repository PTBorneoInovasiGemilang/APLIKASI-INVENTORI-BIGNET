<?php
$pageTitle = 'Kategori Barang';
ob_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Kategori Barang</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Kelola klasifikasi untuk barang inventaris.</p>
        </div>
        <?php if (!\App\Helpers\AuthHelper::hasRole('SUPERADMIN')): ?>
        <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow">
            <i class="fas fa-plus mr-2"></i> Tambah Kategori
        </button>
        <?php endif; ?>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
            <span class="block sm:inline"><?= htmlspecialchars($_SESSION['success']) ?></span>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            <span class="block sm:inline"><?= htmlspecialchars($_SESSION['error']) ?></span>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5 border border-gray-200 dark:border-gray-700">
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Kategori</div>
            <div class="mt-1 text-2xl font-bold text-gray-900 dark:text-gray-100"><?= number_format($statTotal ?? 0) ?></div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5 border border-gray-200 dark:border-gray-700">
            <div class="text-sm font-medium text-green-600 dark:text-green-400">Aktif</div>
            <div class="mt-1 text-2xl font-bold text-gray-900 dark:text-gray-100"><?= number_format($statActive ?? 0) ?></div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5 border border-gray-200 dark:border-gray-700">
            <div class="text-sm font-medium text-red-600 dark:text-red-400">Nonaktif</div>
            <div class="mt-1 text-2xl font-bold text-gray-900 dark:text-gray-100"><?= number_format($statInactive ?? 0) ?></div>
        </div>
    </div>

    <!-- Toolbar -->
    <?php
    $toolbarAction = url('/categories');
    $searchPlaceholder = "Cari Kode / Nama...";
    ob_start();
    ?>
    <div>
        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Status</label>
        <select name="status" class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-36 py-2 px-3 text-sm text-gray-700 dark:text-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Semua Status</option>
            <option value="1" <?= (isset($_GET['status']) && $_GET['status'] === '1') ? 'selected' : '' ?>>Aktif</option>
            <option value="0" <?= (isset($_GET['status']) && $_GET['status'] === '0') ? 'selected' : '' ?>>Tidak Aktif</option>
        </select>
    </div>
    <?php
    $extraFilters = ob_get_clean();
    require __DIR__ . '/../../components/table/toolbar.php';

    // Bulk actions (hanya role dengan hak tulis)
    if (!\App\Helpers\AuthHelper::hasRole('SUPERADMIN')):
        $bulkAction = url('/categories/bulk-delete');
        $bulkId = 'bulkCat';
        require __DIR__ . '/../../components/table/bulk_actions.php';
    endif;
    ?>

    <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900/50">
                <tr>
                    <?php if (!\App\Helpers\AuthHelper::hasRole('SUPERADMIN')): ?>
                    <th class="px-4 py-3 w-10">
                        <input type="checkbox" class="bulk-select-all bulkCat rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500" aria-label="Pilih semua">
                    </th>
                    <?php endif; ?>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-12 text-center">No</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kode</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama Kategori</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Digunakan</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                    <?php if (!\App\Helpers\AuthHelper::hasRole('SUPERADMIN')): ?>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <?php 
                $no = ($offset ?? 0) + 1;
                foreach ($categories as $cat): 
                ?>
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <?php if (!\App\Helpers\AuthHelper::hasRole('SUPERADMIN')): ?>
                    <td class="px-4 py-3 whitespace-nowrap text-center">
                        <input type="checkbox" class="bulk-checkbox bulkCat rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500" value="<?= (int)$cat['id'] ?>" aria-label="Pilih baris">
                    </td>
                    <?php endif; ?>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center"><?= $no++ ?></td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100"><?php echo htmlspecialchars($cat['code']); ?></td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($cat['name']); ?></td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">
                        <span class="bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 py-1 px-2 rounded-full text-xs font-medium">
                            <?php echo number_format($cat['used_count'] ?? 0); ?> item
                        </span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        <?php if ($cat['is_active']): ?>
                            <?= \App\Helpers\UIHelper::badge('Aktif', 'SUCCESS') ?>
                        <?php else: ?>
                            <?= \App\Helpers\UIHelper::badge('Tidak Aktif', 'DANGER') ?>
                        <?php endif; ?>
                    </td>
                    <?php if (\App\Helpers\AuthHelper::hasRole('ADMIN')): ?>
                    <?php
                    $rowActions = [['type' => 'view', 'url' => url('/categories/edit/' . (int)$cat['id']), 'label' => 'Ubah', 'icon' => 'fa-edit']];
                    require __DIR__ . '/../../components/table/row_actions.php';
                    ?>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($categories)): ?>
                    <?php 
                    $colspan = \App\Helpers\AuthHelper::hasRole('SUPERADMIN') ? 5 : 7;
                    $emptyMessage = "Belum ada data kategori.";
                    require __DIR__ . '/../../components/table/empty_state.php';
                    ?>
                <?php endif; ?>
            </tbody>
        </table>
        </div>

        <?php 
        $paginationBaseUrl = url('/categories');
        require __DIR__ . '/../../components/table/pagination.php'; 
        ?>
    </div>
</div>

<!-- Add Modal -->
<div id="addModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50 transition-opacity">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-2xl rounded-xl bg-white dark:bg-gray-800 dark:border-gray-700 fade-in">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">Tambah Kategori Baru</h3>
                <button onclick="document.getElementById('addModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form action="<?php echo url('/categories'); ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Kode</label>
                    <input type="text" value="[Auto Generated]" disabled class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-full py-2 px-3 text-gray-400 bg-gray-100 dark:bg-gray-800 leading-tight">
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Nama</label>
                    <input type="text" name="name" required class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-full py-2 px-3 text-gray-700 dark:text-white dark:bg-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Deskripsi</label>
                    <textarea name="description" class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-full py-2 px-3 text-gray-700 dark:text-white dark:bg-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" checked class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded">
                        <span class="ml-2 text-gray-700 dark:text-gray-300">Aktif</span>
                    </label>
                </div>
                
                <div class="flex justify-end gap-2 mt-6 border-t dark:border-gray-700 pt-4">
                    <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="bg-gray-200 hover:bg-gray-300 text-gray-800 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 font-bold py-2 px-4 rounded">Batal</button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
