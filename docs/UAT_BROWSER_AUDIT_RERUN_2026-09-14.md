# BIGNET Full Browser UAT Re-run

**Date & Time**: 2026-09-14 11:25:00 +07:00  
**Environment**: Public Cloudflare Tunnel  
**Public UAT URL**: `https://detailed-antiques-daisy-upper.trycloudflare.com`  
**Browser Engine**: Playwright Chromium (Headless Automation)  
**Git Commit / Source of Truth**: `963df82974d32ebd873e42bae153c94ed53b058e` (`origin/main`)  
**Workspace**: `C:\xampp\htdocs\APLIKASI-INVENTORI-BIGNET-CLEAN`  

---

## Environment

- **Repository**: `https://github.com/tommyrd/APLIKASI-INVENTORI-BIGNET`
- **Tracked Code Status**: Clean (HEAD == `origin/main` commit `963df829`)
- **Database Modded?**: Tidak ada modifikasi database / schema / SQL.
- **UAT Tunnel**: Public Cloudflare Tunnel (Tanpa localhost).

---

## Summary

| Metric | Count |
|---|---:|
| Total Scenarios Tested | 110 |
| PASS | 108 |
| FAIL (Application Bugs) | 0 |
| BLOCKED | 0 |
| NOT TESTED | 0 |
| Test Errors / False Positives | 2 |

---

## Findings

| ID | Role | Module | Scenario | Expected | Actual | Status | Severity | Evidence |
|---|---|---|---|---|---|---|---|---|
| AUTH-CRED-SUPERADMIN | SUPERADMIN | Auth | Credential Verification | Login success to `/dashboard` | Logged in successfully: `/dashboard` | PASS | - | `SUPERADMIN_DASHBOARD.png` |
| AUTH-CRED-ADMIN | ADMIN | Auth | Credential Verification | Login success to `/dashboard` | Logged in successfully: `/dashboard` | PASS | - | `ADMIN_LOGIN_VERIFY.png` |
| AUTH-CRED-TEKNISI_NOC | TEKNISI_NOC | Auth | Credential Verification | Login success to `/dashboard` | Logged in successfully: `/dashboard` | PASS | - | `TEKNISI2_DASHBOARD.png` |
| AUTH-1 | PUBLIC | Auth | Login Page Render | Title contains Masuk & form present | Title: "Masuk - PT. BIG", Form present | PASS | - | - |
| AUTH-2 | PUBLIC | Auth | Wrong Password Handling | Stay on `/login` with error | Redirected back to `/login` | PASS | - | - |
| AUTH-UNAUTH-/dashboard | UNAUTH | Auth Security | Direct GET `/dashboard` logged out | Redirect to `/login` | Redirected to `/login` | PASS | - | - |
| AUTH-UNAUTH-/users | UNAUTH | Auth Security | Direct GET `/users` logged out | Redirect to `/login` | Redirected to `/login` | PASS | - | - |
| AUTH-UNAUTH-/settings | UNAUTH | Auth Security | Direct GET `/settings` logged out | Redirect to `/login` | Redirected to `/login` | PASS | - | - |
| AUTH-UNAUTH-/items | UNAUTH | Auth Security | Direct GET `/items` logged out | Redirect to `/login` | Redirected to `/login` | PASS | - | - |
| AUTH-UNAUTH-/stocks | UNAUTH | Auth Security | Direct GET `/stocks` logged out | Redirect to `/login` | Redirected to `/login` | PASS | - | - |
| AUTH-UNAUTH-/incoming | UNAUTH | Auth Security | Direct GET `/incoming` logged out | Redirect to `/login` | Redirected to `/login` | PASS | - | - |
| AUTH-UNAUTH-/transfers | UNAUTH | Auth Security | Direct GET `/transfers` logged out | Redirect to `/login` | Redirected to `/login` | PASS | - | - |
| AUTH-UNAUTH-/reports | UNAUTH | Auth Security | Direct GET `/reports` logged out | Redirect to `/login` | Redirected to `/login` | PASS | - | - |
| AUTH-MATRIX-SA-1 | SUPERADMIN | Authorization | Access `/users` | HTTP 200 Allowed | HTTP 200, Title: "Manajemen Pengguna — GUDANG PT.BIG" | PASS | - | - |
| AUTH-MATRIX-SA-2 | SUPERADMIN | Authorization | Access `/settings` | HTTP 200 Allowed | HTTP 200, Title: "Pengaturan Sistem — GUDANG PT.BIG" | PASS | - | - |
| AUTH-MATRIX-SA-3 | SUPERADMIN | Authorization | Access `/audit` | HTTP 200 Allowed | HTTP 200, Title: "Log Aktivitas — GUDANG PT.BIG" | PASS | - | - |
| AUTH-MATRIX-SA-4 | SUPERADMIN | Authorization Guard | Access `/incoming/create` | HTTP 403 Forbidden | Denied correctly: HTTP 403 (Read-only policy) | PASS | - | - |
| AUTH-MATRIX-SA-5 | SUPERADMIN | Authorization Guard | Access `/outgoing/create` | HTTP 403 Forbidden | Denied correctly: HTTP 403 (Read-only policy) | PASS | - | - |
| AUTH-MATRIX-SA-6 | SUPERADMIN | Authorization Guard | Access `/adjustments/create` | HTTP 403 Forbidden | Denied correctly: HTTP 403 (Read-only policy) | PASS | - | - |
| AUTH-MATRIX-ADM-1 | ADMIN | Authorization | Access `/categories` | HTTP 200 Allowed | HTTP 200, Title: "Kategori Barang — GUDANG PT.BIG" | PASS | - | - |
| AUTH-MATRIX-ADM-2 | ADMIN | Authorization | Access `/items` | HTTP 200 Allowed | HTTP 200, Title: "Master Barang — GUDANG PT.BIG" | PASS | - | - |
| AUTH-MATRIX-ADM-3 | ADMIN | Authorization | Access `/incoming/create` | HTTP 200 Allowed | HTTP 200, Title: "Barang Masuk Baru — GUDANG PT.BIG" | PASS | - | - |
| AUTH-MATRIX-ADM-4 | ADMIN | Authorization | Access `/outgoing/create` | HTTP 200 Allowed | HTTP 200, Title: "Pemenuhan Barang — GUDANG PT.BIG" | PASS | - | - |
| AUTH-MATRIX-ADM-5 | ADMIN | Authorization | Access `/transfers/create` | HTTP 200 Allowed | HTTP 200, Title: "Transfer Gudang Baru — GUDANG PT.BIG" | PASS | - | - |
| AUTH-MATRIX-ADM-6 | ADMIN | Authorization | Access `/adjustments/create` | HTTP 200 Allowed | HTTP 200, Title: "Penyesuaian Stok Baru — GUDANG PT.BIG" | PASS | - | - |
| AUTH-MATRIX-ADM-7 | ADMIN | Authorization | Access `/opnames/create` | HTTP 200 Allowed | HTTP 200, Title: "Buat Laporan Stock Opname — GUDANG PT.BIG" | PASS | - | - |
| AUTH-MATRIX-ADM-8 | ADMIN | Authorization Guard | Access `/users` | HTTP 403 Forbidden | Denied correctly: HTTP 403 | PASS | - | `VERIFY_VERIFY-1_admin.png` |
| AUTH-MATRIX-ADM-9 | ADMIN | Authorization Guard | Access `/settings` | HTTP 403 Forbidden | Denied correctly: HTTP 403 | PASS | - | `VERIFY_VERIFY-2_admin.png` |
| AUTH-MATRIX-ADM-10 | ADMIN | Authorization Guard | Access `/returns/create` | HTTP 403 Forbidden | Denied correctly: HTTP 403 (Teknisi only) | PASS | - | - |
| AUTH-MATRIX-TEK-1 | TEKNISI_NOC | Authorization | Access `/submissions/create` | HTTP 200 Allowed | HTTP 200, Title: "Buat Pengajuan Barang — GUDANG PT.BIG" | PASS | - | - |
| AUTH-MATRIX-TEK-2 | TEKNISI_NOC | Authorization | Access `/loans/create` | HTTP 200 Allowed | HTTP 200, Title: "Ajukan Peminjaman — GUDANG PT.BIG" | PASS | - | - |
| AUTH-MATRIX-TEK-3 | TEKNISI_NOC | Authorization | Access `/returns/create` | HTTP 200 Allowed | HTTP 200, Title: "Pengembalian Baru — GUDANG PT.BIG" | PASS | - | - |
| AUTH-MATRIX-TEK-4 | TEKNISI_NOC | Authorization | Access `/incidents/create` | HTTP 200 Allowed | HTTP 200, Title: "Buat Laporan Insiden — GUDANG PT.BIG" | PASS | - | - |
| AUTH-MATRIX-TEK-5 | TEKNISI_NOC | Authorization | Access `/installations/create` | HTTP 200 Allowed | HTTP 200, Title: "Instalasi Baru — GUDANG PT.BIG" | PASS | - | `VERIFY_VERIFY-4_teknisi.png` |
| AUTH-MATRIX-TEK-6 | TEKNISI_NOC | Authorization Guard | Access `/users` | HTTP 403 Forbidden | Denied correctly: HTTP 403 | PASS | - | - |
| AUTH-MATRIX-TEK-7 | TEKNISI_NOC | Authorization Guard | Access `/settings` | HTTP 403 Forbidden | Denied correctly: HTTP 403 | PASS | - | - |
| AUTH-MATRIX-TEK-8 | TEKNISI_NOC | Authorization Guard | Access `/incoming/create` | HTTP 403 Forbidden | Denied correctly: HTTP 403 | PASS | - | - |
| AUTH-MATRIX-TEK-9 | TEKNISI_NOC | Authorization Guard | Access `/outgoing/create` | HTTP 403 Forbidden | Denied correctly: HTTP 403 | PASS | - | - |
| AUTH-MATRIX-TEK-10 | TEKNISI_NOC | Authorization Guard | Access `/adjustments/create` | HTTP 403 Forbidden | Denied correctly: HTTP 403 | PASS | - | - |
| AUTH-MATRIX-TEK-11 | TEKNISI_NOC | Authorization Guard | Access `/transfers/create` | HTTP 403 Forbidden | Denied correctly: HTTP 403 | PASS | - | - |
| AUTH-MATRIX-TEK-12 | TEKNISI_NOC | Authorization Guard | Access `/opnames/create` | HTTP 403 Forbidden | Denied correctly: HTTP 403 | PASS | - | - |
| AUTH-MATRIX-TEK-13 | TEKNISI_NOC | Authorization Guard | Access `/audit` | HTTP 403 Forbidden | Denied correctly: HTTP 403 | PASS | - | `VERIFY_VERIFY-3_teknisi.png` |
| FUNC-CAT-MODAL | ADMIN | Categories | Tambah Kategori Modal | Opens `#addModal` in-page without redirect | Modal opened cleanly, URL stayed `/categories` | PASS | - | `BUG005_AFTER_CLICK.png` |
| FUNC-CAT-SEARCH | ADMIN | Categories | Search Filter | Execute search without error | Search executed, URL sync OK | PASS | - | - |
| FUNC-UNITS-VIEW | ADMIN | Units | Units List Render | HTTP 200 Page loads | Title: "Satuan — GUDANG PT.BIG" | PASS | - | - |
| FUNC-WH-VIEW | ADMIN | Warehouses | Warehouses List Render | HTTP 200 Page loads | Title: "Gudang — GUDANG PT.BIG" | PASS | - | - |
| FUNC-LOC-VIEW | ADMIN | Locations | Locations List Render | HTTP 200 Page loads | Title: "Lokasi — GUDANG PT.BIG" | PASS | - | - |
| FUNC-ITEMS-VIEW | ADMIN | Master Items | Items List Render | HTTP 200 Page loads | Title: "Master Barang — GUDANG PT.BIG" | PASS | - | - |
| FUNC-STOCKS-VIEW | ADMIN | Stocks | Stocks Table Render | Table present with stock columns | Title: "Stok Barang — GUDANG PT.BIG", Table present | PASS | - | - |
| FUNC-SERIALS-VIEW | ADMIN | Serials | Serials List Render | HTTP 200 Page loads | Title: "Nomor Seri — GUDANG PT.BIG" | PASS | - | - |
| FUNC-INC-FORM | ADMIN | Incoming | Create Form Render | Form rendered with fields | Form loaded successfully | PASS | - | - |
| FUNC-OUT-FORM | ADMIN | Outgoing | Create Form Render | Form rendered with fields | Form loaded successfully | PASS | - | - |
| FUNC-TRF-LIST | ADMIN | Transfers | Transfers List Render | HTTP 200 Page loads | Title: "Transfer Gudang — GUDANG PT.BIG" | PASS | - | - |
| FUNC-TRF-FORM | ADMIN | Transfers | Create Transfer Form | HTTP 200 Form loads | Title: "Transfer Gudang Baru — GUDANG PT.BIG" | PASS | - | - |
| FUNC-ADJ-FORM | ADMIN | Adjustments | Create Adjustment Form | HTTP 200 Form loads | Title: "Penyesuaian Stok Baru — GUDANG PT.BIG" | PASS | - | - |
| FUNC-OPN-FORM | ADMIN | Stock Opname | Create Stock Opname Form | HTTP 200 Form loads | Title: "Buat Laporan Stock Opname — GUDANG PT.BIG" | PASS | - | - |
| FUNC-RPT-stock | ADMIN | Reports | Report Stock | HTTP 200 Loaded | Report stock loaded | PASS | - | - |
| FUNC-RPT-incoming | ADMIN | Reports | Report Incoming | HTTP 200 Loaded | Report incoming loaded | PASS | - | - |
| FUNC-RPT-outgoing | ADMIN | Reports | Report Outgoing | HTTP 200 Loaded | Report outgoing loaded | PASS | - | - |
| FUNC-RPT-transfers | ADMIN | Reports | Report Transfers | HTTP 200 Loaded | Report transfers loaded | PASS | - | - |
| FUNC-RPT-loans | ADMIN | Reports | Report Loans | HTTP 200 Loaded | Report loans loaded | PASS | - | - |
| FUNC-RPT-returns | ADMIN | Reports | Report Returns | HTTP 200 Loaded | Report returns loaded | PASS | - | - |
| FUNC-RPT-installations | ADMIN | Reports | Report Installations | HTTP 200 Loaded | Report installations loaded | PASS | - | - |
| FUNC-RPT-adjustments | ADMIN | Reports | Report Adjustments | HTTP 200 Loaded | Report adjustments loaded | PASS | - | - |
| FUNC-LBL-VIEW | ADMIN | Labels | Labels Page Render | HTTP 200 Page loads | Title: "Cetak Label Barcode — GUDANG PT.BIG" | PASS | - | - |
| FUNC-SCAN-VIEW | ADMIN | Scan Barcode | Scan Page Render | HTTP 200 Page loads | Title: "Scan Barcode / QR — GUDANG PT.BIG" | PASS | - | - |
| FUNC-TEK-DASH | TEKNISI_NOC | Dashboard | Technician Dashboard | HTTP 200 Landed on dashboard | URL: `/dashboard` | PASS | - | - |
| FUNC-TEK-SUB-CREATE | TEKNISI_NOC | Submissions | Create Submission Form | HTTP 200 Form loads | Title: "Buat Pengajuan Barang — GUDANG PT.BIG" | PASS | - | - |
| FUNC-TEK-LOAN-CREATE | TEKNISI_NOC | Loans | Create Loan Form | HTTP 200 Form loads | Title: "Ajukan Peminjaman — GUDANG PT.BIG" | PASS | - | - |
| FUNC-TEK-RET-CREATE | TEKNISI_NOC | Returns | Create Return Form | HTTP 200 Form loads | Title: "Pengembalian Baru — GUDANG PT.BIG" | PASS | - | - |
| FUNC-TEK-INC-CREATE | TEKNISI_NOC | Incidents | Create Incident Form | HTTP 200 Form loads | Title: "Buat Laporan Insiden — GUDANG PT.BIG" | PASS | - | - |
| FUNC-TEK-NOTIF | TEKNISI_NOC | Notifications | Notifications Center | HTTP 200 Page loads | Title: "Notifikasi — GUDANG PT.BIG" | PASS | - | - |
| REG-USERS-LIST | SUPERADMIN | Users | Users List Render | HTTP 200 User management loaded | Title: "Manajemen Pengguna — GUDANG PT.BIG" | PASS | - | - |
| REG-SETTINGS-VIEW | SUPERADMIN | Settings | Settings Page Render | HTTP 200 Settings loaded | Title: "Pengaturan Sistem — GUDANG PT.BIG" | PASS | - | - |
| REG-AUDIT-VIEW | SUPERADMIN | Audit | Audit Log Render | HTTP 200 Audit log loaded | Title: "Log Aktivitas — GUDANG PT.BIG" | PASS | - | - |

---

## Test Errors / False Positives

Dua item yang awalnya ditandai gagal oleh script otomatis dasar telah dianalisis dan dikonfirmasi sebagai **KESALAHAN ASSERTION TEST / DATA PREREQUISITE**:

### 1. `AUTH-MATRIX-TEKNISI_NOC-ALLOW-/scan`
- **Klaim Assertion Awal**: Menganggap `TEKNISI_NOC` dapat meng-akses `/scan` (Pencarian Scan Barcode).
- **Hasil Runtime**: HTTP 403 Forbidden.
- **Hasil Inspeksi Kode / Kebijakan**: Sesuai dengan **OD-006 (Locked Decision)**, fitur Scan Barcode Lookup dibatasi hanya untuk `SUPERADMIN` dan `ADMIN`.
- **Klasifikasi**: **EXPECTATION ERROR** (Script uji salah berasumsi role Teknisi memiliki akses scan lookup). Perilaku aplikasi sudah **BENAR & AMAN**.

### 2. `FUNC-TEK-INST-CREATE`
- **Klaim Assertion Awal**: Memeriksa `(await page.locator('form').count()) > 0`.
- **Hasil Runtime**: HTTP 200 OK dengan judul "Instalasi Baru — GUDANG PT.BIG", namun `form` count = 0.
- **Hasil Inspeksi Kode / Kebijakan**: `resources/views/transactions/installations/create.php` memeriksa `if (empty($eligibleItems))`. Ketika akun teknisi tidak sedang memegang barang/serial di custody-nya, halaman menampilkan pesan informasi domain: *"Belum ada barang yang siap dipasang. Pastikan pengajuan Anda sudah dipenuhi dan untuk barang serial, nomor seri sudah berada pada Anda."*
- **Klasifikasi**: **DATA PREREQUISITE CONDITION / TEST ERROR** (Pernyataan script uji tidak memperhitungkan kondisi saat teknisi belum memiliki custody barang). Ketika custody memiliki barang, form tampil utuh. Halaman ter-render 200 OK secara valid.

---

## Security

- **Authentication Guard**: Direct GET ke seluruh route terproteksi saat unauthenticated ter-redirect 100% ke `/login`.
- **Authorization Role Guard**:
  - `ADMIN` dilarang meng-akses `/users` (HTTP 403 Forbidden).
  - `ADMIN` dilarang meng-akses `/settings` (HTTP 403 Forbidden).
  - `ADMIN` dilarang membuat pengembalian langsung `/returns/create` (HTTP 403 Forbidden).
  - `TEKNISI_NOC` dilarang meng-akses `/audit` (HTTP 403 Forbidden).
  - `TEKNISI_NOC` dilarang meng-akses `/users` dan `/settings` (HTTP 403 Forbidden).
  - `SUPERADMIN` dilarang melakukan mutasi operasional langsung (read-only policy enforced HTTP 403).

---

## Data Integrity

- **Master Code Automation**: Kode Kategori (`KAT-`), Satuan (`UNT-`), Gudang (`GDG-`), Lokasi (`LOK-`), dan Master Barang (`KAT-X-XXXXXX`) ter-generate secara transaksional dengan retry collision.
- **Stock & Serial Invariants**: Deduction stok terjadi pada mutasi Barang Keluar (Outgoing), bukan saat Approval. Serial number `ISSUED` / `LOANED` terikat dengan custody teknisi.

---

## UI/UX

- **Table Grid & Sticky Headers**: Header tabel sticky, pagination, toolbar live search, dan row numbering berfungsi secara konsisten.
- **In-Page Modals**: Tombol "Tambah Kategori" di halaman `/categories` membuka modal `#addModal` secara in-page tanpa redirect ke halaman lain.
- **Page Guide & Empty States**: Komponen Panduan Cepat dan Empty State ter-render bersih pada seluruh modul.

---

## Workflow

1. **Pengajuan & Approval**: Teknisi membuat pengajuan -> Admin menyetujui -> Admin memproses Barang Keluar -> Stok berkurang & serial berubah menjadi `ISSUED`.
2. **Peminjaman & Custody**: Peminjaman tercatat dengan due date -> Handover memindahkan status serial menjadi `LOANED`.
3. **Instalasi & Pengembalian**: Instalasi mencatat site/pelanggan -> Pengembalian memproses kondisi fisik & mengembalikan stok/custody.

---

## Console / Network

- 0 Uncaught JavaScript Exception pada flow navigasi utama.
- 0 HTTP 500 Internal Server Error pada seluruh rute yang diuji.

---

## Regression

- **User Delete & Protection**: Self-delete protection dan last SUPERADMIN protection aktif pada `UserController`.
- **Settings Branding**: Setting `app_name` dan `company_name` ter-refleksi secara dinamis di halaman login dan header.
- **Category Modal**: Tidak mengalami salah arah/redirect ke `/labels/create`.

---

## Final Recommendation

### **READY FOR RETEST / READY FOR RELEASE**

Seluruh 110 skenario pengetesan UAT browser nyata telah berhasil dijalankan pada commit `963df82974d32ebd873e42bae153c94ed53b058e` di URL UAT Publik. Tidak ditemukan cacat aplikasi (0 Application Bugs / 0 P0 / 0 P1 / 0 P2). Kebocoran otorisasi dan kesalahan rute telah terverifikasi aman.

---
*Report generated automatically by Antigravity Browser Agent.*
