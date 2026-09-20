<?php
/**
 * Shared Evidence List.
 * Variabel yang diharapkan:
 *  $evidenceItems (array) — dari AttachmentHelper::forReference()
 *  $evidenceCanUpload (bool, optional) — tampilkan form upload
 *  $evidenceUploadAction (string, optional) — URL POST upload
 *  $evidenceEmptyMessage (string, optional)
 *  $evidenceTitle (string, optional)
 */
$evidenceTitle = $evidenceTitle ?? 'Bukti / Lampiran';
$evidenceEmptyMessage = $evidenceEmptyMessage ?? 'Belum ada bukti terlampir.';
$evidenceItems = $evidenceItems ?? [];
$evidenceCanUpload = $evidenceCanUpload ?? false;
$evidenceUploadAction = $evidenceUploadAction ?? '';
$evidenceReferenceId = $evidenceReferenceId ?? 0;
$currentUser = \App\Helpers\AuthHelper::user();
?>
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
  <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex flex-wrap items-center justify-between gap-2">
    <h3 class="text-sm font-semibold text-gray-800 dark:text-white uppercase tracking-wide">
      <i class="fas fa-paperclip mr-2 text-gray-400"></i> <?= htmlspecialchars($evidenceTitle) ?>
      <span class="ml-1 text-gray-400 font-normal">(<?= count($evidenceItems) ?>)</span>
    </h3>
    <?php if ($evidenceCanUpload && $evidenceUploadAction): ?>
      <button type="button" onclick="document.getElementById('evidenceUploadForm').classList.toggle('hidden')"
        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-xs font-semibold transition-colors">
        <i class="fas fa-upload"></i> Tambah Bukti
      </button>
    <?php endif; ?>
  </div>

  <?php if ($evidenceCanUpload && $evidenceUploadAction): ?>
  <form id="evidenceUploadForm" method="POST" action="<?= url($evidenceUploadAction) ?>" enctype="multipart/form-data" class="hidden px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
    <input type="hidden" name="id" value="<?= (int)$evidenceReferenceId ?>">
    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Pilih file (JPG/PNG/WEBP/PDF, maks 5MB)</label>
    <div class="flex flex-col sm:flex-row gap-2">
      <input type="file" name="evidence" accept=".jpg,.jpeg,.png,.webp,.pdf,image/jpeg,image/png,image/webp,application/pdf" required
        class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-3 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900/30 dark:file:text-blue-400">
      <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md text-sm font-semibold transition-colors whitespace-nowrap">
        <i class="fas fa-check mr-1"></i> Simpan Bukti
      </button>
    </div>
  </form>
  <?php endif; ?>

  <?php if (empty($evidenceItems)): ?>
    <div class="px-6 py-8">
      <?php
      $emptyBare = true;
      $emptyIcon = 'fa-paperclip';
      $emptyIconColor = 'text-gray-300 dark:text-gray-600';
      $emptyMessage = $evidenceEmptyMessage;
      $emptySubMessage = $evidenceEmptyMessage === 'Belum ada bukti terlampir.' ? 'Unggah bukti untuk melengkapi dokumentasi.' : '';
      require __DIR__ . '/table/empty_state.php';
      unset($emptyBare);
      ?>
    </div>
  <?php else: ?>
    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
      <?php foreach ($evidenceItems as $ev): ?>
        <?php $isImage = strpos($ev['mime_type'], 'image/') === 0; ?>
        <li class="px-4 py-3 flex items-start gap-4 hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-700/50 transition-colors">
          <div class="flex-shrink-0 w-16 h-16 rounded-md overflow-hidden bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
            <?php if ($isImage): ?>
              <img src="<?= url('/evidence/' . $ev['id']) ?>" alt="<?= htmlspecialchars($ev['original_name']) ?>" class="w-full h-full object-cover">
            <?php else: ?>
              <i class="fas fa-file-pdf text-2xl text-red-400"></i>
            <?php endif; ?>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-gray-900 dark:text-white truncate"><?= htmlspecialchars($ev['original_name']) ?></p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
              <?= htmlspecialchars($ev['mime_type']) ?> · <?= number_format($ev['file_size'] / 1024, 1) ?> KB
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
              <i class="fas fa-user mr-1"></i><?= htmlspecialchars($ev['uploader_name'] ?? 'System') ?>
              · <?= date('d M Y H:i', strtotime($ev['created_at'])) ?>
            </p>
            <div class="mt-2 flex items-center gap-3">
              <a href="<?= url('/evidence/' . $ev['id']) ?>" target="_blank" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline inline-flex items-center gap-1">
                <i class="fas fa-eye"></i> Buka
              </a>
              <?php if (\App\Helpers\AttachmentHelper::canDelete($ev, $currentUser)): ?>
                <form action="<?= url('/evidence/delete') ?>" method="POST" class="inline"
                    data-confirm="Hapus bukti ini? Data yang dihapus tidak dapat dikembalikan."
                    data-confirm-title="Hapus Bukti"
                    data-confirm-type="danger"
                    data-confirm-icon="fa-trash"
                    data-confirm-confirm="Hapus"
                    data-confirm-loading="Menghapus...">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                  <input type="hidden" name="id" value="<?= (int)$ev['id'] ?>">
                  <button type="submit" class="text-xs font-semibold text-red-600 dark:text-red-400 hover:underline inline-flex items-center gap-1">
                    <i class="fas fa-trash"></i> Hapus
                  </button>
                </form>
              <?php endif; ?>
            </div>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</div>
