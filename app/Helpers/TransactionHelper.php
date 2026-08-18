<?php

namespace App\Helpers;

use App\Core\Database;
use PDO;

class TransactionHelper {
    
    /**
     * Generate a unique transaction code with format PREFIX-YYYYMMDD-XXXX
     * 
     * @param string $prefix E.g., REQ, IN, OUT, TRF, LOAN, RET, INS, ADJ
     * @param string $table The table to check for existing codes
     * @param string $column The column name of the code (default: transaction_code)
     * @return string The generated code
     */
    public static function generateCode($prefix, $table, $column = 'transaction_code') {
        $pdo = Database::getInstance()->getConnection();
        $date = date('Ymd');
        $basePattern = $prefix . '-' . $date . '-%';
        
        $sql = "SELECT {$column} FROM {$table} WHERE {$column} LIKE ? ORDER BY id DESC LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$basePattern]);
        $lastCode = $stmt->fetchColumn();
        
        if ($lastCode) {
            $parts = explode('-', $lastCode);
            $lastNum = (int)end($parts);
            $newNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNum = '0001';
        }
        
        return $prefix . '-' . $date . '-' . $newNum;
    }
}
