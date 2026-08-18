<?php
$pageTitle = 'Transfer Gudang Baru';
ob_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 flex items-center">
        <a href="<?= url('/transfers') ?>" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 mr-4">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Transfer Gudang Baru</h1>
        </div>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            <span class="block sm:inline"><?= htmlspecialchars($_SESSION['error']) ?></span>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <form action="<?= url('/transfers') ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 border-b dark:border-gray-700 pb-2">Detail Transfer</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="source_warehouse_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Gudang Asal</label>
                    <select id="source_warehouse_id" name="source_warehouse_id" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Pilih Gudang Asal...</option>
                        <?php foreach ($warehouses as $wh): ?>
                            <option value="<?= $wh['id'] ?>"><?= htmlspecialchars($wh['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="destination_warehouse_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Gudang Tujuan</label>
                    <select id="destination_warehouse_id" name="destination_warehouse_id" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Pilih Gudang Tujuan...</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Catatan / Alasan</label>
                    <textarea name="notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                </div>
                <div class="md:col-span-2">
                    <label for="dispatch_photo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Foto Bukti Pengiriman <span class="font-normal text-gray-400">(Opsional)</span></label>
                    <input id="dispatch_photo" name="dispatch_photo" type="file" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full text-sm text-gray-700 dark:text-gray-200 file:mr-4 file:rounded-md file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-gray-700 dark:file:text-blue-300">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">JPG, PNG, atau WEBP. Maksimal 5 MB.</p>
                </div>
            </div>
            <p id="warehouseHint" class="mt-3 text-xs text-gray-500 dark:text-gray-400">Pilih gudang asal terlebih dahulu. Gudang tujuan akan otomatis mengecualikan gudang asal.</p>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <div class="flex justify-between items-center mb-4 border-b dark:border-gray-700 pb-2">
                <div>
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Daftar Barang yang Ditransfer</h2>
                    <p id="itemHint" class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pilih gudang asal untuk melihat barang yang tersedia di gudang tersebut.</p>
                </div>
                <button type="button" onclick="addItemRow()" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-sm font-medium">
                    <i class="fas fa-plus"></i> Tambah Barang
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" id="itemsTable">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Barang</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase w-32">Kuantitas</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Serial Number (Jika Berlaku)</th>
                            <th class="px-4 py-2 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase w-16">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody" class="divide-y divide-gray-200 dark:divide-gray-700"></tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-end gap-4">
            <a href="<?= url('/transfers') ?>" class="bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 py-2 px-6 rounded-md shadow-sm text-sm font-medium">Batal</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-6 rounded-md shadow-sm text-sm font-medium">Simpan Transfer</button>
        </div>
    </form>
</div>

<script>
const itemsData = <?= json_encode($items) ?>;
const warehousesData = <?= json_encode(array_map(function ($wh) { return ['id' => (int)$wh['id'], 'name' => $wh['name'] ?? '']; }, $warehouses)) ?>;
const sourceWarehouse = document.getElementById('source_warehouse_id');
const destinationWarehouse = document.getElementById('destination_warehouse_id');
const warehouseHint = document.getElementById('warehouseHint');
const itemHint = document.getElementById('itemHint');
let rowCount = 0;

function getSourceItems() {
    const sourceId = String(sourceWarehouse.value || '');
    return sourceId ? itemsData.filter(item => String(item.warehouse_id) === sourceId && Number(item.stock_quantity) > 0) : [];
}

function refreshDestinationOptions() {
    const sourceId = sourceWarehouse.value;
    destinationWarehouse.innerHTML = '<option value="">Pilih Gudang Tujuan...</option>';
    warehousesData.forEach(wh => {
        if (String(wh.id) === String(sourceId)) return;
        const option = document.createElement('option');
        option.value = wh.id;
        option.textContent = wh.name;
        destinationWarehouse.appendChild(option);
    });
    warehouseHint.textContent = sourceId
        ? (destinationWarehouse.options.length === 1 ? 'Tidak ada gudang tujuan lain yang tersedia selain gudang asal.' : 'Gudang tujuan tidak dapat sama dengan gudang asal.')
        : 'Pilih gudang asal terlebih dahulu. Gudang tujuan akan otomatis mengecualikan gudang asal.';
}

function refreshItemOptions() {
    const sourceItems = getSourceItems();
    document.querySelectorAll('.item-select').forEach(select => populateItemSelect(select));
    itemHint.textContent = !sourceWarehouse.value
        ? 'Pilih gudang asal untuk melihat barang yang tersedia di gudang tersebut.'
        : (sourceItems.length ? `Menampilkan ${sourceItems.length} barang yang memiliki stok di gudang asal.` : 'Tidak ada barang dengan stok tersedia di gudang asal yang dipilih.');
}

function populateItemSelect(select) {
    const current = select.value;
    const sourceItems = getSourceItems();
    select.innerHTML = '';
    const placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.textContent = sourceWarehouse.value ? (sourceItems.length ? 'Pilih Barang' : 'Tidak ada barang tersedia di gudang ini') : 'Pilih Gudang Asal Terlebih Dahulu';
    select.appendChild(placeholder);
    sourceItems.forEach(item => {
        const opt = document.createElement('option');
        opt.value = item.id;
        opt.dataset.serialized = item.is_serialized;
        opt.dataset.stock = item.stock_quantity;
        opt.textContent = `${item.nama} (${item.kode_barang})`;
        select.appendChild(opt);
    });
    select.disabled = !sourceWarehouse.value || sourceItems.length === 0;
    if (sourceItems.some(item => String(item.id) === String(current))) select.value = current;
    else { select.value = ''; resetSerialState(select.closest('tr')); }
}

function resetSerialState(tr) {
    if (!tr) return;
    const snContainer = tr.querySelector('[id^="sn-container-"]');
    const snDisabled = tr.querySelector('[id^="sn-disabled-"]');
    const snInput = tr.querySelector('[id^="sn-input-"]');
    const qtyInput = tr.querySelector('.qty-input');
    if (snContainer) snContainer.classList.add('hidden');
    if (snDisabled) snDisabled.classList.remove('hidden');
    if (snInput) { snInput.removeAttribute('required'); snInput.value = ''; }
    if (qtyInput) { qtyInput.removeAttribute('max'); qtyInput.value = 1; }
}

function handleItemChange(selectElem, rowIndex) {
    const option = selectElem.options[selectElem.selectedIndex];
    const tr = selectElem.closest('tr');
    const qty = tr ? tr.querySelector('.qty-input') : null;
    const stock = option && option.dataset.stock ? Number(option.dataset.stock) : 0;
    if (qty && stock > 0) { qty.max = stock; if (Number(qty.value) > stock) qty.value = stock; }
    const isSerialized = option && option.dataset.serialized === '1';
    const snContainer = document.getElementById(`sn-container-${rowIndex}`);
    const snDisabled = document.getElementById(`sn-disabled-${rowIndex}`);
    const snInput = document.getElementById(`sn-input-${rowIndex}`);
    if (!snContainer || !snDisabled || !snInput) return;
    if (isSerialized) {
        snContainer.classList.remove('hidden'); snDisabled.classList.add('hidden'); snInput.setAttribute('required', 'required'); validateSnCount(rowIndex);
    } else {
        snContainer.classList.add('hidden'); snDisabled.classList.remove('hidden'); snInput.removeAttribute('required'); snInput.value = '';
    }
}

function addItemRow() {
    const tbody = document.getElementById('itemsBody');
    const rowIndex = rowCount++;
    const tr = document.createElement('tr');
    tr.dataset.index = rowIndex;
    tr.innerHTML = `
        <td class="px-4 py-3 align-top"><select name="item_ids[]" required disabled class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm text-sm item-select" onchange="handleItemChange(this, ${rowIndex})"><option value="">Pilih Gudang Asal Terlebih Dahulu</option></select></td>
        <td class="px-4 py-3 align-top"><input type="number" name="quantities[]" min="1" value="1" required class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm text-sm text-center qty-input" onchange="validateQty(this, ${rowIndex})" onkeyup="validateQty(this, ${rowIndex})"></td>
        <td class="px-4 py-3 align-top">
            <div id="sn-container-${rowIndex}" class="hidden"><p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Masukkan Serial Number yang akan ditransfer. SN harus tersedia di gudang asal.</p><textarea name="sn_list[]" id="sn-input-${rowIndex}" rows="3" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm text-sm" placeholder="SN001\nSN002..." onkeyup="validateSnCount(${rowIndex})" onchange="validateSnCount(${rowIndex})"></textarea><div id="sn-counter-${rowIndex}" class="text-xs font-bold text-red-500 mt-1">SN Diisi: 0 / 1</div></div>
            <div id="sn-disabled-${rowIndex}" class="text-sm text-gray-500 dark:text-gray-400 italic py-2">Bukan barang serial.</div>
        </td>
        <td class="px-4 py-3 text-center align-top"><button type="button" onclick="this.closest('tr').remove()" class="text-red-500 hover:text-red-700 mt-1"><i class="fas fa-trash"></i></button></td>`;
    tbody.appendChild(tr);
    populateItemSelect(tr.querySelector('.item-select'));
}

function validateQty(qtyElem, rowIndex) {
    const max = parseInt(qtyElem.max || '0', 10), value = parseInt(qtyElem.value || '0', 10);
    if (max > 0 && value > max) qtyElem.value = max;
    validateSnCount(rowIndex);
}

function validateSnCount(rowIndex) {
    const tr = document.querySelector(`tr[data-index="${rowIndex}"]`);
    if (!tr) return;
    const qtyInput = tr.querySelector('.qty-input'), snInput = document.getElementById(`sn-input-${rowIndex}`), counter = document.getElementById(`sn-counter-${rowIndex}`);
    if (!qtyInput || !snInput || !counter) return;
    const expectedQty = parseInt(qtyInput.value) || 0;
    const actualQty = snInput.value.split(/[\n,]+/).map(s => s.trim()).filter(Boolean).length;
    counter.innerText = `SN Diisi: ${actualQty} / ${expectedQty}`;
    counter.className = actualQty === expectedQty ? 'text-xs font-bold text-green-500 mt-1' : (actualQty > expectedQty ? 'text-xs font-bold text-red-500 mt-1' : 'text-xs font-bold text-orange-500 mt-1');
    if (actualQty > expectedQty) counter.innerText += ' (Kelebihan!)'; else if (actualQty < expectedQty) counter.innerText += ' (Kurang!)';
}

sourceWarehouse.addEventListener('change', () => { refreshDestinationOptions(); refreshItemOptions(); });
refreshDestinationOptions();
addItemRow();
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
