<?php
$title = 'Buat Laporan Insiden';
$pageTitle = 'Buat Laporan Insiden';
ob_start();
?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100"><?= $title ?></h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Lapor kerusakan atau kehilangan barang.</p>
    </div>
    <a href="<?= url('/incidents') ?>" class="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
        &larr; Kembali
    </a>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
    <?php if (isset($_SESSION['error'])): ?>
        <div class="mb-4 p-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <form action="<?= url('/incidents/store') ?>" method="POST" enctype="multipart/form-data" id="incidentForm">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label for="incident_type" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Tipe Insiden <span class="text-red-500">*</span></label>
                <select id="incident_type" name="incident_type" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    <option value="">Pilih Tipe</option>
                    <option value="DAMAGE">Kerusakan (DAMAGE)</option>
                    <option value="LOST">Kehilangan</option>
                </select>
            </div>
            <div>
                <label for="warehouse_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Lokasi Gudang (Opsional)</label>
                <select id="warehouse_id" name="warehouse_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    <option value="">Tidak Terikat Gudang / Di Luar</option>
                    <?php foreach ($warehouses as $w): ?>
                        <option value="<?= $w['id'] ?>"><?= htmlspecialchars($w['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="mb-6">
            <label for="notes" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Keterangan Umum</label>
            <textarea id="notes" name="notes" rows="3" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Jelaskan secara singkat kejadian insiden..."></textarea>
        </div>

        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4 border-b pb-2 dark:border-gray-700">Daftar Barang</h3>
        
        <div id="itemsContainer" class="space-y-4 mb-4">
            <!-- Item Row Template -->
            <div class="item-row bg-gray-50 dark:bg-gray-700 p-4 rounded-lg border border-gray-200 dark:border-gray-600 relative">
                <button type="button" class="remove-item absolute top-3 right-3 inline-flex items-center justify-center w-11 h-11 rounded-lg text-red-500 hover:text-red-700 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30 transition-colors hidden" aria-label="Hapus baris">
                    <i class="fas fa-trash"></i>
                </button>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Pilih Barang <span class="text-red-500">*</span></label>
                        <select name="items[0][master_item_id]" class="item-select bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-800 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required onchange="handleItemChange(this)">
                            <option value="">Pilih Barang...</option>
                            <?php foreach ($items as $item): ?>
                                <option value="<?= $item['id'] ?>" data-serial="<?= $item['is_serialized'] ?>"><?= htmlspecialchars($item['kode_barang']) ?> - <?= htmlspecialchars($item['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Quantity <span class="text-red-500">*</span></label>
                        <input type="number" name="items[0][quantity]" min="1" value="1" required class="qty-input bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                    </div>
                    <div class="serial-container hidden">
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nomor Seri (Opsional)</label>
                        <select name="items[0][serial_number_id]" class="serial-select bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                            <option value="">Pilih Serial...</option>
                        </select>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Kosongkan jika nomor seri juga hilang tak terdeteksi.</p>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Pemegang Terakhir (Opsional)</label>
                        <input type="text" name="items[0][last_holder_name]" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-800 dark:border-gray-600 dark:text-white" placeholder="Nama orang/klien">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Alasan Spesifik / Kronologi <span class="text-red-500">*</span></label>
                        <textarea name="items[0][reason]" required rows="2" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-800 dark:border-gray-600 dark:text-white"></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Bukti Foto (Wajib) <span class="text-red-500">*</span></label>
                        <input type="file" name="items[0][evidence]" required accept="image/*" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-white dark:text-gray-400 focus:outline-none dark:bg-gray-800 dark:border-gray-600 dark:placeholder-gray-400">
                    </div>
                </div>
            </div>
        </div>
        
        <button type="button" id="addItemBtn" class="mb-6 inline-flex items-center justify-center gap-2 min-h-[44px] px-4 text-sm font-semibold text-blue-600 dark:text-blue-400 border border-blue-300 dark:border-blue-700 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors">
            <i class="fas fa-plus"></i> Tambah Barang
        </button>

        <div class="flex justify-end gap-3 border-t pt-6 dark:border-gray-700">
            <button type="button" onclick="history.back()" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:ring-4 focus:outline-none focus:ring-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700">Batal</button>
            <button type="submit" id="submitBtn" class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Simpan Laporan</button>
        </div>
    </form>
</div>

<script>
    let itemIndex = 0;
    
    function updateRemoveButtons() {
        const rows = document.querySelectorAll('.item-row');
        const removeBtns = document.querySelectorAll('.remove-item');
        if (rows.length > 1) {
            removeBtns.forEach(btn => btn.classList.remove('hidden'));
        } else {
            removeBtns.forEach(btn => btn.classList.add('hidden'));
        }
    }
    
    document.getElementById('addItemBtn').addEventListener('click', () => {
        const container = document.getElementById('itemsContainer');
        const firstRow = container.querySelector('.item-row');
        const newRow = firstRow.cloneNode(true);
        itemIndex++;
        
        // Update input names
        const inputs = newRow.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            const name = input.getAttribute('name');
            if (name) {
                input.setAttribute('name', name.replace(/\[0\]/, `[${itemIndex}]`));
            }
            if(input.type !== 'file') input.value = '';
            if(input.type === 'number') input.value = '1';
        });
        
        // Hide serial container by default
        newRow.querySelector('.serial-container').classList.add('hidden');
        const serialSelect = newRow.querySelector('.serial-select');
        serialSelect.innerHTML = '<option value="">Pilih Serial...</option>';
        
        container.appendChild(newRow);
        updateRemoveButtons();
    });
    
    document.getElementById('itemsContainer').addEventListener('click', (e) => {
        if (e.target.closest('.remove-item')) {
            const row = e.target.closest('.item-row');
            row.remove();
            updateRemoveButtons();
        }
    });

    async function handleItemChange(selectElement) {
        const row = selectElement.closest('.item-row');
        const serialContainer = row.querySelector('.serial-container');
        const serialSelect = row.querySelector('.serial-select');
        const qtyInput = row.querySelector('.qty-input');
        
        const option = selectElement.options[selectElement.selectedIndex];
        const hasSerial = option.dataset.serial == '1';
        
        if (hasSerial) {
            serialContainer.classList.remove('hidden');
            qtyInput.value = 1;
            qtyInput.setAttribute('readonly', true);
            
            // Fetch available serials API
            const itemId = selectElement.value;
            try {
                // Not mandatory to fetch all serials if lost, but helpful to map it.
                // We will leave it simple here as it's optional in DB anyway for Lost items.
                serialSelect.innerHTML = '<option value="">Pilih Serial...</option>';
            } catch (error) {
                console.error(error);
            }
        } else {
            serialContainer.classList.add('hidden');
            qtyInput.removeAttribute('readonly');
        }
    }

    document.getElementById('incidentForm').addEventListener('submit', function() {
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<svg class="animate-spin h-5 w-5 mr-3 inline-block" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Menyimpan...';
    });
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
