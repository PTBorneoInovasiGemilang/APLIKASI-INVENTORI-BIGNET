# BIGNET PROJECT STATE

> File ini adalah "save game" project. Harus selalu menggambarkan kondisi TERKINI.

## LAST UPDATED
- Date: 2026-09-14
- Updated by: Antigravity — sesi UI STANDARDIZATION & PRODUCTION HARDENING

## CURRENT PHASE
- P0: **UI STANDARDIZATION & PRODUCTION HARDENING COMPLETED (READY FOR PRODUCTION)** — Full UI/UX standardization across all modules (Data Table, Toolbar, Pagination, Empty State, Badges, Modals, Buttons) + Production Hardening (.env loader, SEC-001 DB config, SEC-002 App config, SEC-003 Dynamic HTTPS cookie secure flag, SEC-004 DocumentRoot /public/ compatibility). Verified via HTTP & Browser regression across SUPERADMIN, ADMIN, TEKNISI_NOC with 0 application bugs and 100% backend authorization enforcement.

## CURRENT TASK
- Task: UI-STANDARDIZATION-PRODUCTION-HARDENING
- Objective: Standardize UI/UX across all inventory modules and complete repository-level production security hardening.
- Status: COMPLETED. All PHP files linted clean (0 errors), security regression passed, final report saved to `docs/UI_STANDARDIZATION_PRODUCTION_HARDENING_2026-09-14.md`.


## COMPLETED
- [x] Initial DB Schema Creation
- [x] Basic UI Layout and Sidebar rendering
- [x] Documentation scaffolding (PRD V2, Decisions, Open Decisions, State, Changelog)
- [x] Route registration for all main modules
- [x] Role-based login and basic dashboard display
- [x] Audit Backend Logic for Core Inventory Transactions (Atomic transactions, permission assertions, locking)
- [x] PHASE 1: Created Shared Table Components (Toolbar, Pagination, Empty State)
- [x] Batch A: Master Data & Inventory UI Standardization
- [x] Batch B: Operational Transactions UI Standardization
- [x] Batch C: Asset Lifecycle (Damage/Lost & Stock Opname) UI Standardization & Implementation
- [x] Batch D: Reports UI Standardization (row numbering, search, pagination, guide, export CSV)
- [x] Batch D: Timeline/Monitoring UI Standardization
- [x] Batch D: Audit Log (via report viewer)
- [x] Batch D: Users/System UI Standardization
- [x] Batch D: Account Management (Forgot/Reset Password flow + Change Password hardening)
- [x] Dashboard Analytics: KPI nyata, trend 6 bulan, pergerakan stok, stok rendah, request aktif, outstanding return, installed, damage/lost, alert/anomaly, quick action per role
- [x] Global Page Guide: shared component `components/page_guide.php` + `PageGuideHelper` (konten terpusat per route) + micro-guidance form + dark mode + integrasi auth pages
- [x] Global Dark Mode: seluruh `resources/views` bebas pola light-only (audit_timeline, timeline/show, toolbar Reset, items/show, transaksi show/create, users forms, modal avatar); file mati reports/audit.php & ledger.php dihapus; parse error audit_timeline.php diperbaiki
- [x] Role-Based UI: sidebar per role (SUPERADMIN monitoring/system, ADMIN operasional, TEKNISI personal) + otorisasi backend konsisten (403 untuk route langsung tidak sah)
- [x] Global Table Layout Rule: shared CSS `.table-sticky` (No kiri & Aksi kanan sticky, background solid, empty-state dikecualikan); fix 2 tabel terpotong (incoming/index, submissions/show → `overflow-x-auto`); 5 tabel `w-full` → `min-w-full`; 11 tabel lebar diberi min-width + sticky; report_viewer min-width dinamis ≥ 6 kolom
- [x] Refinement Page Guide: users/edit.php inline guide kiri dihapus → panel kanan layout; PageGuideHelper role-aware (`roles`/`roles_exclude`) + section ber-role di dashboard/serials/timeline/submissions/loans/installations
- [x] Empty State shared: `$emptyIcon`/`$emptyIconColor`, py-16 + align-middle, max-w-md, terpusat horizontal+vertikal, light/dark/mobile
- [x] CRUD/Bulk Action: shared `components/table/bulk_actions.php` (checkbox, select-all, count, confirm, loading, flash) + `bulkDelete()` backend (kategori, satuan, gudang, lokasi) dengan guard referensi + audit log DELETE; Superadmin read-only (403)
- [x] Role Reconciliation: SUPERADMIN read-only diperketat (adjustments/incidents/opnames/returns/transfers — UI + Router 403 + denySuperadminWrite); ADMIN_GUDANG (dead role) → ADMIN di Incident/StockOpname controller & view; approval variance opname tetap SUPERADMIN-only (D-014); sidebar Insiden & Opname untuk ADMIN + SUPERADMIN
- [x] Empty State global: mode ROW+PANEL di komponen shared; loans double-wrapper diperbaiki; search/report/timeline no-result pakai panel shared; 8 tabel detail dapat empty state
- [x] Account/User Management lengkap: user detail + riwayat audit, status-aware actions (Tinjau/Nonaktifkan/Aktifkan/Reset Password), route baru, approve/reject kembali ke halaman asal
- [x] Notification Center: tabel `notifications` + helper user-isolated + bell unread count + pusat notifikasi (mark read/all) + event hooks (register→SA, approve/reject/toggle→user, opname→SA)
- [x] Live Search: endpoint JSON `?format=json` + JS debounce 350ms, loading/error/empty, URL sinkron, filter live
- [x] Barcode/QR: `BarcodeHelper::code128` (murni PHP, diverifikasi vs JsBarcode) + QR client-side (qrcode-generator di-vendor) + `/labels` (print sheet + batch) + `/scan` (lookup) + integrasi items/serials + Page Guide
- [x] Evidence/Attachment: `AttachmentHelper` (MIME+size, secure storage, authz) + `EvidenceController` (/evidence view+delete) + shared `components/evidence_list.php` + integrasi incidents + BUG-012 (link evidence lama /file 404) diperbaiki
- [x] Export PDF: `PdfHelper` murni PHP (A4, tabel multi-halaman, nomor halaman, xref valid) + `?export=pdf` (filter mengikuti laporan) + tombol Export PDF
- [x] Mobile-first Teknisi: menu Lapor Kerusakan/Kehilangan di sidebar; verifikasi 375/390/768/desktop seluruh workflow tanpa overflow
- [x] FINAL HARDENING — D-015 (superadmin create = APPROVED+ACTIVE, audit `SUPERADMIN_CREATE_USER`); D-016 (internal password recovery: forgot minimal + hapus alur email/token, drop `password_reset_tokens`, reset SUPERADMIN-only); D-017 (bulk label: `/labels/create` filter+seleksi+count, `POST /labels/print` dedupe+audit `LABEL_PRINT`, `bulk=KODE:qty`, sidebar "Cetak Label Massal" SA+ADMIN)
- [x] FINAL HARDENING — Page Guide: login tanpa guide, register info singkat, forgot minimal; legacy `$guide_content` dihapus (outgoing/submissions/returns create+show → konten pindah ke `PageGuideHelper` route spesifik)
- [x] FINAL HARDENING — Empty State mode BARE (in-card tanpa kartu bersarang) di audit_timeline/timeline show/items show
- [x] FINAL HARDENING — Compact Table: sel `px-6 py-4`→`px-4 py-3`, header `px-6 py-3`→`px-4 py-3` (py-3.5 dashboard dilindungi)
- [x] FINAL HARDENING — Reports dedicated print layout (@media print sembunyikan UI, print header/footer + nomor halaman, overflow visible, sticky off)
- [x] FINAL HARDENING — Import/Export: template + instruksi + contoh, auto-kode, validasi ID/enum, error per baris+kolom, UI penjelasan kolom + alur import (BUG-013 CLOSED)
- [x] REMEDIASI DEEP — Adjustment SQL sinkron ke `stock_adjustment_id` (BUG-017); Outgoing detail route+controller+view (BUG-018); Return `process()` ADMIN-only + SA 403 (BUG-019); Reports selection toolbar + PDF body mapping key DB (BUG-020); 6 view unbalanced div diperbaiki + guide kanan (BUG-021)
- [x] FIX ANTIGRAVITY — `/loans/create` `submissions.user_id` → `submitted_by` (BUG-014, 500 TEKNISI); 13 English page titles → Indonesia (BUG-015); raw enum/status/condition → `UIHelper::statusLabel` (BUG-016); Page Guide coverage audit (helper benar, guide hadir di virtually semua halaman 200); scratch artifacts QA dibersihkan (development/, test-results/, file root tak dirujuk)
- [x] FINAL 2-ISSUE — raw enum INSTALLED di guide report + PageGuideHelper → "Terpasang", installations/index badge pakai status row (BUG-022); return authorization audit: backend 403 ADMIN create dipertahankan (locked rule TEKNISI=create/ADMIN=process), tombol create di-gate TEKNISI + label "Ajukan Pengembalian" (BUG-023)
- [x] UI POLISH — empty-state nested `<tr>` wrapper dihapus di 5 view index transaksi (BUG-024); colspan=6 incidents/opnames + mode BARE labels/notifications/evidence + fallback `$colspan ?? 1` (BUG-025); transfers header/badge/tanggal Indonesian (BUG-026)

## NOT STARTED
- [ ] OD-003 — Action Permission Detail (termasuk nasib role ADMIN_GUDANG) — menunggu Product Owner
- [ ] OD-004 — Maintenance / Service (PROPOSED, tidak dikerjakan sesuai instruksi)
- [ ] OD-005 — Disposal / Penghapusan Aset (PROPOSED, tidak dikerjakan sesuai instruksi)
- [ ] OD-006 — Akses TEKNISI ke pencarian/live search & barcode — menunggu Product Owner
- [ ] OD-008 — Migrasi evidence lama ke secure storage + Export Excel native (XLSX) — menunggu Product Owner

## VERIFIED
- [x] Scan Barcode Pencarian Barang (`test_scan_barcode_e2e.spec.js` 8/8: A exact match kode via ENTER tanpa klik Cari; B barcode tak ditemukan → pesan jelas tanpa 500; C scan berulang tanpa reload; D manual search (nama/kode/SN) regression; E ADMIN scanner berfungsi; F SUPERADMIN scanner berfungsi; G Lihat → Inventory 360; H tanpa console error, auto-focus saat dibuka & fokus kembali setelah scan) — 2026-08-17
- [x] Curl: `/search?barcode=...` exact match (serial 1 hasil, kode serialized 5 SN, bulk berstok 1) & not-found 0; TEKNISI `/search` tetap 403 — 2026-08-17
- [x] Superadmin User Workflow (`test_user_workflow_e2e.spec.js` 9/9: A create=AKTIF+login; B password → status tetap Aktif + login password baru; C role → tetap Aktif; D nama/email → tetap Aktif; E Nonaktif eksplisit → SUSPENDED + login ditolak; F Aktif eksplisit → APPROVED + login OK; G public registration PENDING + tidak bisa login; H approve → Aktif + login OK; I reject → REJECTED + alasan tersimpan) — 2026-08-17
- [x] Curl: form edit menampilkan select "Status Akun"; guard self-deactivate (superadmin status=0 pada diri sendiri → ditolak, tetap APPROVED) — 2026-08-17
- [x] Login headline "Welcome to [Nama Aplikasi]" (`test_login_branding_e2e.spec.js` 3/3: h1 = Welcome to + `app_name`, span branding = `company_name`, tanpa hardcode/broken image/console error; perubahan app_name/company_name/logo ter-refleksi lalu dikembalikan; fallback ikon; login tetap berfungsi) — 2026-08-17
- [x] Curl alur Superadmin nyata: POST `/settings/update` app_name=`TEST WMS BIGNET` → headline login berubah; restore `SYSTEM GUDANG BIGNET` → kembali — 2026-08-17
- [x] Login branding dinamis dari settings (`test_login_branding_e2e.spec.js` 3/3: nama+logo dari settings tanpa hardcode & tanpa broken image/console error; perubahan nama & logo ter-refleksi lalu dikembalikan; fallback ikon saat logo kosong/tak valid; login tetap berfungsi) — 2026-08-17
- [x] Curl: `/logo` & `/favicon` publik 200 (bytes match file); `/logo` 404 saat setting kosong/file tidak ada; login page tanpa referensi `/file?path=` — 2026-08-17
- [x] Dashboard UI — Playwright snapshot — 2026-08-16
- [x] Sidebar Navigation — Playwright snapshot — 2026-08-16
- [x] Database Tables — Command inspection — 2026-08-16
- [x] Request & Transaction Flows — Code Audit — 2026-08-16
- [x] Stock & Serial Integrity (Atomicity, FOR UPDATE lock) — Code Audit — 2026-08-16
- [x] Timeline & Audit Log event creation — Code Audit — 2026-08-16
- [x] Global Table UI Audit — 2026-08-16
- [x] Table UI Phase 2 POC (Search, Filter, Pagination, Empty State) — Playwright test — 2026-08-16
- [x] Search Traceability Bugfix (Serial Movements -> Outgoing, and SQL syntax fixes) — Playwright E2E — 2026-08-16
- [x] Table UI Batch A (Master Data & Inventory) — Playwright test — 2026-08-16
- [x] Table UI Batch B (Operational Transactions) — Playwright test — 2026-08-16
- [x] Table UI Batch C (Asset Lifecycle: Damage/Lost & Stock Opname) — Playwright test — 2026-08-16
- [x] Stock Opname E2E re-run (`test_opnames_e2e.spec.js`) — PASS — 2026-08-16
- [x] Table UI Batch D (Reports, Monitoring, System) — Playwright test — 2026-08-16
- [x] Account Management E2E (forgot/reset/change password, protected route) — Playwright test — 2026-08-16
- [x] Dashboard Analytics E2E (KPI, chart, activity, alert, empty state, role visibility, dark mode, console) — Playwright test — 2026-08-16
- [x] Global Page Guide E2E (panel di halaman representatif, teks per modul, role visibility, micro-guidance, dark mode, responsive, console) — Playwright test — 2026-08-16
- [x] Global Dark Mode & Role-Based UI E2E (sidebar per role, 403 route langsung tidak sah, dark mode tabel/form/detail/audit timeline, mobile 375px, no console error) — Playwright test — 2026-08-16
- [x] Global Table Layout Rule E2E (desktop scroll terkontrol + alignment, mobile 375px sticky No/Aksi + tanpa overflow halaman, empty state, halaman terpotong kini scrollable, dark mode sticky bg) — Playwright test — 2026-08-16
- [x] Refinement E2E (guide kanan desktop / bawah mobile / edit user, empty state terpusat, CRUD per role + 403 route langsung, bulk select-all/confirm/delete/success + guard terpakai, dark mode) — Playwright test — 2026-08-16
- [x] Role & Page UX Reconciliation E2E (SUPERADMIN tanpa mutation button + 403 route, ADMIN insiden/opname create, TEKNISI lapor tanpa proses + 403 opname, empty state shared terpusat, guide auth pages, dark mode) — Playwright test — 2026-08-16
- [x] Batch C re-role E2E (incidents: teknisi melapor → admin proses; opnames: admin rekam → superadmin approve variance) — Playwright test — 2026-08-16
- [x] Account/User Management E2E (`test_account_e2e` 3/3: detail + approve/deactivate/reactivate/reset-password + auth negatives, teknisi 403, register tidak bisa SUPERADMIN) — 2026-08-16
- [x] Notification Center E2E (`test_notifications_e2e` 2/2: register→notifikasi SA, approve→notifikasi user, mark read/all, isolation, dark, mobile, no console) — 2026-08-16
- [x] Live Search E2E (`test_livesearch_e2e` 2/2: debounce ketik, empty state, filter live, URL sinkron, Lihat tetap jalan, dark) — 2026-08-16
- [x] Barcode/QR E2E (`test_barcode_e2e` 4/4: Code128 render, QR modal, label sheet + batch, scan lookup, teknisi 403) — 2026-08-16
- [x] Evidence E2E (`test_evidence_e2e` 3/3: upload→metadata→view→delete, teknisi 403 + legacy fix, dark, mobile, no console) — 2026-08-16
- [x] Export PDF E2E (`test_export_e2e` 2/2: unduh PDF valid %PDF-1.4 + %%EOF + filter terbawa, teknisi 403) — 2026-08-16
- [x] Mobile-first Teknisi E2E (`test_mobile_e2e` 12/12: 375/390/768/1280, 9 workflow, hamburger sidebar, dark, tanpa overflow) — 2026-08-16
- [x] Full regression — **82/82 PASS (1.3m)** — 2026-08-16
- [x] Final Hardening targeted — `test_bulk_labels_e2e` **4/4** (workspace+seleksi+count+print sheet, select-all/bersihkan+empty, teknisi 403, dark+mobile) — 2026-08-16
- [x] Final Hardening curl — D-015 store APPROVED+ACTIVE+login langsung+audit; D-016 forgot internal + reset route 404; import valid/auto-kode/rollback; D-017 dedupe SN + gabung bulk + audit `LABEL_PRINT` — 2026-08-16
- [x] Full regression — **86/86 PASS (1.2m)** — 2026-08-16
- [x] Full regression — **93/93 PASS** (historical, sebelum fix Antigravity terakhir) — 2026-08-17
- [x] Remediasi Deep — curl: adjustment detail render (BUG-017); outgoing detail 200 + data (BUG-018); seeded return Admin proses OK / SA 403 (BUG-019); PDF body berisi data + selection count exact (BUG-020); div-balance seluruh views (BUG-021) — 2026-08-17
- [x] Fix Antigravity — curl: `/loans/create` TEKNISI 200 + form + guide (BUG-014); static grep titles (BUG-015) & rendered-page scan enums (BUG-016) bersih; guide coverage script — 2026-08-17
- [x] CODING ONLY lint/schema — `php -l` 0 error seluruh file; schema kanonik `stock_adjustment_id`/`submitted_by` terkonfirmasi — 2026-08-17
- [x] Final 2-Issue — static: lint 4 file OK, enum scan user-facing bersih (sisa = option value internal/translation map/DB insert), route inspection create/store TEKNISI-only + process ADMIN, sidebar/dashboard tanpa link create return — 2026-08-17
- [x] **FINAL BROWSER UAT (Antigravity)** — installation raw enum fixed; return role flow correct; Page Guide right side desktop; raw TEST/UAT/DEBUG clean; loans/create PASS; outgoing detail PASS; adjustment PASS; reports print/CSV/PDF PASS; empty state centered; compact tables; Indonesian page titles; role flow SUPERADMIN/ADMIN/TEKNISI verified — 2026-08-17
- [x] UI Polish statik — php -l 83 file 0 error; deteksi wrapper/colspan 43 pemanggilan 0 masalah; Page Guide wiring tak tersentuh — 2026-08-17

## NOT VERIFIED
- [ ] Edge cases pada concurrent multi-session transactions dalam production.

## ACTIVE BUGS
- [ ] Belum ada bug core logic yang tercatat (Backend PASS).
- [ ] Page Guide global — CLOSED (komponen reusable + konten terpusat terverifikasi).
- [ ] Dashboard Analytics (GAP-001) — CLOSED (chart & monitoring data nyata terverifikasi).

## OPEN DECISIONS
- [ ] OD-003 — Action Permission Detail
- [ ] OD-004 — Maintenance / Service Requirement (Modul belum ada)
- [ ] OD-005 — Disposal / Penghapusan Aset (Modul belum ada)
- [ ] OD-006 — Akses TEKNISI ke pencarian/live search & barcode (konsisten dgn MENU_ROLE_MATRIX saat ini)
- [ ] OD-008 — Migrasi evidence lama ke secure storage + Export Excel native (XLSX)
- OD-007 — **CLOSED (D-015)**: superadmin-created user langsung APPROVED + ACTIVE.

## RECENT CHANGES
- 2026-08-17 — SCAN-BARCODE-SEARCH: Pencarian Barang mendukung scan barcode langsung untuk Admin dan Superadmin. Scanner mengisi input barcode dan ENTER memicu pencarian otomatis tanpa klik tombol Cari. `SearchController@index` menerima `?barcode=` (exact match parameterized pada `serial_numbers.serial_number` / `master_items.kode_barang`, bukan LIKE; filter manual diabaikan di mode scan); view `search/index.php` mendapat panel "Scan Barcode" (input `#scanInput`, auto-focus, Enter/submit → fetch JSON, fokus kembali, pesan "Barcode tidak ditemukan.", URL sync). Manual search tetap utuh; permission tidak berubah (SUPERADMIN/ADMIN; TEKNISI 403). Diverifikasi Playwright `test_scan_barcode_e2e.spec.js` 8/8 (A-H) + curl TEKNISI 403.
- 2026-08-17 — SUPERADMIN-USER-WORKFLOW (BUG-037): `UserController::update()` tidak lagi memaksa `is_active=0` (root cause: form edit tak punya field `is_active` → setiap simpan password/role/nama membuat user Nonaktif). Kini status `is_active`/`account_status` **dipertahankan dari nilai existing** dan hanya berubah bila Superadmin mengubah field "Status Akun" (Aktif/Nonaktif) secara eksplisit (Aktif→APPROVED+active, Nonaktif→SUSPENDED+inactive); guard menolak Superadmin menonaktifkan akun sendiri. Form `users/edit.php` mendapat select "Status Akun" (tanpa redesign). `store()` sudah langsung APPROVED+active (D-015) — tanpa approval; public registration tetap PENDING_APPROVAL. Diverifikasi Playwright `test_user_workflow_e2e.spec.js` 9/9 (A-I) + curl (self-deactivate guard).
- 2026-08-17 — LOGIN-HEADLINE-FROM-APP_NAME: Headline "Welcome to" pada halaman Login sekarang mengikuti setting **Nama Aplikasi** dari Pengaturan Sistem → Umum (`app_name`, fallback `GUDANG PT.BIG`, di-escape). `resources/views/auth/login.php`: h1 headline diganti dari "Selamat Datang di [company_name]" → "Welcome to [app_name]"; `company_name` tetap dipakai untuk branding span/title/mobile; tidak ada hardcode nama aplikasi. Diverifikasi via alur Superadmin asli (settings/update): ubah ke `TEST WMS BIGNET` → headline berubah, restore → kembali. Playwright `test_login_branding_e2e.spec.js` diperbarui → 3/3 PASS.
- 2026-08-17 — LOGIN-BRANDING-FROM-SETTINGS: branding Login kini bersumber dari Nama Perusahaan (`company_name`) + Logo Utama (`logo_path`) pada Pengaturan Superadmin. `resources/views/auth/login.php` (nama dari `company_name` di-escape, logo dari `logo_path` + validasi `is_file`, fallback ikon, tanpa hardcode `PT.BIG WMS`, tanpa `/file?path=`); `StaticController@logo`/`@favicon` (endpoint publik aman: nilai dari settings, basename + whitelist ekstensi + is_file); route `/logo` & `/favicon`. `login_title` tidak lagi jadi sumber nama di view login (field tetap ada di settings). Diverifikasi: Playwright `test_login_branding_e2e.spec.js` 3/3 + curl (ubah nama/logo → refleksi; fallback; auth SUPERADMIN login→dashboard OK).
- 2026-08-17 — Final 2-Issue: raw enum INSTALLED → "Terpasang" di guide report/PageGuideHelper + installations/index badge status row (BUG-022); return create button di-gate TEKNISI, backend 403 ADMIN dipertahankan sesuai locked rule (BUG-023).
- 2026-08-17 — Fix Antigravity: `LoanController` `submissions.user_id` → `submitted_by` (BUG-014); 13 English page titles → Indonesia (BUG-015); raw enum/status/condition leak → `UIHelper::statusLabel` (BUG-016); Page Guide coverage audit (hadir virtually semua halaman; false positives dibedakan); scratch artifacts QA dihapus.
- 2026-08-17 — Remediasi Deep: adjustment SQL → `stock_adjustment_id` (BUG-017); outgoing detail route+view (BUG-018); return process ADMIN-only + SA 403 (BUG-019); reports selection toolbar + PDF body key-DB mapping (BUG-020); unbalanced div 6 view (BUG-021).
- 2026-08-16 — Global Page Guide: shared component `page_guide.php` + `PageGuideHelper` (map route→konten, prefix match, micro-guidance form) + integrasi layout + auth pages (register/login). Seluruh menu relevan kini punya Panduan Cepat konsisten, dark-mode friendly, responsive (Playwright PASS 7/7).
- 2026-08-16 — Dashboard Analytics: KPI real-time + trend 6 bulan (bar chart CSS data nyata) + pergerakan stok + stok rendah + request aktif + outstanding return + installed + damage/lost + alert/anomaly + quick action per role (Playwright PASS 6/6).
- 2026-08-16 — Batch D Reports: pagination + search + row numbering + guide di seluruh laporan (Playwright PASS).
- 2026-08-16 — Batch D Timeline & Users: shared toolbar, pagination, row numbering, dark mode (Playwright PASS).
- 2026-08-16 — Account Management: forgot/reset password flow fungsional (tabel `password_reset_tokens`), change password dengan verifikasi password saat ini (Playwright PASS).
- 2026-08-16 — UAT Status Stock Opname disinkronkan ke PASS (re-run E2E `test_opnames_e2e.spec.js` lulus tanpa timeout).
- 2026-08-16 — BIGNET Documentation baseline created, UI baseline confirmed via Playwright.
- 2026-08-16 — Core backend transactions audited and validated to meet business invariants (PASS).
- 2026-08-16 — Fixed Search 500 Error (Unknown column `oi.serial_number_id`) by pointing traceability correctly to `serial_movements`.
- 2026-08-16 — Table UI Standardization Batch A (Master Data & Inventory) Completed.
- 2026-08-16 — Table UI Standardization Batch B (Operational Transactions) Completed & Auto-verified.
- 2026-08-16 — Table UI Standardization Batch C (Damage/Lost & Stock Opname) Completed, including core logic implementations & Playwright tests.

## NEXT ACTION
- Exact next action: Seed ulang data pengujian (master data + inventory) sesuai kebutuhan UAT berikutnya, lalu jalankan kembali spec yang butuh data nyata (`test_scan_barcode_e2e.spec.js`).
- Expected result: Environment testing siap untuk UAT fitur dengan DB bersih + data seed yang disengaja.

## IMPORTANT CONTEXT
- Locked business rules: Traceability (Timeline), Accountability (Audit Logs), Evidence.
- Role boundaries: Super Admin (System), Admin (Warehouse Operator), Teknisi (Requester).
- Inventory invariants: Ending = Beginning + In - Out ± Transfer. Serial tidak boleh duplicate/double assignment.
- Timeline/evidence rules: Timeline harus merepresentasikan kejadian nyata, dengan evidence tersimpan valid.
- Other critical context: Do not guess logic.

## CHECKPOINT
Task terakhir: MASTER DATA CODE AUTO
Result: SELESAI — kode Satuan/Gudang/Lokasi auto-generated (`UNT-`/`GDG-`/`LOK-` + 6 digit, transaksional + retry collision); UI Tambah `[Auto Generated]` + helper text; Ubah readonly; `locations.code` unique global; Kategori/Master Barang dipertahankan; kode existing tidak diregenerate; baseline SYS-BASE di-seed sehingga spec stock/scan kembali 10/10 & 8/8; regresi penuh 44/44 PASS.
Remaining: UAT lanjutan; OPEN DECISIONS — OD-003/004/005/006/008 menunggu keputusan Product Owner.
- **[2026-08-17] PDF Export Enhancement & Pagination Fix (BUG-029)**: Final Polish to ensure no clipping on long company names, perfect adaptive font layout, translation mapping for raw enums (INCOMING, AVAILABLE), native JPEG favicon support via DCTDecode, multi-page scaling with repeatable table headers, and stable footer boundaries.
- **[2026-08-17] CSV Export Professional Formatting (BUG-030)**: Standardized CSV output for Excel Windows compatibility. Added UTF-8 BOM, strict semicolon delimiter, translation of raw enums (status/type), safe date formatting, and Excel formula injection prevention.
- **[2026-08-17] Universal Report Row Numbering (BUG-031)**: Added sequential numbering (No. 1, 2, 3...) universally to all PDF and CSV exports via array injection in ReportController. Also modified PdfHelper to enforce fixed 30pt column width specifically for the 'No' column to ensure efficient layout distribution.
- **[2026-08-17] Dashboard Low Stock Navigation (BUG-032)**: Fixed the KPI card on the dashboard to route explicitly to the stock list using a dedicated 'low_stock' filter parameter, maintaining synchronized business logic (quantity <= stok_minimum) between the KPI counter and the list view.
- **[2026-08-17] Incident Open KPI Mismatch (BUG-033)**: Fixed the Dashboard KPI for Open Incidents by linking the card to a dedicated 'PENDING' status filter on the Incident List. Synchronized the controller query and added a matching status dropdown filter to the view to maintain true consistency.
- **[2026-08-17] Barcode Overflow Fix (BUG-034)**: Modified BarcodeHelper.php to render SVGs with inline max-width:100% and height:auto ensuring universal responsiveness across all views. Wrapped the barcode output in items/show.php in a max-w-full overflow-hidden container to strictly bound it within the card without horizontal overflow or clipping.
- **[2026-08-17] Master Item Edit UI Authorization (BUG-035)**: Synchronized UI visibility of the 'Edit Barang' button with the backend rules. Master Barang edit endpoints inherently block SUPERADMIN via AuthHelper::denySuperadminWrite(), so the UI was updated to explicitly only render the button for the ADMIN role (hasRole('ADMIN')) instead of just blocking TEKNISI_NOC.
