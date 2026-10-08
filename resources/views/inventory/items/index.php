<?php
$pageTitle = 'Master Barang';
$title = 'Master Barang';
ob_start();
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<div class="space-y-6">
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
      <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Master Barang</h1>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Katalog data master seluruh item, perangkat, dan komoditas gudang.</p>
    </div>
    <?php if (\App\Helpers\AuthHelper::hasRole('ADMIN')): ?>
    <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-4 rounded-xl shadow-sm text-xs transition-all w-full sm:w-auto">
      <i class="fas fa-plus text-xs"></i> Tambah Barang
    </button>
    <?php endif; ?>
  </div>

  <!-- Summary Cards (Disafa Style) -->
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-800 p-4 sm:p-5 flex items-center">
      <div class="p-3 rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-300 shrink-0">
        <i class="fas fa-box text-base sm:text-lg"></i>
      </div>
      <div class="ml-3 sm:ml-4 min-w-0">
        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider truncate">Total Barang</p>
        <p class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-slate-100"><?= number_format($statTotal ?? 0) ?></p>
      </div>
    </div>
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-800 p-4 sm:p-5 flex items-center">
      <div class="p-3 rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-300 shrink-0">
        <i class="fas fa-circle-check text-base sm:text-lg"></i>
      </div>
      <div class="ml-3 sm:ml-4 min-w-0">
        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider truncate">Barang Aktif</p>
        <p class="text-xl sm:text-2xl font-extrabold text-emerald-600 dark:text-emerald-400"><?= number_format($statActive ?? 0) ?></p>
      </div>
    </div>
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-800 p-4 sm:p-5 flex items-center">
      <div class="p-3 rounded-xl bg-purple-50 text-purple-600 dark:bg-purple-950/40 dark:text-purple-300 shrink-0">
        <i class="fas fa-barcode text-base sm:text-lg"></i>
      </div>
      <div class="ml-3 sm:ml-4 min-w-0">
        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider truncate">Pakai Serial</p>
        <p class="text-xl sm:text-2xl font-extrabold text-purple-600 dark:text-purple-400"><?= number_format($statSerialized ?? 0) ?></p>
      </div>
    </div>
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-800 p-4 sm:p-5 flex items-center">
      <div class="p-3 rounded-xl bg-slate-50 text-slate-600 dark:bg-slate-800 dark:text-slate-300 shrink-0">
        <i class="fas fa-cubes text-base sm:text-lg"></i>
      </div>
      <div class="ml-3 sm:ml-4 min-w-0">
        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider truncate">Non-Serial (Bulk)</p>
        <p class="text-xl sm:text-2xl font-extrabold text-slate-700 dark:text-slate-300"><?= number_format($statBulk ?? 0) ?></p>
      </div>
    </div>
  </div>

  <!-- Toolbar -->
  <?php
  $toolbarAction = url('/items');
  $searchPlaceholder ="Cari Nama Barang...";
  ob_start();
  ?>
  <div>
    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Kategori</label>
    <select name="category_id" class="shadow-sm appearance-none border border-slate-300 dark:border-slate-700 rounded-xl w-40 py-2 px-3 text-sm text-slate-700 dark:text-slate-200 dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
      <option value="">Semua Kategori</option>
      <?php foreach ($categories as $cat): ?>
        <option value="<?php echo $cat['id']; ?>" <?php echo (isset($_GET['category_id']) && $_GET['category_id'] == $cat['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div>
    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Status</label>
    <select name="status" class="shadow-sm appearance-none border border-slate-300 dark:border-slate-700 rounded-xl w-36 py-2 px-3 text-sm text-slate-700 dark:text-slate-200 dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
      <option value="">Semua Status</option>
      <option value="1" <?php echo (isset($_GET['status']) && $_GET['status'] === '1') ? 'selected' : ''; ?>>Aktif</option>
      <option value="0" <?php echo (isset($_GET['status']) && $_GET['status'] === '0') ? 'selected' : ''; ?>>Nonaktif</option>
    </select>
  </div>
  <?php
  $extraFilters = ob_get_clean();
  require __DIR__ . '/../../components/table/toolbar.php';
  ?>

  <div class="bg-white dark:bg-slate-900 shadow-sm rounded-2xl overflow-hidden border border-slate-200/80 dark:border-slate-800">
    <div class="overflow-x-auto">
      <table class="w-full min-w-[760px] lg:min-w-full divide-y divide-slate-200/80 dark:divide-slate-800">
        <thead class="bg-slate-50 dark:bg-slate-800/60">
          <tr>
            <th class="px-4 py-3.5 text-center text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-12">No</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-16">Foto</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Nama Barang</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kategori</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Satuan</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tipe</th>
            <th class="px-4 py-3.5 text-center text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Digunakan</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
            <th class="px-4 py-3.5 text-center text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
        <?php 
        $no = ($offset ?? 0) + 1;
        foreach ($items as $item): 
        ?>
        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
          <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400 text-center"><?= $no++ ?></td>
          <td class="px-4 py-3 whitespace-nowrap">
            <?php if ($item['photo_path']): ?>
              <img src="<?= url('/file?path='.urlencode($item['photo_path'])) ?>" alt="Item" class="h-10 w-10 rounded-xl object-cover ring-1 ring-slate-200 dark:ring-slate-700">
            <?php else: ?>
              <div class="h-10 w-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 dark:text-slate-500 text-sm">
                <i class="fas fa-box"></i>
              </div>
            <?php endif; ?>
          </td>
          <td class="px-4 py-3 whitespace-normal text-sm font-bold text-slate-900 dark:text-slate-100"><?php echo htmlspecialchars($item['nama']); ?></td>
          <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300"><?php echo htmlspecialchars($item['category_name']); ?></td>
          <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300"><?php echo htmlspecialchars($item['unit_name']); ?></td>
          <td class="px-4 py-3 whitespace-nowrap text-sm">
            <?php if ($item['is_serialized']): ?>
              <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300 border border-purple-200/60 dark:border-purple-800">
                <i class="fas fa-barcode text-[9px]"></i> Serial
              </span>
            <?php else: ?>
              <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                <i class="fas fa-cubes text-[9px]"></i> Non-Serial
              </span>
            <?php endif; ?>
          </td>
          <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
            <span class="bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 py-0.5 px-2.5 rounded-full text-xs font-medium">
              <?php echo !empty($item['is_used']) ? 'Ya' : 'Tidak'; ?>
            </span>
          </td>
          <td class="px-4 py-3 whitespace-nowrap text-sm">
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
          $emptyMessage ="Tidak ada barang ditemukan.";
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
<div id="addModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 p-4 flex items-center justify-center overflow-y-auto transition-opacity">
  <div class="relative my-auto p-6 border border-slate-200 dark:border-slate-800 w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-2xl rounded-2xl bg-white dark:bg-slate-900 fade-in">
    <div>
      <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
        <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">Tambah Master Barang Baru</h3>
        <button onclick="document.getElementById('addModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg">
          <i class="fas fa-times text-sm"></i>
        </button>
      </div>

      <form action="<?php echo url('/items'); ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wide text-slate-700 dark:text-slate-300 mb-1.5">Nama Barang <span class="text-red-500">*</span></label>
            <input type="text" name="nama" required placeholder="Contoh: Router Mikrotik RB750Gr3" class="w-full py-2 px-3 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>

          <div>
            <label class="block text-xs font-bold uppercase tracking-wide text-slate-700 dark:text-slate-300 mb-1.5">Kategori <span class="text-red-500">*</span></label>
            <select name="item_category_id" required class="w-full py-2 px-3 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="">Pilih Kategori</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <label class="block text-xs font-bold uppercase tracking-wide text-slate-700 dark:text-slate-300 mb-1.5">Satuan <span class="text-red-500">*</span></label>
            <select name="unit_id" required class="w-full py-2 px-3 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="">Pilih Satuan</option>
              <?php foreach ($units as $unit): ?>
                <option value="<?php echo $unit['id']; ?>"><?php echo htmlspecialchars($unit['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <label class="block text-xs font-bold uppercase tracking-wide text-slate-700 dark:text-slate-300 mb-1.5">Stok Minimum</label>
            <input type="number" name="stok_minimum" value="0" min="0" required class="w-full py-2 px-3 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>

          <div class="md:col-span-2">
            <label class="block text-xs font-bold uppercase tracking-wide text-slate-700 dark:text-slate-300 mb-1.5">Foto Barang</label>
            <input type="file" name="photo" accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-slate-800 dark:file:text-slate-300">
          </div>
        </div>

        <div>
          <label class="block text-xs font-bold uppercase tracking-wide text-slate-700 dark:text-slate-300 mb-1.5">Deskripsi</label>
          <textarea name="deskripsi" rows="2" placeholder="Spesifikasi atau deskripsi barang..." class="w-full py-2 px-3 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        </div>
        
        <div>
          <label class="flex items-center cursor-pointer">
            <input type="checkbox" name="is_serialized" class="form-checkbox h-4 w-4 text-purple-600 border-slate-300 dark:border-slate-700 rounded">
            <span class="ml-2.5 text-xs font-bold text-slate-700 dark:text-slate-300">Barang Berseri (Wajib melacak Nomor Seri unik per unit)</span>
          </label>
        </div>

        <div>
          <label class="block text-xs font-bold uppercase tracking-wide text-slate-700 dark:text-slate-300 mb-1.5">Status Barang</label>
          <div class="flex gap-4">
            <label class="flex items-center cursor-pointer">
              <input type="radio" name="is_active" value="1" checked class="form-radio h-4 w-4 text-blue-600">
              <span class="ml-2 text-xs font-bold text-slate-700 dark:text-slate-300">Aktif</span>
            </label>
            <label class="flex items-center cursor-pointer">
              <input type="radio" name="is_active" value="0" class="form-radio h-4 w-4 text-red-500">
              <span class="ml-2 text-xs font-bold text-slate-700 dark:text-slate-300">Nonaktif</span>
            </label>
          </div>
        </div>
        
        <div class="flex justify-end gap-2 pt-4 border-t border-slate-100 dark:border-slate-800">
          <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition-colors">Batal</button>
          <button type="submit" class="px-5 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-sm transition-colors">Simpan Barang</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
