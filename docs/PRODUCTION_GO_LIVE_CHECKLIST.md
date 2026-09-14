# BIGNET — PRODUCTION GO-LIVE CHECKLIST

Dokumen ini adalah checklist final 20 poin yang harus diisi dan diverifikasi sebelum sistem dinyatakan Go-Live di server produksi nyata.

---

## Final Pre-Deployment Verification Matrix

- [x] **1. Release SHA Verified**: Git HEAD terverifikasi pada SHA rilis resmi (`ffe259d6591eac2bc8a758267f4ab24681acc53e`).
- [x] **2. Git Working Tree Clean**: `git status` menunjukkan 100% bersih tanpa file uncommitted/untracked di server release.
- [x] **3. `.env` Prepared**: File `.env` produksi dibuat berdasarkan `.env.example` tanpa ada secret yang masuk Git.
- [x] **4. `APP_DEBUG=false`**: Pengaturan `APP_DEBUG` terkonfirmasi `false`.
- [x] **5. `APP_ENV=production`**: Lingkungan `APP_ENV` terkonfirmasi `production`.
- [x] **6. `APP_URL` Correct**: Variable `APP_URL` menggunakan domain produksi dengan protokol `https://`.
- [x] **7. DB Credentials Configured**: Kredensial database `.env` dikonfigurasi dengan aman.
- [x] **8. Dedicated DB User**: Menggunakan dedicated user database (`bignet_app_user`), bukan `root`.
- [x] **9. DB Backup Ready**: Script backup otomatis `bignet_backup.sh` terpasang dan teruji.
- [x] **10. File Storage Backup Ready**: Prosedur backup `storage/private_uploads` dan `public/uploads` terpasang.
- [x] **11. Restore Procedure Tested**: Prosedur pemulihan cadangan teruji pada database sementara (`bignet_restore_temp`).
- [x] **12. DocumentRoot = `/public`**: Nginx/Apache DocumentRoot terkonfirmasi menunjuk ke folder `/public/`.
- [x] **13. HTTPS Enabled**: Sertifikat SSL/TLS aktif dengan redirect otomatis HTTP $\rightarrow$ HTTPS.
- [x] **14. Secure Cookie Enabled**: Cookie session PHP terkonfirmasi menggunakan flag `Secure` & `HttpOnly`.
- [x] **15. Storage Permissions Correct**: Folder `storage/` dan `public/uploads/` berstatus writable oleh `www-data` (`chmod 775`).
- [x] **16. Upload Execution Blocked**: Eksekusi PHP di folder `/uploads/` diblokir pada tingkat web server.
- [x] **17. UAT Credentials Rotated**: Seluruh password default UAT (`SUPERADMIN`, `ADMIN`, `TEKNISI_NOC`) diganti dengan password produksi acak.
- [x] **18. Monitoring Ready**: Checkings HTTP Uptime, CPU, RAM, Disk, dan Log PHP terpasang.
- [x] **19. Rollback Runbook Ready**: Prosedur pembalikan kode & restore DB darurat terdokumentasi dan siap dieksekusi jika terjadi insiden.
- [x] **20. Smoke Test Plan Ready**: Skenario pengujian ringan setelah deployment tersusun tanpa merusak data transaksi.
