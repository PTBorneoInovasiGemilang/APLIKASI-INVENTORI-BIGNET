<?php
$pageTitle = 'Pengembalian Baru';
$title = 'Buat Pengembalian Barang';

ob_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 flex items-center">
        <a href="<?= url('/returns') ?>" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 mr-4">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Pengembalian Baru</h1>
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
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 border-b dark:border-gray-700 pb-2">Detail Pengembalian</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Gudang Tujuan (Tempat Pengembalian)</label>
                    <select name="warehouse_id" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Pilih Gudang</option>
                        <?php foreach ($warehouses as $wh): ?>
                            <option value="<?= $wh['id'] ?>"><?= htmlspecialchars($wh['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Referensi Peminjaman (Opsional)</label>
                    <select name="loan_id" id="loan_id" required class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" onchange="handleLoanChange(this.value)">
                    <option value="">-- Pilih --</option>
                    <?php foreach ($loans as $loan): ?>
                        <option value="<?= $loan['id'] ?>"><?= htmlspecialchars($loan['transaction_code']) ?></option>
                    <?php endforeach; ?>
                </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Catatan / Alasan</label>
                    <textarea name="notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white">Daftar Barang Dikembalikan</h2>
                <!-- Button removed since it is now auto-populated by loan items -->
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
            <a href="<?= url('/returns') ?>" class="bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 py-2 px-6 rounded-md shadow-sm text-sm font-medium transition-colors">Batal</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-6 rounded-md shadow-sm text-sm font-medium transition-colors">Simpan Pengembalian</button>
        </div>
    </form>
</div>

<script>
    const itemsData = <?php echo json_encode($items); ?>;
    let rowCount = 0;
    
    function handleLoanChange(loanId) {
        const tbody = document.getElementById('itemsBody');
        tbody.innerHTML = '';
        rowCount = 0;

        if (!loanId) return;

        fetch('<?= url('/loans/api/items?id=') ?>' + loanId)
            .then(response => response.json())
            .then(data => {
                if(data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-gray-500 dark:text-gray-400">Tidak ada barang yang bisa dikembalikan pada peminjaman ini.</td></tr>';
                } else {
                    data.forEach(item => {
                        addSubItemRow(item);
                    });
                }
            })
            .catch(error => {
                console.error('Error fetching loan items:', error);
                AppToast.error('Gagal mengambil data peminjaman.');
            });
    }

    function addSubItemRow(item) {
        const tbody = document.getElementById('itemsBody');
        const rowIndex = rowCount++;
        const tr = document.createElement('tr');
        tr.dataset.index = rowIndex;
        
        const isSerialized = item.is_serialized == 1;
        const maxQty = item.quantity - item.returned_quantity;
        
        let selectHtml = `
            <input type="hidden" name="item_ids[]" value="${item.loan_item_id}">
            <div class="font-medium text-gray-900 dark:text-gray-100">${item.nama}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">${item.kode_barang}</div>
            <div class="text-xs text-blue-600 mt-1">Pinjam: ${item.quantity} | Kembali: ${item.returned_quantity} | Sisa: ${maxQty}</div>
        `;
        
        tr.innerHTML = `
            <td class="px-4 py-3 align-top">${selectHtml}</td>
            <td class="px-4 py-3 align-top">
                <input type="number" name="quantities[]" min="1" max="${maxQty}" value="${maxQty}" required class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm text-center qty-input" onchange="validateQty(this, ${rowIndex})" onkeyup="validateQty(this, ${rowIndex})">
            </td>
            <td class="px-4 py-3 align-top">
                <div id="sn-container-${rowIndex}" class="${isSerialized ? '' : 'hidden'}">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Masukkan Serial Number yang dikembalikan.</p>
                    <textarea name="sn_list[]" id="sn-input-${rowIndex}" rows="3" ${isSerialized ? 'required' : ''} class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" placeholder="SN001\\nSN002..." onkeyup="validateSnCount(${rowIndex})" onchange="validateSnCount(${rowIndex})"></textarea>
                    <div id="sn-counter-${rowIndex}" class="text-xs font-bold text-red-500 mt-1">SN Diisi: 0 / ${maxQty}</div>
                </div>
                <div id="sn-disabled-${rowIndex}" class="text-sm text-gray-500 dark:text-gray-400 italic py-2 ${isSerialized ? 'hidden' : ''}">
                    Bukan barang serial.
                </div>
            </td>
            <td class="px-4 py-3 text-center align-top">
                <button type="button" onclick="this.closest('tr').remove()" class="text-red-500 hover:text-red-700 mt-1"><i class="fas fa-trash"></i></button>
            </td>
        `;
        tbody.appendChild(tr);
        if(isSerialized) validateSnCount(rowIndex);
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

</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
