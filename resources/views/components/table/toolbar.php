<?php
/**
 * REUSABLE TABLE TOOLBAR COMPONENT
 * 
 * Variables to pass before requiring this file:
 * $toolbarAction : string (URL for form action)
 * $searchPlaceholder : string (Optional, default: 'Cari...')
 * $extraFilters : string (Optional, HTML for extra filters like status selects)
 * $resetUrl : string (Optional, default: $toolbarAction)
 */

$searchPlaceholder = $searchPlaceholder ?? 'Cari...';
$resetUrl = $resetUrl ?? $toolbarAction;
?>
<div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6 p-4 border border-gray-200 dark:border-gray-700">
  <form method="GET" action="<?= htmlspecialchars($toolbarAction) ?>" class="flex flex-wrap gap-4 items-end">
    <div class="w-full sm:w-auto">
      <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Pencarian</label>
      <input type="text" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" placeholder="<?= htmlspecialchars($searchPlaceholder) ?>" class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-full sm:w-64 py-2 px-3 text-sm text-gray-700 dark:text-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    
    <?php if (isset($extraFilters)) echo $extraFilters; ?>
    
    <div class="flex gap-2 w-full sm:w-auto">
      <button type="submit" class="flex-1 sm:flex-initial bg-gray-800 hover:bg-gray-900 text-white font-bold py-2 px-4 rounded-lg shadow transition-colors text-sm flex items-center justify-center min-w-[90px]" onclick="this.innerHTML='<i class=\'fas fa-spinner fa-spin mr-2\'></i>...';">
        <i class="fas fa-search mr-2"></i> Cari
      </button>
      <a href="<?= htmlspecialchars($resetUrl) ?>" class="flex-1 sm:flex-initial bg-gray-200 hover:bg-gray-300 text-gray-800 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200 font-bold py-2 px-4 rounded-lg shadow transition-colors text-sm flex items-center justify-center">
        <i class="fas fa-undo mr-2"></i> Reset
      </a>
    </div>
  </form>
</div>
