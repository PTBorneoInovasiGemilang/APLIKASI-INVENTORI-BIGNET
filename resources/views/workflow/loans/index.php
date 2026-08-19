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
<div class="max-w-7xl mx-auto px-4 py-8">
  <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4 mb-6">
    <div><h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Peminjaman Barang</h1><p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Permintaan, persetujuan, serah terima, dan pengembalian.</p></div>
    <?php if($role==='TEKNISI_NOC'): ?><a href="<?=url('/loans/create')?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold inline-flex items-center w-fit"><i class="fas fa-plus mr-2"></i>Peminjaman Baru</a><?php endif; ?>
  </div>

  <?php foreach(['success'=>'green','error'=>'red'] as $key=>$color): if(isset($_SESSION[$key])): ?><div class="mb-4 px-4 py-3 rounded-lg border bg-<?=$color?>-50 border-<?=$color?>-200 text-<?=$color?>-700"><?=htmlspecialchars($_SESSION[$key]); unset($_SESSION[$key]);?></div><?php endif; endforeach; ?>

  <form method="GET" class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 mb-5 flex gap-3 flex-wrap items-end">
    <div class="flex-1 min-w-[240px]"><label class="block text-xs font-semibold text-gray-500 mb-1">Pencarian</label><input name="search" value="<?=htmlspecialchars($_GET['search']??'')?>" placeholder="Cari nama peminjam / gudang..." class="w-full border rounded-lg px-3 py-2"></div>
    <div><label class="block text-xs font-semibold text-gray-500 mb-1">Status</label><select name="status" class="border rounded-lg px-3 py-2"><option value="">Semua Status</option><?php foreach($labels as $v=>$x): ?><option value="<?=$v?>" <?=$status===$v?'selected':''?>><?=$x[0]?></option><?php endforeach; ?></select></div>
    <button class="bg-slate-800 text-white px-4 py-2 rounded-lg">Cari</button><a href="<?=url('/loans')?>" class="bg-gray-100 px-4 py-2 rounded-lg">Reset</a>
  </form>

  <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden border border-gray-200 dark:border-gray-700">
    <div class="overflow-x-auto">
      <table class="w-full min-w-[1080px]">
        <thead class="bg-gray-50 dark:bg-gray-900/40"><tr>
          <th class="p-3 text-center text-xs font-semibold text-gray-500 uppercase w-12">No</th>
          <th class="p-3 text-left text-xs font-semibold text-gray-500 uppercase">Tanggal</th>
          <th class="p-3 text-left text-xs font-semibold text-gray-500 uppercase">Peminjam</th>
          <th class="p-3 text-left text-xs font-semibold text-gray-500 uppercase">Gudang</th>
          <th class="p-3 text-left text-xs font-semibold text-gray-500 uppercase">Jatuh Tempo</th>
          <th class="p-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
          <th class="p-3 text-center text-xs font-semibold text-gray-500 uppercase min-w-[260px]">Aksi</th>
        </tr></thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
        <?php $no=$offset+1; foreach($loans as $ln): $b=$labels[$ln['status']]??[$ln['status'],'gray']; $due=!empty($ln['due_date'])?date('d M Y',strtotime($ln['due_date'])):'-'; $isOverdue=!empty($ln['due_date']) && time()>strtotime($ln['due_date']) && !in_array($ln['status'],['RETURNED','REJECTED'],true); ?>
          <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
            <td class="p-3 text-center text-sm"><?=$no++?></td>
            <td class="p-3 text-sm whitespace-nowrap"><?=date('d M Y',strtotime($ln['loan_date']))?></td>
            <td class="p-3 text-sm font-medium"><?=htmlspecialchars($ln['borrower_name'])?></td>
            <td class="p-3 text-sm"><?=htmlspecialchars($ln['warehouse_name'])?></td>
            <td class="p-3 text-sm"><span class="<?= $isOverdue?'text-red-600 font-semibold':'' ?>"><?=htmlspecialchars($due)?></span><?php if($isOverdue): ?><div class="text-xs text-red-500 mt-1">Terlambat</div><?php endif; ?></td>
            <td class="p-3"><span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-<?=$b[1]?>-100 text-<?=$b[1]?>-800"><?=$b[0]?></span></td>
            <td class="p-3"><div class="flex flex-wrap gap-2 justify-center">
              <a class="inline-flex items-center px-3 py-1.5 rounded-lg border border-blue-200 text-blue-700 bg-blue-50 hover:bg-blue-100 text-sm font-semibold" href="<?=url('/loans/show?id='.(int)$ln['id'])?>"><i class="fas fa-eye mr-1.5"></i>Lihat</a>
              <?php if($role==='ADMIN' && $ln['status']==='PENDING_APPROVAL'): ?>
                <a class="inline-flex items-center px-3 py-1.5 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-semibold" href="<?=url('/loans/show?id='.(int)$ln['id'])?>#keputusan"><i class="fas fa-check mr-1.5"></i>Setujui / Tolak</a>
              <?php elseif($role==='ADMIN' && $ln['status']==='APPROVED'): ?>
                <a class="inline-flex items-center px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold" href="<?=url('/loans/show?id='.(int)$ln['id'])?>#serah-terima"><i class="fas fa-hand-holding mr-1.5"></i>Serahkan</a>
              <?php elseif($role==='TEKNISI_NOC' && in_array($ln['status'],['BORROWED','PARTIAL_RETURNED'],true)): ?>
                <a class="inline-flex items-center px-3 py-1.5 rounded-lg bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold" href="<?=url('/returns/create?loan_id='.(int)$ln['id'])?>"><i class="fas fa-undo mr-1.5"></i>Ajukan Pengembalian</a>
              <?php elseif($role==='ADMIN' && in_array($ln['status'],['BORROWED','PARTIAL_RETURNED'],true)): ?>
                <a class="inline-flex items-center px-3 py-1.5 rounded-lg bg-orange-50 hover:bg-orange-100 text-orange-700 border border-orange-200 text-sm font-semibold" href="<?=url('/returns')?>"><i class="fas fa-inbox mr-1.5"></i>Lihat Pengembalian</a>
              <?php endif; ?>
            </div></td>
          </tr>
        <?php endforeach; if(empty($loans)): ?><tr><td colspan="7" class="p-10 text-center text-gray-500">Belum ada peminjaman.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
    <div class="p-3 text-sm text-gray-500">Menampilkan total <?=$totalItems?> data</div>
    <?php $paginationBaseUrl=url('/loans'); require __DIR__.'/../../components/table/pagination.php'; ?>
  </div>
</div>
<?php $content=ob_get_clean(); require __DIR__.'/../../layouts/app.php'; ?>