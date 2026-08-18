# MASTER BIGNET PROJECT V2
## Unified Product, Business Logic, System Architecture & AI-Agent Operating Contract

**Status:** MASTER / SINGLE SOURCE OF TRUTH  
**Scope:** BIGNET ISP Inventory, Warehouse, Asset, Accountability & Traceability System  
**Purpose:** Menyatukan requirement produk, business flow, role, menu, UI standards, backend rules, database expectations, Graphify, Playwright, implementation status, dan aturan kerja AI Agent tanpa membiarkan logika saling bertabrakan.

---

# 0. ATURAN KEBENARAN

Dokumen ini adalah target produk dan aturan kerja. Source code saat ini adalah kondisi implementasi. Graphify adalah peta teknis. Playwright adalah bukti perilaku browser.

## Hirarki

### Untuk PRODUCT / BUSINESS REQUIREMENT

`LOCKED REQUIREMENT → PRODUCT OWNER DECISION → IMPLEMENTATION`

### Untuk CURRENT STATE

`SOURCE + DATABASE + RUNTIME + PLAYWRIGHT → kondisi aktual`

`GRAPHIFY → evidence arsitektur/dependency`

Graphify tidak boleh mengubah requirement.

Playwright tidak menentukan business logic; Playwright membuktikan perilaku.

Source code tidak boleh dijadikan alasan untuk mengubah requirement.

---

# 1. TUJUAN BESAR PRODUK

Sistem dibuat terutama untuk menyelesaikan masalah kehilangan barang, selisih stok, dan sulitnya mengetahui siapa yang terakhir bertanggung jawab atas suatu barang.

Sistem bukan sekadar CRUD.

Sistem harus mampu menjawab, dengan bukti yang dapat ditelusuri:

- barang apa;
- kategori dan item apa;
- serial number jika serialized;
- quantity;
- berada di warehouse/location mana;
- siapa pemegang/penanggung jawab;
- kapan masuk;
- dari mana asalnya;
- kapan dan mengapa berpindah;
- siapa yang meminta;
- siapa yang menyetujui;
- siapa yang memproses;
- siapa yang menerima;
- kapan keluar;
- untuk pekerjaan apa;
- di mana dipasang;
- kapan dikembalikan;
- kondisi saat kembali;
- apakah rusak atau hilang;
- evidence/foto apa yang tersedia;
- seluruh timeline dan audit trail.

**Ukuran keberhasilan utama: accountability + traceability + inventory integrity.**

---

# 2. LOCKED PRODUCT PRINCIPLES

## 2.1 Traceability

Semua movement penting harus dapat ditelusuri.

Untuk serialized asset: sampai serial number.

Untuk non-serialized stock: sampai item, quantity, transaction, location, actor, timestamp, dan evidence yang relevan.

## 2.2 Accountability

Transaksi relevan wajib memiliki:

- actor;
- timestamp;
- reference transaction;
- source/destination bila berlaku;
- responsibility/custody bila berlaku.

Field seperti `created_by`, `approved_by`, `processed_by`, `received_by`, `requested_by`, `assigned_to` digunakan sesuai konteks. Tidak semua harus muncul di semua transaksi.

## 2.3 Timestamp

Tanggal/waktu wajib untuk transaksi dan event yang relevan:

- incoming;
- request;
- approval;
- outgoing;
- transfer;
- borrowing;
- return;
- installation;
- damage;
- lost;
- stock opname;
- adjustment;
- audit;
- status change;
- movement.

## 2.4 Evidence

Evidence/foto/dokumen adalah bagian dari accountability.

Jika suatu workflow membutuhkan evidence:

- upload harus nyata;
- tersimpan;
- terkait dengan record/transaksi;
- dapat dilihat kembali;
- metadata relevan tersimpan.

## 2.5 Timeline

Timeline adalah fitur inti.

Timeline harus mampu menggambarkan event yang benar-benar terjadi, misalnya:

`Incoming → Stored → Request → Approval → Outgoing → Assigned → Transfer → Installed → Return → Damage/Lost/Repair/Retire`

Urutan tidak boleh memaksa event yang tidak terjadi.

Setiap event yang ada harus membawa, sesuai konteks:

- timestamp;
- actor;
- event type;
- item/serial;
- source/destination;
- quantity;
- transaction reference;
- evidence;
- status before/after bila relevan.

## 2.6 No Dead UI

Tidak boleh ada:

- dead menu;
- broken route;
- 404;
- tombol dummy;
- modal kosong;
- fake success;
- silent failure;
- page placeholder yang dianggap selesai;
- frontend yang terlihat selesai tetapi backend tidak menyimpan.

## 2.7 No Guessing

Jika business logic tidak jelas atau bertentangan:

**STOP → tandai OPEN DECISION → minta Product Owner.**

AI Agent tidak boleh memilih sendiri hanya karena salah satu pilihan lebih mudah diimplementasikan.

---

# 3. ROLE MODEL

## 3.1 SUPER ADMIN

Tujuan:
administrasi sistem dan kontrol global.

Wewenang utama:
- users;
- roles;
- permissions;
- system settings;
- monitoring;
- audit;
- administrasi global;
- kontrol master data bila memang diberikan kewenangan.

Catatan:
Hak superuser teknis tidak berarti Super Admin otomatis menjadi actor bisnis untuk seluruh proses operasional.

## 3.2 ADMIN / WAREHOUSE ADMIN

Tujuan:
mengelola operasi inventory/gudang.

Wewenang utama:
- master data operasional;
- warehouse/location;
- inventory;
- incoming;
- outgoing;
- transfer;
- pemrosesan request;
- approval sesuai workflow yang disetujui;
- borrowing/return processing;
- stock opname;
- adjustments sesuai aturan;
- laporan operasional.

Admin bukan requester utama kebutuhan lapangan.

## 3.3 TEKNISI / FIELD USER

Tujuan:
menjadi requester dan actor lapangan.

Wewenang utama:
- membuat request;
- melihat status request;
- menerima/menggunakan item sesuai workflow;
- melihat custody/asset tanggung jawabnya;
- installation;
- return;
- evidence;
- damage/lost reporting;
- melihat timeline item yang menjadi tanggung jawabnya sesuai permission.

---

# 4. AUTHENTICATION & ACCOUNT

Harus stabil dan sederhana.

Wajib:

- login;
- logout;
- session;
- protected route;
- role-based authorization;
- credential validation;
- error message;
- tidak redirect loop;
- tidak blank page;
- tidak silent fail.

Super Admin mengelola akun.

Account lifecycle:

`Created → Pending Approval (bila workflow aktif) → Approved/Rejected → Active/Inactive`

Server wajib menegakkan authorization. Hide menu di frontend saja tidak cukup.

### User Management Rule (Superadmin vs Public Registration)

Dua workflow akun yang **tidak boleh dicampur**:

- **Public Registration** → `PENDING_APPROVAL` (Menunggu Persetujuan) → Approve / Reject (alasan penolakan wajib). Registrasi publik TIDAK boleh otomatis aktif.
- **Superadmin Create User** (menu Pengguna → Tambah) → langsung `APPROVED + is_active=1` (**Aktif**), tanpa approval, langsung bisa login.
- **Superadmin Edit User** (Ubah: nama/email/role/password/profil) → **mempertahankan status existing**. Perubahan data TIDAK boleh mengubah status menjadi Nonaktif.
- **Superadmin Change Password / Role / Profile** → **mempertahankan status existing** (user tetap Aktif; tidak perlu aktivasi ulang manual).

**Status `Aktif/Nonaktif` hanya berubah melalui perubahan field Status yang eksplisit** (form Ubah → Status Akun, atau tombol Nonaktifkan/Aktifkan di detail). Side effect seperti `Password berubah → Nonaktif` adalah bug. Superadmin tidak bisa menonaktifkan akun sendiri.

Tombol `Tinjau`/approval hanya tampil untuk user berstatus `PENDING_APPROVAL` (asal public registration); user yang dibuat langsung Superadmin tidak melalui workflow approval.

### Branding Login (dinamis dari Pengaturan Sistem)

Branding halaman Login **wajib** bersumber dari Pengaturan Sistem (Superadmin), bukan hardcode:

- **Headline "Welcome to …"** (h1 panel branding kiri) ← `app_name` (Pengaturan Sistem → Umum → **Nama Aplikasi**), di-escape (`htmlspecialchars`) saat dirender. Fallback default project: `GUDANG PT.BIG`. **Bukan** `company_name`.
- **Nama perusahaan** (title tab, teks di samping logo, header mobile) ← `company_name` (Pengaturan Sistem → Umum → Nama Perusahaan), di-escape (`htmlspecialchars`) saat dirender. Fallback default project: `PT. BIG`.
- **Logo** ← `logo_path` (Pengaturan Sistem → Branding & Tampilan → Logo Utama). Logo/favicon dilayani via endpoint publik `/logo` & `/favicon` (`StaticController`, baca nilai dari settings — bukan input user; basename + ekstensi whitelist + `is_file`; tanpa traversal dan tanpa menampilkan path filesystem) karena `/file` mewajibkan login sehingga tidak bisa dipakai di halaman publik. Fallback bila logo kosong/file tidak ada: ikon `fa-boxes` (tanpa broken image).
- `login_title` (Judul Login) **tidak lagi** menjadi sumber nama di halaman Login; field tersebut tetap ada di Pengaturan Sistem tapi tidak dipakai view login.

---

# 5. GLOBAL UI STANDARD

Semua modul harus menggunakan pola UI yang konsisten.

## 5.1 Tabel

Bila dataset membutuhkan table/grid, standar:

- row number;
- search;
- filter;
- reset;
- pagination;
- sorting bila relevan;
- date/timestamp bila relevan;
- status;
- action;
- empty state;
- loading state;
- error state.

Ketidakhadiran salah satu komponen hanya boleh bila memang tidak relevan secara bisnis, bukan karena lupa.

## 5.2 Form

Wajib:

- label;
- required indicator;
- validation;
- error message;
- loading state;
- disabled state saat submit;
- success feedback;
- failure feedback;
- confirmation untuk destructive action.

## 5.3 Actions

Setiap tombol harus punya fungsi nyata dan jalur backend/data yang jelas.

## 5.4 Feedback UI — Global Standard (AppModal + AppToast)

**Global Feedback UI Standard (2026-08-17):**

1. Native browser `alert()` **tidak digunakan** untuk UI user-facing.
2. Native browser `confirm()` **tidak digunakan** untuk UI user-facing.
3. Konfirmasi menggunakan **custom modal BIGNET** (`AppModal.confirm`).
4. Success/error/warning/info menggunakan **notification/toast internal** (`AppToast.success/error/warning/info`).
5. Validasi form menggunakan **inline validation** bila memungkinkan (bukan popup).
6. Semua modal/toast mengikuti theme BIGNET (light/dark via CSS variable/class existing, Tailwind `dark:`).
7. Semua modul wajib memakai komponen feedback global yang sama.

Implementasi: `public/assets/js/app-feedback.js` (dilayani via route `/assets/js/app-feedback.js` → `StaticController@feedbackJs`, dimuat oleh `layouts/app.php` di seluruh halaman aplikasi).

**API programatik:**

```javascript
AppModal.confirm({ title, message, description, type: 'danger|warning|info|success',
                   icon, confirmText, cancelText, loadingText, onConfirm });
AppModal.close();
AppModal.setLoading(true|false, [loadingText]);
AppToast.success('Pesan'[, 'Judul']);
AppToast.error('Pesan'[, 'Judul']);
AppToast.warning('Pesan'[, 'Judul']);
AppToast.info('Pesan'[, 'Judul']);
```

**API deklaratif** (tanpa inline JS): form atau tombol submit cukup membawa atribut `data-confirm` (+ `data-confirm-title`, `data-confirm-description`, `data-confirm-type`, `data-confirm-icon`, `data-confirm-confirm`, `data-confirm-cancel`, `data-confirm-loading`); event di-intercept global (delegated `submit`/`click`) lalu request diteruskan setelah konfirmasi.

Perilaku modal: overlay/backdrop (klik backdrop = batal), ESC menutup (aksi non-wajib), fokus masuk modal lalu kembali ke elemen pemicu, tombol `Batal` selalu berfungsi, tombol utama menampilkan state **loading ("Memproses…")** dan dinonaktifkan untuk mencegah double-submit, body tidak scroll saat modal terbuka, teks dirender via `textContent` (aman dari injeksi), dan modal responsive di desktop/tablet/mobile. Ikon mengikuti tipe aksi (warning/delete/deactivate/activate/reset/key/dll).

**Tidak ada lagi native browser dialog untuk UI aplikasi** — seluruh aksi (nonaktifkan/aktifkan akun, reset password, approve/reject pendaftaran, hapus bukti, bulk delete, proses import, keputusan insiden/opname, persetujuan/tolak pengajuan, terima transfer) dan notifikasi (sukses/gagal/perhatian/info, validasi form) memakai komponen di atas. Otorisasi tetap sepenuhnya di backend (modal bukan security control).

## 5.5 Role Display Label Standard

**Role Display Label Standard (2026-08-17):**

| Internal Value | Display Label |
| -------------- | ------------- |
| `TEKNISI_NOC`  | `Teknisi/NOC`  |
| `ADMIN`        | `Admin`        |
| `SUPERADMIN`   | `Superadmin`   |

`TEKNISI_NOC` (dan `ADMIN`/`SUPERADMIN`) adalah **identifier internal** — dipakai untuk database value, enum/constant, authorization (`requireRole`/`hasRole`), session, controller logic, query/filter backend, API value, dan route logic. Nilai internal **tidak berubah**. Perubahan hanya menyangkut **presentation/display** — label yang dilihat user via `\App\Helpers\UIHelper::roleLabel($role)` (helper terpusat; fallback = nilai asli bila role tidak dikenal).

Area user-facing yang memakai `roleLabel()`: dropdown Role di form Buat/Ubah Pengguna & pendaftaran publik, badge Role di tabel Manajemen Pengguna, badge Role di detail Pengguna, label role di header/dropdown user (layout), narasi aktivitas dashboard, dan metadata "Dicetak Oleh" pada export PDF laporan. Standard display wajib tepat `Teknisi/NOC` (bukan `Teknisi NOC`/`TEKNISI NOC`/`TEKNISI_NOC`).

## 5.6 Master Data Code Rule

**Master Data Code Rule (2026-08-17):**

| Modul | Field | Keputusan | Format | Immutable | Uniqueness |
|---|---|---|---|---|---|
| Kategori Barang | `code` | **Auto** (pertahankan) | `KAT-000001` | Ya (hanya backend) | Unique (DB) |
| Satuan | `code` | **Auto** (baru) | `UNT-000001` | Ya (hanya backend) | Unique (DB) |
| Gudang | `code` | **Auto** (baru) | `GDG-000001` | Ya (hanya backend) | Unique (DB) |
| Lokasi | `code` | **Auto** (baru) | `LOK-000001` | Ya (hanya backend) | Unique global (index baru `uq_locations_code`) |
| Master Barang | `kode_barang` | **Auto** (pertahankan) | `{KODE_KATEGORI}-000001` (contoh `KAT-000001-000001`) | Ya (hanya backend) | Unique (DB) |

**Generator**: pola sama di backend (`store()` tiap controller) — `SELECT … LIKE 'PREFIX-%' ORDER BY id DESC LIMIT 1 FOR UPDATE` → counter 6 digit zero-padded → retry maks 5x saat collision `1062` (duplicate), semua dalam transaksi. User TIDAK mengisi kode; form Tambah menampilkan `[Auto Generated]` + helper text; form Ubah menampilkan kode **readonly/disabled** (tidak dapat diubah). Kode tidak berubah ketika nama/kategori/stok diubah.

**Alasan keputusan:** convention existing dari data live (backup) sudah `UNT-`/`GDG-`/`LOK-` + 6 digit — pola sama persis dengan Kategori/Master Barang yang sudah auto sejak awal; kode adalah identifier sistem, bukan business code yang dikontrol perusahaan (tidak ada pola bisnis khusus per entity master). Format tetap mengikuti convention existing; tidak ada kode existing yang diregenerate (task hanya mengubah behavior record baru + UI).

## 5.7 Global Action & Lifecycle Standard

**Global Action & Lifecycle Standard (2026-08-17):** action yang tersedia ditentukan oleh **Role + Status + Relasi Data** — bukan sekadar visibility UI. Backend adalah source of truth (UI hiding bukan security control).

### 5.7.1 Global Row Action Standard (UI)

- Tabel user-facing memakai pola compact **`[ icon + Lihat ] [ ⋮ ]`**; action lain masuk menu `⋮` yang isinya **dinamis** berdasarkan role, status, dan relasi/usage data.
- Ikon memakai **Font Awesome existing** (bukan emoji). Destructive/workflow action tetap menyertakan label; ikon-only hanya dengan tooltip + aria-label.
- Konfirmasi destructive/workflow memakai **AppModal** (`data-confirm` delegated di `public/assets/js/app-feedback.js`); feedback sukses/error memakai **AppToast**. Tidak ada native `alert()`/`confirm()`.
- Komponen: `resources/views/components/table/row_actions.php` (menerima `$rowActions` array: `view`/`link`/`form`/`button`). Dipakai di Master Barang, Manajemen Pengguna, Gudang, Transfer.

### 5.7.2 Delete Policy (Global)

| Kategori | Definisi | Contoh |
|---|---|---|
| **DELETE ALLOWED** | Data benar-benar belum pernah digunakan/berelasi dan aman dihapus | Kategori/Satuan/Gudang/Lokasi belum dipakai; Master Barang `is_used=0` |
| **CANCEL** | Data masih request/draft dan dihentikan sebelum berdampak | (Gap: tidak ada route cancel/edit pada Pengajuan/Peminjaman — lihat Gap) |
| **NONAKTIF** | Master masih dibutuhkan untuk history tetapi tidak boleh dipakai baru | Master Barang/Gudang `is_active` |
| **REVERSE/CORRECT** | Transaksi sudah posted/berdampak bisnis; koreksi lewat workflow sah | Barang Keluar/Transfer/Adjustment dikoreksi lewat transaksi baru, bukan hapus |
| **IMMUTABLE** | Record final/approved/completed yang harus dipertahankan | Incoming/Outgoing/Transfer COMPLETED, Pengajuan APPROVED/REJECTED, Pengembalian proses, Instalasi terpasang, Opname approval, Adjustment posted, Insiden RESOLVED/REJECTED |

Prinsip: **Status ≠ Delete** · **Nonaktif ≠ Hapus** · **Hapus ≠ Kurangi Stok** · **Delete ≠ Cancel/Reversal**. Tidak ada Delete untuk record yang seharusnya Cancel/Reverse; modul tanpa reversal/corrective workflow **tidak dibuatkan reversal baru** di task ini — dicatat sebagai gap (lihat 5.7.6).

### 5.7.3 Action Matrix Final (hasil audit implementation 2026-08-17)

| Modul | Status | ADMIN | SUPERADMIN | TEKNISI_NOC | Delete | Edit |
|---|---|---|---|---|---|---|
| Kategori/Satuan/Gudang/Lokasi | — (Aktif/Nonaktif utk gudang) | Buat/Ubah/Hapus (bulk, guard used) | Read-only | Tidak ada akses | Hanya jika belum dipakai | Ubah ya; kode auto immutable |
| Master Barang | Aktif/Nonaktif | Buat/Ubah/Nonaktifkan/Aktifkan/Hapus (guard `is_used` + stok/serial) | Read-only (Lihat + Timeline) | Tidak ada akses | Hanya jika `is_used=0` | Ubah ya (status independen dari edit) |
| Barang Masuk / Keluar | posted | Buat | Read-only | Tidak ada akses | Tidak ada | Tidak ada (immutable) |
| Transfer | PENDING → IN_TRANSIT → COMPLETED | Buat + Terima (saat IN_TRANSIT) | Read-only | Tidak ada akses | Tidak ada | Tidak ada (immutable setelah COMPLETED) |
| Pengajuan | PENDING → APPROVED/PARTIAL/REJECTED → Fulfilled | Approve/Tolak (alasan wajib) | Read-only | Buat + lihat milik sendiri | Tidak ada | Tidak ada route edit/cancel (Gap) |
| Peminjaman | Dipinjam/Kembali Sebagian/Dikembalikan | (lihat existing) | Read-only | Buat + lihat milik sendiri | Tidak ada | Tidak ada (perubahan via Pengembalian) |
| Pengembalian | request → proses | Proses (Terima/inspeksi) | Read-only | Buat | Tidak ada | Tidak ada (immutable setelah proses) |
| Instalasi | issue → terpasang | — | Read-only | Buat + bukti foto | Tidak ada | Tidak ada (immutable setelah terpasang) |
| Penyesuaian Stok | posted | Buat | Read-only | Tidak ada akses | Tidak ada | Tidak ada (immutable) |
| Stock Opname | draft → variance → approval | Buat | **Approve variance** (rule existing) | Tidak ada akses | Tidak ada (jangan orphan adjustment) | Draft saja; setelah approval immutable |
| Incident | OPEN → INVESTIGATING → RESOLVED/REJECTED | Proses | Read-only | Buat + lihat | Tidak ada | Tidak ada (terkunci setelah RESOLVED/REJECTED) |
| Serial Number | lifecycle (AVAILABLE/dll) | Lihat/telusuri | Lihat/telusuri | Lihat milik sendiri | **Tidak ada Delete bebas** | Status hanya lewat transaksi resmi |
| Stok | monitoring | Lihat | Lihat | — | Tidak ada (bukan tempat hapus) | Tidak ada |
| Users | PENDING/APPROVED/REJECTED/SUSPENDED + aktif | — | Kelola penuh (Buat/Ubah/Approve/Reject/Toggle/Reset) | — | Tidak ada delete user | Ubah ya (status independen) |

### 5.7.4 Permission (backend source of truth)

- **Router global CSRF** untuk semua POST (token wajib) — lapisan pertama.
- **Master Data** (Kategori/Satuan/Gudang/Lokasi/Master Barang) + **Barang Masuk/Keluar/Transfer/Penyesuaian**: write `requireRole('ADMIN')`; SUPERADMIN read-only; TEKNISI tidak punya akses (halaman 403 + endpoint 403).
- **Pengajuan/Peminjaman/Pengembalian/Instalasi**: store `requireRole('TEKNISI_NOC')`; approve/process `requireRole('ADMIN')`; SUPERADMIN read-only.
- **Stock Opname**: buat ADMIN, approve variance SUPERADMIN (rule existing). **Incident**: lapor TEKNISI, proses ADMIN.
- **Users**: `requireRole('SUPERADMIN')` via constructor seluruh method.

### 5.7.5 Aturan Panduan (Quick Guide)

`PageGuideHelper` menyediakan seksi **"Aturan Action & Lifecycle"** di semua halaman modul (kategori, satuan, gudang, lokasi, barang masuk, barang keluar, transfer, pengajuan, peminjaman, pengembalian, instalasi, penyesuaian, opname, insiden, nomor seri) yang menjawab: siapa boleh melakukan apa, kapan boleh Edit, kapan boleh Hapus/Cancel, kapan data terkunci, siapa yang Approve, dan apa yang terjadi setelah Approved. Master Barang & Stok memakai seksi khusus (Status vs Delete; monitoring).

### 5.7.6 Gap yang didokumentasikan (tidak dibuat fitur baru di task ini)

- **Pengajuan/Peminjaman tidak memiliki route Edit/Cancel** — setelah submit, record tidak dapat direvisi/dibatalkan dari UI (perubahan = ajukan ulang). Rekomendasi: task terpisah "cancel/revise untuk request PENDING" bila disetujui PO.
- **Barang Masuk/Keluar/Transfer/Penyesuaian tidak memiliki Reversal** — koreksi salah input dilakukan via transaksi penyesuaian/koreksi baru; tidak ada mekanisme reverse one-click (rekomendasi task terpisah bila dibutuhkan).
- **Incident/Instalasi/Pengembalian tidak memiliki cancel sebelum diproses** — gap kecil bila dibutuhkan.

### 5.7.7 Global Row Action UI Standard (finalisasi 2026-08-17)

**Aturan render:**

| Kondisi | Render | Contoh |
|---|---|---|
| 1 action | `[ icon + label ]` — tanpa `⋮` | `[ 👁 Lihat ]`, `[ ✏ Ubah ]`, `[ 📍 Lihat Lokasi ]` |
| >1 action | `[ icon + primary ] [ ⋮ ]` — aksi lain di menu | `[ 👁 Lihat ] [ ⋮ (Ubah, Nonaktifkan, Hapus) ]` |
| 0 action tambahan | `⋮` TIDAK pernah dirender | — |

**Aturan wajib:**

1. **Tidak ada dropdown kosong** — komponen `row_actions.php` hanya merender tombol `⋮` bila `$menuItems` tidak kosong; tidak ada tombol disabled/placeholder.
2. **Satu component global** — `resources/views/components/table/row_actions.php` dipakai oleh SEMUA tabel user-facing yang punya row action (Master Barang, Kategori, Satuan, Gudang, Lokasi, Users, Transfer, Serials, Stocks, Pengajuan, Peminjaman, Pengembalian, Barang Keluar, Instalasi, Penyesuaian, Incident, Stock Opname). Tidak ada variasi row-action per halaman.
3. **Icon Font Awesome existing** (bukan emoji): Lihat `fa-eye` · Ubah `fa-edit` · Hapus `fa-trash` · Aktifkan `fa-play` · Nonaktifkan/Batalkan `fa-ban` · Setujui `fa-check` · Tolak `fa-times` · Proses `fa-cog` · Terima `fa-check-circle` · Lokasi `fa-map-marker-alt` · Perjalanan `fa-route` · Label `fa-barcode` · Tambah `fa-plus` · Cari `fa-search` · Cetak `fa-print` · Export `fa-download` · Import `fa-upload` · Reset Password `fa-key`. Satu action = satu icon yang sama di mana pun.
4. **Label selalu ada** untuk action utama dan destructive (`Hapus`, `Nonaktifkan`) — icon-only hanya bila aman + tooltip + aria-label.
5. **Semantik**: navigation = `<a>`; perubahan data = `<form method="POST">` (CSRF + `data-confirm` AppModal); modal = `<button type="button">`. POST tidak pernah diubah jadi GET demi UI.
6. **Accessibility**: tombol `⋮` ber-`type="button"`, `aria-label="Aksi lainnya"`, `aria-haspopup="true"`, `aria-expanded` berubah via JS (`false`/`true`), fokus terlihat (`focus-visible:ring`), ESC menutup menu, item menu dapat dicapai keyboard (link/button native).
7. **Behavior dropdown** (delegated di `public/assets/js/app-feedback.js`, tanpa listener per row): buka saat `⋮` diklik, tutup saat klik di luar / ESC / membuka menu lain; `closeAllActionsMenus()` mereset `aria-expanded`; tetap berfungsi setelah pagination/filter/search karena event delegated.
8. **Visual**: primary blue netral; menu light/dark (`dark:` classes); destructive merah (`text-red-600`/`dark:text-red-400`, hover `bg-red-50`/`dark:hover:bg-red-900/30`); menu **floating (portal-to-body, `position:fixed`)** — tidak pernah terpotong `overflow-x-auto`/`overflow-hidden`; responsive tanpa keluar viewport (lihat 5.7.8).
9. **Action mengikuti permission/status existing** — visibility per row dihitung pemanggil (role + status + relasi); **UI hiding bukan security control**; backend tetap memvalidasi.
10. **Search/Inventory 360** — hasil pencarian memakai style token yang sama (`fa-eye` + `Lihat`, border/rounded konsisten) meski dirender JS/API (komponen PHP tidak dipaksakan ke konteks non-tabel).

Halaman tanpa row action (Barang Masuk — tanpa halaman detail) tidak dirender kolom aksi; Stok adalah monitoring (action `Lihat` saja).

### 5.7.8 Global Row Action Visual Standard (finalisasi visual 2026-08-17)

Menyempurnakan 5.7.7 dari sisi **visual, positioning, dan interaksi** tanpa mengubah action matrix / permission / lifecycle.

**Aturan final:**

| Kondisi | Render | Contoh |
|---|---|---|
| 1 action | `[ icon + label ]` — SELESAI, TANPA `⋮` | `[ 📍 Lihat Lokasi ]`, `[ ✏ Ubah ]` |
| >1 action | `[ icon + primary ] [ ⋮ ]` — aksi lain di menu | `[ 👁 Lihat ] [ ⋮ ]` |
| tidak ada secondary action | `⋮` TIDAK pernah dirender (0 kasus dropdown kosong) | — |

**Layout kolom AKSI (component, bukan per halaman):**

1. **Semua action cell menggunakan horizontal + vertical center alignment yang konsisten** — `td` ber-`text-center align-middle` + `div.flex.w-full.items-center.justify-center.gap-2`; single action `[ ✏ Ubah ]` maupun group multi-action `[ 👁 Lihat ] [ ⋮ ]` sama-sama center di cell; `white-space:nowrap`; `min-w-[140px]` seragam di semua halaman (komponen global) sehingga isi action tidak menentukan lebar tabel secara liar.
2. **Header AKSI selalu `text-center`** di SEMUA halaman (Master Data, transaksi, search, timeline, detail) — header dan isi cell sejajar; tidak ada `text-left`/`text-right` pada header Aksi (fiks alignment 2026-08-17).
3. Tombol `⋮`: kecil & konsisten (`w-7 h-7`), center, `type="button"`, hover + `focus-visible:ring`, `aria-label="Aksi lainnya"`, `aria-haspopup="menu"`, `aria-expanded` berubah `false`/`true` saat menu dibuka/ditutup. Bukan text biasa, tidak ambigu.
4. Tidak ada `justify-end`/`ml-auto`/`mr-auto`/`float-right` pada trigger/group action — perbaikan hanya di component global, tanpa CSS per halaman.

**Dropdown floating (portal-to-body):**

1. Menu dibuka sebagai **portal ke `<body>`** (`position:fixed`) oleh `openActionsMenu()` di `app-feedback.js` — clone menu source (yang tetap `hidden` inline).
2. **Tidak mengubah tinggi/flow row** — floating overlay; tidak menggeser row; tidak menjadi bagian normal layout tabel.
3. **Tidak terpotong** container `overflow-x-auto`/`overflow-hidden`/animasi `fadeIn` (transform ancestor) karena dirender di `body`.
4. **Viewport-aware**: rata kanan ke sisi kanan tombol; flip ke kiri bila ruang kanan sempit; buka ke bawah; flip ke atas bila ruang bawah sempit; clamp dalam viewport.
5. `z-index` tinggi (90, di bawah AppModal 100); min-width nyaman (≥176px); menu tetap `bg-white dark:bg-gray-800` + border + shadow + rounded sesuai theme; item `[icon] Label` sejajar (`fa-* mr-2 w-4 text-center`); destructive `text-red-600`.
6. **Dark mode** penuh (background, hover, focus, border, icon, text).

**Interaksi JS (delegated global, tanpa listener per row):**

1. Klik `⋮` → buka; klik `⋮` yang sama → tutup; klik menu lain → menu sebelumnya tutup; klik luar → tutup; ESC → tutup (+ reset `aria-expanded`).
2. Hanya satu dropdown terbuka pada satu waktu.
3. **Scroll/resize → menu ditutup** (koordinat fixed tidak valid lagi) — tidak ada menu ghost setelah scroll/pagination/filter/search.
4. Keyboard: fokus otomatis ke item pertama saat buka (`preventScroll`), panah ↑/↓ navigasi item, Tab native, ESC tutup.
5. Tetap kompatibel AppModal `data-confirm` (klik item di dalam portal men-trigger konfirmasi; menu ditutup saat form submit).

**Tidak ada hack per halaman:** tidak ada `margin-left:-XXXpx`/`top:-XXXpx`/z-index random/CSS khusus Gudang/Master Barang — solusi sepenuhnya dari component + global JS.

**Verifikasi (Freebuff, tanpa Playwright):** HTTP render per role — SUPERADMIN: items 7⋮ (menu `Lihat Perjalanan`), serials 1⋮ (`Label`), users 3⋮, kategori/satuan/lokasi/gudang/submissions/loans/returns/transfers/incoming/outgoing/installations/adjustments/opnames/incidents/stocks **0⋮** (single action tanpa ⋮); ADMIN: items 7⋮ (Ubah/Aktifkan|Nonaktifkan/Hapus sesuai eligibility), warehouses 1⋮ (`Ubah`), serials 1⋮ (`Label`), kategori/satuan/lokasi `[✏ Ubah]` 0⋮, users 403. `php -l` + `node --check` bersih; 0 dropdown kosong; 0 PHP error.

### 5.7.9 Teknisi/NOC Mobile-First UI Standard (2026-08-17)

**Mobile adalah primary usage scenario untuk role `TEKNISI_NOC`** (360/390/412px). Desktop tetap rapi, tetapi tidak boleh ada horizontal overflow / text overlap / action terlalu kecil / field sempit di smartphone.

1. **Mobile Card Table (global, CSS-only ≤640px)** — `resources/views/layouts/app.php`: class `.mobile-card-table` mengubah tabel lebar menjadi stack card di mobile (header disembunyikan; tiap `<td>` menjadi baris `label + nilai` via `data-label`; empty-state satu sel ditampilkan tengah; cell Aksi tanpa label rata kanan; sticky No/Aksi dimatikan). Desktop TIDAK berubah. Diterapkan ke tabel utama Teknisi: Pengajuan, Peminjaman, Pengembalian, Nomor Seri, Instalasi, Barang Terpasang (`my_items`), Timeline.
2. **Touch target** — button/action ≥40–44px: tombol `⋮` global naik `w-9 h-9`, item menu `min-h-[40px]`; tombol form (`Tambah Barang`, submit, Batal) `min-h-[44px]`; hapus baris draft `w-11 h-11`.
3. **Form mobile** — satu kolom (grid-cols-1) di bawah `md:`; input `py-2.5`; label jelas; textarea tidak terlalu besar; keyboard tidak menutup input.
4. **Card/list untuk Teknisi** — Dashboard Saya, detail, dan list memakai kartu/badge (bukan tabel desktop yang dipaksa mengecil).
5. **Modal/toast** — AppModal/AppToast existing (tanpa native alert/confirm); dropdown portal-to-body tetap (tidak terpotong, viewport-aware).
6. **Navigation** — sidebar drawer mobile + hamburger existing; menu Teknisi hanya yang relevan (Dasbor Saya, Pengajuan, Barang Dipinjam, Pengembalian, Nomor Seri Saya, Barang Terpasang, Riwayat Saya, Instalasi, Foto Instalasi, Lapor Kerusakan/Kehilangan) sesuai permission existing.
7. **Foto** — `photos.php` grid card responsive; upload foto memakai `accept="image/*"` (browser mobile dapat membuka kamera).
8. **Security** — CSRF, authorization, ownership tetap backend; tidak ada pemindahan validasi ke frontend.

### 5.7.10 Pengajuan Barang UI (2026-08-17)

**Buat Pengajuan Barang (`/submissions/create`)** didesain ulang sebagai workflow request (bukan CRUD table):

1. **Alur Pengajuan** (helper biru): kirim → Menunggu Persetujuan Admin → gudang memproses pemenuhan. Teks tegas: *Pengajuan bukan Barang Keluar dan tidak langsung mengurangi stok.*
2. **Keperluan Pengajuan** — textarea opsional di atas form.
3. **Item = card** (bukan baris tabel): header berisi toggle sumber `📦 Dari Stok` / `✏ Manual` (Font Awesome) + tombol hapus `fa-trash` (touch-friendly, tanpa konfirmasi untuk baris draft); blok STOCK menampilkan pilihan barang + kode; blok MANUAL menampilkan nama/spesifikasi/satuan; `Jumlah` (input besar, min-h 44px, update summary); `Catatan Item` dengan placeholder contoh.
4. **Tambah Barang** — tombol `+` hijau full-width di mobile.
5. **Ringkasan** — `Jenis barang: N` + `Total jumlah: X` (tanpa klaim satuan yang menyesatkan).
6. **Submit area** — `Batal` (link) + `Kirim Pengajuan` (icon `fa-paper-plane`) full-width di mobile, min-height 44px.
7. **Payload identik** — nama field POST tidak berubah (`header_note`, `source_types[]`, `item_ids[]`, `manual_names[]`, `manual_specs[]`, `manual_units[]`, `quantities[]`, `notes[]`) → controller/store TIDAK berubah.
8. **CSRF init di dalam tag PHP** — tidak pernah keluar sebagai output HTML (fix leak, lihat CHANGELOG).

### 5.7.11 Inventory Identity & Stock Integrity Standard (2026-08-17)

**Identity rule:** `master_item_id` adalah identity utama Master Barang. `category_id` hanya klasifikasi; nama/kode hanya presentation/search. Seluruh chain (Barang Masuk → `stocks` → `stock_ledgers` → Search → Inventory 360 → Mutation History → Pengajuan STOCK → Barang Keluar) WAJIB mempertahankan `master_item_id` yang sama. Stok diidentifikasi oleh `master_item_id + warehouse_id`.

- **Audit P0 (hasil):** tidak ditemukan fallback berbasis kategori/nama/prefix di chain. Guard hardening ditambahkan: `IncomingController::store()` dan `OutgoingController::store()` kini menolak `master_item_id` yang tidak valid/nonaktif (**fail-safe**, bukan fallback ke barang lain) — mencegah stok orphan untuk ID basi.

### 5.7.12 Submission Stock Deduction & Sufficiency Standard (2026-08-17)

Workflow Pengajuan Barang yang wajib didukung:

`PENDING (Menunggu) → APPROVED / PARTIAL_APPROVED / REJECTED → Barang Keluar (Fulfillment) → COMPLETED`

1. **Approval (`POST /submissions/approve`)** — per item: Qty Disetujui (0 ≤ qty ≤ diminta), alasan wajib saat Qty=0, mapping master untuk item MANUAL. Keputusan dicatat: aktor, waktu, alasan, status before/after (audit_logs).
2. **Stock Sufficiency Guard (server-side)** — saat Admin menyetujui item bersumber STOCK, sistem menghitung stok aktual (SUM `stocks.quantity` per `master_item_id`) dan menolak approval bila `available < approved_qty` dengan pesan **“Stok Tidak Mencukupi”** + qty diminta/tersedia. Approval multi-item bersifat atomik (transaction): jika satu item tidak cukup, seluruh keputusan di-rollback (tidak ada partial state).
3. **Source of truth deduction = Barang Keluar (`OutgoingController::store`)** — approval TIDAK mengurangi stok. Barang Keluar mengecek ulang stok aktual di gudang (`FOR UPDATE`) tepat sebelum `StockService::mutateStock(-qty)` dan menolak bila tidak cukup; `mutateStock` tetap menjadi guard atomik terakhir (menolak saldo < 0 → No Negative Stock).
4. **No Double Deduction** — satu submission hanya dapat dipenuhi lewat `remaining_quantity` (guard `FOR UPDATE` di `outgoing_items`); approval berulang pada submission non-PENDING ditolak; submission menjadi `COMPLETED` setelah seluruh remaining terpenuhi.
5. **MANUAL source** — tidak menyentuh stok inventory (approval + fulfillment).
6. **Fulfillment shortcut** — detail pengajuan status APPROVED/PARTIAL_APPROVED menampilkan tombol **“Proses Barang Keluar”** → `/outgoing/create?submission_id=X` (item dimuat otomatis).
7. **Deduction identity** — selalu `master_item_id + warehouse_id` dari transaksi; ledger mencatat `quantity_delta`, `balance_before`, `balance_after`, `performed_by`, reference.

### 5.7.13 Global Status Badge & Mutation History Standard (2026-08-17)

**Status Badge (`UIHelper::statusBadge`)** — reusable: icon Font Awesome + label Indonesia + warna semantik (PENDING/MENUNGGU amber, APPROVED/COMPLETED/ACTIVE hijau, REJECTED/INACTIVE merah, PROCESSING/IN PROGRESS biru, PARTIAL ungu, DRAFT abu-abu). Diterapkan di daftar & detail Pengajuan (menggantikan plain text dan bug `$statusColor` yang undefined).

**Barang Masuk list** — kolom “Barang Masuk” menampilkan quantity langsung: single item = `Nama + Qty + Satuan`; multi-item = `N jenis barang · N item` + rincian per baris. Posted transaction tetap immutable.

**Mutation History (Inventory 360)** — kolom `Waktu | Jenis Mutasi | Referensi | Oleh | Stok Sebelum | Perubahan | Stok Sesudah`; Perubahan memakai tanda `+`/`-` dengan warna hijau/merah; referensi di-resolve ke transaction code (link bila route tersedia); invariant `Stok Sesudah = Stok Sebelum + Perubahan`; scoped ketat ke `master_item_id` yang dibuka.

### 5.7.14 Barang Keluar Stock Posting Rule (P0 fix 2026-08-17)

**Rule**: `Barang Keluar yang berhasil diproses wajib menghasilkan stock deduction sesuai quantity yang benar-benar dikeluarkan.` `Outgoing record sukses tanpa stock posting = data integrity failure.`

**Root cause (serialized)**: untuk item **serialized**, `OutgoingController::store()` hanya memanggil `moveSerial(ISSUED)` (ubah status serial) tetapi **tidak pernah memanggil `mutateStock`** — sehingga `stocks.quantity` hanya bertambah (incoming `+qty`, return `+1/unit`) dan tidak pernah berkurang. Gejala persis UAT: "Barang Keluar berhasil tetapi stok tetap 20". Bulk tidak terdampak (sudah `mutateStock(-qty)`).

**Fix**:
- Serialized outgoing sekarang memanggil `StockService::mutateStock(master_item_id, warehouse_id, -qty, 'OUTGOING', ...)` — simetris dengan incoming/return; ledger `-qty` dibuat untuk `master_item_id + warehouse_id` yang benar.
- **Stock sufficiency server-side** untuk serialized dilakukan **sebelum** serial dipindah ke ISSUED: `COUNT(*) serial_numbers WHERE master_item_id=? AND current_warehouse_id=? AND status='AVAILABLE' >= qty`, dengan `FOR UPDATE` per serial; jika tidak cukup → throw `Stok Tidak Mencukupi` + qty diminta/tersedia, rollback penuh, stok tidak berubah, tidak ada outgoing, tidak ada ledger.
- Guard integritas: outgoing **tidak boleh commit tanpa baris barang valid** (0 `outgoing_items` → rollback).
- **No double deduction**: deduction hanya terjadi di `OutgoingController::store()` (source of truth). Approval TIDAK mengurangi stok. Guard `remaining_quantity` per submission-item + `FOR UPDATE` menolak proses ulang ("Kuantitas melebihi sisa pengajuan").
- **Quantity source** = quantity outgoing aktual (approved/fulfilled qty yang benar-benar diproses), bukan request mentah.
- Multi-item: setiap item dikurangi independen sesuai `master_item_id` masing-masing dalam satu transaksi atomik; satu item gagal → rollback penuh.
- **Stock display**: untuk item serialized, stok fisik di halaman Stok & Inventory 360 dihitung otoritatif dari jumlah serial `AVAILABLE` per gudang (`master_item_id + warehouse_id`), agar konsisten dengan posting ledger.

**Verifikasi Freebuff (18/18 + 15/15 PASS)**: serialized outgoing 3→2 + ledger -1 + serial ISSUED; serialized insuff → ditolak, stok tetap, 0 outgoing; empty-outgoing guard; bulk regression 20→15→5 + ledger -15 + COMPLETED; bulk insuff 9 vs 15 → ditolak, stok tetap 9, 0 ledger, status tetap APPROVED, pesan `Stok Tidak Mencukupi`; double submit → satu deduction; multi-item → deduction per item.

### 5.7.15 Warehouse Fulfillment Workflow Standard (2026-08-18)

**Rule**: identity stock = `master_item_id + warehouse_id`. Gudang Pemenuhan adalah bagian dari data pengajuan — stok dicek, disetujui, dan dikurangi **pada gudang yang sama**.

Workflow: `Pengajuan STOCK (pilih Gudang Pemenuhan) → Approval (stok dicek per gudang) → Barang Keluar (gudang otomatis mengikuti pemenuhan) → Stock Deduction (master_item_id + warehouse_id yang sama)`.

1. **Form Teknisi (`submissions/create.php`)** — setiap baris Dari Stok wajib memilih **Gudang Pemenuhan** (`warehouse_ids[]`), wajib dan divalidasi server-side (hanya gudang aktif). Baris Manual tidak memakai gudang.
2. **Penyimpanan (`SubmissionController::store`)** — `warehouse_id` disimpan per item di `submission_items.warehouse_id` (kolom sudah ada di schema). Multi-item: setiap item menyimpan gudang masing-masing.
3. **Approval (`SubmissionController::approve`)** — stock check WAJIB scoped `SELECT quantity FROM stocks WHERE master_item_id = ? AND warehouse_id = ?` (gudang pemenuhan item). **DILARANG SUM lintas gudang.** Jika item STOCK disetujui tanpa `warehouse_id` (data lama) → ditolak dengan pesan jelas (fail-safe, bukan tebakan). Pesan insuff mencantumkan nama gudang + qty diminta/tersedia.
4. **Detail Admin (`submissions/show.php`)** — menampilkan per item: **Gudang Pemenuhan + Stok tersedia di gudang tersebut** + qty diminta + qty disetujui. Data lama (`warehouse_id NULL`) ditandai "Gudang Pemenuhan belum ditentukan (data pengajuan lama)".
5. **Barang Keluar (`OutgoingController::create` + `outgoing/create.php`)** — saat dibuka dari pengajuan (`?submission_id=X`), gudang **di-preselect otomatis** dari Gudang Pemenuhan pengajuan (jika semua item seragam); item menampilkan gudang masing-masing; API `/submissions/api/items` mengembalikan `warehouse_id` per item. Pengajuan lama tanpa gudang → hint "pilih gudang eksplisit".
6. **Warehouse mismatch guard (`OutgoingController::store`)** — untuk item dengan `warehouse_id` terisi: gudang header outgoing **wajib sama** dengan gudang pemenuhan item, jika berbeda → ditolak ("Gudang asal tidak sesuai Gudang Pemenuhan pengajuan"), rollback penuh. Item legacy (`warehouse_id NULL`): gudang ditentukan eksplisit oleh Admin saat proses (data NULL **tidak diisi paksa** ke DB).
7. **Fresh stock check saat proses** — stok dicek ulang pada gudang yang sama tepat sebelum deduction (`FOR UPDATE`); stok yang turun setelah approval → proses ditolak (`Stok Tidak Mencukupi`), tidak ada outgoing/ledger/deduction.
8. **Deduction identity** — `mutateStock(master_item_id, warehouse_id, -qty, ...)` dengan gudang dari header outgoing (= gudang pemenuhan); ledger mencatat warehouse yang sama.
9. **Data lama (`submission_items.warehouse_id IS NULL`)** — tidak di-isi otomatis (dilarang menebak). Pengajuan PENDING lama tidak bisa disetujui (fail-safe dengan pesan) — harus diajukan ulang. Pengajuan APPROVED lama tetap bisa dipenuhi dengan gudang eksplisit oleh Admin. **Recovery issue**: jika diinginkan migrasi massal, perlu keputusan bisnis (gudang mana) — tidak dilakukan otomatis.

**Verifikasi Freebuff (26/26 + legacy PASS, TANPA Playwright)**: gudang tersimpan per item; approval 15 dari gudang stok 0 → ditolak `Stok Tidak Mencukupi` (tidak pakai stok gudang lain); 20→15→5 di gudang 32 + ledger -15 (balance 20→5) + COMPLETED; mismatch gudang → ditolak, status tetap APPROVED; stok turun setelah approval → proses ditolak (stok tetap); double process → satu deduction; multi-item → deduction per item per gudang; legacy NULL → notice di detail + hint di form + tetap bisa dipenuhi dengan gudang eksplisit (20→17). `php -l` 6 file bersih; `node --check` JS view bersih; DB residue 0 (baseline: 3 user, 8 submission, 5 stocks, 3 ledger, 0 outgoing; stok 179=20, 177=15, 55=4).

---

# 6. CANONICAL SIDEBAR / MENU MAP

Menu berikut menjadi **business function map**. Nama route aktual adalah hasil audit implementation, bukan penentu requirement.

## 6.1 Overview

### Dashboard
Tujuan:
monitoring dan analytics.

Minimum:
- Total Barang;
- Stok Rendah;
- Incoming;
- Outgoing;
- Transfer;
- Request aktif;
- Peminjaman aktif;
- aktivitas terbaru.

Dashboard harus berkembang menjadi analytics, bukan hanya KPI:

- incoming trend;
- outgoing trend;
- peak activity;
- top requester;
- item movement;
- outstanding return;
- installed asset;
- damage/lost;
- anomaly.

Chart harus memakai data nyata.

## 6.2 Master Data

### Master Barang
Identitas item.

### Kategori Barang
Klasifikasi item.

### Satuan
Unit quantity.

### Gudang
Warehouse master.

### Lokasi
Location detail.

## 6.3 Inventory

### Stok Barang
Kondisi inventory saat ini.

### Nomor Seri
Serialized asset inventory.

### Pergerakan Stok
Movement ledger.

## 6.4 Operational Transactions

### Pengajuan Barang
Requester workflow.

### Barang Masuk
Receive inventory.

### Barang Keluar
Issue inventory/custody.

### Transfer Gudang
Move inventory antar location/warehouse.

## 6.5 Asset Lifecycle

### Peminjaman
Custody/loan.

### Pengembalian
Return dan inspection.

### Instalasi / Terpasang
Installed asset tracking.

### Damage
Damage report/lifecycle.

### Lost
Lost report/lifecycle.

**Catatan:** Damage dan Lost adalah business events yang wajib didukung. Apakah keduanya memiliki menu terpisah atau berada di dalam modul existing adalah keputusan UI/architecture yang dapat diputuskan kemudian. Jangan menghilangkan business capability hanya karena belum ada menu terpisah.

## 6.6 Stock Control

### Stock Opname
Mencatat physical count dan variance.

### Penyesuaian Stok / Adjustment
Mencatat approved correction terhadap inventory.

**Stock Opname ≠ Adjustment.**

Stock opname adalah proses pengukuran/perbandingan fisik.

Adjustment adalah perubahan inventory yang dilakukan setelah variance ditinjau dan disetujui sesuai aturan.

## 6.7 Reports & Trace

- Laporan Stok & Mutasi;
- Laporan Nomor Seri;
- Laporan Pengajuan;
- Laporan Barang Masuk;
- Laporan Barang Keluar;
- Laporan Transfer;
- Laporan Peminjaman;
- Laporan Pengembalian;
- Laporan Instalasi;
- Laporan Penyesuaian;
- Riwayat Timeline;
- Log Aktivitas/Audit.

## 6.8 System

- Pengguna;
- Pengaturan Sistem.

---

# 7. BUSINESS FLOW CANONICAL

## 7.1 Incoming

`Source/Vendor → Receive → Validate → Inventory Increase → Movement → Audit → Timeline`

Serialized:
serial harus dicatat/ditautkan ke item dan lokasi.

## 7.2 Request

Actor utama: Teknisi.

`Draft → Submit → Pending Approval → Approved/Rejected → Processing → Fulfilled/Outgoing → Assigned`

Reject harus menyimpan actor, timestamp, dan reason bila diwajibkan.

## 7.3 Outgoing

`Approved Request/Authorized Issue → Validate Availability → Issue → Inventory Decrease → Custody/Assignment → Movement → Audit → Timeline`

Tidak boleh mengurangi stock tanpa transaksi yang menjelaskannya.

## 7.4 Transfer

`Initiate → Validate Source → Dispatch → In Transit (jika model state menggunakan) → Receive at Destination → Movement → Audit → Timeline`

Transfer bukan sekadar mengubah location field.

## 7.5 Borrowing

`Available → Borrow/Assign → Custody → Return`

Tanggal pinjam, borrower, asset, dan expected return bila digunakan harus tercatat.

## 7.6 Return

`Custody → Return → Receive/Inspect → Condition Decision → Inventory/Next Lifecycle State → Audit → Timeline`

## 7.7 Installation

`Available/Assigned → Issue/Assignment → Installation → Installed State → Installed Location → Technician → Evidence → Timeline`

## 7.8 Damage

`Report → Evidence → Review → Damage State → Repair/Retire/Replace/Return to Stock sesuai keputusan`

## 7.9 Lost

`Report → Evidence → Last Known Custody/Location → Investigation → Resolution → Audit`

## 7.10 Stock Opname

`Count → Compare System vs Physical → Variance → Review → Approved Adjustment (jika diperlukan) → Audit`

## 7.11 Adjustment

Adjustment hanya dilakukan melalui transaksi yang dapat diaudit dan harus memiliki reason/reference.

---

# 8. INVENTORY INTEGRITY

## 8.1 Non-serialized

Secara konseptual:

`Ending = Beginning + Incoming - Outgoing ± Transfer/Adjustment`

Transfer harus diperlakukan sebagai perpindahan, bukan menjadi incoming/outgoing palsu jika model ledger memang mencatat source/destination secara langsung.

## 8.2 Serialized

Invariant:

- serial unik;
- satu serial tidak boleh berada di dua lokasi aktif yang bertentangan pada waktu yang sama;
- state lifecycle harus konsisten;
- movement harus tercatat;
- custody harus konsisten;
- assignment/install/return tidak boleh meninggalkan status ganda.

## 8.3 Atomicity

Operasi multi-step yang mengubah inventory harus transactional/atomic agar tidak meninggalkan partial state.

## 8.4 Inventory Lifecycle Rule & Delete Rule

**Rule resmi (2026-08-17):**

| Entity | Peran |
|---|---|
| **Master Barang** | Definisi barang (kode, nama, kategori, satuan, serialized/non). |
| **Barang Masuk** | Menambah stok melalui transaksi penerimaan. |
| **Barang Keluar** | Mengurangi stok melalui transaksi pengeluaran. |
| **Transfer Gudang** | Memindahkan stok antar gudang (bukan incoming/outgoing palsu). |
| **Penyesuaian** | Mengoreksi stok melalui workflow opname/adjustment yang sah. |
| **Stok Barang** | Monitoring kondisi stok terkini per barang & gudang — **bukan tempat menghapus Master Barang**. |
| **Inventory 360°** | Detail & traceability (ringkasan, serial, riwayat lifecycle). |

**Delete Rule:**

- **Belum pernah digunakan** (tanpa stok > 0, tanpa nomor seri, tanpa histori transaksi) → **Hapus Permanen diperbolehkan** (hanya dari Master Data → Master Barang, dengan modal konfirmasi BIGNET).
- **Sudah pernah digunakan** → **Delete permanen dilarang** (backend guard; error aman: "Barang tidak dapat dihapus karena sudah memiliki riwayat transaksi. Gunakan Nonaktifkan Barang.").
- **Sudah pernah digunakan** → gunakan **Nonaktifkan** (arsip): hanya boleh bila barang tidak lagi memiliki stok fisik atau serial aktif; riwayat & laporan tetap utuh; barang nonaktif tidak dapat dipakai transaksi baru (`is_active=1` filter existing pada incoming/transfer/submission).
- Nonaktifkan/Aktifkan via `MasterItemController@toggleActive`; Hapus via `MasterItemController@destroy` — keduanya ADMIN-only (SUPERADMIN read-only untuk Master Barang, sesuai permission existing).

**Prinsip penting:**

- **Menghapus Master Barang ≠ mengurangi Stok.**
- **Barang Keluar ≠ menghapus Master Barang.**
- **Transfer ≠ Barang Keluar biasa.**
- **Penyesuaian ≠ Delete.**
- Delete Master Barang TIDAK tersedia di: Stok Barang, Barang Masuk, Barang Keluar, Transfer, Peminjaman, Pengembalian, Penyesuaian.

---

# 9. DATABASE MODEL EXPECTATIONS

Current memory mencatat model/tabel berikut sebagai ada di implementation:

- users
- settings
- item_categories
- units
- warehouses
- locations
- master_items
- master_item_attachments
- stocks
- serial_numbers
- serial_movements
- stock_ledgers
- incoming
- incoming_items
- outgoing
- outgoing_items
- transfers
- transfer_items
- transfer_item_serials
- submissions
- submission_items
- loans
- loan_items
- loan_item_serials
- returns
- return_items
- installations
- installation_items
- stock_adjustments
- stock_adjustment_items
- audit_logs
- attachments
- schema_migrations

**Status:** keberadaan harus diverifikasi terhadap current source/database. Jangan menganggap daftar memory otomatis 100% aktual.

Database wajib menjaga:
- referential integrity;
- unique constraints;
- timestamps;
- required relations;
- transaction consistency;
- no orphan records.

---

# 10. BARCODE / QR / LABEL

Required capability:

- generate;
- identifier yang benar;
- lookup/scan;
- buka item/serial detail;
- buka timeline;
- authorization;
- label preview;
- logo;
- ownership/company information bila disepakati;
- print;
- batch print bila requirement aktif.

Barcode/QR bukan gambar dekoratif.

### Scan Barcode di Pencarian Barang (Admin & Superadmin)

Halaman **Pencarian Barang (`/search`)** mendukung scan barcode langsung untuk **Admin dan Superadmin**:

- Scanner USB/Bluetooth berperilaku seperti keyboard — mengisi input barcode lalu mengirim **ENTER** → sistem otomatis mencari **tanpa klik tombol Cari**.
- Sumber barcode: **exact match** pada `serial_numbers.serial_number` (SN) atau `master_items.kode_barang` (Kode Barang) — bukan pencarian LIKE/nama biasa.
- Input scan **auto-focus** saat halaman dibuka; setelah hasil tampil, fokus kembali ke input scan untuk scan berikutnya tanpa klik mouse.
- Barcode tidak ditemukan → pesan jelas **"Barcode tidak ditemukan."** (tanpa 500); barcode kosong → tidak ada request; tanpa polling/background request.
- Manual search (nama/kode/SN/transaksi/gudang/kategori/status/kondisi) tetap berfungsi penuh — mode scan adalah shortcut tambahan.
- Backend: parameter `barcode` di `GET /search` (`SearchController@index`, query parameterized); hasil dirender di tabel existing dengan tombol `Lihat` → Inventory 360. Authorization existing (SUPERADMIN/ADMIN; role lain 403).

---

# 11. EVIDENCE

Evidence harus dapat dipasang ke transaksi atau event yang relevan.

Contoh:
- incoming;
- outgoing;
- request;
- transfer;
- return;
- installation;
- damage;
- lost;
- stock opname;
- adjustment.

---

# 12. TIMELINE

Detail asset/serial harus dapat menunjukkan:

- identity;
- current location;
- current custodian;
- status;
- full event history;
- transaction references;
- evidence links.

Timeline hanya boleh memuat event yang benar-benar tercipta dari transaksi/event source.

---

# 13. AUDIT LOG

Untuk perubahan penting, simpan:

- actor;
- action;
- entity;
- entity id;
- timestamp;
- before/after bila relevan;
- reference.

Audit digunakan untuk accountability, bukan sekadar log debugging.

---

# 14. REPORTING

Report harus menjawab kebutuhan operasional dan audit.

Filter relevan:
- date;
- warehouse;
- location;
- item;
- category;
- serial;
- user;
- status.

---

# 15. GRAPHIFY PROTOCOL

Graphify digunakan untuk memahami:

- file relationships;
- module dependencies;
- route relationships;
- controller/service relationships;
- model/database relationships;
- architecture.

Graphify bukan source of truth requirement.

Current memory menyatakan tersedia di `graphify-out` dengan graph artifacts.

Sebelum perubahan arsitektur besar:
1. baca graph;
2. cross-check source;
3. cross-check database;
4. cross-check runtime.

---

# 16. PLAYWRIGHT PROTOCOL

Playwright adalah browser verification layer.

Workflow:

`open → snapshot → interact → snapshot/assert → report`

Wajib digunakan untuk membuktikan:
- login;
- sidebar;
- menu;
- form;
- buttons;
- role restrictions;
- business workflow;
- success/error;
- navigation.

HTTP 200 atau route existence bukan PASS.

Current browser baseline:
- login berhasil;
- dashboard berhasil dibuka;
- snapshot menunjukkan 0 console errors dan 1 warning;
- sidebar aktual terdeteksi.

Warning yang sudah terlihat adalah Tailwind CDN production warning; tidak boleh dipromosikan menjadi P0 business issue.

---

# 17. CURRENT IMPLEMENTATION MEMORY

Informasi dari Antigravity Memory yang harus diperlakukan sebagai **STATUS REPORT, bukan requirement**:

## Confirmed / Observed
- login/dashboard dapat dibuka;
- sidebar memiliki banyak modul inventory, operational, reports, system;
- database/schema memiliki banyak tabel inti;
- Graphify tersedia;
- Playwright tersedia.

## Partial / Needs Verification
- dashboard analytics masih KPI-oriented;
- reports/charts perlu verifikasi data nyata;
- inventory tracking perlu full test;
- server-side permission perlu audit penuh;
- atomic transaction perlu audit penuh;
- evidence detail perlu full verification;
- barcode/QR perlu full verification.

## Missing/Unclear dari Current Memory
- explicit Damage/Lost menu tidak terlihat pada sidebar snapshot;
- explicit Stock Opname menu tidak terlihat pada sidebar snapshot.

Business capability Damage/Lost dan Stock Opname tetap REQUIRED walaupun menu UI belum dipastikan.

---

# 18. ROUTE RULE

Jangan menanamkan route implementation sebagai business requirement.

Route yang terdeteksi di browser pada satu baseline adalah evidence implementation.

Contoh aktual yang pernah terlihat:
- `/dashboard`
- `/search`
- `/categories`
- `/units`
- `/warehouses`
- `/locations`
- `/items`
- `/stocks`
- `/serials`
- `/submissions`
- `/incoming`
- `/outgoing`
- `/transfers`
- `/loans`
- `/returns`
- `/installations`
- `/adjustments`
- `/reports/ledger`
- `/reports/serials`
- `/reports/submissions`
- `/reports/incoming`
- `/reports/outgoing`
- `/reports/transfers`
- `/reports/loans`
- `/reports/returns`
- `/reports/installations`
- `/reports/adjustments`
- `/timeline`
- `/reports/audit`
- `/users`
- `/settings`

Route tersebut harus diaudit terhadap current source; jangan menganggap path itu permanen.

---

# 19. FEATURE STATUS MODEL

Setiap feature wajib diberi salah satu status:

- `LOCKED`
- `IMPLEMENTED`
- `PARTIAL`
- `BROKEN`
- `MISSING`
- `PROPOSED`
- `UNCERTAIN`
- `OPEN DECISION`

**IMPLEMENTED hanya boleh diberikan setelah implementation + browser/backend verification memadai.**

---

# 20. CONFLICT CONTROL

Jika ditemukan dua aturan yang tampak bertabrakan, jangan diam-diam memilih.

Gunakan tabel:

| ID | Area | Rule A | Rule B | Conflict | Decision |
|---|---|---|---|---|---|
| C-001 | ... | ... | ... | ... | OPEN |

## Conflicts yang sudah terselesaikan secara konseptual

### C-001: Stock Opname vs Adjustment
Bukan konflik.

- Stock Opname = proses menghitung dan menemukan variance.
- Adjustment = transaksi koreksi setelah review/approval.

### C-002: Damage/Lost vs Adjustment
Bukan konflik.

- Damage/Lost = lifecycle/business event.
- Adjustment = inventory correction, bila memang dibutuhkan setelah keputusan.

### C-003: Transfer vs Incoming/Outgoing
Bukan konflik.

- Transfer = perpindahan antar lokasi/warehouse.
- Incoming/outgoing = receive/issue terhadap inventory/custody.
- Ledger harus mencegah double counting.

### C-004: Super Admin vs Business Actor
Bukan konflik.

Super Admin dapat memiliki technical authority tanpa otomatis menjadi requester/custodian dalam workflow bisnis.

---

# 21. OPEN DECISIONS

Hanya gunakan Open Decision untuk hal yang benar-benar belum ditentukan.

Saat ini:
1. Apakah Damage/Lost mendapat menu terpisah atau menjadi bagian dari lifecycle pada modul asset/adjustment?
2. Apakah Stock Opname mendapat menu utama sendiri atau dipetakan ke modul existing?
3. Detail permission per action untuk setiap role masih harus diverifikasi dari source + business decision.

Jangan menyelesaikan tiga hal tersebut dengan asumsi.

---

# 22. AI AGENT WORKING PROTOCOL

Untuk task besar:

1. READ THIS MASTER.
2. UNDERSTAND BUSINESS FLOW.
3. IDENTIFY ROLE.
4. INSPECT SOURCE.
5. INSPECT DATABASE.
6. INSPECT GRAPHIFY.
7. RUN APPLICATION.
8. USE PLAYWRIGHT.
9. CREATE GAP ANALYSIS.
10. PLAN.
11. IMPLEMENT.
12. VERIFY.
13. REPORT.

## Jangan langsung coding untuk scope yang menyentuh business logic.

Plan harus menyebut:
- requirement;
- current state;
- root cause;
- files/modules;
- DB impact;
- API impact;
- role impact;
- timeline impact;
- audit impact;
- test plan.

---

# 23. PROMPT OPERASIONAL UTAMA

Gunakan prompt berikut untuk melanjutkan build:

```text
Read MASTER_BIGNET_PROJECT_V2.md before changing anything.

Treat this document as the canonical business/product/system contract.

Do not reinterpret or invent business logic.

First inspect:
- source;
- database;
- Graphify;
- runtime;
- current browser state.

Then compare current implementation against this MASTER.

For the requested task:
1. State the business goal.
2. Identify actor/role.
3. Identify business flow.
4. Identify affected modules.
5. Identify database impact.
6. Identify frontend impact.
7. Identify backend impact.
8. Identify timeline/audit/evidence impact.
9. Identify current gap.
10. Create implementation plan.
11. Implement only the approved scope.
12. Verify with Playwright where applicable.
13. Verify backend/database consistency.
14. Report exact results.

Never declare a feature PASS based only on:
- route existence;
- HTTP 200;
- component rendering;
- dummy data.

If business logic is ambiguous:
STOP and create OPEN DECISION.
Do not guess.

Do not change LOCKED requirements without Product Owner decision.
```

---

# 24. CURRENT BUILD PRIORITY

## P0 Foundation
- authentication;
- authorization;
- roles;
- inventory integrity;
- stock ledger;
- serial lifecycle;
- core transactions;
- movement;
- timeline;
- audit.

## P1 Core Operations
- request;
- approval;
- incoming;
- outgoing;
- transfer;
- borrowing;
- return;
- installation;
- damage/lost;
- stock opname;
- adjustment;
- evidence.

## P1 Visibility
- reports;
- dashboard analytics;
- monitoring.

## P2 Convenience
- barcode/QR;
- label;
- batch print;
- advanced exports.

---

# 25. DEFINITION OF DONE

Feature = DONE hanya jika:

- requirement terpenuhi;
- role benar;
- permission benar;
- frontend benar;
- backend benar;
- database benar;
- business flow benar;
- inventory integrity terjaga;
- timeline tercatat;
- audit tercatat;
- evidence tersedia bila diwajibkan;
- browser test berhasil;
- error handling benar;
- tidak ada dead button;
- tidak ada broken route;
- tidak ada regression.

---

# 26. CURRENT GAP TRACKER

Gunakan format:

| ID | Area | Requirement | Current State | Gap | Priority | Status | Verification |
|---|---|---|---|---|---|---|---|
| GAP-001 | Dashboard | Analytics charts | KPI present | charts not fully verified | P1 | Open | Playwright |
| GAP-002 | Barcode | Generate/scan/label | not fully verified | full lifecycle unknown | P1/P2 | Open | Browser |
| GAP-003 | Timeline | Full asset history | partial/unknown | needs deep verification | P0/P1 | Open | Browser + DB |
| GAP-004 | Permissions | server-side role enforcement | uncertain | needs backend audit | P0 | Open | API + browser |
| GAP-005 | Transactions | atomic inventory update | uncertain | needs DB transaction audit | P0 | Open | backend/db |
| GAP-006 | Evidence | full upload/view/relations | partial/unknown | needs verification | P1 | Open | browser + DB |
| GAP-007 | Damage/Lost | business capability | not explicit in sidebar | UI mapping unresolved | P1 | Open Decision | PO |
| GAP-008 | Stock Opname | physical count flow | not explicit in sidebar | UI mapping unresolved | P1 | Open Decision | PO |

---

# 26.1 TEST ENVIRONMENT RESET RULE (TESTING ONLY)

**Test Environment Reset Rule** — utility `scripts/reset_test_data.php` menghapus seluruh data bisnis/test dan seluruh user selain 1 SUPERADMIN, tetapi mempertahankan schema (38 tabel, FK graph DAG) dan system configuration (`settings` 11 baris: app_name, branding, logo, favicon, theme, dsb) yang dibutuhkan aplikasi untuk startup/rendering.

**PERINGATAN: UTILITY INI HANYA UNTUK ENVIRONMENT TESTING. JANGAN DIJALANKAN PADA PRODUCTION.**

### Perilaku utility
- **Dry-run default**; eksekusi nyata hanya dengan flag `--execute` (transaksional: rollback jika ada error; tidak pernah meninggalkan DB setengah terhapus).
- **Dependency-aware**: topo-sort graph FK 38 tabel dari information_schema; child dihapus sebelum parent; `FOREIGN_KEY_CHECKS` tidak dimatikan (prioritas penghapusan berdasar dependency).
- **Superadmin dipertahankan**: identifikasi akun SUPERADMIN existing (id/email/role/is_active/account_status/password hash) sebelum reset; snapshot diverifikasi setelah reset; tidak membuat akun baru.
- **System configuration dipertahankan**: seluruh `settings` (11 baris) + file logo/favicon sistem. Hanya data bisnis/test yang dikosongkan.
- **Audit & activity**: `audit_logs` ikut dikosongkan karena merupakan data testing (struktur tetap ada).
- **File storage**: orphan test files dibersihkan hanya bila aman; logo utama/favicon/public assets tidak pernah dihapus.
- **Backup**: sebelum `--execute` disarankan backup via mysqldump (contoh tersimpan di `storage/backups/`).
- **Verifikasi built-in**: setelah reset mencetak ringkasan count (users=1, seluruh tabel bisnis=0, settings=11, schema_migrations=10).

### Kondisi akhir yang dijamin
`1 SUPERADMIN + 0 DATA BISNIS/TEST + SYSTEM SETTINGS TETAP ADA + SCHEMA TETAP UTUH + APLIKASI TETAP DAPAT LOGIN DAN DIGUNAKAN`

---

# 27. MASTER RULE

Ketika ada perbedaan antara requirement dan implementation:

**Jangan mengubah requirement untuk membuat source terlihat benar.**

Gunakan:

`REQUIREMENT → CURRENT STATE → GAP → DECISION → IMPLEMENT → VERIFY`

Bukan:

`CURRENT SOURCE → GUESS → CHANGE REQUIREMENT`

---

# 28. FINAL PRODUCT TEST

Pertanyaan terakhir untuk seluruh sistem:

> Apakah kita dapat mengetahui apa barangnya, di mana sekarang, siapa yang bertanggung jawab, bagaimana barang bergerak, kapan berpindah, transaksi apa yang menyebabkan perpindahan, dan apa bukti yang tersedia?

Jika tidak, produk belum selesai.

---

# 29. CATATAN UNTUK AI AGENT

Dokumen ini sengaja memisahkan:

- **TARGET** = requirement;
- **CURRENT** = kondisi implementasi;
- **VERIFIED** = telah dibuktikan;
- **BUG** = implementasi salah;
- **GAP** = belum lengkap;
- **OPEN DECISION** = membutuhkan keputusan Product Owner.

Jangan mengubah satu kategori menjadi kategori lain hanya agar laporan terlihat lebih baik.
