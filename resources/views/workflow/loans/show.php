<?php
$pageTitle='Detail Peminjaman';
ob_start();
$role=\App\Helpers\AuthHelper::user()['role'];
if(empty($_SESSION['csrf_token']))$_SESSION['csrf_token']=bin2hex(random_bytes(32));
$labels=['PENDING_APPROVAL'=>['Menunggu Persetujuan','yellow'],'APPROVED'=>['Siap Diproses','blue'],'BORROWED'=>['Sedang Dipinjam','orange'],'PARTIAL_RETURNED'=>['Kembali Sebagian','purple'],'RETURNED'=>['Dikembalikan','green'],'REJECTED'=>['Ditolak','red']];
$badge=$labels[$loan['status']]??[$loan['status'],'gray'];
$isPending=$loan['status']==='PENDING_APPROVAL';
$isApproved=$loan['status']==='APPROVED';
?>
<style>
.loan-ui .review-serial-box{display:none}.loan-ui .review-serial-box.is-visible{display:block}
.loan-ui .review-serial-hint{font-size:12px;margin-top:6px;color:#64748b}.loan-ui .review-serial-error{font-size:12px;margin-top:6px;color:#dc2626}
.dark .loan-ui{color:#e5e7eb}.dark .loan-ui .bg-white{background:#111827!important;color:#e5e7eb!important}.dark .loan-ui .bg-gray-50{background:#182235!important}.dark .loan-ui .bg-gray-100{background:#243044!important;color:#e2e8f0!important}.dark .loan-ui .border-gray-200{border-color:#37465d!important}.dark .loan-ui .text-gray-900,.dark .loan-ui .text-gray-800{color:#f8fafc!important}.dark .loan-ui .text-gray-700{color:#e2e8f0!important}.dark .loan-ui .text-gray-600{color:#cbd5e1!important}.dark .loan-ui .text-gray-500,.dark .loan-ui .text-gray-400{color:#94a3b8!important}.dark .loan-ui input,.dark .loan-ui select,.dark .loan-ui textarea{background:#1e293b!important;color:#f8fafc!important;border-color:#475569!important}
.loan-confirm-backdrop{position:fixed;inset:0;background:rgba(15,23,42,.58);display:none;align-items:center;justify-content:center;padding:20px;z-index:1000}.loan-confirm-backdrop.is-open{display:flex}.loan-confirm-card{width:min(520px,100%);background:#fff;border-radius:16px;box-shadow:0 24px 80px rgba(15,23,42,.3);overflow:hidden}.loan-confirm-head{padding:22px 24px 10px}.loan-confirm-title{font-size:18px;font-weight:700;color:#0f172a}.loan-confirm-text{font-size:14px;line-height:1.55;color:#475569;margin-top:6px}.loan-confirm-body{padding:0 24px 18px}.loan-confirm-actions{display:flex;justify-content:flex-end;gap:10px;padding:16px 24px;border-top:1px solid #e2e8f0}.loan-confirm-cancel,.loan-confirm-primary,.loan-confirm-danger{border-radius:10px;padding:10px 16px;font-weight:600}.loan-confirm-cancel{background:#f1f5f9;color:#334155}.loan-confirm-primary{background:#2563eb;color:#fff}.loan-confirm-danger{background:#dc2626;color:#fff}.loan-confirm-textarea{width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;min-height:96px;resize:vertical}.dark .loan-confirm-card{background:#111827}.dark .loan-confirm-title{color:#f8fafc}.dark .loan-confirm-text{color:#cbd5e1}.dark .loan-confirm-actions{border-color:#334155}.dark .loan-confirm-cancel{background:#243044;color:#e2e8f0}.dark .loan-confirm-textarea{background:#1e293b;color:#f8fafc;border-color:#475569}
</style>

<div class="loan-ui max-w-6xl mx-auto px-4 py-8">
 <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4 mb-6">
  <div>
   <a href="<?=url('/loans')?>" class="text-blue-600 hover:underline text-sm">← Kembali</a>
   <h1 class="text-3xl font-bold mt-3 text-gray-900 dark:text-gray-100">Detail Peminjaman</h1>
   <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Permintaan → keputusan → Barang Keluar → custody → pengembalian.</p>
  </div>
  <span class="px-3 py-2 rounded-full text-sm font-semibold bg-<?=$badge[1]?>-100 text-<?=$badge[1]?>-800 w-fit"><?=$badge[0]?></span>
 </div>

 <?php foreach(['success'=>'green','error'=>'red'] as $key=>$color):if(isset($_SESSION[$key])): ?>
  <div class="mb-5 px-4 py-3 rounded-lg border bg-<?=$color?>-50 border-<?=$color?>-200 text-<?=$color?>-700"><?=htmlspecialchars($_SESSION[$key]);unset($_SESSION[$key]);?></div>
 <?php endif;endforeach; ?>

 <div class="bg-white dark:bg-gray-800 rounded-xl border shadow-sm p-6 mb-6">
  <div class="grid md:grid-cols-3 gap-5">
   <div><div class="text-xs text-gray-500 dark:text-gray-400">Peminjam</div><div class="font-semibold text-gray-900 dark:text-gray-100 mt-1"><?=htmlspecialchars($loan['borrower_name'])?></div></div>
   <div><div class="text-xs text-gray-500 dark:text-gray-400">Gudang Pemenuhan</div><div class="font-semibold text-gray-900 dark:text-gray-100 mt-1"><?=htmlspecialchars($loan['warehouse_name'])?></div></div>
   <div><div class="text-xs text-gray-500 dark:text-gray-400">Jenis</div><div class="font-semibold text-gray-900 dark:text-gray-100 mt-1"><?=htmlspecialchars($loan['loan_type']??'INTERNAL')?></div></div>
   <div><div class="text-xs text-gray-500 dark:text-gray-400">Diajukan</div><div class="mt-1"><?=!empty($loan['loan_date'])?date('d M Y H:i',strtotime($loan['loan_date'])):'-'?></div></div>
   <div><div class="text-xs text-gray-500 dark:text-gray-400">Jatuh Tempo</div><div class="mt-1"><?=!empty($loan['due_date'])?date('d M Y',strtotime($loan['due_date'])):'-'?></div></div>
   <div><div class="text-xs text-gray-500 dark:text-gray-400">Barang Keluar</div><div class="mt-1"><?php if(!empty($loan['outgoing_id'])): ?><a class="text-blue-600 hover:underline" href="<?=url('/outgoing/show?id='.(int)$loan['outgoing_id'])?>">Lihat transaksi pengeluaran</a><?php else: ?><span class="text-gray-400">Belum dibuat</span><?php endif; ?></div></div>
  </div>
 </div>

 <?php if($role==='ADMIN'&&$isPending): ?>
 <form method="POST" action="<?=url('/loans/approve')?>" class="bg-yellow-50 border border-yellow-200 rounded-xl p-5 mb-6" id="review" onsubmit="return validateLoanApproval()">
  <input type="hidden" name="csrf_token" value="<?=htmlspecialchars($_SESSION['csrf_token']??'')?>"><input type="hidden" name="id" value="<?=$loan['id']?>">
  <div class="mb-4"><h2 class="font-semibold text-lg">Review Barang</h2><p class="text-sm text-gray-600 dark:text-gray-300 mt-1">Tentukan barang, jumlah, mapping barang manual, dan Serial Number yang benar-benar akan diberikan.</p></div>
  <div class="space-y-4">
   <?php foreach($items as $it):$isManual=(($it['source_type']??'')==='MANUAL');$key=$isManual?'m'.$it['id']:(string)$it['id'];$requested=(int)$it['requested_quantity'];$masterId=(int)($it['master_item_id']??0);$serialized=!$isManual&&(int)$it['is_serialized']===1; ?>
   <div class="bg-white dark:bg-gray-800 rounded-lg border p-4" data-review-row data-key="<?=htmlspecialchars($key)?>">
    <div class="grid lg:grid-cols-12 gap-4 items-start">
     <div class="lg:col-span-4"><label class="flex gap-2"><input type="checkbox" name="approved_include[<?=htmlspecialchars($key)?>]" value="1" class="mt-1 review-include" data-key="<?=htmlspecialchars($key)?>"><span><strong><?=htmlspecialchars($it['nama'])?></strong><span class="block text-xs text-gray-500 dark:text-gray-400 mt-1"><?=$isManual?'Manual / Custom request':'Dari Stok'?> · Diminta <?=$requested?></span><?php if($isManual&&!empty($it['specification'])):?><span class="block text-xs text-gray-500 dark:text-gray-400 mt-1"><?=htmlspecialchars($it['specification'])?></span><?php endif;?></span></label></div>
     <div class="lg:col-span-2"><label class="block text-xs font-semibold mb-1">Diberikan</label><input type="number" min="0" max="<?=$requested?>" value="<?=$requested?>" name="<?= $isManual?'approved_manual_qty['.$it['id'].']':'approved_qty['.$it['id'].']' ?>" class="review-qty w-full border rounded-lg px-3 py-2 text-center" data-key="<?=htmlspecialchars($key)?>"></div>
     <div class="lg:col-span-6">
      <?php if($isManual): ?>
       <label class="block text-xs font-semibold mb-1">Mapping ke Master Barang</label>
       <select name="mapped_master_item[<?=$it['id']?>]" class="review-master w-full border rounded-lg px-3 py-2" data-key="<?=htmlspecialchars($key)?>" data-warehouse="<?=htmlspecialchars((string)$loan['warehouse_id'])?>"><option value="">Pilih barang inventory...</option><?php foreach($masterItems as $mi):?><option value="<?=$mi['id']?>" <?=($masterId===(int)$mi['id'])?'selected':''?>><?=htmlspecialchars($mi['nama'])?></option><?php endforeach;?></select>
       <div class="review-serial-box mt-3" data-key="<?=htmlspecialchars($key)?>"><label class="block text-xs font-semibold mb-1">Pilih Serial yang Diberikan</label><select multiple size="4" name="approved_serials[<?=htmlspecialchars($key)?>][]" class="review-serial w-full border rounded-lg px-3 py-2" data-key="<?=htmlspecialchars($key)?>" disabled></select><div class="review-serial-hint">Jika Master Barang serialized, pilih serial sesuai jumlah yang diberikan.</div><div class="review-serial-error" hidden></div></div>
      <?php elseif($serialized): ?>
       <div class="review-serial-box is-visible mt-3" data-key="<?=htmlspecialchars($key)?>" data-master-id="<?=$masterId?>"><label class="block text-xs font-semibold mb-1">Pilih Serial yang Diberikan</label><select multiple size="4" name="approved_serials[<?=htmlspecialchars($key)?>][]" class="review-serial w-full border rounded-lg px-3 py-2" data-key="<?=htmlspecialchars($key)?>" data-warehouse="<?=htmlspecialchars((string)$loan['warehouse_id'])?>" disabled></select><div class="review-serial-hint">Pilih Serial Number sesuai jumlah yang diberikan.</div><div class="review-serial-error" hidden></div></div>
      <?php else: ?><div class="text-sm text-gray-500 dark:text-gray-400 pt-6">Barang bulk: cukup tentukan jumlah. Stok dicek ulang saat Barang Keluar.</div><?php endif; ?>
     </div>
    </div>
   </div>
   <?php endforeach; ?>
  </div>
  <textarea name="approval_notes" rows="2" placeholder="Catatan keputusan (opsional)" class="w-full border rounded-lg px-3 py-2 mt-4"></textarea>
  <div class="flex flex-wrap gap-2 mt-4"><button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold">Setujui &amp; Lanjutkan</button><button type="button" onclick="openRejectModal()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold">Tolak</button></div>
 </form>
 <?php endif; ?>

 <?php if($isPending): ?>
 <?php /* BUG-050: loan yang masih Menunggu Persetujuan tetap menampilkan barang yang diajukan Teknisi */ ?>
 <div class="bg-white dark:bg-gray-800 rounded-xl border shadow-sm p-6 mb-6">
  <div class="flex items-start justify-between gap-4 mb-4"><div><h2 class="text-lg font-semibold">Barang Diajukan</h2><p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Barang yang diminta Peminjam. Admin menentukan barang, jumlah, dan serial saat review.</p></div><span class="text-xs px-2.5 py-1 rounded-full bg-yellow-100 text-yellow-700">Menunggu Persetujuan</span></div>
  <div class="space-y-3">
   <?php foreach($items as $it): ?>
   <div class="border rounded-lg p-4"><div class="flex flex-col md:flex-row md:justify-between gap-3"><div><div class="font-semibold"><?=htmlspecialchars($it['nama'])?></div><div class="text-xs text-gray-500 dark:text-gray-400 mt-1"><?=($it['source_type']??'STOCK')==='MANUAL'?'Manual / Custom':'Dari Stok'?></div><?php if(!empty($it['specification'])):?><div class="text-xs text-gray-500 dark:text-gray-400 mt-1"><?=htmlspecialchars($it['specification'])?></div><?php endif;?></div><div class="text-sm text-right"><div>Diminta <strong><?=$it['requested_quantity']?></strong></div></div></div></div>
   <?php endforeach;if(empty($items)):?><div class="p-8 text-center text-gray-500 dark:text-gray-400">Belum ada barang.</div><?php endif; ?>
  </div>
 </div>
 <?php endif; ?>

 <?php if(!$isPending): ?>
 <div class="bg-white dark:bg-gray-800 rounded-xl border shadow-sm p-6 mb-6">
  <div class="flex items-start justify-between gap-4 mb-4"><div><h2 class="text-lg font-semibold">Barang Peminjaman</h2><p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Rincian barang yang sudah diputuskan Admin.</p></div><span class="text-xs px-2.5 py-1 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">Ringkasan</span></div>
  <div class="space-y-3">
   <?php foreach($items as $it):$remaining=max(0,(int)$it['quantity']-(int)$it['returned_quantity']); ?>
   <div class="border rounded-lg p-4"><div class="flex flex-col md:flex-row md:justify-between gap-3"><div><div class="font-semibold"><?=htmlspecialchars($it['nama'])?></div><div class="text-xs text-gray-500 dark:text-gray-400 mt-1"><?=($it['source_type']??'STOCK')==='MANUAL'?'Manual / Custom → '.htmlspecialchars($it['mapped_name']??'belum dipetakan'):'Dari Stok'?></div><?php if(!empty($it['specification'])):?><div class="text-xs text-gray-500 dark:text-gray-400 mt-1"><?=htmlspecialchars($it['specification'])?></div><?php endif;?></div><div class="text-sm text-right"><div>Diminta <strong><?=$it['requested_quantity']?></strong></div><div>Diberikan <strong><?=$it['approved_quantity']?></strong></div><div>Sisa Dipinjam <strong><?=$remaining?></strong></div></div></div><?php if(!empty($it['serial_numbers'])):?><div class="mt-3 flex flex-wrap gap-2"><?php foreach($it['serial_numbers'] as $sn):?><span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-sm"><?=htmlspecialchars($sn['serial_number'])?> · <?=$sn['status']?></span><?php endforeach;?></div><?php endif;?></div>
   <?php endforeach;if(empty($items)):?><div class="p-8 text-center text-gray-500 dark:text-gray-400">Belum ada barang.</div><?php endif; ?>
  </div>
 </div>
 <?php endif; ?>

 <?php if($role==='ADMIN'&&$isApproved): ?>
 <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 mb-6" id="handover"><h2 class="font-semibold text-lg">Barang Keluar &amp; Serah Terima</h2><p class="text-sm text-gray-600 dark:text-gray-300 mt-1 mb-3">Buat transaksi Barang Keluar untuk Peminjaman. Stok fisik tetap, tetapi barang menjadi custody.</p><form method="POST" action="<?=url('/loans/handover')?>" id="handoverForm"><input type="hidden" name="csrf_token" value="<?=htmlspecialchars($_SESSION['csrf_token']??'')?>"><input type="hidden" name="id" value="<?=$loan['id']?>"><button type="button" onclick="openHandoverModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold">Proses &amp; Serahkan</button></form></div>
 <?php endif; ?>

 <?php if($role==='TEKNISI_NOC'&&in_array($loan['status'],['BORROWED','PARTIAL_RETURNED'],true)): ?>
 <div class="bg-orange-50 border border-orange-200 rounded-xl p-5 mb-6"><h2 class="font-semibold">Pengembalian</h2><p class="text-sm text-gray-600 dark:text-gray-300 mt-1 mb-3">Pengembalian diajukan teknisi, lalu diperiksa Admin.</p><a href="<?=url('/returns/create?loan_id='.(int)$loan['id'])?>" class="inline-block bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg font-semibold">Ajukan Pengembalian</a></div>
 <?php endif; ?>

 <?php require __DIR__.'/../../components/audit_timeline.php'; ?>
</div>

<div id="loanModal" class="loan-confirm-backdrop" aria-hidden="true">
 <div class="loan-confirm-card" role="dialog" aria-modal="true" aria-labelledby="loanModalTitle">
  <div class="loan-confirm-head"><div id="loanModalTitle" class="loan-confirm-title">Konfirmasi</div><div id="loanModalText" class="loan-confirm-text"></div></div>
  <div id="loanModalBody" class="loan-confirm-body"></div>
  <div class="loan-confirm-actions"><button type="button" class="loan-confirm-cancel" onclick="closeLoanModal()">Batal</button><button type="button" id="loanModalPrimary" class="loan-confirm-primary">Lanjutkan</button></div>
 </div>
</div>

<script>
const loanModal=document.getElementById('loanModal'),loanModalTitle=document.getElementById('loanModalTitle'),loanModalText=document.getElementById('loanModalText'),loanModalBody=document.getElementById('loanModalBody'),loanModalPrimary=document.getElementById('loanModalPrimary');
let loanModalAction=null;
function openLoanModal({title,text,body='',primary='Lanjutkan',danger=false,onConfirm}){loanModalTitle.textContent=title;loanModalText.textContent=text;loanModalBody.innerHTML=body;loanModalPrimary.textContent=primary;loanModalPrimary.className='loan-confirm-'+(danger?'danger':'primary');loanModalAction=onConfirm;loanModal.classList.add('is-open');loanModal.setAttribute('aria-hidden','false');setTimeout(()=>loanModalPrimary.focus(),20)}
function closeLoanModal(){loanModal.classList.remove('is-open');loanModal.setAttribute('aria-hidden','true');loanModalBody.innerHTML='';loanModalAction=null}
loanModal.addEventListener('click',e=>{if(e.target===loanModal)closeLoanModal()});document.addEventListener('keydown',e=>{if(e.key==='Escape'&&loanModal.classList.contains('is-open'))closeLoanModal()});loanModalPrimary.addEventListener('click',()=>{const fn=loanModalAction;closeLoanModal();if(fn)fn()});
function showValidationModal(message){openLoanModal({title:'Data belum lengkap',text:message,primary:'Mengerti',onConfirm:null})}
function openHandoverModal(){openLoanModal({title:'Proses Barang Keluar',text:'Transaksi Barang Keluar untuk peminjaman akan dibuat dan barang diserahkan kepada peminjam.',body:'<div class="rounded-lg bg-blue-50 border border-blue-200 px-4 py-3 text-sm text-blue-800">Stok fisik tidak berkurang. Barang akan tercatat sebagai sedang dipinjam/custody.</div>',primary:'Proses & Serahkan',onConfirm:()=>document.getElementById('handoverForm').submit()})}
function openRejectModal(){openLoanModal({title:'Tolak Peminjaman',text:'Peminjaman akan ditolak dan alasan akan dicatat pada riwayat.',body:'<textarea id="rejectReason" class="loan-confirm-textarea" placeholder="Alasan penolakan (wajib)"></textarea>',primary:'Tolak Peminjaman',danger:true,onConfirm:()=>{const reason=(document.getElementById('rejectReason')?.value||'').trim();if(!reason){openLoanModal({title:'Alasan diperlukan',text:'Masukkan alasan penolakan sebelum melanjutkan.',primary:'Mengerti',onConfirm:null});return;}const form=document.createElement('form');form.method='POST';form.action='<?=url('/loans/reject')?>';form.innerHTML='<input type="hidden" name="csrf_token" value="<?=htmlspecialchars($_SESSION['csrf_token']??'')?>"><input type="hidden" name="id" value="<?=$loan['id']?>"><input type="hidden" name="approval_notes" value="'+reason.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;')+'">';document.body.appendChild(form);form.submit()}})}
function byKey(selector,key){return document.querySelector(selector+'[data-key="'+CSS.escape(String(key))+'"]')}
function serialBox(key){return byKey('.review-serial-box',key)}
async function loadSerialOptions(masterSelect){const key=masterSelect.dataset.key,masterId=parseInt(masterSelect.value||'0',10),warehouseId=parseInt(masterSelect.dataset.warehouse||'0',10),box=serialBox(key);const sel=box?box.querySelector('.review-serial'):null,hint=box?box.querySelector('.review-serial-hint'):null,err=box?box.querySelector('.review-serial-error'):null;if(!box||!sel)return;sel.innerHTML='';sel.disabled=true;box.classList.remove('is-visible');if(err){err.hidden=true;err.textContent=''}if(!masterId)return;try{const response=await fetch('<?=url('/api/stock')?>?item_id='+encodeURIComponent(masterId)+'&warehouse_id='+encodeURIComponent(warehouseId),{headers:{Accept:'application/json'}});const data=await response.json();if(!response.ok||data.error)throw new Error(data.error||'Gagal mengambil stok.');const serials=Array.isArray(data.serials)?data.serials:[];if(!('serials'in data)){if(hint)hint.textContent='Barang bulk/non-serial: tidak perlu memilih Serial Number.';return}box.classList.add('is-visible');if(!serials.length){if(hint)hint.textContent='Tidak ada Serial Number yang tersedia di gudang pemenuhan.';return}serials.forEach(sn=>{const opt=document.createElement('option');opt.value=String(sn.id);opt.textContent=sn.serial_number+(sn.location_name?' — '+sn.location_name:'');sel.appendChild(opt)});sel.disabled=false;if(hint)hint.textContent='Pilih Serial Number sesuai jumlah yang diberikan.'}catch(e){box.classList.add('is-visible');if(err){err.hidden=false;err.textContent=e.message||'Gagal memuat Serial Number.'}if(hint)hint.textContent=''}}
document.addEventListener('DOMContentLoaded',()=>{document.querySelectorAll('.review-master').forEach(select=>{select.addEventListener('change',()=>loadSerialOptions(select));if(select.value)loadSerialOptions(select)});document.querySelectorAll('.review-serial-box[data-master-id]').forEach(box=>{const masterId=parseInt(box.dataset.masterId||'0',10);const warehouseId=parseInt(<?=json_encode((int)$loan['warehouse_id'])?>,10);const sel=box.querySelector('.review-serial');if(!masterId||!sel)return;fetch('<?=url('/api/stock')?>?item_id='+encodeURIComponent(masterId)+'&warehouse_id='+encodeURIComponent(warehouseId),{headers:{Accept:'application/json'}}).then(r=>r.json()).then(data=>{const serials=Array.isArray(data.serials)?data.serials:[];serials.forEach(sn=>{const o=document.createElement('option');o.value=String(sn.id);o.textContent=sn.serial_number+(sn.location_name?' — '+sn.location_name:'');sel.appendChild(o)});if(serials.length)sel.disabled=false})})});
function validateLoanApproval(){let ok=true;let message='Periksa barang, jumlah, mapping, dan Serial Number.';document.querySelectorAll('.review-include:checked').forEach(cb=>{const key=cb.dataset.key,qtyEl=byKey('.review-qty',key),qty=parseInt(qtyEl?qtyEl.value:'0',10);if(qty<1)ok=false;const master=byKey('.review-master',key);if(master&&!master.value)ok=false;const box=serialBox(key),serial=box?box.querySelector('.review-serial'):null;if(serial&&serial.closest('.review-serial-box')?.classList.contains('is-visible')){if(serial.disabled)ok=false;else if(serial.selectedOptions.length!==qty)ok=false}});if(!ok){showValidationModal(message);return false}return true}
</script>
<?php $content=ob_get_clean();require __DIR__.'/../../layouts/app.php'; ?>