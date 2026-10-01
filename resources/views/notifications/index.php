<?php
$pageTitle = 'Notifikasi';
ob_start();
$cleanNotificationText = static function ($value) {
  return trim((string)preg_replace('/\\s*\\(\\s*\\)/u', '', (string)$value));
};

$typeIcon = [
  'success' => ['fa-check-circle', 'text-green-500'],
  'warning' => ['fa-exclamation-triangle', 'text-amber-500'],
  'danger' => ['fa-exclamation-circle', 'text-red-500'],
  'info' => ['fa-info-circle', 'text-blue-500'],
];
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Notifikasi</h1>
      <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Pusat notifikasi pribadi Anda.</p>
    </div>
    <?php if (!empty($notifications)): ?>
      <form action="<?= url('/notifications/read-all') ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold transition-colors flex items-center gap-2">
          <i class="fas fa-check-double"></i> Tandai Semua Dibaca
        </button>
      </form>
    <?php endif; ?>
  </div>

  <?php if (isset($_SESSION['success'])): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
      <span class="block sm:inline"><?= htmlspecialchars($_SESSION['success']) ?></span>
      <?php unset($_SESSION['success']); ?>
    </div>
  <?php endif; ?>
  <?php if (isset($_SESSION['error'])): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
      <span class="block sm:inline"><?= htmlspecialchars($_SESSION['error']) ?></span>
      <?php unset($_SESSION['error']); ?>
    </div>
  <?php endif; ?>

  <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <?php if (empty($notifications)): ?>
      <?php
      $emptyBare = true;
      $emptyIcon = 'fa-bell-slash';
      $emptyIconColor = 'text-gray-400 dark:text-gray-500';
      $emptyMessage = 'Tidak ada notifikasi.';
      $emptySubMessage = 'Pembaruan penting untuk Anda akan muncul di sini.';
      require __DIR__ . '/../components/table/empty_state.php';
      unset($emptyBare);
      ?>
    <?php else: ?>
      <ul class="divide-y divide-gray-200 dark:divide-gray-700">
        <?php foreach ($notifications as $n): ?>
          <?php $icon = $typeIcon[$n['type']] ?? $typeIcon['info']; ?>
          <li class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors <?= $n['read_at'] ? '' : 'bg-blue-50/50 dark:bg-blue-900/10' ?>">
            <div class="px-4 py-3 flex items-start gap-4">
              <div class="flex-shrink-0 mt-0.5">
                <i class="fas <?= $icon[0] ?> <?= $icon[1] ?> text-lg"></i>
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2">
                  <p class="text-sm font-semibold text-gray-900 dark:text-white">
                    <?= htmlspecialchars($n['title']) ?>
                    <?php if (!$n['read_at']): ?>
                      <span class="ml-2 inline-block w-2 h-2 rounded-full bg-blue-500 align-middle" title="Belum dibaca"></span>
                    <?php endif; ?>
                  </p>
                  <span class="text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap"><?= date('d M Y H:i', strtotime($n['created_at'])) ?></span>
                </div>
                <?php if ($n['message']): ?>
                  <p class="mt-1 text-sm text-gray-600 dark:text-gray-400"><?= htmlspecialchars($cleanNotificationText($n['message'])) ?></p>
                <?php endif; ?>
                <div class="mt-2 flex items-center gap-3">
                  <?php if ($n['source_route']): ?>
                    <a href="<?= url($n['source_route']) ?>" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">
                      <i class="fas fa-arrow-right mr-1"></i> Buka
                    </a>
                  <?php endif; ?>
                  <?php if (!$n['read_at']): ?>
                    <form action="<?= url('/notifications/read') ?>" method="POST" class="inline">
                      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                      <input type="hidden" name="id" value="<?= (int)$n['id'] ?>">
                      <button type="submit" class="text-xs text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400">
                        Tandai dibaca
                      </button>
                    </form>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/app.php'; ?>
