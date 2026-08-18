<?php
$title = 'Buat Pengajuan Barang';
$pageTitle = 'Buat Pengajuan Barang';

ob_start();

// CSRF init wajib berada DI DALAM tag PHP — jangan pernah keluar sebagai output HTML.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<div class="mb-5">
    <a href="<?php echo url('/submissions'); ?>" class="inline-flex items-center text-blue-600 dark:text-blue-400 hover:underline text-sm"><i class="fas fa-arrow-left mr-2"></i> Kembali ke Pengajuan</a>
</div>

<!-- Alur Pengajuan (helper workflow) -->
<div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-xl p-4 mb-6">
    <p class="text-sm font-bold text-blue-800 dark:text-blue-300 mb-2"><i class="fas fa-circle-info mr-2"></i>Alur Pengajuan</p>
    <ol class="text-sm text-blue-700 dark:text-blue-300 list-decimal list-inside space-y-1">
        <li>Pengajuan dikirim dan masuk status <strong>Menunggu Persetujuan</strong>.</li>
        <li>Admin melakukan persetujuan.</li>
        <li>Setelah disetujui, gudang memproses pemenuhan.</li>
    </ol>
    <p class="mt-2 text-xs text-blue-600 dark:text-blue-400"><i class="fas fa-info-circle mr-1"></i>Pengajuan bukan Barang Keluar dan tidak langsung mengurangi stok.</p>
</div>

<div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 sm:p-6 border border-gray-200 dark:border-gray-700">
    <h2 class="text-lg sm:text-xl font-bold text-gray-800 dark:text-white mb-5">Ajukan Kebutuhan Barang</h2>

    <form action="<?php echo url('/submissions/store'); ?>" method="POST" id="submissionForm">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

        <!-- Keperluan Pengajuan -->
        <div class="mb-6">
            <label for="header_note" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Keperluan Pengajuan <span class="text-gray-400 dark:text-gray-500 font-normal">(Opsional)</span></label>
            <textarea id="header_note" name="header_note" rows="2" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg py-2.5 px-3 text-gray-700 dark:text-white dark:bg-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Contoh: Permintaan material untuk project X..."></textarea>
        </div>

        <!-- Daftar Barang yang Diminta -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                <h3 class="text-base font-bold text-gray-800 dark:text-white">Barang yang Diminta</h3>
                <button type="button" onclick="addItemRow()" class="inline-flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold py-3 px-4 rounded-lg transition-colors shadow w-full sm:w-auto min-h-[44px]">
                    <i class="fas fa-plus"></i> Tambah Barang
                </button>
            </div>

            <div id="itemsList" class="space-y-4">
                <!-- JS populated: item cards -->
            </div>

            <!-- Ringkasan -->
            <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700 rounded-lg px-4 py-3">
                <span><i class="fas fa-box mr-1.5 text-gray-400"></i>Jenis barang: <strong id="sumTypes" class="text-gray-900 dark:text-white">0</strong></span>
                <span><i class="fas fa-cubes mr-1.5 text-gray-400"></i>Total jumlah: <strong id="sumQty" class="text-gray-900 dark:text-white">0</strong></span>
            </div>
        </div>

        <!-- Submit Area -->
        <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 mt-6 pt-5 border-t border-gray-200 dark:border-gray-700">
            <a href="<?php echo url('/submissions'); ?>" class="inline-flex items-center justify-center gap-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-semibold py-3 px-6 rounded-lg transition-colors w-full sm:w-auto min-h-[44px]">
                Batal
            </a>
            <button type="submit" class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg shadow transition-colors w-full sm:w-auto min-h-[44px]">
                <i class="fas fa-paper-plane"></i> Kirim Pengajuan
            </button>
        </div>
    </form>
</div>

<script>
    const masterItems = <?php echo json_encode($items); ?>;
    const warehouses = <?php echo json_encode($warehouses); ?>;
    let warehouseOptions = '<option value="">Pilih Gudang...</option>';
    warehouses.forEach(wh => {
        warehouseOptions += '<option value="' + wh.id + '">Gudang #' + wh.id + ' — ' + wh.name + '</option>';
    });

    let rowCount = 0;

    function updateSummary() {
        const cards = document.querySelectorAll('#itemsList .item-card');
        let total = 0;
        cards.forEach(card => {
            const q = parseInt(card.querySelector('[name="quantities[]"]').value, 10);
            if (!isNaN(q) && q > 0) total += q;
        });
        document.getElementById('sumTypes').textContent = cards.length;
        document.getElementById('sumQty').textContent = total;
    }

    function setRowSource(btn, rowId) {
        const card = document.getElementById('row_' + rowId);
        const type = btn.getAttribute('data-src');
        const stockBlock = card.querySelector('.stock-block');
        const manualBlock = card.querySelector('.manual-block');
        const select = card.querySelector('select[name="source_types[]"]');
        const stockInput = card.querySelector('select[name="item_ids[]"]');
        const whInput = card.querySelector('select[name="warehouse_ids[]"]');
        const manualInput = card.querySelector('input[name="manual_names[]"]');

        select.value = type;
        card.querySelectorAll('.src-btn').forEach(b => {
            b.classList.remove('bg-blue-600', 'text-white', 'border-blue-600');
            b.classList.add('bg-white', 'dark:bg-gray-700', 'text-gray-600', 'dark:text-gray-300', 'border-gray-300', 'dark:border-gray-600');
        });
        btn.classList.remove('bg-white', 'dark:bg-gray-700', 'text-gray-600', 'dark:text-gray-300', 'border-gray-300', 'dark:border-gray-600');
        btn.classList.add('bg-blue-600', 'text-white', 'border-blue-600');

        if (type === 'STOCK') {
            stockBlock.classList.remove('hidden');
            manualBlock.classList.add('hidden');
            stockInput.required = true;
            whInput.required = true;
            manualInput.required = false;
        } else {
            stockBlock.classList.add('hidden');
            manualBlock.classList.remove('hidden');
            stockInput.required = false;
            whInput.required = false;
            manualInput.required = true;
            stockInput.value = '';
            whInput.value = '';
        }
        updateSummary();
    }

    function removeItemRow(btn) {
        const card = btn.closest('.item-card');
        if (card) card.remove();
        updateSummary();
    }

    function addItemRow() {
        const list = document.getElementById('itemsList');
        const id = rowCount;

        let stockSelect = '<select name="item_ids[]" class="stock-input w-full border border-gray-300 dark:border-gray-600 rounded-lg py-2.5 px-3 text-gray-700 dark:text-white dark:bg-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 min-h-[44px]"><option value="">Pilih Barang...</option>';
        masterItems.forEach(item => {
            stockSelect += '<option value="' + item.id + '">' + item.nama + ' (' + item.kode_barang + ')</option>';
        });
        stockSelect += '</select>';

        const card = document.createElement('div');
        card.className = 'item-card bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-xl p-4 space-y-4';
        card.id = 'row_' + id;
        card.innerHTML = `
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-2 flex-wrap">
                    <button type="button" data-src="STOCK" onclick="setRowSource(this, ${id})" class="src-btn inline-flex items-center gap-1.5 text-xs font-semibold border rounded-lg px-3 py-2 min-h-[44px] transition-colors bg-blue-600 text-white border-blue-600"><i class="fas fa-box"></i> Dari Stok</button>
                    <button type="button" data-src="MANUAL" onclick="setRowSource(this, ${id})" class="src-btn inline-flex items-center gap-1.5 text-xs font-semibold border rounded-lg px-3 py-2 min-h-[44px] transition-colors bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 border-gray-300 dark:border-gray-600"><i class="fas fa-pen"></i> Manual</button>
                    <select name="source_types[]" class="hidden" tabindex="-1" aria-hidden="true">
                        <option value="STOCK" selected>STOCK</option>
                        <option value="MANUAL">MANUAL</option>
                    </select>
                </div>
                <button type="button" onclick="removeItemRow(this)" title="Hapus baris" aria-label="Hapus baris" class="inline-flex items-center justify-center w-11 h-11 shrink-0 rounded-lg text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors"><i class="fas fa-trash"></i></button>
            </div>

            <div class="stock-block">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Pilih Barang</label>
                ${stockSelect}
                <div class="mt-3">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Gudang Pemenuhan <span class="text-red-500">*</span> <span class="font-normal text-gray-400 dark:text-gray-500">(stok dicek dari gudang ini saat persetujuan & pemenuhan)</span></label>
                    <select name="warehouse_ids[]" class="wh-input w-full border border-gray-300 dark:border-gray-600 rounded-lg py-2.5 px-3 text-gray-700 dark:text-white dark:bg-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 min-h-[44px]">
                        ${warehouseOptions}
                    </select>
                </div>
            </div>

            <div class="manual-block hidden space-y-3">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Nama Barang/Jasa <span class="text-red-500">*</span></label>
                    <input type="text" name="manual_names[]" placeholder="Nama Barang/Jasa *" class="manual-input w-full border border-gray-300 dark:border-gray-600 rounded-lg py-2.5 px-3 text-gray-700 dark:text-white dark:bg-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 min-h-[44px]">
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    <input type="text" name="manual_specs[]" placeholder="Spesifikasi/Tipe (Ops)" class="col-span-2 w-full border border-gray-300 dark:border-gray-600 rounded-lg py-2.5 px-3 text-gray-700 dark:text-white dark:bg-gray-700 text-sm focus:outline-none min-h-[44px]">
                    <input type="text" name="manual_units[]" placeholder="Satuan (Pcs/Roll)" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg py-2.5 px-3 text-gray-700 dark:text-white dark:bg-gray-700 text-sm focus:outline-none min-h-[44px]">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Jumlah</label>
                <input type="number" name="quantities[]" min="1" value="1" required onchange="updateSummary()" oninput="updateSummary()" class="w-32 border border-gray-300 dark:border-gray-600 rounded-lg py-2.5 px-3 text-gray-700 dark:text-white dark:bg-gray-700 text-center text-base focus:outline-none focus:ring-2 focus:ring-blue-500 min-h-[44px]">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Catatan Item <span class="text-gray-400 dark:text-gray-500 font-normal">(Opsional)</span></label>
                <textarea name="notes[]" rows="2" placeholder="Contoh: Untuk pemasangan pelanggan A" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg py-2 px-3 text-gray-700 dark:text-white dark:bg-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>
        `;
        list.appendChild(card);
        rowCount++;
        updateSummary();
    }

    // Baris pertama otomatis tersedia
    addItemRow();
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>