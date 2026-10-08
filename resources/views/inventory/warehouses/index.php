<?php
$pageTitle = 'Gudang';
ob_start();
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<div class="space-y-6">
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
      <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Gudang</h1>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Kelola semua fasilitas dan lokasi gudang penyimpanan.</p>
    </div>
    <?php if (!\App\Helpers\AuthHelper::hasRole('SUPERADMIN')): ?>
    <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-4 rounded-xl shadow-sm text-xs transition-all w-full sm:w-auto">
      <i class="fas fa-plus text-xs"></i> Tambah Gudang
    </button>
    <?php endif; ?>
  </div>

  <?php if (isset($_SESSION['success'])): ?>
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 dark:border-emerald-900/50 dark:bg-emerald-950/30 p-4 text-sm text-emerald-800 dark:text-emerald-200 flex items-center justify-between shadow-sm">
      <div class="flex items-center gap-3">
        <i class="fas fa-check-circle text-emerald-500 text-base shrink-0"></i>
        <span><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></span>
      </div>
    </div>
  <?php endif; ?>
  <?php if (isset($_SESSION['error'])): ?>
    <div class="rounded-xl border border-red-200 bg-red-50 dark:border-red-900/50 dark:bg-red-950/30 p-4 text-sm text-red-800 dark:text-red-200 flex items-center justify-between shadow-sm">
      <div class="flex items-center gap-3">
        <i class="fas fa-exclamation-circle text-red-500 text-base shrink-0"></i>
        <span><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
      </div>
    </div>
  <?php endif; ?>

  <!-- Summary Cards (Disafa Style) -->
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-800 p-3.5 sm:p-5 flex flex-col justify-between">
      <div class="flex items-center justify-between gap-1.5">
        <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Total Gudang</span>
        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-300 flex items-center justify-center shrink-0">
          <i class="fas fa-warehouse text-xs sm:text-sm"></i>
        </div>
      </div>
      <div class="mt-2 sm:mt-3">
        <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-slate-100"><?= number_format($statTotal ?? 0) ?></p>
        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Semua fasilitas</p>
      </div>
    </div>
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-800 p-3.5 sm:p-5 flex flex-col justify-between">
      <div class="flex items-center justify-between gap-1.5">
        <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Gudang Aktif</span>
        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-300 flex items-center justify-center shrink-0">
          <i class="fas fa-circle-check text-xs sm:text-sm"></i>
        </div>
      </div>
      <div class="mt-2 sm:mt-3">
        <p class="text-2xl sm:text-3xl font-extrabold text-emerald-600 dark:text-emerald-400"><?= number_format($statActive ?? 0) ?></p>
        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Operasional aktif</p>
      </div>
    </div>
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-800 p-3.5 sm:p-5 flex flex-col justify-between">
      <div class="flex items-center justify-between gap-1.5">
        <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Nonaktif</span>
        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-red-50 text-red-600 dark:bg-red-950/40 dark:text-red-300 flex items-center justify-center shrink-0">
          <i class="fas fa-ban text-xs sm:text-sm"></i>
        </div>
      </div>
      <div class="mt-2 sm:mt-3">
        <p class="text-2xl sm:text-3xl font-extrabold text-slate-700 dark:text-slate-300"><?= number_format($statInactive ?? 0) ?></p>
        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Diarsipkan</p>
      </div>
    </div>
  </div>

  <!-- Toolbar -->
  <?php
  $toolbarAction = url('/warehouses');
  $searchPlaceholder ="Cari Nama Gudang...";
  ob_start();
  ?>
  <div>
    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Status</label>
    <select name="status" class="shadow-sm appearance-none border border-slate-300 dark:border-slate-700 rounded-xl w-36 py-2 px-3 text-sm text-slate-700 dark:text-slate-200 dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
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

  <div class="bg-white dark:bg-slate-900 shadow-sm rounded-2xl overflow-hidden border border-slate-200/80 dark:border-slate-800">
    <div class="overflow-x-auto">
      <table class="w-full min-w-[700px] lg:min-w-full divide-y divide-slate-200/80 dark:divide-slate-800">
        <thead class="bg-slate-50 dark:bg-slate-800/60">
          <tr>
            <?php if (!\App\Helpers\AuthHelper::hasRole('SUPERADMIN')): ?>
            <th class="px-4 py-3.5 w-10">
              <input type="checkbox" class="bulk-select-all bulkWh rounded border-slate-300 dark:border-slate-700 text-blue-600 focus:ring-blue-500" aria-label="Pilih semua">
            </th>
            <?php endif; ?>
            <th class="px-4 py-3.5 text-center text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-12">No</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Nama Gudang</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Alamat & Deskripsi</th>
            <th class="px-4 py-3.5 text-center text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Digunakan</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
            <?php if (!\App\Helpers\AuthHelper::hasRole('SUPERADMIN')): ?>
            <th class="px-4 py-3.5 text-center text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
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
<div id="addModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 p-4 flex items-center justify-center overflow-y-auto transition-opacity">
  <div class="relative my-auto p-6 border border-slate-200 dark:border-slate-800 w-full max-w-lg max-h-[90vh] overflow-y-auto shadow-2xl rounded-2xl bg-white dark:bg-slate-900 fade-in">
    <div>
      <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
        <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">Tambah Gudang Baru</h3>
        <button onclick="document.getElementById('addModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg">
          <i class="fas fa-times text-sm"></i>
        </button>
      </div>
      
      <form action="<?php echo url('/warehouses'); ?>" method="POST" class="space-y-4">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

        <div>
          <label class="block text-xs font-bold uppercase tracking-wide text-slate-700 dark:text-slate-300 mb-1.5">Nama Gudang <span class="text-red-500">*</span></label>
          <input type="text" name="name" required placeholder="Contoh: Gudang Pontianak" class="w-full py-2 px-3 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
          <label class="block text-xs font-bold uppercase tracking-wide text-slate-700 dark:text-slate-300 mb-1.5">Alamat</label>
          <textarea name="address" rows="2" placeholder="Alamat lengkap fasilitas gudang..." class="w-full py-2 px-3 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        </div>
        
        <div>
          <label class="block text-xs font-bold uppercase tracking-wide text-slate-700 dark:text-slate-300 mb-1.5">Deskripsi</label>
          <textarea name="description" rows="2" placeholder="Keterangan operasional..." class="w-full py-2 px-3 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        </div>
        
        <div>
          <label class="flex items-center cursor-pointer">
            <input type="checkbox" name="is_active" checked class="form-checkbox h-4 w-4 text-blue-600 border-slate-300 dark:border-slate-700 rounded">
            <span class="ml-2 text-xs font-bold text-slate-700 dark:text-slate-300">Aktifkan Gudang</span>
          </label>
        </div>
        
        <div class="flex justify-end gap-2 pt-4 border-t border-slate-100 dark:border-slate-800">
          <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition-colors">Batal</button>
          <button type="submit" class="px-5 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-sm transition-colors">Simpan Gudang</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
