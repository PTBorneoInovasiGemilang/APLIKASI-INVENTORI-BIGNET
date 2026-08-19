# BIGNET — AI AGENT WORKING RULES

## ACTIVE PRODUCT/SYSTEM MASTER
**`MASTER_BIGNET_PROJECT_V2.md`**

File ini adalah satu-satunya MASTER ACTIVE.

## REFERENCE / ARCHIVE ONLY
- `MASTER_PROGRAM_PRD_INDUK_BIGNET.md`
- `BIGNET_ANTIGRAVITY_PROJECT_MEMORY.md`

Kedua file tersebut hanya referensi historis/background.
Jangan memperlakukan keduanya sebagai requirement aktif jika bertentangan dengan `MASTER_BIGNET_PROJECT_V2.md`.

## PROJECT MEMORY
- `docs/PROJECT_STATE.md`
- `docs/BUSINESS_FLOW.md`
- `docs/MENU_ROLE_MATRIX.md`
- `docs/DATABASE_BLUEPRINT.md`
- `docs/DECISIONS.md`
- `docs/OPEN_DECISIONS.md`
- `docs/CHANGELOG.md`
- `docs/BUG_TRACKER.md`
- `docs/UAT_STATUS.md`

Gunakan file-file tersebut sebagai living project memory.

## GRAPHIFY
`graphify-out` = architecture/relationship evidence.

Graphify bukan sumber requirement.
Gunakan **Graphify incremental**: Jalankan `graphify update .` hanya ketika architecture/dependency berubah. Jangan menjalankan re-extract atau re-cluster full tanpa alasan.

## PLAYWRIGHT AUTO-VERIFICATION
Playwright = browser behavior evidence.

HTTP 200 / route exists / page renders bukan bukti feature PASS.

Jika task memengaruhi UI, route, form, search, filter, pagination, button, role, permission, atau business workflow:
1. Gunakan Playwright CLI secara otomatis (Auto Playwright).
2. Verifikasi scope yang terdampak.
3. Periksa console errors.
4. Jangan menyatakan PASS sebelum verification berhasil.
5. Jangan meminta Product Owner menjalankan Playwright secara manual jika Agent bisa melakukannya.

## MANDATORY DOCUMENTATION RULE
Setiap task yang mengubah source/database/UI/business flow WAJIB:

1. Read `MASTER_BIGNET_PROJECT_V2.md`
2. Read `docs/PROJECT_STATE.md`
3. Read `docs/DECISIONS.md`
4. Read `docs/OPEN_DECISIONS.md`
5. Inspect source/database/Graphify
6. Work
7. Verify
8. Update `docs/PROJECT_STATE.md`
9. Append `docs/CHANGELOG.md`
10. Update `docs/BUG_TRACKER.md` if needed
11. Update `docs/UAT_STATUS.md` if verification was performed
12. Update `docs/OPEN_DECISIONS.md` if a new decision is required

Jangan pernah menyelesaikan coding tanpa documentation checkpoint.

## DEVELOPMENT WORKFLOW (TOKEN-EFFICIENT INCREMENTAL)
Kerjakan sistem secara inkremental dan token-efficient:
1. IMPLEMENT scope kecil.
2. PLAYWRIGHT AUTO-VERIFY scope tersebut.
3. DOCUMENT hasilnya.
4. CHECKPOINT dan minta arahan selanjutnya.
Jangan mencoba merombak puluhan view/file sekaligus dalam satu tembakan tanpa verifikasi.

## UI & UX STANDARDS
Setiap UI baru atau refactor harus mematuhi standar berikut (merujuk `MASTER_BIGNET_PROJECT_V2.md`):
- **Global UI Standard**: Tampilan harus bersih, menggunakan Tailwind CSS, responsif.
- **Table/List Standard**: Setiap tabel/list **WAJIB** memiliki komponen: Row numbering, Search, Filter, Reset, Pagination, Empty State, Loading state. Gunakan Shared Components.
- **Dashboard Standard**: Harus menampilkan real-world monitoring/analytics (stok, pergerakan). Jangan gunakan static placeholder yang tidak mencerminkan data asli.
- **Page Guide**: Setiap menu relevan harus memiliki bantuan kontekstual/informasi halaman statis yang konsisten.
- **Dark Mode**: Dukungan penuh untuk mode gelap (Tailwind `dark:` class).
- **Role-Based UI**: Warna atau layout menyesuaikan Role (mis. Teknisi NOC vs Admin Gudang) untuk visibilitas dan batasan akses.

## GLOBAL INTERNAL CODE VISIBILITY RULE
- Kode internal/generated seperti kode barang, gudang, kategori, satuan, pengajuan, transfer, lokasi, dan identifier database **tidak boleh tampil di UI user-facing**.
- Jangan menampilkan field kode saat tambah/ubah data bila kode dibuat otomatis oleh sistem.
- Jangan menampilkan kolom kode pada tabel/list, search placeholder, page guide, confirmation text, heading, detail, atau filter yang ditujukan untuk pengguna umum.
- Kode tetap digunakan di backend, database, URL, audit log, relasi, import/export, dan proses internal.
- **Nomor seri fisik** tetap boleh ditampilkan karena merupakan identitas unit fisik, bukan sekadar kode internal.
- Barcode/QR untuk operasi scanning tetap boleh digunakan; teks kode internal tidak boleh ditampilkan sebagai informasi biasa kepada pengguna.
