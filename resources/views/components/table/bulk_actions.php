<?php
/**
 * REUSABLE BULK ACTIONS COMPONENT (shared pattern, jangan copy-paste)
 *
 * Variabel yang didukung (set sebelum include):
 *  - $bulkAction : string (URL tujuan POST, mis. url('/categories/bulk-delete'))
 *  - $bulkId     : string (opsional, unik bila ada lebih dari satu di satu halaman)
 *  - $bulkLabel  : string (opsional, label tombol aksi, default 'Hapus Terpilih')
 *
 * Markup tabel harus menyediakan:
 *  - checkbox per baris  : <input type="checkbox" class="bulk-checkbox {bulkId}" value="{id}">
 *  - select-all di header: <input type="checkbox" class="bulk-select-all {bulkId}">
 *
 * Komponen menangani: select all, selected count, batal, konfirmasi destructive,
 * loading state, submit POST + CSRF, feedback via flash message session.
 */
$bulkId = $bulkId ?? 'bulk';
$bulkLabel = $bulkLabel ?? 'Hapus Terpilih';
$csrfToken = htmlspecialchars($_SESSION['csrf_token'] ?? '');
?>
<div id="<?= $bulkId ?>-bar" class="hidden bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm px-4 py-3 mb-6 flex flex-wrap items-center justify-between gap-3">
    <div class="text-sm text-gray-600 dark:text-gray-300">
        <span class="font-bold text-gray-900 dark:text-white" id="<?= $bulkId ?>-count">0</span> data dipilih
    </div>
    <div class="flex gap-2">
        <button type="button" id="<?= $bulkId ?>-cancel" class="bg-gray-200 hover:bg-gray-300 text-gray-800 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200 font-bold py-2 px-4 rounded transition-colors text-sm">
            Batal
        </button>
        <button type="button" id="<?= $bulkId ?>-submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition-colors text-sm flex items-center gap-2">
            <i class="fas fa-trash-alt"></i> <?= htmlspecialchars($bulkLabel) ?>
        </button>
    </div>
</div>

<form id="<?= $bulkId ?>-form" method="POST" action="<?= htmlspecialchars($bulkAction) ?>" class="hidden">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
    <input type="hidden" name="ids" id="<?= $bulkId ?>-ids" value="">
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var ns = <?= json_encode($bulkId) ?>;
    var bar = document.getElementById(ns + '-bar');
    var countEl = document.getElementById(ns + '-count');
    var idsEl = document.getElementById(ns + '-ids');
    var form = document.getElementById(ns + '-form');
    var checkboxes = Array.prototype.slice.call(document.querySelectorAll('.bulk-checkbox.' + ns));
    var selectAll = document.querySelector('.bulk-select-all.' + ns);
    var cancelBtn = document.getElementById(ns + '-cancel');
    var submitBtn = document.getElementById(ns + '-submit');
    if (!bar || !form || checkboxes.length === 0) return;

    function refresh() {
        var sel = checkboxes.filter(function (c) { return c.checked; });
        countEl.textContent = sel.length;
        idsEl.value = sel.map(function (c) { return c.value; }).join(',');
        bar.classList.toggle('hidden', sel.length === 0);
    }

    checkboxes.forEach(function (cb) { cb.addEventListener('change', refresh); });

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            checkboxes.forEach(function (cb) { cb.checked = selectAll.checked; });
            refresh();
        });
    }

    if (cancelBtn) {
        cancelBtn.addEventListener('click', function () {
            checkboxes.forEach(function (cb) { cb.checked = false; });
            if (selectAll) selectAll.checked = false;
            refresh();
        });
    }

    if (submitBtn) {
        submitBtn.addEventListener('click', function () {
            var sel = checkboxes.filter(function (c) { return c.checked; });
            if (sel.length === 0) return;
            AppModal.confirm({
                title: 'Hapus Data',
                message: 'Hapus ' + sel.length + ' data terpilih? Tindakan ini tidak dapat dibatalkan.',
                type: 'danger',
                icon: 'fa-trash',
                confirmText: 'Hapus',
                loadingText: 'Menghapus...',
                onConfirm: function () {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menghapus...';
                    form.submit();
                }
            });
        });
    }
});
</script>
