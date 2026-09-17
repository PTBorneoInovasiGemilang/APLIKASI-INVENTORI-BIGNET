<?php
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
            putenv("{$name}={$value}");
        }
    }
}
$host = getenv('DB_HOST') ?: '127.0.0.1';
$db   = getenv('DB_DATABASE') ?: 'db_gudang_tommy';
$user = getenv('DB_USERNAME') ?: 'root';
$pass = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '';
$pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$queries = [
    "SELECT t.*, COALESCE(NULLIF(t.receiver_name, ''), ur.name) AS actual_receiver_name FROM transfers t LEFT JOIN users ur ON t.received_by = ur.id"
];

foreach ($queries as $i => $q) {
    try {
        $wrapped = "SELECT * FROM ({$q}) AS test LIMIT 1";
        $pdo->query($wrapped);
    } catch (\Exception $e) {
        echo "Error in query $i: " . $e->getMessage() . "\n";
    }
}
echo "Done.\n";
