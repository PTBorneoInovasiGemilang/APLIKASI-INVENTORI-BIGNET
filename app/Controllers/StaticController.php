<?php
namespace App\Controllers;

/**
 * Melayani aset statis yang di-vendor melalui router agar bekerja di
 * php built-in server maupun Apache (semua request masuk ke front controller).
 */
class StaticController {

    public function qrcodeJs() {
        $this->serveJsAsset('qrcode.min.js');
    }

    /**
     * Global Feedback UI (AppModal + AppToast) — dipakai seluruh halaman aplikasi.
     * Dilayani via router (bukan file publik langsung) agar konsisten dengan qrcode.min.js.
     */
    public function feedbackJs() {
        $this->serveJsAsset('app-feedback.js');
    }

    private function serveJsAsset($filename) {
        $file = __DIR__ . '/../../public/assets/js/' . $filename;
        if (!is_file($file)) {
            http_response_code(404);
            exit('Not found');
        }
        header('Content-Type: application/javascript; charset=utf-8');
        header('Cache-Control: public, max-age=3600');
        readfile($file);
        exit;
    }

    /**
     * Logo Utama (Pengaturan Sistem -> Branding & Tampilan).
     * Publik karena ditampilkan di halaman login yang tidak memerlukan autentikasi.
     * Path dibaca dari settings (bukan dari input user), dibatasi basename + ekstensi
     * whitelist + is_file, sehingga aman dari traversal dan tidak menampilkan path.
     */
    public function logo() {
        $this->serveBranding('logo_path');
    }

    /**
     * Favicon (Pengaturan Sistem -> Branding & Tampilan). Publik, aturan keamanan sama dgn logo().
     */
    public function favicon() {
        $this->serveBranding('favicon_path');
    }

    private function serveBranding($settingKey) {
        $path = \App\Helpers\SettingsHelper::get($settingKey, '');
        if (empty($path)) {
            http_response_code(404);
            exit('Not found');
        }

        $filename = basename($path); // anti-traversal: abaikan komponen direktori
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'svg', 'ico', 'webp'];
        if (!in_array($ext, $allowed)) {
            http_response_code(404);
            exit('Not found');
        }

        $file = __DIR__ . '/../../storage/private_uploads/' . $filename;
        if (!is_file($file)) {
            http_response_code(404);
            exit('Not found');
        }

        $mimeType = mime_content_type($file);
        header('Content-Type: ' . ($mimeType ?: 'application/octet-stream'));
        header('Content-Length: ' . filesize($file));
        header('Cache-Control: public, max-age=3600');
        readfile($file);
        exit;
    }
}
