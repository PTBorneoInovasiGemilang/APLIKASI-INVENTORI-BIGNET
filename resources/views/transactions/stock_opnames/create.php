<?php
$title = 'Buat Laporan Stock Opname';
$pageTitle = 'Buat Laporan Stock Opname';
ob_start();
?>

<div class="mb-6 flex justify-between items-center">
  <div>
    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100"><?= $title ?></h1>
    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Catat selisih stok gudang (Variance).</p>
  </div>
  <a href="<?= url('/opnames') ?>" class="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
    &larr; Kembali
  </a>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
  <?php if (isset($_SESSION['error'])): ?>
    <div class="mb-4 p-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
      <?= $_SESSION['error']; unset($_SESSION['error']); ?>
    </div>
  <?php endif; ?>

  <form action="<?= url('/opnames/store') ?>" method="POST" id="opnameForm">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
      <div>
        <label for="warehouse_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Lokasi Gudang <span class="text-red-500">*</span></label>
        <select id="warehouse_id" name="warehouse_id" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" onchange="fetchWarehouseItems(this.value)">
          <option value="">Pilih Gudang</option>
          <?php foreach ($warehouses as $w): ?>
            <option value="<?= $w['id'] ?>"><?= htmlspecialchars($w['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label for="notes" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Keterangan Umum</label>
        <textarea id="notes" name="notes" rows="2" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Catatan opname..."></textarea>
      </div>
    </div>

    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4 border-b pb-2 dark:border-gray-700">Pencatatan Selisih Barang (Variance)</h3>
    
    <div id="itemsContainer" class="space-y-4 mb-4">
      <!-- Template Baris Kosong Awal -->
      <div class="bg-blue-50 dark:bg-blue-900/30 text-sm text-blue-700 dark:text-blue-300 p-4 rounded text-center" id="emptyRowMsg">
        Pilih Gudang terlebih dahulu untuk memuat daftar stok sistem.
      </div>
    </div>
    
    <button type="button" id="addItemBtn" class="hidden mb-6 text-sm font-medium text-blue-600 dark:text-blue-500 hover:underline items-center">
      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
      Tambah Baris Pencatatan Manual
    </button>

    <div class="flex justify-end gap-3 border-t pt-6 dark:border-gray-700">
      <button type="button" onclick="history.back()" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:ring-4 focus:outline-none focus:ring-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700">Batal</button>
      <button type="submit" id="submitBtn" class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800" disabled>Kirim untuk Direview</button>
    </div>
  </form>
</div>

<script>
  let warehouseItemsCache = [];
  let itemIndex = 0;
  
  async function fetchWarehouseItems(warehouseId) {
    const container = document.getElementById('itemsContainer');
    const emptyMsg = document.getElementById('emptyRowMsg');
    const submitBtn = document.getElementById('submitBtn');
    const addBtn = document.getElementById('addItemBtn');
    
    if (!warehouseId) {
      container.innerHTML = '<div class="bg-blue-50 text-sm text-blue-700 p-4 rounded text-center">Pilih Gudang terlebih dahulu untuk memuat daftar stok sistem.</div>';
      submitBtn.disabled = true;
      addBtn.classList.add('hidden');
      return;
    }
    
    container.innerHTML = '<div class="text-center p-4 text-gray-500 dark:text-gray-400">Memuat data stok...</div>';
    submitBtn.disabled = true;
    
    try {
      const response = await fetch(`<?= url('/opnames/api/items') ?>?warehouse_id=${warehouseId}`);
      const data = await response.json();
      warehouseItemsCache = data;
      
      container.innerHTML = '';
      itemIndex = 0;
      
      if (data.length === 0) {
        container.innerHTML = '<div class="bg-yellow-50 text-yellow-800 p-4 rounded text-center text-sm">Gudang ini tidak memiliki stok barang non-serial saat ini. Anda dapat menambahkannya manual.</div>';
      } else {
        // Tampilkan satu baris default
        addManualRow();
      }
      
      addBtn.classList.remove('hidden');
      addBtn.classList.add('flex');
      submitBtn.disabled = false;
    } catch (e) {
      container.innerHTML = '<div class="text-red-500 p-4">Gagal memuat data.</div>';
    }
  }
  
  function addManualRow() {
    const container = document.getElementById('itemsContainer');
    
    // Buat opsi select
    let options = '<option value="">Pilih Barang...</option>';
    warehouseItemsCache.forEach(i => {
      options += `<option value="${i.master_item_id}" data-qty="${i.quantity}">${i.kode_barang} - ${i.nama} (Sistem: ${i.quantity})</option>`;
    });
    
    const rowHTML = `
      <div class="item-row bg-gray-50 dark:bg-gray-700 p-4 rounded-lg border border-gray-200 dark:border-gray-600 relative">
        <button type="button" class="remove-item absolute top-4 right-4 text-red-500 hover:text-red-700 dark:text-red-400">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
        </button>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="md:col-span-2">
            <label class="block mb-2 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Barang</label>
            <select name="items[${itemIndex}][master_item_id]" class="item-select bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 text-sm rounded-lg block w-full p-2.5" required onchange="handleItemSelect(this)">
              ${options}
            </select>
          </div>
          <div>
            <label class="block mb-2 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Stok Sistem</label>
            <input type="number" name="items[${itemIndex}][system_quantity]" class="sys-qty bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 text-sm rounded-lg block w-full p-2.5" readonly required value="0">
          </div>
          <div>
            <label class="block mb-2 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Stok Fisik</label>
            <input type="number" name="items[${itemIndex}][physical_quantity]" class="phys-qty bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 text-sm rounded-lg block w-full p-2.5" required oninput="calcVariance(this)">
          </div>
          <div class="md:col-span-4">
            <input type="text" name="items[${itemIndex}][reason]" class="reason bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 text-sm rounded-lg block w-full p-2.5" placeholder="Alasan selisih (jika ada)">
          </div>
        </div>
        <div class="mt-2 text-sm variance-text font-semibold text-gray-600 dark:text-gray-300">Selisih: 0</div>
      </div>
    `;
    
    container.insertAdjacentHTML('beforeend', rowHTML);
    itemIndex++;
  }
  
  document.getElementById('addItemBtn').addEventListener('click', addManualRow);
  
  document.getElementById('itemsContainer').addEventListener('click', (e) => {
    if (e.target.closest('.remove-item')) {
      e.target.closest('.item-row').remove();
    }
  });

  function handleItemSelect(select) {
    const option = select.options[select.selectedIndex];
    const row = select.closest('.item-row');
    const sysQtyInput = row.querySelector('.sys-qty');
    const physQtyInput = row.querySelector('.phys-qty');
    
    if (option.value) {
      sysQtyInput.value = option.dataset.qty;
      if(!physQtyInput.value) physQtyInput.value = option.dataset.qty;
      calcVariance(physQtyInput);
    } else {
      sysQtyInput.value = 0;
      physQtyInput.value = 0;
      calcVariance(physQtyInput);
    }
  }
  
  function calcVariance(input) {
    const row = input.closest('.item-row');
    const sys = parseInt(row.querySelector('.sys-qty').value) || 0;
    const phys = parseInt(row.querySelector('.phys-qty').value) || 0;
    const diff = phys - sys;
    const text = row.querySelector('.variance-text');
    
    if (diff > 0) {
      text.innerHTML = `Selisih: <span class="text-green-600">+${diff} (Kelebihan)</span>`;
    } else if (diff < 0) {
      text.innerHTML = `Selisih: <span class="text-red-600">${diff} (Kekurangan)</span>`;
    } else {
      text.innerHTML = `Selisih: <span class="text-gray-500 dark:text-gray-400">0 (Cocok)</span>`;
    }
  }

  document.getElementById('opnameForm').addEventListener('submit', function() {
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = 'Menyimpan...';
  });
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
