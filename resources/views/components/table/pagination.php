<?php
/**
 * REUSABLE PAGINATION COMPONENT
 * 
 * Variables to pass before requiring this file:
 * $page : int (Current page number)
 * $totalPages : int (Total number of pages)
 * $totalItems : int (Total number of items)
 * $paginationBaseUrl : string (Base URL for pagination links, e.g. url('/categories'))
 */

if (!isset($page) || !isset($totalPages) || !isset($totalItems) || !isset($paginationBaseUrl)) {
  return; // Don't render if variables are missing
}
?>
<?php if ($totalPages > 1): ?>
<div class="px-4 py-3 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row items-center justify-between gap-4">
  <div class="text-sm text-gray-700 dark:text-gray-400">
    Menampilkan halaman <span class="font-bold text-gray-900 dark:text-gray-100"><?= $page ?></span> dari <span class="font-bold text-gray-900 dark:text-gray-100"><?= $totalPages ?></span> (Total: <span class="font-bold text-gray-900 dark:text-gray-100"><?= number_format($totalItems) ?></span> data)
  </div>
  <div class="flex flex-wrap justify-center gap-1 sm:gap-1.5">
    <?php 
      $queryParams = $_GET;
      unset($queryParams['page']);
      $queryString = http_build_query($queryParams);
      $baseUrl = $paginationBaseUrl . ($queryString ? '?' . $queryString . '&' : '?');
    ?>
    <?php if ($page > 1): ?>
      <a href="<?= $baseUrl . 'page=' . ($page - 1) ?>" class="px-3 py-1.5 sm:py-1 min-h-[36px] sm:min-h-0 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-white rounded border border-gray-300 dark:border-gray-600 text-sm flex items-center transition-colors">
        <i class="fas fa-chevron-left text-xs mr-1"></i> Prev
      </a>
    <?php endif; ?>
    
    <?php 
      $startPage = max(1, $page - 2);
      $endPage = min($totalPages, $page + 2);
      for ($i = $startPage; $i <= $endPage; $i++): 
    ?>
      <a href="<?= $baseUrl . 'page=' . $i ?>" class="px-3 py-1.5 sm:py-1 min-h-[36px] min-w-[36px] flex items-center justify-center <?= $i === $page ? 'bg-blue-600 text-white border-blue-600 font-bold' : 'bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-white border-gray-300 dark:border-gray-600' ?> rounded border text-sm transition-colors"><?= $i ?></a>
    <?php endfor; ?>
    
    <?php if ($page < $totalPages): ?>
      <a href="<?= $baseUrl . 'page=' . ($page + 1) ?>" class="px-3 py-1.5 sm:py-1 min-h-[36px] sm:min-h-0 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-white rounded border border-gray-300 dark:border-gray-600 text-sm flex items-center transition-colors">
        Next <i class="fas fa-chevron-right text-xs ml-1"></i>
      </a>
    <?php endif; ?>
  </div>
</div>
<?php else: ?>
<!-- Show simple info if only 1 page -->
<?php if ($totalItems > 0): ?>
<div class="px-4 py-3 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 flex items-center justify-start">
  <div class="text-sm text-gray-700 dark:text-gray-400">
    Menampilkan total <span class="font-bold text-gray-900 dark:text-gray-100"><?= number_format($totalItems) ?></span> data
  </div>
</div>
<?php endif; ?>
<?php endif; ?>
