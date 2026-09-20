<?php
$title = 'Pemenuhan Barang';
$pageTitle = 'Pemenuhan Barang';

ob_start();
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<div class="mb-6">
  <a href="<?php echo url('/outgoing'); ?>" class="text-blue-600 dark:text-blue-400 hover:underline">
    <i class="fas fa-arrow-left mr-1"></i> Kembali ke Barang Keluar
  </a>
</div>

<div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 border border-gray-200 dark:border-gray-700">
  <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-6">Tambah Barang Keluar Baru</h2>

  <form action="<?php echo url('/outgoing/store'); ?>" method="POST" id="outgoingForm">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
      <div>
        <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">
          Gudang Asal <span id="whHint" class="font-normal text-xs text-gray-400"></span>
        </label>
        <?php if (empty($warehouses)): ?>
          <div class="text-sm text-red-500 dark:text-red-400 py-2">Tidak ada gudang aktif.</div>
        <?php else: ?>
          <select name="warehouse_id" id="warehouse_id" required class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-full py-2 px-3 text-gray-700 dark:text-white dark:bg-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Pilih Gudang</option>
            <?php foreach ($warehouses as $wh): ?>
              <option value="<?php echo (int)$wh['id']; ?>" <?php echo ((int)($preselectWarehouseId ?? 0) === (int)$wh['id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($wh['name'] . (!empty($wh['address']) ? ' — ' . $wh['address'] : '')); ?>
              </option>
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
                $subLabelParts = [$sub['submitter_name'], $subDate];
                if (!empty($sub['items_summary'])) {
                  $subLabelParts[] = $sub['items_summary'];
                }
                $subLabel = htmlspecialchars(implode(' — ', $subLabelParts));
              ?>
              <option value="<?php echo (int)$sub['id']; ?>" <?php echo ((int)($preselectSubmissionId ?? 0) === (int)$sub['id']) ? 'selected' : ''; ?>><?php echo $subLabel; ?></option>
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
          <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
              <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Barang</th>
              <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase w-32">Kuantitas</th>
              <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase min-w-[360px]">Serial Number (Jika Berlaku)</th>
              <th class="px-4 py-2 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase w-16">Aksi</th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700" id="itemsBody"></tbody>
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
  const preselectSubmissionId = <?php echo (int)($preselectSubmissionId ?? 0); ?>;
  const warehouseMap = {};
  warehouses.forEach(wh => {
    warehouseMap[wh.id] = wh.name + (wh.address ? ' — ' + wh.address : '');
  });

  let rowCount = 0;

  function esc(value) {
    return String(value ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function buildSerialPicker(rowIndex, masterItemId, warehouseId, quantity, serialized) {
    const wrap = document.getElementById(`serial-picker-${rowIndex}`);
    const textarea = document.getElementById(`sn-input-${rowIndex}`);
    const counter = document.getElementById(`sn-counter-${rowIndex}`);
    if (!wrap || !textarea || !counter) return;

    if (!serialized) {
      wrap.innerHTML = '<div class="text-sm text-gray-500 dark:text-gray-400 italic py-2">Bukan barang serial.</div>';
      textarea.value = '';
      textarea.disabled = true;
      counter.textContent = '';
      return;
    }

    textarea.disabled = false;
    wrap.innerHTML = `
      <div class="space-y-2">
        <div class="flex items-center gap-2">
          <input id="sn-search-${rowIndex}" type="search" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg py-2 px-3 text-sm text-gray-700 dark:text-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Cari Serial Number..." autocomplete="off">
          <span id="sn-load-${rowIndex}" class="hidden text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">Memuat...</span>
        </div>
        <div id="sn-options-${rowIndex}" class="border border-gray-200 dark:border-gray-700 rounded-lg max-h-44 overflow-y-auto bg-white dark:bg-gray-800"></div>
        <div class="flex items-center justify-between text-xs">
          <span class="text-gray-500 dark:text-gray-400">Pilih SN yang benar-benar ada di gudang asal.</span>
          <span id="sn-selected-${rowIndex}" class="font-semibold text-gray-700 dark:text-gray-200">0 dipilih</span>
        </div>
        <div id="sn-chips-${rowIndex}" class="flex flex-wrap gap-2"></div>
      </div>`;

    const search = document.getElementById(`sn-search-${rowIndex}`);
    const options = document.getElementById(`sn-options-${rowIndex}`);
    const loadLabel = document.getElementById(`sn-load-${rowIndex}`);
    const selectedLabel = document.getElementById(`sn-selected-${rowIndex}`);
    const chips = document.getElementById(`sn-chips-${rowIndex}`);
    let available = [];
    let selected = [];

    const maxQty = () => parseInt(document.querySelector(`tr[data-index="${rowIndex}"] .qty-input`)?.value || quantity || 0, 10) || 0;

    function sync() {
      textarea.value = selected.join('\n');
      const qty = maxQty();
      selectedLabel.textContent = `${selected.length} / ${qty} dipilih`;
      counter.textContent = `SN Dipilih: ${selected.length} / ${qty}`;
      counter.className = selected.length === qty
        ? 'text-xs font-bold text-green-600 mt-1'
        : (selected.length > qty ? 'text-xs font-bold text-red-600 mt-1' : 'text-xs font-bold text-orange-500 mt-1');

      chips.innerHTML = selected.map(sn => `
        <button type="button" data-sn="${esc(sn)}" class="inline-flex items-center gap-1 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-200 border border-blue-200 dark:border-blue-800 rounded-full px-2.5 py-1 text-xs font-semibold hover:bg-blue-100">
          ${esc(sn)} <i class="fas fa-times"></i>
        </button>`).join('');

      chips.querySelectorAll('button[data-sn]').forEach(btn => {
        btn.addEventListener('click', () => {
          selected = selected.filter(sn => sn !== btn.dataset.sn);
          renderOptions();
          sync();
        });
      });
    }

    function renderOptions() {
      const q = search.value.trim().toLowerCase();
      const filtered = available.filter(sn => !q || sn.toLowerCase().includes(q));
      if (filtered.length === 0) {
        options.innerHTML = '<div class="px-3 py-3 text-sm text-gray-500 dark:text-gray-400">Tidak ada Serial Number tersedia.</div>';
        return;
      }
      options.innerHTML = filtered.map(sn => `
        <label class="flex items-center gap-3 px-3 py-2 hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-700 cursor-pointer border-b last:border-b-0 border-gray-100 dark:border-gray-700">
          <input type="checkbox" value="${esc(sn)}" class="sn-check rounded border-gray-300 dark:border-gray-700 text-blue-600 focus:ring-blue-500" ${selected.includes(sn) ? 'checked' : ''}>
          <span class="text-sm text-gray-800 dark:text-gray-100">${esc(sn)}</span>
        </label>`).join('');

      options.querySelectorAll('.sn-check').forEach(cb => {
        cb.addEventListener('change', () => {
          const sn = cb.value;
          const qty = maxQty();
          if (cb.checked) {
            if (selected.length >= qty) {
              cb.checked = false;
              if (typeof AppToast !== 'undefined' && AppToast.warning) AppToast.warning(`Maksimal ${qty} Serial Number untuk kuantitas ini.`);
              return;
            }
            if (!selected.includes(sn)) selected.push(sn);
          } else {
            selected = selected.filter(x => x !== sn);
          }
          sync();
          renderOptions();
        });
      });
    }

    search.addEventListener('input', renderOptions);
    search.addEventListener('keydown', e => {
      if (e.key === 'Enter') e.preventDefault();
    });

    async function load() {
      if (!masterItemId || !warehouseId) {
        available = [];
        renderOptions();
        sync();
        return;
      }
      loadLabel.classList.remove('hidden');
      try {
        const response = await fetch(`<?= url('/api/stock') ?>?item_id=${encodeURIComponent(masterItemId)}&warehouse_id=${encodeURIComponent(warehouseId)}`, { headers: { 'Accept': 'application/json' } });
        const data = await response.json();
        available = Array.isArray(data.serials) ? data.serials.map(x => x.serial_number) : [];
        selected = selected.filter(sn => available.includes(sn));
        renderOptions();
        sync();
      } catch (err) {
        available = [];
        renderOptions();
        sync();
        if (typeof AppToast !== 'undefined' && AppToast.error) AppToast.error('Gagal memuat Serial Number dari stok gudang.');
      } finally {
        loadLabel.classList.add('hidden');
      }
    }

    load();
  }

  function refreshSerialPicker(rowIndex) {
    const tr = document.querySelector(`tr[data-index="${rowIndex}"]`);
    if (!tr) return;
    const masterItemId = tr.dataset.masterItemId || '';
    const serialized = tr.dataset.serialized === '1';
    const warehouseId = document.getElementById('warehouse_id')?.value || '';
    const qty = parseInt(tr.querySelector('.qty-input')?.value || '0', 10) || 0;
    buildSerialPicker(rowIndex, masterItemId, warehouseId, qty, serialized);
  }

  function addItemRow() {
    const tbody = document.getElementById('itemsBody');
    const rowIndex = rowCount++;
    const tr = document.createElement('tr');
    tr.dataset.index = rowIndex;
    tr.dataset.masterItemId = '';
    tr.dataset.serialized = '0';

    let selectHtml = `<select name="item_ids[]" required class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-full py-2 px-3 text-gray-700 dark:text-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 item-select" onchange="handleItemChange(this, ${rowIndex})">
      <option value="">Pilih Barang</option>`;
    itemsData.forEach(item => {
      selectHtml += `<option value="${esc(item.id)}" data-master-item-id="${esc(item.id)}" data-serialized="${esc(item.is_serialized)}">${esc(item.nama)}</option>`;
    });
    selectHtml += '</select>';

    tr.innerHTML = `
      <td class="px-4 py-3 align-top">${selectHtml}</td>
      <td class="px-4 py-3 align-top">
        <input type="number" name="quantities[]" min="1" value="1" required class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-full py-2 px-3 text-gray-700 dark:text-white dark:bg-gray-700 text-center focus:outline-none focus:ring-2 focus:ring-blue-500 qty-input" onchange="validateQty(this, ${rowIndex})" oninput="validateQty(this, ${rowIndex})">
      </td>
      <td class="px-4 py-3 align-top">
        <div id="serial-picker-${rowIndex}"></div>
        <input type="hidden" name="sn_list[]" id="sn-input-${rowIndex}" value="" disabled>
        <div id="sn-counter-${rowIndex}" class="text-xs font-bold text-gray-500 dark:text-gray-400 mt-1"></div>
      </td>
      <td class="px-4 py-3 text-center align-top">
        <button type="button" onclick="this.closest('tr').remove()" class="text-red-500 hover:text-red-700 mt-1"><i class="fas fa-trash"></i></button>
      </td>`;
    tbody.appendChild(tr);
  }

  function handleItemChange(selectElem, rowIndex) {
    const option = selectElem.options[selectElem.selectedIndex];
    const tr = document.querySelector(`tr[data-index="${rowIndex}"]`);
    if (!tr) return;
    tr.dataset.masterItemId = option?.dataset?.masterItemId || '';
    tr.dataset.serialized = option?.dataset?.serialized || '0';
    refreshSerialPicker(rowIndex);
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

    btnAddRow.style.display = 'none';

    fetch('<?= url('/submissions/api/items?id=') ?>' + encodeURIComponent(submissionId))
      .then(response => response.json())
      .then(data => {
        if (data.length === 0) {
          tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-gray-500 dark:text-gray-400">Tidak ada barang yang perlu dikeluarkan.</td></tr>';
          return;
        }

        data.forEach(item => addSubItemRow(item));

        const whSelect = document.getElementById('warehouse_id');
        const whHint = document.getElementById('whHint');
        const whSet = new Set(data.map(i => i.warehouse_id ? String(i.warehouse_id) : null).filter(Boolean));
        if (whSet.size === 1) {
          whSelect.value = [...whSet][0];
          whHint.textContent = '(otomatis: Gudang Pemenuhan pengajuan)';
          document.querySelectorAll('#itemsBody tr[data-index]').forEach(tr => refreshSerialPicker(tr.dataset.index));
        } else if (whSet.size > 1) {
          whSelect.value = '';
          whHint.textContent = '(item memakai gudang berbeda — proses per gudang)';
          if (typeof AppToast !== 'undefined' && AppToast.warning) AppToast.warning('Item pengajuan memakai Gudang Pemenuhan berbeda. Proses per gudang.');
        } else {
          whSelect.value = '';
          whHint.textContent = '(pengajuan lama tanpa gudang — pilih gudang eksplisit)';
        }
      })
      .catch(() => {
        if (typeof AppToast !== 'undefined' && AppToast.error) AppToast.error('Gagal mengambil data pengajuan.');
      });
  }

  function addSubItemRow(item) {
    const tbody = document.getElementById('itemsBody');
    const rowIndex = rowCount++;
    const tr = document.createElement('tr');
    tr.dataset.index = rowIndex;
    tr.dataset.masterItemId = item.master_item_id || '';
    tr.dataset.serialized = item.is_serialized == 1 ? '1' : '0';

    const itemLabel = `
      <input type="hidden" name="item_ids[]" value="${esc(item.submission_item_id)}">
      <div class="font-medium text-gray-900 dark:text-gray-100">${esc(item.nama)}</div>
      <div class="text-xs text-blue-600 mt-1">Disetujui: ${esc(item.approved_quantity)} | Sisa: ${esc(item.remaining_quantity)}</div>
      ${item.warehouse_id ? `<div class="text-xs text-gray-500 dark:text-gray-400"><i class="fas fa-warehouse mr-1"></i>Gudang Pemenuhan: ${esc(warehouseMap[item.warehouse_id] || 'Gudang tidak diketahui')}</div>` : ''}`;

    tr.innerHTML = `
      <td class="px-4 py-3 align-top">${itemLabel}</td>
      <td class="px-4 py-3 align-top">
        <input type="number" name="quantities[]" min="1" max="${esc(item.remaining_quantity)}" value="${esc(item.remaining_quantity)}" required class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-full py-2 px-3 text-gray-700 dark:bg-gray-700 dark:text-white text-center focus:outline-none focus:ring-2 focus:ring-blue-500 qty-input" onchange="validateQty(this, ${rowIndex})" oninput="validateQty(this, ${rowIndex})">
      </td>
      <td class="px-4 py-3 align-top">
        <div id="serial-picker-${rowIndex}"></div>
        <input type="hidden" name="sn_list[]" id="sn-input-${rowIndex}" value="" ${item.is_serialized == 1 ? '' : 'disabled'}>
        <div id="sn-counter-${rowIndex}" class="text-xs font-bold text-gray-500 dark:text-gray-400 mt-1"></div>
      </td>
      <td class="px-4 py-3 text-center align-top">
        <button type="button" onclick="this.closest('tr').remove()" class="text-red-500 hover:text-red-700 mt-1"><i class="fas fa-trash"></i></button>
      </td>`;

    tbody.appendChild(tr);
    refreshSerialPicker(rowIndex);
  }

  function validateQty(qtyElem, rowIndex) {
    const tr = document.querySelector(`tr[data-index="${rowIndex}"]`);
    if (!tr) return;
    const picker = document.getElementById(`serial-picker-${rowIndex}`);
    if (picker && tr.dataset.serialized === '1') {
      const selectedButtons = picker.querySelectorAll('button[data-sn]');
      const counter = document.getElementById(`sn-counter-${rowIndex}`);
      const qty = parseInt(qtyElem.value, 10) || 0;
      const selected = selectedButtons.length;
      if (counter) {
        counter.textContent = `SN Dipilih: ${selected} / ${qty}`;
        counter.className = selected === qty ? 'text-xs font-bold text-green-600 mt-1' : (selected > qty ? 'text-xs font-bold text-red-600 mt-1' : 'text-xs font-bold text-orange-500 mt-1');
      }
    }
    if (tr.dataset.serialized === '1') refreshSerialPicker(rowIndex);
  }

  document.getElementById('warehouse_id')?.addEventListener('change', function() {
    document.querySelectorAll('#itemsBody tr[data-index]').forEach(tr => {
      if (tr.dataset.serialized === '1') refreshSerialPicker(tr.dataset.index);
    });
  });

  document.getElementById('outgoingForm')?.addEventListener('submit', function(e) {
    const rows = document.querySelectorAll('#itemsBody tr[data-index]');
    for (const tr of rows) {
      if (tr.dataset.serialized !== '1') continue;
      const qty = parseInt(tr.querySelector('.qty-input')?.value || '0', 10) || 0;
      const raw = tr.querySelector('input[name="sn_list[]"]')?.value || '';
      const sns = raw.split(/[\n,]+/).map(s => s.trim()).filter(Boolean);
      if (sns.length !== qty) {
        e.preventDefault();
        if (typeof AppToast !== 'undefined' && AppToast.error) AppToast.error(`Serial Number harus dipilih tepat ${qty} untuk setiap barang serial.`);
        return false;
      }
    }
  }, {capture: true});

  if (preselectSubmissionId > 0) {
    handleSubmissionChange(String(preselectSubmissionId));
  } else {
    addItemRow();
  }
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>