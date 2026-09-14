# BIGNET — PRODUCTION DATA CLEANSING & ISOLATION CHECKLIST

Dokumen ini berisi panduan untuk memastikan bahwa seluruh data uji coba (UAT / Dummy Data) tidak ikut masuk ke dalam database produksi nyata saat rilis awal (Initial Clean Deployment).

---

## 1. Identifikasi Data UAT / Dummy yang Harus Diisolasi

Sebelum sistem diserahkan ke pengguna produksi, pastikan database produksi bersih dari item berikut:

- **Dummy Users**: User uji coba seperti `admin@ptbig.com`, `teknisi@ptbig.com`, `superadmin@ptbig.com` (apabila menggunakan password default UAT `admin123`/`teknisi123`).
- **Test Categories & Units**: Kategori atau satuan dengan nama uji coba (misalnya `Kategori Test E2E`).
- **Test Items & Serials**: Barang master dan nomor seri buatan pengujian (`SN-TEST-001`, `BARANG-DUMMY`).
- **Test Transactions**: Transaksi pengajuan, barang masuk, barang keluar, peminjaman, instalasi, insiden, transfer, opname, dan adjustment uji coba.
- **Test Files**: Evidence foto atau dokumen lampiran di `storage/private_uploads/` dan `public/uploads/` yang dibuat selama UAT.

---

## 2. Prosedur Inisialisasi Database Produksi Bersih (Clean Baseline)

Untuk deployment awal produksi yang bersih:

1. Import schema murni tanpa data uji coba, ATAU
2. Buat akun **SUPERADMIN Produksi Asli** dengan kredensial baru yang kuat.
3. Hapus seluruh data transaksi uji coba dan file bukti dummy.
4. Input Master Data resmi perusahaan (Gudang resmi, Kategori resmi, Satuan resmi, Lokasi resmi).
