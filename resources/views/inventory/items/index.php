<?php
$pageTitle = 'Master Barang';
$title = 'Master Barang';
ob_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Master Barang</h1>
    <?php if (\App\Helpers\AuthHelper::hasRole('ADMIN')): ?>
    <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow transition-colors">
        <i class="fas fa-plus mr-2"></i> Tambah Barang
    </button>
    <?php endif; ?>
</div>

<?php
$toolbarAction = url('/items');
$searchPlaceholder = "Cari Nama Barang...";
ob_start();
?>
<div>
    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Kategori</label>
    <select name="category_id" class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-40 py-2 px-3 text-sm text-gray-700 dark:text-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="">Semua Kategori</option>
        <?php foreach ($categories as $cat): ?>
            <option value="<?php echo $cat['id']; ?>" <?php echo (isset($_GET['category_id']) && $_GET['category_id'] == $cat['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
        <?php endforeach; ?>
    </select>
</div>
<div>
    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Status</label>
    <select name="status" class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-36 py-2 px-3 text-sm text-gray-700 dark:text-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="">Semua Status</option>
        <option value="1" <?php echo (isset($_GET['status']) && $_GET['status'] === '1') ? 'selected' : ''; ?>>Aktif</option>
        <option value="0" <?php echo (isset($_GET['status']) && $_GET['status'] === '0') ? 'selected' : ''; ?>>Nonaktif</option>
    </select>
</div>
<?php
$extraFilters = ob_get_clean();
require __DIR__ . '/../../components/table/toolbar.php';
?>

<div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[800px] lg:min-w-full table-sticky divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900/50">
                <tr>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-12">No</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Foto</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kategori</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Satuan</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tipe</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Digunakan</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                <?php 
                $no = ($offset ?? 0) + 1;
                foreach ($items as $item): 
                ?>
                <tr class="hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-700/50 transition-colors">
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center"><?= $no++ ?></td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <?php if ($item['photo_path']): ?>
                            <img src="<?= url('/file?path='.urlencode($item['photo_path'])) ?>" alt="Item" class="h-10 w-10 rounded-md object-cover">
                        <?php else: ?>
                            <div class="h-10 w-10 rounded-md bg-gray-200 dark:bg-gray-600 flex items-center justify-center text-gray-500 dark:text-gray-400">
                                <i class="fas fa-box"></i>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 whitespace-normal text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($item['nama']); ?></td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($item['category_name']); ?></td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($item['unit_name']); ?></td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        <?php if ($item['is_serialized']): ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">Berskala (Seri)</span>
                        <?php else: ?>
                            <?= \App\Helpers\UIHelper::badge('Curah (Bulk)', 'NEUTRAL') ?>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">
                        <span class="bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 py-1 px-2 rounded-full text-xs font-medium">
                            <?php echo !empty($item['is_used']) ? 'Ya' : 'Tidak'; ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        <?php if ($item['is_active']): ?>
                            <?= \App\Helpers\UIHelper::badge('Aktif', 'SUCCESS') ?>
                        <?php else: ?>
                            <?= \App\Helpers\UIHelper::badge('Nonaktif', 'DANGER') ?>
                        <?php endif; ?>
                    </td>
                    <?php
                    $rowActions = [];
                    $rowActions[] = ['type' => 'view', 'url' => url('/items/show/' . (int)$item['id']), 'label' => 'Lihat', 'icon' => 'fa-eye', 'title' => 'Detail Inventory 360'];
                    if (\App\Helpers\AuthHelper::hasRole('ADMIN')) {
                        $rowActions[] = ['type' => 'link', 'url' => url('/items/edit/' . (int)$item['id']), 'label' => 'Ubah', 'icon' => 'fa-edit'];
                        $csrf = $_SESSION['csrf_token'] ?? '';
                        if (!$item['is_active']) {
                            $rowActions[] = [
                                'type' => 'form', 'action' => url('/items/toggle-active/' . (int)$item['id']),
                                'fields' => ['csrf_token' => $csrf], 'label' => 'Aktifkan', 'icon' => 'fa-play',
                                'confirm' => [
                                    'title' => 'Aktifkan Barang?',
                                    'message' => 'Barang ini akan diaktifkan kembali?',
                                    'description' => 'Barang akan kembali berstatus Aktif dan dapat digunakan untuk transaksi baru sesuai aturan sistem.',
                                    'type' => 'success', 'icon' => 'fa-play', 'confirm' => 'Aktifkan', 'loading' => 'Mengaktifkan...'
                                ]
                            ];
                        } else {
                            $rowActions[] = [
                                'type' => 'form', 'action' => url('/items/toggle-active/' . (int)$item['id']),
                                'fields' => ['csrf_token' => $csrf], 'label' => 'Nonaktifkan', 'icon' => 'fa-ban',
                                'confirm' => [
                                    'title' => 'Nonaktifkan Barang?',
                                    'message' => 'Nonaktifkan barang ini?',
                                    'description' => 'Barang ini akan menjadi Nonaktif dan tidak dapat digunakan untuk transaksi baru sesuai aturan sistem. Riwayat barang tetap tersimpan.',
                                    'type' => 'warning', 'icon' => 'fa-ban', 'confirm' => 'Nonaktifkan', 'loading' => 'Menonaktifkan...'
                                ]
                            ];
                        }
                        if (!$item['is_used']) {
                            $rowActions[] = [
                                'type' => 'form', 'action' => url('/items/delete/' . (int)$item['id']),
                                'fields' => ['csrf_token' => $csrf], 'label' => 'Hapus', 'icon' => 'fa-trash', 'danger' => true,
                                'confirm' => [
                                    'title' => 'Hapus Master Barang?',
                                    'message' => 'Barang ini belum memiliki riwayat transaksi dan dapat dihapus permanen. Lanjutkan?',
                                    'type' => 'danger', 'icon' => 'fa-trash', 'confirm' => 'Hapus', 'loading' => 'Menghapus...'
                                ]
                            ];
                        }
                    } else {
                        $rowActions[] = ['type' => 'link', 'url' => url('/timeline?search=' . urlencode($item['kode_barang'])), 'label' => 'Lihat Perjalanan', 'icon' => 'fa-route'];
                    }
                    require __DIR__ . '/../../components/table/row_actions.php';
                    ?>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($items)): ?>
                    <?php 
                    $colspan = 8;
                    $emptyMessage = "Tidak ada barang ditemukan.";
                    require __DIR__ . '/../../components/table/empty_state.php';
                    ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php 
    $paginationBaseUrl = url('/items');
    require __DIR__ . '/../../components/table/pagination.php'; 
    ?>
</div>

<!-- Add Modal -->
<div id="addModal" class="hidden fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 p-4 flex items-center justify-center overflow-y-auto transition-opacity">
    <div class="relative my-auto p-5 border w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-2xl rounded-xl bg-white dark:bg-gray-800 dark:border-gray-700 fade-in">
        <div class="mt-3">
            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-4">Tambah Master Barang Baru</h3>
            <form action="<?php echo url('/items'); ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Nama Barang</label>
                        <input type="text" name="nama" required class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-full py-2 px-3 text-gray-700 dark:text-white dark:bg-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Kategori</label>
                        <select name="item_category_id" required class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-full py-2 px-3 text-gray-700 dark:text-white dark:bg-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih Kategori</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Satuan</label>
                        <select name="unit_id" required class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-full py-2 px-3 text-gray-700 dark:text-white dark:bg-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih Satuan</option>
                            <?php foreach ($units as $unit): ?>
                                <option value="<?php echo $unit['id']; ?>"><?php echo htmlspecialchars($unit['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Stok Minimum</label>
                        <input type="number" name="stok_minimum" value="0" min="0" required class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-full py-2 px-3 text-gray-700 dark:text-white dark:bg-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Foto</label>
                        <input type="file" name="photo" accept="image/*" class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-full py-2 px-3 text-gray-700 dark:text-white dark:bg-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Deskripsi</label>
                    <textarea name="deskripsi" rows="3" class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-full py-2 px-3 text-gray-700 dark:text-white dark:bg-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                
                <div class="mb-4 flex gap-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_serialized" class="form-checkbox h-4 w-4 text-purple-600">
                        <span class="ml-2 text-gray-700 dark:text-gray-300">Apakah Barang Seri?</span>
                    </label>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Status Barang</label>
                    <div class="flex gap-4">
                        <label class="flex items-center">
                            <input type="radio" name="is_active" value="1" checked class="form-radio h-4 w-4 text-blue-600">
                            <span class="ml-2 text-gray-700 dark:text-gray-300">Aktif</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="is_active" value="0" class="form-radio h-4 w-4 text-red-500">
                            <span class="ml-2 text-gray-700 dark:text-gray-300">Nonaktif</span>
                        </label>
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Barang Aktif dapat digunakan dalam transaksi baru sesuai aturan sistem.</p>
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
