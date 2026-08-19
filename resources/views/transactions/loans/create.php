<?php
$pageTitle = 'Peminjaman Baru';
ob_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
use App\Helpers\AuthHelper;
?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 flex items-center">
        <a href="<?= url('/loans') ?>" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 mr-4">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Peminjaman Baru</h1>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            <span class="block sm:inline"><?= htmlspecialchars($_SESSION['error']) ?></span>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <form action="<?= url('/loans/store') ?>" method="POST" class="space-y-6" id="loanForm">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 border-b dark:border-gray-700 pb-2">Detail Peminjaman</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Gudang Asal</label>
                    <select name="warehouse_id" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm">
                        <option value="">Pilih Gudang</option>
                        <?php foreach ($warehouses as $wh): ?>
                            <option value="<?= $wh['id'] ?>"><?= htmlspecialchars($wh['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipe Peminjaman</label>
                    <select name="loan_type" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm">
                        <option value="INTERNAL">Internal</option>
                        <option value="EXTERNAL">Eksternal</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Referensi Pengajuan (Opsional)</label>
                    <select name="submission_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm">
                        <option value="">Tanpa Pengajuan</option>
                        <?php foreach ($submissions as $sub): ?>
                            <option value="<?= $sub['id'] ?>">Pengajuan tersedia</option>
                        <?php endforeach; ?>
                    </select>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Kode pengajuan tidak ditampilkan di UI.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Jatuh Tempo (Opsional)</label>
                    <input type="date" name="due_date" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Catatan / Alasan</label>
                    <textarea name="notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm"></textarea>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <div class="flex justify-between items-center mb-4 border-b dark:border-gray-700 pb-2">
                <div>
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Daftar Barang yang Dipinjam</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Bisa mengambil dari stok atau mencatat barang manual/custom.</p>
                </div>
                <button type="button" onclick="addItemRow()" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 text-sm font-medium">
                    <i class="fas fa-plus"></i> Tambah Barang
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" id="itemsTable">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Sumber Barang</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Barang</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase w-28">Kuantitas</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Serial Number (Jika Berlaku)</th>
                            <th class="px-4 py-2 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase w-16">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody" class="divide-y divide-gray-200 dark:divide-gray-700"></tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-end gap-4">
            <a href="<?= url('/loans') ?>" class="bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 py-2 px-6 rounded-md shadow-sm text-sm font-medium">Batal</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-6 rounded-md shadow-sm text-sm font-medium">Simpan Peminjaman</button>
        </div>
    </form>
</div>

<script>
const itemsData = <?= json_encode($items, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
let rowCount = 0;

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[ch]));
}

function addItemRow() {
    const tbody = document.getElementById('itemsBody');
    const index = rowCount++;
    const tr = document.createElement('tr');
    tr.dataset.index = index;

    const options = itemsData.map(item => `<option value="${item.id}" data-serialized="${item.is_serialized}">${escapeHtml(item.nama)}</option>`).join('');

    tr.innerHTML = `
        <td class="px-4 py-3 align-top">
            <select name="source_types[]" class="source-type block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" onchange="handleSourceChange(${index})">
                <option value="STOCK">Dari Stok</option>
                <option value="MANUAL">Manual / Custom</option>
            </select>
        </td>
        <td class="px-4 py-3 align-top">
            <div id="stock-fields-${index}">
                <select name="item_ids[]" required class="item-select block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" onchange="handleItemChange(${index})">
                    <option value="">Pilih Barang</option>
                    ${options}
                </select>
            </div>
            <div id="manual-fields-${index}" class="hidden space-y-2">
                <input type="text" name="manual_names[]" placeholder="Nama barang custom" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                <input type="text" name="manual_specs[]" placeholder="Spesifikasi / keterangan (opsional)" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                <input type="text" name="manual_units[]" placeholder="Satuan (pcs, unit, meter, dll.)" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
            </div>
        </td>
        <td class="px-4 py-3 align-top">
            <input type="number" name="quantities[]" min="1" value="1" required class="qty-input block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm text-center">
        </td>
        <td class="px-4 py-3 align-top">
            <div id="sn-container-${index}" class="hidden">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Untuk barang serial, pilih SN yang tersedia di gudang asal.</p>
                <select name="sn_list[]" id="sn-input-${index}" multiple size="4" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm"></select>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Tahan Ctrl untuk memilih lebih dari satu SN.</p>
            </div>
            <div id="sn-disabled-${index}" class="text-sm text-gray-500 dark:text-gray-400 italic py-2">Barang non-serial.</div>
        </td>
        <td class="px-4 py-3 text-center align-top">
            <button type="button" onclick="this.closest('tr').remove()" class="text-red-500 hover:text-red-700 mt-1"><i class="fas fa-trash"></i></button>
        </td>`;

    tbody.appendChild(tr);
    handleSourceChange(index);
}

function handleSourceChange(index) {
    const tr = document.querySelector(`tr[data-index="${index}"]`);
    if (!tr) return;
    const source = tr.querySelector('.source-type').value;
    const stockFields = document.getElementById(`stock-fields-${index}`);
    const manualFields = document.getElementById(`manual-fields-${index}`);
    const itemSelect = tr.querySelector('.item-select');
    const snContainer = document.getElementById(`sn-container-${index}`);
    const snDisabled = document.getElementById(`sn-disabled-${index}`);

    if (source === 'MANUAL') {
        stockFields.classList.add('hidden');
        manualFields.classList.remove('hidden');
        itemSelect.removeAttribute('required');
        snContainer.classList.add('hidden');
        snDisabled.textContent = 'Barang manual tidak terkait stok / serial.';
    } else {
        stockFields.classList.remove('hidden');
        manualFields.classList.add('hidden');
        itemSelect.setAttribute('required', 'required');
        snDisabled.textContent = 'Barang non-serial.';
        handleItemChange(index);
    }
}

function handleItemChange(index) {
    const tr = document.querySelector(`tr[data-index="${index}"]`);
    if (!tr) return;
    const select = tr.querySelector('.item-select');
    const option = select.options[select.selectedIndex];
    const isSerialized = option && option.dataset.serialized === '1';
    const snContainer = document.getElementById(`sn-container-${index}`);
    const snDisabled = document.getElementById(`sn-disabled-${index}`);
    const snSelect = document.getElementById(`sn-input-${index}`);

    snSelect.innerHTML = '';
    if (!isSerialized || !option.value) {
        snContainer.classList.add('hidden');
        snDisabled.classList.remove('hidden');
        return;
    }

    snContainer.classList.remove('hidden');
    snDisabled.classList.add('hidden');
    loadSerials(index);
}

async function loadSerials(index) {
    const tr = document.querySelector(`tr[data-index="${index}"]`);
    const warehouse = document.querySelector('[name="warehouse_id"]').value;
    const itemId = tr?.querySelector('.item-select')?.value;
    const snSelect = document.getElementById(`sn-input-${index}`);
    if (!warehouse || !itemId || !snSelect) return;

    try {
        const response = await fetch(`<?= url('/api/stock') ?>?warehouse_id=${encodeURIComponent(warehouse)}&item_id=${encodeURIComponent(itemId)}`);
        const data = await response.json();
        const serials = Array.isArray(data.serials) ? data.serials : [];
        snSelect.innerHTML = serials.map(sn => `<option value="${escapeHtml(sn.serial_number || sn)}">${escapeHtml(sn.serial_number || sn)}</option>`).join('');
    } catch (e) {
        snSelect.innerHTML = '';
    }
}

document.querySelector('[name="warehouse_id"]').addEventListener('change', () => {
    document.querySelectorAll('tr[data-index]').forEach(tr => handleItemChange(Number(tr.dataset.index)));
});

addItemRow();
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
