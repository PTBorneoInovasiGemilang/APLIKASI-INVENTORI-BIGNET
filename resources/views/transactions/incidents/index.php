<?php
$title = 'Laporan Kerusakan & Kehilangan';
$pageTitle = 'Laporan Kerusakan & Kehilangan';
ob_start();
?>

<div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
  <div>
    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100"><?= $title ?></h1>
    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Daftar laporan barang rusak (Damage) atau hilang (Lost).</p>
  </div>
  <?php if (\App\Helpers\AuthHelper::hasRole(['ADMIN', 'TEKNISI_NOC'])): ?>
  <a href="<?= url('/incidents/create') ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
    </svg>
    Buat Laporan
  </a>
  <?php endif; ?>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
  <!-- Toolbar -->
  <?php 
  ob_start(); 
  ?>
  <div>
    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Status</label>
    <select name="status" class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-40 py-2 px-3 text-sm text-gray-700 dark:text-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
      <option value="">Semua Status</option>
      <option value="PENDING" <?php echo (isset($_GET['status']) && $_GET['status'] == 'PENDING') ? 'selected' : ''; ?>>Menunggu Review (Open)</option>
      <option value="RESOLVED" <?php echo (isset($_GET['status']) && $_GET['status'] == 'RESOLVED') ? 'selected' : ''; ?>>Selesai</option>
      <option value="REJECTED" <?php echo (isset($_GET['status']) && $_GET['status'] == 'REJECTED') ? 'selected' : ''; ?>>Ditolak</option>
    </select>
  </div>
  <?php
  $extraFilters = ob_get_clean();
  require __DIR__ . '/../../components/table/toolbar.php'; 
  ?>

  <div class="overflow-x-auto">
    <table class="w-full min-w-[800px] lg:min-w-full text-sm text-left text-gray-500 dark:text-gray-400">
      <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
        <tr>
          <th scope="col" class="px-4 py-3 w-16">No</th>
          <th scope="col" class="px-4 py-3">Kode / Tipe</th>
          <th scope="col" class="px-4 py-3">Tanggal / Gudang</th>
          <th scope="col" class="px-4 py-3">Pelapor</th>
          <th scope="col" class="px-4 py-3">Status</th>
          <th scope="col" class="px-4 py-3 text-center">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($incidents)): ?>
          <?php
          $colspan = 6;
          $emptyMessage = (isset($_GET['status']) && $_GET['status'] == 'PENDING') ?"Belum ada insiden terbuka." :"Belum ada laporan insiden.";
          require __DIR__ . '/../../components/table/empty_state.php';
          ?>
        <?php else: ?>
          <?php foreach ($incidents as $index => $incident): ?>
          <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
            <td class="px-4 py-3"><?= $offset + $index + 1 ?></td>
            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
              <?= htmlspecialchars($incident['transaction_code']) ?>
              <br>
              <?php if ($incident['incident_type'] === 'DAMAGE'): ?>
                <span class="bg-orange-100 text-orange-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-orange-900 dark:text-orange-300">Kerusakan</span>
              <?php else: ?>
                <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-red-900 dark:text-red-300">Kehilangan</span>
              <?php endif; ?>
            </td>
            <td class="px-4 py-3">
              <?= date('d M Y H:i', strtotime($incident['reported_date'])) ?>
              <br>
              <span class="text-xs text-gray-500 dark:text-gray-400"><?= $incident['warehouse_name'] ? htmlspecialchars($incident['warehouse_name']) : 'N/A' ?></span>
            </td>
            <td class="px-4 py-3">
              <?= htmlspecialchars($incident['reporter_name']) ?>
            </td>
            <td class="px-4 py-3">
              <?php 
                $statusClass = [
                  'PENDING' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
                  'INVESTIGATING' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
                  'RESOLVED' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                  'REJECTED' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
                ];
                $sc = $statusClass[$incident['status']] ?? 'bg-gray-100 text-gray-800';
              ?>
              <span class="px-2.5 py-0.5 rounded-full text-xs font-medium <?= $sc ?>">
                <?= htmlspecialchars(\App\Helpers\UIHelper::statusLabel($incident['status'])) ?>
              </span>
            </td>
            <td class="px-4 py-3 text-right">
              <?php
              $rowActions = [['type' => 'view', 'url' => url('/incidents/show?id=' . (int)$incident['id']), 'label' => 'Lihat', 'icon' => 'fa-eye']];
              require __DIR__ . '/../../components/table/row_actions.php';
              ?>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php require __DIR__ . '/../../components/table/pagination.php'; ?>
</div>

<!-- Page Guide -->
<div class="mt-8 bg-blue-50 dark:bg-blue-900/30 p-4 rounded-lg border border-blue-100 dark:border-blue-800">
  <h3 class="text-blue-800 dark:text-blue-300 font-medium flex items-center mb-2">
    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
    </svg>
    Panduan Damage/Lost
  </h3>
  <ul class="list-disc list-inside text-sm text-blue-700 dark:text-blue-400 space-y-1">
    <li>Laporan dibuat oleh Teknisi atau Superadmin ketika mendapati barang rusak atau hilang.</li>
    <li>Wajib melampirkan bukti fisik berupa foto dan penjelasan pemegang terakhir (holder).</li>
    <li>Insiden yang berstatus <strong>Selesai</strong> akan otomatis disesuaikan secara stok/serial.</li>
  </ul>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
