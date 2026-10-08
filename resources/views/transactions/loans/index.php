<?php
$pageTitle = 'Peminjaman Barang';
ob_start();
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$role = \App\Helpers\AuthHelper::user()['role'];
$labels = [
  'PENDING_APPROVAL' => ['Menunggu Persetujuan', 'yellow'],
  'APPROVED' => ['Siap Diserahkan', 'blue'],
  'BORROWED' => ['Sedang Dipinjam', 'orange'],
  'PARTIAL_RETURNED' => ['Kembali Sebagian', 'purple'],
  'RETURNED' => ['Dikembalikan', 'green'],
  'REJECTED' => ['Ditolak', 'red'],
];
?>
<style>
  /* Desktop memakai tabel; mobile memakai kartu transaksi. */
</style>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4 sm:mb-6">
    <div>
      <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Peminjaman Barang</h1>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Permintaan, persetujuan, serah terima, dan pengembalian.</p>
    </div>
    <?php if ($role === 'TEKNISI_NOC'): ?>
      <a href="<?= url('/loans/create') ?>" class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-4 rounded-xl shadow-sm text-sm transition-all w-full sm:w-auto">
        <i class="fas fa-plus text-xs"></i> Peminjaman Baru
      </a>
    <?php endif; ?>
  </div>

  <?php foreach (['success' => 'green', 'error' => 'red'] as $key => $color): ?>
    <?php if (isset($_SESSION[$key])): ?>
      <div class="mb-4 px-4 py-3 rounded-lg border bg-<?= $color ?>-50 border-<?= $color ?>-200 text-<?= $color ?>-700">
        <?= htmlspecialchars($_SESSION[$key]); unset($_SESSION[$key]); ?>
      </div>
    <?php endif; ?>
  <?php endforeach; ?>

  <form method="GET" action="<?= url('/loans') ?>" class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-4 mb-5 flex gap-3 flex-wrap items-end">
    <div class="flex-1 min-w-[240px]">
      <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Pencarian</label>
      <input name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" placeholder="Cari nama peminjam / gudang..." class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 dark:bg-gray-700 dark:text-white">
    </div>
    <div>
      <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Status</label>
      <select name="status" class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 dark:bg-gray-700 dark:text-white">
        <option value="">Semua Status</option>
        <?php foreach ($labels as $value => $meta): ?>
          <option value="<?= $value ?>" <?= (($_GET['status'] ?? '') === $value) ? 'selected' : '' ?>><?= $meta[0] ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button class="bg-slate-800 text-white px-4 py-2 rounded-lg font-semibold"><i class="fas fa-search mr-2"></i>Cari</button>
    <a href="<?= url('/loans') ?>" class="bg-gray-100 dark:bg-gray-700 px-4 py-2 rounded-lg">Reset</a>
  </form>

  <div class="bg-white dark:bg-gray-800 shadow rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700">
    <!-- Desktop: tabel penuh -->
    <div class="loan-desktop-view hidden sm:block overflow-x-auto">
      <table class="w-full min-w-[800px] divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700">
          <tr>
            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">No</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Tanggal</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Peminjam</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Gudang</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Jatuh Tempo</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Status</th>
            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
        <?php $no = $offset + 1; foreach ($loans as $ln):
          $statusMeta = $labels[$ln['status']] ?? [$ln['status'], 'gray'];
          $isOverdue = !empty($ln['due_date']) && time() > strtotime($ln['due_date']) && !in_array($ln['status'], ['RETURNED','REJECTED'], true);
        ?>
          <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
            <td class="px-4 py-4 text-center text-sm text-gray-500 dark:text-gray-400"><?= $no++ ?></td>
            <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300"><?= date('d M Y', strtotime($ln['loan_date'])) ?></td>
            <td class="px-4 py-4 text-sm font-semibold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($ln['borrower_name']) ?></td>
            <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300"><?= htmlspecialchars($ln['warehouse_name']) ?></td>
            <td class="px-4 py-4 text-sm <?= $isOverdue ? 'text-red-600 font-bold' : 'text-gray-600 dark:text-gray-300' ?>">
              <?php if (!empty($ln['due_date'])): ?>
                <?= date('d M Y', strtotime($ln['due_date'])) ?>
                <?php if ($isOverdue): ?><div class="text-xs mt-1">Terlambat</div><?php endif; ?>
              <?php else: ?>-
              <?php endif; ?>
            </td>
            <td class="px-4 py-4">
              <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-<?= $statusMeta[1] ?>-100 text-<?= $statusMeta[1] ?>-800">
                <?= htmlspecialchars($statusMeta[0]) ?>
              </span>
            </td>
            <td class="px-4 py-4">
              <div class="flex flex-wrap gap-2 justify-center">
                <a href="<?= url('/loans/show?id=' . (int)$ln['id']) ?>" class="inline-flex items-center px-3 py-2 rounded-lg border border-blue-200 text-blue-700 bg-blue-50 hover:bg-blue-100 text-sm font-semibold">
                  <i class="fas fa-eye mr-1"></i>Lihat
                </a>

                <?php if ($role === 'ADMIN' && $ln['status'] === 'PENDING_APPROVAL'): ?>
                  <form method="POST" action="<?= url('/loans/approve') ?>" onsubmit="return confirm('Setujui peminjaman ini?');">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="id" value="<?= (int)$ln['id'] ?>">
                    <button class="inline-flex items-center px-3 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-semibold"><i class="fas fa-check mr-1"></i>Setujui</button>
                  </form>
                  <button type="button" onclick="openReject(<?= (int)$ln['id'] ?>)" class="inline-flex items-center px-3 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm font-semibold"><i class="fas fa-times mr-1"></i>Tolak</button>
                <?php elseif ($role === 'ADMIN' && $ln['status'] === 'APPROVED'): ?>
                  <form method="POST" action="<?= url('/loans/handover') ?>" onsubmit="return confirm('Konfirmasi barang benar-benar sudah diserahkan kepada teknisi? Stok akan berkurang.');">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="id" value="<?= (int)$ln['id'] ?>">
                    <button class="inline-flex items-center px-3 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold"><i class="fas fa-handshake mr-1"></i>Serahkan</button>
                  </form>
                <?php elseif ($role === 'TEKNISI_NOC' && in_array($ln['status'], ['BORROWED','PARTIAL_RETURNED'], true)): ?>
                  <a href="<?= url('/returns/create?loan_id=' . (int)$ln['id']) ?>" class="inline-flex items-center px-3 py-2 rounded-lg bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold">
                    <i class="fas fa-undo mr-1"></i>Ajukan Pengembalian
                  </a>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($loans)): ?>
          <?php $colspan = 7; $emptyMessage ="Belum ada transaksi peminjaman."; require __DIR__ . '/../../components/table/empty_state.php'; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Mobile: card transaksi, tanpa horizontal scroll -->
    <div class="loan-mobile-view block sm:hidden p-3 space-y-3">
      <?php if (empty($loans)): ?>
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/60 px-4 py-8 text-center">
          <i class="fas fa-inbox text-2xl text-gray-300 dark:text-gray-600"></i>
          <p class="mt-3 text-sm font-medium text-gray-500 dark:text-gray-400">Belum ada transaksi peminjaman.</p>
        </div>
      <?php else: ?>
        <?php foreach ($loans as $ln):
          $statusMeta = $labels[$ln['status']] ?? [$ln['status'], 'gray'];
          $isOverdue = !empty($ln['due_date']) && time() > strtotime($ln['due_date']) && !in_array($ln['status'], ['RETURNED','REJECTED'], true);
        ?>
          <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm">
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wide">Peminjam</p>
                <h3 class="mt-1 text-base font-bold text-gray-900 dark:text-white break-words"><?= htmlspecialchars($ln['borrower_name']) ?></h3>
              </div>
              <span class="shrink-0 inline-flex max-w-[48%] px-2.5 py-1 rounded-full text-[11px] leading-4 font-bold text-center bg-<?= $statusMeta[1] ?>-100 text-<?= $statusMeta[1] ?>-800">
                <?= htmlspecialchars($statusMeta[0]) ?>
              </span>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-x-4 gap-y-3">
              <div>
                <p class="text-[11px] font-semibold text-gray-400 dark:text-gray-500">Tanggal</p>
                <p class="mt-0.5 text-sm font-medium text-gray-700 dark:text-gray-200"><?= date('d M Y', strtotime($ln['loan_date'])) ?></p>
              </div>
              <div>
                <p class="text-[11px] font-semibold text-gray-400 dark:text-gray-500">Gudang</p>
                <p class="mt-0.5 text-sm font-medium text-gray-700 dark:text-gray-200 break-words"><?= htmlspecialchars($ln['warehouse_name']) ?></p>
              </div>
              <div class="col-span-2">
                <p class="text-[11px] font-semibold text-gray-400 dark:text-gray-500">Jatuh Tempo</p>
                <?php if (!empty($ln['due_date'])): ?>
                  <p class="mt-0.5 text-sm font-semibold <?= $isOverdue ? 'text-red-600' : 'text-gray-700 dark:text-gray-200' ?>">
                    <?= date('d M Y', strtotime($ln['due_date'])) ?>
                    <?php if ($isOverdue): ?><span class="ml-1 text-xs">• Terlambat</span><?php endif; ?>
                  </p>
                <?php else: ?>
                  <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Tidak ditentukan</p>
                <?php endif; ?>
              </div>
            </div>

            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700 flex flex-wrap gap-2">
              <a href="<?= url('/loans/show?id=' . (int)$ln['id']) ?>" class="flex-1 min-w-[110px] inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-lg border border-blue-200 text-blue-700 bg-blue-50 hover:bg-blue-100 text-sm font-semibold">
                <i class="fas fa-eye"></i>Lihat
              </a>

              <?php if ($role === 'ADMIN' && $ln['status'] === 'PENDING_APPROVAL'): ?>
                <form method="POST" action="<?= url('/loans/approve') ?>" class="flex-1 min-w-[110px]" onsubmit="return confirm('Setujui peminjaman ini?');">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                  <input type="hidden" name="id" value="<?= (int)$ln['id'] ?>">
                  <button class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-semibold"><i class="fas fa-check"></i>Setujui</button>
                </form>
                <button type="button" onclick="openReject(<?= (int)$ln['id'] ?>)" class="flex-1 min-w-[110px] inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm font-semibold"><i class="fas fa-times"></i>Tolak</button>
              <?php elseif ($role === 'ADMIN' && $ln['status'] === 'APPROVED'): ?>
                <form method="POST" action="<?= url('/loans/handover') ?>" class="flex-1 min-w-[110px]" onsubmit="return confirm('Konfirmasi barang benar-benar sudah diserahkan kepada teknisi? Stok akan berkurang.');">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                  <input type="hidden" name="id" value="<?= (int)$ln['id'] ?>">
                  <button class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold"><i class="fas fa-handshake"></i>Serahkan</button>
                </form>
              <?php elseif ($role === 'TEKNISI_NOC' && in_array($ln['status'], ['BORROWED','PARTIAL_RETURNED'], true)): ?>
                <a href="<?= url('/returns/create?loan_id=' . (int)$ln['id']) ?>" class="flex-1 min-w-[110px] inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-lg bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold">
                  <i class="fas fa-undo"></i>Ajukan Pengembalian
                </a>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <div class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 border-t">Menampilkan total <?= (int)$totalItems ?> data</div>
  </div>

<div id="rejectModal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
  <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-6">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Tolak Peminjaman</h3>
      <button type="button" onclick="closeReject()" class="text-gray-400 hover:text-gray-600 dark:text-gray-300 dark:hover:text-gray-200"><i class="fas fa-times"></i></button>
    </div>
    <form method="POST" action="<?= url('/loans/reject') ?>">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
      <input type="hidden" name="id" id="rejectLoanId">
      <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Alasan penolakan</label>
      <textarea name="approval_notes" required rows="4" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-gray-900 dark:text-gray-100 dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Jelaskan alasan penolakan..."></textarea>
      <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
        <button type="button" onclick="closeReject()" class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-800 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200 font-bold text-sm">Batal</button>
        <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white font-bold text-sm">Tolak Peminjaman</button>
      </div>
    </form>
  </div>
</div>
<script>
function openReject(id){const m=document.getElementById('rejectModal');document.getElementById('rejectLoanId').value=id;m.classList.remove('hidden');m.classList.add('flex');}
function closeReject(){const m=document.getElementById('rejectModal');m.classList.add('hidden');m.classList.remove('flex');}
</script>
<?php $content = ob_get_clean(); require __DIR__ . '/../../layouts/app.php'; ?>
