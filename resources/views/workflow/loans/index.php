<?php
$pageTitle='Peminjaman Barang';
ob_start();
$role=\App\Helpers\AuthHelper::user()['role'];
if(empty($_SESSION['csrf_token']))$_SESSION['csrf_token']=bin2hex(random_bytes(32));
$labels=['PENDING_APPROVAL'=>['Menunggu Persetujuan','yellow'],'APPROVED'=>['Siap Diproses','blue'],'BORROWED'=>['Sedang Dipinjam','orange'],'PARTIAL_RETURNED'=>['Kembali Sebagian','purple'],'RETURNED'=>['Dikembalikan','green'],'REJECTED'=>['Ditolak','red']];
?>
<style>
.dark .loan-ui{color:#e5e7eb}
.dark .loan-ui .bg-white{background:#111827!important;color:#e5e7eb!important}
.dark .loan-ui .bg-gray-50{background:#182235!important}
.dark .loan-ui .border-gray-200{border-color:#37465d!important}
.dark .loan-ui .border-gray-700{border-color:#3d4b63!important}
.dark .loan-ui .text-gray-900,.dark .loan-ui .text-gray-800{color:#f8fafc!important}
.dark .loan-ui .text-gray-700{color:#e2e8f0!important}
.dark .loan-ui .text-gray-600{color:#cbd5e1!important}
.dark .loan-ui .text-gray-500,.dark .loan-ui .text-gray-400{color:#94a3b8!important}
.dark .loan-ui input,.dark .loan-ui select{background:#1e293b!important;color:#f8fafc!important;border-color:#475569!important}
.dark .loan-ui input::placeholder{color:#94a3b8!important}
.dark .loan-ui input:focus,.dark .loan-ui select:focus{border-color:#60a5fa!important;box-shadow:0 0 0 2px rgba(96,165,250,.18)!important}
.loan-items-cell .loan-item-chip{display:inline-flex;align-items:center;gap:.35rem;padding:.25rem .5rem;border-radius:.5rem;background:#f8fafc;border:1px solid #e2e8f0;margin:.15rem .2rem .15rem 0;font-size:.75rem}
.dark .loan-items-cell .loan-item-chip{background:#1e293b;border-color:#475569;color:#e2e8f0}
.loan-items-cell .loan-items-muted{font-size:.75rem;color:#94a3b8}
</style>
<div class="loan-ui max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
 <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4 mb-6">
  <div>
   <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Peminjaman Barang</h1>
   <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Permintaan → keputusan Admin → Barang Keluar → barang dipinjam → pengembalian.</p>
  </div>
  <?php if($role==='TEKNISI_NOC'): ?>
   <a href="<?=url('/loans/create')?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold inline-flex items-center"><i class="fas fa-plus mr-2"></i>Peminjaman Baru</a>
  <?php endif; ?>
 </div>

 <?php foreach(['success'=>'green','error'=>'red'] as $key=>$color): if(isset($_SESSION[$key])): ?>
  <div class="mb-4 px-4 py-3 rounded-lg border bg-<?=$color?>-50 border-<?=$color?>-200 text-<?=$color?>-700"><?=htmlspecialchars($_SESSION[$key]); unset($_SESSION[$key]);?></div>
 <?php endif; endforeach; ?>

 <form method="GET" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-5">
  <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_220px_auto_auto] gap-3 items-end">
   <div><label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Pencarian</label><input name="search" value="<?=htmlspecialchars($_GET['search']??'')?>" placeholder="Cari peminjam / gudang..." class="w-full border rounded-lg px-3 py-2.5"></div>
   <div><label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Status</label><select name="status" class="w-full border rounded-lg px-3 py-2.5"><option value="">Semua Status</option><?php foreach($labels as $v=>$x):?><option value="<?=$v?>" <?=$status===$v?'selected':''?>><?=$x[0]?></option><?php endforeach;?></select></div>
   <button class="bg-slate-800 text-white px-5 py-2.5 rounded-lg font-semibold">Cari</button>
   <a href="<?=url('/loans')?>" class="bg-gray-100 dark:bg-gray-700 px-5 py-2.5 rounded-lg font-semibold text-center">Reset</a>
  </div>
 </form>

 <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
  <div class="overflow-x-auto">
   <table class="w-full min-w-[800px] lg:min-w-full">
    <thead class="bg-gray-50 dark:bg-gray-900/40">
     <tr>
      <th class="p-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase w-14">No</th>
      <th class="p-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Tanggal</th>
      <th class="p-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Peminjam</th>
      <th class="p-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Gudang</th>
      <th class="p-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase min-w-[280px]">Barang Dipinjam</th>
      <th class="p-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Jatuh Tempo</th>
      <th class="p-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Status</th>
      <th class="p-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase w-44">Aksi</th>
     </tr>
    </thead>
    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
    <?php $no=$offset+1; foreach($loans as $ln):
     $b=$labels[$ln['status']]??[$ln['status'],'gray'];
     $due=!empty($ln['due_date'])?date('d M Y',strtotime($ln['due_date'])):'-';
     $overdue=!empty($ln['due_date'])&&time()>strtotime($ln['due_date'])&&!in_array($ln['status'],['RETURNED','REJECTED'],true);
     $detail=url('/loans/show?id='.(int)$ln['id']);
     $loanId=(int)$ln['id'];
     $returnUrl=url('/returns/create?loan_id='.$loanId);
    ?>
     <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40 align-top">
      <td class="p-4 text-center text-sm"><?=$no++?></td>
      <td class="p-4 text-sm whitespace-nowrap"><?=date('d M Y',strtotime($ln['loan_date']))?></td>
      <td class="p-4 text-sm font-semibold leading-5 max-w-[180px]"><?=htmlspecialchars($ln['borrower_name'])?></td>
      <td class="p-4 text-sm leading-5 max-w-[180px]"><?=htmlspecialchars($ln['warehouse_name'])?></td>
      <td class="p-4 text-sm loan-items-cell" data-loan-id="<?=$loanId?>" data-loan-status="<?=htmlspecialchars($ln['status'])?>">
       <div class="loan-items-muted">Memuat barang...</div>
      </td>
      <td class="p-4 text-sm"><?php if($due!=='-'): ?><span class="<?=$overdue?'text-red-600 font-semibold':''?>"><?=htmlspecialchars($due)?></span><?php if($overdue): ?><div class="text-xs text-red-500 mt-1">Terlambat</div><?php endif; ?><?php else: ?><span class="text-gray-400">-</span><?php endif; ?></td>
      <td class="p-4"><span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-<?=$b[1]?>-100 text-<?=$b[1]?>-800 whitespace-nowrap"><?=$b[0]?></span></td>
      <td class="p-4">
       <div class="flex justify-center">
        <?php if($role==='ADMIN'&&$ln['status']==='PENDING_APPROVAL'): ?>
         <a href="<?=$detail?>#review" class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-xs font-semibold"><i class="fas fa-clipboard-check mr-1.5"></i>Review</a>
        <?php elseif($role==='ADMIN'&&$ln['status']==='APPROVED'): ?>
         <a href="<?=$detail?>#handover" class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold"><i class="fas fa-box-open mr-1.5"></i>Proses Keluar</a>
        <?php elseif($role==='TEKNISI_NOC'&&in_array($ln['status'],['BORROWED','PARTIAL_RETURNED'],true)): ?>
         <a href="<?=$returnUrl?>" class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-orange-600 hover:bg-orange-700 text-white text-xs font-semibold"><i class="fas fa-undo mr-1.5"></i>Kembalikan</a>
        <?php elseif($role==='ADMIN'&&in_array($ln['status'],['BORROWED','PARTIAL_RETURNED'],true)): ?>
         <a href="<?=$detail?>#admin-return" class="inline-flex items-center justify-center px-4 py-2 rounded-lg border border-emerald-200 text-emerald-700 bg-emerald-50 text-xs font-semibold"><i class="fas fa-clipboard-check mr-1.5"></i>Konfirmasi Kembali</a>
        <?php elseif($role==='TEKNISI_NOC'&&$ln['status']==='PENDING_APPROVAL'): ?>
         <div class="flex items-center justify-center gap-2">
          <a href="<?=$detail?>" class="inline-flex items-center justify-center px-4 py-2 rounded-lg border border-blue-200 text-blue-700 bg-blue-50 text-xs font-semibold"><i class="fas fa-eye mr-1.5"></i>Lihat</a>
          <form method="POST" action="<?=url('/loans/delete')?>" class="inline-block" data-confirm="Peminjaman ini akan dihapus permanen karena belum diproses Admin." data-confirm-title="Hapus Peminjaman?" data-confirm-type="danger" data-confirm-icon="fa-trash" data-confirm-confirm="Hapus" data-confirm-loading="Menghapus...">
           <input type="hidden" name="csrf_token" value="<?=htmlspecialchars($_SESSION['csrf_token']??'')?>">
           <input type="hidden" name="id" value="<?=$loanId?>">
           <button type="submit" class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg border border-red-200 bg-red-50 text-red-700 hover:bg-red-100 text-xs font-semibold" title="Hapus Peminjaman" aria-label="Hapus Peminjaman"><i class="fas fa-trash"></i> Hapus</button>
          </form>
         </div>
        <?php else: ?>
         <a href="<?=$detail?>" class="inline-flex items-center justify-center px-4 py-2 rounded-lg border border-blue-200 text-blue-700 bg-blue-50 text-xs font-semibold"><i class="fas fa-eye mr-1.5"></i>Lihat</a>
        <?php endif; ?>
       </div>
      </td>
     </tr>
    <?php endforeach; if(empty($loans)): ?>
     <?php $colspan = 8; $emptyMessage ="Belum ada peminjaman."; require __DIR__ . '/../../components/table/empty_state.php'; ?>
    <?php endif; ?>
    </tbody>
   </table>
  </div>
  <div class="p-4 text-sm text-gray-500 dark:text-gray-400">Menampilkan total <?=$totalItems?> data</div>
  <?php $paginationBaseUrl=url('/loans'); require __DIR__.'/../../components/table/pagination.php'; ?>
 </div>
</div>

<script>
(function(){
 const cells=[...document.querySelectorAll('.loan-items-cell')];
 if(!cells.length)return;
 const escapeHtml=(v)=>String(v??'').replace(/[&<>'"]/g,s=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[s]));
 cells.forEach(cell=>{
  const loanId=cell.dataset.loanId;
  fetch('<?=url('/loans/api/items?id=')?>'+encodeURIComponent(loanId))
   .then(r=>{if(!r.ok)throw new Error('request');return r.json();})
   .then(items=>{
    if(!Array.isArray(items)||items.length===0){cell.innerHTML='<span class="loan-items-muted">Belum ada barang pada peminjaman ini.</span>';return;}
    const rowStatus=cell.dataset.loanStatus||'';
    const visible=items.slice(0,4);
    cell.innerHTML=visible.map(it=>{
     const name=escapeHtml(it.nama||it.name||'-');
     const requested=Math.max(0,Number(it.requested_quantity)||0);
     const approved=Math.max(0,Number(it.approved_quantity)||0);
     const remaining=Math.max(0,(Number(it.quantity)||0)-(Number(it.returned_quantity)||0));
     let label;
     if(rowStatus==='PENDING_APPROVAL'){
      label=requested>0?('× '+requested+' diminta'):'menunggu persetujuan';
     }else if(remaining>0){
      label='× '+remaining+' sisa dipinjam';
     }else if(approved>0){
      label='× '+approved+' disetujui';
     }else{
      label=requested>0?('× '+requested+' diminta'):'menunggu persetujuan';
     }
     return '<span class="loan-item-chip"><strong>'+name+'</strong><span>'+label+'</span></span>';
    }).join('')+(items.length>4?'<div class="loan-items-muted mt-1">+ '+(items.length-4)+' barang lainnya</div>':'');
   })
   .catch(()=>{cell.innerHTML='<span class="loan-items-muted">Barang tidak dapat dimuat.</span>';});
 });
})();
</script>
<?php $content=ob_get_clean(); require __DIR__.'/../../layouts/app.php'; ?>