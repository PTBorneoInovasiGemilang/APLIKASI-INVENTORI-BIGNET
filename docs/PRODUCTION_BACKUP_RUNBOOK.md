# BIGNET — PRODUCTION BACKUP & RESTORE RUNBOOK

Dokumen ini menjelaskan prosedur baku untuk pembuatan dan pengujian pemulihan (restore) cadangan Database dan File Storage pada server produksi BIGNET.

---

## 1. Strategi Backup Database

- **Pre-deployment Backup**: **WAJIB** dieksekusi tepat sebelum perilisan kode baru atau perubahan schema.
- **Daily Automated Backup**: Berjalan otomatis setiap hari pukul 02:00 WIB via Cron Job.
- **Retention Policy**: Cadangan disimpan selama **30 Hari**. File yang lebih tua dari 30 hari dihapus otomatis.
- **Format Penamaan**: `bignet_YYYY-MM-DD_HH-mm.sql.gz`

---

## 2. Cron Job Automated Backup Script Example

Buat script `/usr/local/bin/bignet_backup.sh`:

```bash
#!/bin/bash
set -e

BACKUP_DIR="/var/backups/bignet"
TIMESTAMP=$(date +"%Y-%m-%d_%H-%M")
DB_NAME="bignet_inventory_prod"
DB_USER="bignet_app_user"
DB_PASS="YOUR_STRONG_PRODUCTION_PASSWORD"
BACKUP_FILE="${BACKUP_DIR}/bignet_${TIMESTAMP}.sql.gz"

mkdir -p ${BACKUP_DIR}

# 1. Database Dump dengan mysqldump + gzip
mysqldump --single-transaction --quick --lock-tables=false \
  -u ${DB_USER} -p"${DB_PASS}" ${DB_NAME} | gzip -9 > ${BACKUP_FILE}

chmod 600 ${BACKUP_FILE}

# 2. File Storage Dump (Bukti / Uploads)
STORAGE_FILE="${BACKUP_DIR}/storage_${TIMESTAMP}.tar.gz"
tar -czf ${STORAGE_FILE} -C /var/www/bignet storage/private_uploads public/uploads

chmod 600 ${STORAGE_FILE}

# 3. Purge Backup Lebih Tua Dari 30 Hari
find ${BACKUP_DIR} -type f -name "bignet_*.sql.gz" -mtime +30 -delete
find ${BACKUP_DIR} -type f -name "storage_*.tar.gz" -mtime +30 -delete

echo "[$(date)] Backup completed successfully: ${BACKUP_FILE}" >> /var/log/bignet_backup.log
```

Tambahkan ke cron (`crontab -e`):
```cron
0 2 * * * /usr/local/bin/bignet_backup.sh >/dev/null 2>&1
```

---

## 3. Database Restore Verification Procedure

> **PERATURAN KRUSIAL**: Pengujian restore **TIDAK BOLEH** dilakukan secara langsung pada Database Produksi aktif! Gunakan database pengujian sementara (`bignet_restore_temp`).

### Langkah Restore Test:
1. **Buat Database Sementara**:
   ```sql
   CREATE DATABASE bignet_restore_temp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
2. **Import File Backup**:
   ```bash
   gunzip -c /var/backups/bignet/bignet_2026-09-14_02-00.sql.gz | mysql -u root -p bignet_restore_temp
   ```
3. **Verifikasi Integritas Schema & Data**:
   - Periksa jumlah tabel (wajib lengkap): `users`, `categories`, `units`, `warehouses`, `locations`, `items`, `stocks`, `serial_numbers`, `submissions`, `incoming_items`, `outgoing_items`, `transfers`, `loans`, `returns`, `installations`, `incidents`, `adjustments`, `stock_opnames`, `stock_ledgers`, `audit_logs`, `notifications`, `settings`.
   - Periksa row count pada tabel utama (`users`, `items`, `stocks`).
   - Periksa keberadaan Foreign Key constraints.
4. **Hapus Database Sementara Setelah Verifikasi**:
   ```sql
   DROP DATABASE bignet_restore_temp;
   ```

---

## 4. File Storage Backup & Restore

- **Private Uploads**: `storage/private_uploads`
- **Public Uploads**: `public/uploads`
- **Pemulihan File Storage**:
  ```bash
  tar -xzf /var/backups/bignet/storage_2026-09-14_02-00.tar.gz -C /var/www/bignet/
  chown -R www-data:www-data /var/www/bignet/storage/private_uploads /var/www/bignet/public/uploads
  ```
