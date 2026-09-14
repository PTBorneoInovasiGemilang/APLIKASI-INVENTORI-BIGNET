# BIGNET — PRODUCTION DEPLOYMENT SEQUENCE RUNBOOK

Dokumen ini menjelaskan urutan langkah wajib (Sequential Checklist) yang harus dieksekusi secara ketat oleh DevOps / Release Engineer saat melakukan deployment rilis BIGNET ke lingkungan produksi.

---

## 1. Urutan Langkah Deployment (Sequential Flow)

```text
 [1. Backup DB]  ──>  [2. Backup Uploads] ──> [3. Verify SHA]
        │
        ▼
 [4. Server Prep] ──> [5. Setup .env]    ──> [6. DB Config]
        │
        ▼
 [7. Permissions] ──> [8. DocumentRoot]  ──> [9. HTTPS Config]
        │
        ▼
 [10. Deploy Code] ──> [11. Reload FPM]  ──> [12. Smoke Test]
        │
        ▼
 [13. Monitor]    ──> [14. Go-Live Announcement]
```

---

## 2. Rincian Eksekusi Setiap Langkah

1. **Backup Database Produksi**:
   - Jalankan `bignet_backup.sh` untuk membuat snapshot database pre-deployment (`bignet_PRE_DEPLOY_YYYY-MM-DD.sql.gz`).
2. **Backup File Storage Uploads**:
   - Ambil arsip folder `storage/private_uploads` dan `public/uploads`.
3. **Verifikasi Release SHA**:
   - Pastikan Git HEAD pada server penarik kode match persis dengan Release SHA terkonfirmasi: `git rev-parse HEAD`.
4. **Server Environment Preparation**:
   - Verifikasi PHP versi $\ge 8.1$ dan seluruh ekstensi PHP terpasang.
5. **Prepare Production `.env`**:
   - Salin `.env.example` ke `.env`. Isi kredensial produksi nyata, `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://...`.
6. **Configure Dedicated Database User**:
   - Pastikan database `bignet_inventory_prod` dan user `bignet_app_user` sudah dibuat dengan hak akses terbatas.
7. **Configure File & Directory Permissions**:
   - Source code read-only, folder `storage/` dan `public/uploads/` read-write oleh user `www-data`.
8. **Configure DocumentRoot**:
   - Arahkan Nginx/Apache DocumentRoot ke `/var/www/bignet/public`.
9. **Configure HTTPS & SSL**:
   - Verifikasi sertifikat Let's Encrypt / SSL aktif dan redirect HTTP $\rightarrow$ HTTPS berjalan.
10. **Deploy Release Codebase**:
    - Tarik kode dari repository `origin/main` commit SHA rilis resmi.
11. **Restart / Reload Services**:
    - Systemctl reload `php8.2-fpm` & `nginx` / `apache2`.
12. **Smoke Test Verification**:
    - Lakukan verifikasi login publik, dashboard Superadmin, Admin, dan Teknisi tanpa mengubah data transaksi nyata.
13. **Monitoring Window**:
    - Pantau error log `/var/log/php/bignet_errors.log` selama minimal 30 menit.
14. **Announce GO-LIVE**:
    - Kirim notifikasi keberhasilan deployment rilis produksi kepada tim manajemen & stakeholder.
