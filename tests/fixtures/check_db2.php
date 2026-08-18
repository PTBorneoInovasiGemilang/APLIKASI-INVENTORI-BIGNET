<?php
require 'app/Core/Database.php';
$db = \App\Core\Database::getInstance();
$stmt = $db->query("DESCRIBE incidents");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
