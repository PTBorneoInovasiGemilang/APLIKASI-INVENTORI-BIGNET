<?php

// Basic Autoloader for App namespace
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Lightweight .env Loader
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || $line[0] === '#') continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv("{$name}={$value}");
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

// Load config
$appConfig = require __DIR__ . '/../config/app.php';
date_default_timezone_set($appConfig['timezone']);

// Global URL Helper
if (!function_exists('url')) {
    function url($path = '') {
        if (php_sapi_name() === 'cli-server') {
            $base = '';
        } else {
            $scriptName = dirname($_SERVER['SCRIPT_NAME']);
            $scriptName = str_replace('\\', '/', $scriptName);
            $requestUri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH));
            
            // If the user's URI does not contain /public/ but the script name does,
            // we should omit /public from the base URL to keep the URL clean.
            if (strpos($requestUri, '/public/') === false && strpos($scriptName, '/public') !== false) {
                $base = preg_replace('#/public$#', '', $scriptName);
            } else {
                $base = ($scriptName === '/') ? '' : $scriptName;
            }
        }
        $path = ltrim($path, '/');
        return $base . '/' . $path;
    }
}

// Global Settings Helper
if (!function_exists('setting')) {
    function setting($key, $default = '') {
        return \App\Helpers\SettingsHelper::get($key, $default);
    }
}

// Session security settings
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_strict_mode', 1);

// Enable Secure Cookie Flag dynamically if running over HTTPS
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
           (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') ||
           (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);
if ($isHttps) {
    ini_set('session.cookie_secure', 1);
}


// Suppress PHP warnings/notices from being shown to users
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);

session_start();

// Global CSRF Token initialization
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Server-side session timeout check
$sessionTimeoutMinutes = (int)(\App\Helpers\SettingsHelper::get('session_timeout_minutes', '30'));
$sessionTimeoutSeconds = $sessionTimeoutMinutes * 60;

if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
    if (isset($_SESSION['last_activity'])) {
        $idleTime = time() - $_SESSION['last_activity'];
        if ($idleTime > $sessionTimeoutSeconds) {
            // Session expired
            session_unset();
            session_destroy();
            session_start();
            session_regenerate_id(true);
            $_SESSION['error'] = 'Sesi Anda telah berakhir karena tidak ada aktivitas. Silakan masuk kembali.';
            header('Location: ' . url('/login'));
            exit;
        }
    }
    // Update last activity timestamp
    $_SESSION['last_activity'] = time();

    // Prevent caching of authenticated pages
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
}

// Initialize Router
$router = new \App\Core\Router();

// Load Routes
require __DIR__ . '/../routes/web.php';

// Dispatch
$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

try {
  // Dispatch route
ob_start(function($buffer) {
    $headers = headers_list();
    $isHtml = true;
    foreach ($headers as $header) {
        if (stripos($header, 'Content-Type:') === 0 && stripos($header, 'text/html') === false) {
            $isHtml = false;
            break;
        }
    }

    // Inject CSRF token into all POST forms only for HTML responses
    if ($isHtml && isset($_SESSION['csrf_token'])) {
        $token = $_SESSION['csrf_token'];
        $input = "\n" . '    <input type="hidden" name="csrf_token" value="' . $token . '">' . "\n";
        $buffer = preg_replace('/(<form[^>]*method=["\']POST["\'][^>]*>)/i', '$1' . $input, $buffer);
    }
    return $buffer;
});
$router->dispatch($uri, $method);
ob_end_flush();
} catch (\Exception $e) {
    if ($e->getMessage() === 'Route not found' || strpos($e->getMessage(), '404') !== false) {
        http_response_code(404);
        require __DIR__ . '/../resources/views/errors/404.php';
    } else {
        http_response_code(500);
        $errorMessage = $e->getMessage();
        require __DIR__ . '/../resources/views/errors/500.php';
    }
}
