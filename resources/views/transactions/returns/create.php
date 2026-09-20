<?php
$pageTitle = 'Pengembalian Baru';
$title = 'Buat Pengembalian Barang';
$selectedLoanId = (int)($_GET['loan_id'] ?? 0);

ob_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 flex items-center">
        <a href="<?= url('/loans') ?>" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 mr-4">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Ajukan Pengembalian</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Pilih barang yang benar-benar Anda kembalikan. Admin akan memeriksa kondisi dan memproses pengembaliannya.</p>
        </div>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            <span class="block sm:inline"><?= htmlspecialchars($_SESSION['error']) ?></span>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <form action="<?= url('/returns/store') ?>" method="POST" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 border-b dark:border-gray-700 pb-2">1. Peminjaman yang Dikembalikan</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Barang yang tampil setelah peminjaman dipilih hanyalah barang yang masih berada dalam tanggung jawab Anda.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pilih Peminjaman <span class="text-red-500">*</span></label>
                    <select name="loan_id" id="loan_id" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" onchange="handleLoanChange(this.value)">
                        <option value="">Pilih peminjaman...</option>
                        <?php foreach ($loans as $loan): ?>
                            <option value="<?= $loan['id'] ?>" <?= ((int)$loan['id'] === $selectedLoanId) ? 'selected' : '' ?> >
                                Peminjaman <?= htmlspecialchars(date('d M Y', strtotime($loan['created_at'] ?? 'now'))) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($selectedLoanId): ?>
                        <div class="mt-2 text-xs text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800 rounded-md px-3 py-2">
                            Pengembalian dibuka langsung dari daftar barang yang sedang Anda pinjam.
                        </div>
                    <?php endif; ?>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Gudang Pengembalian <span class="text-red-500">*</span></label>
                    <select name="warehouse_id" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Pilih gudang...</option>
                        <?php foreach ($warehouses as $wh): ?>
                            <option value="<?= $wh['id'] ?>"><?= htmlspecialchars($wh['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Pilih gudang tempat barang diserahkan kepada Admin.</p>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Catatan Tambahan</label>
                    <textarea name="notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Contoh: Barang sudah selesai dipakai untuk pemasangan..."></textarea>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2 mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">2. Barang yang Dikembalikan</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Centang hanya barang yang benar-benar Anda serahkan kembali.</p>
                </div>
                <span id="returnSummary" class="text-xs font-semibold px-3 py-1 rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">0 barang dipilih</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" id="itemsTable">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase w-14">Pilih</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Barang</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase w-32">Jumlah</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Nomor Seri</th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody" class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr><td colspan="4" class="text-center py-8 text-gray-500 dark:text-gray-400">Pilih peminjaman terlebih dahulu.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row justify-end gap-3">
            <a href="<?= url('/loans') ?>" class="bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 py-2 px-6 rounded-md shadow-sm text-center text-sm font-medium transition-colors">Batal</a>
            <button type="submit" id="submitReturnBtn" disabled class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white py-2 px-6 rounded-md shadow-sm text-sm font-medium transition-colors">Kirim Pengajuan Pengembalian</button>
        </div>
    </form>
</div>

<script>
const selectedLoanFromUrl = <?= (int)$selectedLoanId ?>;
let rowCount = 0;

function updateSummary() {
    const selected = document.querySelectorAll('.return-check:checked').length;
    document.getElementById('returnSummary').innerText = `${selected} barang dipilih`;
}

function setSubmitEnabled(enabled) {
    document.getElementById('submitReturnBtn').disabled = !enabled;
}

function handleLoanChange(loanId) {
    const tbody = document.getElementById('itemsBody');
    tbody.innerHTML = '';
    rowCount = 0;
    setSubmitEnabled(false);
    updateSummary();

    if (!loanId) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center py-8 text-gray-500 dark:text-gray-400">Pilih peminjaman terlebih dahulu.</td></tr>';
        return;
    }

    tbody.innerHTML = '<tr><td colspan="4" class="text-center py-8 text-gray-500 dark:text-gray-400">Memuat barang yang masih dipinjam...</td></tr>';

    fetch('<?= url('/loans/api/items?id=') ?>' + encodeURIComponent(loanId))
        .then(response => {
            if (!response.ok) throw new Error('Gagal mengambil data');
            return response.json();
        })
        .then(data => {
            tbody.innerHTML = '';
            if (!Array.isArray(data) || data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center py-8 text-gray-500 dark:text-gray-400">Tidak ada barang yang masih perlu dikembalikan.</td></tr>';
                updateSummary();
                return;
            }
            data.forEach(addSubItemRow);
            updateSummary();
            validateAllRows();
        })
        .catch(() => {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center py-8 text-red-500">Gagal mengambil data barang yang dipinjam.</td></tr>';
        });
}

function addSubItemRow(item) {
    const tbody = document.getElementById('itemsBody');
    const rowIndex = rowCount++;
    const tr = document.createElement('tr');
    tr.dataset.index = rowIndex;

    const sourceType = String(item.source_type || 'STOCK').toUpperCase() === 'MANUAL' ? 'MANUAL' : 'STOCK';
    const isSerialized = Number(item.is_serialized || 0) === 1;
    const maxQty = Math.max(0, Number(item.quantity || 0) - Number(item.returned_quantity || 0));
    const serials = Array.isArray(item.serial_numbers) ? item.serial_numbers : [];
    if (maxQty < 1) return;

    const serialMarkup = isSerialized ? `
        <div>
            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-2">Pilih Nomor Seri yang Dikembalikan</label>
            <div class="space-y-2 max-h-40 overflow-y-auto rounded-md border border-gray-200 dark:border-gray-700 p-2 bg-gray-50 dark:bg-gray-900/40">
                ${serials.length ? serials.map(sn => `
                    <label class="flex items-center gap-2 rounded-md px-2 py-1.5 hover:bg-white dark:bg-gray-800 dark:hover:bg-gray-800 cursor-pointer">
                        <input type="checkbox" class="sn-option-${rowIndex} h-4 w-4 text-blue-600 border-gray-300 dark:border-gray-700 rounded" value="${escapeHtml(sn)}" onchange="syncReturnRow(${rowIndex})">
                        <span class="text-sm font-medium text-gray-800 dark:text-gray-200">${escapeHtml(sn)}</span>
                    </label>
                `).join('') : '<div class="text-sm text-red-500">Nomor seri barang ini tidak ditemukan.</div>'}
            </div>
            <div id="sn-counter-${rowIndex}" class="text-xs font-semibold text-orange-500 mt-1">0 / ${maxQty} nomor seri</div>
        </div>
    ` : '<span class="text-sm text-gray-500 dark:text-gray-400 italic">Barang tanpa nomor seri</span>';

    tr.innerHTML = `
        <td class="px-4 py-4 align-top text-center">
            <input type="checkbox" class="return-check mt-1 h-4 w-4 text-blue-600 border-gray-300 dark:border-gray-700 rounded" onchange="toggleReturnRow(${rowIndex})">
            <input type="hidden" name="item_ids[]" value="${Number(item.loan_item_id || 0)}" disabled id="item-id-${rowIndex}">
            <input type="hidden" name="item_types[]" value="${sourceType}" disabled id="item-type-${rowIndex}">
            <input type="hidden" name="quantities[]" value="${maxQty}" disabled id="qty-hidden-${rowIndex}">
            <input type="hidden" name="sn_list[]" value="" disabled id="sn-hidden-${rowIndex}">
        </td>
        <td class="px-4 py-4 align-top">
            <div class="font-semibold text-gray-900 dark:text-gray-100">${escapeHtml(item.nama || '-')}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Dipinjam: ${Number(item.quantity || 0)} · Sudah kembali: ${Number(item.returned_quantity || 0)} · Sisa: ${maxQty}</div>
            ${item.master_item_id ? `<div class="text-xs text-blue-600 dark:text-blue-300 mt-1">Terhubung ke Master Barang</div>` : ''}
        </td>
        <td class="px-4 py-4 align-top">
            <input type="number" min="1" max="${maxQty}" value="${maxQty}" id="qty-${rowIndex}" class="w-24 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-center" onchange="syncReturnRow(${rowIndex})" oninput="syncReturnRow(${rowIndex})">
        </td>
        <td class="px-4 py-4 align-top">
            ${serialMarkup}
        </td>
    `;
    tbody.appendChild(tr);
}

function toggleReturnRow(rowIndex) {
    const tr = document.querySelector(`tr[data-index="${rowIndex}"]`);
    if (!tr) return;
    const checked = tr.querySelector('.return-check').checked;
    ['item-id-', 'item-type-', 'qty-hidden-', 'sn-hidden-'].forEach(prefix => {
        const el = document.getElementById(prefix + rowIndex);
        if (el) el.disabled = !checked;
    });
    syncReturnRow(rowIndex);
    updateSummary();
}

function syncReturnRow(rowIndex) {
    const tr = document.querySelector(`tr[data-index="${rowIndex}"]`);
    if (!tr) return;
    const checked = tr.querySelector('.return-check').checked;
    const qty = Math.max(0, Number(document.getElementById('qty-' + rowIndex)?.value || 0));
    const qtyHidden = document.getElementById('qty-hidden-' + rowIndex);
    if (qtyHidden) qtyHidden.value = qty;

    const snHidden = document.getElementById('sn-hidden-' + rowIndex);
    const counter = document.getElementById('sn-counter-' + rowIndex);
    const snOptions = [...document.querySelectorAll('.sn-option-' + rowIndex + ':checked')].map(el => el.value);
    if (snHidden) snHidden.value = snOptions.join('\n');
    if (counter) {
        counter.textContent = `${snOptions.length} / ${qty} nomor seri`;
        counter.className = `text-xs font-semibold mt-1 ${snOptions.length === qty ? 'text-green-600' : 'text-orange-500'}`;
    }

    if (checked) validateAllRows();
}

function validateAllRows() {
    const selected = [...document.querySelectorAll('.return-check:checked')];
    let valid = selected.length > 0;
    selected.forEach(cb => {
        const tr = cb.closest('tr');
        const index = tr.dataset.index;
        const qty = Number(document.getElementById('qty-' + index)?.value || 0);
        const snOptions = tr.querySelectorAll('.sn-option-' + index + ':checked');
        if (qty < 1) valid = false;
        if (tr.querySelector('.sn-option-' + index) && snOptions.length !== qty) valid = false;
    });
    setSubmitEnabled(valid);
}

function escapeHtml(v) {
    return String(v ?? '').replace(/[&<>'\"]/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','\"':'&quot;',"'":'&#039;'}[s]));
}

if (selectedLoanFromUrl) handleLoanChange(String(selectedLoanFromUrl));
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>