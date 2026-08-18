# BIGNET PRODUCT REQUIREMENTS REGISTRY

Registry ini berisi daftar final fitur dan standar sistem yang sudah disepakati, berdasarkan `MASTER_BIGNET_PROJECT_V2.md` dan keputusan teknis yang terdokumentasi.

Status:
- **LOCKED** = Aturan bisnis tidak boleh diubah (Core Invariants).
- **IMPLEMENTED** = Sudah di-coding dan terverifikasi secara E2E/Playwright.
- **PARTIAL** = Sudah di-coding tapi baru sebagian atau belum semuanya terverifikasi.
- **MISSING** = Belum di-coding atau hilang dari codebase.
- **OPEN DECISION** = Menunggu keputusan bisnis lebih lanjut.

---

## 1. CORE INVENTORY INVARIANTS (LOCKED)
Aturan dasar yang tidak boleh dilanggar oleh fitur apapun.

- **[LOCKED]** Stock Equation: `Ending = Beginning + In - Out ± Transfer`.
- **[LOCKED]** Traceability: Setiap pergerakan barang (khususnya *serialized*) harus bisa ditelusuri dari asal hingga tujuan akhir via `serial_movements`.
- **[LOCKED]** Evidence: Setiap transaksi fisik wajib menyimpan bukti (foto/dokumen) secara valid.
- **[LOCKED]** Atomicity: Setiap multi-step transaction harus menggunakan Database Transactions (`BEGIN`, `COMMIT`, `ROLLBACK`) dan lock `FOR UPDATE`.
- **[LOCKED]** Serial Uniqueness: Nomor seri tidak boleh mengalami *double assignment*.

## 2. SYSTEM ROLES (IMPLEMENTED)
- **[IMPLEMENTED]** SUPERADMIN: **Read-only pada semua modul operasional** (monitoring, detail/riwayat, laporan, audit, users, settings; approval variance opname sesuai D-014). Mutation operasional (create/edit/delete/process/approve pada modul gudang) ditolak backend (Router read-only list + `denySuperadminWrite` → 403) dan tombolnya tidak ditampilkan. (Reconciliation 2026-08-16; sebelumnya SUPERADMIN bisa menjalankan CRUD operasional — diperbaiki.)
- **[IMPLEMENTED]** ADMIN: Operasional warehouse/inventory — master data, incoming/outgoing/transfer, pengajuan (approve), peminjaman/pengembalian (proses), insiden (proses), stock opname (rekam).
- **[IMPLEMENTED]** TEKNISI_NOC: Workflow personal — pengajuan, custody, instalasi, return, evidence, lapor damage/lost (pelapor), tanpa proses/admin.
- **[NOTED]** ADMIN_GUDANG: Role ini direferensikan controller (Incident/StockOpname) namun **tidak ada di enum DB** (`ENUM('SUPERADMIN','ADMIN','TEKNISI_NOC')`) sehingga tidak dapat dimiliki user. Diselaraskan sementara ke role nyata `ADMIN` (2026-08-16). Nasib final (adopsi atau digabung ke ADMIN) menunggu keputusan OD-003 / Product Owner.

## 3. UI/UX STANDARDS
Standar global aplikasi yang harus dipatuhi.

- **[IMPLEMENTED]** Global Table Standard: Semua tabel/list harus memiliki *Row Numbering*, *Search*, *Filter*, *Reset*, *Pagination*, *Empty State*, dan Layout konsisten. (Batch A, B, C, D: IMPLEMENTED & terverifikasi Playwright).
- **[IMPLEMENTED]** Global Table Layout Rule: Semua tabel responsif terhadap lebar viewport, tidak memotong kolom penting, kolom Aksi selalu dapat diakses, horizontal scroll terkontrol (`overflow-x-auto` + `min-w-full`/min-width pada tabel lebar), header & body sejajar saat scroll (satu elemen `<table>`), kolom No (kiri) & Aksi (kanan) sticky pada tabel lebar via shared CSS `.table-sticky` (empty-state `colspan` dikecualikan), dark mode support. Terverifikasi Playwright `test_table_layout_e2e` 5/5 (desktop, mobile 375px, tablet 768px, empty state, dark mode, no console error).
- **[PARTIAL]** Loading/Error State: Validasi *server-side* standar dan tampilan sinkronus untuk PHP.
- **[IMPLEMENTED]** Dashboard Analytics: Menampilkan *real-world monitoring* stok dan pergerakan (KPI nyata, trend 6 bulan, pergerakan stok, stok rendah, request aktif, outstanding return, installed asset, damage/lost, alert/anomaly, quick action per role) — terverifikasi Playwright.
- **[IMPLEMENTED]** Page Guide: Bantuan kontekstual statis di setiap menu relevan (shared component `page_guide.php` + sumber konten terpusat `PageGuideHelper` per route; tujuan, fungsi utama, workflow/status, aturan penting, micro-guidance form; dark mode & responsive). Refinement: panel di **kanan konten** di desktop & turun di bawah konten di mobile/tablet sempit (konsisten di semua halaman — panel inline kiri seperti di Edit User dihapus); konten **role-aware** (section `roles`/`roles_exclude` — Super Admin/Admin/Teknisi). Terverifikasi Playwright `test_refine_e2e`.
- **[IMPLEMENTED]** Empty State Standard: komponen shared (ikon + judul + deskripsi), terpusat horizontal & vertikal, berlaku untuk empty data & search/filter no-result, konsisten light/dark/mobile. Terverifikasi Playwright.
- **[IMPLEMENTED]** CRUD / Action Standard: aksi (CREATE/VIEW/EDIT/APPROVE/REJECT/PROCESS/business action) mengikuti permission; UI tidak menghilangkan action, backend tetap menolak unauthorized direct route/API (Router read-only SUPERADMIN → 403). Terverifikasi Playwright.
- **[IMPLEMENTED]** Bulk Action Standard: pola shared (`components/table/bulk_actions.php` — checkbox per row, select-all, selected count, confirm, loading, success/error feedback) + backend `bulkDelete()` dengan guard referensi & audit log, diterapkan ke master data yang aman (kategori, satuan, gudang, lokasi); TIDAK pada tabel transaksi/histori. Terverifikasi Playwright.
- **[IMPLEMENTED]** Dark Mode & Role-Based UI: Seluruh `resources/views` konsisten light/dark (sidebar, header, dashboard, page guide, tabel, form, dropdown, modal, badge, pagination, timeline, report, alert, empty/error/loading, profile) — tanpa pola light-only tersisa (scan 2026-08-16). Role-Based UI konsisten antara UI & backend: SUPERADMIN monitoring/system (read-only operasional via Router + 403 mutation), ADMIN operasional gudang, TEKNISI personal (pengajuan/pinjaman/instalasi/return/evidence/damage-lost); route langsung tidak sah → 403. Terverifikasi Playwright `test_dark_role_e2e` 7/7.

## 4. MASTER DATA MODULES
- **[IMPLEMENTED]** Kategori Barang
- **[IMPLEMENTED]** Satuan Barang
- **[IMPLEMENTED]** Master Gudang
- **[IMPLEMENTED]** Master Lokasi Gudang
- **[IMPLEMENTED]** Master Barang (Items)
- **[IMPLEMENTED]** Master Pengguna (Users) - *Table UI terstandardisasi (search, filter status, pagination, row numbering, modal approval)*.

## 5. INVENTORY & TRACKING
- **[IMPLEMENTED]** Stok Barang
- **[IMPLEMENTED]** Nomor Seri (Serial) - *UI Terstandardisasi*.
- **[IMPLEMENTED]** Global Search - *Berfungsi penuh tanpa HTTP 500*.
- **[IMPLEMENTED]** Timeline Pergerakan - *Core logic jalan, UI list terstandardisasi (toolbar, pagination, row numbering, dark mode)*.
- **[IMPLEMENTED]** Audit Log - *Core logic jalan, UI list terstandardisasi via report viewer (search, filter, pagination, row numbering)*.

## 6. OPERATIONAL TRANSACTIONS (BATCH B)
- **[IMPLEMENTED]** Pengajuan (Submissions)
- **[IMPLEMENTED]** Barang Masuk (Incoming)
- **[IMPLEMENTED]** Barang Keluar (Outgoing)
- **[IMPLEMENTED]** Transfer Gudang (Transfers)
- **[IMPLEMENTED]** Peminjaman (Loans)
- **[IMPLEMENTED]** Pengembalian (Returns) - *Role flow: TEKNISI membuat pengembalian (create/store TEKNISI-only), ADMIN memproses/validasi (`process()` ADMIN), SUPERADMIN read-only (monitor). Verifikasi final browser UAT: return role flow correct (2026-08-17).*
- **[IMPLEMENTED]** Instalasi Lapangan (Installations) - *Status ditampilkan human-readable via `UIHelper::statusLabel` (INSTALLED → Terpasang); guide laporan instalasi tidak lagi memuat enum mentah. Verifikasi final browser UAT: installation raw enum fixed (2026-08-17).*
- **[IMPLEMENTED]** Penyesuaian Stok (Adjustments) - *Query memakai kolom schema kanonik `stock_adjustment_id`. Verifikasi final browser UAT: adjustment PASS (2026-08-17).*

## 7. ASSET LIFECYCLE (BATCH C)
- **[LOCKED/IMPLEMENTED]** Damage/Lost - *Ditetapkan sebagai accountability capability wajib (D-013). Sudah diimplementasikan secara logic dan terverifikasi E2E.*
- **[LOCKED/IMPLEMENTED]** Stock Opname - *Ditetapkan sebagai fitur wajib pembanding fisik vs sistem (D-014). Sudah diimplementasikan secara logic dan terverifikasi E2E.*
- **[PROPOSED/MISSING]** Maintenance / Service - *Belum ada modul, route, atau tabel di codebase. Menunggu keputusan (OD-004).*
- **[PROPOSED/MISSING]** Disposal / Penghapusan - *Belum ada modul, route, atau tabel di codebase. Menunggu keputusan (OD-005).*

## 8. REPORTS (BATCH D)
- **[IMPLEMENTED]** Laporan Transaksi / Mutasi - *UI terstandardisasi: row numbering, search, filter, reset, pagination, empty state, page guide, checkbox selection (select-all tampil/filter, clear, selected count), export CSV, export PDF, print — server-validated. PDF body memakai mapping key DB eksplisit (tidak kosong). Verifikasi final browser UAT: reports print/CSV/PDF PASS (2026-08-17).*
- **[IMPLEMENTED]** Export PDF / Excel - *Export CSV & Export PDF (2026-08-16, `test_export_e2e` 2/2). Excel native belum ada (CSV dijadikan kompromi; catat utk keputusan Product Owner).*
- **[IMPLEMENTED]** Dedicated Print Layout - *`@media print` menyembunyikan sidebar/header/Page Guide/aksi; print header (judul, periode, filter, timestamp) + footer (timestamp + nomor halaman); sticky & overflow dinonaktifkan saat cetak (2026-08-16).*

> **VERIFIKASI FINAL (2026-08-17):** FINAL BROWSER UAT oleh Antigravity PASS — installation raw enum fixed; return role flow correct; Page Guide right side desktop; raw TEST/UAT/DEBUG clean; `/loans/create` PASS; outgoing detail PASS; adjustment PASS; reports print/CSV/PDF PASS; empty state centered; compact tables; Indonesian page titles; SUPERADMIN/ADMIN/TEKNISI role flow verified. Automated regression historical 93/93 PASS.

## 9. ACCOUNT MANAGEMENT (BATCH D)
- **[IMPLEMENTED]** Register - Pendaftaran publik (Pending Approval).
- **[IMPLEMENTED]** Approval - Approve/Reject oleh Superadmin dengan alasan wajib.
- **[IMPLEMENTED]** Login/Logout - Otentikasi sesi + session timeout + CSRF.
- **[IMPLEMENTED]** Superadmin-Create User (D-015) - *User yang dibuat Superadmin langsung APPROVED + ACTIVE (langsung login), tanpa loop approval; audit `SUPERADMIN_CREATE_USER`. Registrasi publik tetap PENDING.*
- **[IMPLEMENTED]** Forgot Password (D-016) - *Internal: halaman minimal "Hubungi Superadmin untuk mereset kata sandi" + langkah; TIDAK ada alur email/reset-token publik.*
- **[IMPLEMENTED]** Reset Password (D-016) - *SUPERADMIN-only via Users → Reset Password: password sementara sekali-tampil, audit `ADMIN_PASSWORD_RESET`, notifikasi ke user; tidak mengaktifkan account yang nonaktif.*
- **[IMPLEMENTED]** Change Password - Ubah sandi dari profil dengan verifikasi password saat ini + konfirmasi.
- **[IMPLEMENTED]** Active/Inactive Status - Blokir/aktifkan user (is_active, account_status).
- **[IMPLEMENTED]** Role/Permission - Pembatasan akses level (SUPERADMIN/ADMIN/TEKNISI_NOC).
- **[IMPLEMENTED]** Protected Route - Redirect ke login + 403 untuk role terlarang.
- **[IMPLEMENTED]** User Detail & Riwayat Akun - Halaman `users/show.php`: profil, status-aware actions (Setujui/Tolak utk PENDING; Nonaktifkan/Aktifkan utk APPROVED/SUSPENDED; Reset Password dengan password sementara sekali-tampil), riwayat audit akun. (Final Remediation 2026-08-16, `test_account_e2e` 3/3.)

## 10. NOTIFICATION CENTER (FINAL REMEDIATION)
- **[IMPLEMENTED]** Notifications - Tabel `notifications` + `NotificationHelper` (user-isolated), bell + unread count di header, pusat notifikasi (read/unread, timestamp, source link, mark read/all, empty state, dark, mobile), event hooks: pendaftaran publik → SUPERADMIN, approve/reject/toggle akun → user, stock opname dibuat → SUPERADMIN. Terverifikasi `test_notifications_e2e` 2/2. (Login/logout TIDAK menghasilkan notifikasi bisnis.)

## 11. LIVE SEARCH (FINAL REMEDIATION)
- **[IMPLEMENTED]** Live Search - Endpoint JSON `?format=json` di `/search` + JS debounce 350ms: ketik/filter → update otomatis, loading/error/empty state, URL sinkron, aksi/detail tetap berfungsi, role restriction tetap (SUPERADMIN/ADMIN). Terverifikasi `test_livesearch_e2e` 2/2. (Akses TEKNISI ke pencarian masih sesuai MENU_ROLE_MATRIX — OD-006.)

## 12. BARCODE / QR (FINAL REMEDIATION + HARDENING)
- **[IMPLEMENTED]** Barcode / QR - `BarcodeHelper::code128` (murni PHP, SVG, diverifikasi bar-count vs JsBarcode), QR client-side (qrcode-generator di-vendor `public/assets/js/qrcode.min.js` + route static), halaman `/labels` (label sheet Code 128 + QR + print CSS + batch `serial_ids`) dan `/scan` (lookup kode/serial → detail, not-found → search), integrasi `items/show` (barcode + QR modal + Cetak Label) & `serials` (Label per baris + Cetak Semua Label). Identitas memakai `kode_barang` & `serial_number` existing. Role: SUPERADMIN/ADMIN. Terverifikasi `test_barcode_e2e` 4/4.
- **[IMPLEMENTED]** Bulk Label Printing (D-017) - `/labels/create` (ruang kerja: filter search/kategori/gudang/status, tabel SN + checkbox, Pilih Semua Tampil/Bersihkan, selected count live, item non-serialized dengan jumlah label 1–100, sticky action bar) → `POST /labels/print` (dedupe SN, gabung kode bulk, audit `LABEL_PRINT`, redirect lembar cetak `?serial_ids=&bulk=KODE:qty`) → lembar A4 multi-label. Menu sidebar "Cetak Label Massal" (SUPERADMIN/ADMIN) + Page Guide. Terverifikasi `test_bulk_labels_e2e` 4/4.

## 13. EVIDENCE / ATTACHMENT (FINAL REMEDIATION)
- **[IMPLEMENTED]** Evidence / Attachment - `AttachmentHelper` (validasi MIME via finfo + size 5MB, simpan ke `storage/private_uploads/evidence` — bukan public root, metadata actor/timestamp, authz uploader/ADMIN/SUPERADMIN), `EvidenceController` (`/evidence/{id}` view secure + delete dengan CSRF + audit), shared `components/evidence_list.php` (preview, metadata, empty state, upload), integrasi incidents show (Bukti Tambahan). Link evidence lama incidents (BUG-012) diperbaiki. Terverifikasi `test_evidence_e2e` 3/3. Catatan: evidence lama masih tersimpan di `public/uploads/evidence/` (migrasi penuh ke secure storage menunggu keputusan).

## 14. EXPORT PDF (FINAL REMEDIATION)
- **[IMPLEMENTED]** Export PDF - `PdfHelper` murni PHP (PDF 1.4: A4, judul, timestamp, aktor, tabel multi-halaman + header berulang + nomor halaman, xref valid), `?export=pdf` di semua laporan (filter/date-range/search mengikuti laporan, data identik dgn viewer), tombol Export PDF di report_viewer. Authorization SUPERADMIN/ADMIN. Terverifikasi `test_export_e2e` 2/2.
