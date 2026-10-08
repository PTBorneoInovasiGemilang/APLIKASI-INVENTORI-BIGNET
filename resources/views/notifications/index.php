<?php
$pageTitle = 'Notifikasi';
ob_start();
$cleanNotificationText = static function ($value) {
  return trim((string)preg_replace('/\\s*\\(\\s*\\)/u', '', (string)$value));
};

$typeIcon = [
  'success' => ['fa-check-circle', 'text-emerald-500'],
  'warning' => ['fa-exclamation-triangle', 'text-amber-500'],
  'danger' => ['fa-exclamation-circle', 'text-red-500'],
  'info' => ['fa-info-circle', 'text-blue-500'],
];
?>
<div class="space-y-6">
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
      <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Pusat Notifikasi</h1>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Pemberitahuan aktivitas, persetujuan, dan transaksi akun Anda.</p>
    </div>
    <?php if (!empty($notifications)): ?>
      <form action="<?= url('/notifications/read-all') ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <button type="submit" class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-xl shadow-sm text-xs transition-all w-full sm:w-auto">
          <i class="fas fa-check-double text-xs"></i> Tandai Semua Dibaca
        </button>
      </form>
    <?php endif; ?>
  </div>

  <?php if (isset($_SESSION['success'])): ?>
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-200 shadow-sm">
      <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
    </div>
  <?php endif; ?>
  <?php if (isset($_SESSION['error'])): ?>
    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-200 shadow-sm">
      <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
    </div>
  <?php endif; ?>

  <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-800 overflow-hidden">
    <?php if (empty($notifications)): ?>
      <?php
      $emptyBare = true;
      $emptyIcon = 'fa-bell-slash';
      $emptyIconColor = 'text-slate-400 dark:text-slate-500';
      $emptyMessage = 'Tidak ada notifikasi.';
      $emptySubMessage = 'Pemberitahuan penting untuk Anda akan muncul di sini.';
      require __DIR__ . '/../components/table/empty_state.php';
      unset($emptyBare);
      ?>
    <?php else: ?>
      <ul class="divide-y divide-slate-100 dark:divide-slate-800">
        <?php foreach ($notifications as $n): ?>
          <?php $icon = $typeIcon[$n['type']] ?? $typeIcon['info']; ?>
          <li class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors <?= $n['read_at'] ? '' : 'bg-blue-50/40 dark:bg-blue-950/20' ?>">
            <div class="px-5 py-4 flex items-start gap-4">
              <div class="shrink-0 mt-0.5">
                <i class="fas <?= $icon[0] ?> <?= $icon[1] ?> text-lg"></i>
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2">
                  <p class="text-sm font-bold text-slate-900 dark:text-slate-100">
                    <?= htmlspecialchars($n['title']) ?>
                    <?php if (!$n['read_at']): ?>
                      <span class="ml-1.5 inline-block w-2 h-2 rounded-full bg-blue-600 align-middle" title="Belum dibaca"></span>
                    <?php endif; ?>
                  </p>
                  <span class="text-xs text-slate-400 dark:text-slate-500 whitespace-nowrap"><?= date('d M Y, H:i', strtotime($n['created_at'])) ?></span>
                </div>
                <?php if ($n['message']): ?>
                  <p class="mt-1 text-xs text-slate-600 dark:text-slate-400"><?= htmlspecialchars($cleanNotificationText($n['message'])) ?></p>
                <?php endif; ?>
                <div class="mt-2.5 flex items-center gap-3">
                  <?php if ($n['source_route']): ?>
                    <a href="<?= url($n['source_route']) ?>" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
                      <span>Buka Halaman</span> <i class="fas fa-arrow-right text-[10px]"></i>
                    </a>
                  <?php endif; ?>
                  <?php if (!$n['read_at']): ?>
                    <form action="<?= url('/notifications/read') ?>" method="POST" class="inline">
                      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                      <input type="hidden" name="id" value="<?= (int)$n['id'] ?>">
                      <button type="submit" class="text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                        Tandai sudah dibaca
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

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
