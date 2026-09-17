# BIGNET ANTIGRAVITY PROJECT MEMORY

## CONFIDENCE LEGEND
- **CONFIRMED**: Info verified via PRD and current codebase.
- **IMPLEMENTED**: verified working or present in the current source.
- **PARTIAL**: Partly implemented, some missing features.
- **BUG**: Known issue.
- **MISSING**: Documented requirement but not yet implemented.
- **PROPOSED**: Suggested but not confirmed by PO.
- **UNCERTAIN**: Need more details.
- **OPEN DECISION**: Waiting for PO decision.
- **CONFLICTING REQUIREMENTS**: Different instructions given.

---

### 1. PROJECT OVERVIEW
**Tujuan Aplikasi:** Sistem kendali inventory dan accountability untuk operasional ISP yang bukan sekadar CRUD gudang, melainkan harus menjawab barang apa, di mana, siapa penanggung jawab, pergerakan, dan buktinya.  
**Masalah Bisnis:** Barang gudang sering hilang, barang di teknisi sulit dilacak, selisih stok sulit ditelusuri, jejak tanggung jawab tidak kuat, riwayat perpindahan tidak terlacak, kurangnya evidence/foto.  
**Pengguna Sistem:** Super Admin, Admin/Warehouse Admin, Teknisi/Field User.  
**Fungsi Utama:** Master data, manajemen inventory & asset, operasional gudang (masuk, keluar, transfer, peminjaman, pengembalian, instalasi), reporting, monitoring, accountability & audit trail.

### 2. BUSINESS LOGIC
- **Traceability (Inti):** Setiap pergerakan barang (terutama nomor seri) harus bisa ditelusuri (timeline, lokasi, quantity, actor).
- **Accountability:** Setiap transaksi harus ada actor (created_by, approved_by, dsb) dan timestamp.
- **Evidence:** Perlu ada bukti (foto/dokumen) untuk transaksi operasional.
- **Timeline:** Sejarah barang secara runut dari incoming -> stock -> transfer -> request -> approval -> outgoing -> return/damage/lost.
- **Inventory/Stock Invariants:** Stok Akhir = Stok Awal + Incoming - Outgoing ± Transfer/Adjustment. Serial tidak boleh di dua tempat, perpindahan harus ada event log.
- **Barcode/QR:** Pencarian, traceability, batch print, tidak boleh sekadar gambar dummy, scan harus bisa membuka detail/timeline asset.
- **Pengajuan (Request):** Teknisi -> Draft -> Submit -> Approval -> Processing -> Outgoing.
- **Approval:** Harus memiliki actor, timestamp, dan catatan keputusan.
- **Incoming:** Vendor/reference, item, qty, serial, destination, actor, evidence. Menambah stok.
- **Outgoing:** Requester, qty, serial, source, destination, reference, evidence. Mengurangi stok.
- **Transfer:** Pemindahan barang antar gudang/lokasi dengan dispatch dan receive.
- **Borrowing (Peminjaman):** Item/Serial yang dipinjamkan ke teknisi, waktu peminjaman, dan evidence.
- **Return (Pengembalian):** Pengembalian dari teknisi ke gudang beserta kondisi dan evidence.
- **Installation:** Asset terpasang, detail lokasi instalasi, teknisi, timeline.
- **Damage/Lost:** Pencatatan barang hilang/rusak dengan audit dan investigasi lanjutan.
- **Stock Opname:** Perbandingan stok sistem vs fisik beserta adjustment dan variance.
- **Reporting/Monitoring/Audit:** Laporan dari setiap modul, audit action log untuk perubahan, dashboard analytics.

### 3. ROLES
**Super Admin:** 
- Tujuan: Mengelola sistem dan administrasi global.
- Menu/Aktivitas: Pengguna, Pengaturan Sistem, Monitoring, Audit. Tidak semua operasional wajib.
**Admin / Warehouse Admin:**
- Tujuan: Operasional inventory.
- Menu/Aktivitas: Master Barang, Incoming, Outgoing, Transfer, Request Approval, Stock Opname, Laporan.
**Teknisi / Field User:**
- Tujuan: Requester barang lapangan.
- Menu/Aktivitas: Mengajukan request, melihat tanggung jawab asset, menggunakan barang, lapor pengembalian/kerusakan/kehilangan, upload evidence, lihat timeline asset.

### 4. MENU & SIDEBAR [CONFIRMED from Playwright / PRD]
- **Dasbor (/dashboard):** KPI, Aktivitas terbaru.
- **Master Data:** Master Barang (/master-data/items), Kategori (/master-data/categories), Satuan (/master-data/units), Gudang (/master-data/warehouses), Lokasi (/master-data/locations).
- **Inventory:** Stok (/inventory/stocks), Nomor Seri (/inventory/serials), Pergerakan Stok (/inventory/movements).
- **Operasional:** Pengajuan (/operational/submissions), Barang Masuk (/operational/incoming), Barang Keluar (/operational/outgoing), Transfer Gudang (/operational/transfers).
- **Peminjaman & Pemasangan:** Peminjaman Asset (/assets/loans), Pengembalian Asset (/assets/returns), Terpasang (/assets/installations).
- **Laporan & Jejak:** Laporan Stok, Laporan Nomor Seri, Laporan Pengajuan, Laporan Masuk, Laporan Keluar, Laporan Transfer, Laporan Peminjaman, Laporan Pengembalian, Laporan Instalasi, Laporan Penyesuaian, Riwayat Timeline (/timeline), Log Aktivitas (/reports/audit).
- **Sistem:** Pengguna (/users), Pengaturan Sistem (/settings).

### 5. FEATURES
- **IMPLEMENTED (Partial or Full based on UI/Log):** Dashboard Analytics (KPI counters), Log Aktivitas (Audit log functional - visible in UI), Menu Routing, Master Data (Kategori), Pengguna (Approve/Reject User), Transfer Gudang.
- **REQUESTED / MISSING (Must check in depth):** Timeline Viewer Detail, Full Evidence Upload, Batch Label/Barcode Print, Damage/Lost Module (Not in Sidebar), Stock Opname Module (Not explicitly in sidebar apart from 'Laporan Penyesuaian').

### 6. FRONTEND
- **Layout & Sidebar:** AdminLTE / Bootstrap-like layout based on class names from UI tests. Sidebar dengan pengelompokan menu (Master, Inventory, Operasional, Peminjaman, Laporan, Sistem). Dark mode toggle tersedia.
- **Dashboard:** Menampilkan widget ringkasan Total Barang, Stok Rendah, Masuk Hari Ini, Keluar Hari Ini, Transfer, Pengajuan, Peminjaman, dan list Aktivitas Terbaru.
- **Standard UI yang diminta:** Table dengan search, filter, pagination. Form dengan label, validation, feedback, button nyata, dan tidak ada dead menu. Loading state dan error handling.

### 7. BACKEND
- **Framework:** PHP (Native/MVC based on the environment `php -S localhost:8000`). Database: MySQL/MariaDB.
- **Business Logic Rules:** 
  - Tidak boleh ada data yatim.
  - Role check dilakukan secara server-side.
  - Perubahan inventory harus atomic dengan transaction.
  - Audit logs wajib diisi.

### 8. DATABASE
Tabel yang ada [IMPLEMENTED]:
- `users`, `settings`
- `item_categories`, `units`, `warehouses`, `locations`
- `master_items`, `master_item_attachments`
- `stocks`, `serial_numbers`, `serial_movements`, `stock_ledgers`
- `incoming`, `incoming_items`
- `outgoing`, `outgoing_items`
- `transfers`, `transfer_items`, `transfer_item_serials`
- `submissions`, `submission_items`
- `loans`, `loan_items`, `loan_item_serials`
- `returns`, `return_items`
- `installations`, `installation_items`
- `stock_adjustments`, `stock_adjustment_items`
- `audit_logs`, `attachments`, `schema_migrations`
Status: **IMPLEMENTED**. Lengkap untuk mendukung requirement bisnis utama.

### 9. INVENTORY / ACCOUNTABILITY
- Semua perpindahan stok dicatat di `stock_ledgers` / `serial_movements`.
- Custody dan Location harus di-update dalam operasional. Traceability diwajibkan melalui modul timeline. 

### 10. BARCODE / QR
- **REQUESTED:** Harus bisa di-generate, scan (lookup), print/batch print, berlogo, info jelas. Bukan hanya gambar dummy.

### 11. DASHBOARD / ANALYTICS
- **IMPLEMENTED (Partial):** Widget angka KPI sudah muncul di frontend.
- **REQUESTED:** Chart, activity trends, analytics siapa requester teratas, pergerakan terbanyak, dll.

### 12. UI / DESIGN STANDARDS
- **REQUESTED:** Konsisten, responsif, table filterable, form validasi lengkap, indikator status, permission role-aware, tidak menutupi error.

### 13. SECURITY / PERMISSION
- **REQUESTED & PARTIAL IMPLEMENTED:** Role separation antara Superadmin, Admin Gudang, Teknisi. Approval user diterapkan (User approval events terlihat di dashboard). Endpoint backend harus terlindungi.

### 14. GRAPHIFY
- **CONFIRMED:** Graphify tersedia di folder `graphify-out` dengan file `graph.json` & `manifest.json`.
- **Purpose:** Digunakan untuk memahami architecture relationship. Graphify BUKAN requirement/source of truth. Jika beda, ikuti Source/DB/PRD.

### 15. PLAYWRIGHT
- **CONFIRMED:** Framework testing yang digunakan. Snapshot telah menghasilkan output UI Dashboard & Sidebar menu. Semua klik dan navigasi harus verified menggunakan Playwright atau pengujian browser.

### 16. IMPLEMENTATION HISTORY
- **DATE UNKNOWN / RECENT:** Implementasi table setup, schema setup (terlihat dari `db_gudang_tommy.sql` dan `seed.php`).
- **DATE 15 Aug 2026:** Create & approve user (Super Admin - Pengguna).
- **DATE 16 Aug 2026:** Create categories, create/receive transfer (Admin Gudang).

### 17. KNOWN BUGS
- **UNCERTAIN:** Belum ada list bug spesifik tanpa inspeksi lebih lanjut di repository dan source code, namun PRD menegaskan setiap bug dicatat dalam format khusus.

### 18. KNOWN GAPS
- **UNCERTAIN:** Report dan Analitik Chart mungkin belum fully connected dengan data riil (PRD mewajibkan chart harus asli, bukan dummy).
- **MISSING:** Menu Damage & Lost (tidak ada di snapshot sidebar), Stock Opname (hanya ada Penyesuaian, mungkin ini penggantinya atau terpisah).

### 19. OPEN DECISIONS
- `OPEN DECISION`: Apakah Damage/Lost menggunakan menu Penyesuaian (Adjustment) atau dibuatkan modul terpisah? Menu khusus Damage/Lost tidak terlihat di Playwright Sidebar Snapshot.

### 20. CONFLICTS
- Tidak ada yang terdeteksi secara langsung saat ini antara instruksi dan PRD.

### 21. IMPORTANT PROJECT RULES
- 1. Baca PRD Induk sebelum mengubah apa pun.
- 2. Graphify hanya pendukung, prioritas: PRD > PO Decision > Implementasi (DB/Source/Graphify).
- 3. Jangan mengarang bisnis logic, JANGAN MENEBAK. Jika ambigu, tandai `OPEN DECISION`.
- 4. Fitur selesai HANYA JIKA bisnis gol tercapai, audit/timeline tercatat, role benar, dan bebas dummy.

### 22. CURRENT SYSTEM STATE
- **What works:** Login/Dashboard UI, Menu Navigation, User Management (User Approval log), Categories (log), Transfers (log).
- **What partially works:** Inventory Tracking (schema present, UI present, logic needs full test).
- **What is missing:** Damage/Lost explicit module, Analytics Chart/Trends, Batch Print Barcode.
- **What is uncertain:** Validasi Role di sisi Server-Side (backend PHP murni/MVC) dan konsistensi transaksi (atomic).

---

# FINAL MEMORY SUMMARY

### WHAT THE SYSTEM IS
Sistem kendali inventaris, pergerakan, dan akuntabilitas aset untuk operasional ISP dengan jejak telusur penuh (traceability), pencatatan kronologis (timeline), otorisasi (accountability), dan bukti (evidence).

### WHAT THE SYSTEM MUST DO
Sistem harus selalu bisa menjawab di mana barang, siapa yang megang, dari mana, untuk apa, kapan pindah, beserta audit log-nya.

### WHAT IS CURRENTLY IMPLEMENTED
Skema database untuk seluruh proses, sidebar menu yang lengkap sesuai PRD (Dashboard, Master, Operational, Asset, Reports, System), layout UI dashboard dengan widget KPI dan log aktivitas, integrasi Graphify, Testing setup.

### WHAT IS NOT FINISHED
Pemenuhan standar "Definition of Done" untuk semua menu (Validasi, Error handling, Barcode Print, Detail Timeline, Full Charts). Damage/Lost module tidak terlihat eksplisit di sidebar.

### WHAT MUST NOT BE CHANGED WITHOUT PRODUCT OWNER DECISION
Role Separation (Super Admin, Admin, Teknisi), Inventory Invariants (Stock ledger logic), Business Requirements di MASTER PRD, Aturan Evidence & Timeline.

### WHAT NEEDS VERIFICATION
- Backend server-side validation & atomic transaction implementation.
- Apakah menu 'Laporan Penyesuaian' mencakup 'Damage' dan 'Lost' item atau butuh modul baru.
- Fungsi scan dan generate Barcode.
