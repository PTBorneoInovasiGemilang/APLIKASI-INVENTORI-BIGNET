<?php
require 'app/Core/Database.php';
$db = \App\Core\Database::getInstance();
$stmt = $db->query("SELECT * FROM settings");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
