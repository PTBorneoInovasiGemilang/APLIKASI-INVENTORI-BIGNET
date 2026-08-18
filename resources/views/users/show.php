<?php
$pageTitle = 'Detail Pengguna';
ob_start();
$statusBadge = [
    'PENDING_APPROVAL' => ['Menunggu Persetujuan', 'WARNING'],
    'APPROVED' => ['Disetujui', 'SUCCESS'],
    'REJECTED' => ['Ditolak', 'DANGER'],
    'SUSPENDED' => ['Ditangguhkan', 'DANGER'],
];
$badge = $statusBadge[$user['account_status']] ?? ['Tidak Diketahui', 'NEUTRAL'];
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 fade-in">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="<?= url('/users') ?>" class="text-gray-500 dark:text-gray-400 hover:text-blue-600 transition-colors">
                    <i class="fas fa-arrow-left text-xl"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Detail Pengguna</h1>
                    <p class="text-sm text-gray-600 dark:text-gray-400"><?= htmlspecialchars($user['name']) ?> (<?= htmlspecialchars($user['email']) ?>)</p>
                </div>
            </div>
            <a href="<?= url('/users/edit/' . $user['id']) ?>" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold transition-colors flex items-center gap-2">
                <i class="fas fa-edit"></i> Ubah
            </a>
        </div>
    </div>

    <!-- Flash ditampilkan via toast global (layouts/app.php -> #toastContainer), standard UI 2026-08-17 -->

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Profile card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-6">
                    <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4">
                        <div class="flex-shrink-0 h-20 w-20 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center overflow-hidden">
                            <?php if ($user['photo_path']): ?>
                                <img src="<?= url('/file?path=' . urlencode($user['photo_path'])) ?>" alt="Foto Profil" class="h-20 w-20 object-cover">
                            <?php else: ?>
                                <i class="fas fa-user text-3xl text-gray-400 dark:text-gray-500"></i>
                            <?php endif; ?>
                        </div>
                        <div class="text-center sm:text-left flex-1">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($user['name']) ?></h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400"><?= htmlspecialchars($user['email']) ?></p>
                            <div class="mt-2 flex flex-wrap justify-center sm:justify-start gap-2">
                                <?= \App\Helpers\UIHelper::badge(\App\Helpers\UIHelper::roleLabel($user['role']), 'INFO') ?>
                                <?= \App\Helpers\UIHelper::badge($badge[0], $badge[1]) ?>
                                <?= $user['is_active'] ? \App\Helpers\UIHelper::badge('Aktif', 'SUCCESS') : \App\Helpers\UIHelper::badge('Nonaktif', 'DANGER') ?>
                            </div>
                            <?php if ($user['account_status'] === 'REJECTED' && $user['rejection_reason']): ?>
                                <p class="mt-3 text-sm text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-md px-3 py-2">
                                    <i class="fas fa-exclamation-circle mr-1"></i> Alasan penolakan: <?= htmlspecialchars($user['rejection_reason']) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-3 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wide">Telepon</p>
                        <p class="text-gray-900 dark:text-white font-medium"><?= htmlspecialchars($user['phone'] ?: '-') ?></p>
                    </div>
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wide">Terakhir Login</p>
                        <p class="text-gray-900 dark:text-white font-medium"><?= $user['last_login_at'] ? date('d M Y H:i', strtotime($user['last_login_at'])) : 'Belum pernah' ?></p>
                    </div>
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wide">Dibuat</p>
                        <p class="text-gray-900 dark:text-white font-medium"><?= date('d M Y H:i', strtotime($user['created_at'])) ?></p>
                    </div>
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wide">Diperbarui</p>
                        <p class="text-gray-900 dark:text-white font-medium"><?= date('d M Y H:i', strtotime($user['updated_at'])) ?></p>
                    </div>
                </div>
            </div>

            <!-- Status-aware actions -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-white uppercase tracking-wide">Aksi Akun</h3>
                </div>
                <div class="px-4 py-3 flex flex-wrap gap-3">
                    <?php if ($user['account_status'] === 'PENDING_APPROVAL'): ?>
                        <form action="<?= url('/users/approve') ?>" method="POST"
                              data-confirm="Setujui dan aktifkan akun <?= htmlspecialchars($user['name']) ?>?"
                              data-confirm-title="Setujui Akun"
                              data-confirm-type="success"
                              data-confirm-icon="fa-check"
                              data-confirm-confirm="Setujui"
                              data-confirm-loading="Memproses...">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                            <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">
                            <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-semibold transition-colors">
                                <i class="fas fa-check mr-1"></i> Setujui
                            </button>
                        </form>
                        <form action="<?= url('/users/reject') ?>" method="POST" class="flex items-center gap-2"
                              data-confirm="Tolak pendaftaran akun <?= htmlspecialchars($user['name']) ?>? Akun tidak akan dapat login."
                              data-confirm-title="Tolak Pendaftaran"
                              data-confirm-type="danger"
                              data-confirm-icon="fa-times"
                              data-confirm-confirm="Tolak"
                              data-confirm-loading="Memproses...">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                            <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">
                            <input type="text" name="rejection_reason" required placeholder="Alasan penolakan (wajib)" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                            <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-semibold transition-colors">
                                <i class="fas fa-times mr-1"></i> Tolak
                            </button>
                        </form>
                    <?php endif; ?>

                    <?php if ($user['account_status'] === 'APPROVED' || $user['account_status'] === 'SUSPENDED'): ?>
                        <form action="<?= url('/users/toggle-active') ?>" method="POST"
                              data-confirm-title="<?= $user['is_active'] ? 'Nonaktifkan Akun' : 'Aktifkan Akun' ?>"
                              data-confirm="<?= $user['is_active'] ? 'Apakah Anda yakin ingin menonaktifkan akun ' . htmlspecialchars($user['name']) . '?' : 'Aktifkan kembali akun ' . htmlspecialchars($user['name']) . '?' ?>"
                              data-confirm-description="<?= $user['is_active'] ? 'Akun tidak akan dapat digunakan untuk login sampai diaktifkan kembali.' : 'Akun akan dapat digunakan untuk login kembali.' ?>"
                              data-confirm-type="<?= $user['is_active'] ? 'danger' : 'success' ?>"
                              data-confirm-icon="<?= $user['is_active'] ? 'fa-ban' : 'fa-play' ?>"
                              data-confirm-confirm="<?= $user['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>"
                              data-confirm-loading="<?= $user['is_active'] ? 'Menonaktifkan...' : 'Mengaktifkan...' ?>">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                            <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">
                            <?php if ($user['is_active']): ?>
                                <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-sm font-semibold transition-colors">
                                    <i class="fas fa-ban mr-1"></i> Nonaktifkan
                                </button>
                            <?php else: ?>
                                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold transition-colors">
                                    <i class="fas fa-play mr-1"></i> Aktifkan
                                </button>
                            <?php endif; ?>
                        </form>
                        <form action="<?= url('/users/reset-password') ?>" method="POST"
                              data-confirm="Reset password akun <?= htmlspecialchars($user['name']) ?>? Password baru akan ditampilkan sekali."
                              data-confirm-title="Reset Password"
                              data-confirm-type="warning"
                              data-confirm-icon="fa-key"
                              data-confirm-confirm="Reset Password"
                              data-confirm-loading="Memproses...">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                            <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">
                            <button type="submit" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg text-sm font-semibold transition-colors">
                                <i class="fas fa-key mr-1"></i> Reset Password
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Account history -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-white uppercase tracking-wide">Riwayat Akun & Audit</h3>
                    <a href="<?= url('/audit?search=' . urlencode($user['email'])) ?>" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">Buka Audit Log</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-500 dark:text-gray-400 uppercase text-xs">
                            <tr>
                                <th class="px-4 py-3">Waktu</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                                <th class="px-4 py-3">Pelaku</th>
                                <th class="px-4 py-3">IP</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <?php foreach ($auditHistory as $log): ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-600 dark:text-gray-300"><?= date('d M Y H:i', strtotime($log['created_at'])) ?></td>
                                    <td class="px-4 py-3">
                                        <?= \App\Helpers\UIHelper::badge(htmlspecialchars($log['action']), 'NEUTRAL') ?>
                                    </td>
                                    <td class="px-4 py-3 text-gray-900 dark:text-white"><?= htmlspecialchars($log['actor_name'] ?: 'System') ?></td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400"><?= htmlspecialchars($log['ip_address'] ?: '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($auditHistory)): ?>
                                <tr>
                                    <td colspan="4" class="px-4 py-3">
                                        <?php
                                        $colspan = 4;
                                        $emptyMessage = "Belum ada riwayat aktivitas.";
                                        $emptySubMessage = "Aksi pada akun ini akan tercatat di sini.";
                                        require __DIR__ . '/../components/table/empty_state.php';
                                        ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/app.php'; ?>
