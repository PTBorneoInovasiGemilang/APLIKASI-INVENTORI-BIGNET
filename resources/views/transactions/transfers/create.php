<?php
$pageTitle = 'Transfer Gudang Baru';
ob_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$availableSerials = [];
try {
    $pdo = \App\Core\Database::getInstance()->getConnection();
    $serialStmt = $pdo->query("SELECT id, master_item_id, current_warehouse_id, serial_number
        FROM serial_numbers
        WHERE status = 'AVAILABLE'
          AND current_warehouse_id IS NOT NULL
        ORDER BY serial_number ASC");
    foreach ($serialStmt->fetchAll(PDO::FETCH_ASSOC) as $sn) {
        $warehouseId = (int)$sn['current_warehouse_id'];
        $masterItemId = (int)$sn['master_item_id'];
        $availableSerials[$warehouseId][$masterItemId][] = [
            'id' => (int)$sn['id'],
            'serial_number' => $sn['serial_number'],
        ];
    }
} catch (Throwable $e) {
    $availableSerials = [];
}
?>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 flex items-center gap-3">
        <a href="<?= url('/transfers') ?>" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700" aria-label="Kembali"><i class="fas fa-arrow-left"></i></a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Transfer Gudang Baru</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pilih barang dari stok gudang asal. Untuk barang yang memakai nomor seri, pilih nomor seri langsung dari stok yang tersedia.</p>
        </div>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-300" role="alert">
            <div class="flex items-start gap-3"><i class="fas fa-circle-exclamation mt-0.5"></i><span><?= htmlspecialchars($_SESSION['error']) ?></span></div>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <form id="transferForm" action="<?= url('/transfers') ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-200 px-6 py-5 dark:border-gray-700">
                <div class="flex items-center gap-3"><div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-300"><i class="fas fa-route"></i></div><div><h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Rute Pengiriman</h2><p class="text-xs text-gray-500 dark:text-gray-400">Tentukan gudang asal dan gudang tujuan.</p></div></div>
            </div>
            <div class="grid grid-cols-1 gap-5 px-6 py-6 md:grid-cols-2">
                <div><label for="source_warehouse_id" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Gudang Asal <span class="text-red-600">*</span></label><select id="source_warehouse_id" name="source_warehouse_id" required class="mt-2 block w-full rounded-xl border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"><option value="">Pilih gudang asal...</option><?php foreach ($warehouses as $wh): ?><option value="<?= (int)$wh['id'] ?>"><?= htmlspecialchars($wh['name']) ?></option><?php endforeach; ?></select></div>
                <div><label for="destination_warehouse_id" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Gudang Tujuan <span class="text-red-600">*</span></label><select id="destination_warehouse_id" name="destination_warehouse_id" required class="mt-2 block w-full rounded-xl border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"><option value="">Pilih gudang tujuan...</option></select></div>
                <div class="md:col-span-2"><label for="notes" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Catatan Pengiriman</label><textarea id="notes" name="notes" rows="3" class="mt-2 block w-full rounded-xl border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Contoh: Permintaan stok untuk Gudang Ngabang."></textarea></div>
                <div class="md:col-span-2"><div class="rounded-xl border border-blue-100 bg-blue-50/70 p-4 dark:border-blue-900/50 dark:bg-blue-950/20"><div class="flex items-start gap-3"><div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-white text-blue-600 shadow-sm dark:bg-blue-950/50 dark:text-blue-300"><i class="fas fa-camera"></i></div><div class="min-w-0 flex-1"><label for="dispatch_photo" class="block text-sm font-semibold text-gray-800 dark:text-gray-100">Foto Bukti Pengiriman <span class="text-red-600">*</span></label><p class="mt-1 text-xs text-gray-600 dark:text-gray-300">Foto barang sebelum dikirim. Foto ini menjadi bukti pengiriman.</p><input id="dispatch_photo" name="dispatch_photo" type="file" accept="image/jpeg,image/png,image/webp" required class="mt-3 block w-full rounded-lg border border-gray-300 bg-white text-sm text-gray-700 file:mr-3 file:border-0 file:bg-blue-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-blue-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:file:bg-blue-600"><p class="mt-1 text-xs text-gray-500 dark:text-gray-400">JPG, PNG, atau WEBP · maksimal 5 MB.</p></div></div></div></div>
            </div>
            <div id="warehouseHint" class="border-t border-gray-100 px-6 py-4 text-xs text-gray-500 dark:border-gray-700 dark:text-gray-400">Pilih gudang asal terlebih dahulu. Gudang tujuan yang sama akan otomatis disembunyikan.</div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex flex-col gap-3 border-b border-gray-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between dark:border-gray-700"><div class="flex items-center gap-3"><div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-300"><i class="fas fa-boxes-stacked"></i></div><div><h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Barang yang Dikirim</h2><p id="itemHint" class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pilih gudang asal untuk menampilkan barang yang tersedia.</p></div></div><button type="button" onclick="addItemRow()" class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700"><i class="fas fa-plus"></i>Tambah Barang</button></div>
            <div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" id="itemsTable"><thead class="bg-gray-50/80 dark:bg-gray-900/30"><tr><th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Barang</th><th class="w-32 px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Jumlah</th><th class="min-w-[390px] px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Nomor Seri</th><th class="w-20 px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Aksi</th></tr></thead><tbody id="itemsBody" class="divide-y divide-gray-200 dark:divide-gray-700"></tbody></table></div>
            <div class="border-t border-gray-100 px-6 py-4 text-xs text-gray-500 dark:border-gray-700 dark:text-gray-400">Barang tanpa nomor seri cukup tentukan jumlah. Barang dengan nomor seri dipilih langsung dari daftar SN yang tersedia, sehingga 30 atau 100 modem tidak perlu diketik satu per satu.</div>
        </section>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end"><a href="<?= url('/transfers') ?>" class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">Batal</a><button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700"><i class="fas fa-paper-plane"></i>Simpan Transfer</button></div>
    </form>
</div>

<div id="serialModal" class="fixed inset-0 z-[120] hidden items-center justify-center bg-gray-950/50 p-4 backdrop-blur-sm">
    <div class="w-full max-w-3xl overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-start justify-between border-b border-gray-200 px-6 py-5 dark:border-gray-700"><div><h3 id="serialModalTitle" class="text-lg font-bold text-gray-900 dark:text-white">Pilih Nomor Seri</h3><p id="serialModalSubtitle" class="mt-1 text-xs text-gray-500 dark:text-gray-400">Pilih SN yang benar-benar akan dikirim.</p></div><button type="button" onclick="closeSerialModal()" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-700" aria-label="Tutup"><i class="fas fa-times"></i></button></div>
        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700"><div class="grid gap-3 md:grid-cols-[1fr_auto_auto] md:items-center"><div class="relative"><i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-400"></i><input id="serialSearch" type="search" placeholder="Cari nomor seri..." class="w-full rounded-xl border border-gray-300 bg-white py-2.5 pl-9 pr-3 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></div><button type="button" onclick="autoSelectSerials()" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700"><i class="fas fa-wand-magic-sparkles"></i>Pilih Otomatis</button><button type="button" onclick="clearSerialSelection()" class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"><i class="fas fa-eraser"></i>Kosongkan</button></div><div class="mt-3 flex flex-wrap items-center justify-between gap-2 text-xs"><span id="serialSelectionStatus" class="font-semibold text-gray-700 dark:text-gray-200">0 dipilih</span><span id="serialAvailabilityStatus" class="text-gray-500 dark:text-gray-400">0 tersedia</span></div></div>
        <div id="serialList" class="max-h-[55vh] overflow-y-auto px-6 py-4"></div>
        <div class="flex flex-col-reverse gap-3 border-t border-gray-200 bg-gray-50 px-6 py-4 sm:flex-row sm:justify-end dark:border-gray-700 dark:bg-gray-900/40"><button type="button" onclick="closeSerialModal()" class="rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">Batal</button><button type="button" onclick="applySerialSelection()" class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Gunakan Nomor Seri</button></div>
    </div>
</div>

<script>
const itemsData = <?= json_encode($items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const warehousesData = <?= json_encode(array_map(fn($wh) => ['id' => (int)$wh['id'], 'name' => $wh['name'] ?? ''], $warehouses), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const availableSerials = <?= json_encode($availableSerials, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const sourceWarehouse = document.getElementById('source_warehouse_id');
const destinationWarehouse = document.getElementById('destination_warehouse_id');
const warehouseHint = document.getElementById('warehouseHint');
const itemHint = document.getElementById('itemHint');
const transferForm = document.getElementById('transferForm');
const serialModal = document.getElementById('serialModal');
const serialSearch = document.getElementById('serialSearch');
const serialList = document.getElementById('serialList');
let rowCount = 0;
let activeSerialRow = null;
let workingSerialSelection = new Set();
let workingSerialOptions = [];

function itemLabel(item) {
    return String(item.nama ?? '').trim();
}
function getSourceItems() { const sourceId = String(sourceWarehouse.value || ''); return sourceId ? itemsData.filter(item => String(item.warehouse_id) === sourceId && Number(item.stock_quantity) > 0) : []; }
function getSerialOptions(itemId) { const sourceId = String(sourceWarehouse.value || ''); return (availableSerials[sourceId] && availableSerials[sourceId][String(itemId)]) ? availableSerials[sourceId][String(itemId)] : []; }
function refreshDestinationOptions() { const sourceId = sourceWarehouse.value; const previous = destinationWarehouse.value; destinationWarehouse.innerHTML = '<option value="">Pilih gudang tujuan...</option>'; warehousesData.forEach(wh => { if (String(wh.id) === String(sourceId)) return; const option = document.createElement('option'); option.value = wh.id; option.textContent = wh.name; destinationWarehouse.appendChild(option); }); if ([...destinationWarehouse.options].some(o => o.value === previous)) destinationWarehouse.value = previous; warehouseHint.textContent = sourceId ? (destinationWarehouse.options.length === 1 ? 'Belum ada gudang tujuan lain yang tersedia.' : 'Gudang tujuan tidak boleh sama dengan gudang asal.') : 'Pilih gudang asal terlebih dahulu. Gudang tujuan yang sama akan otomatis disembunyikan.'; }
function refreshItemOptions() { const sourceItems = getSourceItems(); document.querySelectorAll('.item-select').forEach(select => populateItemSelect(select)); itemHint.textContent = !sourceWarehouse.value ? 'Pilih gudang asal untuk menampilkan barang yang tersedia.' : (sourceItems.length ? `Menampilkan ${sourceItems.length} jenis barang yang memiliki stok tersedia.` : 'Tidak ada barang dengan stok tersedia di gudang asal yang dipilih.'); }
function populateItemSelect(select) { const current = select.value; const sourceItems = getSourceItems(); select.innerHTML = ''; const placeholder = document.createElement('option'); placeholder.value = ''; placeholder.textContent = sourceWarehouse.value ? (sourceItems.length ? 'Pilih barang...' : 'Tidak ada barang tersedia') : 'Pilih gudang asal terlebih dahulu'; select.appendChild(placeholder); sourceItems.forEach(item => { const option = document.createElement('option'); option.value = item.id; option.dataset.serialized = Number(item.is_serialized) === 1 ? '1' : '0'; option.dataset.stock = Number(item.stock_quantity || 0); option.textContent = itemLabel(item); select.appendChild(option); }); select.disabled = !sourceWarehouse.value || sourceItems.length === 0; if (sourceItems.some(item => String(item.id) === String(current))) select.value = current; else { select.value = ''; resetRowState(select.closest('tr')); } }
function resetRowState(tr) { if (!tr) return; const summary = tr.querySelector('[id^="sn-summary-"]'); const input = tr.querySelector('[id^="sn-input-"]'); const picker = tr.querySelector('[id^="sn-picker-"]'); const qty = tr.querySelector('.qty-input'); if (input) input.value = ''; if (summary) summary.innerHTML = '<span class="text-gray-500 dark:text-gray-400">Belum ada nomor seri dipilih.</span>'; if (picker) picker.classList.add('hidden'); if (qty) { qty.removeAttribute('max'); qty.value = 1; } }
function handleItemChange(selectElem, rowIndex) { const option = selectElem.options[selectElem.selectedIndex]; const tr = selectElem.closest('tr'); const qty = tr ? tr.querySelector('.qty-input') : null; const stock = option && option.dataset.stock ? Number(option.dataset.stock) : 0; const isSerialized = option && option.dataset.serialized === '1'; const snSection = document.getElementById(`sn-section-${rowIndex}`); const nonSn = document.getElementById(`sn-non-${rowIndex}`); const picker = document.getElementById(`sn-picker-${rowIndex}`); if (qty && stock > 0) { qty.max = stock; if (Number(qty.value) > stock) qty.value = stock; } if (!snSection || !nonSn || !picker) return; if (isSerialized) { snSection.classList.remove('hidden'); nonSn.classList.add('hidden'); picker.classList.remove('hidden'); renderRowSerialSummary(tr); } else { snSection.classList.add('hidden'); nonSn.classList.remove('hidden'); picker.classList.add('hidden'); const input = tr.querySelector('[id^="sn-input-"]'); if (input) input.value = ''; } }
function escapeHtml(value) { return String(value).replace(/[&<>\"]/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[ch])); }
function renderRowSerialSummary(tr) { if (!tr) return; const input = tr.querySelector('[id^="sn-input-"]'); const summary = tr.querySelector('[id^="sn-summary-"]'); if (!input || !summary) return; const selected = String(input.value || '').split(/\n+/).map(v => v.trim()).filter(Boolean); if (!selected.length) { summary.innerHTML = '<span class="text-gray-500 dark:text-gray-400">Belum ada nomor seri dipilih.</span>'; return; } const preview = selected.slice(0, 4).map(sn => `<span class="rounded-lg border border-blue-100 bg-blue-50 px-2 py-1 font-mono text-[11px] text-blue-700 dark:border-blue-900/50 dark:bg-blue-950/30 dark:text-blue-300">${escapeHtml(sn)}</span>`).join(''); const extra = selected.length > 4 ? `<span class="rounded-lg bg-gray-100 px-2 py-1 text-[11px] font-semibold text-gray-600 dark:bg-gray-700 dark:text-gray-200">+${selected.length - 4} lainnya</span>` : ''; summary.innerHTML = `<div class="mb-2 text-sm font-semibold text-gray-800 dark:text-gray-100">${selected.length} nomor seri dipilih</div><div class="flex flex-wrap gap-1.5">${preview}${extra}</div>`; }
function addItemRow() { const tbody = document.getElementById('itemsBody'); const rowIndex = rowCount++; const tr = document.createElement('tr'); tr.dataset.index = rowIndex; tr.innerHTML = `<td class="px-6 py-4 align-top"><select name="item_ids[]" required disabled class="item-select block w-full rounded-xl border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white" onchange="handleItemChange(this, ${rowIndex})"><option value="">Pilih gudang asal terlebih dahulu</option></select></td><td class="px-4 py-4 align-top"><input type="number" name="quantities[]" min="1" value="1" required class="qty-input block w-full rounded-xl border-gray-300 bg-white px-3 py-2.5 text-center text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white" onchange="validateQty(this, ${rowIndex})" oninput="validateQty(this, ${rowIndex})"><div class="mt-1 text-center text-[11px] text-gray-400">maksimal sesuai stok</div></td><td class="px-4 py-4 align-top"><div id="sn-section-${rowIndex}" class="hidden"><div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"><div id="sn-summary-${rowIndex}" class="min-w-0"><span class="text-gray-500 dark:text-gray-400">Belum ada nomor seri dipilih.</span></div><button type="button" id="sn-picker-${rowIndex}" class="hidden shrink-0 rounded-xl border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100 dark:border-blue-900/50 dark:bg-blue-950/30 dark:text-blue-300" onclick="openSerialModal(${rowIndex})"><i class="fas fa-list-check mr-1"></i>Pilih Nomor Seri</button></div><textarea name="sn_list[]" id="sn-input-${rowIndex}" class="hidden" aria-hidden="true"></textarea></div><div id="sn-non-${rowIndex}" class="py-2 text-sm text-gray-500 dark:text-gray-400">Barang ini tidak menggunakan nomor seri.</div></td><td class="px-6 py-4 text-center align-top"><button type="button" onclick="this.closest('tr').remove()" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-red-200 bg-white text-red-500 hover:bg-red-50 dark:border-red-900/50 dark:bg-gray-800 dark:text-red-300 dark:hover:bg-red-950/30" title="Hapus"><i class="fas fa-trash"></i></button></td>`; tbody.appendChild(tr); populateItemSelect(tr.querySelector('.item-select')); }
function validateQty(qtyElem, rowIndex) { const tr = document.querySelector(`tr[data-index="${rowIndex}"]`); const max = parseInt(qtyElem.max || '0', 10); let value = parseInt(qtyElem.value || '0', 10); if (max > 0 && value > max) { value = max; qtyElem.value = max; } if (value < 1) { qtyElem.value = 1; value = 1; } if (!tr) return; const select = tr.querySelector('.item-select'); if (select && select.value && select.options[select.selectedIndex]?.dataset.serialized === '1') { const input = tr.querySelector('[id^="sn-input-"]'); const selected = String(input?.value || '').split(/\n+/).map(v => v.trim()).filter(Boolean); if (selected.length > value) input.value = selected.slice(0, value).join('\n'); renderRowSerialSummary(tr); } }
function openSerialModal(rowIndex) { const tr = document.querySelector(`tr[data-index="${rowIndex}"]`); if (!tr) return; const select = tr.querySelector('.item-select'); const option = select?.options[select.selectedIndex]; if (!option || !option.value || option.dataset.serialized !== '1') return; activeSerialRow = rowIndex; workingSerialOptions = getSerialOptions(option.value); const current = String(tr.querySelector('[id^="sn-input-"]')?.value || '').split(/\n+/).map(v => v.trim()).filter(Boolean); workingSerialSelection = new Set(current); document.getElementById('serialModalTitle').textContent = `Pilih Nomor Seri — ${option.textContent}`; document.getElementById('serialModalSubtitle').textContent = `Jumlah dikirim: ${tr.querySelector('.qty-input')?.value || 0}. Pilih langsung dari SN yang tersedia di gudang asal.`; serialSearch.value = ''; serialModal.classList.remove('hidden'); serialModal.classList.add('flex'); document.body.classList.add('overflow-hidden'); renderSerialList(); setTimeout(() => serialSearch.focus(), 50); }
function closeSerialModal() { serialModal.classList.add('hidden'); serialModal.classList.remove('flex'); document.body.classList.remove('overflow-hidden'); activeSerialRow = null; workingSerialOptions = []; workingSerialSelection = new Set(); }
function getActiveQty() { const tr = document.querySelector(`tr[data-index="${activeSerialRow}"]`); return tr ? parseInt(tr.querySelector('.qty-input')?.value || '0', 10) : 0; }
function renderSerialList() { const q = String(serialSearch.value || '').trim().toLowerCase(); const filtered = workingSerialOptions.filter(sn => String(sn.serial_number || '').toLowerCase().includes(q)); const qty = getActiveQty(); document.getElementById('serialSelectionStatus').textContent = `${workingSerialSelection.size} / ${qty} dipilih`; document.getElementById('serialAvailabilityStatus').textContent = `${workingSerialOptions.length} nomor seri tersedia`; if (!filtered.length) { serialList.innerHTML = '<div class="rounded-xl border border-dashed border-gray-300 p-8 text-center text-sm text-gray-500 dark:border-gray-600 dark:text-gray-400">Nomor seri tidak ditemukan.</div>'; return; } serialList.innerHTML = filtered.map(sn => { const checked = workingSerialSelection.has(String(sn.serial_number)); return `<label class="mb-2 flex cursor-pointer items-center gap-3 rounded-xl border px-4 py-3 transition ${checked ? 'border-blue-300 bg-blue-50 dark:border-blue-800 dark:bg-blue-950/30' : 'border-gray-200 bg-white hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700'}"><input type="checkbox" class="serial-check h-4 w-4 rounded border-gray-300 dark:border-gray-700 text-blue-600 focus:ring-blue-500" data-sn="${escapeHtml(sn.serial_number)}" ${checked ? 'checked' : ''} onchange="toggleSerialSelection(this)"><span class="font-mono text-sm text-gray-800 dark:text-gray-100">${escapeHtml(sn.serial_number)}</span></label>`; }).join(''); }
function toggleSerialSelection(checkbox) { const sn = checkbox.dataset.sn; const qty = getActiveQty(); if (checkbox.checked) { if (workingSerialSelection.size >= qty) { checkbox.checked = false; return; } workingSerialSelection.add(sn); } else workingSerialSelection.delete(sn); renderSerialList(); }
function autoSelectSerials() { const qty = getActiveQty(); workingSerialSelection = new Set(workingSerialOptions.slice(0, qty).map(sn => String(sn.serial_number))); renderSerialList(); }
function clearSerialSelection() { workingSerialSelection.clear(); renderSerialList(); }
function applySerialSelection() { const tr = document.querySelector(`tr[data-index="${activeSerialRow}"]`); if (!tr) return; const qty = getActiveQty(); if (workingSerialSelection.size !== qty) { document.getElementById('serialSelectionStatus').className = 'font-semibold text-red-600'; document.getElementById('serialSelectionStatus').textContent = `Harus memilih tepat ${qty} nomor seri`; return; } const input = tr.querySelector('[id^="sn-input-"]'); input.value = Array.from(workingSerialSelection).join('\n'); renderRowSerialSummary(tr); closeSerialModal(); }
serialSearch.addEventListener('input', renderSerialList);
serialModal.addEventListener('click', e => { if (e.target === serialModal) closeSerialModal(); });
sourceWarehouse.addEventListener('change', () => { refreshDestinationOptions(); refreshItemOptions(); });
transferForm.addEventListener('submit', e => {
    let valid = true;
    document.querySelectorAll('#itemsBody tr').forEach(tr => {
        const select = tr.querySelector('.item-select');
        const qty = parseInt(tr.querySelector('.qty-input')?.value || '0', 10);
        const option = select?.options[select.selectedIndex];
        if (!option || !option.value) return;
        if (option.dataset.serialized === '1') {
            const selected = String(tr.querySelector('[id^="sn-input-"]')?.value || '').split(/\n+/).map(v => v.trim()).filter(Boolean);
            if (selected.length !== qty) valid = false;
        }
    });
    if (!valid) {
        e.preventDefault();
        if (window.AppToast && typeof window.AppToast.error === 'function') AppToast.error('Lengkapi pilihan nomor seri sesuai jumlah barang yang akan dikirim.', 'Data belum lengkap');
        else window.dispatchEvent(new CustomEvent('bignet:toast', { detail: { type: 'error', message: 'Lengkapi pilihan nomor seri sesuai jumlah barang yang akan dikirim.' } }));
    }
});
refreshDestinationOptions();
addItemRow();
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>