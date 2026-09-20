<?php
$pageTitle = 'Ubah Pengguna';
ob_start();
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="mb-6 flex items-center">
    <a href="<?= url('/users') ?>" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 mr-4">
      <i class="fas fa-arrow-left"></i>
    </a>
    <div>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Ubah Pengguna</h1>
      <p class="text-sm text-gray-600 dark:text-gray-400">Perbarui informasi dan akses pengguna.</p>
    </div>
  </div>

  <?php if (isset($_SESSION['error'])): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
      <span class="block sm:inline"><?= htmlspecialchars($_SESSION['error']) ?></span>
      <?php unset($_SESSION['error']); ?>
    </div>
  <?php endif; ?>

  <div class="overflow-x-auto bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700">
    <form action="<?= url('/users/update/' . $user['id']) ?>" method="POST" enctype="multipart/form-data" class="p-6">
      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="md:col-span-2 border-b dark:border-gray-700 pb-4 mb-2">
          <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Foto Profil</h3>
          <div class="flex items-center space-x-4">
            <div class="h-20 w-20 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center overflow-hidden" id="photoPreviewContainer">
              <?php if (!empty($user['photo_path'])): ?>
                <img id="photoPreview" src="<?= url('/file?path=' . urlencode($user['photo_path'])) ?>" class="h-full w-full object-cover" alt="Preview">
                <i class="fas fa-user text-gray-400 text-3xl hidden" id="photoPlaceholder"></i>
              <?php else: ?>
                <img id="photoPreview" class="h-full w-full object-cover hidden" alt="Preview">
                <i class="fas fa-user text-gray-400 text-3xl" id="photoPlaceholder"></i>
              <?php endif; ?>
            </div>
            <div>
              <input type="file" name="photo" id="photo" accept="image/jpeg,image/png,image/webp" class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900/30 dark:file:text-blue-400" onchange="previewImage(this)">
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Biarkan kosong jika tidak ingin mengubah foto. Maks: 2MB.</p>
            </div>
          </div>
        </div>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Lengkap <span class="text-red-500">*</span></label>
          <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Alamat Email <span class="text-red-500">*</span></label>
          <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nomor Telepon</label>
          <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Peran <span class="text-red-500">*</span></label>
          <select name="role" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
            <option value="TEKNISI_NOC" <?= $user['role'] === 'TEKNISI_NOC' ? 'selected' : '' ?>><?= \App\Helpers\UIHelper::roleLabel('TEKNISI_NOC') ?></option>
            <option value="ADMIN" <?= $user['role'] === 'ADMIN' ? 'selected' : '' ?>><?= \App\Helpers\UIHelper::roleLabel('ADMIN') ?></option>
            <option value="SUPERADMIN" <?= $user['role'] === 'SUPERADMIN' ? 'selected' : '' ?>><?= \App\Helpers\UIHelper::roleLabel('SUPERADMIN') ?></option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status Akun</label>
          <select name="status" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
            <option value="1" <?= (int)$user['is_active'] === 1 ? 'selected' : '' ?>>Aktif</option>
            <option value="0" <?= (int)$user['is_active'] !== 1 ? 'selected' : '' ?>>Nonaktif</option>
          </select>
          <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Status hanya berubah jika Anda mengubah field ini. Perubahan nama/email/role/password tidak memengaruhi status akun.</p>
        </div>
        
        <div class="md:col-span-2 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
          <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">Reset Password</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password Baru</label>
              <input type="password" name="password" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Kosongkan untuk mempertahankan password saat ini">
              <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Hanya isi jika ingin mengubah password user ini.</p>
            </div>
          </div>
        </div>

      </div>

      <div class="flex justify-end gap-4 mt-6 border-t border-gray-200 dark:border-gray-700 pt-6">
        <a href="<?= url('/users') ?>" class="bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 py-2 px-4 rounded-md shadow-sm text-sm font-medium transition-colors">Batal</a>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-6 rounded-md shadow-sm text-sm font-medium transition-colors">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

<script>
function previewImage(input) {
  const preview = document.getElementById('photoPreview');
  const placeholder = document.getElementById('photoPlaceholder');
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      preview.src = e.target.result;
      preview.classList.remove('hidden');
      placeholder.classList.add('hidden');
    }
    reader.readAsDataURL(input.files[0]);
  }
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
