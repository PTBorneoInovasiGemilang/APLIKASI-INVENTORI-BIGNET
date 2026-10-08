<?php
$pageTitle = 'Import / Export Data';
ob_start();
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$importResult = $_SESSION['import_result'] ?? null;
unset($_SESSION['import_result']);
?>

<div class="space-y-6">
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
      <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Import / Export Data</h1>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Manajemen data massal (CSV) untuk katalog master barang, kategori, dan serial.</p>
    </div>
  </div>

  <?php if ($importResult): ?>
    <div class="bg-white dark:bg-slate-900 shadow-sm rounded-2xl p-5 border border-slate-200/80 dark:border-slate-800">
      <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 mb-2">Hasil Import CSV</h3>
      <div class="flex flex-wrap gap-4 text-xs font-bold">
        <span class="text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-3 py-1 rounded-lg">
          <i class="fas fa-check-circle mr-1"></i> Berhasil diimpor: <?php echo $importResult['success']; ?> baris
        </span>
        <span class="text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-950/40 px-3 py-1 rounded-lg">
          <i class="fas fa-exclamation-circle mr-1"></i> Gagal / Error: <?php echo $importResult['error']; ?> baris
        </span>
      </div>
      
      <?php if (!empty($importResult['error_details'])): ?>
        <div class="mt-4 p-3.5 bg-red-50/70 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl max-h-60 overflow-y-auto">
          <ul class="list-disc list-inside text-xs text-red-700 dark:text-red-300 space-y-1">
            <?php foreach ($importResult['error_details'] as $err): ?>
              <li><?php echo htmlspecialchars($err); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 sm:gap-6">
    
    <!-- Export Section -->
    <div class="bg-white dark:bg-slate-900 shadow-sm rounded-2xl p-5 sm:p-6 border border-slate-200/80 dark:border-slate-800 flex flex-col justify-between">
      <div>
        <div class="flex items-center gap-3 mb-2">
          <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-300 flex items-center justify-center shrink-0">
            <i class="fas fa-file-export text-base"></i>
          </div>
          <div>
            <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">Export Data CSV</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Unduh data sistem dalam format CSV (UTF-8, pemisah titik koma ';').</p>
          </div>
        </div>
        
        <div class="space-y-3 mt-5">
          <!-- Master Barang -->
          <div class="flex items-center justify-between p-3.5 bg-slate-50 dark:bg-slate-800/40 border border-slate-200/80 dark:border-slate-800 rounded-xl">
            <div>
              <h4 class="font-bold text-slate-800 dark:text-slate-200 text-xs">Master Barang</h4>
              <p class="text-[11px] text-slate-400 mt-0.5">Export seluruh katalog master barang aktif.</p>
            </div>
            <a href="<?php echo url('/import-export/export-items'); ?>" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-1.5 px-3.5 rounded-lg text-xs shadow-sm transition-colors whitespace-nowrap">
              <i class="fas fa-download mr-1"></i> Unduh
            </a>
          </div>

          <!-- Master Kategori -->
          <div class="flex items-center justify-between p-3.5 bg-slate-50 dark:bg-slate-800/40 border border-slate-200/80 dark:border-slate-800 rounded-xl">
            <div>
              <h4 class="font-bold text-slate-800 dark:text-slate-200 text-xs">Kategori Barang</h4>
              <p class="text-[11px] text-slate-400 mt-0.5">Export daftar kategori klasifikasi barang.</p>
            </div>
            <a href="<?php echo url('/import-export/export-categories'); ?>" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-1.5 px-3.5 rounded-lg text-xs shadow-sm transition-colors whitespace-nowrap">
              <i class="fas fa-download mr-1"></i> Unduh
            </a>
          </div>

          <!-- Gudang -->
          <div class="flex items-center justify-between p-3.5 bg-slate-50 dark:bg-slate-800/40 border border-slate-200/80 dark:border-slate-800 rounded-xl">
            <div>
              <h4 class="font-bold text-slate-800 dark:text-slate-200 text-xs">Gudang Penyimpanan</h4>
              <p class="text-[11px] text-slate-400 mt-0.5">Export data fasilitas gudang yang aktif.</p>
            </div>
            <a href="<?php echo url('/import-export/export-warehouses'); ?>" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-1.5 px-3.5 rounded-lg text-xs shadow-sm transition-colors whitespace-nowrap">
              <i class="fas fa-download mr-1"></i> Unduh
            </a>
          </div>
          
          <!-- Serial Numbers -->
          <div class="flex items-center justify-between p-3.5 bg-slate-50 dark:bg-slate-800/40 border border-slate-200/80 dark:border-slate-800 rounded-xl">
            <div>
              <h4 class="font-bold text-slate-800 dark:text-slate-200 text-xs">Nomor Seri</h4>
              <p class="text-[11px] text-slate-400 mt-0.5">Export seluruh data SN, lokasi, dan status.</p>
            </div>
            <a href="<?php echo url('/import-export/export-serials'); ?>" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-1.5 px-3.5 rounded-lg text-xs shadow-sm transition-colors whitespace-nowrap">
              <i class="fas fa-download mr-1"></i> Unduh
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Import Section -->
    <div class="bg-white dark:bg-slate-900 shadow-sm rounded-2xl p-5 sm:p-6 border border-slate-200/80 dark:border-slate-800 flex flex-col justify-between">
      <div>
        <div class="flex items-center gap-3 mb-2">
          <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-300 flex items-center justify-center shrink-0">
            <i class="fas fa-file-import text-base"></i>
          </div>
          <div>
            <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">Import Data CSV</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Unggah data massal dari file CSV (UTF-8, pemisah titik koma ';').</p>
          </div>
        </div>
        
        <div class="space-y-4 mt-5">
          <!-- Master Items Import Card -->
          <div class="p-4 bg-slate-50 dark:bg-slate-800/40 border border-slate-200/80 dark:border-slate-800 rounded-xl">
            <div class="flex justify-between items-center mb-2">
              <h4 class="font-bold text-slate-800 dark:text-slate-200 text-xs">Import Master Barang</h4>
              <a href="<?= url('/import-export/template-items') ?>" class="text-[11px] font-semibold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
                <i class="fas fa-download text-[10px]"></i> Unduh Template
              </a>
            </div>
            
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-3 leading-relaxed">
              Format 7 kolom: <code class="font-mono text-[10px] text-blue-600 dark:text-blue-300 bg-white dark:bg-slate-800 px-1.5 py-0.5 rounded border border-slate-200 dark:border-slate-700">Kode; Nama; ID Kategori; ID Satuan; Serialized; Min Stok; Status</code>.
            </p>

            <form action="<?= url('/import-export/import-items') ?>" method="POST" enctype="multipart/form-data" class="space-y-3">
              <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
              <div>
                <input type="file" name="csv_file" accept=".csv" required class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-slate-800 dark:file:text-slate-300">
              </div>
              <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-4 rounded-xl shadow-sm text-xs transition-colors">
                <i class="fas fa-upload text-xs"></i> Mulai Import Master Barang
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
