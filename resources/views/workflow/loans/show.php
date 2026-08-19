<?php
$pageTitle='Detail Peminjaman';
ob_start();
$role=\App\Helpers\AuthHelper::user()['role'];
if(empty($_SESSION['csrf_token'])) $_SESSION['csrf_token']=bin2hex(random_bytes(32));
$labels=['PENDING_APPROVAL'=>['Menunggu Persetujuan','yellow'],'APPROVED'=>['Siap Diproses','blue'],'BORROWED'=>['Sedang Dipinjam','orange'],'PARTIAL_RETURNED'=>['Kembali Sebagian','purple'],'RETURNED'=>['Dikembalikan','green'],'REJECTED'=>['Ditolak','red']];
$badge=$labels[$loan['status']]??[$loan['status'],'gray'];
?>
<style>
.dark .loan-ui{color:#e5e7eb}
.dark .loan-ui .bg-white{background:#111827!important;color:#e5e7eb!important}
.dark .loan-ui .bg-gray-50{background:#182235!important}
.dark .loan-ui .bg-gray-100{background:#243044!important;color:#e2e8f0!important}
.dark .loan-ui .border-gray-200{border-color:#37465d!important}
.dark .loan-ui .border-gray-700{border-color:#3d4b63!important}
.dark .loan-ui .text-gray-900,.dark .loan-ui .text-gray-800{color:#f8fafc!important}
.dark .loan-ui .text-gray-700{color:#e2e8f0!important}
.dark .loan-ui .text-gray-600{color:#cbd5e1!important}
.dark .loan-ui .text-gray-500{color:#94a3b8!important}
.dark .loan-ui .text-gray-400{color:#94a3b8!important}
.dark .loan-ui input,.dark .loan-ui select,.dark .loan-ui textarea{background:#1e293b!important;color:#f8fafc!important;border-color:#475569!important}
.dark .loan-ui input::placeholder,.dark .loan-ui textarea::placeholder{color:#94a3b8!important}
.dark .loan-ui input:focus,.dark .loan-ui select:focus,.dark .loan-ui textarea:focus{border-color:#60a5fa!important;box-shadow:0 0 0 2px rgba(96,165,250,.18)!important}
.dark .loan-ui .bg-blue-50{background:#0f2747!important}.dark .loan-ui .border-blue-200{border-color:#245da8!important}.dark .loan-ui .text-blue-700{color:#93c5fd!important}
.dark .loan-ui .bg-green-50{background:#0d2f22!important}.dark .loan-ui .border-green-200{border-color:#206344!important}.dark .loan-ui .text-green-700{color:#86efac!important}
.dark .loan-ui .bg-red-50{background:#381519!important}.dark .loan-ui .border-red-200{border-color:#7f1d1d!important}.dark .loan-ui .text-red-700{color:#fca5a5!important}
.dark .loan-ui .bg-yellow-50{background:#332a0b!important}.dark .loan-ui .border-yellow-200{border-color:#715d16!important}.dark .loan-ui .text-yellow-700{color:#fde68a!important}
.dark .loan-ui .bg-orange-50{background:#3c210d!important}.dark .loan-ui .border-orange-200{border-color:#8a4a13!important}.dark .loan-ui .text-orange-700{color:#fdba74!important}
.dark .loan-ui .hover\:bg-gray-50:hover{background:#1a273b!important}
</style>
<div class="loan-ui max-w-6xl mx-auto px-4 py-8">
  <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4 mb-6">
    <div><a href="<?=url('/loans')?>" class="text-blue-600 hover:underline text-sm">← Kembali</a><h1 class="text-3xl font-bold mt-3 text-gray-900">Detail Peminjaman</h1><p class="text-sm text-gray-500 mt-1">Permintaan → keputusan Admin → Barang Keluar → custody → pengembalian.</p></div>
    <span class="px-3 py-2 rounded-full text-sm font-semibold bg-<?=$badge[1]?>-100 text-<?=$badge[1]?>-800 w-fit"><?=$badge[0]?></span>
  </div>

  <?php foreach(['success'=>'green','error'=>'red'] as $key=>$color): if(isset($_SESSION[$key])): ?>
    <div class="mb-5 px-4 py-3 rounded-lg border bg-<?=$color?>-50 border-<?=$color?>-200 text-<?=$color?>-700"><?=htmlspecialchars($_SESSION[$key]); unset($_SESSION[$key]);?></div>
  <?php endif; endforeach; ?>

  <div class="bg-white rounded-xl border shadow-sm p-6 mb-6">
    <div class="grid md:grid-cols-3 gap-5">
      <div><div class="text-xs text-gray-500">Peminjam</div><div class="font-semibold text-gray-900 mt-1"><?=htmlspecialchars($loan['borrower_name'])?></div></div>
      <div><div class="text-xs text-gray-500">Gudang Pemenuhan</div><div class="font-semibold text-gray-900 mt-1"><?=htmlspecialchars($loan['warehouse_name'])?></div></div>
      <div><div class="text-xs text-gray-500">Jenis</div><div class="font-semibold text-gray-900 mt-1"><?=htmlspecialchars($loan['loan_type']??'INTERNAL')?></div></div>
      <div><div class="text-xs text-gray-500">Diajukan</div><div class="mt-1"><?=!empty($loan['loan_date'])?date('d M Y H:i',strtotime($loan['loan_date'])):'-'?></div></div>
      <div><div class="text-xs text-gray-500">Jatuh Tempo</div><div class="mt-1"><?=!empty($loan['due_date'])?date('d M Y',strtotime($loan['due_date'])):'-'?></div></div>
      <div><div class="text-xs text-gray-500">Barang Keluar</div><div class="mt-1"><?php if(!empty($loan['outgoing_id'])):?><a class="text-blue-600 hover:underline" href="<?=url('/outgoing/show?id='.(int)$loan['outgoing_id'])?>">Lihat transaksi pengeluaran</a><?php else:?><span class="text-gray-400">Belum dibuat</span><?php endif;?></div></div>
    </div>
  </div>

  <?php if($role==='ADMIN' && $loan['status']==='PENDING_APPROVAL'): ?>
  <form method="POST" action="<?=url('/loans/approve')?>" class="bg-yellow-50 border border-yellow-200 rounded-xl p-5 mb-6" id="keputusan">
    <input type="hidden" name="csrf_token" value="<?=htmlspecialchars($_SESSION['csrf_token']??'')?>"><input type="hidden" name="id" value="<?=$loan['id']?>">
    <div class="mb-4"><h2 class="font-semibold text-lg">Review Admin</h2><p class="text-sm text-gray-600 mt-1">Admin wajib menentukan barang yang benar-benar diberikan. Untuk permintaan manual, wajib dipetakan ke Master Barang terlebih dahulu. Serah terima belum membuat pengeluaran stok sampai tombol <strong>Proses & Serahkan</strong> dijalankan.</p></div>
    <div class="space-y-4">
    <?php foreach($items as $it): $isManual=($it['source_type']??'')==='MANUAL'; $key=$isManual?'m'.$it['id']:$it['id']; $requested=(int)$it['requested_quantity']; $serialChoices=$availableSerials[$key]??[]; ?>
      <div class="bg-white rounded-lg border p-4">
        <div class="grid lg:grid-cols-12 gap-4 items-start">
          <div class="lg:col-span-4">
            <label class="flex gap-2"><input type="checkbox" name="approved_include[<?=$key?>]" value="1" class="mt-1 review-include" data-key="<?=$key?>"><span><strong><?=htmlspecialchars($it['nama'])?></strong><span class="block text-xs text-gray-500 mt-1"><?=$isManual?'Manual / Custom request':'Dari Stok'?> · Diminta <?=$requested?></span><?php if($isManual && !empty($it['specification'])):?><span class="block text-xs text-gray-500 mt-1"><?=htmlspecialchars($it['specification'])?></span><?php endif;?></span></label>
          </div>
          <div class="lg:col-span-2"><label class="block text-xs font-semibold mb-1">Diberikan</label><input type="number" min="0" max="<?=$requested?>" value="<?=$requested?>" name="<?=$isManual?'approved_manual_qty['.$it['id'].']':'approved_qty['.$it['id'].']'?>" class="review-qty w-full border rounded-lg px-3 py-2 text-center" data-key="<?=$key?>"></div>
          <div class="lg:col-span-6">
            <?php if($isManual): ?>
              <label class="block text-xs font-semibold mb-1">Mapping ke Master Barang</label>
              <select name="mapped_master_item[<?=$it['id']?>]" class="review-master w-full border rounded-lg px-3 py-2" data-key="<?=$key?>"><option value="">Pilih barang inventory...</option><?php foreach($masterItems as $mi):?><option value="<?=$mi['id']?>" <?=((int)($it['master_item_id']??0)===(int)$mi['id'])?'selected':''?>><?=htmlspecialchars($mi['nama'])?></option><?php endforeach;?></select>
              <?php if(!empty($it['mapped_name'])):?><div class="text-xs text-gray-500 mt-1">Saat ini dipetakan ke: <?=htmlspecialchars($it['mapped_name'])?></div><?php endif;?>
              <?php if(!empty($serialChoices)): ?><label class="block text-xs font-semibold mt-3 mb-1">Pilih Serial</label><select name="approved_serials[<?=$key?>][]" multiple size="4" class="review-serial w-full border rounded-lg px-3 py-2" data-key="<?=$key?>"><?php foreach($serialChoices as $sn):?><option value="<?=$sn['id']?>"><?=htmlspecialchars($sn['serial_number'])?></option><?php endforeach;?></select><div class="text-xs text-gray-500 mt-1">Hanya serial yang tersedia di gudang yang muncul.</div><?php endif; ?>
            <?php elseif((int)$it['is_serialized']===1): ?>
              <label class="block text-xs font-semibold mb-1">Pilih Serial yang Diberikan</label><select name="approved_serials[<?=$key?>][]" multiple size="4" class="review-serial w-full border rounded-lg px-3 py-2" data-key="<?=$key?>"><?php foreach($serialChoices as $sn):?><option value="<?=$sn['id']?>"><?=htmlspecialchars($sn['serial_number'])?></option><?php endforeach;?></select><div class="text-xs text-gray-500 mt-1">Jumlah serial harus sama dengan jumlah diberikan.</div>
            <?php else: ?>
              <div class="text-sm text-gray-500 pt-6">Barang bulk: Admin cukup menentukan jumlah. Stok dicek ulang saat proses Barang Keluar.</div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
    </div>
    <textarea name="approval_notes" rows="2" placeholder="Catatan keputusan Admin (opsional)" class="w-full border rounded-lg px-3 py-2 mt-4"></textarea>
    <div class="flex flex-wrap gap-2 mt-4"><button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold" onclick="return validateLoanApproval()">Setujui Keputusan Admin</button></div>
  </form>
  <form method="POST" action="<?=url('/loans/reject')?>" onsubmit="return rejectLoan()" class="mb-6"><input type="hidden" name="csrf_token" value="<?=htmlspecialchars($_SESSION['csrf_token']??'')?>"><input type="hidden" name="id" value="<?=$loan['id']?>"><input type="hidden" name="approval_notes" id="reject_notes"><button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold">Tolak Peminjaman</button></form>
  <?php endif; ?>

  <div class="bg-white rounded-xl border shadow-sm p-6 mb-6">
    <div class="flex justify-between items-center mb-4"><div><h2 class="text-lg font-semibold">Keputusan Barang</h2><p class="text-xs text-gray-500">Permintaan, jumlah yang diberikan, dan status pengembalian.</p></div></div>
    <div class="space-y-3">
      <?php foreach($items as $it): $remaining=max(0,(int)$it['quantity']-(int)$it['returned_quantity']); ?>
      <div class="border rounded-lg p-4"><div class="flex flex-col md:flex-row md:justify-between gap-3"><div><div class="font-semibold"><?=htmlspecialchars($it['nama'])?></div><div class="text-xs text-gray-500 mt-1"><?=($it['source_type']??'STOCK')==='MANUAL'?'Manual / Custom → '.htmlspecialchars($it['mapped_name']??'belum dipetakan'):'Dari Stok'?></div><?php if(!empty($it['specification'])):?><div class="text-xs text-gray-500 mt-1"><?=htmlspecialchars($it['specification'])?></div><?php endif;?></div><div class="text-sm text-right"><div>Diminta <strong><?=$it['requested_quantity']?></strong></div><div>Diberikan <strong><?=$it['approved_quantity']?></strong></div><div>Sisa Dipinjam <strong><?=$remaining?></strong></div></div></div><?php if(!empty($it['serial_numbers'])):?><div class="mt-3 flex flex-wrap gap-2"><?php foreach($it['serial_numbers'] as $sn):?><span class="px-2 py-1 bg-gray-100 rounded text-sm"><?=htmlspecialchars($sn['serial_number'])?> · <?=$sn['status']?></span><?php endforeach;?></div><?php endif;?></div>
      <?php endforeach; if(empty($items)):?><div class="p-8 text-center text-gray-500">Belum ada barang.</div><?php endif; ?>
    </div>
  </div>

  <?php if($role==='ADMIN' && $loan['status']==='APPROVED'): ?>
  <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 mb-6" id="serah-terima"><h2 class="font-semibold text-lg">Proses Barang Keluar & Serah Terima</h2><p class="text-sm text-gray-600 mt-1 mb-3">Langkah ini membuat satu transaksi <strong>Barang Keluar</strong> dengan jenis <strong>Peminjaman</strong>. Stok fisik tidak dikurangi; barang ditandai sedang dipinjam/custody.</p><form method="POST" action="<?=url('/loans/handover')?>"><input type="hidden" name="csrf_token" value="<?=htmlspecialchars($_SESSION['csrf_token']??'')?>"><input type="hidden" name="id" value="<?=$loan['id']?>"><button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold" onclick="return confirm('Buat Barang Keluar untuk Peminjaman dan serahkan barang sekarang?')">Proses & Serahkan</button></form></div>
  <?php endif; ?>

  <?php if($role==='TEKNISI_NOC' && in_array($loan['status'],['BORROWED','PARTIAL_RETURNED'],true)): ?>
  <div class="bg-orange-50 border border-orange-200 rounded-xl p-5 mb-6"><h2 class="font-semibold">Pengembalian</h2><p class="text-sm text-gray-600 mt-1 mb-3">Pengembalian diajukan teknisi, lalu diperiksa Admin. Barang baik kembali tersedia; barang rusak masuk status rusak.</p><a href="<?=url('/returns/create?loan_id='.(int)$loan['id'])?>" class="inline-block bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg font-semibold">Ajukan Pengembalian</a></div>
  <?php endif; ?>

  <?php require __DIR__.'/../../components/audit_timeline.php'; ?>
</div>
<script>
function validateLoanApproval(){
  let ok=true;
  document.querySelectorAll('.review-include').forEach(cb=>{if(!cb.checked)return;const key=cb.dataset.key;const qty=document.querySelector('.review-qty[data-key="'+key+'"]');if(!qty||parseInt(qty.value||0)<1)ok=false;const master=document.querySelector('.review-master[data-key="'+key+'"]');if(master&&parseInt(qty.value||0)>0&&!master.value)ok=false;const serial=document.querySelector('.review-serial[data-key="'+key+'"]');if(serial&&serial.options.length>0&&serial.selectedOptions.length!==parseInt(qty.value||0))ok=false;});
  if(!ok){alert('Periksa barang, jumlah, mapping manual, dan nomor seri yang diberikan.');return false;}return true;
}
function rejectLoan(){const reason=prompt('Masukkan alasan penolakan:');if(!reason||!reason.trim())return false;document.getElementById('reject_notes').value=reason.trim();return true;}
</script>
<?php $content=ob_get_clean(); require __DIR__.'/../../layouts/app.php'; ?>