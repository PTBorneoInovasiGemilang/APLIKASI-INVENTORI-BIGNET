<?php
$pageTitle = 'Instalasi Baru';
ob_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
use App\Helpers\AuthHelper;
use App\Core\Database;

// Teknisi hanya boleh memilih barang yang memang sudah dipenuhi/dikeluarkan
// melalui pengajuan miliknya. Master barang lain tidak ditampilkan di UI.
if (AuthHelper::hasRole(['TEKNISI_NOC'])) {
    $pdo = Database::getInstance()->getConnection();
    $technicianId = (int)AuthHelper::user()['id'];

    $issuedStmt = $pdo->prepare("\n        SELECT\n            oi.master_item_id AS id,\n            m.nama,\n            m.is_serialized,\n            SUM(oi.quantity) AS issued_quantity,\n            COALESCE((\n                SELECT SUM(ii.quantity)\n                FROM installation_items ii\n                JOIN installations ins ON ins.id = ii.installation_id\n                WHERE ins.technician_id = ?\n                  AND ins.status = 'INSTALLED'\n                  AND ii.master_item_id = oi.master_item_id\n            ), 0) AS installed_quantity\n        FROM outgoing_items oi\n        JOIN outgoing o ON o.id = oi.outgoing_id\n        JOIN submissions s ON s.id = o.submission_id\n        JOIN master_items m ON m.id = oi.master_item_id\n        WHERE s.submitted_by = ?\n          AND m.is_active = 1\n        GROUP BY oi.master_item_id, m.nama, m.is_serialized\n        HAVING (issued_quantity - installed_quantity) > 0\n        ORDER BY m.nama ASC\n    ");
    $issuedStmt->execute([$technicianId, $technicianId]);
    $items = $issuedStmt->fetchAll();

    // Untuk barang serial, tampilkan jumlah SN yang sedang dipegang teknisi.
    foreach ($items as &$issuedItem) {
        if ((int)$issuedItem['is_serialized'] === 1) {
            $snStmt = $pdo->prepare("\n                SELECT COUNT(*)\n                FROM serial_numbers\n                WHERE master_item_id = ?\n                  AND current_holder_id = ?\n                  AND status IN ('ISSUED', 'LOANED')\n            ");
            $snStmt->execute([(int)$issuedItem['id'], $technicianId]);
            $issuedItem['available_serial_quantity'] = (int)$snStmt->fetchColumn();
        }
    }
    unset($issuedItem);
}
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
                    <input type="text" name="reference_code" placeholder="Referensi transaksi" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <?php endif; ?>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Instalasi</label>
                    <input type="date" name="installation_date" value="<?= date('Y-m-d') ?>" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <?php if (AuthHelper::hasRole(['SUPERADMIN', 'ADMIN'])): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Referensi Pengeluaran (Opsional)</label>
                    <input type="text" name="reference_code" placeholder="Referensi transaksi" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
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
                <div>
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Daftar Barang yang Dipasang</h2>
                    <?php if (AuthHelper::hasRole(['TEKNISI_NOC'])): ?>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Hanya barang yang sudah dipenuhi melalui pengajuan Anda yang tersedia.</p>
                    <?php endif; ?>
                </div>
                <button type="button" onclick="addItemRow()" <?= (AuthHelper::hasRole(['TEKNISI_NOC']) && empty($items)) ? 'disabled' : '' ?> class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-plus"></i> Tambah Barang
                </button>
            </div>

            <?php if (AuthHelper::hasRole(['TEKNISI_NOC']) && empty($items)): ?>
                <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:bg-amber-900/20 dark:border-amber-800 dark:text-amber-200">
                    Belum ada barang yang sudah dipenuhi untuk Anda. Proses instalasi tersedia setelah pengajuan disetujui dan barang dikeluarkan.
                </div>
            <?php endif; ?>
            
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
                    <tbody id="itemsBody" class="divide-y divide-gray-200 dark:divide-gray-700"></tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-end gap-4">
            <a href="<?= url('/installations') ?>" class="bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 py-2 px-6 rounded-md shadow-sm text-sm font-medium transition-colors">Batal</a>
            <button type="submit" <?= (AuthHelper::hasRole(['TEKNISI_NOC']) && empty($items)) ? 'disabled' : '' ?> class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-6 rounded-md shadow-sm text-sm font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed">Simpan Instalasi</button>
        </div>
    </form>
</div>

<script>
    const itemsData = <?php echo json_encode($items, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    let rowCount = 0;

    function addItemRow() {
        const tbody = document.getElementById('itemsBody');
        const rowIndex = rowCount++;
        const tr = document.createElement('tr');
        tr.dataset.index = rowIndex;

        let selectHtml = `<select name="item_ids[]" required class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm item-select" onchange="handleItemChange(this, ${rowIndex})">
            <option value="">Pilih Barang</option>`;

        itemsData.forEach(item => {
            const remaining = Math.max(0, (parseInt(item.issued_quantity) || 0) - (parseInt(item.installed_quantity) || 0));
            const qtyLabel = remaining > 0 ? ` — tersedia untuk instalasi: ${remaining}` : '';
            selectHtml += `<option value="${item.id}" data-serialized="${item.is_serialized}" data-remaining="${remaining}">${escapeHtml(item.nama)}${qtyLabel}</option>`;
        });
        selectHtml += `</select>`;

        tr.innerHTML = `
            <td class="px-4 py-3 align-top">${selectHtml}</td>
            <td class="px-4 py-3 align-top">
                <input type="number" name="quantities[]" min="1" value="1" required class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm text-center qty-input" onchange="validateQty(this, ${rowIndex})" onkeyup="validateQty(this, ${rowIndex})">
            </td>
            <td class="px-4 py-3 align-top">
                <div id="sn-container-${rowIndex}" class="hidden">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Masukkan SN yang sedang Anda pegang untuk barang ini.</p>
                    <textarea name="sn_list[]" id="sn-input-${rowIndex}" rows="3" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" placeholder="SN001\nSN002..." onkeyup="validateSnCount(${rowIndex})" onchange="validateSnCount(${rowIndex})"></textarea>
                    <div id="sn-counter-${rowIndex}" class="text-xs font-bold text-red-500 mt-1">SN Diisi: 0 / 1</div>
                </div>
                <div id="sn-disabled-${rowIndex}" class="text-sm text-gray-500 dark:text-gray-400 italic py-2">Bukan barang serial.</div>
            </td>
            <td class="px-4 py-3 text-center align-top">
                <button type="button" onclick="this.closest('tr').remove()" class="text-red-500 hover:text-red-700 mt-1"><i class="fas fa-trash"></i></button>
            </td>
        `;
        tbody.appendChild(tr);
        handleItemChange(tr.querySelector('.item-select'), rowIndex);
    }

    function handleItemChange(selectElem, rowIndex) {
        const option = selectElem.options[selectElem.selectedIndex];
        const isSerialized = option && option.getAttribute('data-serialized') === '1';
        const remaining = option ? parseInt(option.getAttribute('data-remaining') || '0') : 0;
        const tr = document.querySelector(`tr[data-index="${rowIndex}"]`);
        if (!tr) return;
        const qtyInput = tr.querySelector('.qty-input');
        qtyInput.max = remaining > 0 ? remaining : '';

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
            validateSnCount(rowIndex);
        }
    }

    function validateQty(qtyElem, rowIndex) {
        const tr = document.querySelector(`tr[data-index="${rowIndex}"]`);
        if (!tr) return;
        const select = tr.querySelector('.item-select');
        const option = select.options[select.selectedIndex];
        const remaining = option ? parseInt(option.getAttribute('data-remaining') || '0') : 0;
        const qty = parseInt(qtyElem.value) || 0;
        if (remaining > 0 && qty > remaining) {
            qtyElem.value = remaining;
        }
        validateSnCount(rowIndex);
    }

    function validateSnCount(rowIndex) {
        const tr = document.querySelector(`tr[data-index="${rowIndex}"]`);
        if (!tr) return;
        const qtyInput = tr.querySelector('.qty-input');
        const snInput = document.getElementById(`sn-input-${rowIndex}`);
        const counter = document.getElementById(`sn-counter-${rowIndex}`);
        if (!snInput || !counter) return;

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

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>'"]/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[ch]));
    }

    <?php if (!empty($items)): ?>
    addItemRow();
    <?php endif; ?>
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>