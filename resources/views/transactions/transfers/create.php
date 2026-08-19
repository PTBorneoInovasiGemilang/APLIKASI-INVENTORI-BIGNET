<?php
$pageTitle = 'Transfer Gudang Baru';
ob_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 flex items-center gap-3">
        <a href="<?= url('/transfers') ?>" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700" aria-label="Kembali">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Transfer Gudang Baru</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pindahkan barang dari satu gudang ke gudang lain dengan pencatatan barang dan nomor seri yang jelas.</p>
        </div>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-300" role="alert">
            <div class="flex items-start gap-3">
                <i class="fas fa-circle-exclamation mt-0.5"></i>
                <span><?= htmlspecialchars($_SESSION['error']) ?></span>
            </div>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <form action="<?= url('/transfers') ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-200 px-6 py-5 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-300">
                        <i class="fas fa-route"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Rute Pengiriman</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Tentukan gudang asal dan gudang tujuan sebelum memilih barang.</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 px-6 py-6 md:grid-cols-2">
                <div>
                    <label for="source_warehouse_id" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Gudang Asal <span class="text-red-600">*</span></label>
                    <select id="source_warehouse_id" name="source_warehouse_id" required class="mt-2 block w-full rounded-xl border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Pilih gudang asal...</option>
                        <?php foreach ($warehouses as $wh): ?>
                            <option value="<?= (int)$wh['id'] ?>"><?= htmlspecialchars($wh['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="destination_warehouse_id" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Gudang Tujuan <span class="text-red-600">*</span></label>
                    <select id="destination_warehouse_id" name="destination_warehouse_id" required class="mt-2 block w-full rounded-xl border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Pilih gudang tujuan...</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label for="notes" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Catatan Pengiriman</label>
                    <textarea id="notes" name="notes" rows="3" class="mt-2 block w-full rounded-xl border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Contoh: Permintaan stok untuk Gudang Ngabang."></textarea>
                </div>

                <div class="md:col-span-2">
                    <div class="rounded-xl border border-blue-100 bg-blue-50/70 p-4 dark:border-blue-900/50 dark:bg-blue-950/20">
                        <div class="flex items-start gap-3">
                            <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-white text-blue-600 shadow-sm dark:bg-blue-950/50 dark:text-blue-300">
                                <i class="fas fa-camera"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <label for="dispatch_photo" class="block text-sm font-semibold text-gray-800 dark:text-gray-100">Foto Bukti Pengiriman <span class="text-red-600">*</span></label>
                                <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">Foto barang saat akan dikirim. Foto ini disimpan sebagai bukti pengiriman dan dapat ditampilkan pada detail transfer.</p>
                                <input id="dispatch_photo" name="dispatch_photo" type="file" accept="image/jpeg,image/png,image/webp" required class="mt-3 block w-full rounded-lg border border-gray-300 bg-white text-sm text-gray-700 file:mr-3 file:border-0 file:bg-blue-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-blue-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:file:bg-blue-600">
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">JPG, PNG, atau WEBP · maksimal 5 MB.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="warehouseHint" class="border-t border-gray-100 px-6 py-4 text-xs text-gray-500 dark:border-gray-700 dark:text-gray-400">Pilih gudang asal terlebih dahulu. Gudang tujuan yang sama akan otomatis disembunyikan.</div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex flex-col gap-3 border-b border-gray-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-300">
                        <i class="fas fa-boxes-stacked"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Barang yang Dikirim</h2>
                        <p id="itemHint" class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pilih gudang asal untuk menampilkan barang yang tersedia.</p>
                    </div>
                </div>
                <button type="button" onclick="addItemRow()" class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                    <i class="fas fa-plus"></i>
                    Tambah Barang
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" id="itemsTable">
                    <thead class="bg-gray-50/80 dark:bg-gray-900/30">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Barang</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 w-32">Jumlah</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 min-w-[360px]">Nomor Seri</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 w-20">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody" class="divide-y divide-gray-200 dark:divide-gray-700"></tbody>
                </table>
            </div>

            <div class="border-t border-gray-100 px-6 py-4 text-xs text-gray-500 dark:border-gray-700 dark:text-gray-400">
                Barang dengan nomor seri wajib diisi sesuai jumlah yang dikirim. Barang tanpa nomor seri cukup masukkan jumlahnya.
            </div>
        </section>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="<?= url('/transfers') ?>" class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">Batal</a>
            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                <i class="fas fa-paper-plane"></i>
                Simpan Transfer
            </button>
        </div>
    </form>
</div>

<script>
const itemsData = <?= json_encode($items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const warehousesData = <?= json_encode(array_map(function ($wh) {
    return ['id' => (int)$wh['id'], 'name' => $wh['name'] ?? ''];
}, $warehouses), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

const sourceWarehouse = document.getElementById('source_warehouse_id');
const destinationWarehouse = document.getElementById('destination_warehouse_id');
const warehouseHint = document.getElementById('warehouseHint');
const itemHint = document.getElementById('itemHint');
let rowCount = 0;

function itemLabel(item) {
    const name = String(item.nama ?? '').trim();
    const code = String(item.kode_barang ?? '').trim();
    // Jangan pernah menghasilkan "()" atau label kode kosong.
    return code ? `${name} · ${code}` : name;
}

function getSourceItems() {
    const sourceId = String(sourceWarehouse.value || '');
    return sourceId
        ? itemsData.filter(item => String(item.warehouse_id) === sourceId && Number(item.stock_quantity) > 0)
        : [];
}

function refreshDestinationOptions() {
    const sourceId = sourceWarehouse.value;
    const previous = destinationWarehouse.value;
    destinationWarehouse.innerHTML = '<option value="">Pilih gudang tujuan...</option>';

    warehousesData.forEach(wh => {
        if (String(wh.id) === String(sourceId)) return;
        const option = document.createElement('option');
        option.value = wh.id;
        option.textContent = wh.name;
        destinationWarehouse.appendChild(option);
    });

    if ([...destinationWarehouse.options].some(o => o.value === previous)) {
        destinationWarehouse.value = previous;
    }

    warehouseHint.textContent = sourceId
        ? (destinationWarehouse.options.length === 1
            ? 'Belum ada gudang tujuan lain yang tersedia.'
            : 'Gudang tujuan tidak boleh sama dengan gudang asal.')
        : 'Pilih gudang asal terlebih dahulu. Gudang tujuan yang sama akan otomatis disembunyikan.';
}

function refreshItemOptions() {
    const sourceItems = getSourceItems();
    document.querySelectorAll('.item-select').forEach(select => populateItemSelect(select));

    itemHint.textContent = !sourceWarehouse.value
        ? 'Pilih gudang asal untuk menampilkan barang yang tersedia.'
        : (sourceItems.length
            ? `Menampilkan ${sourceItems.length} jenis barang yang memiliki stok tersedia.`
            : 'Tidak ada barang dengan stok tersedia di gudang asal yang dipilih.');
}

function populateItemSelect(select) {
    const current = select.value;
    const sourceItems = getSourceItems();
    select.innerHTML = '';

    const placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.textContent = sourceWarehouse.value
        ? (sourceItems.length ? 'Pilih barang...' : 'Tidak ada barang tersedia')
        : 'Pilih gudang asal terlebih dahulu';
    select.appendChild(placeholder);

    sourceItems.forEach(item => {
        const option = document.createElement('option');
        option.value = item.id;
        option.dataset.serialized = Number(item.is_serialized) === 1 ? '1' : '0';
        option.dataset.stock = Number(item.stock_quantity || 0);
        option.textContent = itemLabel(item);
        select.appendChild(option);
    });

    select.disabled = !sourceWarehouse.value || sourceItems.length === 0;

    if (sourceItems.some(item => String(item.id) === String(current))) {
        select.value = current;
    } else {
        select.value = '';
        resetSerialState(select.closest('tr'));
    }
}

function resetSerialState(tr) {
    if (!tr) return;
    const snContainer = tr.querySelector('[id^="sn-container-"]');
    const snDisabled = tr.querySelector('[id^="sn-disabled-"]');
    const snInput = tr.querySelector('[id^="sn-input-"]');
    const qtyInput = tr.querySelector('.qty-input');

    if (snContainer) snContainer.classList.add('hidden');
    if (snDisabled) snDisabled.classList.remove('hidden');
    if (snInput) {
        snInput.removeAttribute('required');
        snInput.value = '';
    }
    if (qtyInput) {
        qtyInput.removeAttribute('max');
        qtyInput.value = 1;
    }
}

function handleItemChange(selectElem, rowIndex) {
    const option = selectElem.options[selectElem.selectedIndex];
    const tr = selectElem.closest('tr');
    const qty = tr ? tr.querySelector('.qty-input') : null;
    const stock = option && option.dataset.stock ? Number(option.dataset.stock) : 0;
    const isSerialized = option && option.dataset.serialized === '1';
    const snContainer = document.getElementById(`sn-container-${rowIndex}`);
    const snDisabled = document.getElementById(`sn-disabled-${rowIndex}`);
    const snInput = document.getElementById(`sn-input-${rowIndex}`);

    if (qty && stock > 0) {
        qty.max = stock;
        if (Number(qty.value) > stock) qty.value = stock;
    }

    if (!snContainer || !snDisabled || !snInput) return;

    if (isSerialized) {
        snContainer.classList.remove('hidden');
        snDisabled.classList.add('hidden');
        snInput.setAttribute('required', 'required');
        validateSnCount(rowIndex);
    } else {
        snContainer.classList.add('hidden');
        snDisabled.classList.remove('hidden');
        snInput.removeAttribute('required');
        snInput.value = '';
    }
}

function addItemRow() {
    const tbody = document.getElementById('itemsBody');
    const rowIndex = rowCount++;
    const tr = document.createElement('tr');
    tr.dataset.index = rowIndex;

    tr.innerHTML = `
        <td class="px-6 py-4 align-top">
            <select name="item_ids[]" required disabled class="item-select block w-full rounded-xl border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white" onchange="handleItemChange(this, ${rowIndex})">
                <option value="">Pilih gudang asal terlebih dahulu</option>
            </select>
        </td>
        <td class="px-4 py-4 align-top">
            <input type="number" name="quantities[]" min="1" value="1" required class="qty-input block w-full rounded-xl border-gray-300 bg-white px-3 py-2.5 text-sm text-center shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white" onchange="validateQty(this, ${rowIndex})" oninput="validateQty(this, ${rowIndex})">
            <div class="mt-1 text-center text-[11px] text-gray-400">stok tersedia akan dibatasi otomatis</div>
        </td>
        <td class="px-4 py-4 align-top">
            <div id="sn-container-${rowIndex}" class="hidden">
                <p class="mb-2 text-xs text-gray-500 dark:text-gray-400">Masukkan nomor seri yang benar-benar dikirim, satu nomor per baris.</p>
                <textarea name="sn_list[]" id="sn-input-${rowIndex}" rows="4" class="block w-full rounded-xl border-gray-300 bg-white px-3 py-2.5 font-mono text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="SN001\nSN002..." oninput="validateSnCount(${rowIndex})"></textarea>
                <div id="sn-counter-${rowIndex}" class="mt-1 text-xs font-semibold text-orange-500">Nomor seri diisi: 0 / 1</div>
            </div>
            <div id="sn-disabled-${rowIndex}" class="rounded-xl border border-dashed border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-400">
                Barang ini tidak menggunakan nomor seri.
            </div>
        </td>
        <td class="px-6 py-4 text-center align-top">
            <button type="button" onclick="this.closest('tr').remove()" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-red-200 text-red-600 hover:bg-red-50 dark:border-red-900/50 dark:text-red-400 dark:hover:bg-red-950/30" aria-label="Hapus barang">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;

    tbody.appendChild(tr);
    populateItemSelect(tr.querySelector('.item-select'));
}

function validateQty(qtyElem, rowIndex) {
    const max = parseInt(qtyElem.max || '0', 10);
    const value = parseInt(qtyElem.value || '0', 10);
    if (max > 0 && value > max) qtyElem.value = max;
    if (value < 1 || Number.isNaN(value)) qtyElem.value = 1;
    validateSnCount(rowIndex);
}

function validateSnCount(rowIndex) {
    const tr = document.querySelector(`tr[data-index="${rowIndex}"]`);
    if (!tr) return;

    const qtyInput = tr.querySelector('.qty-input');
    const snInput = document.getElementById(`sn-input-${rowIndex}`);
    const counter = document.getElementById(`sn-counter-${rowIndex}`);
    if (!qtyInput || !snInput || !counter) return;

    const expectedQty = parseInt(qtyInput.value || '0', 10);
    const actualQty = snInput.value.split(/[\n,]+/).map(s => s.trim()).filter(Boolean).length;

    counter.textContent = `Nomor seri diisi: ${actualQty} / ${expectedQty}`;
    counter.className = actualQty === expectedQty
        ? 'mt-1 text-xs font-semibold text-green-600'
        : (actualQty > expectedQty
            ? 'mt-1 text-xs font-semibold text-red-600'
            : 'mt-1 text-xs font-semibold text-orange-500');
}

sourceWarehouse.addEventListener('change', () => {
    refreshDestinationOptions();
    refreshItemOptions();
});

refreshDestinationOptions();
addItemRow();
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
