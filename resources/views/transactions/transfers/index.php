<?php
$pageTitle = 'Transfer Gudang';
ob_start();
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div><h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Transfer Gudang</h1><p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Kelola transfer stok internal antar gudang.</p></div>
        <?php if (!\App\Helpers\AuthHelper::hasRole('SUPERADMIN')): ?><a href="<?= url('/transfers/create') ?>" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow whitespace-nowrap inline-flex items-center"><i class="fas fa-plus mr-2"></i> Transfer Baru</a><?php endif; ?>
    </div>
    <?php if (isset($_SESSION['success'])): ?><div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div><?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?><div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div><?php endif; ?>
    <?php $toolbarAction=url('/transfers'); $searchPlaceholder='Cari Kode Transaksi...'; ob_start(); ?>
    <div><label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Status</label><select name="status" class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-40 py-2 px-3 text-sm text-gray-700 dark:text-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500"><option value="">Semua Status</option><option value="IN_TRANSIT" <?= (($_GET['status'] ?? '')==='IN_TRANSIT')?'selected':'' ?>><?= htmlspecialchars(\App\Helpers\UIHelper::statusLabel('IN_TRANSIT')) ?></option><option value="PARTIAL_RECEIVED" <?= (($_GET['status'] ?? '')==='PARTIAL_RECEIVED')?'selected':'' ?>><?= htmlspecialchars(\App\Helpers\UIHelper::statusLabel('PARTIAL_RECEIVED')) ?></option><option value="COMPLETED" <?= (($_GET['status'] ?? '')==='COMPLETED')?'selected':'' ?>><?= htmlspecialchars(\App\Helpers\UIHelper::statusLabel('COMPLETED')) ?></option></select></div>
    <?php $extraFilters=ob_get_clean(); require __DIR__.'/../../components/table/toolbar.php'; ?>
    <div class="overflow-x-auto bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700"><div class="overflow-x-auto"><table class="w-full min-w-[1000px] table-sticky divide-y divide-gray-200 dark:divide-gray-700"><thead class="bg-gray-50 dark:bg-gray-900/50"><tr>
        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase w-12">No</th><th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Tanggal</th><th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Kode</th><th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Dari Gudang</th><th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Ke Gudang</th><th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Pengirim</th><th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Status</th><th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Aksi</th>
    </tr></thead><tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
    <?php $no=$offset+1; foreach($transfers as $trf): ?>
    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
        <td class="px-4 py-3 text-sm text-center text-gray-500 dark:text-gray-400"><?= $no++ ?></td><td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap"><?= date('d M Y H:i',strtotime($trf['sent_at'])) ?></td><td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100"><?= htmlspecialchars($trf['transaction_code']) ?></td><td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars($trf['source_warehouse']) ?></td><td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars($trf['dest_warehouse']) ?></td><td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars($trf['sender_name']) ?></td>
        <td class="px-4 py-3 text-sm text-center"><?php $badgeType=$trf['status']==='IN_TRANSIT'?'INFO':($trf['status']==='COMPLETED'?'SUCCESS':'NEUTRAL'); ?><?= \App\Helpers\UIHelper::badge($trf['status'],$badgeType) ?></td>
        <td class="px-4 py-3 text-sm text-center">
            <div class="flex items-center justify-center gap-3">
                <a href="<?= url('/transfers/show?id='.(int)$trf['id']) ?>" class="text-blue-600 hover:text-blue-800 inline-flex items-center"><i class="fas fa-eye mr-1"></i>Lihat</a>
                <?php if ($trf['status']==='IN_TRANSIT' && \App\Helpers\AuthHelper::hasRole('ADMIN')): ?>
                <form action="<?= url('/transfers/delete') ?>" method="POST" class="inline">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"><input type="hidden" name="id" value="<?= (int)$trf['id'] ?>">
                    <button type="submit" class="text-red-600 hover:text-red-800 inline-flex items-center" data-confirm="Transfer ini akan dibatalkan dan stok dikembalikan ke gudang asal. Lanjutkan?" data-confirm-title="Hapus Transfer" data-confirm-type="danger" data-confirm-icon="fa-trash" data-confirm-confirm="Hapus" data-confirm-loading="Menghapus..."><i class="fas fa-trash mr-1"></i>Hapus</button>
                </form>
                <?php endif; ?>
            </div>
        </td>
    </tr>
    <?php endforeach; ?>
    <?php if(empty($transfers)): ?><?php $colspan=8;$emptyMessage='Belum ada transaksi transfer.';require __DIR__.'/../../components/table/empty_state.php'; ?><?php endif; ?>
    </tbody></table></div>
    <?php $paginationBaseUrl=url('/transfers'); require __DIR__.'/../../components/table/pagination.php'; ?>
    </div>
</div>
<?php $content=ob_get_clean(); require __DIR__.'/../../layouts/app.php'; ?>
