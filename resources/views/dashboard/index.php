<?php
$pageTitle = 'Dasbor';
ob_start();

if (!function_exists('formatActivityNarrative')) {
  function formatActivityNarrative($activity) {
    $roleMap = ['SUPERADMIN' => 'Superadmin', 'ADMIN' => 'Admin', 'TEKNISI_NOC' => 'Teknisi/NOC'];
    $role = $roleMap[$activity['actor_role'] ?? ''] ?? 'Sistem';
    $actor = $activity['actor_name'] ?? 'Sistem';
    $entity = $activity['entity'];

    $entityMap = [
      'master_items' => 'Master Barang', 'incoming' => 'Barang Masuk', 'outgoing' => 'Barang Keluar',
      'transfers' => 'Transfer Gudang', 'submissions' => 'Pengajuan', 'loans' => 'Peminjaman',
      'returns' => 'Pengembalian', 'installations' => 'Instalasi', 'adjustments' => 'Penyesuaian Stok',
      'users' => 'Pengguna', 'serial_numbers' => 'Nomor Seri', 'settings' => 'Pengaturan',
      'incidents' => 'Insiden (Damage/Lost)', 'stock_opnames' => 'Stock Opname'
    ];
    $entityName = $entityMap[$entity] ?? $entity;

    $actionMap = [
      'CREATE' => 'menambahkan', 'UPDATE' => 'memperbarui', 'DELETE' => 'menghapus',
      'APPROVE' => 'menyetujui', 'REJECT' => 'menolak', 'PROCESS' => 'memproses',
      'RECEIVE_TRANSFER' => 'menerima'
    ];
    $actionText = $actionMap[$activity['action']] ?? strtolower($activity['action']);

    return"<strong>{$role} ({$actor})</strong> {$actionText} data {$entityName} #{$activity['entity_id']}";
  }
}

if (!function_exists('dashboardBadge')) {
  function dashboardBadge($text, $type) {
    $map = [
      'success' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
      'warning' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300',
      'danger' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
      'info' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
      'neutral' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
      'purple' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300',
    ];
    $cls = $map[$type] ?? $map['neutral'];
    return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold ' . $cls . '">' . htmlspecialchars($text) . '</span>';
  }
}
?>

<div class="space-y-6">

  <!-- Overview Header (Disafa SaaS Style) -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
      <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Overview</h1>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Monitoring stok dan pergerakan operasional gudang secara real-time</p>
    </div>
    <div class="flex items-center gap-2 self-start sm:self-auto">
      <div class="inline-flex items-center gap-1.5 bg-white dark:bg-slate-800 border border-slate-200/90 dark:border-slate-700 rounded-xl px-3 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200 shadow-sm">
        <i class="fas fa-warehouse text-blue-500 text-xs"></i>
        <span>Semua Gudang</span>
      </div>
      <button onclick="window.location.reload()" class="inline-flex items-center gap-1.5 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-200/90 dark:border-slate-700 rounded-xl px-3 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200 shadow-sm transition-colors">
        <i class="fas fa-sync-alt text-slate-400 text-xs"></i>
        <span>Refresh</span>
      </button>
    </div>
  </div>

  <!-- Hero KPI Widget (Royal Blue Gradient Banner - Disafa Style) -->
  <?php if (!isset($teknis_view) || !$teknis_view): ?>
  <div class="bg-gradient-to-r from-[#0a2540] via-[#0d3b66] to-[#1d4ed8] text-white rounded-2xl shadow-lg p-5 sm:p-6 relative overflow-hidden">
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 divide-y sm:divide-y-0 sm:divide-x divide-white/10">
      <div class="pr-3 pt-2 sm:pt-0">
        <p class="text-[11px] font-semibold text-blue-200/90 uppercase tracking-wider">Total Jenis Barang</p>
        <p class="text-2xl sm:text-3xl font-extrabold text-white mt-1"><?= number_format($stats['total_items']) ?></p>
        <p class="text-[11px] text-blue-200/70 mt-0.5">Master barang unik</p>
      </div>
      <div class="sm:px-4 pt-3 sm:pt-0">
        <p class="text-[11px] font-semibold text-blue-200/90 uppercase tracking-wider">Total Stok Fisik</p>
        <p class="text-2xl sm:text-3xl font-extrabold text-white mt-1"><?= number_format($stats['total_stock']) ?></p>
        <p class="text-[11px] text-blue-200/70 mt-0.5">Unit di seluruh gudang</p>
      </div>
      <div class="sm:px-4 pt-3 sm:pt-0">
        <p class="text-[11px] font-semibold text-blue-200/90 uppercase tracking-wider">Stok Rendah</p>
        <p class="text-2xl sm:text-3xl font-extrabold <?= $stats['low_stock'] > 0 ? 'text-amber-300' : 'text-white' ?> mt-1"><?= number_format($stats['low_stock']) ?></p>
        <p class="text-[11px] text-blue-200/70 mt-0.5"><?= $stats['low_stock'] > 0 ? 'Perlu restock' : 'Semua aman' ?></p>
      </div>
      <div class="sm:px-4 pt-3 sm:pt-0">
        <p class="text-[11px] font-semibold text-blue-200/90 uppercase tracking-wider">Masuk Hari Ini</p>
        <p class="text-2xl sm:text-3xl font-extrabold text-emerald-300 mt-1"><?= number_format($stats['incoming_today']) ?></p>
        <p class="text-[11px] text-blue-200/70 mt-0.5">Transaksi masuk</p>
      </div>
      <div class="sm:px-4 pt-3 sm:pt-0">
        <p class="text-[11px] font-semibold text-blue-200/90 uppercase tracking-wider">Keluar Hari Ini</p>
        <p class="text-2xl sm:text-3xl font-extrabold text-blue-100 mt-1"><?= number_format($stats['outgoing_today']) ?></p>
        <p class="text-[11px] text-blue-200/70 mt-0.5">Transaksi keluar</p>
      </div>
    </div>
  </div>
  <?php else: ?>
  <div class="bg-gradient-to-r from-[#0a2540] via-[#0d3b66] to-[#1d4ed8] text-white rounded-2xl shadow-lg p-5 sm:p-6 relative overflow-hidden">
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 divide-y sm:divide-y-0 sm:divide-x divide-white/10">
      <div class="pr-3 pt-2 sm:pt-0">
        <p class="text-[11px] font-semibold text-blue-200/90 uppercase tracking-wider">Pengajuan Aktif Saya</p>
        <p class="text-2xl sm:text-3xl font-extrabold text-white mt-1"><?= number_format($stats['active_submissions']) ?></p>
        <p class="text-[11px] text-blue-200/70 mt-0.5">Menunggu/disetujui</p>
      </div>
      <div class="sm:px-4 pt-3 sm:pt-0">
        <p class="text-[11px] font-semibold text-blue-200/90 uppercase tracking-wider">Barang Dipinjam</p>
        <p class="text-2xl sm:text-3xl font-extrabold text-white mt-1"><?= number_format($stats['active_loans']) ?></p>
        <p class="text-[11px] text-blue-200/70 mt-0.5">Tanggung jawab aktif</p>
      </div>
      <div class="sm:px-4 pt-3 sm:pt-0">
        <p class="text-[11px] font-semibold text-blue-200/90 uppercase tracking-wider">Barang Dipegang</p>
        <p class="text-2xl sm:text-3xl font-extrabold text-emerald-300 mt-1"><?= number_format($stats['held_serials']) ?></p>
        <p class="text-[11px] text-blue-200/70 mt-0.5">Nomor seri di tangan</p>
      </div>
      <div class="sm:px-4 pt-3 sm:pt-0">
        <p class="text-[11px] font-semibold text-blue-200/90 uppercase tracking-wider">Total Pengajuan</p>
        <p class="text-2xl sm:text-3xl font-extrabold text-blue-100 mt-1"><?= number_format($stats['total_submissions'] ?? 0) ?></p>
        <p class="text-[11px] text-blue-200/70 mt-0.5">Riwayat pengajuan</p>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Needs Attention Card (Disafa Style) -->
  <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-800 p-5 sm:p-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
      <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
        <span>Needs Attention</span>
        <?php if (!empty($alerts)): ?>
          <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300"><?= count($alerts) ?></span>
        <?php endif; ?>
      </h3>
      <div class="flex flex-wrap items-center gap-1.5 text-xs">
        <a href="<?= url('/stocks?filter=low_stock') ?>" class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 font-medium transition-colors">Stok Rendah</a>
        <a href="<?= url('/submissions?status=PENDING') ?>" class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 font-medium transition-colors">Pengajuan</a>
        <a href="<?= url('/incidents?status=PENDING') ?>" class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 font-medium transition-colors">Insiden</a>
      </div>
    </div>

    <div class="pt-4">
      <?php if (empty($alerts)): ?>
        <div class="py-6 text-center text-sm">
          <div class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-emerald-50 dark:bg-emerald-950/40 text-emerald-500 mb-2">
            <i class="fas fa-check text-sm"></i>
          </div>
          <p class="font-semibold text-emerald-600 dark:text-emerald-400">Semua aman</p>
          <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Tidak ada kejadian atau anomali yang perlu perhatian saat ini.</p>
        </div>
      <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
          <?php foreach ($alerts as $alert): ?>
            <?php
            $levelCls = [
              'danger' => 'border-red-200 dark:border-red-800 bg-red-50/70 dark:bg-red-900/20 text-red-700 dark:text-red-300',
              'warning' => 'border-amber-200 dark:border-amber-800 bg-amber-50/70 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300',
              'info' => 'border-blue-200 dark:border-blue-800 bg-blue-50/70 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300',
            ][$alert['level']] ?? 'border-slate-200 bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300';
            $iconCls = ['danger' => 'text-red-500', 'warning' => 'text-amber-500', 'info' => 'text-blue-500'][$alert['level']] ?? 'text-slate-400';
            ?>
            <a href="<?= $alert['url'] ?>" class="flex items-start gap-3 p-3.5 rounded-xl border <?= $levelCls ?> hover:shadow-sm transition-all">
              <i class="fas <?= $alert['icon'] ?> mt-0.5 <?= $iconCls ?>"></i>
              <div>
                <p class="text-xs font-bold"><?= htmlspecialchars($alert['title']) ?></p>
                <p class="text-xs opacity-90 mt-0.5"><?= htmlspecialchars($alert['desc']) ?></p>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Sekunder KPI Grid -->
  <?php if (!isset($teknis_view) || !$teknis_view): ?>
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm p-4 sm:p-5 flex items-center border border-slate-200/80 dark:border-slate-800">
      <div class="p-3 rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-300">
        <i class="fas fa-truck fa-lg"></i>
      </div>
      <div class="ml-3.5">
        <p class="text-xs font-semibold text-slate-400 uppercase">Dalam Transfer</p>
        <p class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100"><?= number_format($stats['transfers_in_transit']) ?></p>
      </div>
    </div>
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm p-4 sm:p-5 flex items-center border border-slate-200/80 dark:border-slate-800">
      <div class="p-3 rounded-xl bg-purple-50 text-purple-600 dark:bg-purple-900/40 dark:text-purple-300">
        <i class="fas fa-clipboard-list fa-lg"></i>
      </div>
      <div class="ml-3.5">
        <p class="text-xs font-semibold text-slate-400 uppercase">Pengajuan Aktif</p>
        <p class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100"><?= number_format($stats['active_submissions']) ?></p>
      </div>
    </div>
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm p-4 sm:p-5 flex items-center border border-slate-200/80 dark:border-slate-800">
      <div class="p-3 rounded-xl bg-teal-50 text-teal-600 dark:bg-teal-900/40 dark:text-teal-300">
        <i class="fas fa-tools fa-lg"></i>
      </div>
      <div class="ml-3.5">
        <p class="text-xs font-semibold text-slate-400 uppercase">Aset Terpasang</p>
        <p class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100"><?= number_format($stats['installed_assets']) ?></p>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Top Teknisi: Paling Sering Mengajukan -->
  <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm overflow-hidden border border-slate-200/80 dark:border-slate-800 mb-6">
    <div class="px-4 py-4 border-b border-slate-200/80 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
      <div>
        <h3 class="text-base font-semibold text-gray-800 dark:text-white">
          <i class="fas fa-trophy mr-2 text-yellow-500"></i> Top Teknisi yang Paling Sering Mengajukan
        </h3>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Teknisi/NOC dengan jumlah pengajuan barang terbanyak.</p>
      </div>
      <a href="<?= url('/submissions') ?>" class="text-xs font-semibold text-blue-600 hover:text-blue-500 dark:text-blue-400">Lihat pengajuan →</a>
    </div>

    <?php if (!empty($topSubmitters)): ?>
      <div class="divide-y divide-slate-100 dark:divide-slate-800/60">
        <?php foreach ($topSubmitters as $rank => $top): ?>
          <?php
            $rankNumber = $rank + 1;
            $rankClass = $rankNumber === 1
              ? 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300'
              : ($rankNumber === 2
                ? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200'
                : ($rankNumber === 3
                  ? 'bg-orange-50 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300'
                  : 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'));
            $count = (int)$top['submission_count'];
          ?>
          <div class="px-4 sm:px-6 py-3.5 flex items-center gap-3">
            <span class="flex-shrink-0 inline-flex items-center justify-center w-9 h-9 rounded-full text-sm font-extrabold <?= $rankClass ?>">
              #<?= $rankNumber ?>
            </span>

            <div class="flex-1 min-w-0">
              <p class="text-sm font-bold text-gray-800 dark:text-gray-100 truncate">
                <?= htmlspecialchars($top['name']) ?>
              </p>
              <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                Terakhir mengajukan: <?= !empty($top['last_submission_at']) ? date('d M Y, H:i', strtotime($top['last_submission_at'])) : '-' ?>
              </p>
            </div>

            <div class="flex-shrink-0 text-right">
              <p class="text-lg font-extrabold text-blue-600 dark:text-blue-400 leading-none"><?= number_format($count) ?></p>
              <p class="text-[10px] uppercase tracking-wide text-gray-400 dark:text-gray-500 mt-1">pengajuan</p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="px-6 py-8 text-center text-gray-400 dark:text-gray-500 text-sm">
        <i class="fas fa-trophy text-2xl mb-2"></i>
        <p>Belum ada pengajuan dari Teknisi/NOC.</p>
      </div>
    <?php endif; ?>
  </div>

  <!-- Top Barang Paling Sering Keluar -->
  <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm overflow-hidden border border-slate-200/80 dark:border-slate-800 mb-6">
    <div class="px-4 py-4 border-b border-slate-200/80 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
      <div>
        <h3 class="text-base font-semibold text-gray-800 dark:text-white">
          <i class="fas fa-arrow-up mr-2 text-red-500"></i> Barang yang Paling Sering Keluar
        </h3>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Barang dengan total kuantitas keluar terbanyak dari gudang.</p>
      </div>
      <a href="<?= url('/outgoing') ?>" class="text-xs font-semibold text-blue-600 hover:text-blue-500 dark:text-blue-400">Lihat barang keluar →</a>
    </div>

    <?php if (!empty($topOutgoingItems)): ?>
      <div class="divide-y divide-slate-100 dark:divide-slate-800/60">
        <?php foreach ($topOutgoingItems as $rank => $item): ?>
          <?php
            $rankNumber = $rank + 1;
            $rankClass = $rankNumber === 1
              ? 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300'
              : ($rankNumber === 2
                ? 'bg-orange-50 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300'
                : ($rankNumber === 3
                  ? 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300'
                  : 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'));
            $qty = (int)$item['outgoing_quantity'];
            $unit = trim((string)($item['unit_name'] ?? ''));
          ?>
          <div class="px-4 sm:px-6 py-3.5 flex items-center gap-3">
            <span class="flex-shrink-0 inline-flex items-center justify-center w-9 h-9 rounded-full text-sm font-extrabold <?= $rankClass ?>">
              #<?= $rankNumber ?>
            </span>

            <div class="flex-1 min-w-0">
              <p class="text-sm font-bold text-gray-800 dark:text-gray-100 break-words">
                <?= htmlspecialchars($item['nama']) ?>
              </p>
              <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                <?= htmlspecialchars($item['kode_barang'] ?? '-') ?>
                · <?= number_format((int)$item['outgoing_transactions']) ?> transaksi
                <?php if (!empty($item['last_outgoing_at'])): ?>
                  · terakhir <?= date('d M Y, H:i', strtotime($item['last_outgoing_at'])) ?>
                <?php endif; ?>
              </p>
            </div>

            <div class="flex-shrink-0 text-right">
              <p class="text-lg font-extrabold text-red-600 dark:text-red-400 leading-none"><?= number_format($qty) ?></p>
              <p class="text-[10px] uppercase tracking-wide text-gray-400 dark:text-gray-500 mt-1"><?= htmlspecialchars($unit ?: 'unit') ?> keluar</p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="px-6 py-8 text-center text-gray-400 dark:text-gray-500 text-sm">
        <i class="fas fa-box-open text-2xl mb-2"></i>
        <p>Belum ada transaksi Barang Keluar.</p>
      </div>
    <?php endif; ?>
  </div>

  <!-- Prediksi Stok & Rekomendasi Restock -->
  <section class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm overflow-hidden border border-slate-200/80 dark:border-slate-800 mb-6">
    <div class="px-4 sm:px-5 py-4 border-b border-slate-200/80 dark:border-slate-800">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-start gap-3 min-w-0">
          <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-300">
            <i class="fas fa-chart-line"></i>
          </span>
          <div class="min-w-0">
            <h3 class="text-base font-bold text-gray-900 dark:text-white">Prediksi Stok & Rekomendasi Restock</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Berdasarkan pemakaian Barang Keluar selama 30 hari terakhir.</p>
          </div>
        </div>
        <a href="<?= url('/stocks') ?>" class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 hover:text-blue-500 dark:text-blue-400 shrink-0">
          Lihat stok <i class="fas fa-arrow-right text-[9px]"></i>
        </a>
      </div>
    </div>

    <?php if (!empty($restockRecommendations)): ?>
      <div class="divide-y divide-slate-100 dark:divide-slate-800/70">
        <?php foreach ($restockRecommendations as $rec): ?>
          <?php
            $critical = !empty($rec['critical']);
            $days = $rec['days_until_empty'];
            $daysText = $days === null ? 'Belum ada pola pemakaian' : ($days <= 0 ? 'Diperkirakan habis hari ini' : 'Diperkirakan habis dalam ' . number_format($days) . ' hari');
            $statusTone = $critical
              ? 'border-red-400 bg-red-50/70 dark:border-red-500 dark:bg-red-900/10'
              : 'border-amber-400 bg-amber-50/40 dark:border-amber-500 dark:bg-amber-900/10';
            $predictionTone = $critical
              ? 'text-red-600 dark:text-red-400'
              : 'text-amber-600 dark:text-amber-400';
            $recommendation = $rec['recommended_qty'] > 0
              ? 'Pesan ' . number_format($rec['recommended_qty']) . ' ' . htmlspecialchars($rec['unit_name'])
              : 'Pantau stok';
          ?>
          <div class="px-3 sm:px-5 py-4 sm:py-5">
            <div class="border-l-4 <?= $statusTone ?> rounded-r-xl p-3 sm:p-4">
              <div class="grid grid-cols-1 lg:grid-cols-[minmax(230px,1.45fr)_repeat(3,minmax(120px,0.8fr))_minmax(190px,1fr)] gap-3 lg:gap-4 items-stretch">
                <!-- Barang -->
                <div class="min-w-0">
                  <div class="flex flex-wrap items-center gap-2">
                    <p class="text-sm font-bold text-gray-900 dark:text-gray-100 break-words"><?= htmlspecialchars($rec['item_name']) ?></p>
                    <?= dashboardBadge($critical ? 'Kritis' : 'Perlu Restock', $critical ? 'danger' : 'warning') ?>
                  </div>
                  <div class="mt-1.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-[11px] text-gray-500 dark:text-gray-400">
                    <span class="font-medium text-gray-600 dark:text-gray-300"><?= htmlspecialchars($rec['kode_barang'] ?? '-') ?></span>
                    <span class="text-gray-300 dark:text-gray-600">•</span>
                    <span><?= htmlspecialchars($rec['warehouse_name']) ?></span>
                  </div>
                </div>

                <!-- Statistik -->
                <div class="rounded-lg bg-white/80 dark:bg-slate-900/70 border border-slate-200/80 dark:border-slate-800 px-3 py-2.5">
                  <p class="text-[9px] uppercase tracking-wider font-semibold text-gray-400 dark:text-gray-500">Stok saat ini</p>
                  <p class="mt-1 text-base font-extrabold text-gray-900 dark:text-white leading-none">
                    <?= number_format($rec['physical']) ?> <span class="text-[11px] font-bold text-gray-400 dark:text-gray-500"><?= htmlspecialchars($rec['unit_name']) ?></span>
                  </p>
                </div>

                <div class="rounded-lg bg-white/80 dark:bg-slate-900/70 border border-slate-200/80 dark:border-slate-800 px-3 py-2.5">
                  <p class="text-[9px] uppercase tracking-wider font-semibold text-gray-400 dark:text-gray-500">Keluar 30 hari</p>
                  <p class="mt-1 text-base font-extrabold text-gray-900 dark:text-white leading-none">
                    <?= number_format($rec['outgoing_30d']) ?> <span class="text-[11px] font-bold text-gray-400 dark:text-gray-500"><?= htmlspecialchars($rec['unit_name']) ?></span>
                  </p>
                </div>

                <div class="rounded-lg bg-white/80 dark:bg-slate-900/70 border border-slate-200/80 dark:border-slate-800 px-3 py-2.5">
                  <p class="text-[9px] uppercase tracking-wider font-semibold text-gray-400 dark:text-gray-500">Prediksi habis</p>
                  <p class="mt-1 text-sm font-extrabold <?= $predictionTone ?> leading-tight"><?= htmlspecialchars($daysText) ?></p>
                </div>

                <!-- Rekomendasi -->
                <div class="rounded-lg border border-indigo-100 bg-indigo-50/80 dark:border-indigo-900/60 dark:bg-indigo-900/20 px-3.5 py-2.5">
                  <div class="flex items-center justify-between gap-2">
                    <p class="text-[9px] uppercase tracking-wider font-semibold text-indigo-500/80 dark:text-indigo-300/70">Rekomendasi</p>
                    <i class="fas <?= $rec['recommended_qty'] > 0 ? 'fa-cart-plus' : 'fa-eye' ?> text-[10px] text-indigo-500 dark:text-indigo-300"></i>
                  </div>
                  <p class="mt-1 text-base font-extrabold text-indigo-700 dark:text-indigo-300 leading-tight"><?= $recommendation ?></p>
                  <?php if ($rec['avg_daily'] > 0): ?>
                    <p class="mt-1 text-[10px] text-indigo-500/80 dark:text-indigo-300/70">≈ <?= rtrim(rtrim(number_format($rec['avg_daily'], 2, '.', ''), '0'), '.') ?> <?= htmlspecialchars($rec['unit_name']) ?>/hari</p>
                  <?php else: ?>
                    <p class="mt-1 text-[10px] text-gray-400 dark:text-gray-500">Belum ada pemakaian 30 hari</p>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="px-6 py-10 text-center text-gray-400 dark:text-gray-500 text-sm">
        <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-green-50 text-green-500 dark:bg-green-900/20 dark:text-green-300 mb-3">
          <i class="fas fa-shield-check text-lg"></i>
        </span>
        <p class="font-semibold text-gray-500 dark:text-gray-400">Belum ada barang yang perlu direstock.</p>
        <p class="text-xs mt-1">Stok masih aman berdasarkan batas minimum dan pola pemakaian 30 hari.</p>
      </div>
    <?php endif; ?>
  </section>

  <!-- Quick Actions (Role-Based) -->
  <div>
    <h3 class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">
      <i class="fas fa-bolt mr-1 text-yellow-500"></i> Aksi Cepat
    </h3>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
      <?php foreach ($quickActions as $qa): ?>
        <?php
        $colorMap = [
          'blue' => 'border-blue-200 dark:border-blue-800 hover:border-blue-400 text-blue-600 dark:text-blue-300',
          'green' => 'border-green-200 dark:border-green-800 hover:border-green-400 text-green-600 dark:text-green-300',
          'red' => 'border-red-200 dark:border-red-800 hover:border-red-400 text-red-600 dark:text-red-300',
          'yellow' => 'border-yellow-200 dark:border-yellow-800 hover:border-yellow-400 text-yellow-600 dark:text-yellow-300',
          'indigo' => 'border-indigo-200 dark:border-indigo-800 hover:border-indigo-400 text-indigo-600 dark:text-indigo-300',
          'purple' => 'border-purple-200 dark:border-purple-800 hover:border-purple-400 text-purple-600 dark:text-purple-300',
          'teal' => 'border-teal-200 dark:border-teal-800 hover:border-teal-400 text-teal-600 dark:text-teal-300',
          'orange' => 'border-orange-200 dark:border-orange-800 hover:border-orange-400 text-orange-600 dark:text-orange-300',
          'gray' => 'border-slate-200/80 dark:border-slate-800 hover:border-gray-400 text-gray-600 dark:text-gray-300',
          'slate' => 'border-slate-200 dark:border-slate-700 hover:border-slate-400 text-slate-600 dark:text-slate-300',
        ];
        $cls = $colorMap[$qa['color']] ?? $colorMap['blue'];
        ?>
        <a href="<?= $qa['url'] ?>" class="bg-white dark:bg-slate-900 border-2 rounded-lg p-4 transition-all hover:shadow-md <?= $cls ?>">
          <i class="fas <?= $qa['icon'] ?> text-lg"></i>
          <p class="text-sm font-bold mt-2"><?= htmlspecialchars($qa['label']) ?></p>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 hidden lg:block"><?= htmlspecialchars($qa['desc']) ?></p>
        </a>
      <?php endforeach; ?>
    </div>
  </div>

  <?php if (!isset($teknis_view) || !$teknis_view): ?>
  <!-- Charts: Trend 6 Bulan -->
  <div>
    <h3 class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">
      <i class="fas fa-chart-line mr-1 text-blue-500"></i> Trend Pergerakan (6 Bulan Terakhir)
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <?php
      $chartDefs = [
        ['key' => 'incoming', 'title' => 'Barang Masuk', 'bar' => 'bg-green-500 dark:bg-green-400'],
        ['key' => 'outgoing', 'title' => 'Barang Keluar', 'bar' => 'bg-red-500 dark:bg-red-400'],
        ['key' => 'transfer', 'title' => 'Transfer', 'bar' => 'bg-indigo-500 dark:bg-indigo-400'],
      ];
      foreach ($chartDefs as $def):
        $points = $trends[$def['key']] ?? [];
        $max = 1;
        foreach ($points as $pt) { if ($pt['count'] > $max) $max = $pt['count']; }
        $hasData = false;
        foreach ($points as $pt) { if ($pt['count'] > 0) { $hasData = true; break; } }
      ?>
      <div class="bg-white dark:bg-slate-900 rounded-lg shadow-sm border border-slate-200/80 dark:border-slate-800 p-5">
        <div class="flex items-center justify-between mb-4">
          <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200"><?= $def['title'] ?></h4>
          <span class="text-xs text-gray-400"><?= $hasData ? $max . ' max/bln' : '' ?></span>
        </div>
        <?php if ($hasData): ?>
        <div class="flex items-end gap-2 h-36">
          <?php foreach ($points as $pt): ?>
            <div class="flex-1 flex flex-col items-center justify-end h-full">
              <span class="text-xs font-bold text-gray-600 dark:text-gray-300 mb-1"><?= $pt['count'] ?></span>
              <div class="w-full max-w-[30px] rounded-t <?= $def['bar'] ?>" style="height: <?= max(3, (int)round($pt['count'] / $max * 100)) ?>%"></div>
              <span class="text-[10px] text-gray-400 dark:text-gray-500 mt-1 truncate w-full text-center"><?= htmlspecialchars($pt['label']) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
        <?php else: ?>
          <div class="h-36 flex flex-col items-center justify-center text-gray-400 dark:text-gray-500">
            <i class="fas fa-chart-bar text-2xl mb-2"></i>
            <p class="text-sm">Belum ada data <?= strtolower($def['title']) ?>.</p>
          </div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Pergerakan Stok Terakhir -->
  <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm overflow-hidden border border-slate-200/80 dark:border-slate-800">
    <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
      <h3 class="text-base font-semibold text-gray-800 dark:text-white"><i class="fas fa-exchange-alt mr-2 text-blue-500"></i> Pergerakan Stok Terakhir</h3>
      <a href="<?= url('/reports/ledger') ?>" class="text-xs font-semibold text-blue-600 hover:text-blue-500 dark:text-blue-400">Lihat semua →</a>
    </div>
    <?php if (!empty($recentLedger)): ?>
    <div class="divide-y divide-slate-100 dark:divide-slate-800/50">
      <?php foreach ($recentLedger as $lg): ?>
        <div class="px-6 py-3.5 hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-700/50 transition-colors flex items-center gap-3">
          <span class="inline-flex items-center justify-center h-8 w-8 rounded-full <?= $lg['quantity_delta'] >= 0 ? 'bg-green-50 text-green-600 dark:bg-green-900/50 dark:text-green-300' : 'bg-red-50 text-red-600 dark:bg-red-900/50 dark:text-red-300' ?> flex-shrink-0">
            <i class="fas <?= $lg['quantity_delta'] >= 0 ? 'fa-arrow-down' : 'fa-arrow-up' ?> text-xs"></i>
          </span>
          <div class="flex-1 min-w-0">
            <p class="text-sm text-gray-700 dark:text-gray-300 truncate">
              <span class="font-medium"><?= htmlspecialchars($lg['item_name']) ?></span>
            </p>
            <p class="text-xs text-gray-400 dark:text-gray-500">
              <?= htmlspecialchars($lg['warehouse_name']) ?> · <?= htmlspecialchars($lg['transaction_type']) ?> · oleh <?= htmlspecialchars($lg['user_name'] ?? 'Sistem') ?>
            </p>
          </div>
          <span class="text-xs font-bold <?= $lg['quantity_delta'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' ?> whitespace-nowrap">
            <?= $lg['quantity_delta'] >= 0 ? '+' : '' ?><?= number_format($lg['quantity_delta']) ?>
          </span>
          <span class="text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap hidden sm:block"><?= date('d M H:i', strtotime($lg['created_at'])) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
      <div class="px-6 py-8 text-center text-gray-400 dark:text-gray-500 text-sm">
        <i class="fas fa-exchange-alt text-2xl mb-2"></i>
        <p>Belum ada pergerakan stok tercatat.</p>
      </div>
    <?php endif; ?>
  </div>

  <!-- Lists: Pengajuan Aktif & Stok Rendah -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Pengajuan Aktif -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm overflow-hidden border border-slate-200/80 dark:border-slate-800">
      <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
        <h3 class="text-base font-semibold text-gray-800 dark:text-white"><i class="fas fa-clipboard-list mr-2 text-purple-500"></i> Pengajuan Aktif</h3>
        <a href="<?= url('/submissions') ?>" class="text-xs font-semibold text-blue-600 hover:text-blue-500 dark:text-blue-400">Lihat semua →</a>
      </div>
      <?php if (!empty($activeSubmissions)): ?>
      <div class="divide-y divide-slate-100 dark:divide-slate-800/50">
        <?php foreach ($activeSubmissions as $sub): ?>
          <a href="<?= url('/submissions/show?id=' . $sub['id']) ?>" class="px-6 py-3.5 hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-700/50 transition-colors flex items-center gap-3">
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-800 dark:text-gray-200"><?= htmlspecialchars($sub['submission_code']) ?></p>
              <p class="text-xs text-gray-400 dark:text-gray-500"><?= htmlspecialchars($sub['submitter_name']) ?> · <?= date('d M', strtotime($sub['created_at'])) ?></p>
            </div>
            <?php if ($sub['status'] === 'PENDING'): echo dashboardBadge('Menunggu', 'warning'); else: echo dashboardBadge('Disetujui Sebagian', 'info'); endif; ?>
          </a>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
        <div class="px-6 py-8 text-center text-gray-400 dark:text-gray-500 text-sm">
          <i class="fas fa-check-circle text-2xl mb-2 text-green-400"></i>
          <p>Tidak ada pengajuan menunggu proses.</p>
        </div>
      <?php endif; ?>
    </div>

    <!-- Stok Rendah -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm overflow-hidden border border-slate-200/80 dark:border-slate-800">
      <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
        <h3 class="text-base font-semibold text-gray-800 dark:text-white"><i class="fas fa-exclamation-triangle mr-2 text-red-500"></i> Stok Rendah</h3>
        <a href="<?= url('/stocks') ?>" class="text-xs font-semibold text-blue-600 hover:text-blue-500 dark:text-blue-400">Lihat semua →</a>
      </div>
      <?php if (!empty($lowStockList)): ?>
      <div class="divide-y divide-slate-100 dark:divide-slate-800/50">
        <?php foreach ($lowStockList as $ls): ?>
          <div class="px-6 py-3.5 flex items-center gap-3">
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-800 dark:text-gray-200"><?= htmlspecialchars($ls['nama']) ?></p>
              <p class="text-xs text-gray-400 dark:text-gray-500"><?= htmlspecialchars($ls['kode_barang']) ?> · <?= htmlspecialchars($ls['warehouse_name']) ?></p>
            </div>
            <span class="text-xs font-bold text-red-600 dark:text-red-400 whitespace-nowrap">
              <?= number_format($ls['quantity']) ?> / min <?= number_format($ls['stok_minimum']) ?>
            </span>
          </div>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
        <div class="px-6 py-8 text-center text-gray-400 dark:text-gray-500 text-sm">
          <i class="fas fa-check-circle text-2xl mb-2 text-green-400"></i>
          <p>Semua stok di atas batas minimum.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Lists: Outstanding Return & Instalasi Terpasang -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Outstanding Return -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm overflow-hidden border border-slate-200/80 dark:border-slate-800">
      <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
        <h3 class="text-base font-semibold text-gray-800 dark:text-white"><i class="fas fa-hand-holding mr-2 text-orange-500"></i> Belum Dikembalikan</h3>
        <a href="<?= url('/loans') ?>" class="text-xs font-semibold text-blue-600 hover:text-blue-500 dark:text-blue-400">Lihat semua →</a>
      </div>
      <?php if (!empty($outstandingLoans)): ?>
      <div class="divide-y divide-slate-100 dark:divide-slate-800/50">
        <?php foreach ($outstandingLoans as $loan): ?>
          <div class="px-6 py-3.5 flex items-center gap-3">
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-800 dark:text-gray-200"><?= htmlspecialchars($loan['transaction_code']) ?></p>
              <p class="text-xs text-gray-400 dark:text-gray-500"><?= htmlspecialchars($loan['borrower_name']) ?> · <?= htmlspecialchars($loan['warehouse_name']) ?></p>
            </div>
            <?php if ($loan['is_overdue']): ?>
              <?= dashboardBadge('Terlambat', 'danger') ?>
            <?php elseif (!empty($loan['due_date'])): ?>
              <span class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">Jatuh tempo <?= date('d M', strtotime($loan['due_date'])) ?></span>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
        <div class="px-6 py-8 text-center text-gray-400 dark:text-gray-500 text-sm">
          <i class="fas fa-check-circle text-2xl mb-2 text-green-400"></i>
          <p>Tidak ada pinjaman yang belum dikembalikan.</p>
        </div>
      <?php endif; ?>
    </div>

    <!-- Instalasi Terpasang -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm overflow-hidden border border-slate-200/80 dark:border-slate-800">
      <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
        <h3 class="text-base font-semibold text-gray-800 dark:text-white"><i class="fas fa-tools mr-2 text-teal-500"></i> Instalasi Terpasang</h3>
        <a href="<?= url('/installations') ?>" class="text-xs font-semibold text-blue-600 hover:text-blue-500 dark:text-blue-400">Lihat semua →</a>
      </div>
      <?php if (!empty($recentInstallations)): ?>
      <div class="divide-y divide-slate-100 dark:divide-slate-800/50">
        <?php foreach ($recentInstallations as $inst): ?>
          <div class="px-6 py-3.5 flex items-center gap-3">
            <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-teal-50 text-teal-600 dark:bg-teal-900/50 dark:text-teal-300 flex-shrink-0">
              <i class="fas fa-check text-xs"></i>
            </span>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-800 dark:text-gray-200"><?= htmlspecialchars($inst['customer_site'] ?: $inst['customer_name'] ?: '-') ?></p>
              <p class="text-xs text-gray-400 dark:text-gray-500"><?= htmlspecialchars($inst['transaction_code']) ?> · <?= htmlspecialchars($inst['technician_name']) ?></p>
            </div>
            <span class="text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap"><?= date('d M', strtotime($inst['installed_at'])) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
        <div class="px-6 py-8 text-center text-gray-400 dark:text-gray-500 text-sm">
          <i class="fas fa-tools text-2xl mb-2"></i>
          <p>Belum ada aset terpasang.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Damage / Lost -->
  <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm overflow-hidden border border-slate-200/80 dark:border-slate-800">
    <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
      <h3 class="text-base font-semibold text-gray-800 dark:text-white"><i class="fas fa-exclamation-triangle mr-2 text-red-500"></i> Insiden (Damage / Lost)</h3>
      <a href="<?= url('/incidents') ?>" class="text-xs font-semibold text-blue-600 hover:text-blue-500 dark:text-blue-400">Lihat semua →</a>
    </div>
    <?php if (!empty($recentIncidents)): ?>
    <div class="divide-y divide-slate-100 dark:divide-slate-800/50">
      <?php foreach ($recentIncidents as $inc): ?>
        <a href="<?= url('/incidents/show?id=' . $inc['id']) ?>" class="px-6 py-3.5 hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-700/50 transition-colors flex items-center gap-3">
          <span class="inline-flex items-center justify-center h-8 w-8 rounded-full <?= $inc['incident_type'] === 'LOST' ? 'bg-purple-50 text-purple-600 dark:bg-purple-900/50 dark:text-purple-300' : 'bg-red-50 text-red-600 dark:bg-red-900/50 dark:text-red-300' ?> flex-shrink-0">
            <i class="fas <?= $inc['incident_type'] === 'LOST' ? 'fa-question-circle' : 'fa-exclamation' ?> text-xs"></i>
          </span>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-800 dark:text-gray-200"><?= htmlspecialchars($inc['transaction_code']) ?></p>
            <p class="text-xs text-gray-400 dark:text-gray-500"><?= htmlspecialchars($inc['reporter_name'] ?? '-') ?> · <?= date('d M', strtotime($inc['reported_date'])) ?></p>
          </div>
          <?= $inc['incident_type'] === 'LOST' ? dashboardBadge('Lost', 'purple') : dashboardBadge('Damage', 'danger') ?>
          <?php if ($inc['status'] === 'PENDING'): echo dashboardBadge('Terbuka', 'warning'); else: echo dashboardBadge('Selesai', 'success'); endif; ?>
        </a>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
      <div class="px-6 py-8 text-center text-gray-400 dark:text-gray-500 text-sm">
        <i class="fas fa-check-circle text-2xl mb-2 text-green-400"></i>
        <p>Tidak ada insiden Damage/Lost tercatat.</p>
      </div>
    <?php endif; ?>
  </div>

  <!-- Recent Activity -->
  <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm overflow-hidden border border-slate-200/80 dark:border-slate-800">
    <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
      <h3 class="text-base font-semibold text-gray-800 dark:text-white"><i class="fas fa-history mr-2 text-blue-500"></i> Aktivitas Terbaru</h3>
      <a href="<?= url('/reports/audit') ?>" class="text-xs font-semibold text-blue-600 hover:text-blue-500 dark:text-blue-400">Lihat semua →</a>
    </div>
    <div class="divide-y divide-slate-100 dark:divide-slate-800/50">
      <?php if (!empty($recentActivities)): ?>
        <?php foreach ($recentActivities as $activity): ?>
          <div class="px-6 py-3.5 hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-700/50 transition-colors flex items-center space-x-3">
            <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-blue-50 text-blue-600 dark:bg-blue-900/50 dark:text-blue-300 flex-shrink-0">
              <i class="fas fa-history text-xs"></i>
            </span>
            <div class="flex-1 min-w-0">
              <p class="text-sm text-gray-700 dark:text-gray-300">
                <?= formatActivityNarrative($activity) ?>
              </p>
            </div>
            <span class="text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap"><?= date('d M H:i', strtotime($activity['created_at'])) ?></span>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="px-6 py-8 text-center text-gray-400 dark:text-gray-500 text-sm">
          <i class="fas fa-inbox text-2xl mb-2"></i>
          <p>Belum ada aktivitas terbaru.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
  <?php else: ?>
  <!-- Teknisi: Pengajuan Saya, Peminjaman Saya, Instalasi Saya -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm overflow-hidden border border-slate-200/80 dark:border-slate-800">
      <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
        <h3 class="text-base font-semibold text-gray-800 dark:text-white"><i class="fas fa-clipboard-list mr-2 text-purple-500"></i> Pengajuan Saya</h3>
        <a href="<?= url('/submissions') ?>" class="text-xs font-semibold text-blue-600 hover:text-blue-500 dark:text-blue-400">Lihat semua →</a>
      </div>
      <?php if (!empty($mySubmissions)): ?>
      <div class="divide-y divide-slate-100 dark:divide-slate-800/50">
        <?php foreach ($mySubmissions as $sub): ?>
          <a href="<?= url('/submissions/show?id=' . $sub['id']) ?>" class="px-6 py-3.5 hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-700/50 transition-colors flex items-center gap-3">
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-800 dark:text-gray-200"><?= htmlspecialchars($sub['submission_code']) ?></p>
              <p class="text-xs text-gray-400 dark:text-gray-500"><?= date('d M', strtotime($sub['created_at'])) ?></p>
            </div>
            <?php
            if ($sub['status'] === 'PENDING') echo dashboardBadge('Menunggu', 'warning');
            elseif ($sub['status'] === 'PARTIAL_APPROVED') echo dashboardBadge('Sebagian', 'info');
            elseif ($sub['status'] === 'APPROVED') echo dashboardBadge('Disetujui', 'success');
            elseif ($sub['status'] === 'REJECTED') echo dashboardBadge('Ditolak', 'danger');
            else echo dashboardBadge($sub['status'], 'neutral');
            ?>
          </a>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
        <div class="px-6 py-8 text-center text-gray-400 dark:text-gray-500 text-sm">
          <i class="fas fa-clipboard-list text-2xl mb-2"></i>
          <p>Belum ada pengajuan.</p>
        </div>
      <?php endif; ?>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm overflow-hidden border border-slate-200/80 dark:border-slate-800">
      <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
        <h3 class="text-base font-semibold text-gray-800 dark:text-white"><i class="fas fa-hand-holding mr-2 text-orange-500"></i> Peminjaman Saya</h3>
        <a href="<?= url('/loans') ?>" class="text-xs font-semibold text-blue-600 hover:text-blue-500 dark:text-blue-400">Lihat semua →</a>
      </div>
      <?php if (!empty($myLoans)): ?>
      <div class="divide-y divide-slate-100 dark:divide-slate-800/50">
        <?php foreach ($myLoans as $loan): ?>
          <div class="px-6 py-3.5 flex items-center gap-3">
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-800 dark:text-gray-200"><?= htmlspecialchars($loan['transaction_code']) ?></p>
              <p class="text-xs text-gray-400 dark:text-gray-500"><?= htmlspecialchars($loan['warehouse_name']) ?><?= $loan['due_date'] ? ' · jatuh tempo ' . date('d M', strtotime($loan['due_date'])) : '' ?></p>
            </div>
            <?php
            if ($loan['status'] === 'BORROWED') echo dashboardBadge('Dipinjam', 'warning');
            elseif ($loan['status'] === 'RETURNED') echo dashboardBadge('Dikembalikan', 'success');
            else echo dashboardBadge($loan['status'], 'neutral');
            ?>
          </div>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
        <div class="px-6 py-8 text-center text-gray-400 dark:text-gray-500 text-sm">
          <i class="fas fa-hand-holding text-2xl mb-2"></i>
          <p>Belum ada peminjaman.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Instalasi Saya -->
  <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm overflow-hidden border border-slate-200/80 dark:border-slate-800">
    <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
      <h3 class="text-base font-semibold text-gray-800 dark:text-white"><i class="fas fa-tools mr-2 text-teal-500"></i> Instalasi Saya</h3>
      <a href="<?= url('/installations/my_items') ?>" class="text-xs font-semibold text-blue-600 hover:text-blue-500 dark:text-blue-400">Lihat semua →</a>
    </div>
    <?php if (!empty($myInstallations)): ?>
    <div class="divide-y divide-slate-100 dark:divide-slate-800/50">
      <?php foreach ($myInstallations as $inst): ?>
        <div class="px-6 py-3.5 flex items-center gap-3">
          <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-teal-50 text-teal-600 dark:bg-teal-900/50 dark:text-teal-300 flex-shrink-0">
            <i class="fas fa-check text-xs"></i>
          </span>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-800 dark:text-gray-200"><?= htmlspecialchars($inst['customer_site'] ?: '-') ?></p>
            <p class="text-xs text-gray-400 dark:text-gray-500"><?= htmlspecialchars($inst['transaction_code']) ?> · <?= date('d M Y', strtotime($inst['installed_at'])) ?></p>
          </div>
          <?= $inst['status'] === 'INSTALLED' ? dashboardBadge('Terpasang', 'success') : dashboardBadge(\App\Helpers\UIHelper::statusLabel($inst['status']), 'neutral') ?>
        </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
      <div class="px-6 py-8 text-center text-gray-400 dark:text-gray-500 text-sm">
        <i class="fas fa-tools text-2xl mb-2"></i>
        <p>Belum ada instalasi.</p>
      </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
