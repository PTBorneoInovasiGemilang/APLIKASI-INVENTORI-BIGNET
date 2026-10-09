<?php
$pageTitle = 'Manajemen Pengguna';
ob_start();
?>
<div class="space-y-6">
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
      <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Manajemen Pengguna</h1>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Kelola akun pengguna sistem, peran hak akses, dan persetujuan pendaftaran.</p>
    </div>
    <a href="<?= url('/users/create') ?>" class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-4 rounded-xl shadow-sm text-xs transition-all w-full sm:w-auto">
      <i class="fas fa-plus text-xs"></i> Tambah Pengguna
    </a>
  </div>

  <?php if (isset($_SESSION['success'])): ?>
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-200 shadow-sm">
      <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
    </div>
  <?php endif; ?>
  <?php if (isset($_SESSION['error'])): ?>
    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-200 shadow-sm">
      <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
    </div>
  <?php endif; ?>

  <?php
  $toolbarAction = url('/users');
  $searchPlaceholder ="Cari Nama / Email...";
  ob_start();
  ?>
  <div>
    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Status Akun</label>
    <select name="status" class="shadow-sm appearance-none border border-slate-300 dark:border-slate-700 rounded-xl w-44 py-2 px-3 text-sm text-slate-700 dark:text-slate-200 dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
      <option value="">Semua Status</option>
      <option value="PENDING_APPROVAL" <?= (isset($_GET['status']) && $_GET['status'] === 'PENDING_APPROVAL') ? 'selected' : '' ?>><?= \App\Helpers\UIHelper::statusLabel('PENDING_APPROVAL') ?></option>
      <option value="APPROVED" <?= (isset($_GET['status']) && $_GET['status'] === 'APPROVED') ? 'selected' : '' ?>><?= \App\Helpers\UIHelper::statusLabel('APPROVED') ?></option>
      <option value="REJECTED" <?= (isset($_GET['status']) && $_GET['status'] === 'REJECTED') ? 'selected' : '' ?>><?= \App\Helpers\UIHelper::statusLabel('REJECTED') ?></option>
      <option value="SUSPENDED" <?= (isset($_GET['status']) && $_GET['status'] === 'SUSPENDED') ? 'selected' : '' ?>><?= \App\Helpers\UIHelper::statusLabel('SUSPENDED') ?></option>
    </select>
  </div>
  <?php
  $extraFilters = ob_get_clean();
  require __DIR__ . '/../components/table/toolbar.php';
  ?>

  <div class="bg-white dark:bg-slate-900 shadow-sm rounded-2xl overflow-hidden border border-slate-200/80 dark:border-slate-800">
    <div class="overflow-x-auto">
      <table class="w-full min-w-[760px] lg:min-w-full divide-y divide-slate-200/80 dark:divide-slate-800">
        <thead class="bg-slate-50 dark:bg-slate-800/60">
          <tr>
            <th class="px-4 py-3.5 text-center text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 w-12">No</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Pengguna</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Peran</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Status</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Terakhir Masuk</th>
            <th class="px-4 py-3.5 text-center text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 w-28">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
          <?php 
          $no = ($offset ?? 0) + 1;
          foreach ($users as $u): 
          ?>
          <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
            <td class="px-4 py-3.5 whitespace-nowrap text-xs text-center text-slate-400 font-semibold"><?= $no++ ?></td>
            <td class="px-4 py-3.5 whitespace-nowrap">
              <div class="flex items-center gap-3">
                <div class="shrink-0 h-9 w-9">
                  <?php if ($u['photo_path']): ?>
                    <img class="h-9 w-9 rounded-full object-cover ring-2 ring-slate-100 dark:ring-slate-800" src="<?= url('/file?path='.urlencode($u['photo_path'])) ?>" alt="">
                  <?php else: ?>
                    <div class="h-9 w-9 rounded-full bg-blue-100 dark:bg-blue-950/40 flex items-center justify-center text-blue-600 dark:text-blue-300 font-bold text-xs">
                      <?= strtoupper(substr($u['name'], 0, 1)) ?>
                    </div>
                  <?php endif; ?>
                </div>
                <div class="min-w-0">
                  <div class="text-sm font-bold text-slate-900 dark:text-slate-100"><?= htmlspecialchars($u['name']) ?></div>
                  <div class="text-xs text-slate-400"><?= htmlspecialchars($u['email']) ?></div>
                </div>
              </div>
            </td>
            <td class="px-4 py-3.5 whitespace-nowrap">
              <span class="px-2.5 py-0.5 inline-flex text-xs font-semibold rounded-full bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                <?= htmlspecialchars(\App\Helpers\UIHelper::roleLabel($u['role'])) ?>
              </span>
            </td>
            <td class="px-4 py-3.5 whitespace-nowrap">
              <?php if ($u['account_status'] === 'PENDING_APPROVAL'): ?>
                <?= \App\Helpers\UIHelper::badge('PENDING_APPROVAL', 'WARNING') ?>
              <?php elseif ($u['account_status'] === 'APPROVED' && $u['is_active']): ?>
                <?= \App\Helpers\UIHelper::badge('ACTIVE', 'SUCCESS') ?>
              <?php elseif ($u['account_status'] === 'REJECTED'): ?>
                <?= \App\Helpers\UIHelper::badge('REJECTED', 'DANGER', 'title="' . htmlspecialchars($u['rejection_reason'] ?? '') . '"') ?>
              <?php elseif ($u['account_status'] === 'SUSPENDED'): ?>
                <?= \App\Helpers\UIHelper::badge('SUSPENDED', 'NEUTRAL') ?>
              <?php else: ?>
                <?= \App\Helpers\UIHelper::badge('Nonaktif', 'DANGER') ?>
              <?php endif; ?>
            </td>
            <td class="px-4 py-3.5 whitespace-nowrap text-xs text-slate-400">
              <?= $u['last_login_at'] ? date('d M Y, H:i', strtotime($u['last_login_at'])) : 'Belum Pernah' ?>
            </td>
            <?php
            $rowActions = [];
            $rowActions[] = ['type' => 'view', 'url' => url('/users/show/' . (int)$u['id']), 'label' => 'Lihat', 'icon' => 'fa-eye'];
            $rowActions[] = ['type' => 'link', 'url' => url('/users/edit/' . (int)$u['id']), 'label' => 'Ubah', 'icon' => 'fa-edit'];
            if ($u['account_status'] === 'PENDING_APPROVAL') {
              $rowActions[] = [
                'type' => 'button', 'label' => 'Tinjau', 'icon' => 'fa-clipboard-check',
                'onclick' => "openApprovalModal(" . (int)$u['id'] . ", '" . htmlspecialchars($u['name'], ENT_QUOTES) . "', '" . ($u['photo_path'] ? url('/file?path=' . urlencode($u['photo_path'])) : '') . "')"
              ];
            }
            require __DIR__ . '/../components/table/row_actions.php';
            ?>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($users)): ?>
            <?php
            $colspan = 6;
            $emptyMessage ="Belum ada data user.";
            $emptySubMessage ="User baru akan muncul di sini setelah mendaftar atau ditambahkan.";
            require __DIR__ . '/../components/table/empty_state.php';
            ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <?php 
    $paginationBaseUrl = url('/users');
    require __DIR__ . '/../components/table/pagination.php'; 
    ?>
  </div>
</div>

<!-- Approval Modal -->
<div id="approvalModal" class="fixed z-50 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
  <div class="flex items-center justify-center min-h-screen p-4 text-center">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeApprovalModal()" aria-hidden="true"></div>
    <div class="relative bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-2xl border border-slate-200 dark:border-slate-800 max-w-lg w-full p-6 fade-in">
      <div class="sm:flex sm:items-start mb-4">
        <div id="approvePhotoContainer" class="mx-auto flex-shrink-0 flex items-center justify-center h-16 w-16 rounded-full bg-slate-100 dark:bg-slate-800 sm:mx-0 sm:h-20 sm:w-20 overflow-hidden hidden ring-2 ring-slate-100 dark:ring-slate-700">
          <img id="approveUserPhoto" src="" alt="Foto Profil" class="h-20 w-20 object-cover">
        </div>
        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
          <h3 class="text-base font-bold text-slate-900 dark:text-slate-100" id="modal-title">
            Tinjau Pendaftaran: <span id="approveUserName" class="text-blue-600 dark:text-blue-400"></span>
          </h3>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Harap verifikasi identitas pengguna sebelum menyetujui atau menolak akses.</p>
        </div>
      </div>
      
      <div class="mt-5 space-y-4">
        <form action="<?= url('/users/approve') ?>" method="POST">
          <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
          <input type="hidden" name="id" id="approveUserId">
          <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-xl py-2.5 px-4 bg-emerald-600 text-sm font-semibold text-white hover:bg-emerald-700 shadow-sm transition-colors">
            <i class="fas fa-check"></i> Setujui (Approve)
          </button>
        </form>

        <div class="border-t border-slate-100 dark:border-slate-800 pt-4">
          <form action="<?= url('/users/reject') ?>" method="POST" class="space-y-3">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="id" id="rejectUserId">
            <div>
              <label class="block text-xs font-bold uppercase tracking-wide text-slate-700 dark:text-slate-300 mb-1">Alasan Penolakan <span class="text-red-500">*</span></label>
              <textarea name="rejection_reason" required rows="2" class="w-full py-2 px-3 text-xs bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="Wajib diisi untuk penolakan..."></textarea>
            </div>
            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-xl py-2.5 px-4 bg-red-600 text-sm font-semibold text-white hover:bg-red-700 shadow-sm transition-colors">
              <i class="fas fa-times"></i> Tolak (Reject)
            </button>
          </form>
        </div>
      </div>

      <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-end">
        <button type="button" onclick="closeApprovalModal()" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
          Tutup
        </button>
      </div>
    </div>
  </div>
</div>

<script>
function openApprovalModal(id, name, photoUrl) {
  document.getElementById('approveUserId').value = id;
  document.getElementById('rejectUserId').value = id;
  document.getElementById('approveUserName').textContent = name;
  
  const photoContainer = document.getElementById('approvePhotoContainer');
  const photoImg = document.getElementById('approveUserPhoto');
  if (photoUrl) {
    photoImg.src = photoUrl;
    photoContainer.classList.remove('hidden');
  } else {
    photoImg.src = '';
    photoContainer.classList.add('hidden');
  }
  
  document.getElementById('approvalModal').classList.remove('hidden');
}

function closeApprovalModal() {
  document.getElementById('approvalModal').classList.add('hidden');
}
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
