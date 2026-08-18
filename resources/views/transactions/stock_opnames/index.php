<?php
$title = 'Stock Opname';
$pageTitle = 'Stock Opname';
ob_start();
?>

<div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100"><?= $title ?></h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Audit fisik stok gudang dan penyesuaian selisih.</p>
    </div>
    <?php if (\App\Helpers\AuthHelper::hasRole(['ADMIN'])): ?>
    <a href="<?= url('/opnames/create') ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Buat Stock Opname
    </a>
    <?php endif; ?>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
    <!-- Toolbar -->
    <?php require __DIR__ . '/../../components/table/toolbar.php'; ?>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[1000px] text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                <tr>
                    <th scope="col" class="px-4 py-3 w-16">No</th>
                    <th scope="col" class="px-4 py-3">Kode Opname</th>
                    <th scope="col" class="px-4 py-3">Gudang</th>
                    <th scope="col" class="px-4 py-3">Tanggal / Oleh</th>
                    <th scope="col" class="px-4 py-3">Status</th>
                    <th scope="col" class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($opnames)): ?>
                    <?php
                    $colspan = 6;
                    $emptyMessage = "Belum ada stock opname.";
                    require __DIR__ . '/../../components/table/empty_state.php';
                    ?>
                <?php else: ?>
                    <?php foreach ($opnames as $index => $opname): ?>
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3"><?= $offset + $index + 1 ?></td>
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                            <?= htmlspecialchars($opname['transaction_code']) ?>
                        </td>
                        <td class="px-4 py-3">
                            <?= htmlspecialchars($opname['warehouse_name']) ?>
                        </td>
                        <td class="px-4 py-3">
                            <?= date('d M Y H:i', strtotime($opname['opname_date'])) ?>
                            <br>
                            <span class="text-xs text-gray-500">Oleh: <?= htmlspecialchars($opname['conducted_by_name']) ?></span>
                        </td>
                        <td class="px-4 py-3">
                            <?php 
                                $statusClass = [
                                    'DRAFT' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
                                    'REVIEW' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
                                    'APPROVED' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                                    'REJECTED' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
                                ];
                                $sc = $statusClass[$opname['status']] ?? 'bg-gray-100 text-gray-800';
                            ?>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium <?= $sc ?>">
                                <?= htmlspecialchars(\App\Helpers\UIHelper::statusLabel($opname['status'])) ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <?php
                            $rowActions = [['type' => 'view', 'url' => url('/opnames/show?id=' . (int)$opname['id']), 'label' => 'Lihat', 'icon' => 'fa-eye']];
                            require __DIR__ . '/../../components/table/row_actions.php';
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php require __DIR__ . '/../../components/table/pagination.php'; ?>
</div>

<!-- Page Guide -->
<div class="mt-8 bg-blue-50 dark:bg-blue-900/30 p-4 rounded-lg border border-blue-100 dark:border-blue-800">
    <h3 class="text-blue-800 dark:text-blue-300 font-medium flex items-center mb-2">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        Panduan Stock Opname
    </h3>
    <ul class="list-disc list-inside text-sm text-blue-700 dark:text-blue-400 space-y-1">
        <li>Modul ini digunakan untuk membandingkan stok fisik di gudang dengan stok tercatat di sistem.</li>
        <li>Hanya item yang memiliki selisih (Variance) yang perlu dilaporkan.</li>
        <li>Laporan harus direview dan disetujui (APPROVED) oleh Superadmin agar sistem dapat membuat penyesuaian otomatis.</li>
    </ul>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
