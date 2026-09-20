<?php
$pageTitle = 'Instalasi Baru';
ob_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
use App\Helpers\AuthHelper;
use App\Core\Database;

$technicianId = (int)AuthHelper::user()['id'];

// $items sudah dibatasi controller/view sebelumnya menjadi barang yang telah dipenuhi
// melalui pengajuan milik teknisi. Untuk item serial, hanya SN yang sedang dipegang
// teknisi yang ditawarkan di UI.
$serialsByItem = [];
if (!empty($items)) {
    $itemIds = array_values(array_unique(array_map('intval', array_column($items, 'id'))));
    if (!empty($itemIds)) {
        $pdo = Database::getInstance()->getConnection();
        $placeholders = implode(',', array_fill(0, count($itemIds), '?'));
        $serialStmt = $pdo->prepare("SELECT id, master_item_id, serial_number FROM serial_numbers WHERE master_item_id IN ($placeholders) AND current_holder_id = ? AND status IN ('ISSUED','LOANED') ORDER BY serial_number ASC");
        $params = $itemIds;
        $params[] = $technicianId;
        $serialStmt->execute($params);
        foreach ($serialStmt->fetchAll() as $row) {
            $serialsByItem[(int)$row['master_item_id']][] = $row;
        }
    }
}

$eligibleItems = [];
foreach (($items ?? []) as $item) {
    $itemId = (int)$item['id'];
    $isSerialized = (int)$item['is_serialized'] === 1;
    if ($isSerialized && empty($serialsByItem[$itemId])) {
        continue;
    }
    $eligibleItems[] = $item;
}
?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 flex items-center">
        <a href="<?= url('/installations') ?>" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 mr-4">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Instalasi Baru</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Satu barang per form instalasi. Untuk barang serial, pilih nomor seri yang sedang Anda pegang.</p>
        </div>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            <span class="block sm:inline"><?= htmlspecialchars($_SESSION['error']) ?></span>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($eligibleItems)): ?>
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-800 dark:bg-amber-900/20 dark:border-amber-800 dark:text-amber-200">
                Belum ada barang yang siap dipasang. Pastikan pengajuan Anda sudah dipenuhi dan untuk barang serial, nomor seri sudah berada pada Anda.
            </div>
            <div class="mt-4">
                <a href="<?= url('/installations') ?>" class="inline-flex items-center gap-2 bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 py-2 px-5 rounded-md text-sm font-medium">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    <?php else: ?>

    <form action="<?= url('/installations/store') ?>" method="POST" enctype="multipart/form-data" class="space-y-6" id="installationForm">
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
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Instalasi</label>
                    <input type="date" name="installation_date" value="<?= date('Y-m-d') ?>" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Referensi Pengeluaran (Opsional)</label>
                    <input type="text" name="reference_code" placeholder="Referensi transaksi" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
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
            <div class="mb-4 border-b dark:border-gray-700 pb-2">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Barang yang Dipasang</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Teknisi hanya dapat memasang 1 barang dalam 1 form instalasi.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-5 items-start">
                <div class="md:col-span-5">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Barang</label>
                    <select name="item_ids[]" id="itemSelect" required class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        <option value="">Pilih Barang</option>
                        <?php foreach ($eligibleItems as $item): ?>
                            <?php $itemId = (int)$item['id']; ?>
                            <option value="<?= $itemId ?>" data-serialized="<?= (int)$item['is_serialized'] === 1 ? '1' : '0' ?>">
                                <?= htmlspecialchars($item['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kuantitas</label>
                    <input type="number" name="quantities[]" value="1" min="1" max="1" readonly class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-100 shadow-sm text-sm text-center cursor-not-allowed">
                </div>

                <div class="md:col-span-5">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Serial Number</label>
                    <select id="serialSelect" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" disabled>
                        <option value="">Pilih barang terlebih dahulu</option>
                    </select>
                    <input type="hidden" name="sn_list[]" id="serialHidden" value="">
                    <p id="serialHelp" class="text-xs text-gray-500 dark:text-gray-400 mt-1">Untuk barang serial, pilih SN yang sedang Anda pegang. SN tidak diketik manual.</p>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-4">
            <a href="<?= url('/installations') ?>" class="bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 py-2 px-6 rounded-md shadow-sm text-sm font-medium transition-colors">Batal</a>
            <button type="submit" id="submitInstallation" disabled class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-6 rounded-md shadow-sm text-sm font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed">Simpan Instalasi</button>
        </div>
    </form>

    <?php endif; ?>
</div>

<script>
    const serialsByItem = <?= json_encode($serialsByItem, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const itemSelect = document.getElementById('itemSelect');
    const serialSelect = document.getElementById('serialSelect');
    const serialHidden = document.getElementById('serialHidden');
    const submitInstallation = document.getElementById('submitInstallation');
    const installationForm = document.getElementById('installationForm');

    function refreshInstallationPicker() {
        if (!itemSelect || !serialSelect || !serialHidden || !submitInstallation) return;

        const option = itemSelect.options[itemSelect.selectedIndex];
        const itemId = itemSelect.value;
        const isSerialized = option?.dataset.serialized === '1';
        const serials = itemId ? (serialsByItem[itemId] || []) : [];

        serialHidden.value = '';
        serialSelect.innerHTML = '';

        if (!itemId) {
            serialSelect.disabled = true;
            serialSelect.innerHTML = '<option value="">Pilih barang terlebih dahulu</option>';
            submitInstallation.disabled = true;
            return;
        }

        if (!isSerialized) {
            serialSelect.disabled = true;
            serialSelect.innerHTML = '<option value="">Barang ini tidak menggunakan serial number</option>';
            submitInstallation.disabled = false;
            return;
        }

        serialSelect.disabled = false;
        serialSelect.innerHTML = '<option value="">Pilih Serial Number...</option>';
        serials.forEach(function (sn) {
            const opt = document.createElement('option');
            opt.value = sn.serial_number;
            opt.textContent = sn.serial_number;
            serialSelect.appendChild(opt);
        });

        submitInstallation.disabled = serials.length === 0;
    }

    if (itemSelect) {
        itemSelect.addEventListener('change', refreshInstallationPicker);
    }

    if (serialSelect) {
        serialSelect.addEventListener('change', function () {
            serialHidden.value = this.value || '';
            submitInstallation.disabled = !itemSelect.value || (itemSelect.options[itemSelect.selectedIndex]?.dataset.serialized === '1' && !this.value);
        });
    }

    if (installationForm) {
        installationForm.addEventListener('submit', function (event) {
            if (!itemSelect.value) {
                event.preventDefault();
                return;
            }

            const option = itemSelect.options[itemSelect.selectedIndex];
            const isSerialized = option?.dataset.serialized === '1';
            if (isSerialized && !serialHidden.value) {
                event.preventDefault();
                alert('Pilih Serial Number yang sedang Anda pegang.');
            }
        });
    }
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>