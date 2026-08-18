# BIGNET OPEN DECISIONS

> Hanya isu yang benar-benar belum diputuskan. Jangan mengisi keputusan tanpa Product Owner.

## OD-001 — Damage/Lost UI
**Question:** Apakah Damage dan Lost memiliki menu terpisah atau menjadi workflow di modul existing?
**Status:** CLOSED (Lihat D-013)

## OD-002 — Stock Opname UI
**Question:** Apakah Stock Opname memiliki menu utama sendiri atau memakai modul existing?
**Status:** CLOSED (Lihat D-014)

## OD-003 — Action Permission Detail
**Question:** Permission per action untuk setiap role perlu diverifikasi/ditetapkan detailnya.
**Status:** OPEN
**Catatan (2026-08-16):** Role reconciliation telah memutuskan sementara — SUPERADMIN read-only pada semua modul operasional (Router read-only list + `denySuperadminWrite`, UI disembunyikan); role `ADMIN_GUDANG` (direferensikan Incident/StockOpname controller tapi tidak ada di enum DB users) diselaraskan sementara ke `ADMIN`; approval variance opname tetap SUPERADMIN-only (D-014). Keputusan final: (a) adopsi ADMIN_GUDANG ke enum DB + seed user, atau (b) resmi menggabung ke ADMIN; perluasan bulk action ke modul lain juga menunggu keputusan ini.

## OD-004 — Maintenance / Service Requirement
**Question:** Modul Maintenance tidak ada di *database* dan *routing*. Apakah ini benar-benar fitur wajib (LOCKED) untuk versi ini atau sekadar wacana (PROPOSED)? Jika wajib, bagaimana *business flow*-nya?
**Status:** OPEN

## OD-005 — Disposal / Penghapusan Aset
**Question:** Sama seperti Maintenance, modul Disposal belum ada di dalam codebase sama sekali. Apakah akan dibuat menu khusus atau menggunakan Adjustment negatif?
**Status:** OPEN

## OD-006 — Akses TEKNISI ke Pencarian & Barcode/Scan
**Question:** PRD V2 (Section M) mencantumkan "Pencarian Barang" dan "Barcode/QR lookup" sebagai workflow mobile Teknisi, tetapi MENU_ROLE_MATRIX saat ini menetapkan Pencarian "Use sesuai scope" dan MasterItemController (`/items/show`) hanya SUPERADMIN/ADMIN.
**Status:** OPEN
**Catatan (2026-08-16):** Final Remediation memutuskan sementara konsisten dengan matrix — TEKNISI tidak punya menu Pencarian di sidebar dan `/search`, `/labels`, `/scan` ditolak 403 untuk TEKNISI. Jika Product Owner menginginkan Teknisi bisa mencari barang (read-only, mis. hanya item yang pernah menjadi tanggung jawabnya), perlu: buka sebagian `/search` + `/items/show` read-only untuk TEKNISI + tambah menu sidebar + perbarui MENU_ROLE_MATRIX.

## OD-007 — User dibuat SUPERADMIN selalu PENDING
**Question:** `UserController::store` (superadmin membuat user) selalu menyimpan `account_status = PENDING_APPROVAL` + `is_active = 0`, sehingga user yang baru dibuat oleh superadmin harus di-approve lagi dari daftar. Apakah ini flow yang diinginkan, atau superadmin-created user langsung APPROVED (karena sudah diverifikasi oleh superadmin)?
**Status:** CLOSED (Lihat D-015)
**Catatan (2026-08-16):** Master Final Hardening mengunci **D-015** — user buatan SUPERADMIN langsung APPROVED + ACTIVE (bisa langsung login); registrasi publik tetap PENDING. `UserController::store` diperbarui sesuai D-015 dan diverifikasi (curl + `test_account_e2e`).

## OD-008 — Evidence lama & Export Excel
**Question:** (a) Evidence Damage/Lost lama tersimpan di `public/uploads/evidence/` (public root) — migrasi penuh ke `storage/private_uploads/evidence` untuk semua data lama? (b) Export Excel native (XLSX) — CSV dipakai sebagai kompromi saat ini.
**Status:** OPEN
**Catatan (2026-08-16):** Secure storage diterapkan untuk upload baru; link lama diperbaiki (BUG-012); migrasi data lama + XLSX menunggu keputusan Product Owner.

## RULE

Jika agent menemukan keputusan baru yang diperlukan:
1. Tambahkan ke file ini.
2. Jangan menebak.
3. Setelah Product Owner menjawab, pindahkan keputusan final ke `DECISIONS.md`.
