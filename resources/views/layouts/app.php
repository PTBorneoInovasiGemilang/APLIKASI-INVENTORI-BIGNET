<?php
// Ensure this layout has access to auth user
$userName = $_SESSION['user_name'] ?? 'Guest';
$userRole = $_SESSION['role'] ?? 'GUEST';
$photo_path = \App\Helpers\AuthHelper::getUserPhoto() ?? null;

$primaryColor = setting('primary_color', 'blue');
$sidebarTheme = setting('sidebar_theme', 'dark');
$defaultTheme = setting('default_theme', 'light');

// Prepare color classes based on setting (Disafa modern clean SaaS theme)
$primaryBg = "bg-blue-600 hover:bg-blue-700";
$primaryText = "text-blue-600";
$sidebarBg = 'bg-white text-slate-700 border-r border-slate-200/90 dark:bg-slate-900 dark:text-slate-300 dark:border-slate-800';
$sidebarHover = 'hover:bg-slate-50 hover:text-slate-900 dark:hover:bg-slate-800/60 dark:hover:text-white';
$sidebarActive = 'bg-blue-50/90 text-blue-600 font-semibold rounded-xl dark:bg-blue-950/40 dark:text-blue-400';
$sidebarIcon = 'text-slate-400 dark:text-slate-500';
$sidebarGroupText = 'text-slate-400 dark:text-slate-500 font-bold tracking-wider';
$sidebarBorder = 'border-slate-200/80 dark:border-slate-800';

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

    return isActive($path, $currentPath) ? 'text-blue-600 dark:text-blue-400' : 'text-slate-400 dark:text-slate-500';
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
    $badge = '<span class="ml-auto inline-flex min-w-[20px] h-5 px-1.5 items-center justify-center rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/60 dark:text-blue-300 text-[10px] font-bold leading-none shadow-sm">'.(int)$badgeCount.'</span>';
  }
  return "<a href=\"{$url}\" class=\"sidebar-link flex items-center px-3 py-2 min-h-[40px] rounded-xl text-sm font-medium transition-all {$cls}\">\n        <i class=\"{$icon} w-5 text-center text-sm {$iCls}\"></i>\n        <span class=\"ml-3\">{$label}</span>{$badge}\n      </a>";
}

function renderHeader($label, $sidebarGroupText) {
  return "<div class=\"pt-4 pb-1.5 px-3 text-[10px] font-bold {$sidebarGroupText} uppercase tracking-wider\">{$label}</div>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
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
    /* Standard shell: semua halaman dari seluruh role memakai titik awal horizontal/vertical yang sama (kelipatan 4px). */
    main > .flex-1 > .fade-in {
      width: 100%;
      max-width: none;
      margin-left: auto;
      margin-right: auto;
      padding: 0;
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

    /* ========================================================
       DISAFA MODERN SAAS THEME HARMONIZATION (GLOBAL)
       ======================================================== */
    /* Light mode card defaults */
    html:not(.dark) main .bg-white.shadow,
    html:not(.dark) main .bg-white.shadow-sm,
    html:not(.dark) main .bg-white.rounded-lg,
    html:not(.dark) main .bg-white.rounded-xl,
    html:not(.dark) main .bg-white.rounded-2xl {
      background-color: #ffffff !important;
      border-radius: 1rem !important; /* rounded-2xl */
      border: 1px solid rgba(226, 232, 240, 0.8) !important; /* slate-200/80 */
      box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.05) !important;
    }
    html:not(.dark) main .border-gray-200,
    html:not(.dark) main .border-slate-200 {
      border-color: rgba(226, 232, 240, 0.8) !important;
    }

    /* Dark mode global harmonization */
    html.dark main .bg-white,
    html.dark main [class*="dark:bg-slate-900"],
    html.dark main [class*="dark:bg-gray-800"],
    html.dark main .bg-gray-800,
    html.dark main .bg-slate-900 {
      background-color: #0f172a !important; /* slate-900 */
      border-radius: 1rem !important;
      border: 1px solid #1e293b !important; /* slate-800 */
      color: #f1f5f9 !important;
    }

    /* Sub-containers / inner cards in dark mode */
    html.dark main .bg-slate-50,
    html.dark main .bg-gray-50,
    html.dark main [class*="dark:bg-slate-800"],
    html.dark main [class*="dark:bg-gray-700"] {
      background-color: #1e293b !important; /* slate-800 */
      border-color: #334155 !important; /* slate-700 */
    }

    html.dark main .border-gray-200,
    html.dark main .border-gray-700,
    html.dark main .border-slate-200,
    html.dark main [class*="dark:border-slate-800"],
    html.dark main [class*="dark:border-gray-700"] {
      border-color: #1e293b !important;
    }

    /* Dark mode text colors */
    html.dark main .text-gray-900,
    html.dark main .text-slate-900,
    html.dark main .text-gray-800,
    html.dark main .text-slate-800 {
      color: #f8fafc !important;
    }
    html.dark main .text-gray-700,
    html.dark main .text-slate-700,
    html.dark main .text-gray-600,
    html.dark main .text-slate-600 {
      color: #cbd5e1 !important;
    }
    html.dark main .text-gray-500,
    html.dark main .text-slate-500,
    html.dark main .text-gray-400,
    html.dark main .text-slate-400 {
      color: #94a3b8 !important;
    }

    /* Global Table Container & Styling */
    main .overflow-x-auto {
      border-radius: 1rem !important;
    }
    main table thead {
      background-color: #f8fafc !important; /* slate-50 */
    }
    .dark main table thead {
      background-color: rgba(30, 41, 59, 0.6) !important; /* slate-800/60 */
    }
    main table thead th {
      font-size: 0.72rem !important;
      font-weight: 700 !important;
      text-transform: uppercase !important;
      letter-spacing: 0.05em !important;
      color: #64748b !important; /* slate-500 */
      border-bottom: 1px solid rgba(226, 232, 240, 0.8) !important;
    }
    .dark main table thead th {
      color: #94a3b8 !important; /* slate-400 */
      border-bottom-color: #1e293b !important;
    }
    main table tbody tr {
      transition: background-color 0.15s ease !important;
    }
    main table tbody tr:hover {
      background-color: rgba(248, 250, 252, 0.8) !important;
    }
    .dark main table tbody tr:hover {
      background-color: rgba(30, 41, 59, 0.4) !important;
    }
    main table tbody td {
      border-bottom: 1px solid #f1f5f9 !important; /* slate-100 */
    }
    .dark main table tbody td {
      border-bottom-color: #1e293b !important;
    }

    /* Global Inputs */
    main input[type="text"],
    main input[type="email"],
    main input[type="password"],
    main input[type="number"],
    main input[type="date"],
    main select,
    main textarea {
      border-radius: 0.75rem !important; /* rounded-xl */
      border: 1px solid #cbd5e1 !important; /* slate-300 */
      transition: border-color 0.15s ease, box-shadow 0.15s ease !important;
    }
    .dark main input[type="text"],
    .dark main input[type="email"],
    .dark main input[type="password"],
    .dark main input[type="number"],
    .dark main input[type="date"],
    .dark main select,
    .dark main textarea {
      background-color: #1e293b !important;
      border-color: #334155 !important;
      color: #f8fafc !important;
    }
    main input:focus,
    main select:focus,
    main textarea:focus {
      border-color: #2563eb !important;
      box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15) !important;
    }

    /* Buttons */
    main button.bg-blue-600,
    main a.bg-blue-600 {
      border-radius: 0.75rem !important;
      font-weight: 600 !important;
      box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
    }

    /* ========================================================
       MOBILE UX & COMPACT TABLE OPTIMIZATION (< 640px)
       ======================================================== */
    @media (max-width: 639px) {
      /* 1. Global Compact Table Sizing */
      table {
        font-size: 12px !important;
      }
      table thead th {
        padding: 8px 8px !important;
        font-size: 11px !important;
        line-height: 1.25 !important;
        white-space: nowrap !important;
      }
      table tbody td {
        padding: 8px 8px !important;
        font-size: 12px !important;
        line-height: 1.35 !important;
      }
      table tbody td[colspan] {
        padding: 32px 16px !important;
        text-align: center !important;
        white-space: normal !important;
        word-break: normal !important;
        overflow-wrap: normal !important;
      }
      /* Compact badges di dalam tabel */
      table .inline-flex[class*="rounded-full"],
      table span[class*="rounded-full"] {
        font-size: 10px !important;
        padding: 2px 6px !important;
        line-height: 1.15 !important;
      }
      /* Min-width tabel proporsional di mobile agar hemat jarak swipe */
      table[class*="min-w-[800px]"],
      .overflow-x-auto > table {
        min-width: 580px !important;
      }
      /* Scrollbar tabel halus & jelas di layar sentuh */
      .overflow-x-auto {
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
      }
      .overflow-x-auto::-webkit-scrollbar {
        height: 4px !important;
        display: block !important;
      }
      .overflow-x-auto::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.04) !important;
      }
      .overflow-x-auto::-webkit-scrollbar-thumb {
        background: rgba(156, 163, 175, 0.45) !important;
        border-radius: 4px !important;
      }
      .dark .overflow-x-auto::-webkit-scrollbar-thumb {
        background: rgba(156, 163, 175, 0.3) !important;
      }

      /* Sticky columns No & Aksi tetap rapi & compact di mobile */
      .table-sticky thead th:first-child,
      .table-sticky tbody td:first-child {
        min-width: 32px !important;
        max-width: 44px !important;
        padding-left: 6px !important;
        padding-right: 6px !important;
        text-align: center !important;
      }
      .table-sticky thead th:last-child,
      .table-sticky tbody td:last-child {
        padding-left: 6px !important;
        padding-right: 6px !important;
      }
      [data-label="Aksi"] .inline-grid,
      td:last-child .inline-grid {
        grid-template-columns: 64px 28px !important;
        gap: 4px !important;
      }
      [data-label="Aksi"] a,
      td:last-child a.inline-flex {
        width: 64px !important;
        height: 28px !important;
        font-size: 11px !important;
      }
      [data-label="Aksi"] button[data-actions-toggle],
      td:last-child button[data-actions-toggle] {
        width: 28px !important;
        height: 28px !important;
      }

      /* 2. Padding Container Kartu & Layout Responsif (Kelipatan 4px) */
      main .p-6,
      main .p-8 {
        padding: 16px !important;
      }
      main .px-6 {
        padding-left: 16px !important;
        padding-right: 16px !important;
      }
      main .py-8 {
        padding-top: 20px !important;
        padding-bottom: 20px !important;
      }
      .mb-6 {
        margin-bottom: 16px !important;
      }

      /* 3. Form Input & Dropdown Nyaman Disentuh (Min 40px) */
      input[type="text"], input[type="email"], input[type="password"], input[type="number"], input[type="date"], select, textarea {
        font-size: 14px !important;
        min-height: 40px !important;
      }
      input[type="text"]:not([class*="pl-"]),
      input[type="email"]:not([class*="pl-"]),
      input[type="password"]:not([class*="pl-"]),
      input[type="number"]:not([class*="pl-"]),
      input[type="date"]:not([class*="pl-"]),
      select,
      textarea {
        padding-left: 12px !important;
        padding-right: 12px !important;
      }
      .table-toolbar form > div:not(.flex) {
        width: 100% !important;
      }
      .table-toolbar form select {
        width: 100% !important;
      }

      /* 4. Page Title Ringkas di Layar HP */
      main h1 {
        font-size: 20px !important;
        line-height: 1.3 !important;
      }
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
    .table-sticky thead th:first-child { background-color: #f8fafc; }
    .table-sticky thead th:last-child,
    .table-sticky tbody td:last-child {
      position: sticky;
      right: 0;
      z-index: 10;
      background-color: #ffffff;
      box-shadow: -4px 0 8px -2px rgba(0, 0, 0, 0.06);
    }
    .table-sticky thead th:last-child { background-color: #f8fafc; }
    html.dark .table-sticky thead th:first-child,
    html.dark .table-sticky thead th:last-child { background-color: #1e293b !important; }
    html.dark .table-sticky tbody td:first-child,
    html.dark .table-sticky tbody td:last-child {
      background-color: #0f172a !important;
      box-shadow: -4px 0 8px -2px rgba(0, 0, 0, 0.4);
    }
    html.dark .table-sticky tbody tr:hover td:first-child,
    html.dark .table-sticky tbody tr:hover td:last-child { background-color: #1e293b !important; }
    .table-sticky tbody tr td:only-child { position: static; left: auto; right: auto; box-shadow: none !important; }

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

    /* Table empty state centering & anti vertical-wrap fix */
    table tbody tr td[colspan] {
      text-align: center !important;
      vertical-align: middle !important;
      white-space: normal !important;
      word-break: normal !important;
      overflow-wrap: normal !important;
    }
    table tbody tr td[colspan] div,
    table tbody tr td[colspan] h3,
    table tbody tr td[colspan] p {
      white-space: normal !important;
      word-break: normal !important;
      overflow-wrap: normal !important;
    }
  </style>
</head>
<body class="bg-[#F8FAFC] dark:bg-slate-950 text-slate-800 dark:text-slate-200 fade-in font-sans">

  <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-20 hidden lg:hidden" onclick="toggleSidebar()"></div>

  <aside id="sidebar" class="w-64 h-screen <?= $sidebarBg ?> shadow-sm flex flex-col fixed left-0 top-0 overflow-hidden z-30 transition-transform -translate-x-full lg:translate-x-0">
    <a href="<?= url('/dashboard') ?>" class="px-5 py-4 flex items-center border-b <?= $sidebarBorder ?> min-h-[64px] flex-shrink-0 hover:opacity-90 transition-opacity">
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
        <?= renderMenu('Laporan Posisi Stok', 'fas fa-boxes-stacked', '/reports/stock-position', $currentPath, $sidebarActive, $sidebarHover, $primaryText, $sidebarIcon) ?>
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
    <header class="bg-white dark:bg-slate-900 shadow-sm border-b border-slate-200/90 dark:border-slate-800 min-h-[56px] sm:min-h-[64px] flex items-center justify-between px-2.5 sm:px-4 lg:px-6 sticky top-0 z-20">
      <div class="flex items-center min-w-0 mr-2 sm:mr-4">
        <button class="lg:hidden shrink-0 text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 focus:outline-none mr-2 p-1.5 rounded-lg transition-colors" onclick="toggleSidebar()" aria-label="Toggle menu"><i class="fas fa-bars text-base sm:text-lg"></i></button>
        <div class="min-w-0"><h2 class="text-sm sm:text-base md:text-lg font-bold text-slate-800 dark:text-slate-100 truncate"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h2></div>
      </div>
      <div class="flex items-center space-x-1 sm:space-x-3 shrink-0">
        <button id="togglePageGuideBtn" onclick="togglePageGuide()" class="text-slate-400 hover:text-slate-600 dark:text-slate-300 dark:hover:text-slate-200 focus:outline-none p-1.5 sm:p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" title="Buka/Tutup Panduan Halaman" aria-label="Buka/Tutup Panduan"><i class="fas fa-book-open text-xs sm:text-sm"></i></button>
        <button onclick="document.documentElement.classList.toggle('dark'); localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');" class="text-slate-400 hover:text-slate-600 dark:text-slate-300 dark:hover:text-slate-200 focus:outline-none p-1.5 sm:p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" aria-label="Toggle dark mode"><i class="fas fa-moon text-xs sm:text-sm"></i></button>
        <div class="flex items-center text-right leading-tight mr-1 sm:mr-2">
          <div>
            <p id="serverClockDate" class="text-[10px] sm:text-xs text-slate-400 dark:text-slate-500 font-medium hidden md:block">
              <?= date('l, d M Y') ?>
            </p>
            <p class="text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-200 tabular-nums" aria-label="Waktu server">
              <i class="fas fa-clock text-blue-500 mr-0.5 sm:mr-1 text-[10px] sm:text-xs"></i>
              <span id="serverClockTime"><?= date('H:i:s') ?></span>
            </p>
          </div>
        </div>
        <?php if (isset($_SESSION['user_id'])): ?>
        <?php $unreadCount = \App\Helpers\NotificationHelper::unreadCount((int)$_SESSION['user_id']); ?>
        <a href="<?= url('/notifications') ?>" class="relative text-slate-400 hover:text-slate-600 dark:text-slate-300 dark:hover:text-slate-200 focus:outline-none p-1.5 sm:p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" aria-label="Notifikasi"><i class="fas fa-bell text-xs sm:text-sm"></i><?php if ($unreadCount > 0): ?><span class="absolute -top-0.5 -right-0.5 bg-red-500 text-white text-[9px] sm:text-[10px] font-bold rounded-full min-w-[14px] sm:min-w-[16px] h-3.5 sm:h-4 px-1 flex items-center justify-center"><?= $unreadCount > 99 ? '99+' : $unreadCount ?></span><?php endif; ?></a>
        <?php endif; ?>
        <div class="relative ml-1 sm:ml-2">
          <button id="userDropdownBtn" class="flex items-center space-x-1 sm:space-x-2 focus:outline-none p-1 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
            <?php if ($photo_path): ?><img src="<?= url('/file?path='.urlencode($photo_path)) ?>" class="w-7 h-7 sm:w-8 sm:h-8 rounded-full object-cover ring-2 ring-slate-200 dark:ring-slate-700" alt="Foto"><?php else: ?><div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold text-xs ring-2 ring-slate-200 dark:ring-slate-700"><?= strtoupper(substr($userName, 0, 1)) ?></div><?php endif; ?><i class="fas fa-chevron-down text-[10px] text-slate-400"></i>
          </button>
          <div id="userDropdownMenu" class="hidden absolute right-0 mt-2 w-48 bg-white dark:bg-slate-800 rounded-xl shadow-lg py-1.5 ring-1 ring-black/5 border border-slate-100 dark:border-slate-700 focus:outline-none z-50">
            <div class="px-4 py-2 border-b border-slate-100 dark:border-slate-700"><p class="text-sm font-semibold text-slate-900 dark:text-slate-100 truncate"><?= htmlspecialchars($userName) ?></p><p class="text-xs text-slate-400 dark:text-slate-400 truncate"><?= $roleLabel ?></p></div>
            <a href="<?= url('/profile') ?>" class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700"><i class="fas fa-user-circle mr-2 w-4 text-center"></i> Profil Saya</a>
            <a href="<?= url('/profile?tab=password') ?>" class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700"><i class="fas fa-key mr-2 w-4 text-center"></i> Ubah Password</a>
            <a href="<?= url('/logout') ?>" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30 border-t border-slate-100 dark:border-slate-700 mt-1"><i class="fas fa-sign-out-alt mr-2 w-4 text-center"></i> Keluar</a>
          </div>
        </div>
      </div>
    </header>

    <div id="toastContainer" class="fixed top-4 right-4 z-[110] space-y-2 pointer-events-none">
      <?php if (isset($_SESSION['success'])): ?><div class="toast bg-green-50 border border-green-200 text-green-800 dark:bg-green-900/80 dark:border-green-700 dark:text-green-200 px-4 py-3 rounded-lg shadow-lg flex items-center max-w-sm"><i class="fas fa-check-circle mr-3 text-green-500"></i><span class="text-sm"><?= htmlspecialchars($_SESSION['success']) ?></span></div><?php unset($_SESSION['success']); endif; ?>
      <?php if (isset($_SESSION['error'])): ?><div class="toast bg-red-50 border border-red-200 text-red-800 dark:bg-red-900/80 dark:border-red-700 dark:text-red-200 px-4 py-3 rounded-lg shadow-lg flex items-center max-w-sm"><i class="fas fa-exclamation-circle mr-3 text-red-500"></i><span class="text-sm"><?= htmlspecialchars($_SESSION['error']) ?></span></div><?php unset($_SESSION['error']); endif; ?>
    </div>

    <main class="flex-1 bg-[#F8FAFC] dark:bg-slate-950 p-3 sm:p-4 md:p-6 2xl:p-8 2xl:flex 2xl:gap-6">
      <div class="flex-1 min-w-0 w-full"><div class="fade-in"><?= $content ?></div></div>
      <?php
      if (!isset($guide) && !isset($guide_content)) $guide = \App\Helpers\PageGuideHelper::forPath($currentPath);
      if (isset($guide) || (isset($guide_content) && !empty($guide_content))):
      ?>
      <div id="pageGuideSidebar" class="w-full 2xl:w-80 shrink-0 mt-8 2xl:mt-0 mb-6 2xl:mb-0 transition-all duration-200"><div class="sticky top-6 max-h-[calc(100vh-5rem)] overflow-y-auto scrollbar-thin pr-0.5"><?php require __DIR__ . '/../components/page_guide.php'; ?></div></div>
      <?php endif; ?>
    </main>

    <!-- Global Image Preview Modal -->
    <div id="imagePreviewModal" class="fixed inset-0 z-[120] hidden" role="dialog" aria-modal="true" aria-labelledby="imagePreviewTitle">
      <div id="imagePreviewBackdrop" class="absolute inset-0 bg-slate-900/20 backdrop-blur-[2px]"></div>
      <div class="relative flex min-h-full items-center justify-center p-3 sm:p-6">
        <div class="relative flex max-h-[94vh] w-full max-w-6xl flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-[0_24px_80px_rgba(15,23,42,0.18)]">
          <div class="flex items-center justify-between gap-3 border-b border-gray-200 bg-white px-4 py-3.5 sm:px-5">
            <div class="min-w-0">
              <p id="imagePreviewEyebrow" class="text-[10px] font-semibold uppercase tracking-[0.16em] text-gray-400">Preview Foto</p>
              <h2 id="imagePreviewTitle" class="truncate text-sm font-semibold text-gray-900 sm:text-base">Foto</h2>
            </div>
            <button type="button" id="imagePreviewClose" class="inline-flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-gray-500 transition hover:border-gray-300 hover:bg-gray-100 hover:text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-200" aria-label="Tutup preview">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <div class="flex min-h-0 flex-1 items-center justify-center bg-gray-100 p-2 sm:p-4">
            <div class="flex max-h-[79vh] max-w-full items-center justify-center rounded-xl border border-gray-200 bg-white p-1.5 shadow-sm sm:p-2">
              <img id="imagePreviewImage" src="" alt="" class="max-h-[76vh] max-w-full rounded-lg object-contain" draggable="false">
            </div>
          </div>
          <div class="flex flex-col items-center justify-between gap-2 border-t border-gray-200 bg-white px-4 py-2.5 text-[11px] text-gray-500 sm:flex-row sm:px-5">
            <span><i class="fas fa-expand mr-1 text-gray-400"></i> Klik di luar atau tekan Esc untuk menutup</span>
            <a id="imagePreviewOriginalLink" href="#" target="_blank" rel="noopener" class="hidden flex-shrink-0 font-semibold text-blue-600 hover:text-blue-700 hover:underline">Buka file asli</a>
          </div>
        </div>
      </div>
    </div>

    <footer class="bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800 px-4 py-3"><p class="text-xs text-center text-gray-400 dark:text-gray-600">© 2026 GUDANG PT.BIG - Dibuat oleh Tommy Ryan Dwiputra</p></footer>
  </div>

  <script>
  const isDashboardPage = <?= json_encode($isDashboardPage) ?>;

  (function initGlobalImagePreview() {
    let modal, image, title, originalLink, backdrop, closeButton;

    function closePreview() {
      if (!modal) return;
      modal.classList.add('hidden');
      document.body.classList.remove('overflow-hidden');
      if (image) {
        image.removeAttribute('src');
        image.alt = '';
      }
    }

    function openPreview(src, altText) {
      if (!src) return;
      modal = modal || document.getElementById('imagePreviewModal');
      image = image || document.getElementById('imagePreviewImage');
      title = title || document.getElementById('imagePreviewTitle');
      originalLink = originalLink || document.getElementById('imagePreviewOriginalLink');
      backdrop = backdrop || document.getElementById('imagePreviewBackdrop');
      closeButton = closeButton || document.getElementById('imagePreviewClose');
      if (!modal || !image) return;

      const cleanTitle = altText || 'Foto';
      image.src = src;
      image.alt = cleanTitle;
      if (title) title.textContent = cleanTitle;
      if (originalLink) {
        originalLink.href = src;
        originalLink.classList.remove('hidden');
        originalLink.classList.add('inline-flex');
      }
      modal.classList.remove('hidden');
      document.body.classList.add('overflow-hidden');
      requestAnimationFrame(function () {
        if (closeButton) closeButton.focus();
      });
    }

    document.addEventListener('click', function (event) {
      const link = event.target.closest('[data-image-preview], .js-image-preview');
      if (!link) return;

      const src = link.getAttribute('data-image-src') || link.getAttribute('href');
      const img = link.querySelector('img');
      const altText = link.getAttribute('data-image-title') || (img ? img.getAttribute('alt') : '') || 'Foto';
      if (!src || !/\/((evidence)\/|file\?path=)/i.test(src)) return;

      event.preventDefault();
      openPreview(src, altText);
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') closePreview();
    });

    document.addEventListener('DOMContentLoaded', function () {
      modal = document.getElementById('imagePreviewModal');
      image = document.getElementById('imagePreviewImage');
      backdrop = document.getElementById('imagePreviewBackdrop');
      closeButton = document.getElementById('imagePreviewClose');
      if (closeButton) closeButton.addEventListener('click', closePreview);
      if (backdrop) backdrop.addEventListener('click', closePreview);
      if (image) image.addEventListener('click', function (e) { e.stopPropagation(); });
    });
  })();



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
        // Simpan posisi terakhir tepat sebelum navigasi agar halaman tujuan
        // dapat mengembalikan sidebar ke titik scroll sebelumnya.
        if (!isDashboardPage) {
          try {
            const sidebarScrollArea = document.getElementById('sidebarScrollArea');
            if (sidebarScrollArea) {
              localStorage.setItem('sidebar_scroll_top', String(sidebarScrollArea.scrollTop));
            }
          } catch (e) {}
        }

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

    // Pulihkan posisi sidebar setelah struktur DOM + layout selesai dihitung.
    initSidebarScroll();
    requestAnimationFrame(function() {
      initSidebarScroll();
    });
    setTimeout(function() {
      initSidebarScroll();
    }, 0);
  });

  window.addEventListener("load", function() {
    if (isDashboardPage) {
      resetSidebarScroll();
    }
  });

  window.addEventListener("pageshow", function(event) {
    if (isDashboardPage) {
      resetSidebarScroll();
    } else {
      initSidebarScroll();
      requestAnimationFrame(function() {
        initSidebarScroll();
      });
    }
  });

  setTimeout(() => { document.querySelectorAll('.toast').forEach(t => t.remove()); }, 5000);

  // === TOGGLE PAGE GUIDE (Buka/Tutup Panduan Halaman) ===
  function togglePageGuide() {
    const guide = document.getElementById('pageGuideSidebar');
    if (!guide) return;
    const isHidden = guide.classList.toggle('hidden');
    localStorage.setItem('hide_page_guide', isHidden ? '1' : '0');
    const btn = document.getElementById('togglePageGuideBtn');
    if (btn) {
      btn.classList.toggle('text-blue-600', !isHidden);
      btn.classList.toggle('dark:text-blue-400', !isHidden);
      btn.classList.toggle('bg-blue-50', !isHidden);
      btn.classList.toggle('dark:bg-blue-950/40', !isHidden);
    }
    setTimeout(() => {
      window.dispatchEvent(new Event('resize'));
    }, 150);
  }

  // === DUAL HORIZONTAL SCROLLBAR & EASY TABLE SCROLLING ===
  function initTableScrollbars() {
    document.querySelectorAll('.overflow-x-auto').forEach(container => {
      if (container.dataset.hasTopScroll === 'true') return;
      const table = container.querySelector('table');
      if (!table) return;

      let topBar = container.previousElementSibling;
      const hasTopBar = topBar && topBar.classList.contains('table-top-scrollbar');

      function sync() {
        const needsScroll = table.scrollWidth > (container.clientWidth + 2);
        if (needsScroll) {
          if (!hasTopBar) {
            topBar = document.createElement('div');
            topBar.className = 'table-top-scrollbar overflow-x-auto overflow-y-hidden mb-1.5 rounded-lg bg-slate-100/80 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-800 transition-opacity';
            topBar.style.height = '12px';
            const inner = document.createElement('div');
            inner.style.height = '1px';
            topBar.appendChild(inner);
            container.parentNode.insertBefore(topBar, container);

            let isSyncingTop = false;
            let isSyncingContainer = false;

            topBar.addEventListener('scroll', () => {
              if (!isSyncingTop) {
                isSyncingContainer = true;
                container.scrollLeft = topBar.scrollLeft;
              }
              isSyncingTop = false;
            }, { passive: true });

            container.addEventListener('scroll', () => {
              if (!isSyncingContainer) {
                isSyncingTop = true;
                topBar.scrollLeft = container.scrollLeft;
              }
              isSyncingContainer = false;
            }, { passive: true });
          }
          if (topBar && topBar.firstChild) {
            topBar.firstChild.style.width = table.scrollWidth + 'px';
            topBar.style.display = 'block';
          }
        } else {
          if (topBar && topBar.classList.contains('table-top-scrollbar')) {
            topBar.style.display = 'none';
          }
        }
      }

      sync();
      window.addEventListener('resize', sync);
      container.dataset.hasTopScroll = 'true';
    });
  }

  document.addEventListener('DOMContentLoaded', function() {
    const guide = document.getElementById('pageGuideSidebar');
    if (guide) {
      const userPref = localStorage.getItem('hide_page_guide');
      // Default tertutup pada layar laptop/desktop < 1600px agar tabel mendapatkan 100% lebar layar
      if (userPref === '1' || (userPref === null && window.innerWidth < 1600)) {
        guide.classList.add('hidden');
      } else {
        guide.classList.remove('hidden');
        const btn = document.getElementById('togglePageGuideBtn');
        if (btn) {
          btn.classList.add('text-blue-600', 'dark:text-blue-400', 'bg-blue-50', 'dark:bg-blue-950/40');
        }
      }
    }
    initTableScrollbars();
  });

  // Shift + Wheel horizontal scroll anywhere on tables
  document.addEventListener('wheel', function(e) {
    const container = e.target.closest('.overflow-x-auto');
    if (container && e.deltaY !== 0 && (e.shiftKey || (Math.abs(e.deltaX) === 0 && e.altKey))) {
      container.scrollLeft += e.deltaY;
    }
  }, { passive: true });
  </script>
  <script>
  // Jam ditampilkan berdasarkan waktu server saat halaman dirender, lalu bergerak realtime.
  (function () {
    const serverEpochMs = <?= (int)round(microtime(true) * 1000) ?>;
    const clientStartMs = Date.now();
    const timeEl = document.getElementById('serverClockTime');
    const dateEl = document.getElementById('serverClockDate');

    function updateServerClock() {
      if (!timeEl) return;

      const nowMs = serverEpochMs + (Date.now() - clientStartMs);
      const d = new Date(nowMs);

      const pad = (n) => String(n).padStart(2, '0');
      timeEl.textContent = pad(d.getHours()) + ':' + pad(d.getMinutes()) + ':' + pad(d.getSeconds());

      if (dateEl) {
        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        dateEl.textContent = days[d.getDay()] + ', ' + pad(d.getDate()) + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
      }
    }

    updateServerClock();
    setInterval(updateServerClock, 1000);
  })();

  (function () {
    const sessionCheckUrl = <?= json_encode(url('/session/check')) ?>;
    const sessionTouchUrl = <?= json_encode(url('/session/touch')) ?>;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    let lastUserActivity = Date.now();
    let lastTouchSent = 0;
    let loggingOut = false;

    function redirectToLogin(message) {
      if (loggingOut) return;
      loggingOut = true;
      if (message) {
        try { sessionStorage.setItem('bignet_session_message', message); } catch (e) {}
      }
      window.location.href = <?= json_encode(url('/login')) ?>;
    }

    async function touchSession(force = false) {
      if (document.hidden && !force) return;
      const now = Date.now();
      if (!force && now - lastUserActivity > 30000) return;
      if (!force && now - lastTouchSent < 10000) return;

      lastTouchSent = now;
      try {
        const body = new URLSearchParams();
        body.set('csrf_token', csrfToken);
        const response = await fetch(sessionTouchUrl, {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
          body: body.toString(),
          credentials: 'same-origin',
          cache: 'no-store',
          keepalive: true
        });
        if (response.status === 401) {
          const data = await response.json().catch(() => ({}));
          redirectToLogin(data.message || 'Akun ini telah login di perangkat lain. Sesi Anda telah berakhir.');
        }
      } catch (e) {}
    }

    async function checkSession() {
      if (loggingOut) return;
      try {
        const response = await fetch(sessionCheckUrl, {
          method: 'GET',
          credentials: 'same-origin',
          cache: 'no-store'
        });
        if (response.status === 401) {
          const data = await response.json().catch(() => ({}));
          redirectToLogin(data.message || 'Sesi Anda telah berakhir.');
        }
      } catch (e) {}
    }

    const markActivity = function () {
      lastUserActivity = Date.now();
    };

    // Hanya interaksi nyata pengguna yang dianggap aktivitas.
    // Event "scroll" sengaja tidak dipakai karena script UI dapat memicu scroll programatis.
    ['pointerdown', 'keydown', 'touchstart', 'wheel'].forEach(function (eventName) {
      window.addEventListener(eventName, markActivity, { passive: true });
    });

    setInterval(function () {
      if (Date.now() - lastUserActivity <= 30000) {
        touchSession();
      }
    }, 10000);

    setInterval(checkSession, 15000);

    document.addEventListener('visibilitychange', function () {
      if (!document.hidden) {
        // Kembali ke tab bukan berarti ada aktivitas. Cek status saja.
        checkSession();
      }
    });
  })();
  </script>
<script src="<?= url('/assets/js/app-feedback.js') ?>?v=20261005-1"></script>
</body>
</html>
