# BIGNET MENU & ROLE MATRIX

## ROLE PRINCIPLES

### Super Admin
System/account/role/settings/monitoring/audit. **Read-only pada semua modul operasional** (Reconciliation 2026-08-16).

### Admin / Warehouse Admin
Inventory/master data/operational warehouse/reporting. Role nyata: `ADMIN` (role `ADMIN_GUDANG` di controller diselaraskan ke `ADMIN` — lihat catatan).

### Teknisi / Field User
Request/custody/use/installation/return/evidence/damage/lost.

## MATRIX (verified 2026-08-16 Playwright + **2026-08-17 FINAL BROWSER UAT PASS (Antigravity)** — installation raw enum fixed, return role flow correct, role flow SUPERADMIN/ADMIN/TEKNISI verified)

| Menu/Module | Super Admin | Admin | Teknisi | Business Purpose | Verification |
|---|---|---|---|---|---|
| Dashboard | Monitor (fokus monitoring/sistem, tanpa CRUD gudang) | Monitor + quick action operasional | Scope sesuai permission (personal) | Monitoring/analytics | PASS |
| Pencarian Barang | View | Use | Use sesuai scope | Find inventory | PASS |
| Master Barang | View/Monitor (read-only) | Manage | View sesuai scope | Item master | PASS |
| Kategori | View/Monitor (read-only) | Manage (termasuk bulk delete aman) | View | Classification | PASS |
| Satuan | View/Monitor (read-only) | Manage (termasuk bulk delete aman) | View | Unit | PASS |
| Gudang | View/Monitor (read-only) | Manage (termasuk bulk delete aman) | View sesuai scope | Warehouse | PASS |
| Lokasi | View/Monitor (read-only) | Manage (termasuk bulk delete aman) | View sesuai scope | Location | PASS |
| Stok | Monitor | Manage | View scope | Current inventory | PASS |
| Nomor Seri | Monitor | Manage | View scope | Serialized asset | PASS |
| Cetak Label Massal (Barcode/QR, D-017) | Print (pemilihan + filter + preview) | Print (operasional batch) | No (403) | Label printing | PASS |
| Pengajuan | Monitor | Process/Approve | Create/View own | Request | PASS |
| Barang Masuk | Monitor (read-only) | Manage | View if relevant | Receive | PASS |
| Barang Keluar | Monitor (read-only) | Manage | Receive/confirm scope | Issue/custody | PASS |
| Transfer | Monitor (read-only) | Manage (receive) | Receive/confirm scope | Movement | PASS |
| Peminjaman | Monitor | Manage | Request/return scope | Custody | PASS |
| Pengembalian | Monitor (read-only) | Process (via detail, `process()` ADMIN) | **Create/return own** (tombol "Ajukan Pengembalian" hanya TEKNISI) | Return | PASS |
| Instalasi | Monitor (read-only) | Monitor/Process | Create/View own | Deployment | PASS |
| Penyesuaian | Monitor (read-only — tanpa tombol "Penyesuaian Baru") | Process with control | No unrestricted adjustment | Correction | PASS |
| Insiden (Damage/Lost) | Monitor (read-only — tanpa create/process) | Process (resolve) | Report (pelapor) | Accountability | PASS |
| Stock Opname | Monitor + **approve variance (D-014, locked)** | Rekam (create/isi) | No | Physical vs system | PASS |
| Timeline | Monitor | Manage/view | View own/scope | Traceability | PASS |
| Audit | View | Restricted | No unrestricted access | Accountability | PASS |
| Users | Manage | No | No | Accounts | PASS |
| Settings | Manage | Restricted | No | System settings | PASS |

**CATATAN (2026-08-16):**
- `ADMIN_GUDANG` direferensikan controller (Incident/StockOpname) namun tidak ada di enum DB `users` (`ENUM('SUPERADMIN','ADMIN','TEKNISI_NOC')`) — diselaraskan sementara ke role nyata `ADMIN`. Keputusan final (adopsi ADMIN_GUDANG atau penggabungan) menunggu **OD-003** / Product Owner. Jangan menambah role baru tanpa keputusan.
- **D-015 (2026-08-16):** Superadmin-created user langsung APPROVED + ACTIVE (tanpa persetujuan ulang); registrasi publik tetap PENDING.
- **D-016 (2026-08-16):** Password recovery internal — SUPERADMIN-only (Users → Reset Password); alur email/reset-token publik dihapus (tabel `password_reset_tokens` di-drop).
- **D-017 (2026-08-16):** Cetak label wajib massal — `/labels/create` (filter, Pilih Semua, qty bulk non-serialized, preview jumlah, proteksi duplikat, audit `LABEL_PRINT`, lembar A4).
- Matrix ini adalah business target. Perubahan role di luar tabel di atas memerlukan keputusan Product Owner.
