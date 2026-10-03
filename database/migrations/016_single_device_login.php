<?php

use PDO;

class SingleDeviceLogin
{
    public function up(PDO $pdo)
    {
        $columns = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN, 0);
        if (!in_array('active_session_token_hash', $columns, true)) {
            $pdo->exec("ALTER TABLE users ADD COLUMN active_session_token_hash VARCHAR(64) NULL AFTER last_login_at");
        }
        if (!in_array('active_session_started_at', $columns, true)) {
            $pdo->exec("ALTER TABLE users ADD COLUMN active_session_started_at DATETIME NULL AFTER active_session_token_hash");
        }
        if (!in_array('active_session_last_seen_at', $columns, true)) {
            $pdo->exec("ALTER TABLE users ADD COLUMN active_session_last_seen_at DATETIME NULL AFTER active_session_started_at");
        }
    }
}
