<?php
$title = 'Pemenuhan Barang';
$pageTitle = 'Pemenuhan Barang';

ob_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<div class="mb-6">
    <a href="<?php echo url('/outgoing'); ?>" class="text-blue-600 dark:text-blue-400 hover:underline"><i class="fas fa-arrow-left mr-1"></i> Kembali ke Barang Keluar</a>
</div>

<div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 border border-gray-200 dark:border-gray-700">
    <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-6">Tambah Barang Keluar Baru</h2>
    
    <form action="<?php echo url('/outgoing/store'); ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Gudang Asal <span id="whHint" class="font-normal text-xs text-gray-400"></span></label>
                <?php if (empty($warehouses)): ?>
                    <div class="text-sm text-red-500 dark:text-red-400 py-2">Tidak ada gudang aktif.</div>
                <?php else: ?>
                <select name="warehouse_id" id="warehouse_id" required class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-full py-2 px-3 text-gray-700 dark:text-white dark:bg-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Pilih Gudang</option>
                    <?php foreach ($warehouses as $wh): ?>
                        <option value="<?php echo $wh['id']; ?>" <?php echo ((int)($preselectWarehouseId ?? 0) === (int)$wh['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($wh['code'] . ' — ' . $wh['name'] . (!empty($wh['address']) ? ' — ' . $wh['address'] : '')); ?></option>
                    <?php endforeach; ?>
                </select>
                <?php endif; ?>
            </div>
            
            <div>
                <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Referensi Pengajuan (Opsional)</label>
                <?php if (empty($submissions)): ?>
                    <div class="text-sm text-gray-500 dark:text-gray-400 py-2">Tidak ada pengajuan yang tersedia.</div>
                <?php else: ?>
                <select name="submission_id" id="submission_id" class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-full py-2 px-3 text-gray-700 dark:text-white dark:bg-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="handleSubmissionChange(this.value)">
                    <option value="">Tanpa Pengajuan (Langsung)</option>
                    <?php foreach ($submissions as $sub): ?>
                        <?php
                            $subDate = date('d M Y', strtotime($sub['created_at']));
                            $subLabel = htmlspecialchars($sub['submission_code'] . ' — ' . $sub['submitter_name'] . ' — ' . $subDate);
                            if (!empty($sub['items_summary'])) {
                                $subLabel .= ' — ' . htmlspecialchars($sub['items_summary']);
                            }
                        ?>
                        <option value="<?php echo $sub['id']; ?>" <?php echo ((int)($preselectSubmissionId ?? 0) === (int)$sub['id']) ? 'selected' : ''; ?>><?php echo $subLabel; ?></option>
                    <?php endforeach; ?>
                </select>
                <?php endif; ?>
            </div>
        </div>

        <div class="mb-6">
            <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Catatan</label>
            <textarea name="notes" rows="2" class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-full py-2 px-3 text-gray-700 dark:text-white dark:bg-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        </div>

        <div class="mb-6 border-t border-gray-200 dark:border-gray-700 pt-4">
            <div class="flex justify-between items-center mb-2">
                <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold">Daftar Barang Keluar</label>
                <button type="button" id="btnAddRow" onclick="addItemRow()" class="bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200 text-gray-800 text-sm font-semibold py-1 px-3 rounded transition-colors">
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
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow transition-colors">Proses Barang Keluar</button>
        </div>
    </form>
</div>

<script>
    const itemsData = <?php echo json_encode($items); ?>;
    const warehouses = <?php echo json_encode($warehouses); ?>;
    const warehouseMap = {};
    warehouses.forEach(wh => { warehouseMap[wh.id] = wh.code + ' — ' + wh.name + (wh.address ? ' — ' + wh.address : ''); });
    
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
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Masukkan Serial Number yang akan dikeluarkan (pisahkan dengan enter/koma). SN harus tersedia di gudang asal.</p>
                    <textarea name="sn_list[]" id="sn-input-${rowIndex}" rows="3" class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-full py-2 px-3 text-gray-700 dark:text-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="SN001\nSN002..." onkeyup="validateSnCount(${rowIndex})" onchange="validateSnCount(${rowIndex})"></textarea>
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

    function handleSubmissionChange(submissionId) {
        const tbody = document.getElementById('itemsBody');
        const btnAddRow = document.getElementById('btnAddRow');
        tbody.innerHTML = '';
        rowCount = 0;

        if (!submissionId) {
            btnAddRow.style.display = 'block';
            addItemRow();
            return;
        }

        btnAddRow.style.display = 'none'; // Disable adding arbitrary rows
        
        fetch('<?= url('/submissions/api/items?id=') ?>' + submissionId)
            .then(response => response.json())
            .then(data => {
                if(data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-gray-500 dark:text-gray-400">Tidak ada barang yang perlu dikeluarkan.</td></tr>';
                } else {
                    data.forEach(item => {
                        addSubItemRow(item);
                    });
                    // Gudang otomatis mengikuti Gudang Pemenuhan pengajuan (jika seragam).
                    // Identity stock = master_item_id + warehouse_id — jangan biarkan admin
                    // memilih gudang lain secara diam-diam.
                    const whSelect = document.getElementById('warehouse_id');
                    const whHint = document.getElementById('whHint');
                    const whSet = new Set(data.map(i => i.warehouse_id ? String(i.warehouse_id) : null).filter(v => v !== null));
                    if (whSet.size === 1) {
                        const wid = [...whSet][0];
                        whSelect.value = wid;
                        whHint.textContent = '(otomatis: Gudang Pemenuhan pengajuan)';
                    } else if (whSet.size > 1) {
                        whSelect.value = '';
                        whHint.textContent = '(item memakai gudang berbeda — proses per gudang)';
                        if (typeof AppToast !== 'undefined' && AppToast.warning) {
                            AppToast.warning('Item pengajuan memakai Gudang Pemenuhan berbeda. Proses per gudang; item di gudang lain akan ditolak.');
                        }
                    } else {
                        whSelect.value = '';
                        whHint.textContent = '(pengajuan lama tanpa gudang — pilih gudang eksplisit)';
                    }
                }
            })
            .catch(error => {
                console.error('Error fetching submission items:', error);
                AppToast.error('Gagal mengambil data pengajuan.');
            });
    }

    function addSubItemRow(item) {
        const tbody = document.getElementById('itemsBody');
        const rowIndex = rowCount++;
        const tr = document.createElement('tr');
        tr.dataset.index = rowIndex;
        
        const isSerialized = item.is_serialized == 1;
        
        // Hide item id in a readonly input
        let selectHtml = `
            <input type="hidden" name="item_ids[]" value="${item.submission_item_id}">
            <div class="font-medium text-gray-900 dark:text-gray-100">${item.nama}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">${item.kode_barang}</div>
            <div class="text-xs text-blue-600 mt-1">Disetujui: ${item.approved_quantity} | Sisa: ${item.remaining_quantity}</div>
            ${item.warehouse_id ? `<div class="text-xs text-gray-500 dark:text-gray-400"><i class="fas fa-warehouse mr-1"></i>Gudang Pemenuhan: ${warehouseMap[item.warehouse_id] || 'Gudang #' + item.warehouse_id}</div>` : ''}
        `;
        
        tr.innerHTML = `
            <td class="px-4 py-3 align-top">${selectHtml}</td>
            <td class="px-4 py-3 align-top">
                <input type="number" name="quantities[]" min="1" max="${item.remaining_quantity}" value="${item.remaining_quantity}" required class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-full py-2 px-3 text-gray-700 dark:text-white dark:bg-gray-700 text-center focus:outline-none focus:ring-2 focus:ring-blue-500 qty-input" onchange="validateQty(this, ${rowIndex})" onkeyup="validateQty(this, ${rowIndex})">
            </td>
            <td class="px-4 py-3 align-top">
                <div id="sn-container-${rowIndex}" class="${isSerialized ? '' : 'hidden'}">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Masukkan Serial Number yang akan dikeluarkan. SN harus tersedia di gudang asal.</p>
                    <textarea name="sn_list[]" id="sn-input-${rowIndex}" rows="3" ${isSerialized ? 'required' : ''} class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-full py-2 px-3 text-gray-700 dark:text-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="SN001\\nSN002..." onkeyup="validateSnCount(${rowIndex})" onchange="validateSnCount(${rowIndex})"></textarea>
                    <div id="sn-counter-${rowIndex}" class="text-xs font-bold text-red-500 mt-1">SN Diisi: 0 / ${item.remaining_quantity}</div>
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

    // Auto-load item pengajuan bila datang dari link "Proses" di Detail Pengajuan.
    const preselectSubmissionId = <?php echo (int)($preselectSubmissionId ?? 0); ?>;
    if (preselectSubmissionId > 0) {
        handleSubmissionChange(String(preselectSubmissionId));
    } else {
        addItemRow();
    }

    // UX improvement: when user opens outgoing/create from a submission, the warehouse must be selected.
    // Provide clear AppToast feedback instead of silent native constraint failure.
    (function() {
        const form = document.querySelector('form[action="<?= url('/outgoing/store') ?>"]');
        if (!form) return;
        const warehouseSelect = form.querySelector('select[name="warehouse_id"]');
        if (!warehouseSelect) return;

        // On submit, if warehouse is empty and this came from a submission, show friendly toast and focus.
        form.addEventListener('submit', function(e) {
            if (preselectSubmissionId > 0 && (!warehouseSelect.value || warehouseSelect.value === '')) {
                e.preventDefault();
                e.stopPropagation();
                if (typeof AppToast !== 'undefined' && AppToast.error) {
                    AppToast.error('Silakan pilih Gudang Asal terlebih dahulu.');
                } else {
                    // Fallback: highlight the control
                    warehouseSelect.classList.add('ring-2', 'ring-red-500');
                }
                warehouseSelect.focus();
                return false;
            }
            // allow normal submit (including native validation) otherwise
        }, {capture: true});

        // Also show a gentle hint when page loaded with preselected submission but no warehouse chosen.
        if (preselectSubmissionId > 0 && (!warehouseSelect.value || warehouseSelect.value === '')) {
            if (typeof AppToast !== 'undefined' && AppToast.info) {
                AppToast.info('Pengajuan dimuat. Silakan pilih Gudang Asal sebelum memproses Barang Keluar.');
            }
            // and focus the warehouse to encourage choice
            warehouseSelect.focus();
        }
    })();
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
