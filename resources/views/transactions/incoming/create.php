<?php
$title = 'Barang Masuk Baru';
$pageTitle = 'Barang Masuk Baru';
ob_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<div class="mb-6">
    <a href="<?php echo url('/incoming'); ?>" class="text-blue-600 dark:text-blue-400 hover:underline"><i class="fas fa-arrow-left mr-1"></i> Kembali ke Barang Masuk</a>
</div>

<div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 border border-gray-200 dark:border-gray-700">
    <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-6">Tambah Barang Masuk Baru</h2>
    
    <form action="<?php echo url('/incoming/store'); ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Gudang Tujuan</label>
                <select name="warehouse_id" required class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-full py-2 px-3 text-gray-700 dark:text-white dark:bg-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Pilih Gudang</option>
                    <?php foreach ($warehouses as $wh): ?>
                        <option value="<?php echo $wh['id']; ?>"><?php echo htmlspecialchars($wh['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Info Pemasok (Opsional)</label>
                <input type="text" name="supplier_info" class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-full py-2 px-3 text-gray-700 dark:text-white dark:bg-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div class="mb-6">
            <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Catatan</label>
            <textarea name="notes" rows="2" class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-full py-2 px-3 text-gray-700 dark:text-white dark:bg-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        </div>

        <div class="mb-6 border-t border-gray-200 dark:border-gray-700 pt-4">
            <div class="flex justify-between items-center mb-2">
                <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold">Daftar Barang Masuk</label>
                <button type="button" onclick="addItemRow()" class="bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200 text-gray-800 text-sm font-semibold py-1 px-3 rounded transition-colors">
                    <i class="fas fa-plus"></i> Tambah Baris
                </button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border border-gray-200 dark:border-gray-700" id="itemsTable">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Barang</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase w-32">Kuantitas</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Serial Number (Jika Berlaku)</th>
                            <th class="px-4 py-2 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase w-16">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700" id="itemsBody">
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="flex justify-end mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow transition-colors">Proses Barang Masuk</button>
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
        
        let selectHtml = `<select name="item_ids[]" required class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-full py-2 px-3 text-gray-700 dark:text-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 item-select" onchange="handleItemChange(this, ${rowIndex})">
            <option value="">Pilih Barang</option>`;
        itemsData.forEach(item => {
            selectHtml += `<option value="${item.id}" data-serialized="${item.is_serialized}">${item.nama} (${item.kode_barang})</option>`;
        });
        selectHtml += `</select>`;
        
        tr.innerHTML = `
            <td class="px-4 py-3 align-top">${selectHtml}</td>
            <td class="px-4 py-3 align-top">
                <input type="number" name="quantities[]" min="1" value="1" required class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-full py-2 px-3 text-gray-700 dark:text-white dark:bg-gray-700 text-center focus:outline-none focus:ring-2 focus:ring-blue-500 qty-input" onchange="validateQty(this, ${rowIndex})" onkeyup="validateQty(this, ${rowIndex})">
            </td>
            <td class="px-4 py-3 align-top">
                <div id="sn-container-${rowIndex}" class="hidden">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Masukkan Serial Number (pisahkan dengan enter atau koma). Bisa scan barcode atau copy-paste dari CSV/Excel.</p>
                    <textarea name="sn_list[]" id="sn-input-${rowIndex}" rows="3" class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-full py-2 px-3 text-gray-700 dark:text-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="SN001\nSN002\nSN003..." onkeyup="validateSnCount(${rowIndex})" onchange="validateSnCount(${rowIndex})"></textarea>
                    <div id="sn-counter-${rowIndex}" class="text-xs font-bold text-red-500 mt-1">SN Terisi: 0 / 1</div>
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
        
        // Count valid SNs
        const rawText = snInput.value;
        const snArray = rawText.split(/[\n,]+/).map(s => s.trim()).filter(s => s.length > 0);
        const actualQty = snArray.length;
        
        counter.innerText = `SN Terisi: ${actualQty} / ${expectedQty}`;
        
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
