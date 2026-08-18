<?php

use App\Core\Router;

/** @var Router $router */

$router->get('/', function() {
    header("Location: " . url('/login'));
    exit;
});

// Auth Routes
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@authenticate');
$router->get('/register', 'AuthController@showRegister');
$router->post('/register', 'AuthController@register');
$router->get('/logout', 'AuthController@logout');
$router->get('/forgot-password', 'AuthController@showForgotPassword');

// Dashboard / Search
$router->get('/dashboard', 'DashboardController@index');
$router->get('/search', 'SearchController@index');

// Master Data
$router->get('/categories', 'CategoryController@index');
$router->post('/categories', 'CategoryController@store');
$router->get('/categories/edit/{id}', 'CategoryController@edit');
$router->post('/categories/update/{id}', 'CategoryController@update');
$router->post('/categories/bulk-delete', 'CategoryController@bulkDelete');

$router->get('/units', 'UnitController@index');
$router->post('/units', 'UnitController@store');
$router->get('/units/edit/{id}', 'UnitController@edit');
$router->post('/units/update/{id}', 'UnitController@update');
$router->post('/units/bulk-delete', 'UnitController@bulkDelete');

$router->get('/warehouses', 'WarehouseController@index');
$router->post('/warehouses', 'WarehouseController@store');
$router->get('/warehouses/edit/{id}', 'WarehouseController@edit');
$router->post('/warehouses/update/{id}', 'WarehouseController@update');
$router->post('/warehouses/bulk-delete', 'WarehouseController@bulkDelete');

$router->get('/locations', 'LocationController@index');
$router->get('/locations/edit/{id}', 'LocationController@edit');
$router->post('/locations/update/{id}', 'LocationController@update');
$router->post('/locations/bulk-delete', 'LocationController@bulkDelete');

$router->get('/items', 'MasterItemController@index');
$router->post('/items', 'MasterItemController@store');
$router->get('/items/show/{id}', 'MasterItemController@show');
$router->get('/items/edit/{id}', 'MasterItemController@edit');
$router->post('/items/update/{id}', 'MasterItemController@update');
$router->post('/items/delete/{id}', 'MasterItemController@destroy');
$router->post('/items/toggle-active/{id}', 'MasterItemController@toggleActive');

// Transactions
$router->get('/submissions', 'SubmissionController@index');
$router->get('/submissions/create', 'SubmissionController@create');
$router->post('/submissions/store', 'SubmissionController@store');
$router->get('/submissions/show', 'SubmissionController@show');
$router->post('/submissions/approve', 'SubmissionController@approve');
$router->get('/submissions/api/items', 'SubmissionController@apiItems');

$router->get('/incoming', 'IncomingController@index');
$router->get('/incoming/create', 'IncomingController@create');
$router->post('/incoming/store', 'IncomingController@store');

$router->get('/outgoing', 'OutgoingController@index');
$router->get('/outgoing/create', 'OutgoingController@create');
$router->post('/outgoing/store', 'OutgoingController@store');
$router->get('/outgoing/show', 'OutgoingController@show');

$router->get('/transfers', 'TransferController@index');
$router->get('/transfers/create', 'TransferController@create');
$router->post('/transfers', 'TransferController@store');
$router->post('/transfers/delete', 'TransferController@delete');
$router->get('/transfers/show', 'TransferController@show');
$router->post('/transfers/receive', 'TransferController@receive');

$router->get('/loans', 'LoanController@index');
$router->get('/loans/create', 'LoanController@create');
$router->post('/loans/store', 'LoanController@store');
$router->get('/loans/show', 'LoanController@show');
$router->get('/loans/api/items', 'LoanController@apiItems');

$router->get('/returns', 'ReturnController@index');
$router->get('/returns/create', 'ReturnController@create');
$router->post('/returns/store', 'ReturnController@store');
$router->get('/returns/show', 'ReturnController@show');
$router->post('/returns/process', 'ReturnController@process');

$router->get('/installations', 'InstallationController@index');
$router->get('/installations/create', 'InstallationController@create');
$router->post('/installations/store', 'InstallationController@store');
$router->get('/installations/show', 'InstallationController@show');
$router->get('/installations/my_items', 'InstallationController@myItems');
$router->get('/installations/photos', 'InstallationController@photos');

$router->get('/adjustments', 'AdjustmentController@index');
$router->get('/adjustments/create', 'AdjustmentController@create');
$router->post('/adjustments/store', 'AdjustmentController@store');
$router->get('/adjustments/show', 'AdjustmentController@show');

$router->get('/incidents', 'IncidentController@index');
$router->get('/incidents/create', 'IncidentController@create');
$router->post('/incidents/store', 'IncidentController@store');
$router->get('/incidents/show', 'IncidentController@show');
$router->post('/incidents/process', 'IncidentController@process');

$router->get('/opnames', 'StockOpnameController@index');
$router->get('/opnames/create', 'StockOpnameController@create');
$router->post('/opnames/store', 'StockOpnameController@store');
$router->get('/opnames/show', 'StockOpnameController@show');
$router->post('/opnames/process', 'StockOpnameController@process');
$router->get('/opnames/api/items', 'StockOpnameController@apiItems');

// API
$router->get('/api/stock', 'ApiController@stock');

// Settings
$router->get('/settings', 'SettingsController@index');
$router->post('/settings/update', 'SettingsController@update');

// Import / Export
$router->get('/import-export', 'ImportExportController@index');
$router->get('/import-export/export-items', 'ImportExportController@exportItems');
$router->get('/import-export/export-categories', 'ImportExportController@exportCategories');
$router->get('/import-export/export-warehouses', 'ImportExportController@exportWarehouses');
$router->get('/import-export/export-serials', 'ImportExportController@exportSerials');
$router->get('/import-export/template-items', 'ImportExportController@templateItems');
$router->post('/import-export/import-items', 'ImportExportController@importItems');

// Reports / Audit
$router->get('/reports', 'ReportController@index');
$router->get('/reports/audit', 'ReportController@auditLogs');
$router->get('/reports/ledger', 'ReportController@stockLedger');
$router->get('/reports/serials', 'ReportController@serials');
$router->get('/reports/submissions', 'ReportController@submissions');
$router->get('/reports/incoming', 'ReportController@incoming');
$router->get('/reports/outgoing', 'ReportController@outgoing');
$router->get('/reports/transfers', 'ReportController@transfers');
$router->get('/reports/loans', 'ReportController@loans');
$router->get('/reports/returns', 'ReportController@returns');
$router->get('/reports/installations', 'ReportController@installations');
$router->get('/reports/adjustments', 'ReportController@adjustments');
$router->get('/audit', 'ReportController@auditLogs');
$router->get('/history', 'ReportController@stockLedger');

// Inventory
$router->get('/stocks', 'StockController@index');
$router->get('/serials', 'SerialNumberController@index');
$router->get('/timeline', 'TimelineController@index');

// Private files
$router->get('/file', 'FileController@serve');

// Profile
$router->get('/profile', 'ProfileController@show');
$router->post('/profile/photo', 'ProfileController@uploadPhoto');
$router->post('/profile/update', 'ProfileController@update');

// Users
$router->get('/users', 'UserController@index');
$router->get('/users/create', 'UserController@create');
$router->post('/users/store', 'UserController@store');
$router->get('/users/edit/{id}', 'UserController@edit');
$router->post('/users/update/{id}', 'UserController@update');
$router->get('/users/show/{id}', 'UserController@show');
$router->post('/users/toggle-active', 'UserController@toggleActive');
$router->post('/users/reset-password', 'UserController@resetPassword');
$router->post('/users/approve', 'UserController@approve');
$router->post('/users/reject', 'UserController@reject');

// Notifications
$router->get('/notifications', 'NotificationController@index');
$router->post('/notifications/read', 'NotificationController@markRead');
$router->post('/notifications/read-all', 'NotificationController@markAllRead');

// Labels / Scan
$router->get('/labels', 'LabelController@index');
$router->get('/labels/create', 'LabelController@create');
$router->post('/labels/print', 'LabelController@printSheet');
$router->get('/scan', 'LabelController@scan');
$router->post('/scan', 'LabelController@scan');
$router->get('/assets/js/qrcode.min.js', 'StaticController@qrcodeJs');
$router->get('/assets/js/app-feedback.js', 'StaticController@feedbackJs');

// Branding
$router->get('/logo', 'StaticController@logo');
$router->get('/favicon', 'StaticController@favicon');

// Evidence
$router->get('/evidence/{id}', 'EvidenceController@view');
$router->post('/evidence/delete', 'EvidenceController@delete');
$router->post('/incidents/evidence/upload', 'IncidentController@uploadEvidence');

// Installer
$router->get('/install', 'InstallController@index');
$router->post('/install', 'InstallController@run');
