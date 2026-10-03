<?php
$pageTitle = 'Peminjaman Baru';
ob_start();
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
use App\Helpers\AuthHelper;
?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="mb-6 flex items-center">
    <a href="<?= url('/loans') ?>" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 mr-4">
      <i class="fas fa-arrow-left"></i>
    </a>
    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Peminjaman Baru</h1>
  </div>

  <?php if (isset($_SESSION['error'])): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
      <span class="block sm:inline"><?= htmlspecialchars($_SESSION['error']) ?></span>
      <?php unset($_SESSION['error']); ?>
    </div>
  <?php endif; ?>

  <form action="<?= url('/loans/store') ?>" method="POST" class="space-y-6" id="loanForm">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
      <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 border-b dark:border-gray-700 pb-2">Detail Peminjaman</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Gudang Asal</label>
          <select name="warehouse_id" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm">
            <option value="">Pilih Gudang</option>
            <?php foreach ($warehouses as $wh): ?>
              <option value="<?= $wh['id'] ?>"><?= htmlspecialchars($wh['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipe Peminjaman</label>
          <select name="loan_type" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm">
            <option value="INTERNAL">Internal</option>
            <option value="EXTERNAL">Eksternal</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Referensi Pengajuan (Opsional)</label>
          <select name="submission_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm">
            <option value="">Tanpa Pengajuan</option>
            <?php foreach ($submissions as $sub): ?>
              <option value="<?= $sub['id'] ?>">Pengajuan tersedia</option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Jatuh Tempo (Opsional)</label>
          <input type="date" name="due_date" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm">
        </div>

        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Catatan / Alasan</label>
          <textarea name="notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm"></textarea>
        </div>
      </div>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 sm:p-6">
      <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-4 border-b dark:border-gray-700 pb-3">
        <div>
          <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Daftar Barang yang Dipinjam</h2>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Setiap barang dapat dibuka atau disembunyikan. Pilih dari stok atau catat barang manual/custom.</p>
        </div>
        <button type="button" onclick="addItemRow()" class="inline-flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold py-2.5 px-4 rounded-lg shadow transition-colors w-full sm:w-auto min-h-[44px]">
          <i class="fas fa-plus"></i> Tambah Barang
        </button>
      </div>

      <div id="itemsBody" class="space-y-3"></div>
    </div>

    <div class="flex justify-end gap-4">
      <a href="<?= url('/loans') ?>" class="bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 py-2 px-6 rounded-md shadow-sm text-sm font-medium">Batal</a>
      <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-6 rounded-md shadow-sm text-sm font-medium">Simpan Peminjaman</button>
    </div>
  </form>
</div>

<script>
const itemsData = <?= json_encode($items, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
let rowCount = 0;

function escapeHtml(value) {
  return String(value ?? '').replace(/[&<>"']/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[ch]));
}

function getRowSummary(index) {
  const row = document.querySelector(`.loan-item-card[data-index="${index}"]`);
  if (!row) return { name: 'Barang belum dipilih', qty: 0 };

  const source = row.querySelector('.source-type')?.value || 'STOCK';
  const qty = parseInt(row.querySelector('.qty-input')?.value || '0', 10);
  let name = '';

  if (source === 'STOCK') {
    const select = row.querySelector('.item-select');
    if (select && select.value) {
      name = select.options[select.selectedIndex]?.textContent?.trim() || '';
    }
  } else {
    name = row.querySelector('.manual-input')?.value?.trim() || '';
  }

  return {
    name: name || 'Barang belum dipilih',
    qty: Number.isFinite(qty) && qty > 0 ? qty : 0
  };
}

function updateRowMeta(index) {
  const row = document.querySelector(`.loan-item-card[data-index="${index}"]`);
  if (!row) return;

  const rows = Array.from(document.querySelectorAll('#itemsBody .loan-item-card'));
  const position = rows.indexOf(row) + 1;
  const title = row.querySelector('.item-title');
  const name = row.querySelector('.item-summary-name');
  const qty = row.querySelector('.item-summary-qty');
  const body = row.querySelector('.item-body');
  const toggle = row.querySelector('.item-collapse-btn');

  if (title) title.textContent = 'Barang ' + position;

  const summary = getRowSummary(index);
  if (name) name.textContent = summary.name;
  if (qty) qty.textContent = summary.qty > 0 ? 'Qty ' + summary.qty : 'Qty -';

  if (toggle && body) {
    const collapsed = body.classList.contains('hidden');
    toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
    toggle.setAttribute('title', collapsed ? 'Buka detail barang' : 'Sembunyikan detail barang');
    const icon = toggle.querySelector('.collapse-icon');
    if (icon) icon.className = 'fas ' + (collapsed ? 'fa-chevron-down' : 'fa-chevron-up') + ' collapse-icon text-xs';
  }
}

function renumberRows() {
  document.querySelectorAll('#itemsBody .loan-item-card').forEach((row, idx) => updateRowMeta(Number(row.dataset.index)));
}

function toggleItemCard(index) {
  const row = document.querySelector(`.loan-item-card[data-index="${index}"]`);
  if (!row) return;
  const body = row.querySelector('.item-body');
  if (!body) return;

  const collapse = !body.classList.contains('hidden');
  body.classList.toggle('hidden', collapse);
  row.classList.toggle('item-collapsed', collapse);
  updateRowMeta(index);
}

function removeItemRow(index) {
  const row = document.querySelector(`.loan-item-card[data-index="${index}"]`);
  if (row) row.remove();
  renumberRows();
}

function addItemRow() {
  const container = document.getElementById('itemsBody');
  const index = rowCount++;

  const options = itemsData.map(item =>
    `<option value="${item.id}" data-serialized="${item.is_serialized}">${escapeHtml(item.nama)}</option>`
  ).join('');

  const card = document.createElement('div');
  card.className = 'loan-item-card bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-xl p-4 space-y-4';
  card.dataset.index = index;

  card.innerHTML = `
    <div class="flex items-center justify-between gap-3">
      <div class="min-w-0 flex-1">
        <div class="flex items-center gap-2 min-w-0">
          <span class="item-title whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white">Barang ${index + 1}</span>
          <span class="item-summary-qty shrink-0 inline-flex items-center px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 text-xs font-semibold">Qty -</span>
        </div>
        <p class="item-summary-name mt-1 text-sm font-medium text-gray-600 dark:text-gray-300 truncate">Barang belum dipilih</p>
      </div>

      <div class="flex items-center gap-1 shrink-0">
        <button type="button" onclick="toggleItemCard(${index})" class="item-collapse-btn inline-flex items-center justify-center w-10 h-10 rounded-lg text-gray-600 hover:text-blue-600 hover:bg-blue-50 dark:text-gray-300 dark:hover:text-blue-300 dark:hover:bg-blue-900/20 transition-colors" aria-expanded="true" title="Sembunyikan detail barang">
          <i class="fas fa-chevron-up collapse-icon text-xs"></i>
        </button>
        <button type="button" onclick="removeItemRow(${index})" title="Hapus barang" aria-label="Hapus barang" class="inline-flex items-center justify-center w-10 h-10 rounded-lg text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors">
          <i class="fas fa-trash"></i>
        </button>
      </div>
    </div>

    <div class="item-body space-y-4">
      <div>
        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Sumber Barang</label>
        <select name="source_types[]" class="source-type block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
          <option value="STOCK">Dari Stok</option>
          <option value="MANUAL">Manual / Custom</option>
        </select>
      </div>

      <div id="stock-fields-${index}">
        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Barang</label>
        <select name="item_ids[]" required class="item-select block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
          <option value="">Pilih Barang</option>
          ${options}
        </select>
      </div>

      <div id="manual-fields-${index}" class="hidden">
        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Nama Barang Custom</label>
        <input type="text" name="manual_names[]" placeholder="Nama barang custom" class="manual-input block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
      </div>

      <div>
        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Kuantitas</label>
        <input type="number" name="quantities[]" min="1" value="1" required class="qty-input block w-32 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm text-center">
      </div>

      <div id="sn-fields-${index}">
        <div id="sn-container-${index}" class="hidden">
          <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Serial Number (Jika Berlaku)</label>
          <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Pilih SN yang tersedia di gudang asal.</p>
          <select name="sn_list[${index}][]" id="sn-input-${index}" multiple size="4" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm"></select>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pilih sesuai jumlah yang dipinjam.</p>
        </div>
        <div id="sn-disabled-${index}" class="text-sm text-gray-500 dark:text-gray-400 italic py-2">Pilih barang terlebih dahulu.</div>
      </div>
    </div>
  `;

  container.appendChild(card);

  const sourceSelect = card.querySelector('.source-type');
  const itemSelect = card.querySelector('.item-select');
  const qtyInput = card.querySelector('.qty-input');
  const manualInput = card.querySelector('.manual-input');

  sourceSelect.addEventListener('change', () => handleSourceChange(index));
  itemSelect.addEventListener('change', () => {
    handleItemChange(index);
    updateRowMeta(index);
  });
  qtyInput.addEventListener('input', () => updateRowMeta(index));
  manualInput.addEventListener('input', () => updateRowMeta(index));

  handleSourceChange(index);
  updateRowMeta(index);
}

function handleSourceChange(index) {
  const row = document.querySelector(`.loan-item-card[data-index="${index}"]`);
  if (!row) return;
  const source = row.querySelector('.source-type').value;
  const stockFields = document.getElementById(`stock-fields-${index}`);
  const manualFields = document.getElementById(`manual-fields-${index}`);
  const itemSelect = row.querySelector('.item-select');

  if (source === 'MANUAL') {
    stockFields.classList.add('hidden');
    manualFields.classList.remove('hidden');
    itemSelect.removeAttribute('required');
    document.getElementById(`sn-container-${index}`).classList.add('hidden');
    document.getElementById(`sn-disabled-${index}`).textContent = 'Barang manual tidak terkait stok / serial.';
  } else {
    stockFields.classList.remove('hidden');
    manualFields.classList.add('hidden');
    itemSelect.setAttribute('required', 'required');
    document.getElementById(`sn-disabled-${index}`).textContent = 'Pilih barang terlebih dahulu.';
    handleItemChange(index);
  }

  updateRowMeta(index);
}

function handleItemChange(index) {
  const row = document.querySelector(`.loan-item-card[data-index="${index}"]`);
  if (!row) return;
  const select = row.querySelector('.item-select');
  const option = select.options[select.selectedIndex];
  const isSerialized = option && option.dataset.serialized === '1';
  const snContainer = document.getElementById(`sn-container-${index}`);
  const snDisabled = document.getElementById(`sn-disabled-${index}`);
  const snSelect = document.getElementById(`sn-input-${index}`);

  snSelect.innerHTML = '';
  if (!isSerialized || !option.value) {
    snContainer.classList.add('hidden');
    snDisabled.classList.remove('hidden');
    return;
  }

  snContainer.classList.remove('hidden');
  snDisabled.classList.add('hidden');
  loadSerials(index);
}

async function loadSerials(index) {
  const row = document.querySelector(`.loan-item-card[data-index="${index}"]`);
  const warehouse = document.querySelector('[name="warehouse_id"]').value;
  const itemId = row?.querySelector('.item-select')?.value;
  const snSelect = document.getElementById(`sn-input-${index}`);
  if (!warehouse || !itemId || !snSelect) return;

  try {
    const response = await fetch(`<?= url('/api/stock') ?>?warehouse_id=${encodeURIComponent(warehouse)}&item_id=${encodeURIComponent(itemId)}`);
    const data = await response.json();
    const serials = Array.isArray(data.serials) ? data.serials : [];
    snSelect.innerHTML = serials.map(sn => `<option value="${escapeHtml(sn.serial_number || sn)}">${escapeHtml(sn.serial_number || sn)}</option>`).join('');
  } catch (e) {
    snSelect.innerHTML = '';
  }
}

document.querySelector('[name="warehouse_id"]').addEventListener('change', () => {
  document.querySelectorAll('#itemsBody .loan-item-card').forEach(row => handleItemChange(Number(row.dataset.index)));
});

addItemRow();
</script>>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>