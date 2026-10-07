<?php
$title = 'Detail Laporan Insiden';
$pageTitle = 'Detail Laporan Insiden';
ob_start();
?>

<div class="mb-6 flex justify-between items-center">
  <div>
    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100"><?= $title ?></h1>
    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Detail laporan, investigasi, dan resolusi.</p>
  </div>
  <a href="<?= url('/incidents') ?>" class="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
    &larr; Kembali
  </a>
</div>

<?php if (isset($_SESSION['success'])): ?>
  <div class="mb-4 p-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
    <?= $_SESSION['success']; unset($_SESSION['success']); ?>
  </div>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
  <div class="mb-4 p-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
  </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <!-- Kolom Kiri: Detail -->
  <div class="lg:col-span-2 space-y-6">
    <!-- Info Insiden -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
      <div class="flex justify-between items-start mb-4 pb-4 border-b dark:border-gray-700">
        <div>
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white"><?= htmlspecialchars($incident['transaction_code']) ?></h2>
          <p class="text-sm text-gray-500 dark:text-gray-400">Dilaporkan oleh <?= htmlspecialchars($incident['reporter_name']) ?> pada <?= date('d M Y H:i', strtotime($incident['reported_date'])) ?></p>
        </div>
        <div>
          <?php 
            $statusClass = [
              'PENDING' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
              'INVESTIGATING' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
              'RESOLVED' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
              'REJECTED' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
            ];
            $sc = $statusClass[$incident['status']] ?? 'bg-gray-100 text-gray-800';
          ?>
          <span class="px-3 py-1 rounded-full text-sm font-medium <?= $sc ?>">
            <?= htmlspecialchars(\App\Helpers\UIHelper::statusLabel($incident['status'])) ?>
          </span>
        </div>
      </div>
      
      <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
          <span class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Tipe Insiden</span>
          <span class="text-sm font-semibold text-gray-900 dark:text-white"><?= htmlspecialchars(\App\Helpers\UIHelper::statusLabel($incident['incident_type'])) ?></span>
        </div>
        <div>
          <span class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Lokasi/Gudang</span>
          <span class="text-sm font-semibold text-gray-900 dark:text-white"><?= $incident['warehouse_name'] ? htmlspecialchars($incident['warehouse_name']) : 'N/A' ?></span>
        </div>
      </div>
      
      <?php if ($incident['notes']): ?>
      <div class="mb-4">
        <span class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-1">Keterangan Umum</span>
        <p class="text-sm text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700 p-3 rounded"><?= nl2br(htmlspecialchars($incident['notes'])) ?></p>
      </div>
      <?php endif; ?>
      
      <?php if ($incident['resolution']): ?>
      <div class="mb-4">
        <span class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-1">Resolusi / Hasil Investigasi</span>
        <p class="text-sm text-gray-700 dark:text-gray-300 bg-blue-50 dark:bg-blue-900/30 p-3 rounded border border-blue-100 dark:border-blue-800"><?= nl2br(htmlspecialchars($incident['resolution'])) ?></p>
      </div>
      <?php endif; ?>
    </div>

    <!-- Daftar Barang -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
      <div class="px-4 py-3 border-b dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Daftar Barang Dilaporkan</h3>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm text-left text-gray-500 dark:text-gray-400">
          <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
            <tr>
              <th class="px-4 py-3">Barang / Serial</th>
              <th class="px-4 py-3 text-center">Qty</th>
              <th class="px-4 py-3">Pemegang Terakhir</th>
              <th class="px-4 py-3">Alasan / Bukti</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $item): ?>
            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
              <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                <?= htmlspecialchars($item['kode_barang']) ?> - <?= htmlspecialchars($item['item_name']) ?>
                <?php if ($item['serial_number']): ?>
                  <br><span class="text-xs text-gray-500 dark:text-gray-400 font-mono"><?= htmlspecialchars($item['serial_number']) ?></span>
                <?php endif; ?>
              </td>
              <td class="px-4 py-3 text-center">
                <?= $item['quantity'] ?>
              </td>
              <td class="px-4 py-3">
                <?= htmlspecialchars($item['last_holder_name'] ?? '-') ?>
              </td>
              <td class="px-4 py-3">
                <p class="text-xs mb-2"><?= htmlspecialchars($item['reason']) ?></p>
                <?php if ($item['evidence_path']): ?>
                  <a href="<?= url('/file?path=' . urlencode($item['evidence_path'])) ?>" data-image-preview data-image-title="Foto Bukti Insiden" class="inline-flex items-center text-xs font-medium text-blue-600 hover:underline">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Lihat Foto Bukti
                  </a>
                <?php else: ?>
                  <span class="text-xs text-gray-400 dark:text-gray-500">Tidak ada bukti</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($items)): ?>
              <?php $colspan = 4; $emptyMessage ="Belum ada item dalam laporan insiden ini."; require __DIR__ . '/../../components/table/empty_state.php'; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Kolom Kanan: Timeline & Aksi -->
  <div class="space-y-6">
    
    <?php if (($incident['status'] === 'PENDING' || $incident['status'] === 'INVESTIGATING') && \App\Helpers\AuthHelper::hasRole(['ADMIN'])): ?>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Proses Investigasi</h3>
      <form action="<?= url('/incidents/process') ?>" method="POST" id="processForm">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
        <input type="hidden" name="incident_id" value="<?= $incident['id'] ?>">
        
        <div class="mb-4">
          <label for="action" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Keputusan/Status Baru</label>
          <select name="action" id="action" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
            <option value="">Pilih Aksi...</option>
            <?php if ($incident['status'] === 'PENDING'): ?>
            <option value="INVESTIGATING">Mulai Investigasi (INVESTIGATING)</option>
            <?php endif; ?>
            <option value="RESOLVED">Terbukti & Selesai (RESOLVED)</option>
            <option value="REJECTED">Tolak Laporan</option>
          </select>
        </div>
        
        <div class="mb-4">
          <label for="resolution" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Catatan Resolusi</label>
          <textarea name="resolution" id="resolution" required rows="3" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="Alasan keputusan diambil..."></textarea>
        </div>
        
        <div class="bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 p-3 rounded mb-4 text-xs text-yellow-800 dark:text-yellow-300">
          <strong>Perhatian:</strong> Memilih <strong>Selesai</strong> akan secara otomatis memotong stok barang secara permanen atau merubah status Serial Number menjadi <?= htmlspecialchars(\App\Helpers\UIHelper::statusLabel($incident['incident_type'])) ?>!
        </div>
        
        <button type="submit" class="w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
              data-confirm="Apakah Anda yakin dengan keputusan ini? Tindakan ini akan mengubah status laporan dan memengaruhi stok/serial terkait."
              data-confirm-title="Simpan Keputusan"
              data-confirm-type="warning"
              data-confirm-icon="fa-gavel"
              data-confirm-confirm="Simpan"
              data-confirm-loading="Menyimpan...">
          Simpan Keputusan
        </button>
      </form>
    </div>
    <?php endif; ?>

    <!-- Bukti / Lampiran -->
    <div class="space-y-6">
      <?php
      $evidenceItems = $attachments;
      $evidenceCanUpload = \App\Helpers\AuthHelper::hasRole(['ADMIN']);
      $evidenceUploadAction = '/incidents/evidence/upload';
      $evidenceReferenceId = (int)$incident['id'];
      $evidenceTitle = 'Bukti Tambahan';
      $evidenceEmptyMessage = 'Belum ada bukti tambahan.';
      require __DIR__ . '/../../components/evidence_list.php';
      unset($evidenceItems, $evidenceCanUpload, $evidenceUploadAction, $evidenceReferenceId, $evidenceTitle, $evidenceEmptyMessage);
      ?>
    </div>

    <!-- Timeline Log -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Audit Log & Timeline</h3>
      <ol class="relative border-l border-gray-200 dark:border-gray-700 ml-3">
        <?php foreach ($timeline as $log): ?>
        <li class="mb-6 ml-6">
          <span class="absolute flex items-center justify-center w-6 h-6 bg-blue-100 rounded-full -left-3 ring-8 ring-white dark:ring-gray-800 dark:bg-blue-900">
            <svg class="w-3 h-3 text-blue-800 dark:text-blue-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path></svg>
          </span>
          <h3 class="flex items-center mb-1 text-sm font-semibold text-gray-900 dark:text-white"><?= htmlspecialchars($log['action']) ?></h3>
          <time class="block mb-2 text-xs font-normal leading-none text-gray-400 dark:text-gray-500"><?= date('d M Y H:i', strtotime($log['created_at'])) ?> - <?= htmlspecialchars($log['user_name']) ?></time>
          <p class="text-sm font-normal text-gray-500 dark:text-gray-400"><?= htmlspecialchars($log['description']) ?></p>
        </li>
        <?php endforeach; ?>
      </ol>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
