<?php
$pageTitle = 'Penyesuaian Stok Baru';
ob_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 flex items-center">
        <a href="<?= url('/adjustments') ?>" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 mr-4">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Penyesuaian Stok Baru (Opname)</h1>
        </div>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            <span class="block sm:inline"><?= htmlspecialchars($_SESSION['error']) ?></span>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <form action="<?= url('/adjustments/store') ?>" method="POST" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 border-b dark:border-gray-700 pb-2">Detail Penyesuaian</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Gudang</label>
                    <select name="warehouse_id" id="warehouse_id" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500" onchange="fetchStocks()">
                        <option value="">Pilih Gudang</option>
                        <?php foreach ($warehouses as $wh): ?>
                            <option value="<?= $wh['id'] ?>"><?= htmlspecialchars($wh['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Alasan / Catatan Penyesuaian</label>
                    <select name="reason_type" class="mt-1 mb-2 block w-full md:w-1/2 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                        <option value="Selisih Opname">Selisih Opname</option>
                        <option value="Barang Hilang">Barang Hilang</option>
                        <option value="Barang Rusak">Barang Rusak</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                    <textarea name="notes" rows="2" placeholder="Catatan tambahan (Opsional)" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <div class="flex justify-between items-center mb-4 border-b dark:border-gray-700 pb-2">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Daftar Barang Penyesuaian</h2>
                <button type="button" onclick="addItemRow()" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-sm font-medium">
                    <i class="fas fa-plus"></i> Tambah Barang
                </button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" id="itemsTable">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase w-1/3">Barang</th>
                            <th class="px-4 py-2 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Stok Sistem</th>
                            <th class="px-4 py-2 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase w-32">Stok Fisik / Aktual</th>
                            <th class="px-4 py-2 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Selisih</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase w-1/4">Input SN (Jika Selisih)</th>
                            <th class="px-4 py-2 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase w-16">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody" class="divide-y divide-gray-200 dark:divide-gray-700">
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-end gap-4">
            <a href="<?= url('/adjustments') ?>" class="bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 py-2 px-6 rounded-md shadow-sm text-sm font-medium transition-colors">Batal</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-6 rounded-md shadow-sm text-sm font-medium transition-colors">Simpan Penyesuaian</button>
        </div>
    </form>
</div>

<script>
    const itemsData = <?php echo json_encode($items); ?>;
    let rowCount = 0;
    
    function fetchStocks() {
        // Mock fetch or reset
        document.querySelectorAll('.item-select').forEach((sel) => {
            handleItemChange(sel, sel.closest('tr').dataset.index);
        });
    }

    function addItemRow() {
        const tbody = document.getElementById('itemsBody');
        const rowIndex = rowCount++;
        
        const tr = document.createElement('tr');
        tr.dataset.index = rowIndex;
        tr.className = "item-row";
        
        let selectHtml = `<select name="item_ids[]" required class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm item-select" onchange="handleItemChange(this, ${rowIndex})">
            <option value="">Pilih Barang</option>`;
        itemsData.forEach(item => {
            selectHtml += `<option value="${item.id}" data-serialized="${item.is_serialized}">${item.nama} (${item.kode_barang})</option>`;
        });
        selectHtml += `</select>`;
        
        tr.innerHTML = `
            <td class="px-4 py-3 align-top">${selectHtml}</td>
            <td class="px-4 py-3 align-top text-center">
                <span id="sys-qty-${rowIndex}" class="font-semibold text-gray-700 dark:text-gray-300">0</span>
                <input type="hidden" name="sys_quantities[]" id="sys-qty-input-${rowIndex}" value="0">
            </td>
            <td class="px-4 py-3 align-top">
                <input type="number" name="actual_quantities[]" id="actual-qty-${rowIndex}" min="0" value="0" required class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm text-center" onchange="calcDiff(${rowIndex})" onkeyup="calcDiff(${rowIndex})">
            </td>
            <td class="px-4 py-3 align-top text-center">
                <span id="diff-${rowIndex}" class="font-bold text-gray-500 dark:text-gray-400">0</span>
            </td>
            <td class="px-4 py-3 align-top">
                <div id="sn-container-${rowIndex}" class="hidden">
                    <p class="text-[10px] text-gray-500 dark:text-gray-400 mb-1 leading-tight" id="sn-instruction-${rowIndex}">Masukkan SN.</p>
                    <textarea name="sn_list[]" id="sn-input-${rowIndex}" rows="3" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" placeholder="SN001\nSN002..." onkeyup="validateSnCount(${rowIndex})" onchange="validateSnCount(${rowIndex})"></textarea>
                    <div id="sn-counter-${rowIndex}" class="text-[10px] font-bold text-red-500 mt-1">SN Diisi: 0 / 0</div>
                </div>
                <div id="sn-disabled-${rowIndex}" class="text-xs text-gray-500 dark:text-gray-400 italic py-1">
                    Bukan barang serial atau tidak ada selisih.
                </div>
            </td>
            <td class="px-4 py-3 text-center align-top">
                <button type="button" onclick="this.closest('tr').remove()" class="text-red-500 hover:text-red-700 mt-1"><i class="fas fa-trash"></i></button>
            </td>
        `;
        
        tbody.appendChild(tr);
    }

    async function handleItemChange(selectElem, rowIndex) {
        const itemId = selectElem.value;
        const whId = document.getElementById('warehouse_id').value;
        
        const sysQtySpan = document.getElementById(`sys-qty-${rowIndex}`);
        const sysQtyInput = document.getElementById(`sys-qty-input-${rowIndex}`);
        const actualInput = document.getElementById(`actual-qty-${rowIndex}`);
        
        if (!itemId || !whId) {
            sysQtySpan.innerText = '0';
            sysQtyInput.value = '0';
            actualInput.value = '0';
            calcDiff(rowIndex);
            return;
        }

        try {
            const res = await fetch(`<?= url('/api/stock') ?>?item_id=${itemId}&warehouse_id=${whId}`);
            if(res.ok) {
                const data = await res.json();
                const qty = data.quantity || 0;
                sysQtySpan.innerText = qty;
                sysQtyInput.value = qty;
                actualInput.value = qty; // default actual to system
                calcDiff(rowIndex);
            }
        } catch(e) {
            console.error(e);
        }
    }

    function calcDiff(rowIndex) {
        const tr = document.querySelector(`tr[data-index="${rowIndex}"]`);
        if (!tr) return;
        
        const sysQty = parseInt(document.getElementById(`sys-qty-input-${rowIndex}`).value) || 0;
        const actQty = parseInt(document.getElementById(`actual-qty-${rowIndex}`).value) || 0;
        const diffSpan = document.getElementById(`diff-${rowIndex}`);
        const diff = actQty - sysQty;
        
        diffSpan.innerText = (diff > 0 ? '+' : '') + diff;
        if (diff > 0) diffSpan.className = "font-bold text-green-500";
        else if (diff < 0) diffSpan.className = "font-bold text-red-500";
        else diffSpan.className = "font-bold text-gray-500";
        
        const selectElem = tr.querySelector('.item-select');
        const option = selectElem.options[selectElem.selectedIndex];
        const isSerialized = option ? (option.getAttribute('data-serialized') == '1') : false;
        
        const snContainer = document.getElementById(`sn-container-${rowIndex}`);
        const snDisabled = document.getElementById(`sn-disabled-${rowIndex}`);
        const snInput = document.getElementById(`sn-input-${rowIndex}`);
        const snInstr = document.getElementById(`sn-instruction-${rowIndex}`);
        
        if (isSerialized && diff !== 0) {
            snContainer.classList.remove('hidden');
            snDisabled.classList.add('hidden');
            snInput.setAttribute('required', 'required');
            
            if (diff > 0) {
                snInstr.innerText = `Barang bertambah (+${diff}). Masukkan SN BARU yang ditemukan.`;
                snInstr.className = "text-[10px] text-green-600 mb-1 leading-tight";
            } else {
                snInstr.innerText = `Barang berkurang (${diff}). Masukkan SN yang HILANG/RUSAK.`;
                snInstr.className = "text-[10px] text-red-600 mb-1 leading-tight";
            }
            
            validateSnCount(rowIndex);
        } else {
            snContainer.classList.add('hidden');
            snDisabled.classList.remove('hidden');
            snInput.removeAttribute('required');
            snInput.value = '';
        }
    }

    function validateSnCount(rowIndex) {
        const sysQty = parseInt(document.getElementById(`sys-qty-input-${rowIndex}`).value) || 0;
        const actQty = parseInt(document.getElementById(`actual-qty-${rowIndex}`).value) || 0;
        const diff = Math.abs(actQty - sysQty);
        
        const snInput = document.getElementById(`sn-input-${rowIndex}`);
        const counter = document.getElementById(`sn-counter-${rowIndex}`);
        
        const rawText = snInput.value;
        const snArray = rawText.split(/[\n,]+/).map(s => s.trim()).filter(s => s.length > 0);
        const actualCount = snArray.length;
        
        counter.innerText = `SN Diisi: ${actualCount} / ${diff}`;
        
        if (actualCount === diff) {
            counter.className = "text-[10px] font-bold text-green-500 mt-1";
        } else {
            counter.className = "text-[10px] font-bold text-red-500 mt-1";
        }
    }

    addItemRow();
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
