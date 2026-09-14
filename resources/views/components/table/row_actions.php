<?php
/**
 * Global Row Actions Standard (BIGNET)
 * ---------------------------------------------------------------
 * Pola compact: [ icon + Lihat ] [ ⋮ ]  — menu "⋮" dinamis berdasarkan
 * role + status + relasi data. Dideklarasikan oleh pemanggil:
 *
 *   $rowActions = [
 *     ['type' => 'view', 'url' => ..., 'label' => 'Lihat', 'icon' => 'fa-eye', 'title' => '...'],
 *     ['type' => 'link', 'url' => ..., 'label' => 'Ubah',  'icon' => 'fa-edit'],
 *     ['type' => 'form', 'action' => ..., 'fields' => ['csrf_token' => $_SESSION['csrf_token'] ?? ''],
 *      'label' => 'Hapus', 'icon' => 'fa-trash', 'danger' => true,
 *      'confirm' => ['title' => ..., 'message' => ..., 'description' => ...,
 *                    'type' => 'danger', 'icon' => 'fa-trash',
 *                    'confirm' => 'Hapus', 'loading' => 'Menghapus...']],
 *   ];
 *
 * Catatan:
 *  - Konfirmasi destructive/workflow memakai AppModal global (data-confirm)
 *    yang ditangani public/assets/js/app-feedback.js — TANPA alert()/confirm().
 *  - Toggle menu dikelola oleh JS delegated [data-actions-toggle] (app-feedback.js).
 *  - UI hiding BUKAN security control — backend tetap wajib memeriksa role.
 */
if (empty($rowActions) || !is_array($rowActions)) {
    return;
}

$viewItem = null;
$menuItems = [];
foreach ($rowActions as $act) {
    if (($act['type'] ?? '') === 'view') {
        $viewItem = $act;
    } else {
        $menuItems[] = $act;
    }
}

// Users are the only rows that get a hard-delete action from this shared component.
// The backend remains the actual security/data-integrity boundary.
if ($viewItem && preg_match('#/users/show/([0-9]+)$#', (string)(parse_url($viewItem['url'] ?? '', PHP_URL_PATH) ?? ''), $userMatch)) {
    $hasDeleteAction = false;
    foreach ($menuItems as $existingAction) {
        if (strtolower(trim((string)($existingAction['label'] ?? ''))) === 'hapus') {
            $hasDeleteAction = true;
            break;
        }
    }

    if (!$hasDeleteAction) {
        $menuItems[] = [
            'type' => 'form',
            'action' => url('/users/delete'),
            'fields' => [
                'csrf_token' => $_SESSION['csrf_token'] ?? '',
                'id' => (int)$userMatch[1],
            ],
            'label' => 'Hapus',
            'icon' => 'fa-trash',
            'danger' => true,
            'confirm' => [
                'title' => 'Hapus User?',
                'message' => 'User ini akan dihapus secara permanen jika tidak memiliki data yang masih mereferensikannya.',
                'description' => 'Untuk user yang sudah dipakai dalam transaksi atau data sistem, Anda harus menonaktifkannya, bukan menghapusnya.',
                'type' => 'danger',
                'icon' => 'fa-trash',
                'confirm' => 'Hapus',
                'loading' => 'Menghapus...',
            ],
        ];
    }
}
?>
<td data-label="Aksi" class="px-4 py-3 whitespace-nowrap text-sm font-medium min-w-[140px] text-center align-middle">
    <div class="flex w-full items-center justify-center gap-2">
    <?php if ($viewItem): ?>
        <a href="<?= htmlspecialchars($viewItem['url']) ?>"
           title="<?= htmlspecialchars($viewItem['title'] ?? $viewItem['label'] ?? 'Lihat') ?>"
           class="inline-flex items-center text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 whitespace-nowrap">
            <i class="fas <?= htmlspecialchars($viewItem['icon'] ?? 'fa-eye') ?> mr-1 text-xs"></i>
            <?= htmlspecialchars($viewItem['label'] ?? 'Lihat') ?>
        </a>
    <?php endif; ?>

    <?php if (!empty($menuItems)): ?>
    <div class="relative inline-block text-left" data-actions>
        <button type="button" data-actions-toggle title="Aksi lainnya" aria-label="Aksi lainnya" aria-haspopup="menu" aria-expanded="false"
                class="inline-flex items-center justify-center w-9 h-9 shrink-0 rounded-md text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500">
            <i class="fas fa-ellipsis-v text-sm"></i>
        </button>
        <div data-actions-menu role="menu" aria-label="Aksi lainnya" class="hidden w-44 bg-white dark:bg-gray-800 rounded-md shadow-lg py-1 ring-1 ring-black ring-opacity-5 text-left">
            <?php foreach ($menuItems as $act): ?>
                <?php if (($act['type'] ?? '') === 'button'): ?>
                    <button type="button" onclick="<?= htmlspecialchars($act['onclick'] ?? '') ?>"
                            class="w-full text-left px-4 py-2.5 text-sm flex items-center min-h-[40px] text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                        <?php if (!empty($act['icon'])): ?><i class="fas <?= htmlspecialchars($act['icon']) ?> mr-2 w-4 text-center"></i><?php endif; ?>
                        <?= htmlspecialchars($act['label']) ?>
                    </button>
                <?php elseif (($act['type'] ?? '') === 'form'): ?>
                    <form action="<?= htmlspecialchars($act['action']) ?>" method="POST" class="block"
                          <?php if (!empty($act['confirm'])): ?>
                          data-confirm="<?= htmlspecialchars($act['confirm']['message'] ?? 'Apakah Anda yakin?') ?>"
                          data-confirm-title="<?= htmlspecialchars($act['confirm']['title'] ?? '') ?>"
                          data-confirm-type="<?= htmlspecialchars($act['confirm']['type'] ?? 'warning') ?>"
                          data-confirm-confirm="<?= htmlspecialchars($act['confirm']['confirm'] ?? 'Konfirmasi') ?>"
                          data-confirm-loading="<?= htmlspecialchars($act['confirm']['loading'] ?? '') ?>"
                          <?php if (!empty($act['confirm']['icon'])): ?>data-confirm-icon="<?= htmlspecialchars($act['confirm']['icon']) ?>"<?php endif; ?>
                          <?php if (!empty($act['confirm']['description'])): ?>data-confirm-description="<?= htmlspecialchars($act['confirm']['description']) ?>"<?php endif; ?>
                          <?php endif; ?>>
                        <?php foreach (($act['fields'] ?? []) as $k => $v): ?>
                            <input type="hidden" name="<?= htmlspecialchars($k) ?>" value="<?= htmlspecialchars($v) ?>">
                        <?php endforeach; ?>
                        <button type="submit"
                                class="w-full text-left px-4 py-2.5 text-sm flex items-center min-h-[40px] <?= ($act['danger'] ?? false) ? 'text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' ?>">
                            <?php if (!empty($act['icon'])): ?><i class="fas <?= htmlspecialchars($act['icon']) ?> mr-2 w-4 text-center"></i><?php endif; ?>
                            <?= htmlspecialchars($act['label']) ?>
                        </button>
                    </form>
                <?php else: ?>
                    <a href="<?= htmlspecialchars($act['url']) ?>"
                       class="block px-4 py-2.5 text-sm flex items-center min-h-[40px] text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                        <?php if (!empty($act['icon'])): ?><i class="fas <?= htmlspecialchars($act['icon']) ?> mr-2 w-4 text-center"></i><?php endif; ?>
                        <?= htmlspecialchars($act['label']) ?>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    </div>
</td>
