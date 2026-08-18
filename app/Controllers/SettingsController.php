<?php
namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Helpers\SettingsHelper;

class SettingsController {
    public function __construct() {
        AuthHelper::requireRole('SUPERADMIN');
    }

    public function index() {
        $settings = [
            'app_name' => SettingsHelper::get('app_name', 'GUDANG PT.BIG'),
            'app_short_name' => SettingsHelper::get('app_short_name', 'PT.BIG'),
            'company_name' => SettingsHelper::get('company_name', 'PT. BIG'),
            'footer_text' => SettingsHelper::get('footer_text', '© ' . date('Y') . ' PT. BIG. All rights reserved.'),
            'primary_color' => SettingsHelper::get('primary_color', 'blue'),
            'sidebar_theme' => SettingsHelper::get('sidebar_theme', 'dark'),
            'default_theme' => SettingsHelper::get('default_theme', 'light'),
            'login_title' => SettingsHelper::get('login_title', 'GUDANG PT.BIG'),
            'login_subtitle' => SettingsHelper::get('login_subtitle', 'Sign in to your account'),
            'session_timeout_minutes' => SettingsHelper::get('session_timeout_minutes', '30'),
        ];
        
        $tab = $_GET['tab'] ?? 'general';
        require __DIR__ . '/../../resources/views/settings/index.php';
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF protection here
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $_SESSION['error'] = "Invalid CSRF token.";
                header("Location: " . url('/settings'));
                exit;
            }

            $allowedKeys = [
                'app_name', 'app_short_name', 'company_name', 'footer_text',
                'primary_color', 'sidebar_theme', 'default_theme', 'login_title', 'login_subtitle',
                'session_timeout_minutes'
            ];

            foreach ($allowedKeys as $key) {
                if (isset($_POST[$key])) {
                    SettingsHelper::set($key, trim($_POST[$key]));
                }
            }

            // Handle file uploads (logo, favicon, etc)
            $this->handleUpload('logo_path', 'logo');
            $this->handleUpload('favicon_path', 'favicon');
            $this->handleUpload('login_background', 'login_bg');

            $_SESSION['success'] = "Settings updated successfully.";
            $tab = $_POST['tab'] ?? 'general';
            header("Location: " . url('/settings?tab=' . $tab));
            exit;
        }
    }

    private function handleUpload($settingKey, $inputName) {
        if (isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] === UPLOAD_ERR_OK) {
            $fileSize = $_FILES[$inputName]['size'];
            if ($fileSize > 2 * 1024 * 1024) {
                return; // Max 2MB
            }

            $ext = strtolower(pathinfo($_FILES[$inputName]['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'svg', 'ico'];
            if (!in_array($ext, $allowed)) {
                return;
            }

            $mimeType = mime_content_type($_FILES[$inputName]['tmp_name']);
            $allowedMimes = ['image/jpeg', 'image/png', 'image/svg+xml', 'image/x-icon', 'image/vnd.microsoft.icon'];
            
            // SVG safety
            if ($ext === 'svg' || $mimeType === 'image/svg+xml') {
                $svgContent = file_get_contents($_FILES[$inputName]['tmp_name']);
                if (preg_match('/<script/i', $svgContent) || preg_match('/javascript:/i', $svgContent)) {
                    return; // Reject unsafe SVG
                }
            } elseif (!in_array($mimeType, $allowedMimes)) {
                return;
            }

            $filename = preg_replace('/[^a-zA-Z0-9_]/', '', $inputName) . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
            $target = __DIR__ . '/../../storage/private_uploads/' . $filename;
            
            // Collision protection
            if (file_exists($target)) {
                $filename = preg_replace('/[^a-zA-Z0-9_]/', '', $inputName) . '_' . bin2hex(random_bytes(16)) . '.' . $ext;
                $target = __DIR__ . '/../../storage/private_uploads/' . $filename;
            }

            if (move_uploaded_file($_FILES[$inputName]['tmp_name'], $target)) {
                SettingsHelper::set($settingKey, $filename);
            }
        }
    }
}
