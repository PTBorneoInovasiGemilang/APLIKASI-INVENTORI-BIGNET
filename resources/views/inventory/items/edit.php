<?php
$pageTitle = 'Ubah Master Barang';
ob_start();
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="mb-6 flex items-center">
    <a href="<?= url('/items') ?>" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 mr-4">
      <i class="fas fa-arrow-left"></i>
    </a>
    <div>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Ubah Barang</h1>
    </div>
  </div>

  <div class="overflow-x-auto bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700">
    <form action="<?= url('/items/update/' . $item['id']) ?>" method="POST" enctype="multipart/form-data" class="p-6">
      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Barang</label>
          <input type="text" name="nama" value="<?= htmlspecialchars($item['nama']) ?>" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kategori</label>
          <select name="item_category_id" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $item['item_category_id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?><?= !$cat['is_active'] ? ' (Nonaktif)' : '' ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Satuan</label>
          <select name="unit_id" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
            <?php foreach ($units as $unit): ?>
              <option value="<?= $unit['id'] ?>" <?= $unit['id'] == $item['unit_id'] ? 'selected' : '' ?>><?= htmlspecialchars($unit['name']) ?><?= !$unit['is_active'] ? ' (Nonaktif)' : '' ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Stok Minimum</label>
          <input type="number" name="stok_minimum" value="<?= $item['stok_minimum'] ?>" min="0" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tambah Foto Baru</label>
          <input type="file" name="photo" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-gray-700 dark:file:text-gray-300">
        </div>
      </div>

      <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Deskripsi</label>
        <textarea name="deskripsi" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"><?= htmlspecialchars($item['deskripsi']) ?></textarea>
      </div>
      
      <div class="mb-6 flex gap-6">
        <label class="flex items-center">
          <input type="checkbox" name="is_serialized" <?= $item['is_serialized'] ? 'checked' : '' ?> class="form-checkbox h-4 w-4 text-purple-600 border-gray-300 dark:border-gray-700 rounded">
          <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Apakah Barang Seri?</span>
        </label>
      </div>

      <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status Barang</label>
        <div class="flex gap-6">
          <label class="flex items-center">
            <input type="radio" name="is_active" value="1" <?= $item['is_active'] ? 'checked' : '' ?> class="form-radio h-4 w-4 text-blue-600 border-gray-300 dark:border-gray-700">
            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Aktif</span>
          </label>
          <label class="flex items-center">
            <input type="radio" name="is_active" value="0" <?= !$item['is_active'] ? 'checked' : '' ?> class="form-radio h-4 w-4 text-red-500 border-gray-300 dark:border-gray-700">
            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Nonaktif</span>
          </label>
        </div>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Status hanya berubah jika Anda mengubahnya. Perubahan nama/kategori/satuan/deskripsi tidak mengubah status.</p>
      </div>

      <div class="flex justify-end gap-4 mt-6 border-t border-gray-200 dark:border-gray-700 pt-6">
        <a href="<?= url('/items') ?>" class="bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 py-2 px-4 rounded-md shadow-sm text-sm font-medium transition-colors">Batal</a>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-6 rounded-md shadow-sm text-sm font-medium transition-colors">Simpan Perubahan</button>
      </div>
    </form>

    <?php if (!empty($attachments)): ?>
    <div class="p-6 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 rounded-b-lg">
      <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Kelola Foto Existing</h3>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <?php foreach ($attachments as $att): ?>
          <div class="relative group rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 flex flex-col shadow-sm">
            <img src="<?= url('/file?path='.urlencode($att['file_path'])) ?>" class="w-full h-40 object-cover">
            
            <div class="p-3 flex-1 flex flex-col justify-between border-t border-gray-200 dark:border-gray-700">
              <div class="text-xs text-gray-500 dark:text-gray-400 mb-3 truncate" title="<?= htmlspecialchars($att['original_name']) ?>">
                <?= htmlspecialchars($att['original_name']) ?>
              </div>
              
              <div class="flex gap-2 justify-between">
                <!-- Form Ganti -->
                <form action="<?= url('/items/photo/replace/' . $att['id']) ?>" method="POST" enctype="multipart/form-data" class="flex-1 flex" onsubmit="return confirm('Apakah Anda yakin ingin mengganti foto ini?');">
                  <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                  <label class="cursor-pointer bg-blue-50 text-blue-700 hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50 text-xs py-1.5 px-2 rounded font-medium text-center flex-1 transition-colors border border-blue-200 dark:border-blue-800">
                    <i class="fas fa-sync-alt mr-1"></i> Ganti
                    <input type="file" name="photo" accept="image/*" class="hidden" onchange="this.form.submit()">
                  </label>
                </form>
                
                <!-- Form Hapus -->
                <form action="<?= url('/items/photo/delete/' . $att['id']) ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus foto ini? File akan dihapus permanen.');">
                  <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                  <button type="submit" class="bg-red-50 text-red-700 hover:bg-red-100 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50 text-xs py-1.5 px-3 rounded font-medium transition-colors border border-red-200 dark:border-red-800" title="Hapus Foto">
                    <i class="fas fa-trash-alt"></i>
                  </button>
                </form>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
