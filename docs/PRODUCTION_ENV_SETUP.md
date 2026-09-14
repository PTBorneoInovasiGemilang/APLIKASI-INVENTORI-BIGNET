# BIGNET — PRODUCTION ENVIRONMENT CONFIGURATION SETUP GUIDE

Dokumen ini menjelaskan seluruh variabel konfigurasi lingkungan (Environment Variables) untuk deployment Aplikasi Inventori BIGNET ke server produksi.

---

## 1. File `.env` & Keamanan

- **Lokasi File**: `/path/to/project/.env` (di root directory aplikasi).
- **Aturan Git**: File `.env` **TIDAK BOLEH** dimasukkan ke dalam version control (Git). `.env` harus dibuat manual di server produksi berdasarkan contoh template `.env.example`.
- **Hak Akses File**: `chmod 600 .env` atau `chmod 640 .env` dengan ownership `www-data:www-data` (atau user web server yang sesuai).

---

## 2. Template Konfigurasi `.env.example`

```ini
APP_NAME="GUDANG PT.BIG"
APP_URL=https://YOUR_DOMAIN
APP_ENV=production
APP_DEBUG=false
APP_TIMEZONE=Asia/Jakarta

DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bignet_inventory_prod
DB_USERNAME=bignet_app_user
DB_PASSWORD=YOUR_STRONG_SECURE_PASSWORD
DB_CHARSET=utf8mb4
```

---

## 3. Rincian Variabel Konfigurasi

| Nama Variabel | Status | Contoh Nilai | Deskripsi & Aturan |
| :--- | :--- | :--- | :--- |
| `APP_NAME` | Wajib | `"GUDANG PT.BIG"` | Nama resmi aplikasi yang akan muncul di header & title bar. |
| `APP_URL` | Wajib | `https://inventori.ptbig.com` | URL publik server produksi (WAJIB menggunakan `https://`). |
| `APP_ENV` | Wajib | `production` | Lingkungan sistem. Mengaktifkan optimasi & pengamanan produksi. |
| `APP_DEBUG` | Wajib | `false` | **WAJIB `false`**. Mencegah kebocoran stack trace error ke pengguna. |
| `APP_TIMEZONE` | Wajib | `Asia/Jakarta` | Timezone resmi seluruh transaksi stok dan audit log. |
| `DB_HOST` | Wajib | `127.0.0.1` / `localhost` | Host Server Database MySQL/MariaDB. |
| `DB_PORT` | Wajib | `3306` | Port Server Database. |
| `DB_DATABASE` | Wajib | `bignet_inventory_prod` | Nama database produksi. |
| `DB_USERNAME` | Wajib | `bignet_app_user` | Username pengguna khusus aplikasi (bukan root). |
| `DB_PASSWORD` | Wajib | `mK9#xP2$vL5!qZ8` | Password kuat pengguna database produksi. |
| `DB_CHARSET` | Wajib | `utf8mb4` | Charset database untuk mendukung karakter internasional. |

---

## 4. Secret & Password Handling

1. **Password Database**: Minimal 16 karakter acak yang terdiri dari kombinasi huruf besar, huruf kecil, angka, dan simbol.
2. **Jangan Menyimpan Password di Codebase**: Seluruh kredensial sensitif hanya dibaca via `getenv()` atau `$_ENV` yang dimuat oleh `config/database.php` dan `config/app.php`.
3. **Backup `.env`**: Simpan salinan `.env` produksi di Password Manager terenkripsi milik Tim Infrastructure (misalnya 1Password / Bitwarden Vault).
