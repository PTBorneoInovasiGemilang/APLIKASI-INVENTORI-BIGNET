<?php
// Ensure this layout has access to auth user
$userName = $_SESSION['user_name'] ?? 'Guest';
$userRole = $_SESSION['role'] ?? 'GUEST';
$photo_path = \App\Helpers\AuthHelper::getUserPhoto() ?? null;

$primaryColor = setting('primary_color', 'blue');
$sidebarTheme = setting('sidebar_theme', 'dark');
$defaultTheme = setting('default_theme', 'light');

// Prepare color classes based on setting
$primaryBg ="bg-{$primaryColor}-600 hover:bg-{$primaryColor}-700";
$primaryText ="text-{$primaryColor}-600";
$sidebarBg = $sidebarTheme === 'dark' ? 'bg-gray-900 text-gray-300' : 'bg-white text-gray-700 border-r border-gray-200';
$sidebarHover = $sidebarTheme === 'dark' ? 'hover:bg-gray-800 hover:text-white' : 'hover:bg-gray-100 hover:text-gray-900';
$sidebarActive = $sidebarTheme === 'dark' ? 'bg-gray-800 text-white' : 'bg-blue-50 text-blue-700 font-semibold';
$sidebarIcon = $sidebarTheme === 'dark' ? 'text-gray-500' : 'text-gray-400';
$sidebarGroupText = $sidebarTheme === 'dark' ? 'text-gray-500' : 'text-gray-400';
$sidebarBorder = $sidebarTheme === 'dark' ? 'border-gray-800' : 'border-gray-200';

$favicon = setting('favicon_path') ? url('/file?path=' . urlencode(setting('favicon_path'))) : '';
$logo = setting('logo_path') ? url('/file?path=' . urlencode(setting('logo_path'))) : '';
$appName = setting('app_name', 'GUDANG PT.BIG');
$appShortName = setting('app_short_name', 'PT.BIG');

// Determine current path for active menu highlighting
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
$scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
$scriptDir = str_replace('\\', '/', $scriptDir);
if ($scriptDir !== '/') {
  $currentPath = str_replace($scriptDir, '', $currentPath);
}
$currentPath = $currentPath ?: '/dashboard';
$isDashboardPage = ($currentPath === '/dashboard' || $currentPath === '/');

if (!function_exists('isActive')) {
  function isActive($path, $currentPath) {
    if ($path === '/dashboard') {
      return $currentPath === '/dashboard' || $currentPath === '/';
    }
    
    $parsedPath = parse_url($path, PHP_URL_PATH);
    $parsedQuery = parse_url($path, PHP_URL_QUERY);
    
    $isPathMatch = false;
    
    if ($parsedPath === '/') {
      $isPathMatch = ($currentPath === '/');
    } else if ($parsedPath === $currentPath) {
      $isPathMatch = true;
    } else if (strpos($currentPath, $parsedPath . '/') === 0) {
      $isPathMatch = true;
    }
    
    // Prevent parent from being active if a specific child menu exists
    if ($parsedPath === '/serials' && strpos($currentPath, '/timeline') === 0) return false;
    if ($parsedPath === '/installations' && (strpos($currentPath, '/installations/my_items') === 0 || strpos($currentPath, '/installations/photos') === 0)) return false;

    if ($isPathMatch) {
      if ($parsedQuery) {
        parse_str($parsedQuery, $queryArgs);
        foreach ($queryArgs as $key => $val) {
          if (!isset($_GET[$key]) || $_GET[$key] !== $val) {
            return false;
          }
        }
        return true;
      }
      
      // Allow sub-page match if it has no query (e.g. /items)
      return true;
    }
    
    return false;
  }
}
if (!function_exists('menuClass')) {
  function menuClass($path, $currentPath, $sidebarActive, $sidebarHover) {
    return isActive($path, $currentPath) ? $sidebarActive : $sidebarHover;
  }
}
if (!function_exists('iconClass')) {
  function iconClass($path, $currentPath, $primaryText, $sidebarIcon) {
    // Semantic icon colors are global so all roles use the same visual language.
    // Base color is optimized for light mode; dark: variant keeps contrast in dark mode.
    $semantic = [
      '/dashboard' => 'text-blue-500 dark:text-blue-400',
      '/search' => 'text-cyan-500 dark:text-cyan-400',

      // Master data
      '/categories' => 'text-violet-500 dark:text-violet-400',
      '/units' => 'text-cyan-500 dark:text-cyan-400',
      '/warehouses' => 'text-amber-500 dark:text-amber-400',
      '/locations' => 'text-rose-500 dark:text-rose-400',
      '/items' => 'text-blue-500 dark:text-blue-400',

      // Inventory
      '/stocks' => 'text-green-500 dark:text-green-400',
      '/serials' => 'text-violet-500 dark:text-violet-400',
      '/labels/create' => 'text-indigo-500 dark:text-indigo-400',

      // Transactions
      '/submissions' => 'text-purple-500 dark:text-purple-400',
      '/incoming' => 'text-green-500 dark:text-green-400',
      '/outgoing' => 'text-red-500 dark:text-red-400',
      '/transfers' => 'text-sky-500 dark:text-sky-400',
      '/loans' => 'text-amber-500 dark:text-amber-400',
      '/returns' => 'text-emerald-500 dark:text-emerald-400',
      '/installations' => 'text-teal-500 dark:text-teal-400',
      '/installations/my_items' => 'text-teal-500 dark:text-teal-400',
      '/installations/photos' => 'text-fuchsia-500 dark:text-fuchsia-400',
      '/adjustments' => 'text-yellow-500 dark:text-yellow-400',
      '/incidents' => 'text-orange-500 dark:text-orange-400',
      '/incidents/create' => 'text-orange-500 dark:text-orange-400',
      '/opnames' => 'text-indigo-500 dark:text-indigo-400',

      // Reports
      '/reports/ledger' => 'text-blue-500 dark:text-blue-400',
      '/reports/serials' => 'text-violet-500 dark:text-violet-400',
      '/reports/submissions' => 'text-purple-500 dark:text-purple-400',
      '/reports/incoming' => 'text-green-500 dark:text-green-400',
      '/reports/outgoing' => 'text-red-500 dark:text-red-400',
      '/reports/transfers' => 'text-sky-500 dark:text-sky-400',
      '/reports/loans' => 'text-amber-500 dark:text-amber-400',
      '/reports/returns' => 'text-emerald-500 dark:text-emerald-400',
      '/reports/installations' => 'text-teal-500 dark:text-teal-400',
      '/reports/adjustments' => 'text-yellow-500 dark:text-yellow-400',
      '/timeline' => 'text-slate-500 dark:text-slate-400',
      '/reports/audit' => 'text-red-500 dark:text-red-400',

      // System
      '/users' => 'text-cyan-500 dark:text-cyan-400',
      '/settings' => 'text-slate-500 dark:text-slate-400',

      // Technician
      '/returns/create' => 'text-emerald-500 dark:text-emerald-400',
      '/serials' => 'text-violet-500 dark:text-violet-400',
    ];

    if (isset($semantic[$path])) {
      return $semantic[$path];
    }

    // Query-string paths (e.g. import-export?tab=import) use their path key.
    $basePath = parse_url($path, PHP_URL_PATH);
    if ($basePath && isset($semantic[$basePath])) {
      return $semantic[$basePath];
    }

    return isActive($path, $currentPath) ? $primaryText : $sidebarIcon;
  }
}

$roleLabel = \App\Helpers\UIHelper::roleLabel($userRole);

$adminPendingSubmissionCount = 0;
$adminPendingLoanCount = 0;
if ($userRole === 'ADMIN') {
  try {
    $sidebarPdo = \App\Core\Database::getInstance()->getConnection();
    $adminPendingSubmissionCount = (int)$sidebarPdo->query("SELECT COUNT(*) FROM submissions WHERE status = 'PENDING'")->fetchColumn();
    $adminPendingLoanCount = (int)$sidebarPdo->query("SELECT COUNT(*) FROM loans WHERE status = 'PENDING_APPROVAL'")->fetchColumn();
  } catch (\Throwable $sidebarCountError) {
    error_log('[BIGNET] Sidebar pending count gagal: ' . $sidebarCountError->getMessage());
  }
}

function renderMenu($label, $icon, $path, $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon, $badgeCount = 0) {
  $isActive = isActive($path, $currentPath);
  $cls = menuClass($path, $currentPath, $sidebarActive, $sidebarHover);
  $iCls = iconClass($path, $currentPath, $primaryText, $sidebarIcon);
  $url = url($path);
  $badge = '';
  if ((int)$badgeCount > 0) {
    $badge = '<span class="ml-auto inline-flex min-w-[20px] h-5 px-1.5 items-center justify-center rounded-full bg-red-500 text-white text-[10px] font-bold leading-none shadow-sm">'.(int)$badgeCount.'</span>';
  }
  return"<a href=\"{$url}\" class=\"sidebar-link flex items-center px-3 py-2.5 min-h-[44px] rounded-lg text-sm {$cls}\">\n        <i class=\"{$icon} w-5 text-center text-sm {$iCls}\"></i>\n        <span class=\"ml-3\">{$label}</span>{$badge}\n      </a>";
}

function renderHeader($label, $sidebarGroupText) {
  return"<div class=\"pt-4 pb-2 px-3 text-[10px] font-bold {$sidebarGroupText} uppercase tracking-widest\">{$label}</div>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?> — <?= htmlspecialchars($appName) ?></title>
  <?php if ($favicon): ?>
    <link rel="icon" href="<?= $favicon ?>">
  <?php endif; ?>
  <script>
    (function() {
      const theme = localStorage.getItem('theme');
      const defaultTheme = '<?= $defaultTheme ?>';
      if (theme === 'dark' || (!theme && defaultTheme === 'dark')) {
        document.documentElement.classList.add('dark');
      } else {
        document.documentElement.classList.remove('dark');
      }
    })();
  </script>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: { primary: tailwind.colors.<?= $primaryColor ?> },
          fontFamily: { sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'] }
        }
      }
    }
  </script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    * { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
    .sidebar-link { transition: all 0.15s ease; }
    .sidebar-link:hover { transform: translateX(2px); }
    .fade-in { animation: fadeIn 0.25s ease-in-out; }
    /* Standard shell: semua halaman dari seluruh role memakai titik awal horizontal/vertical yang sama. */
    main > .flex-1 > .fade-in {
      width: 100%;
      max-width: none;
      margin-left: auto;
      margin-right: auto;
      padding: 20px 24px 32px;
      box-sizing: border-box;
    }

    /* Normalisasi wrapper halaman lama (max-w-7xl, max-w-5xl, dll) agar tidak menggeser
       sebagian menu lebih ke tengah atau memberi padding ganda. */
    main > .flex-1 > .fade-in > [class*="max-w-"] {
      width: 100% !important;
      max-width: none !important;
      margin-left: 0 !important;
      margin-right: 0 !important;
      padding-top: 0 !important;
      padding-bottom: 0 !important;
      padding-left: 0 !important;
      padding-right: 0 !important;
    }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }
    .toast { animation: slideIn 0.3s ease-out, fadeOut 0.3s ease-in 4.7s forwards; }
    @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
    @keyframes fadeOut { to { opacity: 0; transform: translateY(-10px); } }
    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #4b5563; border-radius: 3px; }
    ::-webkit-scrollbar-thumb:hover { background: #6b7280; }
    .sidebar-scroll::-webkit-scrollbar { width: 4px; }
    .sidebar-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 2px; }
    .table-sticky thead th:first-child,
    .table-sticky tbody td:first-child { position: sticky; left: 0; z-index: 10; background-color: #ffffff; }
    .table-sticky thead th:first-child { background-color: #f9fafb; }
    .table-sticky thead th:last-child,
    .table-sticky tbody td:last-child { position: sticky; right: 0; z-index: 10; background-color: #ffffff; }
    .table-sticky thead th:last-child { background-color: #f9fafb; }
    .dark .table-sticky thead th:first-child,
    .dark .table-sticky thead th:last-child { background-color: #374151; }
    .dark .table-sticky tbody td:first-child,
    .dark .table-sticky tbody td:last-child { background-color: #1f2937; }
    .dark .table-sticky tbody tr:hover td:first-child,
    .dark .table-sticky tbody tr:hover td:last-child { background-color: #374151; }
    .table-sticky tbody tr td:only-child { position: static; left: auto; right: auto; }

    @media print {
      body { background: #fff !important; }
      #sidebar, #sidebarOverlay, header, footer, #toastContainer, #userDropdownMenu, .no-print { display: none !important; }
      main { overflow: visible !important; padding: 0 !important; display: block !important; }
      main .fade-in, main > div { max-width: 100% !important; }
      .overflow-x-auto, .overflow-x-hidden { overflow: visible !important; }
      .table-sticky thead th:first-child, .table-sticky tbody td:first-child,
      .table-sticky thead th:last-child, .table-sticky tbody td:last-child { position: static !important; }
      .print-area { display: block !important; }
      .print-header { display: block !important; }
      @page { margin: 14mm 12mm; }
      .print-footer { display: block !important; position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 10px; color: #6b7280; }
      .print-footer .page-count::after { content: counter(page); }
      .dark { background: #fff !important; }
      .dark .bg-gray-800, .dark .bg-gray-900, .dark .bg-gray-950, .dark .bg-white { background: #fff !important; color: #111 !important; }
      .dark .text-gray-100, .dark .text-gray-200, .dark .text-gray-300, .dark .text-gray-400 { color: #111 !important; }
      .dark .border-gray-700, .dark .border-gray-800 { border-color: #d1d5db !important; }

      /* === SURAT JALAN PRINT TABLE FIX (2026-08-23) ===
        Keep the document table full-width and make the header span all columns.
        Quantity stays on one line; other columns may wrap only inside their cells. */
      #surat-jalan { box-sizing: border-box !important; }
      #surat-jalan .overflow-hidden { overflow: visible !important; }
      #surat-jalan table {
        width: 100% !important;
        min-width: 100% !important;
        max-width: 100% !important;
        table-layout: fixed !important;
        border-collapse: collapse !important;
        margin: 0 !important;
      }
      #surat-jalan table col.col-no { width: 7% !important; }
      #surat-jalan table col.col-name { width: 33% !important; }
      #surat-jalan table col.col-qty { width: 12% !important; }
      #surat-jalan table col.col-tracking { width: 20% !important; }
      #surat-jalan table col.col-serial { width: 28% !important; }
      #surat-jalan table thead { display: table-header-group !important; width: 100% !important; }
      #surat-jalan table thead tr {
        display: table-row !important;
        width: 100% !important;
        background: #111827 !important;
      }
      #surat-jalan table thead th {
        display: table-cell !important;
        background: #111827 !important;
        color: #fff !important;
        width: auto !important;
        box-sizing: border-box !important;
        overflow: hidden !important;
        vertical-align: middle !important;
      }
      #surat-jalan table tbody { display: table-row-group !important; width: 100% !important; }
      #surat-jalan table tbody tr { display: table-row !important; width: 100% !important; }
      #surat-jalan table tbody td { display: table-cell !important; box-sizing: border-box !important; }
      #surat-jalan table th:nth-child(1),
      #surat-jalan table td:nth-child(1) {
        text-align: center !important;
        white-space: nowrap !important;
        word-break: normal !important;
        overflow-wrap: normal !important;
      }
      #surat-jalan table th:nth-child(2),
      #surat-jalan table td:nth-child(2) {
        white-space: normal !important;
        overflow-wrap: anywhere !important;
        word-break: break-word !important;
      }
      #surat-jalan table th:nth-child(3),
      #surat-jalan table td:nth-child(3) {
        text-align: center !important;
        white-space: nowrap !important;
        word-break: normal !important;
        overflow-wrap: normal !important;
        min-width: 18mm !important;
      }
      #surat-jalan table th:nth-child(4),
      #surat-jalan table td:nth-child(4) {
        white-space: normal !important;
        overflow-wrap: anywhere !important;
        word-break: break-word !important;
      }
      #surat-jalan table th:nth-child(5),
      #surat-jalan table td:nth-child(5) {
        white-space: normal !important;
        overflow-wrap: anywhere !important;
        word-break: break-word !important;
      }
    }
    .print-header, .print-footer, .print-area { display: none; }

    body.modal-open { overflow: hidden; }
    .app-modal-backdrop { animation: fadeIn 0.15s ease-out; }
    .app-modal-panel { animation: modalIn 0.18s ease-out; }
    @keyframes modalIn { from { opacity: 0; transform: scale(0.96) translateY(4px); } to { opacity: 1; transform: scale(1) translateY(0); } }

    @media (max-width: 640px) {
      .mobile-card-table thead { display: none; }
      .mobile-card-table,
      .mobile-card-table tbody,
      .mobile-card-table tr,
      .mobile-card-table td { display: block; width: 100%; box-sizing: border-box; }
      .mobile-card-table tr { border: 1px solid #e5e7eb; border-radius: 0.75rem; margin-bottom: 0.75rem; padding: 0.25rem 0.75rem; background: #ffffff; box-shadow: 0 1px 2px rgba(0,0,0,0.04); }
      .dark .mobile-card-table tr { border-color: #374151; background: #1f2937; }
      .mobile-card-table td { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; padding: 0.5rem 0; border: none; white-space: normal; text-align: right; border-bottom: 1px dashed #e5e7eb; min-height: 2.5rem; }
      .dark .mobile-card-table td { border-bottom-color: #374151; }
      .mobile-card-table td:last-child { border-bottom: none; }
      .mobile-card-table td::before { content: attr(data-label); font-weight: 600; color: #6b7280; text-align: left; flex-shrink: 0; }
      .dark .mobile-card-table td::before { color: #9ca3af; }
      .mobile-card-table td { position: static !important; left: auto !important; right: auto !important; }
      .mobile-card-table td:first-child,
      .mobile-card-table td:last-child { background-color: transparent !important; }
      .mobile-card-table tr td:only-child { display: block; text-align: center; }
      .mobile-card-table tr td:only-child::before { content: none; }
      .mobile-card-table td[data-label="Aksi"] { justify-content: flex-end; }
      .mobile-card-table td[data-label="Aksi"]::before { display: none; }
      .mobile-card-table td[data-label="No"] { justify-content: flex-start; }
      .mobile-card-table td[data-label="No"]::before { display: none; }
    }
  </style>
</head>
<body class="bg-gray-50 dark:bg-gray-950 text-gray-800 dark:text-gray-200 fade-in">

  <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-20 hidden lg:hidden" onclick="toggleSidebar()"></div>

  <aside id="sidebar" class="w-64 h-screen <?= $sidebarBg ?> shadow-lg flex flex-col fixed left-0 top-0 overflow-hidden z-30 transition-transform -translate-x-full lg:translate-x-0">
    <a href="<?= url('/dashboard') ?>" class="px-5 py-4 flex items-center border-b <?= $sidebarBorder ?> min-h-[64px] flex-shrink-0 hover:opacity-80 transition-opacity">
      <?php if ($logo): ?>
        <img src="<?= $logo ?>" alt="Logo" class="h-8 mr-3 object-contain">
      <?php else: ?>
        <div class="w-8 h-8 rounded-lg bg-<?= $primaryColor ?>-600 flex items-center justify-center text-white font-bold text-sm mr-3">
          <?= strtoupper(substr($appShortName, 0, 2)) ?>
        </div>
      <?php endif; ?>
      <div>
        <h1 class="text-base font-bold tracking-tight <?= $sidebarTheme === 'dark' ? 'text-white' : 'text-gray-900 dark:text-gray-100' ?>"><?= htmlspecialchars($appShortName) ?></h1>
        <p class="text-xs <?= $sidebarGroupText ?> -mt-0.5">Sistem Gudang</p>
      </div>
    </a>
    <div id="sidebarScrollArea" class="flex-1 overflow-y-auto sidebar-scroll py-3 px-3 space-y-0.5" onscroll="saveSidebarScroll(this)">
      <?php if ($userRole === 'SUPERADMIN'): ?>
        <?= renderHeader('Utama', $sidebarGroupText) ?>
        <?= renderMenu('Dasbor', 'fas fa-th-large', '/dashboard', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Pencarian Barang', 'fas fa-search', '/search', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderHeader('Monitoring Master Data', $sidebarGroupText) ?>
        <?= renderMenu('Kategori Barang', 'fas fa-tags', '/categories', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Satuan', 'fas fa-ruler', '/units', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Gudang', 'fas fa-warehouse', '/warehouses', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Lokasi', 'fas fa-map-marker-alt', '/locations', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Master Barang', 'fas fa-box', '/items', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderHeader('Inventaris Monitoring', $sidebarGroupText) ?>
        <?= renderMenu('Stok Barang', 'fas fa-cubes', '/stocks', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Nomor Seri', 'fas fa-barcode', '/serials', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Cetak Label Massal', 'fas fa-print', '/labels/create', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderHeader('Transaksi Monitoring', $sidebarGroupText) ?>
        <?= renderMenu('Pengajuan Barang', 'fas fa-clipboard-list', '/submissions', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Barang Masuk', 'fas fa-arrow-down', '/incoming', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Barang Keluar', 'fas fa-arrow-up', '/outgoing', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Transfer Gudang', 'fas fa-exchange-alt', '/transfers', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Peminjaman', 'fas fa-hand-holding', '/loans', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Pengembalian', 'fas fa-undo', '/returns', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Instalasi', 'fas fa-tools', '/installations', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Penyesuaian Stok', 'fas fa-sliders-h', '/adjustments', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Insiden (Damage/Lost)', 'fas fa-exclamation-triangle', '/incidents', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Stock Opname', 'fas fa-clipboard-check', '/opnames', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderHeader('Laporan', $sidebarGroupText) ?>
        <?= renderMenu('Laporan Stok & Mutasi', 'fas fa-chart-bar', '/reports/ledger', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Laporan Nomor Seri', 'fas fa-barcode', '/reports/serials', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Laporan Pengajuan', 'fas fa-clipboard-check', '/reports/submissions', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Laporan Barang Masuk', 'fas fa-arrow-down', '/reports/incoming', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Laporan Barang Keluar', 'fas fa-arrow-up', '/reports/outgoing', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Laporan Transfer', 'fas fa-exchange-alt', '/reports/transfers', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Laporan Peminjaman', 'fas fa-hand-holding', '/reports/loans', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Laporan Pengembalian', 'fas fa-undo', '/reports/returns', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Laporan Instalasi', 'fas fa-tools', '/reports/installations', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Laporan Penyesuaian', 'fas fa-sliders-h', '/reports/adjustments', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Riwayat Timeline', 'fas fa-history', '/timeline', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Log Aktivitas', 'fas fa-shield-alt', '/reports/audit', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderHeader('Sistem', $sidebarGroupText) ?>
        <?= renderMenu('Pengguna', 'fas fa-users-cog', '/users', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Pengaturan Sistem', 'fas fa-cogs', '/settings', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
      <?php endif; ?>
      <?php if ($userRole === 'ADMIN'): ?>
        <?= renderHeader('Utama', $sidebarGroupText) ?>
        <?= renderMenu('Dasbor', 'fas fa-th-large', '/dashboard', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Pencarian Barang', 'fas fa-search', '/search', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderHeader('Master Data', $sidebarGroupText) ?>
        <?= renderMenu('Kategori Barang', 'fas fa-tags', '/categories', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Satuan', 'fas fa-ruler', '/units', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Gudang', 'fas fa-warehouse', '/warehouses', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Lokasi', 'fas fa-map-marker-alt', '/locations', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Master Barang', 'fas fa-box', '/items', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderHeader('Inventaris', $sidebarGroupText) ?>
        <?= renderMenu('Stok Barang', 'fas fa-cubes', '/stocks', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Nomor Seri', 'fas fa-barcode', '/serials', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Cetak Label Massal', 'fas fa-print', '/labels/create', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Riwayat Timeline', 'fas fa-history', '/timeline', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderHeader('Transaksi', $sidebarGroupText) ?>
        <?= renderMenu('Pengajuan Barang', 'fas fa-clipboard-list', '/submissions', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon, $adminPendingSubmissionCount) ?>
        <?= renderMenu('Barang Masuk', 'fas fa-arrow-down', '/incoming', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Barang Keluar', 'fas fa-arrow-up', '/outgoing', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Transfer Gudang', 'fas fa-exchange-alt', '/transfers', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Peminjaman', 'fas fa-hand-holding', '/loans', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon, $adminPendingLoanCount) ?>
        <?= renderMenu('Pengembalian', 'fas fa-undo', '/returns', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Instalasi', 'fas fa-tools', '/installations', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Penyesuaian Stok', 'fas fa-sliders-h', '/adjustments', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Insiden (Damage/Lost)', 'fas fa-exclamation-triangle', '/incidents', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Stock Opname', 'fas fa-clipboard-check', '/opnames', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderHeader('Laporan', $sidebarGroupText) ?>
        <?= renderMenu('Laporan Stok & Mutasi', 'fas fa-chart-bar', '/reports/ledger', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Laporan Nomor Seri', 'fas fa-barcode', '/reports/serials', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Laporan Pengajuan', 'fas fa-clipboard-check', '/reports/submissions', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Laporan Barang Masuk', 'fas fa-arrow-down', '/reports/incoming', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Laporan Barang Keluar', 'fas fa-arrow-up', '/reports/outgoing', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Laporan Transfer', 'fas fa-exchange-alt', '/reports/transfers', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Laporan Peminjaman', 'fas fa-hand-holding', '/reports/loans', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Laporan Pengembalian', 'fas fa-undo', '/reports/returns', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Laporan Instalasi', 'fas fa-tools', '/reports/installations', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Laporan Penyesuaian', 'fas fa-sliders-h', '/reports/adjustments', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Riwayat Timeline', 'fas fa-history', '/timeline', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderHeader('Import Data', $sidebarGroupText) ?>
        <?= renderMenu('Import Data', 'fas fa-file-import', '/import-export?tab=import', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
      <?php endif; ?>
      <?php if ($userRole === 'TEKNISI_NOC'): ?>
        <?= renderHeader('Utama', $sidebarGroupText) ?>
        <?= renderMenu('Dasbor Saya', 'fas fa-th-large', '/dashboard', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderHeader('Permintaan', $sidebarGroupText) ?>
        <?= renderMenu('Pengajuan Barang Saya', 'fas fa-clipboard-list', '/submissions', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderHeader('Barang Saya', $sidebarGroupText) ?>
        <?= renderMenu('Barang Dipinjam', 'fas fa-hand-holding', '/loans', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Pengembalian Barang', 'fas fa-undo', '/returns', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Nomor Seri Saya', 'fas fa-barcode', '/serials', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Barang Terpasang', 'fas fa-tools', '/installations/my_items', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Riwayat Saya', 'fas fa-history', '/timeline', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderHeader('Operasional', $sidebarGroupText) ?>
        <?= renderMenu('Instalasi', 'fas fa-tools', '/installations', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Foto Instalasi', 'fas fa-camera', '/installations/photos', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
        <?= renderMenu('Lapor Kerusakan/Kehilangan', 'fas fa-exclamation-triangle', '/incidents/create', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
      <?php endif; ?>
    </div>
  </aside>

  <div class="lg:ml-64 min-h-screen flex flex-col transition-all">
    <header class="bg-white dark:bg-gray-900 shadow-sm border-b border-gray-200 dark:border-gray-800 min-h-[64px] flex items-center justify-between px-4 lg:px-6 sticky top-0 z-20">
      <div class="flex items-center flex-1 min-w-0 mr-4">
        <button class="lg:hidden shrink-0 text-gray-500 dark:text-gray-400 focus:outline-none mr-3 p-1" onclick="toggleSidebar()" aria-label="Toggle menu"><i class="fas fa-bars text-lg"></i></button>
        <div class="min-w-0"><h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 truncate"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h2></div>
      </div>
      <div class="flex items-center space-x-2 sm:space-x-4 shrink-0">
        <button onclick="document.documentElement.classList.toggle('dark'); localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');" class="text-gray-400 hover:text-gray-600 dark:text-gray-300 dark:hover:text-gray-200 focus:outline-none p-2 rounded-lg hover:bg-gray-100 dark:bg-gray-700 dark:hover:bg-gray-800 transition-colors" aria-label="Toggle dark mode"><i class="fas fa-moon text-sm"></i></button>
        <span class="text-xs text-gray-400 dark:text-gray-500 hidden sm:block font-medium"><?= date('l, d M Y') ?></span>
        <?php if (isset($_SESSION['user_id'])): ?>
        <?php $unreadCount = \App\Helpers\NotificationHelper::unreadCount((int)$_SESSION['user_id']); ?>
        <a href="<?= url('/notifications') ?>" class="relative text-gray-400 hover:text-gray-600 dark:text-gray-300 dark:hover:text-gray-200 focus:outline-none p-2 rounded-lg hover:bg-gray-100 dark:bg-gray-700 dark:hover:bg-gray-800 transition-colors" aria-label="Notifikasi"><i class="fas fa-bell text-sm"></i><?php if ($unreadCount > 0): ?><span class="absolute -top-0.5 -right-0.5 bg-red-500 text-white text-[10px] font-bold rounded-full min-w-[16px] h-4 px-1 flex items-center justify-center"><?= $unreadCount > 99 ? '99+' : $unreadCount ?></span><?php endif; ?></a>
        <?php endif; ?>
        <div class="relative ml-3">
          <button id="userDropdownBtn" class="flex items-center space-x-2 focus:outline-none">
            <?php if ($photo_path): ?><img src="<?= url('/file?path='.urlencode($photo_path)) ?>" class="w-8 h-8 rounded-full object-cover ring-2 ring-gray-200 dark:ring-gray-700" alt="Foto"><?php else: ?><div class="w-8 h-8 rounded-full bg-<?= $primaryColor ?>-600 flex items-center justify-center text-white font-bold text-sm ring-2 ring-gray-200 dark:ring-gray-700"><?= strtoupper(substr($userName, 0, 1)) ?></div><?php endif; ?><i class="fas fa-chevron-down text-xs text-gray-400"></i>
          </button>
          <div id="userDropdownMenu" class="hidden absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg py-1 ring-1 ring-black ring-opacity-5 focus:outline-none z-50">
            <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-700"><p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate"><?= htmlspecialchars($userName) ?></p><p class="text-xs text-gray-500 dark:text-gray-400 truncate"><?= $roleLabel ?></p></div>
            <a href="<?= url('/profile') ?>" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:bg-gray-700 dark:hover:bg-gray-700"><i class="fas fa-user-circle mr-2 w-4 text-center"></i> Profil Saya</a>
            <a href="<?= url('/profile?tab=password') ?>" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:bg-gray-700 dark:hover:bg-gray-700"><i class="fas fa-key mr-2 w-4 text-center"></i> Ubah Password</a>
            <a href="<?= url('/logout') ?>" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30 border-t border-gray-100 dark:border-gray-700 mt-1"><i class="fas fa-sign-out-alt mr-2 w-4 text-center"></i> Keluar</a>
          </div>
        </div>
      </div>
    </header>

    <div id="toastContainer" class="fixed top-4 right-4 z-[110] space-y-2 pointer-events-none">
      <?php if (isset($_SESSION['success'])): ?><div class="toast bg-green-50 border border-green-200 text-green-800 dark:bg-green-900/80 dark:border-green-700 dark:text-green-200 px-4 py-3 rounded-lg shadow-lg flex items-center max-w-sm"><i class="fas fa-check-circle mr-3 text-green-500"></i><span class="text-sm"><?= htmlspecialchars($_SESSION['success']) ?></span></div><?php unset($_SESSION['success']); endif; ?>
      <?php if (isset($_SESSION['error'])): ?><div class="toast bg-red-50 border border-red-200 text-red-800 dark:bg-red-900/80 dark:border-red-700 dark:text-red-200 px-4 py-3 rounded-lg shadow-lg flex items-center max-w-sm"><i class="fas fa-exclamation-circle mr-3 text-red-500"></i><span class="text-sm"><?= htmlspecialchars($_SESSION['error']) ?></span></div><?php unset($_SESSION['error']); endif; ?>
    </div>

    <main class="flex-1 bg-gray-50 dark:bg-gray-950 p-4 2xl:p-6 2xl:flex 2xl:gap-6">
      <div class="flex-1 min-w-0 w-full"><div class="fade-in"><?= $content ?></div></div>
      <?php
      if (!isset($guide) && !isset($guide_content)) $guide = \App\Helpers\PageGuideHelper::forPath($currentPath);
      if (isset($guide) || (isset($guide_content) && !empty($guide_content))):
      ?>
      <div class="w-full 2xl:w-80 shrink-0 mt-6 2xl:mt-0"><div class="sticky top-6"><?php require __DIR__ . '/../components/page_guide.php'; ?></div></div>
      <?php endif; ?>
    </main>

    <footer class="bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800 px-4 py-3"><p class="text-xs text-center text-gray-400 dark:text-gray-600">© 2026 GUDANG PT.BIG - Dibuat oleh Tommy Ryan Dwiputra</p></footer>
  </div>

  <script>
  const isDashboardPage = <?= json_encode($isDashboardPage) ?>;

  const pageHeadingIconMap = [
    { match: '/dashboard', icon: 'fa-th-large', color: 'text-blue-500 dark:text-blue-400' },
    { match: '/search', icon: 'fa-search', color: 'text-cyan-500 dark:text-cyan-400' },
    { match: '/categories', icon: 'fa-tags', color: 'text-violet-500 dark:text-violet-400' },
    { match: '/units', icon: 'fa-ruler', color: 'text-cyan-500 dark:text-cyan-400' },
    { match: '/warehouses', icon: 'fa-warehouse', color: 'text-amber-500 dark:text-amber-400' },
    { match: '/locations', icon: 'fa-map-marker-alt', color: 'text-rose-500 dark:text-rose-400' },
    { match: '/items', icon: 'fa-box', color: 'text-blue-500 dark:text-blue-400' },
    { match: '/stocks', icon: 'fa-cubes', color: 'text-green-500 dark:text-green-400' },
    { match: '/serials', icon: 'fa-barcode', color: 'text-purple-500 dark:text-purple-400' },
    { match: '/labels', icon: 'fa-print', color: 'text-indigo-500 dark:text-indigo-400' },
    { match: '/submissions', icon: 'fa-clipboard-list', color: 'text-purple-500 dark:text-purple-400' },
    { match: '/incoming', icon: 'fa-arrow-down', color: 'text-green-500 dark:text-green-400' },
    { match: '/outgoing', icon: 'fa-arrow-up', color: 'text-red-500 dark:text-red-400' },
    { match: '/transfers', icon: 'fa-exchange-alt', color: 'text-sky-500 dark:text-sky-400' },
    { match: '/loans', icon: 'fa-hand-holding', color: 'text-amber-500 dark:text-amber-400' },
    { match: '/returns', icon: 'fa-undo', color: 'text-emerald-500 dark:text-emerald-400' },
    { match: '/installations', icon: 'fa-tools', color: 'text-teal-500 dark:text-teal-400' },
    { match: '/adjustments', icon: 'fa-sliders-h', color: 'text-yellow-500 dark:text-yellow-400' },
    { match: '/incidents', icon: 'fa-exclamation-triangle', color: 'text-orange-500 dark:text-orange-400' },
    { match: '/opnames', icon: 'fa-clipboard-check', color: 'text-indigo-500 dark:text-indigo-400' },
    { match: '/reports', icon: 'fa-chart-bar', color: 'text-blue-500 dark:text-blue-400' },
    { match: '/timeline', icon: 'fa-history', color: 'text-slate-500 dark:text-slate-400' },
    { match: '/audit', icon: 'fa-shield-alt', color: 'text-red-500 dark:text-red-400' },
    { match: '/users', icon: 'fa-users', color: 'text-cyan-500 dark:text-cyan-400' },
    { match: '/settings', icon: 'fa-cog', color: 'text-slate-500 dark:text-slate-400' },
    { match: '/import-export', icon: 'fa-file-export', color: 'text-indigo-500 dark:text-indigo-400' },
    { match: '/notifications', icon: 'fa-bell', color: 'text-amber-500 dark:text-amber-400' }
  ];

  function initPageHeadingIcons() {
    const path = window.location.pathname || '';
    const config = pageHeadingIconMap.find(item => path === item.match || path.startsWith(item.match + '/'));
    if (!config) return;

    const heading = document.querySelector('main h1, main h2');
    if (!heading) return;

    // Hindari duplikasi pada heading yang sudah memiliki icon badge, seperti Transfer Gudang.
    if (heading.querySelector('i.fas, i.far, i.fab')) return;
    const headingWrapper = heading.parentElement;
    const previousSibling = headingWrapper ? headingWrapper.previousElementSibling : null;
    if (previousSibling && previousSibling.querySelector && previousSibling.querySelector('i.fas, i.far, i.fab')) return;

    const icon = document.createElement('i');
    icon.className = 'fas ' + config.icon + ' mr-3 ' + config.color;
    icon.setAttribute('aria-hidden', 'true');
    heading.prepend(icon);
  }


  function resetSidebarScroll() {
    const sidebarScrollArea = document.getElementById("sidebarScrollArea");
    if (sidebarScrollArea) {
      sidebarScrollArea.scrollTop = 0;
    }
    try {
      localStorage.removeItem("sidebar_scroll_top");
    } catch (e) {}
  }

  function saveSidebarScroll(element) {
    if (isDashboardPage) {
      try {
        localStorage.removeItem("sidebar_scroll_top");
      } catch (e) {}
    } else {
      try {
        localStorage.setItem("sidebar_scroll_top", element.scrollTop);
      } catch (e) {}
    }
  }

  function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if (sidebar) {
      sidebar.classList.toggle('-translate-x-full');
      if (isDashboardPage && !sidebar.classList.contains('-translate-x-full')) {
        resetSidebarScroll();
      }
    }
    if (overlay) {
      overlay.classList.toggle('hidden');
    }
  }

  function initSidebarScroll() {
    const sidebarScrollArea = document.getElementById("sidebarScrollArea");
    if (!sidebarScrollArea) return;

    if (isDashboardPage) {
      resetSidebarScroll();
    } else {
      const scrollPos = localStorage.getItem("sidebar_scroll_top");
      if (scrollPos !== null) {
        sidebarScrollArea.scrollTop = parseInt(scrollPos, 10);
      } else {
        const activeLink = sidebarScrollArea.querySelector(".sidebar-link.bg-gray-800, .sidebar-link.bg-blue-50");
        if (activeLink) {
          const rect = activeLink.getBoundingClientRect();
          const containerRect = sidebarScrollArea.getBoundingClientRect();
          if (rect.top < containerRect.top || rect.bottom > containerRect.bottom) {
            activeLink.scrollIntoView({ block:"nearest" });
          }
        }
      }
    }
  }

  if (isDashboardPage) {
    resetSidebarScroll();
  }

  document.addEventListener("DOMContentLoaded", function() {
    const dropdownBtn = document.getElementById('userDropdownBtn');
    const dropdownMenu = document.getElementById('userDropdownMenu');
    if (dropdownBtn && dropdownMenu) {
      dropdownBtn.addEventListener('click', function(e) { e.stopPropagation(); dropdownMenu.classList.toggle('hidden'); });
      document.addEventListener('click', function(e) { if (!dropdownMenu.contains(e.target) && !dropdownBtn.contains(e.target)) dropdownMenu.classList.add('hidden'); });
    }

    function sanitizeUiEmptyParentheses(root) {
      if (!root) return;
      const skipTags = new Set(['SCRIPT', 'STYLE', 'NOSCRIPT', 'TEXTAREA', 'INPUT']);
      const cleanNode = function(node) {
        if (node.nodeType === 3) {
          const parent = node.parentElement;
          if (parent && !skipTags.has(parent.tagName)) {
            node.nodeValue = (node.nodeValue || '').replace(/\s*\(\s*\)/g, '');
          }
          return;
        }
        if (node.nodeType !== 1) return;
        if (skipTags.has(node.tagName)) return;
        Array.from(node.childNodes).forEach(cleanNode);
      };
      cleanNode(root);
    }

    sanitizeUiEmptyParentheses(document.body);

    const emptyUiParenthesesObserver = new MutationObserver(function(mutations) {
      mutations.forEach(function(mutation) {
        mutation.addedNodes.forEach(function(node) {
          sanitizeUiEmptyParentheses(node);
        });
      });
    });
    emptyUiParenthesesObserver.observe(document.body, { childList: true, subtree: true });

    // Jalankan ulang setelah seluruh asset/script halaman selesai agar UI dinamis ikut bersih.
    window.addEventListener('load', function() {
      sanitizeUiEmptyParentheses(document.body);
    });
    setTimeout(function() {
      sanitizeUiEmptyParentheses(document.body);
    }, 0);

    document.querySelectorAll('a[href*="/dashboard"]').forEach(function(link) {
      link.addEventListener('click', function() {
        try {
          localStorage.removeItem("sidebar_scroll_top");
        } catch (e) {}
      });
    });

    document.querySelectorAll('#sidebar .sidebar-link').forEach(function(link) {
      link.addEventListener('click', function() {
        if (window.innerWidth < 1024) {
          const sidebar = document.getElementById('sidebar');
          const overlay = document.getElementById('sidebarOverlay');
          if (sidebar && !sidebar.classList.contains('-translate-x-full')) {
            sidebar.classList.add('-translate-x-full');
          }
          if (overlay && !overlay.classList.contains('hidden')) {
            overlay.classList.add('hidden');
          }
        }
      });
    });
  });

  window.addEventListener("load", function() {
    if (isDashboardPage) {
      resetSidebarScroll();
    }
  });

  window.addEventListener("pageshow", function(event) {
    if (isDashboardPage) {
      resetSidebarScroll();
    }
  });

  setTimeout(() => { document.querySelectorAll('.toast').forEach(t => t.remove()); }, 5000);
  </script>
  <script src="<?= url('/assets/js/app-feedback.js') ?>?v=20261001-2"></script>
</body>
</html>
