<?php
$pageTitle = 'Import / Export Data';
ob_start();
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$importResult = $_SESSION['import_result'] ?? null;
unset($_SESSION['import_result']);
?>

<div class="flex justify-between items-center mb-6 fade-in">
  <div>
    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Import / Export Data</h1>
    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Manajemen data massal (CSV) untuk master data sistem.</p>
  </div>
</div>

<?php if ($importResult): ?>
  <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6 fade-in">
    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">Hasil Import</h3>
    <p class="text-green-600 font-semibold mb-1">Berhasil diimpor: <?php echo $importResult['success']; ?> baris</p>
    <p class="text-red-600 font-semibold">Gagal/Error: <?php echo $importResult['error']; ?> baris</p>
    
    <?php if (!empty($importResult['error_details'])): ?>
      <div class="mt-4 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg max-h-60 overflow-y-auto">
        <ul class="list-disc list-inside text-sm text-red-700 dark:text-red-400">
          <?php foreach ($importResult['error_details'] as $err): ?>
            <li><?php echo htmlspecialchars($err); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>
  </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 fade-in">
  
  <!-- Export Section -->
  <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 border-t-4 border-blue-500">
    <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2 flex items-center">
      <i class="fas fa-file-export mr-3 text-blue-500"></i> Export Data CSV
    </h2>
    <p class="text-gray-600 dark:text-gray-400 text-sm mb-6">Unduh data dari sistem dalam format CSV (UTF-8, pemisah ;).</p>
    
    <div class="space-y-4">
      <!-- Master Barang -->
      <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-lg">
        <div>
          <h4 class="font-bold text-gray-800 dark:text-gray-200 text-sm">Master Barang</h4>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Export seluruh katalog master barang.</p>
        </div>
        <a href="<?php echo url('/import-export/export-items'); ?>" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-xs shadow-sm transition-colors whitespace-nowrap">
          <i class="fas fa-download mr-1"></i> Export
        </a>
      </div>

      <!-- Master Kategori -->
      <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-lg">
        <div>
          <h4 class="font-bold text-gray-800 dark:text-gray-200 text-sm">Kategori Barang</h4>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Export daftar kategori barang.</p>
        </div>
        <a href="<?php echo url('/import-export/export-categories'); ?>" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-xs shadow-sm transition-colors whitespace-nowrap">
          <i class="fas fa-download mr-1"></i> Export
        </a>
      </div>

      <!-- Gudang -->
      <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-lg">
        <div>
          <h4 class="font-bold text-gray-800 dark:text-gray-200 text-sm">Gudang & Cabang</h4>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Export data gudang/cabang yang aktif.</p>
        </div>
        <a href="<?php echo url('/import-export/export-warehouses'); ?>" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-xs shadow-sm transition-colors whitespace-nowrap">
          <i class="fas fa-download mr-1"></i> Export
        </a>
      </div>
      
      <!-- Serial Numbers -->
      <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-lg">
        <div>
          <h4 class="font-bold text-gray-800 dark:text-gray-200 text-sm">Serial Number</h4>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Export seluruh data SN & Status.</p>
        </div>
        <a href="<?php echo url('/import-export/export-serials'); ?>" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-xs shadow-sm transition-colors whitespace-nowrap">
          <i class="fas fa-download mr-1"></i> Export
        </a>
      </div>
    </div>
  </div>

  <!-- Import Section -->
  <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 border-t-4 border-green-500">
    <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2 flex items-center">
      <i class="fas fa-file-import mr-3 text-green-500"></i> Import Data CSV
    </h2>
    <p class="text-gray-600 dark:text-gray-400 text-sm mb-6">Unggah data massal dari file CSV (UTF-8, pemisah ;).</p>
    
    <div class="space-y-6">
      <!-- Master Items Import -->
      <div class="p-5 bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-lg">
        <div class="flex justify-between items-start mb-2">
          <h4 class="font-bold text-gray-800 dark:text-gray-200 text-sm">Import Master Barang</h4>
          <a href="<?= url('/import-export/template-items') ?>" class="text-xs text-blue-600 dark:text-blue-400 hover:underline"><i class="fas fa-download mr-1"></i>Template CSV</a>
        </div>
        <div class="text-[11px] text-gray-500 dark:text-gray-400 mb-4 bg-gray-100 dark:bg-gray-800 p-3 rounded">
          <span class="font-semibold block mb-1 text-gray-700 dark:text-gray-300">Format Header (7 kolom, urut, pemisah ;):</span>
          <code class="block mb-2 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded p-1.5">Kode Barang; Nama Barang; ID Kategori; ID Satuan; Serialized; Min Stok; Status</code>
          <table class="w-full text-left">
            <thead>
              <tr class="border-b border-gray-200 dark:border-gray-700">
                <th class="py-1 font-semibold">Kolom</th>
                <th class="py-1 font-semibold">Wajib</th>
                <th class="py-1 font-semibold">Penjelasan</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
              <tr><td class="py-1">Kode Barang</td><td>Opsional</td><td>Kosongkan → sistem membuat otomatis (KODEKATEGORI-000001)</td></tr>
              <tr><td class="py-1">Nama Barang</td><td class="font-bold text-red-500">Ya</td><td>Nama item</td></tr>
              <tr><td class="py-1">ID Kategori</td><td class="font-bold text-red-500">Ya</td><td>Angka — lihat Master Kategori (menu Master Data)</td></tr>
              <tr><td class="py-1">ID Satuan</td><td class="font-bold text-red-500">Ya</td><td>Angka — lihat Master Satuan (menu Master Data)</td></tr>
              <tr><td class="py-1">Serialized</td><td class="font-bold text-red-500">Ya</td><td>0 = tidak berseri, 1 = berseri</td></tr>
              <tr><td class="py-1">Min Stok</td><td>Opsional</td><td>Ambang stok minimum (default 0)</td></tr>
              <tr><td class="py-1">Status</td><td class="font-bold text-red-500">Ya</td><td>0 = nonaktif, 1 = aktif</td></tr>
            </tbody>
          </table>
          <ul class="mt-2 space-y-0.5">
            <li><i class="fas fa-check-circle text-green-500 mr-1"></i>Pemisah kolom: titik koma (;). Encoding: UTF-8.</li>
            <li><i class="fas fa-check-circle text-green-500 mr-1"></i>Baris contoh pada template berawalan # = panduan, tidak diimpor.</li>
            <li><i class="fas fa-shield-halved text-blue-500 mr-1"></i>Validasi menyeluruh sebelum simpan: bila ada 1 baris salah, seluruh file dibatalkan (aman, tidak setengah jadi).</li>
          </ul>
        </div>
        <div class="text-[11px] text-gray-500 dark:text-gray-400 mb-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 p-3 rounded">
          <span class="font-semibold block mb-1 text-blue-700 dark:text-blue-300">Alur Import:</span>
          <ol class="list-decimal list-inside space-y-0.5">
            <li>Unduh Template CSV (tombol di atas) untuk melihat format & contoh.</li>
            <li>Isi data pada template (hapus baris panduan # dan contoh).</li>
            <li>Pilih file → sistem memvalidasi tiap baris (wajib, ID, enum).</li>
            <li>Hasil ditampilkan: jumlah berhasil & daftar error per baris+kolom.</li>
            <li>Perbaiki error lalu unggah ulang — data hanya tersimpan jika semua baris valid.</li>
          </ol>
        </div>
        
        <form action="<?php echo url('/import-export/import-items'); ?>" method="POST" enctype="multipart/form-data" class="flex items-center gap-2">
          <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
          <input type="file" name="csv_file" accept=".csv" required class="flex-1 block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100 dark:file:bg-gray-700 dark:file:text-gray-200 cursor-pointer border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800">
          <button type="submit"
              data-confirm="Proses import data? Seluruh baris akan divalidasi sebelum disimpan."
              data-confirm-title="Proses Import"
              data-confirm-type="warning"
              data-confirm-icon="fa-file-import"
              data-confirm-confirm="Import"
              data-confirm-loading="Memproses..."
              class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-md text-sm shadow-sm transition-colors whitespace-nowrap">
            <i class="fas fa-upload mr-1"></i> Upload
          </button>
        </form>
      </div>
      
    </div>
  </div>

</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
