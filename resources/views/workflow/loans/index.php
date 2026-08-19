<?php
$pageTitle='Peminjaman Barang';
ob_start();
$role=\App\Helpers\AuthHelper::user()['role'];
if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token']=bin2hex(random_bytes(32)); }
$labels=[
 'PENDING_APPROVAL'=>['Menunggu Persetujuan','yellow'],
 'APPROVED'=>['Siap Diserahkan','blue'],
 'BORROWED'=>['Sedang Dipinjam','orange'],
 'PARTIAL_RETURNED'=>['Kembali Sebagian','purple'],
 'RETURNED'=>['Dikembalikan','green'],
 'REJECTED'=>['Ditolak','red']
];
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4 mb-6">
    <div><h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Peminjaman Barang</h1><p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Permintaan, persetujuan, serah terima, dan pengembalian.</p></div>
    <?php if($role==='TEKNISI_NOC'): ?><a href="<?=url('/loans/create')?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold inline-flex items-center w-fit shadow-sm"><i class="fas fa-plus mr-2"></i>Peminjaman Baru</a><?php endif; ?>
  </div>
  <?php foreach(['success'=>'green','error'=>'red'] as $key=>$color): if(isset($_SESSION[$key])): ?><div class="mb-4 px-4 py-3 rounded-lg border bg-<?=$color?>-50 border-<?=$color?>-200 text-<?=$color?>-700"><?=htmlspecialchars($_SESSION[$key]); unset($_SESSION[$key]);?></div><?php endif; endforeach; ?>
  <form method="GET" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-5">
    <div class="grid grid-cols-1 md:grid-cols-[minmax(0,1fr)_210px_auto_auto] gap-3 items-end">
      <div><label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Pencarian</label><input name="search" value="<?=htmlspecialchars($_GET['search']??'')?>" placeholder="Cari nama peminjam / gudang..." class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5"></div>
      <div><label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Status</label><select name="status" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5"><option value="">Semua Status</option><?php foreach($labels as $v=>$x): ?><option value="<?=$v?>" <?=$status===$v?'selected':''?>><?=$x[0]?></option><?php endforeach; ?></select></div>
      <button class="bg-slate-800 hover:bg-slate-900 text-white px-5 py-2.5 rounded-lg font-semibold inline-flex items-center justify-center"><i class="fas fa-search mr-2"></i>Cari</button><a href="<?=url('/loans')?>" class="bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-white px-5 py-2.5 rounded-lg font-semibold text-center">Reset</a>
    </div>
  </form>
  <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="w-full">
      <table class="w-full table-fixed">
        <colgroup><col class="w-[6%]"><col class="w-[14%]"><col class="w-[21%]"><col class="w-[19%]"><col class="w-[13%]"><col class="w-[13%]"><col class="w-[14%]"></colgroup>
        <thead class="bg-gray-50 dark:bg-gray-900/40"><tr>
          <th class="p-3 text-center text-[11px] font-semibold text-gray-500 uppercase">No</th><th class="p-3 text-left text-[11px] font-semibold text-gray-500 uppercase">Tanggal</th><th class="p-3 text-left text-[11px] font-semibold text-gray-500 uppercase">Peminjam</th><th class="p-3 text-left text-[11px] font-semibold text-gray-500 uppercase">Gudang</th><th class="p-3 text-left text-[11px] font-semibold text-gray-500 uppercase">Jatuh Tempo</th><th class="p-3 text-left text-[11px] font-semibold text-gray-500 uppercase">Status</th><th class="p-3 text-center text-[11px] font-semibold text-gray-500 uppercase">Aksi</th>
        </tr></thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
        <?php $no=$offset+1; foreach($loans as $ln): $b=$labels[$ln['status']]??[$ln['status'],'gray']; $due=!empty($ln['due_date'])?date('d M Y',strtotime($ln['due_date'])):'-'; $isOverdue=!empty($ln['due_date']) && time()>strtotime($ln['due_date']) && !in_array($ln['status'],['RETURNED','REJECTED'],true); ?>
          <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40 align-top">
            <td class="p-3 text-center text-sm text-gray-600 dark:text-gray-300"><?=$no++?></td>
            <td class="p-3 text-sm text-gray-700 dark:text-gray-300 leading-tight"><?=date('d M Y',strtotime($ln['loan_date']))?></td>
            <td class="p-3 text-sm font-semibold text-gray-900 dark:text-gray-100 leading-tight break-words"><?=htmlspecialchars($ln['borrower_name'])?></td>
            <td class="p-3 text-sm text-gray-700 dark:text-gray-300 leading-tight break-words"><?=htmlspecialchars($ln['warehouse_name'])?></td>
            <td class="p-3 text-sm leading-tight"><span class="<?= $isOverdue?'text-red-600 dark:text-red-400 font-semibold':'text-gray-700 dark:text-gray-300' ?>"><?=htmlspecialchars($due)?></span><?php if($isOverdue): ?><div class="text-[11px] text-red-500 mt-1 font-medium">Terlambat</div><?php endif; ?></td>
            <td class="p-3"><span class="inline-flex max-w-full px-2.5 py-1 rounded-full text-[11px] leading-tight font-semibold bg-<?=$b[1]?>-100 text-<?=$b[1]?>-800 dark:bg-<?=$b[1]?>-900/40 dark:text-<?=$b[1]?>-200"><?=$b[0]?></span></td>
            <td class="p-3"><div class="space-y-2">
              <a class="w-full inline-flex items-center justify-center px-2.5 py-1.5 rounded-lg border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 text-xs font-semibold" href="<?=url('/loans/show?id='.(int)$ln['id'])?>"><i class="fas fa-eye mr-1.5"></i>Lihat</a>
              <?php if($role==='ADMIN' && $ln['status']==='PENDING_APPROVAL'): ?><a class="w-full inline-flex items-center justify-center px-2 py-1.5 rounded-lg bg-green-600 hover:bg-green-700 text-white text-xs font-semibold" href="<?=url('/loans/show?id='.(int)$ln['id'])?>#keputusan"><i class="fas fa-check mr-1"></i>Setujui / Tolak</a>
              <?php elseif($role==='ADMIN' && $ln['status']==='APPROVED'): ?><a class="w-full inline-flex items-center justify-center px-2 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold" href="<?=url('/loans/show?id='.(int)$ln['id'])?>#serah-terima"><i class="fas fa-hand-holding mr-1"></i>Serahkan</a>
              <?php elseif($role==='TEKNISI_NOC' && in_array($ln['status'],['BORROWED','PARTIAL_RETURNED'],true)): ?><a class="w-full inline-flex items-center justify-center px-2 py-1.5 rounded-lg bg-orange-600 hover:bg-orange-700 text-white text-xs font-semibold" href="<?=url('/returns/create?loan_id='.(int)$ln['id'])?>"><i class="fas fa-undo mr-1"></i>Ajukan Pengembalian</a>
              <?php elseif($role==='ADMIN' && in_array($ln['status'],['BORROWED','PARTIAL_RETURNED'],true)): ?><a class="w-full inline-flex items-center justify-center px-2 py-1.5 rounded-lg bg-orange-50 hover:bg-orange-100 text-orange-700 border border-orange-200 text-xs font-semibold" href="<?=url('/returns')?>"><i class="fas fa-inbox mr-1"></i>Lihat Pengembalian</a><?php endif; ?>
            </div></td>
          </tr>
        <?php endforeach; if(empty($loans)): ?><tr><td colspan="7" class="p-10 text-center text-gray-500">Belum ada peminjaman.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
    <?php $paginationBaseUrl=url('/loans'); require __DIR__.'/../../components/table/pagination.php'; ?>
  </div>
</div>
<?php $content=ob_get_clean(); require __DIR__.'/../../layouts/app.php'; ?>
