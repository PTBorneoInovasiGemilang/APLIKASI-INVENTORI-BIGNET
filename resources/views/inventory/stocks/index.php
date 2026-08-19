<?php
$pageTitle = 'Stok Barang';
$title = 'Stok Barang';
ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Ringkasan Stok</h1>
</div>

<?php
$toolbarAction = url('/stocks');
$searchPlaceholder = "Cari Nama Barang...";
ob_start();
?>
<div>
    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Gudang</label>
    <select name="warehouse_id" class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-48 py-2 px-3 text-sm text-gray-700 dark:text-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="0">Semua Gudang</option>
        <?php foreach ($warehouses as $wh): ?>
            <option value="<?php echo $wh['id']; ?>" <?php echo (isset($_GET['warehouse_id']) && $_GET['warehouse_id'] == $wh['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($wh['name']); ?></option>
        <?php endforeach; ?>
    </select>
</div>
<div>
    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Kategori</label>
    <select name="category_id" class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-48 py-2 px-3 text-sm text-gray-700 dark:text-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="0">Semua Kategori</option>
        <?php foreach ($categories as $cat): ?>
            <option value="<?php echo $cat['id']; ?>" <?php echo (isset($_GET['category_id']) && $_GET['category_id'] == $cat['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
        <?php endforeach; ?>
    </select>
</div>
<div>
    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Status Stok</label>
    <select name="filter" class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-40 py-2 px-3 text-sm text-gray-700 dark:text-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="">Semua Stok</option>
        <option value="low_stock" <?php echo (isset($_GET['filter']) && $_GET['filter'] == 'low_stock') ? 'selected' : ''; ?>>Stok Rendah</option>
    </select>
</div>
<?php
$extraFilters = ob_get_clean();
require __DIR__ . '/../../components/table/toolbar.php';
?>

<div class="overflow-x-auto bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700">
    <table class="w-full min-w-[1180px] table-sticky divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-900/50">
            <tr>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-12">No</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Gudang</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama Barang</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tipe</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kategori</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Stok Fisik</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Available</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Reserved</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Allocated</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Satuan</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
            </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            <?php 
            $no = ($offset ?? 0) + 1;
            foreach ($stocks as $stock): 
                // Stok Fisik item serialized = jumlah unit serial yang benar-benar berada di gudang
                // (authoritative), bukan kolom stocks.quantity yang tidak pernah berkurang saat
                // Barang Keluar serialized. Bulk = stocks.quantity.
                $physical = $stock['is_serialized'] ? (int)($stock['physical_serials'] ?? 0) : (int)$stock['quantity'];
                $available = $stock['is_serialized'] ? (int)$stock['available_serials'] : $physical;
                $isLowStock = (int)$stock['stok_minimum'] > 0 && $physical <= (int)$stock['stok_minimum'];
            ?>
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center"><?= $no++ ?></td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($stock['warehouse_name']); ?></td>
                <td class="px-4 py-3 whitespace-normal text-sm text-gray-900 dark:text-gray-100"><?php echo htmlspecialchars($stock['item_name']); ?></td>
                <td class="px-4 py-3 whitespace-nowrap text-sm">
                    <?php if ($stock['is_serialized']): ?>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">Berskala (Seri)</span>
                    <?php else: ?>
                        <?= \App\Helpers\UIHelper::badge('Curah (Bulk)', 'NEUTRAL') ?>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($stock['category_name']); ?></td>
                <td class="px-4 py-3 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-gray-100 text-right"><?= number_format($physical) ?></td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-green-600 dark:text-green-400 font-semibold text-right"><?= number_format($available) ?></td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-yellow-600 dark:text-yellow-400 text-right"><?= number_format(0) ?></td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-blue-600 dark:text-blue-400 text-right"><?= number_format(0) ?></td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($stock['unit_name']); ?></td>
                <td class="px-4 py-3 whitespace-nowrap text-sm">
                    <?php if (!$stock['is_active']): ?>
                        <?= \App\Helpers\UIHelper::badge('Nonaktif', 'DANGER') ?>
                    <?php elseif ($isLowStock): ?>
                        <?= \App\Helpers\UIHelper::badge('Stok Rendah', 'WARNING') ?>
                    <?php else: ?>
                        <?= \App\Helpers\UIHelper::badge('Aktif', 'SUCCESS') ?>
                    <?php endif; ?>
                </td>
                <?php
                $rowActions = [['type' => 'view', 'url' => url('/items/show/' . (int)$stock['master_item_id'] . '?warehouse_id=' . (int)$stock['warehouse_id']), 'label' => 'Lihat', 'icon' => 'fa-eye', 'title' => 'Detail Inventory 360']];
                require __DIR__ . '/../../components/table/row_actions.php';
                ?>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($stocks)): ?>
                <?php 
                $colspan = 12;
                $emptyMessage = (isset($_GET['filter']) && $_GET['filter'] === 'low_stock') ? "Belum ada barang dengan stok rendah." : "Tidak ada catatan stok ditemukan.";
                require __DIR__ . '/../../components/table/empty_state.php';
                ?>
            <?php endif; ?>
        </tbody>
    </table>
    
    <?php 
    $paginationBaseUrl = url('/stocks');
    require __DIR__ . '/../../components/table/pagination.php'; 
    ?>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
