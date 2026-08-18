<?php
session_start();
require_once 'app/Core/Database.php';

if (!function_exists('url')) {
    function url($path = '') { return $path; }
}

require_once 'app/Helpers/AuthHelper.php';

// 1. Simulate SUPERADMIN
$_SESSION = [];
$_SESSION['authenticated'] = true;
$_SESSION['role'] = 'SUPERADMIN';
$_SESSION['user_id'] = 1;

$hasAdminRole = (\App\Helpers\AuthHelper::hasRole('ADMIN'));
echo "SUPERADMIN:\n";
echo "- Button rendered? " . ($hasAdminRole ? "YES" : "NO") . "\n";

ob_start();
try {
    \App\Helpers\AuthHelper::denySuperadminWrite();
    echo "- Direct URL access: SUCCESS (200)\n";
} catch (Exception $e) {}
$output = ob_get_clean();
if (strpos($output, '403') !== false) {
    echo "- Direct URL access: BLOCKED (403)\n";
} else {
    echo $output;
}

// 2. Simulate ADMIN
$_SESSION = [];
$_SESSION['authenticated'] = true;
$_SESSION['role'] = 'ADMIN';
$_SESSION['user_id'] = 2;

$hasAdminRole = (\App\Helpers\AuthHelper::hasRole('ADMIN'));
echo "\nADMIN:\n";
echo "- Button rendered? " . ($hasAdminRole ? "YES" : "NO") . "\n";

ob_start();
try {
    \App\Helpers\AuthHelper::denySuperadminWrite();
    echo "- Direct URL access: SUCCESS (200)\n";
} catch (Exception $e) {}
$output = ob_get_clean();
if (strpos($output, '403') !== false) {
    echo "- Direct URL access: BLOCKED (403)\n";
} else {
    echo $output;
}
