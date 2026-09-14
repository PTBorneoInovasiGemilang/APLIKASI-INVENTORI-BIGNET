# BIGNET — PRODUCTION PHP RUNTIME REQUIREMENTS

Dokumen ini menentukan spesifikasi minimum lingkungan PHP runtime yang dibutuhkan untuk menjalankan aplikasi BIGNET di server produksi.

---

## 1. PHP Version
- **Minimum Required**: PHP **8.1.0** atau lebih baru.
- **Recommended**: PHP **8.2.x** LTS.

---

## 2. Required PHP Extensions

Sistem BIGNET membutuhkan ekstensi PHP berikut agar seluruh fitur (Database, PDO, JSON, File Upload, Image Processing, PDF Rendering, Barcode Generation) berfungsi sempurna:

| Ekstensi PHP | Kegunaan dalam BIGNET | Mandatory / Optional |
| :--- | :--- | :--- |
| `pdo_mysql` / `mysqli` | Koneksi database MySQL/MariaDB dan transaksi atomic. | **Mandatory** |
| `mbstring` | Pemrosesan string UTF-8 dan validasi karakter. | **Mandatory** |
| `json` | Pemrosesan API endpoint, payload JSON, dan live search. | **Mandatory** |
| `gd` / `imagick` | Pemrosesan avatar user, bukti instalasi/insiden, dan manipulasi gambar. | **Mandatory** |
| `fileinfo` | Deteksi MIME-type yang aman pada pengunggahan bukti/file. | **Mandatory** |
| `session` | Manajemen session otentikasi dan otorisasi pengguna. | **Mandatory** |
| `ctype` | Validasi tipe karakter internal PHP. | **Mandatory** |
| `filter` | Validasi input email dan data form. | **Mandatory** |
| `curl` / `openssl` | Komunikasi HTTP aman dan generasi token acak. | **Mandatory** |
| `zip` / `zlib` | Ekspor kompresi dan pengelolaan file laporan. | **Optional** |

---

## 3. Recommended `php.ini` Settings for Production

```ini
; Production Error Display Settings (Dilarang menampilkan error ke publik)
display_errors = Off
display_startup_errors = Off
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
log_errors = On
error_log = /var/log/php/bignet_errors.log

; Memory & Execution Limits
memory_limit = 256M
max_execution_time = 60
max_input_time = 60
max_input_vars = 3000

; File Upload Limits
file_uploads = On
upload_max_filesize = 10M
post_max_size = 12M

; Session Security Configuration
session.use_strict_mode = 1
session.cookie_httponly = 1
session.cookie_secure = 1
session.cookie_samesite = "Lax"
session.gc_maxlifetime = 14400

; OPcache Performance Optimization
opcache.enable = 1
opcache.enable_cli = 1
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 8
opcache.max_accelerated_files = 10000
opcache.validate_timestamps = 0
```
