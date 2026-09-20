<?php
$pageTitle = 'Profil Saya';
ob_start();
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<div class="max-w-4xl mx-auto">
  <?php if (isset($_SESSION['success'])): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
      <span class="block sm:inline"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></span>
    </div>
  <?php endif; ?>
  <?php if (isset($_SESSION['error'])): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
      <span class="block sm:inline"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
    </div>
  <?php endif; ?>

  <div class="overflow-x-auto bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700">
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center space-x-6">
        <div class="relative">
          <?php if ($profile['photo_path']): ?>
            <img src="<?= url('/file?path=' . urlencode($profile['photo_path'])) ?>" alt="Profile" class="w-32 h-32 rounded-full object-cover border-4 border-gray-200 dark:border-gray-600 shadow-sm">
          <?php else: ?>
            <div class="w-32 h-32 rounded-full bg-blue-500 flex items-center justify-center font-bold text-white text-4xl shadow-sm border-4 border-gray-200 dark:border-gray-600">
              <?= strtoupper(substr($profile['name'], 0, 1)) ?>
            </div>
          <?php endif; ?>
        </div>
        <div>
          <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($profile['name']) ?></h2>
          <p class="text-gray-500 dark:text-gray-400 font-medium"><?= htmlspecialchars($profile['role']) ?></p>
          <p class="text-gray-500 dark:text-gray-400 text-sm mt-1"><?= htmlspecialchars($profile['email']) ?></p>
        </div>
      </div>
      
      <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
        <form action="<?= url('/profile/photo') ?>" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row items-center gap-4">
          <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
          <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" required class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-gray-700 dark:file:text-gray-300">
          <button type="submit" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow whitespace-nowrap transition-colors">
            <i class="fas fa-upload mr-2"></i> Unggah Foto
          </button>
        </form>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Maks 2MB. Format JPG, PNG, WEBP.</p>
      </div>
    </div>
    
    <div class="p-6 bg-gray-50 dark:bg-gray-800/50">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Informasi Profil</h3>
      <form action="<?= url('/profile/update') ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Lengkap</label>
            <input type="text" name="name" value="<?= htmlspecialchars($profile['name']) ?>" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email (Tidak dapat diubah)</label>
            <input type="email" value="<?= htmlspecialchars($profile['email']) ?>" disabled class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 dark:bg-gray-600 dark:border-gray-500 dark:text-gray-300 shadow-sm cursor-not-allowed">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nomor Telepon</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($profile['phone'] ?? '') ?>" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
          </div>
        </div>

        <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Ubah Password</h3>
          <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Kosongkan jika tidak ingin mengubah password. Password minimal 6 karakter.</p>
          
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password Saat Ini <span class="text-red-500">*</span></label>
              <input type="password" name="current_password" autocomplete="current-password" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password Baru <span class="text-red-500">*</span></label>
              <input type="password" name="password" minlength="6" autocomplete="new-password" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Konfirmasi Password Baru <span class="text-red-500">*</span></label>
              <input type="password" name="password_confirmation" minlength="6" autocomplete="new-password" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
          </div>
        </div>

        <div class="mt-6 flex justify-end">
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow transition-colors">
            Save Changes
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
