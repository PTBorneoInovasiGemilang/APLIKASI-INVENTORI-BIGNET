<?php
$title = 'Pengajuan Barang';
$pageTitle = 'Pengajuan Barang';
ob_start();
?>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4 sm:mb-6">
  <div>
    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Daftar Pengajuan</h1>
    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Kelola permintaan dan pengajuan kebutuhan barang teknisi.</p>
  </div>
  <?php if (\App\Helpers\AuthHelper::hasRole('TEKNISI_NOC')): ?>
  <a href="<?php echo url('/submissions/create'); ?>" class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-4 rounded-xl shadow-sm text-sm transition-all w-full sm:w-auto">
    <i class="fas fa-plus text-xs"></i> Buat Pengajuan
  </a>
  <?php endif; ?>
</div>

<?php if (isset($_SESSION['success'])): ?>
  <div class="bg-green-100 dark:bg-green-900 border-l-4 border-green-500 text-green-700 dark:text-green-300 p-4 mb-6 shadow" role="alert">
    <p><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
  </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
  <div class="bg-red-100 dark:bg-red-900 border-l-4 border-red-500 text-red-700 dark:text-red-300 p-4 mb-6 shadow" role="alert">
    <p><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
  </div>
<?php endif; ?>

<?php
$toolbarAction = url('/submissions');
$searchPlaceholder ="Cari Kode Pengajuan...";
ob_start();
?>
<div>
  <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Status</label>
  <select name="status" class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-40 py-2 px-3 text-sm text-gray-700 dark:text-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
    <option value="">Semua Status</option>
    <option value="PENDING" <?php echo (isset($_GET['status']) && $_GET['status'] === 'PENDING') ? 'selected' : ''; ?>><?= \App\Helpers\UIHelper::statusLabel('PENDING') ?></option>
    <option value="APPROVED" <?php echo (isset($_GET['status']) && $_GET['status'] === 'APPROVED') ? 'selected' : ''; ?>><?= \App\Helpers\UIHelper::statusLabel('APPROVED') ?></option>
    <option value="PARTIAL_APPROVED" <?php echo (isset($_GET['status']) && $_GET['status'] === 'PARTIAL_APPROVED') ? 'selected' : ''; ?>><?= \App\Helpers\UIHelper::statusLabel('PARTIAL_APPROVED') ?></option>
    <option value="REJECTED" <?php echo (isset($_GET['status']) && $_GET['status'] === 'REJECTED') ? 'selected' : ''; ?>>Ditolak</option>
  </select>
</div>
<?php
$extraFilters = ob_get_clean();
require __DIR__ . '/../../components/table/toolbar.php';
?>

<div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
  <div class="overflow-x-auto">
    <table class="w-full min-w-[700px] lg:min-w-full table-sticky divide-y divide-gray-200 dark:divide-gray-700">
      <thead class="bg-gray-50 dark:bg-gray-700">
        <tr>
          <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-12">No</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kode Pengajuan</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Diajukan Oleh</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tipe</th>
          <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
          <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
        </tr>
      </thead>
      <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
        <?php if (empty($submissions)): ?>
        <?php 
        $colspan = 7;
        $emptyMessage ="Belum ada data pengajuan.";
        require __DIR__ . '/../../components/table/empty_state.php';
        ?>
        <?php else: ?>
          <?php 
          $no = ($offset ?? 0) + 1;
          foreach ($submissions as $sub): 
          ?>
          <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
            <td data-label="No" class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center"><?= $no++ ?></td>
            <td data-label="Kode" class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
              <?php echo htmlspecialchars($sub['submission_code']); ?>
            </td>
            <td data-label="Diajukan" class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
              <?php echo htmlspecialchars($sub['submitter_name']); ?>
            </td>
            <td data-label="Tipe" class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
              <span class="px-2 py-1 rounded-full text-xs font-bold bg-gray-200 text-gray-800 dark:bg-gray-600 dark:text-gray-200">
                <?php echo htmlspecialchars($sub['submission_type'] ?? 'STOCK'); ?>
              </span>
            </td>
            <td data-label="Status" class="px-4 py-3 whitespace-nowrap text-sm text-center">
              <?php echo \App\Helpers\UIHelper::statusBadge($sub['status']); ?>
            </td>
            <td data-label="Tanggal" class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
              <?php echo date('Y-m-d H:i', strtotime($sub['created_at'])); ?>
            </td>
            <?php
            $rowActions = [['type' => 'view', 'url' => url('/submissions/show?id=' . (int)$sub['id']), 'label' => 'Lihat', 'icon' => 'fa-eye']];
            if (\App\Helpers\AuthHelper::hasRole('TEKNISI_NOC') && $sub['status'] === 'PENDING') {
              $rowActions[] = ['type' => 'link', 'url' => url('/submissions/edit?id=' . (int)$sub['id']), 'label' => 'Edit', 'icon' => 'fa-edit'];
              $rowActions[] = [
                'type' => 'form',
                'action' => url('/submissions/delete'),
                'fields' => ['csrf_token' => $_SESSION['csrf_token'] ?? '', 'id' => (int)$sub['id']],
                'label' => 'Hapus',
                'icon' => 'fa-trash',
                'danger' => true,
                'confirm' => [
                  'title' => 'Hapus Pengajuan?',
                  'message' => 'Pengajuan ini akan dihapus permanen karena belum diproses Admin.',
                  'description' => 'Pengajuan yang sudah disetujui sebagian, disetujui, atau ditolak tidak dapat dihapus.',
                  'type' => 'danger',
                  'icon' => 'fa-trash',
                  'confirm' => 'Hapus',
                  'loading' => 'Menghapus...'
                ]
              ];
            }
            require __DIR__ . '/../../components/table/row_actions.php';
            ?>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  
  <?php 
  $paginationBaseUrl = url('/submissions');
  require __DIR__ . '/../../components/table/pagination.php'; 
  ?>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
