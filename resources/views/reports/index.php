<?php
$pageTitle = 'Laporan & Analisis';
ob_start();

$reportCards = [
  [
    'url' => '/reports/stock-position',
    'title' => 'Laporan Posisi Stok',
    'desc' => 'Melihat stok fisik saat ini per gudang, jumlah tersedia, dipinjam, dan dalam pengiriman.',
    'icon' => 'fa-boxes-stacked',
    'color' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-300'
  ],
  [
    'url' => '/reports/ledger',
    'title' => 'Laporan Stok & Mutasi',
    'desc' => 'Lihat detail riwayat semua pergerakan dan mutasi stok barang secara kronologis.',
    'icon' => 'fa-cubes',
    'color' => 'bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-300'
  ],
  [
    'url' => '/reports/serials',
    'title' => 'Laporan Nomor Seri',
    'desc' => 'Pantau status, lokasi, dan pemegang nomor seri barang berseri unik.',
    'icon' => 'fa-barcode',
    'color' => 'bg-purple-50 text-purple-600 dark:bg-purple-950/40 dark:text-purple-300'
  ],
  [
    'url' => '/reports/submissions',
    'title' => 'Laporan Pengajuan',
    'desc' => 'Data pengajuan barang oleh teknisi dan riwayat status persetujuannya.',
    'icon' => 'fa-clipboard-list',
    'color' => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-300'
  ],
  [
    'url' => '/reports/incoming',
    'title' => 'Laporan Barang Masuk',
    'desc' => 'Catatan transaksi barang masuk ke dalam gudang dari pengadaan atau vendor.',
    'icon' => 'fa-arrow-down',
    'color' => 'bg-teal-50 text-teal-600 dark:bg-teal-950/40 dark:text-teal-300'
  ],
  [
    'url' => '/reports/outgoing',
    'title' => 'Laporan Barang Keluar',
    'desc' => 'Catatan pengeluaran barang dari gudang untuk pemenuhan pengajuan.',
    'icon' => 'fa-arrow-up',
    'color' => 'bg-red-50 text-red-600 dark:bg-red-950/40 dark:text-red-300'
  ],
  [
    'url' => '/reports/transfers',
    'title' => 'Laporan Transfer Gudang',
    'desc' => 'Mutasi kiriman dan penerimaan barang antar fasilitas gudang cabang.',
    'icon' => 'fa-right-left',
    'color' => 'bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-300'
  ],
  [
    'url' => '/reports/loans',
    'title' => 'Laporan Peminjaman',
    'desc' => 'Daftar peminjaman barang dan nomor seri yang berada di custody teknisi.',
    'icon' => 'fa-hand-holding',
    'color' => 'bg-violet-50 text-violet-600 dark:bg-violet-950/40 dark:text-violet-300'
  ],
  [
    'url' => '/reports/returns',
    'title' => 'Laporan Pengembalian',
    'desc' => 'Laporan pengembalian barang pinjaman ke gudang asal beserta kondisi.',
    'icon' => 'fa-undo',
    'color' => 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-300'
  ],
  [
    'url' => '/reports/installations',
    'title' => 'Laporan Instalasi',
    'desc' => 'Data riwayat pemasangan/instalasi barang dan perangkat di lapangan.',
    'icon' => 'fa-tools',
    'color' => 'bg-cyan-50 text-cyan-600 dark:bg-cyan-950/40 dark:text-cyan-300'
  ],
  [
    'url' => '/reports/adjustments',
    'title' => 'Laporan Penyesuaian Stok',
    'desc' => 'Riwayat penyesuaian stok fisik secara manual atau selisih opname.',
    'icon' => 'fa-sliders',
    'color' => 'bg-orange-50 text-orange-600 dark:bg-orange-950/40 dark:text-orange-300'
  ],
  [
    'url' => '/reports/audit',
    'title' => 'Log Aktivitas Sistem',
    'desc' => 'Jejak audit seluruh aktivitas user dan modifikasi data transaksi operasional.',
    'icon' => 'fa-shield-halved',
    'color' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300'
  ]
];
?>

<div class="space-y-6">
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
      <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Laporan & Analisis</h1>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Pusat rekap data, audit log aktivitas, ekspor CSV, dan pencetakan dokumen PDF.</p>
    </div>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
    <?php foreach ($reportCards as $card): ?>
      <a href="<?= url($card['url']) ?>" class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-800 p-5 hover:shadow-md hover:border-blue-300 dark:hover:border-blue-700/60 transition-all flex flex-col justify-between group">
        <div>
          <div class="flex items-center gap-3.5 mb-3">
            <div class="w-11 h-11 rounded-xl <?= $card['color'] ?> flex items-center justify-center shrink-0 text-lg shadow-sm">
              <i class="fas <?= $card['icon'] ?>"></i>
            </div>
            <h2 class="text-base font-bold text-slate-900 dark:text-slate-100 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors leading-snug">
              <?= htmlspecialchars($card['title']) ?>
            </h2>
          </div>
          <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed"><?= htmlspecialchars($card['desc']) ?></p>
        </div>
        <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs font-bold text-blue-600 dark:text-blue-400">
          <span>Buka Laporan</span>
          <i class="fas fa-arrow-right text-[10px] transform group-hover:translate-x-1 transition-transform"></i>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
