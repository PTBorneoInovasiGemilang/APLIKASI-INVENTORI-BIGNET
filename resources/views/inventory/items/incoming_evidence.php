<?php
$pageTitle = 'Riwayat Foto Barang Masuk';
ob_start();
$warehouseLabel = $selectedWarehouseName ?: 'Semua Gudang';
$photoTotal = (int)$photoCount;
$incomingTotal = (int)$incomingCount;
?>

<div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
  <div class="flex items-center gap-4">
    <a href="<?= url('/items/show/' . (int)$item['id'] . (($warehouse_id ?? null) ? '?warehouse_id=' . (int)$warehouse_id : '')) ?>" class="text-gray-500 dark:text-gray-400 hover:text-blue-600 transition-colors">
      <i class="fas fa-arrow-left text-xl"></i>
    </a>
    <div>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Riwayat Foto Barang Masuk</h1>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        <?= htmlspecialchars($item['nama']) ?> · <?= htmlspecialchars($item['kode_barang']) ?> · <?= htmlspecialchars($warehouseLabel) ?>
      </p>
    </div>
  </div>

  <div class="flex items-center gap-2">
    <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
      <i class="fas fa-box"></i> <?= $incomingTotal ?> transaksi masuk
    </span>
    <span class="inline-flex items-center gap-1.5 rounded-full bg-green-50 px-3 py-1.5 text-xs font-semibold text-green-700 dark:bg-green-900/30 dark:text-green-300">
      <i class="fas fa-camera"></i> <?= $photoTotal ?> foto
    </span>
  </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
  <div class="xl:col-span-1 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
    <h2 class="text-sm font-bold text-gray-800 dark:text-white uppercase tracking-wide mb-3">
      <i class="fas fa-box text-blue-500 mr-2"></i> Foto Master Barang
    </h2>
    <?php if (empty($masterPhotos)): ?>
      <div class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">
        <i class="fas fa-image text-2xl mb-2"></i>
        <p>Belum ada foto master barang.</p>
      </div>
    <?php else: ?>
      <div class="grid grid-cols-2 gap-3">
        <?php foreach ($masterPhotos as $photo): ?>
          <a href="<?= url('/file?path=' . urlencode($photo['file_path'])) ?>" data-image-preview data-image-title="<?= htmlspecialchars($photo['original_name']) ?>" class="group block overflow-hidden rounded-lg border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900">
            <img src="<?= url('/file?path=' . urlencode($photo['file_path'])) ?>" alt="<?= htmlspecialchars($photo['original_name']) ?>" class="h-32 w-full object-cover transition-transform duration-200 group-hover:scale-105">
            <div class="p-2 text-[11px] text-gray-500 dark:text-gray-400 truncate"><?= htmlspecialchars($photo['original_name']) ?></div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <div class="xl:col-span-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
    <h2 class="text-sm font-bold text-gray-800 dark:text-white uppercase tracking-wide mb-3">
      <i class="fas fa-info-circle text-gray-400 mr-2"></i> Identitas Barang
    </h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
      <div>
        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Nama Barang</div>
        <div class="mt-1 font-semibold text-gray-900 dark:text-white"><?= htmlspecialchars($item['nama']) ?></div>
      </div>
      <div>
        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Kode</div>
        <div class="mt-1 font-mono text-gray-900 dark:text-white"><?= htmlspecialchars($item['kode_barang']) ?></div>
      </div>
      <div>
        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Gudang</div>
        <div class="mt-1 text-gray-900 dark:text-white"><?= htmlspecialchars($warehouseLabel) ?></div>
      </div>
      <div>
        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Kategori</div>
        <div class="mt-1 text-gray-900 dark:text-white"><?= htmlspecialchars($item['category_name'] ?? '-') ?></div>
      </div>
    </div>
  </div>
</div>

<div class="space-y-6">
  <?php if (empty($incomingEvidence)): ?>
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-10 text-center">
      <i class="fas fa-camera text-4xl text-gray-300 dark:text-gray-600 mb-3"></i>
      <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Belum ada foto Barang Masuk</h2>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Belum ada transaksi Barang Masuk dengan bukti foto untuk barang ini di gudang yang dipilih.</p>
    </div>
  <?php else: ?>
    <?php foreach ($incomingEvidence as $entry): ?>
      <section class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <div class="flex items-center gap-2">
              <h2 class="font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($entry['transaction_code']) ?></h2>
              <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-700 dark:bg-blue-900/30 dark:text-blue-300"><?= count($entry['photos']) ?> foto</span>
            </div>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
              <?= date('d M Y H:i', strtotime($entry['created_at'])) ?> · <?= htmlspecialchars($entry['warehouse_name']) ?>
              <?php if (!empty($entry['supplier_info'])): ?> · Pemasok: <?= htmlspecialchars($entry['supplier_info']) ?><?php endif; ?>
            </p>
          </div>
          <a href="<?= url('/incoming/show?id=' . (int)$entry['incoming_id']) ?>" class="inline-flex items-center gap-1.5 text-sm font-semibold text-blue-600 dark:text-blue-400 hover:underline">
            <i class="fas fa-arrow-up-right-from-square"></i> Lihat Transaksi
          </a>
        </div>

        <?php if (empty($entry['photos'])): ?>
          <div class="p-6 text-sm text-gray-500 dark:text-gray-400">
            <i class="fas fa-image mr-2"></i> Transaksi ini belum memiliki foto bukti.
          </div>
        <?php else: ?>
          <div class="p-5 grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
            <?php foreach ($entry['photos'] as $photo): ?>
              <?php $isImage = strpos($photo['mime_type'] ?? '', 'image/') === 0; ?>
              <div class="rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                <?php if ($isImage): ?>
                  <a href="<?= url('/evidence/' . (int)$photo['id']) ?>" data-image-preview data-image-title="<?= htmlspecialchars($photo['original_name']) ?>" class="block group">
                    <img src="<?= url('/evidence/' . (int)$photo['id']) ?>" alt="<?= htmlspecialchars($photo['original_name']) ?>" class="h-40 w-full object-cover transition-transform duration-200 group-hover:scale-105">
                  </a>
                <?php else: ?>
                  <a href="<?= url('/evidence/' . (int)$photo['id']) ?>" target="_blank" class="h-40 flex items-center justify-center text-red-400">
                    <i class="fas fa-file-pdf text-4xl"></i>
                  </a>
                <?php endif; ?>
                <div class="p-3">
                  <div class="text-xs font-semibold text-gray-800 dark:text-gray-200 truncate" title="<?= htmlspecialchars($photo['original_name']) ?>"><?= htmlspecialchars($photo['original_name']) ?></div>
                  <div class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                    <?= number_format($photo['file_size'] / 1024, 1) ?> KB · <?= htmlspecialchars($photo['uploader_name'] ?? 'System') ?>
                  </div>
                  <a href="<?= url('/evidence/' . (int)$photo['id']) ?>" data-image-preview data-image-title="<?= htmlspecialchars($photo['original_name']) ?>" class="mt-2 inline-flex items-center gap-1 text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                    <i class="fas fa-eye"></i> Buka
                  </a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>