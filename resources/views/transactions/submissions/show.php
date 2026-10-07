<?php
$title = 'Detail Pengajuan Barang';
$pageTitle = 'Detail Pengajuan Barang';
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
ob_start();
?>

<div class="mb-6">
  <a href="<?php echo url('/submissions'); ?>" class="text-blue-600 dark:text-blue-400 hover:underline"><i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar Pengajuan</a>
</div>

<div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6 border border-gray-200 dark:border-gray-700">
  <div class="flex justify-between items-start mb-4">
    <div>
      <h2 class="text-xl font-bold text-gray-800 dark:text-white"><?php echo htmlspecialchars($submission['submission_code']); ?> <span class="text-sm font-normal text-gray-500 dark:text-gray-400">(<?php echo htmlspecialchars(\App\Helpers\UIHelper::statusLabel($submission['submission_type'])); ?>)</span></h2>
      <p class="text-sm text-gray-500 dark:text-gray-400">Dibuat pada: <?php echo date('Y-m-d H:i', strtotime($submission['created_at'])); ?> oleh <?php echo htmlspecialchars($submission['submitter_name']); ?></p>
    </div>
    <div class="flex flex-col sm:flex-row items-end sm:items-center gap-3">
      <?php echo \App\Helpers\UIHelper::statusBadge($submission['status'], 'px-3 py-1 text-sm'); ?>
      <?php if ($submission['status'] === 'PENDING' && \App\Helpers\AuthHelper::hasRole('TEKNISI_NOC') && (int)$submission['submitted_by'] === (int)\App\Helpers\AuthHelper::user()['id']): ?>
        <a href="<?php echo url('/submissions/edit?id=' . (int)$submission['id']); ?>" class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-bold py-2 px-4 rounded shadow transition-colors">
          <i class="fas fa-edit"></i> Edit Pengajuan
        </a>
      <?php endif; ?>
      <?php if ($submission['status'] === 'PENDING' && \App\Helpers\AuthHelper::hasRole('TEKNISI_NOC') && (int)$submission['submitted_by'] === (int)\App\Helpers\AuthHelper::user()['id']): ?>
        <form method="POST" action="<?php echo url('/submissions/delete'); ?>" class="inline-block" data-confirm="Pengajuan ini akan dihapus permanen karena belum diproses Admin." data-confirm-title="Hapus Pengajuan?" data-confirm-type="danger" data-confirm-icon="fa-trash" data-confirm-confirm="Hapus" data-confirm-loading="Menghapus...">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
          <input type="hidden" name="id" value="<?php echo (int)$submission['id']; ?>">
          <button type="submit" class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white text-sm font-bold py-2 px-4 rounded shadow transition-colors">
            <i class="fas fa-trash"></i> Hapus
          </button>
        </form>
      <?php endif; ?>
      <?php if (in_array($submission['status'], ['APPROVED', 'PARTIAL_APPROVED']) && \App\Helpers\AuthHelper::hasRole('ADMIN')): ?>
        <a href="<?php echo url('/outgoing/create?submission_id=' . (int)$submission['id']); ?>" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold py-2 px-4 rounded shadow transition-colors">
          <i class="fas fa-truck-loading"></i> Proses Barang Keluar
        </a>
      <?php endif; ?>
    </div>
  </div>
  
  <div class="mb-4">
    <h4 class="font-semibold text-gray-700 dark:text-gray-300">Catatan / Alasan:</h4>
    <div class="text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 p-4 rounded mt-2 text-sm">
      <?php 
        $notesData = json_decode($submission['notes'], true);
        if (is_array($notesData) && json_last_error() === JSON_ERROR_NONE) {
          echo '<ul class="list-disc pl-5 space-y-1">';
          foreach ($notesData as $k => $v) {
            echo '<li><strong>' . htmlspecialchars($k) . ':</strong> ' . htmlspecialchars($v ?: '-') . '</li>';
          }
          echo '</ul>';
        } else {
          echo nl2br(htmlspecialchars($submission['notes'] ?: 'Tidak ada catatan.'));
        }
      ?>
    </div>
  </div>

  <?php if ($submission['approved_by']): ?>
  <div class="mb-4">
    <h4 class="font-semibold text-gray-700 dark:text-gray-300">Detail Persetujuan:</h4>
    <p class="text-sm text-gray-600 dark:text-gray-400">Diproses oleh <?php echo htmlspecialchars($submission['approver_name']); ?> pada <?php echo date('Y-m-d H:i', strtotime($submission['approved_at'])); ?></p>
    <?php if ($submission['approval_notes']): ?>
      <p class="text-gray-600 dark:text-gray-400 bg-blue-50 dark:bg-blue-900 p-3 rounded mt-1"><?php echo nl2br(htmlspecialchars($submission['approval_notes'])); ?></p>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>

<div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
  <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Daftar Barang</h3>
    <?php if ($submission['submission_type'] === 'MANUAL' && $submission['status'] === 'PENDING' && \App\Helpers\AuthHelper::hasRole(['ADMIN'])): ?>
      <button type="button" onclick="addMappingRow()" class="bg-blue-100 hover:bg-blue-200 text-blue-800 text-sm font-semibold py-1 px-3 rounded transition-colors">
        <i class="fas fa-plus"></i> Tambah Mapping Barang
      </button>
    <?php endif; ?>
  </div>
  
  <form action="<?php echo url('/submissions/approve'); ?>" method="POST" id="approveForm">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="id" value="<?php echo $submission['id']; ?>">
    
    <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" id="mappingTable">
      <thead class="bg-gray-50 dark:bg-gray-700">
        <tr>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Barang</th>
          <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Sumber</th>
          <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Diminta</th>
          <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Disetujui</th>
          <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-48">Status / Alasan Tolak</th>
        </tr>
      </thead>
      <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
        <?php foreach ($items as $item): ?>
        <tr>
          <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
            <?php if ($item['source_type'] === 'MANUAL'): ?>
              <div class="font-bold text-yellow-600 dark:text-yellow-400"><?= \App\Helpers\UIHelper::badge('Barang Manual', 'WARNING') ?> <?php echo htmlspecialchars($item['manual_name']); ?></div>
              <?php if ($item['manual_spec']): ?>
                <div class="text-xs text-gray-500 dark:text-gray-400">Spec: <?php echo htmlspecialchars($item['manual_spec']); ?></div>
              <?php endif; ?>
              <?php if ($submission['status'] === 'PENDING' && \App\Helpers\AuthHelper::hasRole(['ADMIN'])): ?>
                <div class="mt-2">
                  <label class="block text-xs font-bold text-gray-600 dark:text-gray-400 mb-1">Mapping Master Barang:</label>
                  <select name="mapped_item_ids[<?php echo $item['id']; ?>]" class="shadow border rounded w-full py-1 px-2 text-sm focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">-- Pilih Jika Disetujui --</option>
                    <?php foreach ($master_items as $mi): ?>
                      <option value="<?php echo $mi['id']; ?>" <?php echo $item['master_item_id'] == $mi['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($mi['nama']); ?><?php if (trim((string)($mi['kode_barang'] ?? '')) !== ''): ?> (<?php echo htmlspecialchars($mi['kode_barang']); ?>)<?php endif; ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              <?php endif; ?>
            <?php else: ?>
              <div class="font-medium"><?php echo htmlspecialchars($item['nama']); ?></div>
              <div class="text-gray-500 dark:text-gray-400 text-xs"><?php echo htmlspecialchars($item['kode_barang']); ?></div>
              <?php
                $sinfo = $itemStockInfo[$item['id']] ?? null;
                if ($sinfo && $sinfo['warehouse_name']):
              ?>
                <div class="mt-1.5 text-xs text-gray-600 dark:text-gray-300">
                  <i class="fas fa-warehouse mr-1 text-gray-400"></i>Gudang Pemenuhan: <strong><?php echo htmlspecialchars($sinfo['warehouse_name']); ?></strong>
                  <?php if ($sinfo['available'] !== null): ?>
                    · Stok tersedia: <strong class="<?php echo ($sinfo['available'] > 0) ? 'text-green-600 dark:text-green-400' : 'text-red-500'; ?>"><?php echo $sinfo['available']; ?> Pcs</strong>
                  <?php endif; ?>
                </div>
              <?php elseif ($sinfo): ?>
                <div class="mt-1.5 text-xs text-red-500"><i class="fas fa-triangle-exclamation mr-1"></i>Gudang Pemenuhan belum ditentukan (data pengajuan lama).</div>
              <?php endif; ?>
            <?php endif; ?>
            
            <?php if (!empty($item['notes'])): ?>
              <div class="mt-1 text-xs text-blue-600 dark:text-blue-400 italic"><i class="fas fa-info-circle mr-1"></i><?php echo htmlspecialchars($item['notes']); ?></div>
            <?php endif; ?>
          </td>
          <td class="px-4 py-3 text-sm text-center text-gray-500 dark:text-gray-400">
            <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs font-semibold"><?php echo htmlspecialchars(\App\Helpers\UIHelper::statusLabel($item['source_type'])); ?></span>
          </td>
          <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-300 text-center font-bold text-lg">
            <?php echo $item['requested_quantity']; ?> <?php echo $item['source_type'] === 'MANUAL' ? htmlspecialchars($item['manual_unit']) : ''; ?>
          </td>
          <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
            <?php if ($submission['status'] === 'PENDING' && \App\Helpers\AuthHelper::hasRole(['ADMIN'])): ?>
              <input type="number" name="approved_quantities[<?php echo $item['id']; ?>]" value="<?php echo $item['requested_quantity']; ?>" max="<?php echo $item['requested_quantity']; ?>" min="0" class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-20 py-1 px-2 text-center text-gray-700 dark:text-white dark:bg-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
            <?php else: ?>
              <span class="font-bold text-blue-600 dark:text-blue-400 text-lg"><?php echo $item['approved_quantity'] ?? 0; ?></span>
              <div class="text-xs mt-1 text-gray-500 dark:text-gray-400">Dipenuhi: <?php echo $item['fulfilled_quantity']; ?></div>
              <div class="text-xs text-gray-500 dark:text-gray-400">Sisa: <?php echo $item['remaining_quantity']; ?></div>
            <?php endif; ?>
          </td>
          <td class="px-4 py-3 text-sm">
            <?php if ($submission['status'] === 'PENDING' && \App\Helpers\AuthHelper::hasRole(['ADMIN'])): ?>
              <input type="text" name="rejection_reasons[<?php echo $item['id']; ?>]" placeholder="Alasan jika QTY=0" class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-full py-1 px-2 text-xs text-gray-700 dark:text-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <?php else: ?>
              <div class="text-center mb-1"><?php echo \App\Helpers\UIHelper::statusBadge($item['status']); ?></div>
              <?php if ($item['rejection_reason']): ?>
                <div class="text-xs text-red-500 bg-red-50 dark:bg-red-900/30 p-1 rounded text-center">
                  <?php echo htmlspecialchars($item['rejection_reason']); ?>
                </div>
              <?php endif; ?>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($items)): ?>
          <?php $colspan = 5; $emptyMessage ="Belum ada barang dalam pengajuan ini."; require __DIR__ . '/../../components/table/empty_state.php'; ?>
        <?php endif; ?>
      </tbody>
    </table>
    </div>

    <?php if ($submission['status'] === 'PENDING' && \App\Helpers\AuthHelper::hasRole(['ADMIN'])): ?>
    <div class="p-6 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
      <h4 class="font-semibold text-gray-700 dark:text-gray-300 mb-2">Proses Persetujuan</h4>
      <textarea name="approval_notes" placeholder="Alasan jika menolak semua (Wajib jika klik Tolak Semua)" rows="2" class="shadow appearance-none border border-red-300 dark:border-red-600 rounded w-full py-2 px-3 text-gray-700 dark:text-white dark:bg-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-red-500 mb-4"></textarea>
      
      <div class="flex gap-4">
        <button type="submit" name="action" value="APPROVE" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded shadow transition-colors"
              data-confirm="Proses persetujuan sesuai Kuantitas Disetujui?"
              data-confirm-title="Proses Persetujuan"
              data-confirm-type="warning"
              data-confirm-icon="fa-check"
              data-confirm-confirm="Proses Item"
              data-confirm-loading="Memproses...">
          <i class="fas fa-check mr-2"></i> Proses Item
        </button>
        <button type="submit" name="action" value="REJECT_ALL" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded shadow transition-colors" formnovalidate
              data-confirm="Tolak SELURUH pengajuan ini? Pastikan field Alasan terisi."
              data-confirm-title="Tolak Semua"
              data-confirm-type="danger"
              data-confirm-icon="fa-times"
              data-confirm-confirm="Tolak Semua"
              data-confirm-loading="Memproses...">
          <i class="fas fa-times mr-2"></i> Tolak Semua
        </button>
      </div>
    </div>
    <?php endif; ?>
  </form>
  </div>


<?php if (\App\Helpers\AuthHelper::hasRole('ADMIN') && in_array($submission['status'], ['APPROVED', 'PARTIAL_APPROVED'], true)): ?>
<div class="space-y-6 mb-6">

  <!-- Foto referensi setiap barang yang disetujui -->
  <div class="bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-4 sm:px-6 py-4 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center justify-between gap-3">
        <div>
          <h3 class="text-lg font-bold text-gray-800 dark:text-white">
            <i class="fas fa-box-open mr-2 text-blue-500"></i>Barang yang Diberikan
          </h3>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            Foto barang dan detail jumlah yang disetujui/dipenuhi.
          </p>
        </div>
        <?php if (!empty($submission['approved_at'])): ?>
          <span class="text-xs text-gray-500 dark:text-gray-400 text-right">
            Persetujuan: <strong><?= date('d M Y H:i', strtotime($submission['approved_at'])) ?></strong>
          </span>
        <?php endif; ?>
      </div>
    </div>

    <div class="p-4 sm:p-6 space-y-4">
      <?php foreach ($items as $it):
        $photos = $it['_photos'] ?? [];
        $approvedQty = (int)($it['approved_quantity'] ?? 0);
        $fulfilledQty = (int)($it['fulfilled_quantity'] ?? 0);
        $remainingQty = max(0, $approvedQty - $fulfilledQty);
        $itemName = ($it['source_type'] ?? '') === 'MANUAL'
          ? ($it['manual_name'] ?? ($it['nama'] ?? 'Barang Manual'))
          : ($it['nama'] ?? 'Barang');
        $itemCode = $it['kode_barang'] ?? '';
      ?>
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
          <div class="p-4 flex flex-col lg:flex-row lg:items-start gap-4">
            <div class="w-full lg:w-44 shrink-0">
              <?php if (!empty($photos)): ?>
                <div class="grid grid-cols-2 gap-2">
                  <?php foreach (array_slice($photos, 0, 3) as $photo): ?>
                    <a href="<?= url('/file?path=' . urlencode($photo['file_path'])) ?>" target="_blank"
                       class="block aspect-square rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                      <img src="<?= url('/file?path=' . urlencode($photo['file_path'])) ?>"
                           alt="<?= htmlspecialchars($itemName) ?>"
                           class="w-full h-full object-cover">
                    </a>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <div class="aspect-[4/3] rounded-lg border border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 flex flex-col items-center justify-center text-center px-3">
                  <i class="fas fa-image text-2xl text-gray-300 dark:text-gray-600"></i>
                  <span class="text-xs text-gray-400 dark:text-gray-500 mt-2">Belum ada foto</span>
                </div>
              <?php endif; ?>
            </div>

            <div class="flex-1 min-w-0">
              <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
                <div>
                  <h4 class="font-bold text-gray-900 dark:text-white break-words"><?= htmlspecialchars($itemName) ?></h4>
                  <?php if ($itemCode !== ''): ?>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-mono mt-0.5"><?= htmlspecialchars($itemCode) ?></p>
                  <?php endif; ?>
                  <?php if (($it['source_type'] ?? '') === 'MANUAL'): ?>
                    <span class="inline-flex mt-2 px-2 py-1 rounded-md bg-yellow-100 text-yellow-800 text-xs font-semibold">Manual / Custom</span>
                  <?php endif; ?>
                </div>
                <span class="inline-flex w-fit px-2.5 py-1 rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-xs font-bold">
                  <?= $approvedQty ?> disetujui
                </span>
              </div>

              <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mt-4">
                <div class="rounded-lg bg-gray-50 dark:bg-gray-900/50 p-3">
                  <div class="text-[11px] text-gray-500 dark:text-gray-400">Diminta</div>
                  <div class="text-base font-bold text-gray-900 dark:text-white mt-0.5"><?= (int)$it['requested_quantity'] ?></div>
                </div>
                <div class="rounded-lg bg-gray-50 dark:bg-gray-900/50 p-3">
                  <div class="text-[11px] text-gray-500 dark:text-gray-400">Dipenuhi</div>
                  <div class="text-base font-bold text-green-600 dark:text-green-400 mt-0.5"><?= $fulfilledQty ?></div>
                </div>
                <div class="rounded-lg bg-gray-50 dark:bg-gray-900/50 p-3 col-span-2 sm:col-span-1">
                  <div class="text-[11px] text-gray-500 dark:text-gray-400">Sisa</div>
                  <div class="text-base font-bold text-gray-900 dark:text-white mt-0.5"><?= $remainingQty ?></div>
                </div>
              </div>

              <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700 text-xs text-gray-500 dark:text-gray-400">
                <div class="flex flex-wrap gap-x-5 gap-y-1">
                  <?php if (!empty($submission['approved_at'])): ?>
                    <span><i class="fas fa-check-circle mr-1 text-green-500"></i>Disetujui: <strong><?= date('d M Y H:i', strtotime($submission['approved_at'])) ?></strong></span>
                  <?php endif; ?>
                  <?php if (!empty($photos)): ?>
                    <span><i class="fas fa-image mr-1 text-blue-500"></i><?= count($photos) ?> foto referensi</span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>

      <?php if (empty($items)): ?>
        <div class="text-center text-sm text-gray-500 dark:text-gray-400 py-6">Tidak ada item pada pengajuan.</div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Bukti aktual saat barang dikeluarkan -->
  <div class="bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-4 sm:px-6 py-4 border-b border-gray-200 dark:border-gray-700">
      <h3 class="text-lg font-bold text-gray-800 dark:text-white">
        <i class="fas fa-camera mr-2 text-green-500"></i>Dokumentasi Barang Keluar
      </h3>
      <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
        Foto bukti aktual saat barang diberikan oleh gudang, lengkap dengan tanggal dan jam.
      </p>
    </div>

    <div class="p-4 sm:p-6 space-y-5">
      <?php if (empty($fulfillments)): ?>
        <div class="rounded-xl border border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900/40 px-4 py-8 text-center">
          <i class="fas fa-clock text-xl text-gray-300 dark:text-gray-600"></i>
          <p class="mt-2 text-sm font-semibold text-gray-500 dark:text-gray-400">Belum ada Barang Keluar</p>
          <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Foto bukti akan muncul setelah Admin memproses pemenuhan pengajuan.</p>
        </div>
      <?php else: ?>
        <?php foreach ($fulfillments as $fulfillment):
          $outId = (int)$fulfillment['id'];
          $evs = $fulfillmentPhotos[$outId] ?? [];
          $fItems = $fulfillmentItems[$outId] ?? [];
        ?>
          <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-4 bg-gray-50 dark:bg-gray-900/50">
              <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3">
                <div>
                  <div class="font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($fulfillment['transaction_code']) ?></div>
                  <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    <i class="fas fa-calendar mr-1"></i><?= date('d M Y', strtotime($fulfillment['created_at'])) ?>
                    <i class="fas fa-clock ml-3 mr-1"></i><strong><?= date('H:i:s', strtotime($fulfillment['created_at'])) ?></strong>
                  </div>
                  <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Gudang: <strong><?= htmlspecialchars($fulfillment['warehouse_name'] ?? '-') ?></strong>
                    · Diproses oleh: <strong><?= htmlspecialchars($fulfillment['processor_name'] ?? '-') ?></strong>
                  </div>
                </div>
                <a href="<?= url('/outgoing/show?id=' . $outId) ?>" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 text-xs font-semibold">
                  <i class="fas fa-external-link-alt"></i> Detail Barang Keluar
                </a>
              </div>

              <?php if (!empty($fItems)): ?>
                <div class="mt-3 flex flex-wrap gap-2">
                  <?php foreach ($fItems as $fi): ?>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-xs text-gray-700 dark:text-gray-300">
                      <?= htmlspecialchars($fi['nama'] ?? 'Barang') ?> × <?= (int)$fi['quantity'] ?>
                    </span>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>

            <div class="p-4">
              <?php if (empty($evs)): ?>
                <div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-600 px-4 py-6 text-center">
                  <i class="fas fa-camera-slash text-gray-300 dark:text-gray-600"></i>
                  <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Belum ada foto bukti pada transaksi ini.</p>
                </div>
              <?php else: ?>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                  <?php foreach ($evs as $ev): ?>
                    <?php $isImage = strpos($ev['mime_type'] ?? '', 'image/') === 0; ?>
                    <?php if (!$isImage) continue; ?>
                    <div class="rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                      <a href="<?= url('/evidence/' . (int)$ev['id']) ?>" target="_blank" class="block aspect-[4/3] bg-gray-100 dark:bg-gray-900">
                        <img src="<?= url('/evidence/' . (int)$ev['id']) ?>"
                             alt="<?= htmlspecialchars($ev['original_name'] ?? 'Foto bukti') ?>"
                             class="w-full h-full object-cover">
                      </a>
                      <div class="p-2.5">
                        <p class="text-xs font-semibold text-gray-800 dark:text-white break-words"><?= htmlspecialchars($ev['original_name'] ?? 'Foto') ?></p>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                          Upload: <strong><?= date('d M Y H:i:s', strtotime($ev['created_at'])) ?></strong>
                        </p>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                          Oleh: <?= htmlspecialchars($ev['uploader_name'] ?? 'System') ?>
                        </p>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

</div>
<?php endif; ?>

<?php require __DIR__ . '/../../components/audit_timeline.php'; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
