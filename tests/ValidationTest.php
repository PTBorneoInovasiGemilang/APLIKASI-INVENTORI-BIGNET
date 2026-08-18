<?php
require_once __DIR__ . '/../app/Core/Database.php';

use App\Core\Database;

$results = [
    'PASS' => 0,
    'FAIL' => 0,
    'DETAILS' => []
];

function assertTest($name, $condition, $failMessage = '') {
    global $results;
    if ($condition) {
        $results['PASS']++;
        $results['DETAILS'][] = "[PASS] $name";
    } else {
        $results['FAIL']++;
        $results['DETAILS'][] = "[FAIL] $name - $failMessage";
    }
}

// 1. Create a fake session
$sessionId = 'test-session-' . time();
session_id($sessionId);
session_start();
// Setup admin session
$pdo = Database::getInstance()->getConnection();
$admin = $pdo->query("SELECT id, name, email FROM users WHERE role = 'ADMIN' LIMIT 1")->fetch();
$_SESSION['user_id'] = $admin['id'];
$_SESSION['user_name'] = $admin['name'];
$_SESSION['user_email'] = $admin['email'];
$_SESSION['role'] = 'ADMIN';
$_SESSION['authenticated'] = true;
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$fixedCsrf = $_SESSION['csrf_token'];
session_write_close();

// Set up cookie file for curl with this session
$cookieFile = __DIR__ . '/cookie.txt';
file_put_contents($cookieFile, "localhost\tFALSE\t/\tFALSE\t0\tPHPSESSID\t$sessionId\n");

$baseUrl = 'http://localhost:8000';

// 2. Helper to make POST request and get redirect URL and error session
function postRequest($url, $data) {
    global $baseUrl, $cookieFile, $fixedCsrf;
    $data['csrf_token'] = $fixedCsrf;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // follow to see final page
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

function assertFormError($name, $url, $postUrl, $postData, $expectedErrorSubstring) {
    $response = postRequest($postUrl, $postData);
    
    // We expect the response HTML to contain the error message
    $pass = strpos($response, htmlspecialchars($expectedErrorSubstring)) !== false || strpos($response, $expectedErrorSubstring) !== false;
    assertTest($name, $pass, "Expected error containing '$expectedErrorSubstring' not found.");
}

// ---------------------------------------------------------
// CATEGORY VALIDATION
// ---------------------------------------------------------
assertFormError('Category: empty name', '/categories', '/categories', [
    'name' => '   ',
    'description' => 'test'
], 'Nama kategori tidak boleh kosong');

assertFormError('Category: name > 100 chars', '/categories', '/categories', [
    'name' => str_repeat('A', 101),
    'description' => 'test'
], 'Nama kategori tidak boleh lebih dari 100 karakter');

// ---------------------------------------------------------
// UNIT VALIDATION
// ---------------------------------------------------------
assertFormError('Unit: empty code and name', '/units', '/units', [
    'code' => ' ',
    'name' => ' '
], 'Kode dan nama satuan tidak boleh kosong');

assertFormError('Unit: code > 50 chars', '/units', '/units', [
    'code' => str_repeat('X', 51),
    'name' => 'Test Unit'
], 'Kode satuan maksimal 50 karakter');

// ---------------------------------------------------------
// WAREHOUSE VALIDATION
// ---------------------------------------------------------
assertFormError('Warehouse: empty name', '/warehouses', '/warehouses', [
    'code' => 'WH-TEST',
    'name' => ''
], 'Kode dan nama gudang tidak boleh kosong');

// ---------------------------------------------------------
// LOCATION VALIDATION
// ---------------------------------------------------------
assertFormError('Location: invalid warehouse_id', '/locations', '/locations', [
    'warehouse_id' => '99999',
    'code' => 'LOC-1',
    'name' => 'Loc 1'
], 'Gudang tidak valid atau tidak aktif');

// ---------------------------------------------------------
// MASTER ITEM VALIDATION
// ---------------------------------------------------------
assertFormError('Master Item: empty name', '/items', '/items', [
    'nama' => '',
    'item_category_id' => '1',
    'unit_id' => '1'
], 'Nama, kategori, dan satuan wajib diisi');

assertFormError('Master Item: negative min stock', '/items', '/items', [
    'nama' => 'Test Item',
    'item_category_id' => '1',
    'unit_id' => '1',
    'stok_minimum' => '-5'
], 'Stok minimum tidak boleh negatif');

assertFormError('Master Item: invalid category', '/items', '/items', [
    'nama' => 'Test Item',
    'item_category_id' => '99999',
    'unit_id' => '1',
    'stok_minimum' => '0'
], 'Kategori tidak valid atau tidak aktif');


echo "====================================\n";
echo "       VALIDATION TESTS\n";
echo "====================================\n";
foreach ($results['DETAILS'] as $detail) {
    echo $detail . "\n";
}
echo "------------------------------------\n";
echo "TOTAL REQUIREMENTS TESTED : " . count($results['DETAILS']) . "\n";
echo "PASS                      : " . $results['PASS'] . "\n";
echo "FAIL                      : " . $results['FAIL'] . "\n";
echo "====================================\n";
