<?php

namespace App\Core;

class Router {
    private $routes = [];

    public function get($path, $handler, $middleware = []) {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post($path, $handler, $middleware = []) {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    private function addRoute($method, $path, $handler, $middleware) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middleware' => $middleware
        ];
    }

    public function dispatch($uri, $method) {
        // Global CSRF Validation for POST requests
        if ($method === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (empty($token) || $token !== ($_SESSION['csrf_token'] ?? '')) {
                // If it's an API request, return JSON, else redirect with error
                if (strpos($uri, '/api/') !== false) {
                    http_response_code(403);
                    echo json_encode(['error' => 'CSRF token validation failed.']);
                    exit;
                } else {
                    $_SESSION['error'] = 'Sesi Anda telah kedaluwarsa atau permintaan tidak valid (CSRF). Silakan coba lagi.';
                    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/'));
                    exit;
                }
            }
        }

        // Strip query string and base path if needed
        $parsedUrl = parse_url($uri);
        $path = urldecode($parsedUrl['path']);

        // If app is in a subfolder, remove base path
        if (php_sapi_name() !== 'cli-server') {
            $scriptName = dirname($_SERVER['SCRIPT_NAME']);
            $scriptName = str_replace('\\', '/', $scriptName);
            $baseWithoutPublic = preg_replace('#/public$#', '', $scriptName);

            if ($scriptName !== '/' && strpos($path, $scriptName) === 0) {
                $path = substr($path, strlen($scriptName));
            } elseif ($baseWithoutPublic !== '' && $baseWithoutPublic !== '/' && strpos($path, $baseWithoutPublic) === 0) {
                $path = substr($path, strlen($baseWithoutPublic));
            }
            
            // Just in case there's still a leading /public/
            if (strpos($path, '/public/') === 0) {
                $path = substr($path, 7);
            }
        }
        $path = empty($path) ? '/' : $path;

        foreach ($this->routes as $route) {
            if ($route['method'] === $method) {
                $pattern = preg_replace('#\{([a-zA-Z0-9_]+)\}#', '(?P<\1>[a-zA-Z0-9_-]+)', $route['path']);
                $pattern = "#^{$pattern}$#";

                if (preg_match($pattern, $path, $matches)) {
                    // Check if SUPERADMIN is trying to write or access mutation paths
                    if (\App\Helpers\AuthHelper::hasRole('SUPERADMIN')) {
                        $handlerStr = $route['handler'];
                        if (is_string($handlerStr)) {
                            list($controller, $action) = explode('@', $handlerStr);
                            $restrictedControllers = [
                                'CategoryController', 'UnitController', 'WarehouseController', 
                                'LocationController', 'MasterItemController', 'SubmissionController',
                                'IncomingController', 'OutgoingController', 'TransferController', 
                                'LoanController', 'ReturnController', 'InstallationController', 
                                'AdjustmentController', 'IncidentController'
                            ];
                            $safeActions = ['index', 'show', 'timeline', 'myItems', 'photos'];
                            if (in_array($controller, $restrictedControllers) && !in_array($action, $safeActions)) {
                                http_response_code(403);
                                echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>403 - Akses Ditolak</title><script src="https://cdn.tailwindcss.com"></script></head><body class="bg-gray-50 dark:bg-gray-900 flex items-center justify-center min-h-screen text-gray-800 dark:text-gray-200"><div class="text-center bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-lg max-w-md border border-gray-200 dark:border-gray-700"><h1 class="text-6xl font-bold text-red-500">403</h1><p class="text-xl font-bold mt-4">Akses Ditolak</p><p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Superadmin hanya memiliki hak akses baca-saja (read-only) untuk modul operasional.</p><a href="' . url('/dashboard') . '" class="mt-6 inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors text-sm font-semibold">Kembali ke Dasbor</a></div></body></html>';
                                exit;
                            }
                        }
                    }
                    // Execute middleware here later
                    
                    // Extract params
                    $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                    // Execute handler
                    if (is_callable($route['handler'])) {
                        return call_user_func_array($route['handler'], $params);
                    } elseif (is_string($route['handler'])) {
                        list($controllerName, $methodName) = explode('@', $route['handler']);
                        $controllerClass = "App\\Controllers\\{$controllerName}";
                        if (class_exists($controllerClass)) {
                            $controller = new $controllerClass();
                            if (method_exists($controller, $methodName)) {
                                return call_user_func_array([$controller, $methodName], $params);
                            }
                        }
                    }
                }
            }
        }

        // 404 Not Found
        header("HTTP/1.0 404 Not Found");
        echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>404</title><script src="https://cdn.tailwindcss.com"></script></head><body class="bg-gray-100 flex items-center justify-center min-h-screen"><div class="text-center"><h1 class="text-6xl font-bold text-gray-300">404</h1><p class="text-xl text-gray-500 mt-4">Halaman tidak ditemukan</p><a href="' . url('/dashboard') . '" class="mt-6 inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm">Kembali ke Dasbor</a></div></body></html>';
        exit;
    }
}
