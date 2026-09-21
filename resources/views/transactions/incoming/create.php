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
  
  <form action="<?php echo url('/incoming/store'); ?>" method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
      <div>
        <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Gudang Tujuan <span class="text-red-500">*</span></label>
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
      <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Foto Bukti Barang Masuk (Opsional)</label>
      <input type="file" name="evidence" accept="image/jpeg, image/png, image/webp, application/pdf" class="block w-full text-sm text-gray-500 dark:text-gray-400
        file:mr-4 file:py-2 file:px-4
        file:rounded file:border-0
        file:text-sm file:font-semibold
        file:bg-blue-50 file:text-blue-700
        hover:file:bg-blue-100 dark:file:bg-gray-700 dark:file:text-gray-300">
      <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Format: JPG, PNG, WEBP, PDF. Maksimal 5MB.</p>
    </div>

    <div class="mb-6 border-t border-gray-200 dark:border-gray-700 pt-4">
      <div class="flex justify-between items-center mb-2">
        <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold">Daftar Barang Masuk</label>
        <button type="button" onclick="addItemRow()" class="bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200 text-gray-800 text-sm font-semibold py-1 px-3 rounded transition-colors">
          <i class="fas fa-plus"></i> Tambah Baris
        </button>
      </div>
      
      <div id="duplicate-warning" class="hidden mb-4 p-3 bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-400 border border-red-200 dark:border-red-800 rounded text-sm">
        <i class="fas fa-exclamation-triangle mr-2"></i> Terdapat jenis barang yang sama lebih dari satu baris. Harap gabungkan kuantitas untuk satu jenis barang.
      </div>

      <div class="overflow-x-auto overflow-y-visible pb-12">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border border-gray-200 dark:border-gray-700" id="itemsTable">
          <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
              <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase w-1/3">Barang <span class="text-red-500">*</span></th>
              <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase w-24">Kuantitas <span class="text-red-500">*</span></th>
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
      <button type="submit" id="submitBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow transition-colors">Proses Barang Masuk</button>
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
    
    let dropdownHtml = `<div class="relative w-full custom-select">
      <input type="hidden" name="item_ids[]" class="item-id-hidden" required onchange="checkDuplicates()">
      <input type="text" class="item-search shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-full py-2 px-3 text-gray-700 dark:text-white dark:bg-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ketik untuk mencari barang..." onfocus="showDropdown(this, ${rowIndex})" onkeyup="filterDropdown(this, ${rowIndex})" autocomplete="off" required>
      <div id="dropdown-${rowIndex}" class="item-dropdown hidden absolute z-50 w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 max-h-48 overflow-y-auto mt-1 rounded shadow-lg text-sm text-gray-700 dark:text-gray-200">
      </div>
    </div>`;
    
    tr.innerHTML = `
      <td class="px-4 py-3 align-top">${dropdownHtml}</td>
      <td class="px-4 py-3 align-top">
        <input type="number" name="quantities[]" min="1" value="1" required class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-full py-2 px-3 text-gray-700 dark:text-white dark:bg-gray-700 text-center focus:outline-none focus:ring-2 focus:ring-blue-500 qty-input" onchange="validateQty(this, ${rowIndex})" onkeyup="validateQty(this, ${rowIndex})">
      </td>
      <td class="px-4 py-3 align-top">
        <div id="sn-container-${rowIndex}" class="hidden">
          <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Masukkan Serial Number (pisahkan dengan enter atau koma). Bisa scan barcode atau copy-paste dari CSV/Excel.</p>
          <textarea name="sn_list[]" id="sn-input-${rowIndex}" rows="3" class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-full py-2 px-3 text-gray-700 dark:text-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 sn-input" placeholder="SN001\nSN002\nSN003..." onkeyup="validateSnCount(${rowIndex})" onchange="validateSnCount(${rowIndex})"></textarea>
          <div id="sn-counter-${rowIndex}" class="text-xs font-bold text-red-500 mt-1">SN Terisi: 0 / 1</div>
        </div>
        <div id="sn-disabled-${rowIndex}" class="text-sm text-gray-500 dark:text-gray-400 italic py-2">
          Bukan barang serial.
        </div>
      </td>
      <td class="px-4 py-3 text-center align-top">
        <button type="button" onclick="removeRow(this)" class="text-red-500 hover:text-red-700 mt-1"><i class="fas fa-trash"></i></button>
      </td>
    `;
    
    tbody.appendChild(tr);
    populateDropdown(rowIndex, '');
  }
  
  function removeRow(btn) {
      btn.closest('tr').remove();
      checkDuplicates();
  }

  function populateDropdown(rowIndex, filterText) {
    const dropdown = document.getElementById(`dropdown-${rowIndex}`);
    if (!dropdown) return;
    
    const term = filterText.toLowerCase();
    dropdown.innerHTML = '';
    
    const filtered = itemsData.filter(i => 
      i.nama.toLowerCase().includes(term) || 
      (i.kode_barang && i.kode_barang.toLowerCase().includes(term))
    );
    
    if (filtered.length === 0) {
      dropdown.innerHTML = '<div class="px-3 py-2 text-gray-500 italic">Tidak ditemukan</div>';
      return;
    }
    
    filtered.slice(0, 50).forEach(item => {
      const div = document.createElement('div');
      div.className = 'px-3 py-2 cursor-pointer hover:bg-blue-50 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-700 last:border-0';
      div.innerHTML = item.nama;
      div.onclick = function() { selectItem(rowIndex, item); };
      dropdown.appendChild(div);
    });
  }

  function showDropdown(inputElem, rowIndex) {
    document.querySelectorAll('.item-dropdown').forEach(el => el.classList.add('hidden'));
    document.getElementById(`dropdown-${rowIndex}`).classList.remove('hidden');
    populateDropdown(rowIndex, inputElem.value);
  }

  function filterDropdown(inputElem, rowIndex) {
    populateDropdown(rowIndex, inputElem.value);
    
    // Clear hidden id if user starts typing again
    const tr = inputElem.closest('tr');
    tr.querySelector('.item-id-hidden').value = '';
    
    const snContainer = document.getElementById(`sn-container-${rowIndex}`);
    const snDisabled = document.getElementById(`sn-disabled-${rowIndex}`);
    const snInput = document.getElementById(`sn-input-${rowIndex}`);
    
    snContainer.classList.add('hidden');
    snDisabled.classList.remove('hidden');
    snInput.removeAttribute('required');
    snInput.value = '';
    checkDuplicates();
  }

  function selectItem(rowIndex, item) {
    const tr = document.querySelector(`tr[data-index="${rowIndex}"]`);
    const inputSearch = tr.querySelector('.item-search');
    const inputHidden = tr.querySelector('.item-id-hidden');
    
    inputSearch.value = item.nama;
    inputHidden.value = item.id;
    document.getElementById(`dropdown-${rowIndex}`).classList.add('hidden');
    
    const isSerialized = item.is_serialized == '1';
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
    
    // Trigger validation
    inputSearch.setCustomValidity('');
    checkDuplicates();
  }

  // Close dropdowns on outside click
  document.addEventListener('click', function(e) {
    if (!e.target.closest('.custom-select')) {
      document.querySelectorAll('.item-dropdown').forEach(el => el.classList.add('hidden'));
    }
  });

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
    
    // Check for duplicate SN within this field
    const uniqueSns = new Set(snArray);
    const hasDuplicates = uniqueSns.size !== actualQty;
    
    counter.innerText = `SN Terisi: ${actualQty} / ${expectedQty}`;
    
    if (hasDuplicates) {
      counter.className ="text-xs font-bold text-red-500 mt-1";
      counter.innerText +=" (Ada Duplikat!)";
      snInput.setCustomValidity("Ada Serial Number duplikat pada input ini.");
    } else if (actualQty === expectedQty) {
      counter.className ="text-xs font-bold text-green-500 mt-1";
      snInput.setCustomValidity("");
    } else if (actualQty > expectedQty) {
      counter.className ="text-xs font-bold text-red-500 mt-1";
      counter.innerText +=" (Kelebihan!)";
      snInput.setCustomValidity("Jumlah SN melebihi kuantitas.");
    } else {
      counter.className ="text-xs font-bold text-orange-500 mt-1";
      counter.innerText +=" (Kurang!)";
      if (snInput.hasAttribute('required')) {
        snInput.setCustomValidity("Jumlah SN belum sesuai kuantitas.");
      } else {
        snInput.setCustomValidity("");
      }
    }
  }
  
  function checkDuplicates() {
    const inputs = document.querySelectorAll('.item-id-hidden');
    const warning = document.getElementById('duplicate-warning');
    const submitBtn = document.getElementById('submitBtn');
    
    let values = [];
    let hasDup = false;
    
    inputs.forEach(input => {
      input.closest('tr').querySelector('.item-search').classList.remove('border-red-500', 'ring-red-500');
      
      const val = input.value;
      if (val) {
        if (values.includes(val)) {
          hasDup = true;
          input.closest('tr').querySelector('.item-search').classList.add('border-red-500', 'ring-red-500');
        } else {
          values.push(val);
        }
      }
    });
    
    if (hasDup) {
      warning.classList.remove('hidden');
      submitBtn.disabled = true;
      submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
    } else {
      warning.classList.add('hidden');
      submitBtn.disabled = false;
      submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    }
    
    return !hasDup;
  }
  
  function validateForm() {
    const inputs = document.querySelectorAll('.item-id-hidden');
    if (inputs.length === 0) {
      alert("Harap tambahkan minimal satu barang.");
      return false;
    }
    
    let allFilled = true;
    inputs.forEach(input => {
      if (!input.value) {
        allFilled = false;
        input.closest('tr').querySelector('.item-search').classList.add('border-red-500');
      }
    });
    
    if (!allFilled) {
      alert("Pastikan semua baris barang telah dipilih dari daftar (bukan hanya diketik).");
      return false;
    }
    
    return checkDuplicates();
  }

  // Add first row on load
  addItemRow();
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
