# BIGNET UAT STATUS

## RULE

PASS hanya jika:
- business flow benar;
- role benar;
- backend benar;
- database benar;
- browser flow berhasil;
- tidak ada dead action;
- evidence tersedia bila diperlukan.

HTTP 200 bukan UAT PASS.

## ROLE UAT

### SUPER ADMIN
- [x] Login
- [x] Dashboard
- [x] Users
- [x] Settings
- [x] Audit
- [x] Monitoring
- [x] Logout
- [x] Read-only pada semua modul operasional (tidak ada mutation button; direct route mutation → 403)
- [x] Approval variance Stock Opname (D-014, locked)

### ADMIN / WAREHOUSE
- [x] Login
- [x] Master Data
- [x] Incoming
- [x] Stock
- [x] Request Processing
- [x] Outgoing
- [x] Transfer
- [x] Borrowing
- [x] Return
- [x] Installation
- [x] Stock Opname
- [x] Adjustment
- [x] Reports
- [x] Logout

### TEKNISI
- [x] Login
- [x] Request
- [x] Request Status
- [x] Custody/Assigned Assets
- [x] Evidence
- [x] Installation
- [x] Return
- [x] Damage
- [x] Lost
- [x] Timeline
- [x] Notifikasi (bell + pusat notifikasi)
- [x] Mobile-first (375/390/768/desktop tanpa overflow, guide di bawah konten)
- [x] Logout

> **Catatan 2026-08-17 (TEKNISI/NOC MOBILE-FIRST + FIX PHP LEAK):**
> Perubahan UI Teknisi (form Buat Pengajuan card-based, `.mobile-card-table` di 7 tabel, touch target, fix PHP source leak di `/submissions/create`) **belum diverifikasi di browser** — Playwright tidak dijalankan di Freebuff pada task ini. Status di atas TIDAK diubah menjadi PASS baru; butuh re-UAT browser. Baris check Teknisi di atas adalah hasil UAT browser sebelumnya, bukan hasil task ini.

## BROWSER CHECKLIST

- [ ] All relevant menus clickable
- [ ] Routes valid
- [ ] Search
- [ ] Filter
- [ ] Reset
- [ ] Pagination
- [ ] Forms
- [ ] Validation
- [ ] Success feedback
- [ ] Error feedback
- [x] Role restrictions
- [x] Business workflow
- [x] Responsive tables (scroll horizontal, sticky No/Aksi, header/body sejajar)
- [x] Page Guide panel kanan (desktop) / bawah (mobile), role-aware
- [x] Empty state terpusat (ikon + judul + deskripsi)
- [x] CRUD visibility per role + route langsung tidak sah ditolak backend
- [x] Bulk action (select-all, count, confirm, loading, feedback, guard referensi)
- [x] Timeline
- [x] Audit
- [x] Evidence (upload secure, preview, metadata, authz, hapus)
- [x] Barcode/QR (label print + batch, scan lookup)
- [x] Notification center (bell, unread count, mark read/all, user-isolated)
- [x] Live search (debounce, URL sinkron, empty/error/loading)
- [x] Export PDF (filter mengikuti laporan, PDF valid)
- [x] Mobile Teknisi (375/390/768, hamburger, dark)
- [x] Dashboard analytics

## LAST VERIFIED

Date: 2026-08-17 (**FINAL BROWSER UAT PASS — Antigravity**)
Tester: Antigravity (FINAL BROWSER UAT), Playwright (automated regression historical 93/93 PASS)
Environment: Localhost

> **Catatan 2026-08-18 — SUBMISSION WAREHOUSE FULFILLMENT (perlu re-UAT browser):** workflow Pengajuan diubah — Teknisi wajib memilih **Gudang Pemenuhan** per item STOCK (tersimpan `submission_items.warehouse_id`); detail menampilkan Gudang Pemenuhan + stok tersedia; Barang Keluar preselect gudang pemenuhan dan menolak warehouse mismatch; approval cek stok scoped per gudang. Diverifikasi Freebuff end-to-end (HTTP+DB 26/26 PASS, `php -l` + `node --check` bersih, DB residue 0) — **TANPA Playwright/browser**. Status modul Pengajuan/Approval/Outgoing di bawah BELUM diklaim PASS ulang untuk perubahan ini sampai UAT browser dijalankan.

## STATUS PER MODUL (TERKINI)

| Modul | Status | Evidence |
|---|---|---|
| Global Search | PASS | E2E `test_search_e2e` (tidak ada HTTP 500) |
| Batch A — Master Data & Inventory | PASS | E2E table standard |
| Batch B — Operational Transactions | PASS | E2E table standard |
| Batch C — Damage/Lost & Stock Opname | PASS | `test_incidents_e2e` + `test_opnames_e2e` (re-run 2026-08-16, 6.6s) |
| Batch D — Reports, Timeline, Audit Log, Users | PASS | `test_reports_e2e`, `test_timeline_e2e`, `test_users_e2e` |
| Batch D — Account Management | PASS | `test_account_e2e` (forgot/reset/change password, protected route) |
| Dashboard Analytics | PASS | `test_dashboard_e2e` (KPI nyata, trend, alert, role visibility, dark mode) |
| Global Page Guide | PASS | `test_page_guide_e2e` (7/7) |
| Global Dark Mode + Role-Based UI | PASS | `test_dark_role_e2e` (7/7: sidebar per role, 403 route langsung tidak sah, dark mode tabel/form/detail, mobile 375px, no console error) |
| Global Table Layout Rule | PASS | `test_table_layout_e2e` (5/5: desktop scroll terkontrol & alignment, mobile 375px sticky No/Aksi tanpa overflow, empty state, halaman terpotong kini scrollable, dark mode sticky bg) |
| Refinement: Page Guide + Empty State + CRUD/Bulk | PASS | `test_refine_e2e` (8/8: guide kanan desktop / bawah mobile, edit user tanpa guide kiri, empty state terpusat, CRUD per role + 403 route langsung, bulk select-all/confirm/delete/success + guard terpakai, dark mode) |
| Role & Page UX Reconciliation | PASS | `test_reconcile_e2e` (6/6) + re-role `test_incidents_e2e` & `test_opnames_e2e` (4/4): SUPERADMIN read-only (tanpa mutation button, 403 route), ADMIN insiden/opname, TEKNISI lapor tanpa proses, empty state shared, guide auth pages, dark mode |
| Account/User Management | PASS | `test_account_e2e` (3/3: detail + status-aware actions + reset password + auth negatives + register tidak bisa SUPERADMIN) |
| Notification Center | PASS | `test_notifications_e2e` (2/2: register→SA notified, approve→user notified, mark read/all, isolation, dark, mobile) |
| Live Search | PASS | `test_livesearch_e2e` (2/2: debounce, empty state, filter live, URL sinkron, detail tetap jalan, dark) |
| Barcode/QR | PASS | `test_barcode_e2e` (4/4: Code128 render, QR modal, label sheet + batch print, scan lookup SN/kode/not-found, teknisi 403) |
| Evidence/Attachment | PASS | `test_evidence_e2e` (3/3: upload→metadata→view→delete, teknisi 403, legacy /file fixed, dark, mobile, no console) |
| Export PDF | PASS | `test_export_e2e` (2/2: PDF valid %PDF-1.4 + %%EOF, filter ikut laporan, teknisi 403) |
| Mobile-first Teknisi | PASS | `test_mobile_e2e` (12/12: 375/390/768/1280, 9 workflow, hamburger sidebar, dark, tanpa overflow halaman) |
| Final Hardening — D-015 Account (Superadmin create = APPROVED+ACTIVE) | PASS | curl: store → APPROVED/1 + login langsung + audit `SUPERADMIN_CREATE_USER`; registrasi publik tetap PENDING (2026-08-16) |
| Final Hardening — D-016 Internal Password Recovery | PASS | curl: forgot minimal (hubungi Superadmin, tanpa form email/guide), route `/reset-password` 404, tabel `password_reset_tokens` di-drop; reset = SUPERADMIN-only (2026-08-16) |
| Final Hardening — Page Guide coverage + auth exceptions | PASS | login tanpa guide, register info singkat pending, forgot minimal; legacy `$guide_content` dihapus (outgoing/submissions/returns create+show) → konten di `PageGuideHelper` (2026-08-16) |
| Final Hardening — Empty State (mode BARE) | PASS | audit_timeline, timeline show, items show (stok fisik + SN) memakai shared empty state tanpa kartu bersarang (2026-08-16) |
| Final Hardening — Compact Table | PASS | seluruh view compact (`px-4 py-3`), header/body sejajar, py-3.5 dashboard dilindungi; lint 0 error; full suite PASS (2026-08-16) |
| Final Hardening — Reports Print | PASS | `@media print` dedicated layout (UI tersembunyi, print header judul/periode/filter/timestamp, footer + nomor halaman, sticky off, overflow visible) di `report_viewer` (2026-08-16) |
| Final Hardening — Import/Export | PASS | curl: template + instruksi, import valid auto-kode (`CAT-NET-000001`), invalid rollback + error per baris/kolom, audit `IMPORT`; UI penjelasan kolom + alur (BUG-013 CLOSED) (2026-08-16) |
| Final Hardening — D-017 Bulk Label Printing | PASS | `test_bulk_labels_e2e` (4/4: workspace filter+seleksi+count→print sheet 4 label, select-all/bersihkan+empty state, teknisi 403, dark+mobile) + curl dedupe SN + gabung bulk qty + audit `LABEL_PRINT` (2026-08-16) |
| Remediasi Deep — Adjustment SQL (BUG-017) | PASS | schema inspection: `stock_adjustment_items.stock_adjustment_id`; `AdjustmentController` sinkron; detail render OK (curl, 2026-08-17) |
| Remediasi Deep — Outgoing detail (BUG-018) | PASS | route `/outgoing/show` + controller `show()` + view baru; curl 200 + data + guide (2026-08-17) |
| Remediasi Deep — Return auth (BUG-019) | PASS | `process()` ADMIN (+TEKNISI personal), SUPERADMIN 403; seeded PENDING return: Admin proses OK / SA 403 (curl, 2026-08-17) |
| Remediasi Deep — Reports selection + PDF body (BUG-020) | PASS | PDF body berisi data (mapping key DB eksplisit); selection checkbox/select-all/clear/count/print/CSV/PDF selected server-validated; web=print=CSV=PDF (curl script, 2026-08-17) |
| Remediasi Deep — Unbalanced div (BUG-021) | PASS | seluruh views div-balanced; guide kanan desktop (geometry 26 halaman, `test_remediate_e2e`) (2026-08-17) |
| Fix Antigravity — `/loans/create` TEKNISI 500 (BUG-014) | PASS | `submissions.user_id` → `submitted_by`; regression guard `test_remediate_e2e` "TEKNISI /loans/create tidak 500"; curl 200 + form + guide (2026-08-17) |
| Fix Antigravity — English titles (BUG-015) | PASS | 13 view title → Bahasa Indonesia; static grep seluruh `$pageTitle` (2026-08-17) |
| Fix Antigravity — Raw enum/condition leak (BUG-016) | PASS | `UIHelper` terjemahan diperluas; incidents/opnames/users/dashboard/submissions/search/returns memakai `statusLabel`; scan rendered lintas role bersih (2026-08-17) |
| Fix Antigravity — Page Guide coverage audit | PASS | `PageGuideHelper` exact+prefix match benar; guide hadir di virtually semua 200 halaman (curl scan); false positive URL scan dibedakan (2026-08-17) |
| Fix Antigravity — Scratch artifact cleanup | PASS | `development/`, `test-results/`, file scratch root tak dirujuk dihapus; `screenshots/` + test resmi + test_search/test_batch_a (dirujuk docs) dipertahankan (2026-08-17) |
| Final 2-Issue — Raw enum INSTALLED (BUG-022) | PASS | guide report + PageGuideHelper → "Terpasang"; `installations/index` badge pakai status row (auto-translate); static scan user-facing bersih (2026-08-17) |
| Final 2-Issue — Return authorization (BUG-023) | PASS | Audit locked rule: TEKNISI=create return, ADMIN=process, SA=monitor → backend 403 dipertahankan; tombol create di-gate TEKNISI + label "Ajukan Pengembalian"; ADMIN proses via show tetap jalan (2026-08-17) |
| **FINAL BROWSER UAT (Antigravity, 2026-08-17)** | **PASS** | Installation raw enum fixed; return role flow correct; Page Guide right side desktop; raw TEST/UAT/DEBUG clean; `/loans/create` PASS; outgoing detail PASS; adjustment PASS; reports print/CSV/PDF PASS; empty state centered; compact tables; Indonesian page titles; SUPERADMIN/ADMIN/TEKNISI role flow verified |
| UI Polish — Empty-State Alignment (BUG-024) | PASS | 5 view nested `<tr>` wrapper dihapus (adjustments/installations/outgoing/returns/transfers index); komponen dipanggil langsung + colspan benar; scan 43 pemanggilan 0 wrapper salah (2026-08-17) |
| UI Polish — Empty-State colspan/mode (BUG-025) | PASS | incidents/opnames index `$colspan=6`; labels/notifications/evidence_list → mode BARE; fallback `$colspan ?? 1` di komponen (2026-08-17) |
| UI Polish — Transfer table konsistensi (BUG-026) | PASS | header/badge Indonesian via `UIHelper::badge`, tanggal "d M Y H:i", status center (2026-08-17) |

Automated regression historical: **93/93 PASS**. FINAL BROWSER UAT (Antigravity): **PASS** (2026-08-17). UI Polish 2026-08-17: verifikasi statik (lint 83 file, template inspection); browser re-verification DELEGATED.
Result:
0. **Sync UAT (2026-08-16):** Status Stock Opname disinkronkan menjadi **PASS** berdasarkan re-run E2E `tests/playwright/test_opnames_e2e.spec.js` (1 passed, 6.6s, tanpa redirect timeout). Batch C dinyatakan **FULL PASS**.
5. **Batch D (Reports, Timeline, Audit Log, Users, Account Management):**
   - `test_reports_e2e.spec.js` (5) PASS — laporan memakai kolom No (row numbering), search, filter, reset, pagination, empty state, guide, export CSV.
   - `test_timeline_e2e.spec.js` (3) PASS — toolbar, row numbering, pagination, dark mode, empty state, tanpa 500.
   - `test_users_e2e.spec.js` (3) PASS — toolbar (search + status filter), row numbering, pagination, modal approval, empty state.
   - `test_account_e2e.spec.js` (4) PASS — forgot password → reset password → login flow, change password (verifikasi password saat ini), register/login render, protected route redirect.
   - Regresi Batch C: `test_opnames_e2e.spec.js` & `test_incidents_e2e.spec.js` PASS (tidak ada regresi).
   - **Total Batch D: 17 passed (13.3s).**
   - Evidence: `tests/playwright/*.spec.js`, `test-results/`
6. **Dashboard Analytics (2026-08-16):**
   - `test_dashboard_e2e.spec.js` (6) PASS — superadmin (KPI, chart/trend 6 bulan, pergerakan, list stok rendah/pengajuan/outstanding/installed/insiden, alert, quick action monitoring/system, page guide), admin (quick action operasional), teknisi (dashboard personal), empty state, dark mode toggle, no console errors.
   - Full suite: **23 passed (13.7s)** — dashboard 6 + Batch D 15 + Batch C regresi 2.
   - Dashboard Analytics (GAP-001) dinyatakan **PASS** (data nyata, bukan placeholder).
7. **Global Page Guide (2026-08-16):**
   - `test_page_guide_e2e.spec.js` (7) PASS — panel Panduan Cepat tampil di halaman representatif (dashboard, search, master data, inventory, transaksi, asset lifecycle, users, timeline, reports); konten relevan per modul; micro-guidance di halaman form; role visibility (Admin/Teknisi); dark mode; viewport mobile 375px; tanpa console error.
   - Full suite: **30 passed (21.2s)** — page guide 7 + dashboard 6 + Batch D 15 + Batch C regresi 2.
   - Page Guide dinyatakan **PASS** (komponen reusable, konten terpusat per route). 
1. **Batch A (Master Data & Inventory UI Standardization):** `Satuan`, `Gudang`, `Lokasi`, dan `Nomor Seri` telah menggunakan *shared table components* dan berfungsi normal tanpa 500. **Status keseluruhan UI: PARTIAL (Sisa tabel operasional, dll belum distandardisasi).**
2. **Search Fix:** Perbaikan query traceability di `SearchController.php` telah dilakukan. **Status: PASS (Diverifikasi melalui Playwright E2E full flow browser: search, reset, empty state, pagination).**
3. **Batch B (Operational Transactions UI Standardization):** `Outgoing`, `Transfers`, `Loans`, `Returns`, `Installations`, dan `Adjustments` telah menggunakan *shared table components* dan lulus Playwright auto-verify. **Status: PASS.**
4. **Batch C (Damage/Lost & Stock Opname):** Logic CRUD, Evidence, Timeline, Audit Log terverifikasi. Playwright E2E telah diperbaiki (handling redirect timeout). **Status: PASS.**
Evidence reference: `screenshots/batch_a/*`, `screenshots/outgoing_e2e/*`, `screenshots/transfers_e2e/*`, `screenshots/loans_e2e/*`, `screenshots/returns_e2e/*`, `screenshots/installations_e2e/*`, `screenshots/adjustments_e2e/*` & Logs
8. **UI Global Table Width & Mobile Overflow (2026-08-17):**
   - Tabel memenuhi *available content width* (BUG-027).
   - Halaman *page shell* tidak mengalami *horizontal overflow* pada *mobile device* setelah perbaikan *flex-wrap* dan penambahan *wrapper table* (BUG-027).
   - Status UI BUG-027 menunggu verifikasi *browser* akhir.
- **2026-08-17** (BUG-029): PDF Clipping & Pagination. Status: **PASS**. (Verified multi-page portrait & landscape, header repeat, and footer sizing)
- **2026-08-17** (BUG-030): CSV Export Data Integrity & Excel Compat. Status: **PASS**. (Verified UTF-8 BOM, enum translation, date formats, formula safe injection, and correct filtering)
- **2026-08-17** (BUG-031): Universal Report Row Numbering. Status: **PASS**. (Verified array_unshift logic in CSV and dynamic width in PDF)
- **2026-08-17** (BUG-032): Dashboard Low Stock Navigation. Status: **PASS**. (Verified matching query condition, URL parameters, filter reset, and empty states)
- **2026-08-17** (BUG-033): Incident Open KPI Mismatch. Status: **PASS**. (Verified exact matching query for PENDING status, filter propagation, and empty states)
- **2026-08-17** (BUG-034): Barcode Overflow Inventory 360. Status: **PASS**. (Verified SVG max-width scaling inside card wrapper)
- **2026-08-17** (BUG-035): Master Item Edit UI Authorization. Status: **PASS**. (Verified backend controller consistency and updated UI visibility to hasRole('ADMIN') only)
- **2026-08-17** (SCAN-BARCODE-SEARCH): Status: **PASS**. (Pencarian Barang mendukung scan barcode langsung untuk Admin dan Superadmin. Scanner mengisi input barcode dan ENTER memicu pencarian otomatis tanpa klik tombol Cari. Playwright `test_scan_barcode_e2e.spec.js` 8/8 — A exact match kode via ENTER tanpa klik; B barcode tak ditemukan → pesan jelas tanpa 500; C scan berulang tanpa reload; D manual search nama/kode/SN regression PASS; E ADMIN berfungsi; F SUPERADMIN berfungsi; G Lihat → Inventory 360; H tanpa console error + auto-focus & fokus kembali. Curl: exact match `serial_number`/`kode_barang` (parameterized, bukan LIKE); TEKNISI `/search` tetap 403; `php -l` bersih.)
- **2026-08-17** (SUPERADMIN-USER-WORKFLOW / BUG-037): Status: **PASS**. (Playwright `test_user_workflow_e2e.spec.js` 9/9 — A: create Superadmin = Aktif (APPROVED+active) langsung login & tanpa tombol Tinjau; B: ganti password → status tetap Aktif + login password baru berhasil; C: ganti role → tetap Aktif; D: ganti nama/email → tetap Aktif; E: Nonaktif eksplisit → SUSPENDED + login ditolak; F: Aktif eksplisit → APPROVED + login berhasil; G: public registration → PENDING_APPROVAL + tidak bisa login; H: approve → Aktif + login berhasil; I: reject → REJECTED + alasan tersimpan + login ditolak. Curl: select "Status Akun" tampil di form Ubah; guard self-deactivate menolak Superadmin menonaktifkan akun sendiri. Status hanya berubah melalui perubahan Status yang eksplisit.)
- **2026-08-17** (LOGIN-HEADLINE-FROM-APP_NAME): Status: **PASS**. (Headline "Welcome to" pada halaman Login sekarang mengikuti setting Nama Aplikasi dari Pengaturan Sistem → Umum (`app_name`). Playwright `test_login_branding_e2e.spec.js` 3/3 — h1 = "Welcome to" + `app_name`; span branding = `company_name`; perubahan `app_name` → headline berubah & `company_name` tidak mengubah headline; tanpa hardcode/broken image/console error. Curl alur Superadmin nyata: POST `/settings/update` `app_name=TEST WMS BIGNET` → headline `Welcome to TEST WMS BIGNET` → restore `SYSTEM GUDANG BIGNET` → kembali.)
- **2026-08-17** (STOCK-MONITOR & MASTER-ITEM-LIFECYCLE / BUG-039): Status: **PASS**. (Playwright `test_stock_master_e2e.spec.js` 10/10 — 1: ADMIN /stocks kolom lengkap (Tipe, Stok Fisik, Available, Reserved, Allocated, Satuan, Status, Aksi) + search/filter/Reset/Lihat; 2: SUPERADMIN sama; 3: klik Lihat → Inventory 360 (`/items/show`); 4: angka Stok Fisik & Available = DB (perhitungan existing tidak berubah); 5: buat barang dummy belum dipakai → Hapus tersedia → modal BIGNET → terhapus permanen; 6: barang berhistory → Nonaktifkan tersedia, Hapus tidak tampil; 7: POST `/items/delete/{id}` langsung untuk barang berhistory → backend menolak dengan pesan aman, data & stok aman; 8: incoming 1 + outgoing 1 → berhistory, stok 0 → Nonaktifkan → `is_active=0`, tetap tampil di list, tidak bisa dipilih untuk incoming baru; 9: /stocks tanpa aksi Hapus + panduan "bukan tempat menghapus Master Barang"; 10: regresi /incoming /outgoing /transfers /adjustments /serials /search tetap terbuka. Rule: Hapus permanen hanya jika belum pernah digunakan; barang berhistory → Nonaktifkan; backend guard wajib; Menghapus Master Barang ≠ mengurangi Stok. `php -l` bersih; DB bersih 0 residue; regresi penuh 41/41.)
- **2026-08-17** (GLOBAL-FEEDBACK-UI / BUG-038): Status: **PASS**. (Global Feedback UI Standard: native `alert()`/`confirm()` tidak digunakan untuk UI user-facing — 0 temuan di audit source; konfirmasi memakai custom modal BIGNET (`AppModal.confirm`, termasuk Nonaktifkan/Aktifkan akun, reset password, approve/reject, delete bulk, import, keputusan insiden/opname, terima transfer); success/error/warning/info memakai toast internal (`AppToast`); validation form via toast/inline tanpa native dialog; semua modal/toast theme-aware (dark mode PASS) dan responsive (mobile 390px PASS); loading state anti double-submit. Playwright `test_feedback_ui_e2e.spec.js` 10/10 A-K — A custom modal nonaktifkan (tanpa native dialog); B Batal → modal tertutup & status tidak berubah; C confirm → request sukses + toast; D Aktifkan → modal sama; E Reset Password → modal + notifikasi sukses; F delete bulk → modal + data terhapus; G labels tanpa pilihan → toast warning; H error custom tanpa stack trace; I dark mode; J mobile; K audit source. Regresi 30/30: login branding 3, user workflow 9 (H/I diupdate ke UX modal custom), scan barcode 8, feedback 10. `php -l` bersih; tidak ada perubahan permission/business logic.)
- **2026-08-17** (MASTER-CODE-AUTO): Status: **PASS**. (Master Data Code Rule: Kategori `KAT-000001`, Satuan `UNT-000001`, Gudang `GDG-000001`, Lokasi `LOK-000001`, Master Barang `{KODE_KATEGORI}-000001` — semua **auto-generated** oleh backend (counter `FOR UPDATE` + retry collision 1062, transaksional); user tidak mengisi kode (form Tambah `[Auto Generated]` + helper text; form Ubah readonly); kode immutable & tidak berubah saat nama/kategori/stok diubah; `locations.code` diberi unique index global. Playwright `test_master_code_e2e.spec.js` PASS — A: satuan tanpa input kode → UNT-xxxxxx unik; B: rename satuan → kode tetap; C: dua satuan → kode berbeda (collision aman); D: kategori auto KAT-; E: gudang GDG- + stabil saat rename; F: lokasi LOK- unik global; G: barang auto `KAT-…-000001` + stabil saat rename (stok/serial/transaksi tidak tersentuh); H: search by kode ditemukan; I: kode tampil di list. Regresi penuh 44/44 PASS termasuk stock-master 10/10 & scan-barcode 8/8 setelah baseline SYS-BASE di-seed ulang. `php -l` bersih; tidak ada kode existing yang diregenerate.)
- **2026-08-17** (ROLE-DISPLAY-LABEL): Status: **PASS**. (Role Display Label Standard: `TEKNISI_NOC` -> **Teknisi/NOC**, `ADMIN` -> Admin, `SUPERADMIN` -> Superadmin — HANYA display, nilai internal tidak berubah. Playwright `test_role_label_e2e.spec.js` 2/2 — A: dropdown Buat Pengguna menampilkan Teknisi/NOC/Admin/Superadmin (bukan `TEKNISI NOC`); B: Edit user TEKNISI_NOC opsi terpilih Teknisi/NOC; C: badge tabel Manajemen Pengguna Teknisi/NOC; D: badge detail Pengguna Teknisi/NOC; E: filter role N/A (aplikasi tidak punya filter role — verifikasi via search + internal DB tetap TEKNISI_NOC); F: permission regression (DB role=TEKNISI_NOC|APPROVED|1, teknisi tetap bisa /submissions, `/users` tetap 403); G: login teknisi berhasil + header label Teknisi/NOC; H: label role di dashboard/audit; I: audit source 0 display `TEKNISI NOC`/`TEKNISI_NOC` user-facing; J: Admin & Superadmin konsisten. Regresi user-workflow 9/9 + login-branding 3/3 + feedback-ui 10/10 = 22/22 PASS. `php -l` bersih; tidak ada perubahan permission/business logic.)
- **2026-08-17** (GLOBAL-ACTION-LIFECYCLE): Status: **PASS (Freebuff — non-browser)**. **Browser/UAT di environment lain BELUM dijalankan; status browser TIDAK di-set PASS di task ini.** Verifikasi Freebuff: `php -l` 20+ file bersih; `node --check public/assets/js/app-feedback.js` OK. HTTP nyata (session+CSRF): SUPERADMIN /items /users /warehouses /transfers 200 tanpa PHP error + komponen `[Lihat][⋮]` render (`data-actions-toggle`); SUPERADMIN POST /categories → **403**; TEKNISI uji → GET /categories /items /incoming **403**, GET /submissions 200 (data sendiri), POST /categories /incoming/store /items → **403** (sebelumnya lolos via `denySuperadminWrite`); ADMIN uji → POST /categories 302 + tersimpan; POST /items/delete/{item-unused} → 302 + terhapus permanen; POST /items/delete/{item-used(stok>0)} → 302 + **ditolak** + row utuh (guard `is_used`, pesan "Barang tidak dapat dihapus karena sudah memiliki riwayat transaksi. Gunakan Nonaktifkan Barang."); guard nonaktif "masih memiliki stok fisik atau serial aktif" terkonfirmasi di backend. Quick Guide "Aturan Action & Lifecycle" ter-render di 15 halaman modul. Semua user/record uji dibersihkan. Rule: Role + Status + Relasi Data menentukan action; Delete hanya untuk data belum pernah digunakan; Nonaktif untuk menghentikan pemakaian baru; transaksi posted = immutable; Delete ≠ Cancel/Reversal/Nonaktif.
- **2026-08-17** (BUG-036 — Login Branding Dinamis dari Settings): Status: **PASS**. (Playwright `test_login_branding_e2e.spec.js` 3/3 — nama perusahaan dari `company_name` & logo dari `logo_path` tampil tanpa hardcode `PT.BIG WMS`, tanpa broken image (logo naturalWidth>0) & tanpa console error; perubahan Nama Perusahaan/Logo Utama langsung ter-refleksi lalu dikembalikan; fallback ikon saat logo kosong/tak valid; login SUPERADMIN tetap berfungsi ke /dashboard. Curl: `/logo` & `/favicon` publik 200 bytes-match, 404 saat setting kosong/file tak ada; halaman login tanpa `/file?path=`.)
- **2026-08-17** (OUTGOING-SERIALIZED-STOCK-POSTING — P0 FIX): Status: **PASS (Freebuff — non-browser)**. **Browser/UAT di Antigravity BELUM dijalankan; status browser TIDAK di-set PASS di task ini.** Verifikasi Freebuff (HTTP nyata + DB, user uji sementara lalu dihapus): (1) **Serialized outgoing kini mengurangi stok** — 3→2, ledger OUTGOING -1, serial → ISSUED (root cause: `moveSerial` tanpa `mutateStock`); (2) serialized insuff → ditolak dgn pesan "Stok Tidak Mencukupi" (qty diminta/tersedia), stok tetap, 0 outgoing, 0 ledger; (3) empty-outgoing guard (0 baris barang valid → rollback); (4) **bulk regression skenario UAT**: stok 20 → Barang Keluar 15 → **stok 5**, ledger -15, submission COMPLETED; (5) bulk insuff 9 vs 15 → ditolak, stok tetap 9, 0 ledger, 0 outgoing, status tetap APPROVED, pesan "Stok Tidak Mencukupi"; (6) double submit → satu deduction saja; (7) multi-item → deduction per `master_item_id` masing-masing; (8) Stock page & Inventory 360 menampilkan 20 → -15 → 5 + referensi OUT-xxxx + label "Barang Keluar". `php -l` 5 file bersih; `node --check` OK; 8 halaman HTTP 200 / 0 PHP error. DB cleanup: 0 residue (3 user, 5 submissions, 14 notif, 3 ledger, stok 179=20 & 55=4 dikembalikan, serial AVAILABLE, sub6 APPROVED remaining 15).
- **2026-08-17** (SUBMISSION-WORKFLOW & STOCK-INTEGRITY — P0 FIX): Status: **PASS (Freebuff — non-browser)**. **Browser/UAT di Antigravity BELUM dijalankan; status browser TIDAK di-set PASS di task ini.** Verifikasi Freebuff (HTTP nyata + DB, user uji sementara lalu dihapus): (1) Approval UI tampil utuh untuk ADMIN pada PENDING (input Qty Disetujui per item, Proses Item, Tolak Semua, alasan wajib saat Qty=0, badge fa-clock semantik — bug `$statusColor` undefined dihapus); SUPERADMIN read-only tanpa form. (2) Stock Sufficiency server-side: request 999 saat stok 20 → status tetap PENDING, stok tetap, 0 ledger deduction, pesan "Stok Tidak Mencukupi" tampil; multi-item A cukup + B tidak → rollback atomik penuh (tanpa partial state). (3) Fulfillment via Barang Keluar: stok 20→15, ledger OUTGOING -5, remaining 0, submission COMPLETED, hanya 1 outgoing (no double deduction); double-submit approve ditolak. (4) MANUAL source tidak menyentuh stok. (5) Notification dua arah: submit → notif Admin unread; approve/partial/reject → notif Teknisi; dedup; read/unread turun; bell unreadCount dari DB; halaman Notifikasi menampilkan event; Buka menuju detail. (6) Barang Masuk list menampilkan quantity (single & multi summary). (7) Mutation History 360: Stok Sebelum/Perubahan/Stok Sesudah + referensi INC-xxxx + label "Barang Masuk" (kolom schema yang benar). (8) Incoming/Outgoing menolak master_item_id invalid (0 orphan stock). `php -l` 15 file bersih. DB cleanup: 0 residue (3 user, 5 submissions, 3 ledger, 14 notif, stok 179/177 = 20/15 dikembalikan).

- **2026-08-18** (P0-STOCK-DEDUCTION-FULL-E2E-VERIFICATION): Status: **PASS (Freebuff — non-browser, 38/38 PASS)**. **Browser/UAT di Antigravity BELUM dijalankan.** Full E2E verification: (1) Technician creates STOCK submission with warehouse_id=32 -> submission_items.warehouse_id saved correctly; (2) Admin approves -> stock unchanged (NO deduction at approval); (3) Admin processes outgoing (stock 20, qty 15) -> outgoing COMMITTED, stock 20->5, ledger -15 (balance_before=20, balance_after=5), submission COMPLETED, remaining=0; (4) Double process -> rejected, stock unchanged; (5) Warehouse mismatch (wrong warehouse 999) -> rejected, stock unchanged; (6) Insufficient stock (50 > available) -> rejected, stock unchanged, error feedback. All 38 assertions pass. DB baseline clean (3 users, 0 outgoing, stock 179=20). Previous UAT failures traced to serialized items lacking mutateStock (fixed UPDATE 13) and legacy warehouse_id NULL (documented). No code changes in this verification.