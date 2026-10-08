<?php
$pageTitle = 'Ubah Lokasi';
ob_start();
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<div class="max-w-3xl mx-auto space-y-5">
  <div class="flex items-center gap-2">
    <a href="<?= url('/locations') ?>" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400 transition-colors">
      <i class="fas fa-arrow-left text-[10px]"></i>
      <span>Kembali ke Lokasi</span>
    </a>
  </div>

  <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-800 p-5 sm:p-7">
    <div class="mb-5 pb-4 border-b border-slate-100 dark:border-slate-800">
      <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">Ubah Lokasi: <?= htmlspecialchars($location['name']) ?></h1>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Perbarui nama rak atau penempatan barang.</p>
    </div>

    <form action="<?= url('/locations/update/' . $location['id']) ?>" method="POST" class="space-y-5">
      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
          <label class="block text-xs font-bold uppercase tracking-wide text-slate-700 dark:text-slate-300 mb-1.5">Gudang Penyimpanan</label>
          <select disabled class="w-full py-2.5 px-3.5 bg-slate-100 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-500 dark:text-slate-400 cursor-not-allowed">
            <?php foreach ($warehouses as $wh): ?>
              <option value="<?= $wh['id'] ?>" <?= $location['warehouse_id'] == $wh['id'] ? 'selected' : '' ?>><?= htmlspecialchars($wh['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <div>
          <label class="block text-xs font-bold uppercase tracking-wide text-slate-700 dark:text-slate-300 mb-1.5">Nama / Kode Rak <span class="text-red-500">*</span></label>
          <input type="text" name="name" value="<?= htmlspecialchars($location['name']) ?>" required class="w-full py-2.5 px-3.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
      </div>

      <div>
        <label class="block text-xs font-bold uppercase tracking-wide text-slate-700 dark:text-slate-300 mb-1.5">Deskripsi</label>
        <textarea name="description" rows="2" class="w-full py-2.5 px-3.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($location['description']) ?></textarea>
      </div>
      
      <div>
        <label class="flex items-center cursor-pointer">
          <input type="checkbox" name="is_active" <?= $location['is_active'] ? 'checked' : '' ?> class="form-checkbox h-4 w-4 text-blue-600 border-slate-300 dark:border-slate-700 rounded">
          <span class="ml-2.5 text-xs font-bold text-slate-700 dark:text-slate-300">Status Aktif</span>
        </label>
      </div>

      <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
        <a href="<?= url('/locations') ?>" class="px-4 py-2.5 text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition-colors">Batal</a>
        <button type="submit" class="px-6 py-2.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-sm transition-colors">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
