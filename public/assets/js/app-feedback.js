/**
 * Global Feedback UI — AppModal + AppToast (BIGNET)
 *
 * Standard UI global (2026-08-17):
 *  - Native browser alert()/confirm() TIDAK dipakai untuk UI user-facing.
 *  - Konfirmasi memakai custom modal BIGNET (AppModal.confirm).
 *  - Success/error/warning/info memakai notification/toast internal (AppToast).
 *  - Form/button cukup memakai atribut data-confirm (delegated, tanpa inline JS).
 *
 * API programatik:
 *   AppModal.confirm({ title, message, description, type, icon,
 *                      confirmText, cancelText, loadingText, onConfirm })
 *   AppModal.close()
 *   AppModal.setLoading(bool, [loadingText])
 *   AppToast.success(message[, title])
 *   AppToast.error(message[, title])
 *   AppToast.warning(message[, title])
 *   AppToast.info(message[, title])
 *
 * Atribut deklaratif (pada <form> atau <button type="submit">):
 *   data-confirm="pesan"                (wajib)
 *   data-confirm-title="Judul"
 *   data-confirm-description="Baris info tambahan"
 *   data-confirm-type="danger|warning|info|success"
 *   data-confirm-icon="fa-ban"          (opsional, Font Awesome)
 *   data-confirm-confirm="Tombol utama"
 *   data-confirm-cancel="Batal"
 *   data-confirm-loading="Memproses..."
 *
 * Catatan keamanan: semua teks dirender via textContent (bukan innerHTML),
 * sehingga aman dari injeksi HTML/script.
 */
(function (window, document) {
    'use strict';

    /* ===================== AppToast ===================== */
    var TOAST_STYLES = {
        success: { icon: 'fa-circle-check', wrap: 'bg-green-50 border-green-200 text-green-800 dark:bg-green-900/80 dark:border-green-700 dark:text-green-200', iconCls: 'text-green-500' },
        error:   { icon: 'fa-circle-xmark', wrap: 'bg-red-50 border-red-200 text-red-800 dark:bg-red-900/80 dark:border-red-700 dark:text-red-200', iconCls: 'text-red-500' },
        warning: { icon: 'fa-triangle-exclamation', wrap: 'bg-amber-50 border-amber-200 text-amber-800 dark:bg-amber-900/80 dark:border-amber-700 dark:text-amber-200', iconCls: 'text-amber-500' },
        info:    { icon: 'fa-circle-info', wrap: 'bg-blue-50 border-blue-200 text-blue-800 dark:bg-blue-900/80 dark:border-blue-700 dark:text-blue-200', iconCls: 'text-blue-500' }
    };
    var TOAST_LABELS = { success: 'Berhasil', error: 'Gagal', warning: 'Perhatian', info: 'Informasi' };
    var TOAST_DURATION = 5000;

    function toastContainer() {
        var c = document.getElementById('toastContainer');
        if (!c) {
            c = document.createElement('div');
            c.id = 'toastContainer';
            c.className = 'fixed top-4 right-4 z-[110] space-y-2 pointer-events-none';
            document.body.appendChild(c);
        }
        return c;
    }

    function showToast(kind, message, title) {
        if (!message) return;
        var st = TOAST_STYLES[kind] || TOAST_STYLES.info;
        var label = title || TOAST_LABELS[kind] || '';

        var el = document.createElement('div');
        el.className = 'toast pointer-events-auto flex items-start max-w-sm px-4 py-3 rounded-lg shadow-lg border ' + st.wrap;
        el.setAttribute('role', kind === 'error' ? 'alert' : 'status');

        var icon = document.createElement('i');
        icon.className = 'fas ' + st.icon + ' mr-3 mt-0.5 ' + st.iconCls;

        var body = document.createElement('div');
        body.className = 'min-w-0';
        if (label) {
            var t = document.createElement('p');
            t.className = 'text-sm font-semibold';
            t.textContent = label;
            body.appendChild(t);
            var m = document.createElement('p');
            m.className = 'text-sm opacity-90 mt-0.5';
            m.textContent = message;
            body.appendChild(m);
        } else {
            var s = document.createElement('span');
            s.className = 'text-sm';
            s.textContent = message;
            body.appendChild(s);
        }

        el.appendChild(icon);
        el.appendChild(body);
        el.addEventListener('click', function () { el.remove(); });
        toastContainer().appendChild(el);
        window.setTimeout(function () { el.remove(); }, TOAST_DURATION);
    }

    /* ===================== AppModal ===================== */
    var MODAL_TYPES = {
        danger:  { icon: 'fa-circle-exclamation', iconWrap: 'bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-400', btn: 'bg-red-600 hover:bg-red-700' },
        warning: { icon: 'fa-triangle-exclamation', iconWrap: 'bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-400', btn: 'bg-amber-600 hover:bg-amber-700' },
        info:    { icon: 'fa-circle-info', iconWrap: 'bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-400', btn: 'bg-blue-600 hover:bg-blue-700' },
        success: { icon: 'fa-circle-check', iconWrap: 'bg-green-100 text-green-600 dark:bg-green-900/40 dark:text-green-400', btn: 'bg-green-600 hover:bg-green-700' }
    };

    var modalEl = null;
    var backdropEl = null;
    var confirmBtn = null;
    var cancelBtn = null;
    var confirmText = '';
    var isLoading = false;
    var lastFocused = null;

    function buildModal(opts) {
        var st = MODAL_TYPES[opts.type] || MODAL_TYPES.warning;
        var iconCls = opts.icon || st.icon;
        confirmText = opts.confirmText || 'Konfirmasi';

        backdropEl = document.createElement('div');
        backdropEl.className = 'app-modal-backdrop fixed inset-0 bg-gray-900/50 dark:bg-black/60 z-[100] flex items-center justify-center p-4';
        backdropEl.setAttribute('data-appmodal', 'backdrop');

        modalEl = document.createElement('div');
        modalEl.className = 'app-modal-panel bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-md border border-gray-200 dark:border-gray-700';
        modalEl.setAttribute('role', 'dialog');
        modalEl.setAttribute('aria-modal', 'true');
        modalEl.setAttribute('aria-labelledby', 'appModalTitle');
        if (opts.message || opts.description) {
            modalEl.setAttribute('aria-describedby', 'appModalMessage');
        }

        var iconWrap = document.createElement('div');
        iconWrap.className = 'flex-shrink-0 w-11 h-11 rounded-full flex items-center justify-center text-lg ' + st.iconWrap;
        var icon = document.createElement('i');
        icon.className = 'fas ' + iconCls;
        iconWrap.appendChild(icon);

        var header = document.createElement('div');
        header.className = 'flex items-start gap-4';
        header.appendChild(iconWrap);

        var textWrap = document.createElement('div');
        textWrap.className = 'min-w-0';
        var title = document.createElement('h3');
        title.id = 'appModalTitle';
        title.className = 'text-lg font-bold text-gray-900 dark:text-white';
        title.textContent = opts.title || 'Konfirmasi';
        textWrap.appendChild(title);

        if (opts.message) {
            var msg = document.createElement('p');
            msg.id = 'appModalMessage';
            msg.className = 'mt-1.5 text-sm text-gray-600 dark:text-gray-300';
            msg.textContent = opts.message;
            textWrap.appendChild(msg);
        }
        if (opts.description) {
            var desc = document.createElement('p');
            desc.className = 'mt-2 text-sm text-gray-500 dark:text-gray-400';
            desc.textContent = opts.description;
            textWrap.appendChild(desc);
        }
        header.appendChild(textWrap);

        var body = document.createElement('div');
        body.className = 'p-6';
        body.appendChild(header);

        var footer = document.createElement('div');
        footer.className = 'px-6 pb-6 flex flex-col-reverse sm:flex-row justify-end gap-3';

        cancelBtn = document.createElement('button');
        cancelBtn.type = 'button';
        cancelBtn.className = 'px-4 py-2 rounded-lg text-sm font-semibold bg-gray-200 hover:bg-gray-300 text-gray-800 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200 transition-colors';
        cancelBtn.textContent = opts.cancelText || 'Batal';
        cancelBtn.addEventListener('click', closeModal);

        confirmBtn = document.createElement('button');
        confirmBtn.type = 'button';
        confirmBtn.className = 'px-4 py-2 rounded-lg text-sm font-semibold text-white shadow-sm transition-colors ' + st.btn;
        confirmBtn.textContent = confirmText;
        confirmBtn.addEventListener('click', function () {
            if (isLoading) return;
            setLoading(true, opts.loadingText || confirmText);
            if (typeof opts.onConfirm === 'function') {
                try {
                    opts.onConfirm();
                } catch (err) {
                    setLoading(false);
                    showToast('error', 'Terjadi kesalahan. Silakan coba lagi.');
                }
            }
        });

        footer.appendChild(cancelBtn);
        footer.appendChild(confirmBtn);
        modalEl.appendChild(body);
        modalEl.appendChild(footer);
        backdropEl.appendChild(modalEl);
    }

    function setLoading(loading, loadingText) {
        isLoading = loading;
        if (!confirmBtn || !cancelBtn) return;
        cancelBtn.disabled = loading;
        confirmBtn.disabled = loading;
        if (modalEl) modalEl.setAttribute('aria-busy', loading ? 'true' : 'false');
        if (loading) {
            confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>' + (loadingText || confirmText || 'Memproses...');
        } else {
            confirmBtn.textContent = confirmText;
        }
    }

    function onKeydown(e) {
        if (e.key === 'Escape' && !isLoading) {
            e.preventDefault();
            closeModal();
        }
    }

    function closeModal() {
        if (!backdropEl) return;
        backdropEl.remove();
        modalEl = null;
        backdropEl = null;
        confirmBtn = null;
        cancelBtn = null;
        confirmText = '';
        isLoading = false;
        document.body.classList.remove('modal-open');
        document.removeEventListener('keydown', onKeydown);
        if (lastFocused && lastFocused.focus) {
            try { lastFocused.focus(); } catch (e) { /* noop */ }
        }
        lastFocused = null;
    }

    function confirm(opts) {
        if (!opts) opts = {};
        if (modalEl) closeModal();
        lastFocused = document.activeElement;
        buildModal(opts);
        document.body.appendChild(backdropEl);
        document.body.classList.add('modal-open');
        document.addEventListener('keydown', onKeydown);
        backdropEl.addEventListener('click', function (e) {
            if (e.target === backdropEl) closeModal();
        });
        // Fokus masuk ke modal (tombol Batal = pilihan aman), bukan ke halaman belakang.
        window.setTimeout(function () {
            if (cancelBtn) cancelBtn.focus();
        }, 10);
    }

    /* ============ Delegated declarative confirm (data-confirm) ============ */
    function readConfirmAttrs(el) {
        return {
            title: el.getAttribute('data-confirm-title') || '',
            message: el.getAttribute('data-confirm') || 'Apakah Anda yakin?',
            description: el.getAttribute('data-confirm-description') || '',
            type: el.getAttribute('data-confirm-type') || 'warning',
            icon: el.getAttribute('data-confirm-icon') || '',
            confirmText: el.getAttribute('data-confirm-confirm') || 'Konfirmasi',
            cancelText: el.getAttribute('data-confirm-cancel') || 'Batal',
            loadingText: el.getAttribute('data-confirm-loading') || ''
        };
    }

    // <form data-confirm="..."> — intercept submit
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form || typeof form.hasAttribute !== 'function' || !form.hasAttribute('data-confirm')) return;
        if (form.dataset.appConfirmed === '1') {
            delete form.dataset.appConfirmed;
            return; // izinkan submit asli (setelah konfirmasi)
        }
        e.preventDefault();
        var opts = readConfirmAttrs(form);
        opts.onConfirm = function () {
            form.dataset.appConfirmed = '1';
            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
            } else {
                form.submit();
            }
            window.setTimeout(function () { delete form.dataset.appConfirmed; }, 0);
        };
        AppModal.confirm(opts);
    });

    // <button data-confirm="..."> (type=submit) — intercept click
    document.addEventListener('click', function (e) {
        var target = e.target;
        if (!target || typeof target.closest !== 'function') return;
        var btn = target.closest('button[data-confirm]');
        if (!btn || !btn.form) return;
        if (btn.form.dataset.appConfirmed === '1') {
            delete btn.form.dataset.appConfirmed;
            return;
        }
        e.preventDefault();
        var opts = readConfirmAttrs(btn);
        opts.onConfirm = function () {
            var f = btn.form;
            f.dataset.appConfirmed = '1';
            if (typeof f.requestSubmit === 'function') {
                f.requestSubmit(btn);
            } else {
                f.submit();
            }
            window.setTimeout(function () { delete f.dataset.appConfirmed; }, 0);
        };
        AppModal.confirm(opts);
    });

    /* ============ Global Row Actions (pola [Lihat] [⋮]) ============ */
    // Dropdown dirender sebagai PORTAL ke <body> (position:fixed) agar:
    //  - tidak terpotong container overflow-x-auto / overflow-hidden
    //  - tidak menggeser layout row/tabel (floating overlay)
    //  - viewport-aware: flip ke kiri bila ruang kanan sempit, flip ke atas bila ruang bawah sempit
    // Source menu tetap inline (hidden) — portal adalah clone yang dibuang saat close.
    function closeAllActionsMenus() {
        document.querySelectorAll('[data-actions-portal]').forEach(function (p) {
            p.remove();
        });
        document.querySelectorAll('[data-actions-toggle]').forEach(function (t) {
            t.setAttribute('aria-expanded', 'false');
        });
    }

    function openActionsMenu(toggle) {
        var holder = toggle.parentElement;
        var source = holder.querySelector('[data-actions-menu]');
        if (!source) return;

        var portal = source.cloneNode(true);
        portal.classList.remove('hidden');
        portal.setAttribute('data-actions-menu', '1');
        portal.setAttribute('data-actions-portal', '1');
        document.body.appendChild(portal);

        var rect = toggle.getBoundingClientRect();
        var mrect = portal.getBoundingClientRect();
        var gap = 6;
        var vw = window.innerWidth || document.documentElement.clientWidth;
        var vh = window.innerHeight || document.documentElement.clientHeight;

        var left = rect.right - mrect.width;
        if (left < gap) left = rect.left;
        left = Math.max(gap, Math.min(left, vw - mrect.width - gap));

        var top = rect.bottom + gap;
        if (top + mrect.height > vh - gap) {
            top = rect.top - mrect.height - gap;
        }
        top = Math.max(gap, top);

        portal.style.position = 'fixed';
        portal.style.left = left + 'px';
        portal.style.top = top + 'px';
        portal.style.zIndex = '90';
        portal.style.minWidth = Math.max(mrect.width, 176) + 'px';

        toggle.setAttribute('aria-expanded', 'true');

        var first = portal.querySelector('a, button, input[type="submit"]');
        if (first) {
            try { first.focus({ preventScroll: true }); } catch (e) { first.focus(); }
        }
    }

    document.addEventListener('click', function (e) {
        var toggle = e.target && e.target.closest ? e.target.closest('[data-actions-toggle]') : null;
        if (toggle) {
            e.stopPropagation();
            var wasOpen = toggle.getAttribute('aria-expanded') === 'true';
            closeAllActionsMenus();
            if (!wasOpen) openActionsMenu(toggle);
            return;
        }
        var inMenu = e.target && e.target.closest ? !!e.target.closest('[data-actions-menu]') : false;
        if (!inMenu) closeAllActionsMenus();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' || e.key === 'Esc') {
            closeAllActionsMenus();
            return;
        }
        if (e.key !== 'ArrowDown' && e.key !== 'ArrowUp') return;
        var open = document.querySelector('[data-actions-portal]');
        if (!open) return;
        var items = Array.prototype.slice.call(open.querySelectorAll('a, button, input[type="submit"]'));
        if (!items.length) return;
        e.preventDefault();
        var idx = items.indexOf(document.activeElement);
        if (e.key === 'ArrowDown') idx = (idx + 1) % items.length;
        else idx = (idx - 1 + items.length) % items.length;
        items[idx].focus();
    });

    window.addEventListener('scroll', function () { closeAllActionsMenus(); }, true);
    window.addEventListener('resize', function () { closeAllActionsMenus(); });

    /* ===================== Public API ===================== */
    var AppToast = {
        success: function (message, title) { showToast('success', message, title); },
        error: function (message, title) { showToast('error', message, title); },
        warning: function (message, title) { showToast('warning', message, title); },
        info: function (message, title) { showToast('info', message, title); }
    };

    var AppModal = {
        confirm: confirm,
        close: closeModal,
        setLoading: setLoading
    };

    window.AppToast = AppToast;
    window.AppModal = AppModal;
})(window, document);

/* BIGNET GLOBAL UI RULE — INTERNAL/GENERATED CODES ARE NOT USER-FACING */
(function (window, document) {
    'use strict';

    var INTERNAL_CODE_RE = /\b(?:KAT|GDG|UNT|REQ|TRF|LOC)-[A-Z0-9][A-Z0-9-]*\b/gi;
    var CODE_LABEL_RE = /^\s*(?:kode(?:\s+barang|\s+gudang|\s+kategori|\s+satuan|\s+pengajuan|\s+transfer|\s+lokasi)?(?:\s*\/\s*SKU)?|kode\s*\/\s*sku)\s*$/i;

    function cleanTextNode(node) {
        if (!node || !node.nodeValue || !INTERNAL_CODE_RE.test(node.nodeValue)) return;
        node.nodeValue = node.nodeValue
            .replace(INTERNAL_CODE_RE, '')
            .replace(/\s{2,}/g, ' ')
            .replace(/\s+([—|-])/g, ' $1')
            .replace(/([—|-])\s*([—|-])/g, '$1');
    }

    function isIdentifierCell(cell) {
        var value = (cell.textContent || '').trim();
        return INTERNAL_CODE_RE.test(value) || /^\[Auto Generated\]$/i.test(value);
    }

    function removeCodeFields(root) {
        (root || document).querySelectorAll('label').forEach(function (label) {
            var labelText = (label.textContent || '').replace(/\s+/g, ' ').trim();
            if (!CODE_LABEL_RE.test(labelText)) return;

            var block = label.closest('.mb-4, .mb-6, .form-group, .field');
            if (!block) block = label.parentElement;
            if (block) block.style.display = 'none';
        });

        (root || document).querySelectorAll('input[placeholder], textarea[placeholder]').forEach(function (el) {
            var p = el.getAttribute('placeholder') || '';
            if (/kode\b/i.test(p)) {
                var cleaned = p.replace(/kode\s*/ig, '').replace(/\s*\/\s*/g, ' ').replace(/\s{2,}/g, ' ').trim();
                el.setAttribute('placeholder', cleaned || 'Cari...');
            }
        });
    }

    function removeCodeColumns(root) {
        (root || document).querySelectorAll('table').forEach(function (table) {
            var rows = table.rows;
            if (!rows || rows.length < 1) return;
            var header = rows[0];
            var indexes = [];
            Array.prototype.forEach.call(header.cells, function (cell, idx) {
                var label = (cell.textContent || '').replace(/\s+/g, ' ').trim();
                if (/\bkode\b/i.test(label)) indexes.push(idx);
            });
            if (!indexes.length) return;
            Array.prototype.forEach.call(rows, function (row) {
                indexes.slice().sort(function (a, b) { return b - a; }).forEach(function (idx) {
                    if (row.cells[idx] && row.cells.length > 1) row.deleteCell(idx);
                });
            });
        });
    }

    function removeCodeGuideText(root) {
        (root || document).querySelectorAll('li, small, p').forEach(function (el) {
            var t = (el.textContent || '').replace(/\s+/g, ' ').trim();
            if (/^kode\b/i.test(t) || /kode\s+(?:dibuat|dibikin|dibentuk)/i.test(t) || /format\s+kode/i.test(t)) {
                el.style.display = 'none';
            }
        });
    }

    function stripCodes(root) {
        var walker = document.createTreeWalker(root || document.body, NodeFilter.SHOW_TEXT);
        var nodes = [];
        while (walker.nextNode()) nodes.push(walker.currentNode);
        nodes.forEach(cleanTextNode);
    }

    function applyCodePolicy(root) {
        removeCodeFields(root);
        removeCodeColumns(root);
        removeCodeGuideText(root);
        stripCodes(root || document.body);
    }

    function initCodePolicy() {
        applyCodePolicy(document);
        var observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                Array.prototype.forEach.call(mutation.addedNodes, function (node) {
                    if (node.nodeType === 1) applyCodePolicy(node);
                });
            });
        });
        observer.observe(document.body, { childList: true, subtree: true });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCodePolicy);
    } else {
        initCodePolicy();
    }
})(window, document);

/* BIGNET PRINT FIX — Surat Jalan: batasi logo/foto header agar proporsional. */
(function (window, document) {
    'use strict';

    function constrainSuratJalanImages() {
        var section = document.getElementById('surat-jalan');
        if (!section) return;

        section.querySelectorAll('img').forEach(function (img) {
            img.style.setProperty('width', 'auto', 'important');
            img.style.setProperty('height', '16mm', 'important');
            img.style.setProperty('max-width', '40mm', 'important');
            img.style.setProperty('max-height', '16mm', 'important');
            img.style.setProperty('object-fit', 'contain', 'important');
            img.style.setProperty('flex', '0 0 auto', 'important');
        });

        var header = section.firstElementChild;
        if (header) {
            header.style.setProperty('display', 'flex', 'important');
            header.style.setProperty('flex-wrap', 'nowrap', 'important');
            header.style.setProperty('align-items', 'flex-start', 'important');

            var right = header.querySelector(':scope > div:last-child');
            if (right) {
                right.style.setProperty('flex', '0 0 auto', 'important');
                right.style.setProperty('margin-left', 'auto', 'important');
                right.style.setProperty('min-width', '38mm', 'important');
            }
        }
    }

    window.addEventListener('beforeprint', constrainSuratJalanImages);
})(window, document);
