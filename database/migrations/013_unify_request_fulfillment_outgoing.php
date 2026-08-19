<?php
class UnifyRequestFulfillmentOutgoing {
    public function up(PDO $pdo) {
        $has = function(string $table, string $column) use ($pdo): bool {
            $q = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?");
            $q->execute([$table, $column]);
            return (bool)$q->fetchColumn();
        };

        if (!$has('outgoing', 'purpose')) {
            $pdo->exec("ALTER TABLE outgoing ADD purpose VARCHAR(30) NOT NULL DEFAULT 'ISSUE' AFTER submission_id");
        }
        if (!$has('outgoing', 'loan_id')) {
            $pdo->exec("ALTER TABLE outgoing ADD loan_id INT NULL AFTER purpose");
        }
        if (!$has('loans', 'outgoing_id')) {
            $pdo->exec("ALTER TABLE loans ADD outgoing_id INT NULL AFTER submission_id");
        }
        if (!$has('loan_manual_items', 'master_item_id')) {
            $pdo->exec("ALTER TABLE loan_manual_items ADD master_item_id INT NULL AFTER name");
        }
        if (!$has('loan_item_serials', 'loan_manual_item_id')) {
            $pdo->exec("ALTER TABLE loan_item_serials ADD loan_manual_item_id INT NULL AFTER loan_item_id");
        }
        $pdo->exec("ALTER TABLE loan_item_serials MODIFY loan_item_id INT NULL");

        $pdo->exec("UPDATE outgoing SET purpose='ISSUE' WHERE purpose IS NULL OR purpose=''");
    }
}
