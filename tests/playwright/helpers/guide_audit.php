<?php
/**
 * Audit: daftar route GET yang TIDAK memiliki Page Guide (exact/prefix match di PageGuideHelper).
 * Usage: php tests/playwright/helpers/guide_audit.php
 */
require __DIR__ . '/../../../app/Helpers/PageGuideHelper.php';

$routes = [];
$lines = file(__DIR__ . '/../../../routes/web.php');
foreach ($lines as $l) {
    if (preg_match("/\\\$router->get\('([^']+)'/", $l, $m)) {
        $routes[] = $m[1];
    }
}

$missing = [];
foreach ($routes as $r) {
    $guide = \App\Helpers\PageGuideHelper::forPath($r);
    if ($guide === null) {
        $missing[] = $r;
    }
}

echo "TOTAL_ROUTES=" . count($routes) . "\n";
echo "MISSING=" . count($missing) . "\n";
foreach ($missing as $m) {
    echo "  - " . $m . "\n";
}
