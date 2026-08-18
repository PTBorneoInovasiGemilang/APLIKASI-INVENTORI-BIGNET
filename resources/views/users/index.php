<?php
$pageTitle = 'Manajemen Pengguna';
ob_start();
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Users</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Manage system users, roles, and access.</p>
        </div>
        <a href="<?= url('/users/create') ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md shadow-sm text-sm font-medium transition-colors">
            <i class="fas fa-plus mr-2"></i> Add User
        </a>
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

    <!-- Panel Panduan Approval User -->
    <div class="bg-blue-50 dark:bg-blue-900/30 border-l-4 border-blue-500 p-4 rounded-md text-sm text-blue-700 dark:text-blue-300 mb-6 shadow-sm">
        <p class="font-bold mb-1"><i class="fas fa-info-circle mr-1"></i> Panduan Approval & Manajemen User</p>
        <ul class="list-disc pl-5 space-y-1">
            <li>User baru yang mendaftar secara publik akan berstatus <strong>Menunggu Persetujuan</strong>.</li>
            <li>Klik tombol <strong>Tinjau</strong> untuk menyetujui (Approve) atau menolak (Reject) pendaftaran.</li>
            <li>Jika menolak pendaftaran, Anda <strong>wajib</strong> mengisi alasan penolakan.</li>
            <li>Hanya user dengan status <strong>Aktif</strong> dan <strong>Disetujui</strong> yang dapat login ke sistem.</li>
            <li>Superadmin tidak bisa dibuat melalui pendaftaran publik.</li>
        </ul>
    </div>

    <?php
    $toolbarAction = url('/users');
    $searchPlaceholder = "Cari Nama / Email...";
    ob_start();
    ?>
    <div>
        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Status</label>
        <select name="status" class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-44 py-2 px-3 text-sm text-gray-700 dark:text-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
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

    <div class="bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-12">No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">User</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Role</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Last Login</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <?php 
                    $no = ($offset ?? 0) + 1;
                    foreach ($users as $u): 
                    ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400"><?= $no++ ?></td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <?php if ($u['photo_path']): ?>
                                        <img class="h-10 w-10 rounded-full object-cover" src="<?= url('/file?path='.urlencode($u['photo_path'])) ?>" alt="">
                                    <?php else: ?>
                                        <div class="h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center text-blue-600 dark:text-blue-300 font-bold">
                                            <?= strtoupper(substr($u['name'], 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100"><?= htmlspecialchars($u['name']) ?></div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars($u['email']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                <?= htmlspecialchars(\App\Helpers\UIHelper::roleLabel($u['role'])) ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
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
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            <?= $u['last_login_at'] ? date('d M Y H:i', strtotime($u['last_login_at'])) : 'Belum Pernah' ?>
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
                        $emptyMessage = "Belum ada data user.";
                        $emptySubMessage = "User baru akan muncul di sini setelah mendaftar atau ditambahkan.";
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
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start mb-4">
                    <div id="approvePhotoContainer" class="mx-auto flex-shrink-0 flex items-center justify-center h-16 w-16 rounded-full bg-gray-100 dark:bg-gray-700 sm:mx-0 sm:h-20 sm:w-20 overflow-hidden hidden">
                        <img id="approveUserPhoto" src="" alt="Foto Profil" class="h-20 w-20 object-cover">
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title">
                            Tinjau Pendaftaran: <span id="approveUserName" class="font-bold"></span>
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Harap verifikasi identitas pengguna sebelum menyetujui atau menolak akses.</p>
                    </div>
                </div>
                
                <div class="mt-4 flex gap-4">
                    <form action="<?= url('/users/approve') ?>" method="POST" class="flex-1">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="id" id="approveUserId">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none sm:text-sm">
                            Setujui (Approve)
                        </button>
                    </form>
                </div>
                
                <div class="mt-6 border-t dark:border-gray-700 pt-4">
                    <form action="<?= url('/users/reject') ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="id" id="rejectUserId">
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Alasan Penolakan</label>
                            <textarea name="rejection_reason" required rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-red-500 focus:ring-red-500" placeholder="Wajib diisi untuk penolakan..."></textarea>
                        </div>
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:text-sm">
                            Tolak (Reject)
                        </button>
                    </form>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" onclick="closeApprovalModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Batal
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
