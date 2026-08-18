<?php
$pageTitle = $config['title'];
ob_start();
?>

<style>
/* Shared report table: long values must wrap instead of being clipped/truncated. */
.report-data-wrap {
    overflow-x: auto;
}

.report-data-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: auto;
}

.report-data-table th,
.report-data-table td {
    white-space: normal !important;
    overflow-wrap: anywhere;
    word-break: break-word;
    vertical-align: top;
}

/* Keep the report row-number column readable: never wrap "No" into N/O. */
    .report-data-table th:nth-child(2),
    .report-data-table td:nth-child(2) {
        width: 56px !important;
        min-width: 56px !important;
        max-width: 56px !important;
        padding-left: 8px !important;
        padding-right: 8px !important;
        white-space: nowrap !important;
        word-break: normal !important;
        overflow-wrap: normal !important;
    }

    .report-data-table th {
        line-height: 1.25;
}

.report-data-table td {
    line-height: 1.35;
}

@media print {
    .report-data-wrap {
        overflow: visible !important;
    }

    .report-data-table {
        width: 100% !important;
        min-width: 0 !important;
        table-layout: fixed !important;
        font-size: 8px !important;
    }

    .report-data-table th,
    .report-data-table td {
        white-space: normal !important;
        overflow-wrap: anywhere !important;
        word-break: break-word !important;
        padding: 4px 5px !important;
        line-height: 1.25 !important;
        vertical-align: top !important;
    }

    .report-data-table thead {
        display: table-header-group;
    }

    .report-data-table tr {
        page-break-inside: avoid;
        break-inside: avoid;
    }
}
</style>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 fade-in">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4 border-b border-gray-200 dark:border-gray-800 pb-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($pageTitle) ?></h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Analisis dan data laporan sistem terintegrasi.</p>
        </div>
        <div class="flex gap-2 no-print">
            <button onclick="window.print()" class="bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold py-2 px-4 rounded shadow text-sm">
                <i class="fas fa-print mr-2"></i> Cetak
            </button>
        </div>
    </div>

    <!-- Print-only header: judul + periode + filter + timestamp (Dedicated Print Layout) -->
    <div class="print-header">
        <h1 style="font-size:18px;font-weight:700;margin:0 0 4px;"><?= htmlspecialchars($pageTitle) ?></h1>
        <div style="font-size:11px;color:#374151;margin-bottom:2px;">
            Periode: <?= htmlspecialchars(($_GET['start_date'] ?? '') ?: 'Awal') ?> s/d <?= htmlspecialchars(($_GET['end_date'] ?? '') ?: 'Sekarang') ?><?= !empty($_GET['warehouse_id']) ? ' | Gudang: ' . htmlspecialchars($_GET['warehouse_id']) : '' ?>
        </div>
        <div style="font-size:11px;color:#374151;margin-bottom:10px;">
            <?= !empty($_GET['search']) ? 'Pencarian: ' . htmlspecialchars($_GET['search']) . ' | ' : '' ?>Dicetak: <?= date('d/m/Y H:i') ?>
        </div>
        <hr style="border:0;border-top:1px solid #9ca3af;margin-bottom:10px;">
    </div>

    <!-- Print-only footer: timestamp + halaman -->
    <div class="print-footer">
        <?= htmlspecialchars($pageTitle) ?> — Dicetak <?= date('d/m/Y H:i') ?> | Halaman <span class="page-count"></span>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6 border border-gray-200 dark:border-gray-700 print:hidden">
        <form method="GET" action="" id="reportFilterForm">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Pencarian</label>
                    <input type="text" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" placeholder="Cari kode, nama, serial..." class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg p-2 text-sm text-gray-700 dark:text-gray-300">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="<?= htmlspecialchars($_GET['start_date'] ?? '') ?>" class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg p-2 text-sm text-gray-700 dark:text-gray-300">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Tanggal Akhir</label>
                    <input type="date" name="end_date" value="<?= htmlspecialchars($_GET['end_date'] ?? '') ?>" class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg p-2 text-sm text-gray-700 dark:text-gray-300">
                </div>
                <?php if (isset($config['warehouse_column'])): ?>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Gudang</label>
                    <select name="warehouse_id" class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg p-2 text-sm text-gray-700 dark:text-gray-300">
                        <option value="">Semua Gudang</option>
                        <?php foreach ($warehouses as $wh): ?>
                            <option value="<?= $wh['id'] ?>" <?= (($_GET['warehouse_id'] ?? '') == $wh['id']) ? 'selected' : '' ?>><?= htmlspecialchars($wh['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                <?php if (isset($config['category_column'])): ?>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Kategori</label>
                    <select name="category_id" class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg p-2 text-sm text-gray-700 dark:text-gray-300">
                        <option value="">Semua Kategori</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= (($_GET['category_id'] ?? '') == $cat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                <?php if (isset($config['user_column'])): ?>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Oleh (User)</label>
                    <select name="user_id" class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg p-2 text-sm text-gray-700 dark:text-gray-300">
                        <option value="">Semua User</option>
                        <?php foreach ($users as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= (($_GET['user_id'] ?? '') == $u['id']) ? 'selected' : '' ?>><?= htmlspecialchars($u['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                <?php if (isset($config['action_column']) && !empty($actions)): ?>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Aksi</label>
                    <select name="action_type" class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg p-2 text-sm text-gray-700 dark:text-gray-300">
                        <option value="">Semua Aksi</option>
                        <?php foreach ($actions as $act): ?>
                            <option value="<?= htmlspecialchars($act) ?>" <?= (($_GET['action_type'] ?? '') === $act) ? 'selected' : '' ?>><?= htmlspecialchars($act) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow text-sm flex items-center justify-center min-w-[110px]" onclick="this.innerHTML='<i class=\'fas fa-spinner fa-spin mr-2\'></i>...';">
                    <i class="fas fa-search mr-2"></i> Cari
                </button>
                <a href="?" class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold py-2 px-6 rounded shadow text-sm">
                    <i class="fas fa-undo mr-2"></i> Atur Ulang
                </a>
                <button type="submit" name="export" value="csv" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded shadow text-sm ml-auto">
                    <i class="fas fa-file-csv mr-2"></i> Export CSV (Semua Hasil)
                </button>
                <button type="submit" name="export" value="pdf" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded shadow text-sm">
                    <i class="fas fa-file-pdf mr-2"></i> Export PDF (Semua Hasil)
                </button>
            </div>
        </form>
    </div>

    <!-- Data Table / Empty State -->
    <?php if (empty($data)): ?>
        <?php
        $emptyPanel = true;
        $emptyMessage = "Belum ada data untuk laporan ini.";
        $emptySubMessage = "Coba ubah rentang tanggal / filter untuk melihat data.";
        require __DIR__ . '/../components/table/empty_state.php';
        unset($emptyPanel, $emptyMessage, $emptySubMessage);
        ?>

    <?php else: ?>
        <!-- Summary Box -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-5 border border-gray-200 dark:border-gray-700">
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Total Baris Data</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100"><?= number_format($totalItems ?? count($data)) ?></div>
            </div>
        </div>

        <!-- Selection Toolbar (no-print) -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 mb-4 border border-gray-200 dark:border-gray-700 no-print">
            <form method="GET" action="" id="selectionForm" class="flex flex-wrap items-center gap-3">
                <?php foreach (['search','start_date','end_date','warehouse_id','category_id','user_id','action_type'] as $fk): ?>
                    <?php if (isset($_GET[$fk]) && $_GET[$fk] !== ''): ?>
                        <input type="hidden" name="<?= $fk ?>" value="<?= htmlspecialchars($_GET[$fk]) ?>">
                    <?php endif; ?>
                <?php endforeach; ?>
                <input type="hidden" name="select_all_filtered" id="selectAllFiltered" value="">
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Pilih baris:</span>
                <button type="button" id="btnSelectPage" class="bg-indigo-100 dark:bg-indigo-900/40 hover:bg-indigo-200 dark:hover:bg-indigo-800/60 text-indigo-700 dark:text-indigo-300 font-semibold py-1.5 px-3 rounded text-xs">
                    <i class="fas fa-check-square mr-1"></i> Pilih Semua Tampil
                </button>
                <button type="button" id="btnSelectAllFiltered" class="bg-indigo-100 dark:bg-indigo-900/40 hover:bg-indigo-200 dark:hover:bg-indigo-800/60 text-indigo-700 dark:text-indigo-300 font-semibold py-1.5 px-3 rounded text-xs">
                    <i class="fas fa-check-double mr-1"></i> Pilih Semua Hasil Filter
                </button>
                <button type="button" id="btnClearSelection" class="bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-semibold py-1.5 px-3 rounded text-xs">
                    <i class="fas fa-undo mr-1"></i> Bersihkan Pilihan
                </button>
                <span id="selectedCount" class="text-sm font-bold text-gray-800 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 rounded-full px-3 py-1">0 data dipilih</span>
                <span class="flex-1"></span>
                <button type="submit" name="print_selected" value="1" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded shadow text-sm">
                    <i class="fas fa-print mr-1"></i> Cetak Terpilih
                </button>
                <button type="submit" name="export" value="csv" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded shadow text-sm">
                    <i class="fas fa-file-csv mr-1"></i> Export CSV Terpilih
                </button>
                <button type="submit" name="export" value="pdf" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded shadow text-sm">
                    <i class="fas fa-file-pdf mr-1"></i> Export PDF Terpilih
                </button>
            </form>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
            <div class="report-data-wrap">
                <table class="report-data-table w-full <?= count($headers) >= 6 ? 'min-w-[1000px]' : 'min-w-full' ?> divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-12 no-print">
                                <input type="checkbox" id="checkAllPage" form="selectionForm" class="rounded border-gray-300 dark:border-gray-600">
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-12">No</th>
                            <?php foreach ($headers as $h): ?>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider"><?= htmlspecialchars($h) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <?php 
                        $no = ($offset ?? 0) + 1;
                        foreach ($data as $row): 
                        ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-4 py-3 text-sm text-center text-gray-500 dark:text-gray-400 no-print">
                                    <input type="checkbox" name="selected_ids[]" form="selectionForm" value="<?= (int)($row['id'] ?? 0) ?>" class="row-check rounded border-gray-300 dark:border-gray-600">
                                </td>
                                <td class="px-4 py-3 text-sm text-center text-gray-500 dark:text-gray-400"><?= $no++ ?></td>
                                <?php foreach ($columns as $col): ?>
                                    <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                        <?php 
                                            $val = $row[$col] ?? '-';
                                            if (($col === 'created_at' || $col === 'sent_at' || $col === 'received_at' || $col === 'installed_at' || $col === 'loan_date_display') && $val !== '-') {
                                                echo date('d M Y, H:i', strtotime($val));
                                            } else {
                                                echo htmlspecialchars($val);
                                            }
                                        ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php 
            $paginationBaseUrl = strtok($_SERVER['REQUEST_URI'] ?? '', '?');
            require __DIR__ . '/../components/table/pagination.php'; 
            ?>
        </div>
    <?php endif; ?>
</div>

<?php if (!empty($autoPrint)): ?>
<script>
window.addEventListener('load', function () {
    setTimeout(function () { window.print(); }, 300);
});
</script>
<?php endif; ?>

<script>
(function () {
    var form = document.getElementById('selectionForm');
    var rows = function () { return Array.prototype.slice.call(document.querySelectorAll('.row-check')); };
    var countSel = function () {
        var n = rows().filter(function (c) { return c.checked; }).length;
        var el = document.getElementById('selectedCount');
        if (el) el.textContent = n + ' data dipilih';
    };
    var checkAll = document.getElementById('checkAllPage');
    if (checkAll) {
        checkAll.addEventListener('change', function () {
            rows().forEach(function (c) { c.checked = checkAll.checked; });
            countSel();
        });
    }
    rows().forEach(function (c) { c.addEventListener('change', countSel); });
    var btnPage = document.getElementById('btnSelectPage');
    if (btnPage) btnPage.addEventListener('click', function () {
        rows().forEach(function (c) { c.checked = true; });
        if (checkAll) checkAll.checked = true;
        document.getElementById('selectAllFiltered').value = '';
        countSel();
    });
    var btnAll = document.getElementById('btnSelectAllFiltered');
    if (btnAll) btnAll.addEventListener('click', function () {
        rows().forEach(function (c) { c.checked = true; });
        if (checkAll) checkAll.checked = true;
        document.getElementById('selectAllFiltered').value = '1';
        countSel();
    });
    var btnClear = document.getElementById('btnClearSelection');
    if (btnClear) btnClear.addEventListener('click', function () {
        rows().forEach(function (c) { c.checked = false; });
        if (checkAll) checkAll.checked = false;
        document.getElementById('selectAllFiltered').value = '';
        countSel();
    });
    if (form) {
        form.addEventListener('submit', function (e) {
            var allF = document.getElementById('selectAllFiltered').value === '1';
            if (!allF && rows().filter(function (c) { return c.checked; }).length === 0) {
                e.preventDefault();
                AppToast.warning('Belum ada baris yang dipilih. Centang minimal satu baris, atau gunakan \'Pilih Semua Hasil Filter\'.');
                return false;
            }
        });
    }
})();
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
