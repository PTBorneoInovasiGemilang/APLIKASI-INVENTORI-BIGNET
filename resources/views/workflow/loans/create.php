<?php
$pageTitle = 'Ajukan Peminjaman';
ob_start();

if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<div class="max-w-6xl mx-auto px-4 py-8">
  <div class="mb-7">
    <a href="<?= url('/loans') ?>" class="inline-flex items-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-700">
      ← Kembali
    </a>

    <div class="mt-4">
      <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Ajukan Peminjaman</h1>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
        Tentukan kebutuhan peminjaman. Stok fisik tidak berkurang sampai Admin benar-benar menyerahkan barang.
      </p>
    </div>
  </div>

  <?php if (isset($_SESSION['error'])): ?>
    <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-300">
      <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="<?= url('/loans/store') ?>" class="space-y-6">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

    <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
      <div class="flex items-center gap-3 mb-5">
        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-300">
          <i class="fas fa-clipboard-list"></i>
        </div>
        <div>
          <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Detail Peminjaman</h2>
          <p class="text-xs text-gray-500 dark:text-gray-400">Lengkapi informasi dasar sebelum memilih barang.</p>
        </div>
      </div>

      <div class="grid gap-5 md:grid-cols-2">
        <div>
          <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Gudang Asal</label>
          <select name="warehouse_id" required class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            <option value="">Pilih gudang</option>
            <?php foreach ($warehouses as $w): ?>
              <option value="<?= $w['id'] ?>"><?= htmlspecialchars($w['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Jenis Peminjaman</label>
          <select name="loan_type" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            <option value="INTERNAL">Internal</option>
            <option value="EXTERNAL">Eksternal</option>
          </select>
        </div>

        <?php if (!empty($submissions)): ?>
        <div>
          <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Terkait Pengajuan Barang <span class="font-normal text-gray-400">(opsional)</span></label>
          <select name="submission_id" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            <option value="">Tidak menggunakan pengajuan</option>
            <?php foreach ($submissions as $s): ?>
              <option value="<?= $s['id'] ?>">Pengajuan yang sudah disetujui</option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php endif; ?>

        <div>
          <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Tanggal Pengembalian</label>

          <div
            id="returnDateCard"
            class="group relative flex cursor-pointer items-center gap-3 rounded-xl border border-gray-300 bg-white px-4 py-3 transition hover:border-blue-400 hover:shadow-sm focus-within:border-blue-500 focus-within:ring-4 focus-within:ring-blue-500/10 dark:border-gray-600 dark:bg-gray-700"
          >
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-300">
              <i class="far fa-calendar-alt"></i>
            </div>

            <div class="min-w-0 flex-1">
              <div id="returnDateText" class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                Pilih tanggal pengembalian
              </div>
              <div id="returnDateHint" class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                Tentukan perkiraan kapan barang dikembalikan.
              </div>
            </div>

            <i class="fas fa-chevron-down text-xs text-gray-400"></i>

            <input
              type="date"
              id="return_date_native"
              name="due_date"
              min="<?= date('Y-m-d') ?>"
              class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
              aria-label="Tanggal Pengembalian"
            >
          </div>
        </div>

        <div class="md:col-span-2">
          <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Catatan / Keperluan</label>
          <textarea
            name="notes"
            rows="3"
            class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            placeholder="Contoh: dipinjam untuk kebutuhan pemasangan pelanggan."
          ></textarea>
        </div>
      </div>
    </section>

    <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
      <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-300">
            <i class="fas fa-box-open"></i>
          </div>
          <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Barang yang Dipinjam</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400">
              Pilih dari stok atau tulis barang manual/custom.
            </p>
          </div>
        </div>

        <button
          type="button"
          onclick="addRow()"
          class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700"
        >
          <i class="fas fa-plus"></i>
          Tambah Barang
        </button>
      </div>

      <div class="space-y-3" id="body"></div>
    </section>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
      <a
        href="<?= url('/loans') ?>"
        class="inline-flex items-center justify-center rounded-xl bg-gray-100 px-5 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
      >
        Batal
      </a>
      <button
        type="submit"
        class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700"
      >
        <i class="fas fa-paper-plane"></i>
        Ajukan Peminjaman
      </button>
    </div>
  </form>
</div>

<script>
const items = <?= json_encode($items, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) ?>;
let n = 0;

const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({
  '&':'&amp;',
  '<':'&lt;',
  '>':'&gt;',
  '"':'&quot;',
 "'":'&#39;'
}[c]));

const nativeDate = document.getElementById('return_date_native');
const dateCard = document.getElementById('returnDateCard');
const dateText = document.getElementById('returnDateText');
const dateHint = document.getElementById('returnDateHint');

function formatDateId(value) {
  if (!value) return '';
  const [y, m, d] = value.split('-');
  const months = [
    'Januari','Februari','Maret','April','Mei','Juni',
    'Juli','Agustus','September','Oktober','November','Desember'
  ];
  return `${Number(d)} ${months[Number(m)-1]} ${y}`;
}

nativeDate?.addEventListener('change', () => {
  if (!nativeDate.value) {
    dateText.textContent = 'Pilih tanggal pengembalian';
    dateHint.textContent = 'Tentukan perkiraan kapan barang dikembalikan.';
    return;
  }

  dateText.textContent = formatDateId(nativeDate.value);
  dateHint.textContent = 'Tanggal pengembalian yang diharapkan.';
});

dateCard?.addEventListener('click', () => {
  if (typeof nativeDate?.showPicker === 'function') {
    nativeDate.showPicker();
  }
});

function addRow() {
  const i = n++;
  const row = document.createElement('div');
  row.className = 'rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/40';

  const opts = items.map(x =>
    `<option value="${x.id}">${esc(x.nama)}</option>`
  ).join('');

  row.innerHTML = `
    <div class="grid gap-4 md:grid-cols-12 md:items-end">
      <div class="md:col-span-3">
        <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-300">Sumber Barang</label>
        <select
          name="source_types[${i}]"
          class="source w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
        >
          <option value="STOCK">Dari Stok</option>
          <option value="MANUAL">Manual / Custom</option>
        </select>
      </div>

      <div class="md:col-span-5">
        <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-300">Barang</label>
        <select
          name="item_ids[${i}]"
          class="item w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
        >
          <option value="">Pilih barang</option>
          ${opts}
        </select>

        <input
          name="manual_names[${i}]"
          class="manual hidden w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
          placeholder="Tulis nama barang..."
        >
      </div>

      <div class="md:col-span-2">
        <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-300">Jumlah Diminta</label>
        <input
          type="number"
          name="quantities[${i}]"
          value="1"
          min="1"
          class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-center text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
        >
      </div>

      <div class="md:col-span-2 flex justify-end">
        <button
          type="button"
          class="inline-flex items-center gap-2 rounded-xl border border-red-200 bg-white px-4 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-900/60 dark:bg-gray-800 dark:text-red-300 dark:hover:bg-red-950/30"
          onclick="this.closest('.rounded-2xl').remove()"
        >
          <i class="fas fa-trash-alt"></i>
          Hapus
        </button>
      </div>
    </div>
  `;

  document.getElementById('body').appendChild(row);

  const source = row.querySelector('.source');
  const item = row.querySelector('.item');
  const manual = row.querySelector('.manual');

  const sync = () => {
    const isManual = source.value === 'MANUAL';

    if (isManual) {
      item.classList.add('hidden');
      item.removeAttribute('required');

      manual.classList.remove('hidden');
      manual.setAttribute('required', 'required');
    } else {
      item.classList.remove('hidden');
      item.setAttribute('required', 'required');

      manual.classList.add('hidden');
      manual.removeAttribute('required');
    }
  };

  source.addEventListener('change', sync);
  sync();
}

addRow();
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
