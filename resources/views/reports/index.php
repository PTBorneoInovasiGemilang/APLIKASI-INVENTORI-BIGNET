<?php
$pageTitle = 'Laporan & Analisis';
ob_start();
?>

<div class="flex justify-between items-center mb-6">
  <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Laporan & Analisis</h1>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
  
  <a href="<?php echo url('/reports/stock-position'); ?>" class="block bg-white dark:bg-gray-800 shadow rounded-lg p-6 hover:shadow-lg hover:scale-[1.01] transition-all border-t-4 border-emerald-500">
    <div class="flex items-center mb-4">
      <div class="p-3 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 mr-4">
        <i class="fas fa-boxes-stacked text-xl"></i>
      </div>
      <h3 class="text-lg font-bold text-gray-850 dark:text-white">Laporan Posisi Stok</h3>
    </div>
    <p class="text-gray-600 dark:text-gray-400 text-sm">Melihat stok fisik saat ini per gudang, jumlah tersedia, dipinjam, dan dalam pengiriman.</p>
  </a>

  <a href="<?php echo url('/reports/ledger'); ?>" class="block bg-white dark:bg-gray-800 shadow rounded-lg p-6 hover:shadow-lg hover:scale-[1.01] transition-all border-t-4 border-blue-500">
    <div class="flex items-center mb-4">
      <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 mr-4">
        <i class="fas fa-cubes text-xl"></i>
      </div>
      <h3 class="text-lg font-bold text-gray-850 dark:text-white">Laporan Stok & Mutasi</h3>
    </div>
    <p class="text-gray-600 dark:text-gray-400 text-sm">Lihat detail riwayat semua pergerakan dan mutasi stok barang.</p>
  </a>

  <a href="<?php echo url('/reports/serials'); ?>" class="block bg-white dark:bg-gray-800 shadow rounded-lg p-6 hover:shadow-lg hover:scale-[1.01] transition-all border-t-4 border-indigo-500">
    <div class="flex items-center mb-4">
      <div class="p-3 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 mr-4">
        <i class="fas fa-barcode text-xl"></i>
      </div>
      <h3 class="text-lg font-bold text-gray-850 dark:text-white">Laporan Nomor Seri</h3>
    </div>
    <p class="text-gray-600 dark:text-gray-400 text-sm">Pantau status, lokasi, dan pemegang nomor seri barang.</p>
  </a>

  <a href="<?php echo url('/reports/submissions'); ?>" class="block bg-white dark:bg-gray-800 shadow rounded-lg p-6 hover:shadow-lg hover:scale-[1.01] transition-all border-t-4 border-green-500">
    <div class="flex items-center mb-4">
      <div class="p-3 rounded-full bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 mr-4">
        <i class="fas fa-clipboard-list text-xl"></i>
      </div>
      <h3 class="text-lg font-bold text-gray-850 dark:text-white">Laporan Pengajuan</h3>
    </div>
    <p class="text-gray-600 dark:text-gray-400 text-sm">Data pengajuan barang oleh teknisi dan status persetujuannya.</p>
  </a>

  <a href="<?php echo url('/reports/incoming'); ?>" class="block bg-white dark:bg-gray-800 shadow rounded-lg p-6 hover:shadow-lg hover:scale-[1.01] transition-all border-t-4 border-teal-500">
    <div class="flex items-center mb-4">
      <div class="p-3 rounded-full bg-teal-100 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 mr-4">
        <i class="fas fa-arrow-down text-xl"></i>
      </div>
      <h3 class="text-lg font-bold text-gray-850 dark:text-white">Laporan Barang Masuk</h3>
    </div>
    <p class="text-gray-600 dark:text-gray-400 text-sm">Catatan transaksi barang masuk ke dalam gudang dari supplier.</p>
  </a>

  <a href="<?php echo url('/reports/outgoing'); ?>" class="block bg-white dark:bg-gray-800 shadow rounded-lg p-6 hover:shadow-lg hover:scale-[1.01] transition-all border-t-4 border-red-500">
    <div class="flex items-center mb-4">
      <div class="p-3 rounded-full bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-450 mr-4">
        <i class="fas fa-arrow-up text-xl"></i>
      </div>
      <h3 class="text-lg font-bold text-gray-850 dark:text-white">Laporan Barang Keluar</h3>
    </div>
    <p class="text-gray-600 dark:text-gray-400 text-sm">Catatan pengeluaran barang dari gudang untuk pemenuhan pengajuan.</p>
  </a>

  <a href="<?php echo url('/reports/transfers'); ?>" class="block bg-white dark:bg-gray-800 shadow rounded-lg p-6 hover:shadow-lg hover:scale-[1.01] transition-all border-t-4 border-yellow-500">
    <div class="flex items-center mb-4">
      <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400 mr-4">
        <i class="fas fa-exchange-alt text-xl"></i>
      </div>
      <h3 class="text-lg font-bold text-gray-850 dark:text-white">Laporan Transfer</h3>
    </div>
    <p class="text-gray-600 dark:text-gray-400 text-sm">Mutasi kiriman dan penerimaan barang antar gudang cabang.</p>
  </a>

  <a href="<?php echo url('/reports/loans'); ?>" class="block bg-white dark:bg-gray-800 shadow rounded-lg p-6 hover:shadow-lg hover:scale-[1.01] transition-all border-t-4 border-purple-500">
    <div class="flex items-center mb-4">
      <div class="p-3 rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 mr-4">
        <i class="fas fa-hand-holding text-xl"></i>
      </div>
      <h3 class="text-lg font-bold text-gray-850 dark:text-white">Laporan Peminjaman</h3>
    </div>
    <p class="text-gray-600 dark:text-gray-400 text-sm">Daftar peminjaman barang dan nomor seri oleh para teknisi.</p>
  </a>

  <a href="<?php echo url('/reports/returns'); ?>" class="block bg-white dark:bg-gray-800 shadow rounded-lg p-6 hover:shadow-lg hover:scale-[1.01] transition-all border-t-4 border-pink-500">
    <div class="flex items-center mb-4">
      <div class="p-3 rounded-full bg-pink-100 dark:bg-pink-900/30 text-pink-600 dark:text-pink-400 mr-4">
        <i class="fas fa-undo text-xl"></i>
      </div>
      <h3 class="text-lg font-bold text-gray-850 dark:text-white">Laporan Pengembalian</h3>
    </div>
    <p class="text-gray-600 dark:text-gray-400 text-sm">Laporan pengembalian barang pinjaman ke gudang asal.</p>
  </a>

  <a href="<?php echo url('/reports/installations'); ?>" class="block bg-white dark:bg-gray-800 shadow rounded-lg p-6 hover:shadow-lg hover:scale-[1.01] transition-all border-t-4 border-cyan-500">
    <div class="flex items-center mb-4">
      <div class="p-3 rounded-full bg-cyan-100 dark:bg-cyan-900/30 text-cyan-600 dark:text-cyan-400 mr-4">
        <i class="fas fa-tools text-xl"></i>
      </div>
      <h3 class="text-lg font-bold text-gray-850 dark:text-white">Laporan Instalasi</h3>
    </div>
    <p class="text-gray-600 dark:text-gray-400 text-sm">Data riwayat pemasangan/instalasi barang di lapangan.</p>
  </a>

  <a href="<?php echo url('/reports/adjustments'); ?>" class="block bg-white dark:bg-gray-800 shadow rounded-lg p-6 hover:shadow-lg hover:scale-[1.01] transition-all border-t-4 border-amber-500">
    <div class="flex items-center mb-4">
      <div class="p-3 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 mr-4">
        <i class="fas fa-sliders-h text-xl"></i>
      </div>
      <h3 class="text-lg font-bold text-gray-850 dark:text-white">Laporan Penyesuaian</h3>
    </div>
    <p class="text-gray-600 dark:text-gray-400 text-sm">Riwayat penyesuaian stok fisik secara manual (stock opname).</p>
  </a>

  <a href="<?php echo url('/reports/audit'); ?>" class="block bg-white dark:bg-gray-800 shadow rounded-lg p-6 hover:shadow-lg hover:scale-[1.01] transition-all border-t-4 border-gray-500">
    <div class="flex items-center mb-4">
      <div class="p-3 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 mr-4">
        <i class="fas fa-shield-alt text-xl"></i>
      </div>
      <h3 class="text-lg font-bold text-gray-850 dark:text-white">Log Aktivitas</h3>
    </div>
    <p class="text-gray-600 dark:text-gray-400 text-sm">Jejak audit aktivitas user dan modifikasi data operasional.</p>
  </a>

</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
