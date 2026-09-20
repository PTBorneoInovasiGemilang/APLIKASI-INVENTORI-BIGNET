<?php
$pageTitle = 'Pengaturan Sistem';
ob_start();
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<div class="max-w-5xl mx-auto">
  <div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Pengaturan Sistem</h1>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kelola pengaturan global aplikasi dan branding.</p>
  </div>

  <?php if (isset($_SESSION['success'])): ?>
    <div class="bg-green-50 border-l-4 border-green-500 text-green-700 dark:bg-green-900/30 dark:border-green-600 dark:text-green-300 px-4 py-3 rounded-r mb-4 flex items-center">
      <i class="fas fa-check-circle mr-2"></i>
      <span><?= htmlspecialchars($_SESSION['success']) ?></span>
      <?php unset($_SESSION['success']); ?>
    </div>
  <?php endif; ?>
  <?php if (isset($_SESSION['error'])): ?>
    <div class="bg-red-50 border-l-4 border-red-500 text-red-700 dark:bg-red-900/30 dark:border-red-600 dark:text-red-300 px-4 py-3 rounded-r mb-4 flex items-center">
      <i class="fas fa-exclamation-circle mr-2"></i>
      <span><?= htmlspecialchars($_SESSION['error']) ?></span>
      <?php unset($_SESSION['error']); ?>
    </div>
  <?php endif; ?>

  <div class="overflow-x-auto bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700">
    <div class="border-b border-gray-200 dark:border-gray-700">
      <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
        <a href="<?= url('/settings?tab=general') ?>" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm <?= $tab === 'general' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:border-gray-700 dark:text-gray-400' ?>">
          <i class="fas fa-cog mr-1"></i> Umum
        </a>
        <a href="<?= url('/settings?tab=branding') ?>" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm <?= $tab === 'branding' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:border-gray-700 dark:text-gray-400' ?>">
          <i class="fas fa-paint-brush mr-1"></i> Branding & Tampilan
        </a>
        <a href="<?= url('/settings?tab=security') ?>" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm <?= $tab === 'security' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:border-gray-700 dark:text-gray-400' ?>">
          <i class="fas fa-lock mr-1"></i> Keamanan
        </a>
      </nav>
    </div>
    <div class="p-6">
      <form action="<?= url('/settings/update') ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
        <input type="hidden" name="tab" value="<?= htmlspecialchars($tab) ?>">

        <?php if ($tab === 'general'): ?>
          <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Aplikasi <span class="text-red-500">*</span></label>
                <input type="text" name="app_name" value="<?= htmlspecialchars($settings['app_name']) ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white py-2 px-3" required>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Singkat <span class="text-red-500">*</span></label>
                <input type="text" name="app_short_name" value="<?= htmlspecialchars($settings['app_short_name']) ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white py-2 px-3" required>
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Perusahaan <span class="text-red-500">*</span></label>
              <input type="text" name="company_name" value="<?= htmlspecialchars($settings['company_name']) ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white py-2 px-3" required>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Teks Footer</label>
              <input type="text" name="footer_text" value="<?= htmlspecialchars($settings['footer_text']) ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white py-2 px-3" placeholder="© 2026 PT. BIG. All rights reserved.">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Judul Login</label>
                <input type="text" name="login_title" value="<?= htmlspecialchars($settings['login_title']) ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white py-2 px-3">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Subjudul Login</label>
                <input type="text" name="login_subtitle" value="<?= htmlspecialchars($settings['login_subtitle']) ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white py-2 px-3">
              </div>
            </div>
          </div>
        <?php elseif ($tab === 'branding'): ?>
          <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Warna Utama</label>
                <select name="primary_color" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white py-2 px-3">
                  <?php foreach (['blue' => 'Biru', 'slate' => 'Abu-abu', 'indigo' => 'Indigo', 'emerald' => 'Hijau', 'red' => 'Merah', 'amber' => 'Kuning'] as $val => $label): ?>
                    <option value="<?= $val ?>" <?= $settings['primary_color'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tema Sidebar</label>
                <select name="sidebar_theme" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white py-2 px-3">
                  <option value="dark" <?= $settings['sidebar_theme'] === 'dark' ? 'selected' : '' ?>>Gelap</option>
                  <option value="light" <?= $settings['sidebar_theme'] === 'light' ? 'selected' : '' ?>>Terang</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Mode Default</label>
                <select name="default_theme" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white py-2 px-3">
                  <option value="light" <?= $settings['default_theme'] === 'light' ? 'selected' : '' ?>>Terang</option>
                  <option value="dark" <?= $settings['default_theme'] === 'dark' ? 'selected' : '' ?>>Gelap</option>
                </select>
              </div>
            </div>

            <hr class="border-gray-200 dark:border-gray-700">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Logo Utama</label>
                <?php if ($logo = setting('logo_path')): ?>
                  <div class="mt-2 mb-2 p-2 bg-gray-50 dark:bg-gray-700 rounded inline-block">
                    <img src="<?= url('/file?path=' . urlencode($logo)) ?>" alt="Logo" class="h-12 object-contain">
                  </div>
                <?php endif; ?>
                <input type="file" name="logo" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900 dark:file:text-blue-300">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Favicon</label>
                <input type="file" name="favicon" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900 dark:file:text-blue-300">
              </div>
            </div>
          </div>
        <?php elseif ($tab === 'security'): ?>
          <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Batas Waktu Sesi (Menit) <span class="text-red-500">*</span></label>
                <input type="number" name="session_timeout_minutes" value="<?= htmlspecialchars($settings['session_timeout_minutes'] ?? '30') ?>" min="1" max="1440" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white py-2 px-3" required>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Pengguna akan otomatis logout jika tidak ada aktivitas selama waktu ini. Direkomendasikan 30-120 menit.</p>
              </div>
            </div>
          </div>
        <?php endif; ?>

        <div class="mt-8 flex justify-end border-t border-gray-200 dark:border-gray-700 pt-4">
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-6 rounded-lg shadow-sm text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            <i class="fas fa-save mr-2"></i>Simpan Perubahan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
