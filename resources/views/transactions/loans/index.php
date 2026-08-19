<?php
$pageTitle = 'Peminjaman Barang';
ob_start();
$role = \App\Helpers\AuthHelper::user()['role'] ?? '';
$status = trim($_GET['status'] ?? '');
$search = trim($_GET['search'] ?? '');

$statusOptions = [
    '' => 'Semua Status',
    'PENDING_APPROVAL' => 'Menunggu Persetujuan',
    'APPROVED' => 'Siap Diserahkan',
    'BORROWED' => 'Sedang Dipinjam',
    'PARTIAL_RETURNED' => 'Kembali Sebagian',
    'RETURNED' => 'Dikembalikan',
    'REJECTED' => 'Ditolak',
];
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-5 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Peminjaman Barang</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Kelola permintaan, persetujuan, serah terima, dan pengembalian barang teknisi.</p>
        </div>
        <?php if ($role === 'TEKNISI_NOC'): ?>
            <a href="<?= url('/loans/create') ?>" class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2.5 rounded-lg shadow-sm whitespace-nowrap">
                <i class="fas fa-plus mr-2"></i> Peminjaman Baru
            </a>
        <?php endif; ?>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:bg-green-900/20 dark:border-green-800 dark:text-green-300">
            <?= htmlspecialchars($_SESSION['success']) ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:bg-red-900/20 dark:border-red-800 dark:text-red-300">
            <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form method="GET" action="<?= url('/loans') ?>" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm p-4 mb-5">
        <div class="grid grid-cols-1 md:grid-cols-[1fr_260px_auto_auto] gap-3 items-end">
            <div>
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1.5">Pencarian</label>
                <input
                    type="text"
                    name="search"
                    value="<?= htmlspecialchars($search) ?>"
                    placeholder="Cari nama peminjam atau gudang..."
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1.5">Status</label>
                <select name="status" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <?php foreach ($statusOptions as $value => $label): ?>
                        <option value="<?= htmlspecialchars($value) ?>" <?= $status === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-slate-800 hover:bg-slate-900 text-white px-4 py-2.5 text-sm font-semibold">
                <i class="fas fa-search mr-2"></i> Cari
            </button>
            <a href="<?= url('/loans') ?>" class="inline-flex items-center justify-center rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2.5 text-sm font-semibold dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600">
                <i class="fas fa-undo mr-2"></i> Reset
            </a>
        </div>
    </form>

    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-5">
        <?php
        $summaryMap = [
            'PENDING_APPROVAL' => ['Menunggu', 'amber', 'fa-clock'],
            'APPROVED' => ['Siap Diserahkan', 'blue', 'fa-box-open'],
            'BORROWED' => ['Sedang Dipinjam', 'orange', 'fa-hand-holding'],
            'PARTIAL_RETURNED' => ['Kembali Sebagian', 'purple', 'fa-rotate-left'],
            'RETURNED' => ['Selesai', 'green', 'fa-check-circle'],
        ];
        foreach ($summaryMap as $key => [$label, $tone, $icon]):
            $cardUrl = url('/loans?status=' . urlencode($key));
        ?>
            <a href="<?= $cardUrl ?>" class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm hover:shadow transition">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400"><?= htmlspecialchars($label) ?></div>
                        <div class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">
                            <?php
                            $count = 0;
                            foreach ($loans as $row) {
                                if (($row['status'] ?? '') === $key) $count++;
                            }
                            echo $count;
                            ?>
                        </div>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-<?= $tone ?>-50 text-<?= $tone ?>-600 dark:bg-<?= $tone ?>-900/20 dark:text-<?= $tone ?>-300 flex items-center justify-center">
                        <i class="fas <?= $icon ?>"></i>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-bold text-gray-900 dark:text-gray-100">Daftar Peminjaman</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pilih <strong>Lihat</strong> untuk memeriksa detail, status, barang, dan riwayat.</p>
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Total <?= (int)$totalItems ?> data</div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[980px]">
                <thead class="bg-gray-50 dark:bg-gray-900/60">
                    <tr>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase w-14">No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Peminjam</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Gudang</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Jatuh Tempo</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <?php $no = $offset + 1; foreach ($loans as $ln): ?>
                        <?php
                        $statusKey = $ln['status'] ?? '';
                        $statusMeta = [
                            'PENDING_APPROVAL' => ['Menunggu Persetujuan', 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300'],
                            'APPROVED' => ['Siap Diserahkan', 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300'],
                            'BORROWED' => ['Sedang Dipinjam', 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300'],
                            'PARTIAL_RETURNED' => ['Kembali Sebagian', 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300'],
                            'RETURNED' => ['Dikembalikan', 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'],
                            'REJECTED' => ['Ditolak', 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300'],
                        ][$statusKey] ?? [$statusKey ?: 'Tidak Diketahui', 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200'];
                        $dueText = '-';
                        $overdue = false;
                        if (!empty($ln['due_date'])) {
                            $due = strtotime($ln['due_date']);
                            $overdue = time() > $due && !in_array($statusKey, ['RETURNED', 'REJECTED'], true);
                            $dueText = date('d M Y', $due);
                        }
                        ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors">
                            <td class="px-4 py-4 text-sm text-center text-gray-500 dark:text-gray-400"><?= $no++ ?></td>
                            <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                <div class="font-medium"><?= date('d M Y', strtotime($ln['loan_date'])) ?></div>
                                <div class="text-xs text-gray-400 mt-0.5"><?= date('H:i', strtotime($ln['loan_date'])) ?></div>
                            </td>
                            <td class="px-4 py-4 text-sm font-semibold text-gray-900 dark:text-gray-100">
                                <?= htmlspecialchars($ln['borrower_name']) ?>
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-300">
                                <?= htmlspecialchars($ln['warehouse_name']) ?>
                            </td>
                            <td class="px-4 py-4 text-sm <?= $overdue ? 'text-red-600 font-bold' : 'text-gray-700 dark:text-gray-300' ?> whitespace-nowrap">
                                <?= htmlspecialchars($dueText) ?>
                                <?php if ($overdue): ?><div class="text-xs mt-0.5">Terlambat</div><?php endif; ?>
                            </td>
                            <td class="px-4 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold <?= $statusMeta[1] ?>">
                                    <?= htmlspecialchars($statusMeta[0]) ?>
                                </span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <a href="<?= url('/loans/show?id=' . (int)$ln['id']) ?>" class="inline-flex items-center justify-center rounded-lg border border-blue-200 bg-blue-50 hover:bg-blue-100 text-blue-700 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-300 px-3 py-1.5 text-sm font-semibold">
                                    <i class="fas fa-eye mr-1.5"></i> Lihat
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($loans)): ?>
                        <tr>
                            <td colspan="7" class="px-5 py-14 text-center">
                                <div class="text-gray-400 dark:text-gray-500"><i class="fas fa-clipboard-list text-3xl"></i></div>
                                <div class="mt-3 text-sm font-semibold text-gray-600 dark:text-gray-300">Belum ada peminjaman</div>
                                <div class="mt-1 text-xs text-gray-400">Peminjaman baru akan muncul di sini setelah dibuat.</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php
        $paginationBaseUrl = url('/loans');
        require __DIR__ . '/../../components/table/pagination.php';
        ?>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
