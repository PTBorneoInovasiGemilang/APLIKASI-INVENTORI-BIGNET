<?php
$pageTitle = 'Gudang';
ob_start();
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="flex justify-between items-center mb-6">
    <div>
      <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Gudang</h1>
      <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Kelola semua fasilitas gudang.</p>
    </div>
    <?php if (!\App\Helpers\AuthHelper::hasRole('SUPERADMIN')): ?>
    <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow">
      <i class="fas fa-plus mr-2"></i> Tambah Gudang
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

  <?php
  $toolbarAction = url('/warehouses');
  $searchPlaceholder ="Cari Nama Gudang...";
  ob_start();
  ?>
  <div>
    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Status</label>
    <select name="status" class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-36 py-2 px-3 text-sm text-gray-700 dark:text-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
      <option value="">Semua Status</option>
      <option value="1" <?php echo (isset($_GET['status']) && $_GET['status'] === '1') ? 'selected' : ''; ?>>Aktif</option>
      <option value="0" <?php echo (isset($_GET['status']) && $_GET['status'] === '0') ? 'selected' : ''; ?>>Tidak Aktif</option>
    </select>
  </div>
  <?php
  $extraFilters = ob_get_clean();
  require __DIR__ . '/../../components/table/toolbar.php';

  if (!\App\Helpers\AuthHelper::hasRole('SUPERADMIN')):
    $bulkAction = url('/warehouses/bulk-delete');
    $bulkId = 'bulkWh';
    require __DIR__ . '/../../components/table/bulk_actions.php';
  endif;
  ?>

  <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700">
          <tr>
            <?php if (!\App\Helpers\AuthHelper::hasRole('SUPERADMIN')): ?>
            <th class="px-4 py-3 w-10">
              <input type="checkbox" class="bulk-select-all bulkWh rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500" aria-label="Pilih semua">
            </th>
            <?php endif; ?>
            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-12">No</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Alamat</th>
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
          foreach ($warehouses as $wh): 
          ?>
          <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
            <?php if (!\App\Helpers\AuthHelper::hasRole('SUPERADMIN')): ?>
            <td class="px-4 py-3 whitespace-nowrap text-center">
              <input type="checkbox" class="bulk-checkbox bulkWh rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500" value="<?= (int)$wh['id'] ?>" aria-label="Pilih baris">
            </td>
            <?php endif; ?>
            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center"><?= $no++ ?></td>
            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($wh['name']); ?></td>
            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 truncate max-w-xs"><?php echo htmlspecialchars($wh['address']); ?></td>
            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">
              <span class="bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 py-1 px-2 rounded-full text-xs font-medium">
                <?php echo !empty($wh['is_used']) ? 'Ya' : 'Tidak'; ?>
              </span>
            </td>
            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
              <?php if ($wh['is_active']): ?>
                <?= \App\Helpers\UIHelper::badge('Aktif', 'SUCCESS') ?>
              <?php else: ?>
                <?= \App\Helpers\UIHelper::badge('Nonaktif', 'DANGER') ?>
              <?php endif; ?>
            </td>
            <?php if (!\App\Helpers\AuthHelper::hasRole('SUPERADMIN')): ?>
            <?php
            $rowActions = [];
            $rowActions[] = ['type' => 'view', 'url' => url('/locations?warehouse_id=' . (int)$wh['id']), 'label' => 'Lihat Lokasi', 'icon' => 'fa-map-marker-alt'];
            if (\App\Helpers\AuthHelper::hasRole('ADMIN')) {
              $rowActions[] = ['type' => 'link', 'url' => url('/warehouses/edit/' . (int)$wh['id']), 'label' => 'Ubah', 'icon' => 'fa-edit'];
              if (empty($wh['is_used'])) {
                $rowActions[] = [
                  'type' => 'form',
                  'action' => url('/warehouses/delete/' . (int)$wh['id']),
                  'fields' => [
                    'csrf_token' => $_SESSION['csrf_token']
                  ],
                  'label' => 'Hapus',
                  'icon' => 'fa-trash',
                  'danger' => true,
                  'confirm' => [
                    'title' => 'Hapus Gudang?',
                    'message' => 'Apakah Anda yakin ingin menghapus gudang ini?',
                    'type' => 'danger',
                    'confirm' => 'Hapus',
                    'loading' => 'Menghapus...'
                  ]
                ];
              }
            }
            require __DIR__ . '/../../components/table/row_actions.php';
            ?>
            <?php endif; ?>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($warehouses)): ?>
            <?php 
            $colspan = \App\Helpers\AuthHelper::hasRole('SUPERADMIN') ? 4 : 6;
            $emptyMessage ="Tidak ada gudang yang ditemukan.";
            require __DIR__ . '/../../components/table/empty_state.php';
            ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    
    <?php 
    $paginationBaseUrl = url('/warehouses');
    require __DIR__ . '/../../components/table/pagination.php'; 
    ?>
  </div>
</div>

<!-- Add Modal -->
<div id="addModal" class="hidden fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 p-4 flex items-center justify-center overflow-y-auto transition-opacity">
  <div class="relative my-auto p-5 border w-full max-w-lg max-h-[90vh] overflow-y-auto shadow-2xl rounded-xl bg-white dark:bg-gray-800 dark:border-gray-700 fade-in">
    <div class="mt-3">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">Tambah Gudang Baru</h3>
        <button onclick="document.getElementById('addModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-500 dark:text-gray-400">
          <i class="fas fa-times"></i>
        </button>
      </div>
      
      <form action="<?php echo url('/warehouses'); ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

        <div class="mb-4">
          <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Nama Gudang</label>
          <input type="text" name="name" required class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-full py-2 px-3 text-gray-700 dark:text-white dark:bg-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="mb-4">
          <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Alamat</label>
          <textarea name="address" rows="2" class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-full py-2 px-3 text-gray-700 dark:text-white dark:bg-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        </div>
        
        <div class="mb-4">
          <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Deskripsi</label>
          <textarea name="description" rows="2" class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-full py-2 px-3 text-gray-700 dark:text-white dark:bg-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        </div>
        
        <div class="mb-4">
          <label class="flex items-center">
            <input type="checkbox" name="is_active" checked class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 dark:border-gray-700 rounded">
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
