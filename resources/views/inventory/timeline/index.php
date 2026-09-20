<?php
$pageTitle = 'Riwayat Timeline';
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Page Guide terpusat (shared component + PageGuideHelper)
$guide = \App\Helpers\PageGuideHelper::forPath('/timeline');

ob_start();
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Riwayat Perjalanan Barang</h1>
    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Lacak riwayat lengkap mutasi Serial Number dan Stok Bulk.</p>
  </div>

  <?php if (isset($_SESSION['success'])): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
      <span class="block sm:inline"><?= htmlspecialchars($_SESSION['success']) ?></span>
      <?php unset($_SESSION['success']); ?>
    </div>
  <?php endif; ?>
  <?php if (isset($_SESSION['error'])): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
      <span class="block sm:inline"><?= htmlspecialchars($_SESSION['error']) ?></span>
      <?php unset($_SESSION['error']); ?>
    </div>
  <?php endif; ?>

  <?php
  // Level 1: Pencarian (Shared Toolbar)
  $toolbarAction = url('/timeline');
  $searchPlaceholder ="Masukkan SN / Kode Barang / Nama Barang...";
  require __DIR__ . '/../../components/table/toolbar.php';
  ?>

  <!-- Hasil Pencarian -->
  <?php if (empty($results)): ?>
    <?php
    $emptyPanel = true;
    $icon = 'fa-search';
    $emptyMessage ="Data Tidak Ditemukan";
    $emptySubMessage ="Tidak ada barang yang cocok dengan kriteria pencarian.";
    require __DIR__ . '/../../components/table/empty_state.php';
    unset($emptyPanel, $icon, $emptyMessage, $emptySubMessage);
    ?>
  <?php else: ?>
  <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Hasil Pencarian:"<?= htmlspecialchars($search ?? '') ?>"</h2>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full min-w-[800px] lg:min-w-full table-sticky mobile-card-table divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700">
          <tr>
            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-12">No</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tipe</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Identitas / SN</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Barang</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Lokasi / Holder</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
          <?php 
          $no = ($offset ?? 0) + 1;
          foreach ($results as $row): 
          ?>
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
              <td data-label="No" class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400"><?= $no++ ?></td>
              <td data-label="Tipe" class="px-4 py-3 whitespace-nowrap text-sm">
                <?php if ($row['type'] === 'SN'): ?>
                  <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                    SERIALIZED
                  </span>
                <?php else: ?>
                  <?= \App\Helpers\UIHelper::badge('BULK', 'INFO') ?>
                <?php endif; ?>
              </td>
              <td data-label="Identitas / SN" class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                <?= htmlspecialchars($row['identifier']) ?>
              </td>
              <td data-label="Barang" class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                <div class="font-medium text-gray-800 dark:text-gray-200"><?= htmlspecialchars($row['item_name']) ?></div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Qty: <?= number_format($row['quantity']) ?></div>
              </td>
              <td data-label="Lokasi / Holder" class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                <?php if ($row['location']): ?>
                  <div><i class="fas fa-building text-gray-400 w-4"></i> <?= htmlspecialchars($row['location']) ?></div>
                <?php endif; ?>
                <?php if ($row['holder']): ?>
                  <div><i class="fas fa-user text-gray-400 w-4"></i> <?= htmlspecialchars($row['holder']) ?></div>
                <?php endif; ?>
              </td>
              <td data-label="Status" class="px-4 py-3 whitespace-nowrap text-sm">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                  <?= htmlspecialchars(\App\Helpers\UIHelper::statusLabel($row['status'])) ?>
                </span>
              </td>
              <?php
              $rowActions = [['type' => 'view', 'url' => url('/timeline?type=' . $row['type'] . '&ref_id=' . $row['ref_id']), 'label' => 'Lihat Perjalanan', 'icon' => 'fa-route', 'title' => 'Lihat Timeline']];
              require __DIR__ . '/../../components/table/row_actions.php';
              ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php 
    $paginationBaseUrl = url('/timeline');
    require __DIR__ . '/../../components/table/pagination.php'; 
    ?>
  </div>
  <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
