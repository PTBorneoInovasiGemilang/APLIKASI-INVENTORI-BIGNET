# BIGNET — PRODUCTION MONITORING CHECKLIST

Dokumen ini berisi panduan pemantauan (monitoring) kesehatan infrastruktur dan aplikasi BIGNET di lingkungan produksi.

---

## 1. Pemantauan Infrastruktur & Server

| Item Monitoring | Alat / Perintah | Threshold Peringatan (Warning) | Actions Required |
| :--- | :--- | :--- | :--- |
| **HTTP Uptime** | UptimeRobot / Healthcheck Ping | Availability < 99.9% / HTTP $\neq$ 200 | Periksa status Nginx/Apache & PHP-FPM service. |
| **CPU Usage** | `top` / `htop` / Prometheus | Usage > 80% selama 15 menit | Periksa proses PHP/MySQL yang menggantung. |
| **RAM Usage** | `free -h` | Free Memory < 15% | Periksa memory leak atau tingkatkan RAM server. |
| **Disk Space** | `df -h` | Used Disk > 85% | Clean old logs, periksa folder backups & storage. |
| **MySQL Process** | `systemctl status mysql` | Service Down / Connections > 80% | Restart MySQL daemon / optimasi connection pool. |

---

## 2. Pemantauan Log Aplikasi & PHP

- **Location PHP Error Log**: `/var/log/php/bignet_errors.log` (atau `/var/log/nginx/error.log`).
- **Pemeriksaan Berkala**:
  - `tail -f /var/log/php/bignet_errors.log`
  - Periksa uncaught exceptions, PDO database errors, atau undefined variable warnings.
- **Audit Log Growth**:
  - Pantau pertumbuhan jumlah baris pada tabel `audit_logs` dan `stock_ledgers`.

---

## 3. Pemantauan Cadangan (Backup) & Storage Growth

- **Status Backup**: Pastikan file backup harian `bignet_YYYY-MM-DD_HH-mm.sql.gz` selalu terbentuk di `/var/backups/bignet/`.
- **Pertumbuhan File Upload**: Pantau ukuran folder `storage/private_uploads/` dan `public/uploads/` agar tidak memenuhi kapasitas disk.
