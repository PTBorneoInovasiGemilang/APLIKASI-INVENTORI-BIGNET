<?php
$pageTitle = 'Ubah Master Barang';
ob_start();
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<div class="max-w-4xl mx-auto space-y-5">
  <div class="flex items-center gap-2">
    <a href="<?= url('/items') ?>" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400 transition-colors">
      <i class="fas fa-arrow-left text-[10px]"></i>
      <span>Kembali ke Master Barang</span>
    </a>
  </div>

  <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-800 overflow-hidden">
    <div class="p-5 sm:p-7 border-b border-slate-100 dark:border-slate-800">
      <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">Ubah Master Barang: <?= htmlspecialchars($item['nama']) ?></h1>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Perbarui informasi barang, kategori, satuan, dan foto referensi.</p>
    </div>

    <form action="<?= url('/items/update/' . $item['id']) ?>" method="POST" enctype="multipart/form-data" class="p-5 sm:p-7 space-y-5">
      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
          <label class="block text-xs font-bold uppercase tracking-wide text-slate-700 dark:text-slate-300 mb-1.5">Nama Barang <span class="text-red-500">*</span></label>
          <input type="text" name="nama" value="<?= htmlspecialchars($item['nama']) ?>" required class="w-full py-2.5 px-3.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
          <label class="block text-xs font-bold uppercase tracking-wide text-slate-700 dark:text-slate-300 mb-1.5">Kategori <span class="text-red-500">*</span></label>
          <select name="item_category_id" required class="w-full py-2.5 px-3.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $item['item_category_id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?><?= !$cat['is_active'] ? ' (Nonaktif)' : '' ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block text-xs font-bold uppercase tracking-wide text-slate-700 dark:text-slate-300 mb-1.5">Satuan <span class="text-red-500">*</span></label>
          <select name="unit_id" required class="w-full py-2.5 px-3.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <?php foreach ($units as $unit): ?>
              <option value="<?= $unit['id'] ?>" <?= $unit['id'] == $item['unit_id'] ? 'selected' : '' ?>><?= htmlspecialchars($unit['name']) ?><?= !$unit['is_active'] ? ' (Nonaktif)' : '' ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block text-xs font-bold uppercase tracking-wide text-slate-700 dark:text-slate-300 mb-1.5">Stok Minimum</label>
          <input type="number" name="stok_minimum" value="<?= (int)$item['stok_minimum'] ?>" min="0" required class="w-full py-2.5 px-3.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="md:col-span-2">
          <label class="block text-xs font-bold uppercase tracking-wide text-slate-700 dark:text-slate-300 mb-1.5">Tambah Foto Baru</label>
          <input type="file" name="photo" accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-slate-800 dark:file:text-slate-300">
        </div>
      </div>

      <div>
        <label class="block text-xs font-bold uppercase tracking-wide text-slate-700 dark:text-slate-300 mb-1.5">Deskripsi</label>
        <textarea name="deskripsi" rows="3" class="w-full py-2.5 px-3.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($item['deskripsi']) ?></textarea>
      </div>
      
      <div>
        <?php if (!empty($hasDownstream)): ?>
          <input type="hidden" name="is_serialized" value="<?= $item['is_serialized'] ? '1' : '0' ?>">
          <div class="flex items-center gap-2 p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200/80 dark:border-slate-700 text-xs">
            <i class="fas fa-lock text-slate-400"></i>
            <span class="font-bold text-slate-700 dark:text-slate-300">
              Tipe: <?= $item['is_serialized'] ? 'Barang Berseri (Serial)' : 'Non-Serial (Bulk)' ?>
            </span>
            <span class="text-slate-400 dark:text-slate-500">— Terkunci karena sudah memiliki transaksi keluar, peminjaman, transfer, atau instalasi.</span>
          </div>
        <?php else: ?>
          <label class="flex items-center cursor-pointer">
            <input type="checkbox" id="is_serialized_cb" name="is_serialized" value="1" <?= $item['is_serialized'] ? 'checked' : '' ?> onchange="toggleConversionSerials()" class="form-checkbox h-4 w-4 text-purple-600 border-slate-300 dark:border-slate-700 rounded">
            <span class="ml-2.5 text-xs font-bold text-slate-700 dark:text-slate-300">Barang Berseri (Lacak Nomor Seri unik per unit)</span>
          </label>

          <?php if (!(int)$item['is_serialized'] && !empty($currentStocks)): ?>
            <div id="conversion-serials-container" class="hidden mt-3 p-4 bg-purple-50/70 dark:bg-purple-950/30 border border-purple-200/80 dark:border-purple-800 rounded-2xl space-y-3">
              <div class="flex items-start gap-2.5 text-xs text-purple-900 dark:text-purple-200">
                <i class="fas fa-barcode mt-0.5 text-purple-600 dark:text-purple-400 text-sm shrink-0"></i>
                <div>
                  <p class="font-bold">Input Nomor Seri Stok Saat Ini</p>
                  <p class="text-[11px] text-purple-700 dark:text-purple-300 mt-0.5">
                    Barang ini sudah memiliki stok fisik dari Barang Masuk. Masukkan nomor seri untuk masing-masing unit stok (pisahkan dengan enter atau koma) agar stok dapat dilacak per nomor seri:
                  </p>
                </div>
              </div>

              <?php foreach ($currentStocks as $cs): ?>
                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-purple-100 dark:border-purple-900/50 space-y-1.5">
                  <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-slate-800 dark:text-slate-200">
                      <?= htmlspecialchars($cs['warehouse_name']) ?>
                    </label>
                    <span class="text-[11px] font-semibold text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-950/60 px-2 py-0.5 rounded-md">
                      Dibutuhkan: <?= number_format((int)$cs['quantity']) ?> Serial
                    </span>
                  </div>
                  <textarea name="conversion_serials[<?= (int)$cs['warehouse_id'] ?>]" data-needed="<?= (int)$cs['quantity'] ?>" data-wh="<?= htmlspecialchars($cs['warehouse_name']) ?>" rows="3" class="w-full py-2 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 font-mono focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="SN001&#10;SN002&#10;SN003..." oninput="updateConversionSnCount(this, <?= (int)$cs['quantity'] ?>, 'sn-count-<?= (int)$cs['warehouse_id'] ?>')"></textarea>
                  <div id="sn-count-<?= (int)$cs['warehouse_id'] ?>" class="text-[11px] font-semibold text-slate-400">
                    SN Terisi: 0 / <?= (int)$cs['quantity'] ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php elseif ((int)$item['is_serialized']): ?>
            <p id="serial-uncheck-warning" class="hidden text-[11px] text-amber-600 dark:text-amber-400 mt-1.5">
              <i class="fas fa-triangle-exclamation mr-1"></i> Perhatian: Menonaktifkan serial akan menghapus seluruh data nomor seri yang tersimpan di gudang dan mengubahnya menjadi stok curah (bulk).
            </p>
          <?php endif; ?>
        <?php endif; ?>
      </div>

      <div>
        <label class="block text-xs font-bold uppercase tracking-wide text-slate-700 dark:text-slate-300 mb-1.5">Status Barang</label>
        <div class="flex gap-4">
          <label class="flex items-center cursor-pointer">
            <input type="radio" name="is_active" value="1" <?= $item['is_active'] ? 'checked' : '' ?> class="form-radio h-4 w-4 text-blue-600">
            <span class="ml-2 text-xs font-bold text-slate-700 dark:text-slate-300">Aktif</span>
          </label>
          <label class="flex items-center cursor-pointer">
            <input type="radio" name="is_active" value="0" <?= !$item['is_active'] ? 'checked' : '' ?> class="form-radio h-4 w-4 text-red-500">
            <span class="ml-2 text-xs font-bold text-slate-700 dark:text-slate-300">Nonaktif</span>
          </label>
        </div>
      </div>

      <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
        <a href="<?= url('/items') ?>" class="px-4 py-2.5 text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition-colors">Batal</a>
        <button type="submit" class="px-6 py-2.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-sm transition-colors">Simpan Perubahan</button>
      </div>
    </form>

    <?php if (!empty($attachments)): ?>
    <div class="p-5 sm:p-7 border-t border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/30">
      <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100 mb-3">Kelola Foto Existing</h3>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 sm:gap-4">
        <?php foreach ($attachments as $att): ?>
          <div class="rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 flex flex-col shadow-sm">
            <img src="<?= url('/file?path='.urlencode($att['file_path'])) ?>" class="w-full h-32 sm:h-36 object-cover">
            
            <div class="p-2.5 flex-1 flex flex-col justify-between border-t border-slate-100 dark:border-slate-700">
              <div class="text-[11px] text-slate-500 dark:text-slate-400 mb-2 truncate" title="<?= htmlspecialchars($att['original_name']) ?>">
                <?= htmlspecialchars($att['original_name']) ?>
              </div>
              
              <div class="flex gap-1.5 justify-between">
                <!-- Form Ganti -->
                <form action="<?= url('/items/photo/replace/' . $att['id']) ?>" method="POST" enctype="multipart/form-data" class="flex-1 flex" onsubmit="return confirm('Apakah Anda yakin ingin mengganti foto ini?');">
                  <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                  <label class="cursor-pointer bg-blue-50 text-blue-700 hover:bg-blue-100 dark:bg-blue-950/40 dark:text-blue-300 text-[11px] py-1.5 px-2 rounded-lg font-medium text-center flex-1 transition-colors border border-blue-200 dark:border-blue-800">
                    <i class="fas fa-sync-alt mr-1"></i> Ganti
                    <input type="file" name="photo" accept="image/*" class="hidden" onchange="this.form.submit()">
                  </label>
                </form>
                
                <!-- Form Hapus -->
                <form action="<?= url('/items/photo/delete/' . $att['id']) ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus foto ini? File akan dihapus permanen.');">
                  <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                  <button type="submit" class="bg-red-50 text-red-700 hover:bg-red-100 dark:bg-red-950/40 dark:text-red-300 text-[11px] py-1.5 px-2.5 rounded-lg font-medium transition-colors border border-red-200 dark:border-red-800" title="Hapus Foto">
                    <i class="fas fa-trash-alt"></i>
                  </button>
                </form>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
function toggleConversionSerials() {
  const cb = document.getElementById('is_serialized_cb');
  const box = document.getElementById('conversion-serials-container');
  const warn = document.getElementById('serial-uncheck-warning');
  if (box && cb) {
    if (cb.checked) {
      box.classList.remove('hidden');
    } else {
      box.classList.add('hidden');
    }
  }
  if (warn && cb) {
    if (!cb.checked) {
      warn.classList.remove('hidden');
    } else {
      warn.classList.add('hidden');
    }
  }
}

function updateConversionSnCount(textarea, needed, counterId) {
  const counter = document.getElementById(counterId);
  if (!counter) return;
  const val = textarea.value.trim();
  const count = val ? val.split(/[\r\n,]+/).map(s => s.trim()).filter(Boolean).length : 0;
  counter.textContent = 'SN Terisi: ' + count + ' / ' + needed;
  if (count === needed) {
    counter.className = 'text-[11px] font-semibold text-emerald-600 dark:text-emerald-400';
  } else if (count > needed) {
    counter.className = 'text-[11px] font-semibold text-red-500';
  } else {
    counter.className = 'text-[11px] font-semibold text-amber-600 dark:text-amber-400';
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const editForm = document.querySelector('form[action*="/items/update"]');
  if (editForm) {
    editForm.addEventListener('submit', function(e) {
      const cb = document.getElementById('is_serialized_cb');
      const box = document.getElementById('conversion-serials-container');
      if (cb && cb.checked && box && !box.classList.contains('hidden')) {
        const textareas = box.querySelectorAll('textarea[name^="conversion_serials"]');
        for (const ta of textareas) {
          const needed = parseInt(ta.getAttribute('data-needed') || '0', 10);
          const wh = ta.getAttribute('data-wh') || 'Gudang';
          const val = ta.value.trim();
          const count = val ? val.split(/[\r\n,]+/).map(s => s.trim()).filter(Boolean).length : 0;
          if (count !== needed) {
            e.preventDefault();
            alert('Jumlah nomor seri untuk ' + wh + ' harus tepat ' + needed + ' unit. Saat ini terisi: ' + count);
            ta.focus();
            return;
          }
        }
      }
    });
  }
});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
