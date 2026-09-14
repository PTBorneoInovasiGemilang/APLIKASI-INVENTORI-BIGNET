# BIGNET — PRODUCTION ROLLBACK RUNBOOK

Dokumen ini menjelaskan alur kerja darurat (Emergency Procedure) untuk membatalkan deployment (Rollback) apabila terjadi kegagalan fatal pada server produksi.

---

## 1. Trigger Pembatalan (Rollback Triggers)

Prosedur Rollback **WAJIB SEGERA DIEKSEKUSI** apabila ditemukan salah satu kondisi kritikal berikut setelah deployment:

1. **HTTP 500 Internal Server Error** secara masif pada halaman utama/login.
2. **Broken Authentication / Authorization**: Pengguna tidak bisa login atau otorisasi role bocor.
3. **Stock Data Corruption**: Transaksi mutasi stok menghasilkan angka ganda atau minus ilegal.
4. **Database Fatal Exceptions**: Kegagalan koneksi DB atau query crash berulang.
5. **Critical Security Breach**: Kebocoran file private storage atau error stack trace publik.

---

## 2. Prosedur Code Rollback

1. **Rollback Git Repository**:
   ```bash
   git checkout main
   git reset --hard PREVIOUS_STABLE_COMMIT_SHA
   ```
2. **Reload Web Server & PHP-FPM**:
   ```bash
   systemctl reload php8.2-fpm
   systemctl reload nginx
   ```

---

## 3. Prosedur Database Rollback

1. **Drop & Recreate Production Database**:
   ```sql
   DROP DATABASE bignet_inventory_prod;
   CREATE DATABASE bignet_inventory_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
2. **Import Snapshot Pre-deployment**:
   ```bash
   gunzip -c /var/backups/bignet/bignet_PRE_DEPLOY_2026-09-14.sql.gz | mysql -u root -p bignet_inventory_prod
   ```
3. **Verifikasi Ulang Koneksi Aplikasi**:
   - Jalankan smoke test ringan pada endpoint `/login`.
