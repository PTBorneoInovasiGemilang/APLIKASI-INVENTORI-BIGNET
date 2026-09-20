<?php
$pageTitle='Transfer Gudang Baru';
ob_start();
if(empty($_SESSION['csrf_token']))$_SESSION['csrf_token']=bin2hex(random_bytes(32));
$availableSerials=[];
$pdo=\App\Core\Database::getInstance()->getConnection();
$q=$pdo->query("SELECT id,master_item_id,current_warehouse_id,serial_number FROM serial_numbers WHERE status='AVAILABLE' AND current_warehouse_id IS NOT NULL ORDER BY serial_number ASC");
foreach($q->fetchAll(PDO::FETCH_ASSOC) as $sn)$availableSerials[(int)$sn['current_warehouse_id']][(int)$sn['master_item_id']][]=['id'=>(int)$sn['id'],'serial_number'=>$sn['serial_number']];
?>
<style>
/* Transfer form dark-mode contrast — scoped only to Transfer Gudang Baru. */
.dark body:has(#transferForm) #transferForm section,
.dark body:has(#transferForm) #transferForm .bg-white{background:#111827!important;color:#f3f4f6!important;border-color:#374151!important}
.dark body:has(#transferForm) #transferForm .bg-gray-50{background:#1f2937!important;color:#e5e7eb!important;border-color:#374151!important}
.dark body:has(#transferForm) #transferForm .bg-blue-50{background:#0f274a!important;color:#dbeafe!important;border-color:#1e40af!important}
.dark body:has(#transferForm) #transferForm .border-b,
.dark body:has(#transferForm) #transferForm .border-t{border-color:#374151!important}
.dark body:has(#transferForm) #transferForm h1,
.dark body:has(#transferForm) #transferForm h2,
.dark body:has(#transferForm) #transferForm h3,
.dark body:has(#transferForm) #transferForm label,
.dark body:has(#transferForm) #transferForm th,
.dark body:has(#transferForm) #transferForm .font-semibold,
.dark body:has(#transferForm) #transferForm .font-bold{color:#f3f4f6!important}
.dark body:has(#transferForm) #transferForm .text-gray-500,
.dark body:has(#transferForm) #transferForm .text-gray-600,
.dark body:has(#transferForm) #transferForm .text-gray-700,
.dark body:has(#transferForm) #transferForm .text-gray-400{color:#cbd5e1!important}
.dark body:has(#transferForm) #transferForm input,
.dark body:has(#transferForm) #transferForm select,
.dark body:has(#transferForm) #transferForm textarea{background:#0f172a!important;color:#f8fafc!important;border-color:#4b5563!important}
.dark body:has(#transferForm) #transferForm input::placeholder,
.dark body:has(#transferForm) #transferForm textarea::placeholder{color:#94a3b8!important}
.dark body:has(#transferForm) #transferForm select option{background:#0f172a!important;color:#f8fafc!important}
.dark body:has(#transferForm) #transferForm .border-blue-100{border-color:#1e40af!important}
.dark body:has(#transferForm) #transferForm .text-blue-700{color:#93c5fd!important}
.dark body:has(#transferForm) #transferForm .text-red-600{color:#f87171!important}
.dark body:has(#transferForm) #transferForm .shadow-sm{box-shadow:0 10px 28px rgba(0,0,0,.28)!important}
.dark body:has(#transferForm) #serialModal>div{background:#111827!important;color:#f3f4f6!important;border-color:#374151!important}
.dark body:has(#transferForm) #serialModal .bg-gray-50{background:#1f2937!important;border-color:#374151!important}
.dark body:has(#transferForm) #serialModal input{background:#0f172a!important;color:#f8fafc!important;border-color:#4b5563!important}
</style>
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 flex items-center gap-3">
        <a href="<?=url('/transfers')?>" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400"><i class="fas fa-arrow-left"></i></a>
        <div><h1 class="text-2xl font-bold">Transfer Gudang Baru</h1><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tentukan tujuan, pembawa barang, lalu pilih barang yang benar-benar dikirim.</p></div>
    </div>
    <div id="transferClientError" class="mb-5 hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><i class="fas fa-circle-exclamation mr-2"></i><span></span></div>
    <?php if(isset($_SESSION['error'])):?><div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><?=htmlspecialchars($_SESSION['error']);unset($_SESSION['error']);?></div><?php endif;?>
    <form id="transferForm" action="<?=url('/transfers')?>" method="POST" enctype="multipart/form-data" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?=htmlspecialchars($_SESSION['csrf_token'])?>">
        <section class="rounded-2xl border bg-white dark:bg-gray-800 shadow-sm">
            <div class="border-b px-6 py-5"><h2 class="text-lg font-semibold">Detail Pengiriman</h2><p class="text-xs text-gray-500 dark:text-gray-400">Data pembawa akan tersimpan di Surat Jalan.</p></div>
            <div class="grid grid-cols-1 gap-5 px-6 py-6 md:grid-cols-2">
                <div><label class="text-sm font-semibold">Gudang Asal *</label><select id="source_warehouse_id" name="source_warehouse_id" required class="mt-2 w-full rounded-xl border px-3 py-2.5"><option value="">Pilih gudang asal...</option><?php foreach($warehouses as $w):?><option value="<?=$w['id']?>"><?=htmlspecialchars($w['name'])?></option><?php endforeach;?></select></div>
                <div><label class="text-sm font-semibold">Gudang Tujuan *</label><select id="destination_warehouse_id" name="destination_warehouse_id" required class="mt-2 w-full rounded-xl border px-3 py-2.5"><option value="">Pilih gudang tujuan...</option><?php foreach($warehouses as $w):?><option value="<?=$w['id']?>" data-warehouse-name="<?=htmlspecialchars($w['name'],ENT_QUOTES)?>"><?=htmlspecialchars($w['name'])?></option><?php endforeach;?></select></div>
                <div><label class="text-sm font-semibold">Supir / Pembawa Barang *</label><input name="driver_name" required class="mt-2 w-full rounded-xl border px-3 py-2.5" placeholder="Nama orang yang membawa barang"></div>
                <div><label class="text-sm font-semibold">Nomor HP Supir</label><input name="driver_phone" class="mt-2 w-full rounded-xl border px-3 py-2.5" placeholder="Opsional"></div>
                <div><label class="text-sm font-semibold">Nomor Kendaraan</label><input name="vehicle_number" class="mt-2 w-full rounded-xl border px-3 py-2.5" placeholder="Contoh: KB 1234 XX"></div>
                <div class="md:col-span-2"><label class="text-sm font-semibold">Catatan Pengiriman</label><textarea name="notes" rows="3" class="mt-2 w-full rounded-xl border px-3 py-2.5" placeholder="Catatan tambahan bila diperlukan"></textarea></div>
                <div class="md:col-span-2"><div class="rounded-xl border border-blue-100 bg-blue-50 p-4"><label class="text-sm font-semibold">Foto Bukti Pengiriman *</label><input name="dispatch_photo" type="file" accept="image/jpeg,image/png,image/webp" required class="mt-3 w-full"><p class="mt-1 text-xs text-gray-500 dark:text-gray-400">JPG, PNG, atau WEBP · maksimal 5 MB.</p></div></div>
            </div>
        </section>
        <section class="rounded-2xl border bg-white dark:bg-gray-800 shadow-sm">
            <div class="flex items-center justify-between border-b px-6 py-5"><div><h2 class="text-lg font-semibold">Barang yang Dikirim</h2><p class="text-xs text-gray-500 dark:text-gray-400">Nomor seri dipilih dari stok. Tidak perlu mengetik satu per satu.</p></div><button type="button" onclick="addRow()" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white">Tambah Barang</button></div>
            <div class="overflow-x-auto"><table class="min-w-full"><thead class="bg-gray-50 dark:bg-gray-900"><tr><th class="px-6 py-3 text-left text-xs uppercase text-gray-500 dark:text-gray-400">Barang</th><th class="px-4 py-3 text-center text-xs uppercase text-gray-500 dark:text-gray-400">Jumlah</th><th class="px-4 py-3 text-left text-xs uppercase text-gray-500 dark:text-gray-400">Nomor Seri</th><th class="w-20"></th></tr></thead><tbody id="itemsBody"></tbody></table></div>
            <div class="border-t px-6 py-4 text-xs text-gray-500 dark:text-gray-400">Barang tanpa nomor seri cukup isi jumlah.</div>
        </section>
        <div class="flex justify-end gap-3"><a href="<?=url('/transfers')?>" class="rounded-xl border px-5 py-2.5 font-semibold">Batal</a><button class="rounded-xl bg-blue-600 px-6 py-2.5 font-semibold text-white" type="submit">Simpan Transfer</button></div>
    </form>
</div>
<div id="serialModal" class="fixed inset-0 z-[140] hidden items-center justify-center bg-black/50 p-4"><div class="w-full max-w-3xl rounded-2xl bg-white dark:bg-gray-800 shadow-2xl"><div class="flex items-center justify-between border-b px-6 py-5"><div><h3 class="text-lg font-bold">Pilih Nomor Seri</h3><p id="serialHint" class="text-xs text-gray-500 dark:text-gray-400"></p></div><button type="button" onclick="closeModal()"><i class="fas fa-times"></i></button></div><div class="p-6"><div class="flex gap-3"><input id="serialSearch" class="flex-1 rounded-xl border px-3 py-2.5" placeholder="Cari nomor seri..."><button type="button" onclick="autoSelect()" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-white">Pilih Otomatis</button></div><div id="serialList" class="mt-4 max-h-[55vh] overflow-auto"></div></div><div class="flex justify-end gap-3 border-t bg-gray-50 dark:bg-gray-900 px-6 py-4"><button type="button" onclick="closeModal()" class="rounded-xl border px-5 py-2.5">Batal</button><button type="button" onclick="applySerials()" class="rounded-xl bg-blue-600 px-5 py-2.5 text-white">Gunakan Nomor Seri</button></div></div></div>
<script>
const itemsData=<?=json_encode($items,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)?>,serialData=<?=json_encode($availableSerials,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)?>,source=document.getElementById('source_warehouse_id'),dest=document.getElementById('destination_warehouse_id'),body=document.getElementById('itemsBody');
let row=0,active=null,selection=new Set();
function showError(msg){const e=document.getElementById('transferClientError');e.querySelector('span').textContent=msg;e.classList.remove('hidden');e.scrollIntoView({behavior:'smooth',block:'center'});}
function syncDestinationOptions(){const sid=String(source.value||'');const current=String(dest.value||'');Array.from(dest.options).forEach(opt=>{if(!opt.value)return;opt.hidden=!!sid&&opt.value===sid;opt.disabled=!!sid&&opt.value===sid;});if(current&&current===sid)dest.value='';}
source.addEventListener('change',()=>{syncDestinationOptions();body.innerHTML='';row=0;});
function options(){const sid=String(source.value||'');return itemsData.filter(i=>String(i.warehouse_id)===sid&&Number(i.stock_quantity)>0);}
function addRow(){if(!source.value){showError('Pilih gudang asal terlebih dahulu.');return;}if(!dest.value){showError('Pilih gudang tujuan terlebih dahulu.');return;}if(dest.value===source.value){dest.value='';syncDestinationOptions();return;}const items=options();if(!items.length){showError('Tidak ada stok tersedia di gudang asal.');return;}const i=row++,tr=document.createElement('tr');tr.className='border-b';tr.dataset.row=i;tr.innerHTML=`<td class="px-6 py-4"><select name="item_ids[${i}]" class="item-select w-full rounded-xl border px-3 py-2.5">${items.map(x=>`<option value="${x.id}" data-serialized="${x.is_serialized?'1':'0'}">${esc(x.nama)}</option>`).join('')}</select><div class="mt-1 text-xs text-gray-500 dark:text-gray-400 type-note"></div></td><td class="px-4 py-4 text-center"><input name="quantities[${i}]" type="number" min="1" value="1" class="qty-input w-24 rounded-xl border px-3 py-2.5 text-center"></td><td class="px-4 py-4"><div class="sn-summary text-xs text-gray-500 dark:text-gray-400">Barang tanpa nomor seri.</div><textarea name="sn_list[${i}]" class="sn-input hidden"></textarea><button type="button" onclick="openModal(${i})" class="sn-button hidden mt-2 rounded-xl border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700">Pilih Nomor Seri</button></td><td class="px-4 py-4 text-center"><button type="button" onclick="this.closest('tr').remove()" class="text-red-600"><i class="fas fa-trash"></i></button></td>`;body.appendChild(tr);const sel=tr.querySelector('.item-select');const upd=()=>{const ser=sel.options[sel.selectedIndex].dataset.serialized==='1';tr.querySelector('.type-note').textContent=ser?'Pakai Nomor Seri':'Tanpa Nomor Seri';tr.querySelector('.sn-button').classList.toggle('hidden',!ser);tr.querySelector('.sn-summary').textContent=ser?'Belum ada nomor seri dipilih.':'Barang tanpa nomor seri.';};sel.addEventListener('change',upd);upd();}
function openModal(i){active=i;selection=new Set();const tr=document.querySelector(`tr[data-row="${i}"]`),id=tr.querySelector('.item-select').value,qty=parseInt(tr.querySelector('.qty-input').value||'0',10),arr=(serialData[String(source.value)]?.[id]||[]);document.getElementById('serialHint').textContent=`Pilih tepat ${qty} nomor seri dari ${arr.length} yang tersedia.`;document.getElementById('serialSearch').value='';document.getElementById('serialModal').classList.remove('hidden');document.getElementById('serialModal').classList.add('flex');renderModal();}
function closeModal(){const m=document.getElementById('serialModal');m.classList.add('hidden');m.classList.remove('flex');active=null;}
function renderModal(){if(active===null)return;const tr=document.querySelector(`tr[data-row="${active}"]`),id=tr.querySelector('.item-select').value,q=parseInt(tr.querySelector('.qty-input').value||'0',10),arr=(serialData[String(source.value)]?.[id]||[]),term=document.getElementById('serialSearch').value.toLowerCase();const f=arr.filter(x=>x.serial_number.toLowerCase().includes(term));document.getElementById('serialList').innerHTML=f.map(x=>`<label class="mb-2 flex items-center gap-3 rounded-xl border p-3 cursor-pointer"><input type="checkbox" ${selection.has(x.serial_number)?'checked':''} onchange="toggleSN('${esc(x.serial_number)}',this)"><span class="font-mono text-sm">${esc(x.serial_number)}</span></label>`).join('')||'<div class="p-8 text-center text-sm text-gray-500 dark:text-gray-400">Nomor seri tidak ditemukan.</div>';}
function toggleSN(sn,el){const q=parseInt(document.querySelector(`tr[data-row="${active}"] .qty-input`).value||'0',10);if(el.checked){if(selection.size>=q){el.checked=false;return;}selection.add(sn);}else selection.delete(sn);}
function autoSelect(){if(active===null)return;const tr=document.querySelector(`tr[data-row="${active}"]`),id=tr.querySelector('.item-select').value,q=parseInt(tr.querySelector('.qty-input').value||'0',10),arr=(serialData[String(source.value)]?.[id]||[]);selection=new Set(arr.slice(0,q).map(x=>x.serial_number));renderModal();}
function applySerials(){if(active===null)return;const tr=document.querySelector(`tr[data-row="${active}"]`),q=parseInt(tr.querySelector('.qty-input').value||'0',10);if(selection.size!==q){showError(`Pilih tepat ${q} nomor seri.`);return;}tr.querySelector('.sn-input').value=[...selection].join('\n');tr.querySelector('.sn-summary').textContent=`${selection.size} nomor seri dipilih`;closeModal();}
function esc(v){return String(v).replace(/[&<>\"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]||c));}
document.getElementById('serialSearch').addEventListener('input',renderModal);
document.getElementById('transferForm').addEventListener('submit',e=>{if(!source.value||!dest.value){e.preventDefault();showError('Gudang asal dan gudang tujuan wajib dipilih.');return;}if(source.value===dest.value){e.preventDefault();syncDestinationOptions();showError('Gudang tujuan harus berbeda dari gudang asal.');return;}});
syncDestinationOptions();
</script>
<?php $content=ob_get_clean();require __DIR__.'/../../layouts/app.php'; ?>
