<?php
$pageTitle = 'Instalasi Baru';
ob_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
use App\Helpers\AuthHelper;
?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 flex items-center">
        <a href="<?= url('/installations') ?>" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 mr-4">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Instalasi Baru</h1>
        </div>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            <span class="block sm:inline"><?= htmlspecialchars($_SESSION['error']) ?></span>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <form action="<?= url('/installations/store') ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 border-b dark:border-gray-700 pb-2">Detail Instalasi</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Pelanggan</label>
                    <input type="text" name="customer_name" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Lokasi / Alamat</label>
                    <input type="text" name="location" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>


                
                <?php if (AuthHelper::hasRole(['SUPERADMIN', 'ADMIN'])): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Teknisi</label>
                    <select name="technician_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Saya Sendiri (<?php echo AuthHelper::user()['name']; ?>)</option>
                        <?php foreach ($users as $u): ?>
                            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php else: ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Referensi Pengeluaran (Opsional)</label>
                    <input type="text" name="reference_code" placeholder="Cth: OUT-..., TRF-..." class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <?php endif; ?>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Instalasi</label>
                    <input type="date" name="installation_date" value="<?= date('Y-m-d') ?>" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <?php if (AuthHelper::hasRole(['SUPERADMIN', 'ADMIN'])): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Referensi Pengeluaran (Opsional)</label>
                    <input type="text" name="reference_code" placeholder="Cth: OUT-..., TRF-..." class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <?php endif; ?>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Foto Bukti Instalasi</label>
                    <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-md cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Format: JPG, PNG, WEBP (Opsional)</p>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Catatan Tambahan</label>
                    <textarea name="notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <div class="flex justify-between items-center mb-4 border-b dark:border-gray-700 pb-2">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Daftar Barang yang Dipasang</h2>
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
                    <tbody id="itemsBody" class="divide-y divide-gray-200 dark:divide-gray-700">
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-end gap-4">
            <a href="<?= url('/installations') ?>" class="bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 py-2 px-6 rounded-md shadow-sm text-sm font-medium transition-colors">Batal</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-6 rounded-md shadow-sm text-sm font-medium transition-colors">Simpan Instalasi</button>
        </div>
    </form>
</div>

<script>
    const itemsData = <?php echo json_encode($items); ?>;
    let rowCount = 0;
    
    function addItemRow() {
        const tbody = document.getElementById('itemsBody');
        const rowIndex = rowCount++;
        
        const tr = document.createElement('tr');
        tr.dataset.index = rowIndex;
        
        let selectHtml = `<select name="item_ids[]" required class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm item-select" onchange="handleItemChange(this, ${rowIndex})">
            <option value="">Pilih Barang</option>`;
        itemsData.forEach(item => {
            selectHtml += `<option value="${item.id}" data-serialized="${item.is_serialized}">${item.nama} (${item.kode_barang})</option>`;
        });
        selectHtml += `</select>`;
        
        tr.innerHTML = `
            <td class="px-4 py-3 align-top">${selectHtml}</td>
            <td class="px-4 py-3 align-top">
                <input type="number" name="quantities[]" min="1" value="1" required class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm text-center qty-input" onchange="validateQty(this, ${rowIndex})" onkeyup="validateQty(this, ${rowIndex})">
            </td>
            <td class="px-4 py-3 align-top">
                <div id="sn-container-${rowIndex}" class="hidden">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Masukkan SN yang dipasang.</p>
                    <textarea name="sn_list[]" id="sn-input-${rowIndex}" rows="3" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" placeholder="SN001\nSN002..." onkeyup="validateSnCount(${rowIndex})" onchange="validateSnCount(${rowIndex})"></textarea>
                    <div id="sn-counter-${rowIndex}" class="text-xs font-bold text-red-500 mt-1">SN Diisi: 0 / 1</div>
                </div>
                <div id="sn-disabled-${rowIndex}" class="text-sm text-gray-500 dark:text-gray-400 italic py-2">
                    Bukan barang serial.
                </div>
            </td>
            <td class="px-4 py-3 text-center align-top">
                <button type="button" onclick="this.closest('tr').remove()" class="text-red-500 hover:text-red-700 mt-1"><i class="fas fa-trash"></i></button>
            </td>
        `;
        
        tbody.appendChild(tr);
    }

    function handleItemChange(selectElem, rowIndex) {
        const option = selectElem.options[selectElem.selectedIndex];
        const isSerialized = option.getAttribute('data-serialized') == '1';
        
        const snContainer = document.getElementById(`sn-container-${rowIndex}`);
        const snDisabled = document.getElementById(`sn-disabled-${rowIndex}`);
        const snInput = document.getElementById(`sn-input-${rowIndex}`);
        
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

    function validateQty(qtyElem, rowIndex) {
        validateSnCount(rowIndex);
    }

    function validateSnCount(rowIndex) {
        const tr = document.querySelector(`tr[data-index="${rowIndex}"]`);
        if (!tr) return;
        
        const qtyInput = tr.querySelector('.qty-input');
        const snInput = document.getElementById(`sn-input-${rowIndex}`);
        const counter = document.getElementById(`sn-counter-${rowIndex}`);
        
        const expectedQty = parseInt(qtyInput.value) || 0;
        
        const rawText = snInput.value;
        const snArray = rawText.split(/[\n,]+/).map(s => s.trim()).filter(s => s.length > 0);
        const actualQty = snArray.length;
        
        counter.innerText = `SN Diisi: ${actualQty} / ${expectedQty}`;
        
        if (actualQty === expectedQty) {
            counter.className = "text-xs font-bold text-green-500 mt-1";
        } else if (actualQty > expectedQty) {
            counter.className = "text-xs font-bold text-red-500 mt-1";
            counter.innerText += " (Kelebihan!)";
        } else {
            counter.className = "text-xs font-bold text-orange-500 mt-1";
            counter.innerText += " (Kurang!)";
        }
    }

    // Add first row on load
    addItemRow();
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
