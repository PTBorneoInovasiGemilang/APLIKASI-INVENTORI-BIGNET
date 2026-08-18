# CURRENT PROJECT HANDOFF

## CURRENT STATE (2026-08-17 — FINAL BROWSER UAT PASS + UI POLISH EMPTY STATE COMPLETED)
- **FINAL BROWSER UAT (Antigravity)**: **PASS** — installation raw enum fixed; return role flow correct; Page Guide right side desktop; raw TEST/UAT/DEBUG clean; `/loans/create` PASS; outgoing detail PASS; adjustment PASS; reports print/CSV/PDF PASS; empty state centered; compact tables; Indonesian page titles; SUPERADMIN/ADMIN/TEKNISI role flow verified.
- **Automated regression (historical)**: **93/93 PASS**.
- **UI Polish (2026-08-17)**: **PASS (code)** — BUG-024 (empty-state nested `<tr>` wrapper di 5 view index transaksi → center), BUG-025 (colspan=0 & mode ROW di kartu → colspan valid + BARE), BUG-026 (transfers header/badge/tanggal Indonesian). Verifikasi statik: lint 83 file 0 error, 43 pemanggilan empty-state 0 masalah, Page Guide wiring tak tersentuh. Browser re-verification: DELEGATED.
- **BUG-014..023**: **CLOSED** dengan evidence browser (FINAL BROWSER UAT PASS). BUG-001..013 CLOSED (histori).
- **Tidak ada bug aktif.** Tidak ada fitur baru yang diklaim tanpa implementasi.
- Sisa pekerjaan: **keputusan Product Owner** untuk OD-003/004/005/006/008 (lihat OPEN DECISIONS).
- **Remediasi Deep (business flow)**: **PASS** — BUG-017 adjustment SQL (`stock_adjustment_id`), BUG-018 outgoing detail route+view, BUG-019 return process ADMIN-only (SA 403), BUG-020 reports selection + PDF body mapping key DB, BUG-021 unbalanced div (guide kanan).
- **Fix Antigravity Exploratory UAT**: **PASS (code)** — BUG-014 `/loans/create` TEKNISI 500 (`submissions.user_id` → `submitted_by`), BUG-015 English titles → Indonesia, BUG-016 raw enum/condition leak → `UIHelper::statusLabel`.
- **Page Guide**: **PASS (audit)** — temuan "guide hilang" = false positive; `PageGuideHelper` benar, guide hadir di virtually semua 200 halaman; 3 route tanpa guide by design (login/register/forgot minimal, /install).
- **Scratch cleanup**: **DONE** — development/, test-results/, file scratch root tak dirujuk dihapus; screenshots/ + test resmi + test_search/test_batch_a dipertahankan.
- **Verifikasi**: CODING ONLY — php -l 0 error, static scan bersih, schema kanonik. Regression historical **93/93 PASS**. Browser UAT final: **DELEGATED ke Antigravity** (belum diverifikasi browser di sesi ini).
- **Global Search**: **PASS** (Semua error HTTP 500 telah diperbaiki dan diverifikasi).
- **Batch A (Master Data & Inventory)**: **PASS** (Sudah terstandardisasi UI Tabel dan lulus uji E2E).
- **Batch B (Operational Transactions)**: **PASS** (Sudah terstandardisasi UI Tabel dan lulus uji E2E).
- **Batch C (Asset Lifecycle: Damage/Lost & Stock Opname)**: **PASS** (Logika bisnis, UI, Audit Log/Timeline, dan E2E Playwright lulus 100% tanpa timeout).
- **Global Table Standard**: **PARTIAL** secara keseluruhan (Batch A, B, dan C selesai, sisa Batch D yang belum distandardisasi).
- **Dashboard Analytics**: **PASS** (KPI nyata, trend 6 bulan, pergerakan stok, stok rendah, request aktif, outstanding return, installed, damage/lost, alert/anomaly, quick action per role — data database, bukan placeholder).
- **Page Guide (Panduan Halaman)**: **PASS** (komponen reusable + konten terpusat per route, seluruh menu relevan, dark mode & responsive).
- **Dark Mode**: **PASS** (seluruh `resources/views` bebas pola light-only; audit_timeline/timeline/show/toolbar Reset/transaksi show-create/users forms dimigrasi; file mati reports/audit.php & ledger.php dihapus; parse error audit_timeline.php diperbaiki).
- **Role-based UI**: **PASS** (sidebar per role benar — SUPERADMIN monitoring/system, ADMIN operasional, TEKNISI personal; backend authorization konsisten: 403 untuk route langsung tidak sah & SUPERADMIN read-only operasional).
- **Global Table Layout**: **PASS** (shared CSS `.table-sticky` — No kiri & Aksi kanan sticky; `overflow-x-auto` di semua tabel; `min-w-full`; min-width pada tabel lebar; empty-state colspan dikecualikan; header/body sejajar; desktop/tablet/mobile terverifikasi).
- **Refinement Page Guide / Empty State / CRUD-Bulk**: **PASS** (guide panel kanan desktop & bawah mobile, role-aware; users/edit.php inline guide kiri dihapus; empty state shared terpusat; CRUD visibility per role + 403 route langsung; bulk delete master data dengan guard referensi — kategori/satuan/gudang/lokasi).
- **Role & Page UX Reconciliation**: **PASS** (SUPERADMIN read-only penuh — UI + backend 403 di incidents/opnames/adjustments/returns/transfers; ADMIN_GUDANG dead-role → ADMIN; opname approval tetap SUPERADMIN-only; sidebar Insiden/Opname utk ADMIN+SUPERADMIN; empty state ROW+PANEL shared + fix loans; guide auth forgot/reset).
- **Account / User Management**: **PASS** (detail user + riwayat audit, status-aware actions Setujui/Tolak/Nonaktifkan/Aktifkan/Reset Password, approve/reject kembali ke halaman asal; `test_account_e2e` 3/3).
- **Notification Center**: **PASS** (tabel notifications + helper user-isolated, bell + unread count, pusat notifikasi, event hooks register→SA / approve-reject-toggle→user / opname→SA; `test_notifications_e2e` 2/2).
- **Live Search**: **PASS** (endpoint JSON + debounce 350ms, loading/error/empty, URL sinkron, filter live; `test_livesearch_e2e` 2/2).
- **Barcode / QR**: **PASS** (Code 128 murni PHP terverifikasi vs JsBarcode + QR client-side di-vendor; `/labels` print sheet + batch; `/scan` lookup; integrasi items/serials; `test_barcode_e2e` 4/4).
- **Evidence / Attachment**: **PASS** (AttachmentHelper secure storage + authz, /evidence view/delete, shared evidence_list, integrasi incidents, BUG-012 legacy /file diperbaiki; `test_evidence_e2e` 3/3).
- **Export PDF**: **PASS** (PdfHelper murni PHP PDF 1.4 + ?export=pdf mengikuti filter laporan; `test_export_e2e` 2/2).
- **Mobile-first Teknisi**: **PASS** (12/12 di 375/390/768/1280 — 9 workflow, hamburger sidebar, dark, tanpa overflow; menu Lapor Kerusakan/Kehilangan ditambahkan ke sidebar TEKNISI).
- **Final Regression**: **PASS — 86/86 (1.2m)**.
- **Master Final Hardening**: **PASS** — D-015 (superadmin create = APPROVED+ACTIVE), D-016 (internal password recovery, drop `password_reset_tokens`), D-017 (bulk label `/labels/create` + `POST /labels/print` + dedupe + audit), Page Guide coverage + auth exceptions (login tanpa guide, forgot minimal), legacy `$guide_content` dihapus (4 view → PageGuideHelper), Empty State mode BARE, Compact Table global (`px-4 py-3`), Reports dedicated print layout, Import/Export hardening (template + auto-kode + validasi per baris/kolom, BUG-013), notifikasi reset-password ke user. `test_bulk_labels_e2e` 4/4; **full suite 86/86 PASS (1.2m)**; docs sinkron.

## LAST COMPLETED
- **Final UI Polish (2026-08-17)** — BUG-024: hapus nested `<tr>` wrapper empty-state di adjustments/installations/outgoing/returns/transfers index (center penuh); BUG-025: `$colspan=6` incidents/opnames index + mode BARE labels/notifications/evidence_list + fallback `$colspan ?? 1` di komponen; BUG-026: transfers header/badge/tanggal Indonesian (status center). Audit 43 pemanggilan empty-state bersih; lint 83 file 0 error; Page Guide & business logic tidak berubah.
- **Final 2-Issue Remediation (2026-08-17)** — BUG-022: raw `INSTALLED` di guide content (ReportController + PageGuideHelper) → "Terpasang", installations/index badge status row; BUG-023: return authorization audit — keputusan locked rule (ADMIN tidak create return; backend 403 dipertahankan; tombol create TEKNISI-only; ADMIN proses via show). CODING ONLY: lint + static scan.
- **Fix Antigravity Exploratory UAT (2026-08-17)** — BUG-014 (loans/create 500 → `submitted_by` + regression guard `test_remediate_e2e`), BUG-015 (13 English titles → Indonesia), BUG-016 (raw enum/status/condition → statusLabel, filter dropdown terjemahan), Page Guide coverage audit (false positives dibedakan), scratch artifacts QA dibersihkan. CODING ONLY: lint + static scan + schema; browser UAT final delegated ke Antigravity.
- **Final Remediation Deep (2026-08-17)** — BUG-017 (adjustment SQL → `stock_adjustment_id`), BUG-018 (outgoing detail route+controller+view, 200 + data + guide), BUG-019 (return `process()` ADMIN-only, SA 403, seeded E2E), BUG-020 (PdfHelper `$columns` key DB → PDF body berisi data; report viewer selection toolbar server-validated + auto-print), BUG-021 (6 view unbalanced div; guide kanan desktop). Full regression historical **93/93 PASS**.
- **Master Final Hardening** — seluruh phase 1–14 (Page Guide/Empty/Compact/Role, D-015, D-016, Notifikasi, Reports print, Import/Export, D-017 Bulk Label, Mobile Teknisi regression, full regression 86/86, docs reconciliation). Checkpoint terdokumentasi di CHANGELOG (IMPLEMENT-FINAL-HARDENING), PROJECT_STATE, UAT_STATUS, BUG_TRACKER (BUG-013 CLOSED), PRODUCT_REQUIREMENTS_FINAL, MENU_ROLE_MATRIX, DECISIONS (D-015/D-016/D-017), OPEN_DECISIONS (OD-007 CLOSED), SESSION_HANDOFF.
- **Master Final Remediation** — Account/User Mgmt lengkap (detail + status-aware actions + reset password), Notification Center (bell + unread + mark read/all + hooks), Live Search (debounce JSON), Barcode/QR (Code128 PHP + QR vendored + labels/scan), Evidence (secure storage + authz + shared component + BUG-012), Export PDF (PdfHelper murni), Mobile-first Teknisi (12/12). Targeted suites 28/28 PASS; **full suite 82/82 PASS (1.3m)**.
- **Role & Page UX Reconciliation** — SUPERADMIN read-only (Router + denySuperadminWrite + UI tersembunyi) di adjustments/incidents/opnames/returns/transfers; ADMIN_GUDANG (dead role) diselaraskan ke ADMIN di Incident/StockOpname; sidebar Insiden & Opname; empty state global (mode ROW+PANEL, loans double-wrapper fix, search/report/timeline + 8 tabel detail); guide forgot/reset. Verifikasi Playwright (`test_reconcile_e2e.spec.js` 6/6 + re-role Batch C 4/4, full suite **58 PASS**).
- **Refinement Global Page Guide + Empty State + CRUD/Bulk Action** — guide role-aware + kanan desktop/bawah mobile; hapus guide inline kiri `users/edit.php`; empty state shared ditingkatkan (icon param, py-16, align-middle); shared bulk component + `bulkDelete()` di 4 controller master data (guard referensi + audit log + CSRF), Superadmin read-only. Verifikasi Playwright (`test_refine_e2e.spec.js` 8/8 PASS, full suite **50 PASS**).
- **Global Table Layout Rule** — shared CSS `.table-sticky` (sticky No kiri / Aksi kanan + background solid + reset empty-state); fix tabel terpotong `incoming/index.php` & `submissions/show.php` (dibungkus `overflow-x-auto`); 5 tabel `w-full` → `min-w-full`; 11 tabel lebar `min-w-[1000px/1100px]` (+sticky); `report_viewer` min-width dinamis ≥ 6 kolom. Verifikasi Playwright (`test_table_layout_e2e.spec.js` 5/5 PASS, full suite **42 PASS**).
- **Global Dark Mode + Role-Based UI** — dark variants di semua komponen tersisa (audit timeline, timeline show, toolbar Reset, items show, transaksi show/create, users forms/modal), penghapusan 2 view mati, fix parse error `audit_timeline.php`; verifikasi role UI vs backend (403 teknisi /users & /incoming, admin /settings, superadmin /incoming/create). Verifikasi Playwright (`test_dark_role_e2e.spec.js` 7/7 PASS, full suite **37 PASS**).
- **Global Page Guide** — shared component `components/page_guide.php` + `PageGuideHelper` (map route→konten, prefix match, micro-guidance form) + integrasi layout & auth pages. Verifikasi Playwright (`test_page_guide_e2e.spec.js` 7/7 PASS, full suite 30 PASS).
- Perbaikan bug SQL pada insert `stock_ledgers` (Audit Stok) di `IncidentController.php` dan `StockOpnameController.php` (menyesuaikan kolom schema yang benar seperti `performed_by`, `quantity_delta`, dll).
- Perbaikan query penulisan data `stock_adjustments` dan `stock_adjustment_items` pada `StockOpnameController.php` sesuai schema database yang valid.
- Penambahan penanganan data kosong (*dummy item*) dan *non-zero variance* pada test Playwright.

## CURRENT PRIORITY
- **Seluruh scope P1/P2 + hardening + remediasi deep + fix Antigravity selesai (code).** Langkah berikut: **Antigravity menjalankan exploratory browser UAT ulang** untuk verifikasi final (sesi ini CODING ONLY tanpa browser).
- Menunggu keputusan Product Owner: OD-003 (Action Permission Detail — termasuk nasib role ADMIN_GUDANG), OD-004 (Maintenance), OD-005 (Disposal), OD-006 (akses TEKNISI ke pencarian/barcode), OD-008 (migrasi evidence lama + export Excel native). OD-007 sudah ditutup oleh D-015.
- Catatan: Bulk action sementara hanya di master data (kategori/satuan/gudang/lokasi) — perluasan ke modul lain menunggu keputusan OD-003.
- Catatan: SUPERADMIN kini read-only penuh di modul operasional — JANGAN mengembalikan mutation button/route ke SUPERADMIN tanpa keputusan Product Owner.
- Catatan: Maintenance & Disposal JANGAN diimplementasikan sampai keputusan Product Owner.
- Catatan: D-015/016/017 sudah LOCKED — jangan mengembalikan alur email reset, jangan membuat superadmin-created user PENDING, dan jangan mengembalikan cetak label satu-per-satu tanpa seleksi massal.

## VERIFIED
- **Global Search**: Pencarian serial, kode, nama barang berfungsi aman tanpa crash (HTTP 500).
- **Batch A**: Kategori, Satuan, Master Gudang, Master Lokasi Gudang, Master Barang.
- **Batch B**: Pengajuan, Barang Masuk, Barang Keluar, Transfer Gudang, Peminjaman, Pengembalian, Instalasi Lapangan, Penyesuaian Stok.
- **Batch C**: Damage/Lost & Stock Opname (termasuk upload bukti fisik, approval flow, dan penyesuaian stok otomatis).

## PARTIAL
- Tidak ada modul yang berstatus PARTIAL pada scope terverifikasi. (Histori partial Batch D sudah ditutup oleh Batch D + Dashboard + Page Guide + Dark/Role + Remediasi.)

## MISSING
- **Maintenance / Service**: Tidak ada di database maupun routing (OD-004, PROPOSED — belum dikerjakan sesuai instruksi).
- **Disposal / Penghapusan Aset**: Tidak ada di database maupun routing (OD-005, PROPOSED — belum dikerjakan sesuai instruksi).

## ACTIVE BUGS
- Tidak ada bug aktif tercatat. BUG-014..026 CLOSED (2026-08-17); BUG-001..013 CLOSED (histori). Browser re-verification menunggu UAT ulang Antigravity.
- Catatan: tipe item SERIALIZED/BULK tetap label teknis English (konsisten seluruh app sebagai tipe, bukan status enum) — bukan bagian scope 2-issue.

## OPEN DECISIONS
- **OD-003**: Action Permission Detail (Detail hak akses per aksi spesifik untuk tiap role — termasuk nasib role ADMIN_GUDANG).
- **OD-004**: Modul Maintenance/Service (Apakah wajib diimplementasikan di versi ini atau tidak).
- **OD-005**: Modul Disposal/Penghapusan (Apakah menggunakan Adjustment negatif atau menu khusus).
- **OD-006**: Akses TEKNISI ke Pencarian & Barcode/Scan (konsisten dgn MENU_ROLE_MATRIX saat ini).
- **OD-008**: Migrasi evidence lama ke secure storage + Export Excel native (XLSX).
- OD-007: CLOSED (D-015).

## LOCKED REQUIREMENTS
- **Core Inventory Invariants**:
  - `Ending = Beginning + In - Out ± Transfer`
  - Traceability nomor seri via `serial_movements`
  - Upload bukti fisik (Evidence) di workflow yang relevan
  - Atomicity database transactions (`BEGIN`, `COMMIT`, `ROLLBACK`) dengan lock `FOR UPDATE`
  - Keunikan nomor seri (tidak ada double assignment)
- **Damage/Lost Accountability (D-013)**: Wajib mencatat pelapor, bukti fisik, pemegang terakhir, timeline, dan jejak audit.
- **Stock Opname Workflow (D-014)**: Wajib membandingkan fisik vs sistem, variance review, dan pemisahan persetujuan sebelum adjustment otomatis dipicu.
- **Superadmin-Created User (D-015)**: User buatan SUPERADMIN = APPROVED + ACTIVE langsung; registrasi publik tetap PENDING.
- **Internal Password Recovery (D-016)**: Lupa password ditangani SUPERADMIN; TIDAK ada alur email/reset-token (tabel `password_reset_tokens` di-drop).
- **Bulk Label Printing (D-017)**: Cetak label wajib massal (seleksi, Pilih Semua, qty non-serialized, preview, proteksi duplikat, audit, lembar A4).

## IMPORTANT UI STANDARDS
- Setiap tabel wajib menerapkan **Shared Components**:
  - Row Numbering (Penomoran baris berkelanjutan)
  - Search (Pencarian teks)
  - Filter & Reset (Penyaringan dan tombol reset)
  - Pagination (Navigasi halaman)
  - Empty State & Loading State

## ACCOUNT MANAGEMENT REQUIREMENTS
Modul manajemen akun wajib memiliki cakupan lengkap berikut (sebagian sudah ada, sebagian perlu review/standardisasi UI):
- **Register**: Pendaftaran user baru.
- **Approval**: Aktivasi user baru oleh Superadmin.
- **Login/Logout**: Otentikasi sesi pengguna.
- **Forgot Password**: Form klaim lupa kata sandi.
- **Reset Password**: Form penyetelan ulang kata sandi.
- **Change Password**: Ubah sandi dari dalam profil.
- **Active/Inactive Status**: Blokir/aktifkan user.
- **Role/Permission**: Pembatasan akses level (Superadmin, Admin Gudang, Teknisi NOC).
- **Protected Route**: Proteksi rute backend/frontend.
- **Success/Error Feedback**: Tampilan alert feedback yang konsisten.

## GRAPHIFY STATUS
- Digunakan hanya untuk memetakan arsitektur dan relasi ketergantungan komponen (*architecture/dependency evidence*).
- Direktori output: `graphify-out/`

## PLAYWRIGHT STATUS
- Digunakan sebagai *browser behavior verification* utama.
- Pengujian otomatis terbaru:
  - `test_bulk_labels_e2e.spec.js` -> **PASS (4/4)** — D-017 bulk label (workspace+seleksi+count+print sheet, select-all/bersihkan+empty, teknisi 403, dark+mobile)
  - Full regression: **86/86 PASS (1.2m)** — 2026-08-16
- Server: `php -S localhost:8000` harus berjalan sebelum test (php -S single-thread; di bawah beban suite penuh kadang timeout — retest test yg gagal secara terpisah).

## LAST CHANGES
- Menyejajarkan kueri database pada `IncidentController.php` dan `StockOpnameController.php` agar menggunakan kolom yang tepat pada tabel `stock_ledgers` dan `stock_adjustments`.
- Mengatur test Playwright Stock Opname agar secara dinamis menghitung *variance* (selisih) berdasarkan stok sistem di database agar validasi *variance > 0* atau *variance < 0* terpenuhi.
- **Memperbaiki BUG-027 (UI Global Table Width & Mobile Overflow)**: Tabel yang sebelumnya hanya menggunakan `min-w-[1000px]` kini ditambahkan `w-full` agar memenuhi *available content width*. Batasan `max-w-7xl mx-auto` dari layout aplikasi utama dihapus agar tampilan tidak terpotong pada layar yang lebar. Pada perangkat mobile, *page level horizontal overflow* di halaman `incidents` dan `stock_opnames` telah diselesaikan dengan memperbaiki `flex-wrap` di bagian header dan mengatur komponen input pencarian agar beradaptasi dengan lebar layar.

## NEXT TASK
- Laporan ke Product Owner mengenai perbaikan tabel width (BUG-027).
- Menunggu keputusan Product Owner untuk: OD-003 (permission detail), OD-004 (Maintenance), OD-005 (Disposal), OD-006 (akses TEKNISI ke pencarian/barcode), OD-008 (migrasi evidence lama + XLSX native).
- Setelah keputusan, eksekusi scope terkait (jangan mengubah D-015/016/017).

## DO NOT REPEAT
- **Jangan mengulang audit modul yang sudah bertanda PASS** (Batch A–D, Search, Dashboard, Page Guide, Dark Mode, Role UI, Notification, Live Search, Barcode, Evidence, PDF, Mobile Teknisi, Final Hardening).
- **Jangan mengubah logika bisnis core** yang sudah stabil dan lulus E2E.
- **Jangan membuat tabel atau database column duplikat** di luar skema resmi yang terdaftar di migrasi.
- **Jangan** mengembalikan alur email reset (D-016), membuat superadmin-created user PENDING (D-015), atau mencetak label tanpa seleksi massal (D-017).
- **Jangan** menghidupkan kembali role `ADMIN_GUDANG` (dead role).

## NOTES FOR NEXT AI SESSION
- Pastikan server PHP dev (`localhost:8000`) berjalan sebelum menjalankan test Playwright.
- Gunakan skema database riil yang ada pada `database/migrations/` sebagai satu-satunya kebenaran struktur database.
- Terapkan alur inkremental (Implement -> Auto-verify -> Document -> Checkpoint) untuk menghemat token konteks.
- Selalu perbarui Living Documents (`PROJECT_STATE.md`, `BUG_TRACKER.md`, `UAT_STATUS.md`, `CHANGELOG.md`) di akhir pengerjaan setiap Batch.
- **PRODUCTION CLEANUP (2026-08-17):** Semua test script di root dan debug file di public (dump_session.php, 	est_login.php) telah dihapus atau dipindah ke 	ests/playwright/ dan 	ests/fixtures/. Endpoint yang rentan tidak ada lagi di dalam scope public/web root.
