<?php

namespace App\Controllers;

use App\Helpers\AuthHelper;

class SessionController
{
    public function check()
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

        if (AuthHelper::isLoggedIn()) {
            echo json_encode(['active' => true]);
            return;
        }

        http_response_code(401);
        echo json_encode([
            'active' => false,
            'message' => $_SESSION['error'] ?? 'Sesi Anda telah berakhir. Silakan masuk kembali.'
        ]);
        exit;
    }

    public function touch()
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            echo json_encode(['active' => false, 'message' => 'Permintaan tidak valid.']);
            exit;
        }

        if (!AuthHelper::isLoggedIn()) {
            http_response_code(401);
            echo json_encode([
                'active' => false,
                'message' => $_SESSION['error'] ?? 'Sesi Anda telah berakhir. Silakan masuk kembali.'
            ]);
            exit;
        }

        if (!AuthHelper::touchActiveSession()) {
            http_response_code(401);
            echo json_encode([
                'active' => false,
                'message' => 'Sesi perangkat ini sudah tidak aktif. Silakan masuk kembali.'
            ]);
            exit;
        }

        echo json_encode(['active' => true]);
    }
}
