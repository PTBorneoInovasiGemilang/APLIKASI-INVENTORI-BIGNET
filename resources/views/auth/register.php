<?php
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$loginLogo = setting('login_logo_path') ?: setting('logo_path');
$loginTitle = setting('login_title', 'GUDANG PT.BIG');
$bgImage = setting('login_background_path');
$primaryColor = setting('primary_color', 'blue');
$defaultTheme = setting('default_theme', 'light');
$favicon = setting('favicon_path') ? url('/file?path=' . urlencode(setting('favicon_path'))) : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Buat Akun Baru - <?= htmlspecialchars($loginTitle) ?></title>
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
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            brand: {
              50: '#f0f9ff',
              100: '#e0f2fe',
              500: '#0ea5e9',
              600: '#0284c7',
              700: '#0369a1',
            }
          }
        }
      }
    }
  </script>
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen flex items-center justify-center p-4">
  <div class="max-w-md w-full space-y-8 bg-white dark:bg-gray-800 p-8 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700">
    <div class="text-center">
      <div class="mx-auto h-12 w-12 bg-brand-500 rounded-lg flex items-center justify-center shadow-lg">
        <i class="fas fa-boxes text-white text-2xl"></i>
      </div>
      <h2 class="mt-6 text-3xl font-extrabold text-gray-900 dark:text-gray-100">
        Daftar Akun Baru
      </h2>
      <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
        Lengkapi data di bawah untuk memproses registrasi
      </p>
    </div>

    <?php if(isset($_SESSION['error'])): ?>
      <div class="bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 p-4 rounded-md">
        <div class="flex">
          <div class="flex-shrink-0">
            <i class="fas fa-exclamation-circle text-red-500"></i>
          </div>
          <div class="ml-3">
            <p class="text-sm text-red-700 dark:text-red-400">
              <?= htmlspecialchars($_SESSION['error']) ?>
            </p>
          </div>
        </div>
      </div>
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Informasi singkat pendaftaran (auth exception: bukan Page Guide penuh) -->
    <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
      <div class="flex items-start gap-3">
        <i class="fas fa-clock text-blue-500 mt-0.5"></i>
        <div class="text-sm text-blue-800 dark:text-blue-200">
          <p class="font-semibold">Akun baru = Pending Approval</p>
          <p class="mt-1 text-blue-700 dark:text-blue-300">Setelah mendaftar, akun Anda akan ditinjau Superadmin terlebih dahulu sebelum bisa digunakan untuk login.</p>
        </div>
      </div>
    </div>

    <form class="mt-8 space-y-6" action="<?= url('/register') ?>" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
      
      <div class="space-y-4">
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Lengkap</label>
          <input id="name" name="name" type="text" required class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-brand-500 focus:border-brand-500 sm:text-sm dark:bg-gray-700 dark:text-white mt-1">
        </div>

        <div>
          <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Alamat Email</label>
          <input id="email" name="email" type="email" autocomplete="email" required class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-brand-500 focus:border-brand-500 sm:text-sm dark:bg-gray-700 dark:text-white mt-1">
        </div>

        <div>
          <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            Nomor HP <span class="text-red-500">*</span>
          </label>
          <input id="phone" name="phone" type="tel" inputmode="numeric" autocomplete="tel"
            required maxlength="15" pattern="[0-9]{10,15}"
            placeholder="Contoh: 081234567890"
            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
            class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-brand-500 focus:border-brand-500 sm:text-sm dark:bg-gray-700 dark:text-white mt-1">
          <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Wajib diisi, hanya angka 10–15 digit.</p>
        </div>

        <div>
          <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role Pekerjaan</label>
          <select id="role" name="role" required class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-brand-500 focus:border-brand-500 sm:text-sm dark:bg-gray-700 dark:text-white mt-1">
            <option value="">Pilih Role...</option>
            <option value="TEKNISI_NOC"><?= \App\Helpers\UIHelper::roleLabel('TEKNISI_NOC') ?></option>
            <option value="ADMIN"><?= \App\Helpers\UIHelper::roleLabel('ADMIN') ?></option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Foto Profil (Wajib untuk validasi)</label>
          <div class="flex items-center gap-4">
            <div class="h-16 w-16 bg-gray-200 dark:bg-gray-600 rounded-full flex items-center justify-center overflow-hidden shrink-0 border border-gray-300 dark:border-gray-500">
              <i id="photoPlaceholder" class="fas fa-user text-gray-400 dark:text-gray-400 text-2xl"></i>
              <img id="photoPreview" class="h-16 w-16 object-cover hidden" alt="Preview">
            </div>
            <input type="file" name="photo" id="photo" accept="image/jpeg, image/png, image/webp" required class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900/30 dark:file:text-blue-400" onchange="previewImage(this)">
          </div>
        </div>

        <div>
          <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
          <input id="password" name="password" type="password" required class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-brand-500 focus:border-brand-500 sm:text-sm dark:bg-gray-700 dark:text-white mt-1" minlength="6">
        </div>
        
        <div>
          <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Konfirmasi Password</label>
          <input id="password_confirmation" name="password_confirmation" type="password" required class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-brand-500 focus:border-brand-500 sm:text-sm dark:bg-gray-700 dark:text-white mt-1" minlength="6">
        </div>
      </div>

      <div>
        <button type="submit" class="group relative w-full flex justify-center py-2.5 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-brand-600 hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 shadow-md transition-all">
          Daftar Sekarang
        </button>
      </div>
      
      <div class="text-center mt-4">
        <a href="<?= url('/login') ?>" class="font-medium text-brand-600 hover:text-brand-500 dark:text-brand-400 text-sm">
          Sudah punya akun? Masuk di sini
        </a>
      </div>
    </form>
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
</body>
</html>
