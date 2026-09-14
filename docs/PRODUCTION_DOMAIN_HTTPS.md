# BIGNET — PRODUCTION DOMAIN, HTTPS & WEB SERVER CONFIGURATION GUIDE

Dokumen ini berisi spesifikasi konfigurasi Domain, Sertifikat SSL/TLS (HTTPS), Web Server DocumentRoot, dan Keamanan HTTP Cookie untuk deployment produksi BIGNET.

---

## 1. Domain & DNS Configuration

- **Domain Placeholder**: `YOUR_DOMAIN` (misalnya `inventori.ptbig.com`)
- **DNS Record Types**:
  - `A Record`: `YOUR_DOMAIN` $\rightarrow$ `IP_PUBLIC_SERVER_PRODUCTION`
  - `CNAME Record` (Opsional): `www.YOUR_DOMAIN` $\rightarrow$ `YOUR_DOMAIN`

---

## 2. HTTPS & SSL/TLS Setup

- **Sertifikat SSL**: Wajib menggunakan sertifikat SSL/TLS yang valid (misalnya Let's Encrypt / Certbot, Cloudflare SSL, atau Commercial CA Certificate).
- **Auto-Renewal**: Pastikan cron job / systemd timer untuk Let's Encrypt certbot auto-renew aktif (`certbot renew --dry-run`).
- **HTTP Strict Transport Security (HSTS)**: Disarankan untuk mengaktifkan HSTS header (`Strict-Transport-Security: max-age=31536000; includeSubDomains`).
- **Secure Cookie**: Dengan HTTPS aktif dan `APP_URL=https://...`, sistem BIGNET (`public/index.php`) otomatis menetapkan flag `Secure` dan `HttpOnly` pada cookie session PHP.

---

## 3. Web Server DocumentRoot Configuration

> **PENTING**: DocumentRoot web server **WAJIB** diarahkan ke subfolder `/public/`. Tidak boleh diarahkan ke root folder proyek untuk mencegah akses publik langsung ke source code PHP, file `.env`, atau log aplikasi.

### A. Nginx Configuration Block Example

```nginx
server {
    listen 80;
    server_name YOUR_DOMAIN;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name YOUR_DOMAIN;

    root /var/www/bignet/public;
    index index.php index.html;

    ssl_certificate /etc/letsencrypt/live/YOUR_DOMAIN/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/YOUR_DOMAIN/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    # Block direct access to hidden files (.env, .git, dll)
    location ~ /\. {
        deny all;
    }

    # Blokir eksekusi script PHP di folder upload
    location ~ ^/uploads/.*\.php$ {
        deny all;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_index index.php;
    }

    # Cache static assets
    location ~* \.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, no-transform";
    }
}
```

### B. Apache VirtualHost Configuration Example

```apache
<VirtualHost *:80>
    ServerName YOUR_DOMAIN
    Redirect permanent / https://YOUR_DOMAIN/
</VirtualHost>

<VirtualHost *:443>
    ServerName YOUR_DOMAIN
    DocumentRoot "/var/www/bignet/public"

    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/YOUR_DOMAIN/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/YOUR_DOMAIN/privkey.pem
    SSLProtocol all -SSLv3 -TLSv1 -TLSv1.1

    <Directory "/var/www/bignet/public">
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # Blokir eksekusi PHP pada folder upload
    <Directory "/var/www/bignet/public/uploads">
        <FilesMatch "\.(php|phtml|php3|php4|php5|phps)$">
            Require all denied
        </FilesMatch>
    </Directory>

    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
</VirtualHost>
```
