<?php
require 'app/Core/Database.php';
$db = \App\Core\Database::getInstance();
$stmt = $db->query("SELECT evidence_file FROM incidents WHERE evidence_file LIKE '%dummy_evidence%'");
$res1 = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt = $db->query("SELECT evidence_file FROM installations WHERE evidence_file LIKE '%dummy_evidence%'");
$res2 = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt = $db->query("SELECT evidence_file FROM stock_opnames WHERE evidence_file LIKE '%dummy_evidence%'");
$res3 = $stmt->fetchAll(PDO::FETCH_ASSOC);

print_r(['incidents' => $res1, 'installations' => $res2, 'opnames' => $res3]);
