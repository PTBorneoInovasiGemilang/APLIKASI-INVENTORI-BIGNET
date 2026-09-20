<?php
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Branding Login mengikuti Pengaturan Sistem (Superadmin):
// Nama Perusahaan = Pengaturan Sistem -> Umum (company_name)
// Logo Utama   = Pengaturan Sistem -> Branding & Tampilan (logo_path)
$loginTitle = setting('company_name', 'PT. BIG');
$appName = setting('app_name', 'GUDANG PT.BIG');

$loginLogo = '';
$loginLogoFile = setting('logo_path');
if ($loginLogoFile && is_file(__DIR__ . '/../../../storage/private_uploads/' . basename($loginLogoFile))) {
  $loginLogo = url('/logo');
}

$loginSubtitle = setting('login_subtitle', 'Kelola inventaris, stok, nomor seri, dan seluruh operasional gudang dalam satu sistem.');
$bgImage = setting('login_background_path');
$primaryColor = setting('primary_color', 'blue');
$defaultTheme = setting('default_theme', 'light');

$favicon = '';
$faviconFile = setting('favicon_path');
if ($faviconFile && is_file(__DIR__ . '/../../../storage/private_uploads/' . basename($faviconFile))) {
  $favicon = url('/favicon');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Masuk - <?= htmlspecialchars($loginTitle) ?></title>
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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: { extend: { colors: { primary: tailwind.colors.<?= $primaryColor ?> } } }
    }
  </script>
  <style>
    .fade-in { animation: fadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }
  </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen text-gray-800 dark:text-gray-200 antialiased selection:bg-blue-500 selection:text-white">
  <div class="flex min-h-screen w-full">
    
    <!-- KIRI: Branding & Informasi (Desktop) -->
    <div class="hidden lg:flex lg:w-1/2 flex-col justify-between p-12 bg-slate-900 text-white relative overflow-hidden" 
      <?= $bgImage ? 'style="background-image: linear-gradient(to right, rgba(15, 23, 42, 0.92), rgba(15, 23, 42, 0.75)), url(\''.url('/file?path='.urlencode($bgImage)).'\'); background-size: cover; background-position: center;"' : 'style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #2563eb 100%);"' ?>>
      
      <!-- Ornamen Dekoratif -->
      <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-blue-500 opacity-10 rounded-full blur-3xl"></div>
      <div class="absolute bottom-[-10%] right-[-10%] w-96 h-96 bg-indigo-500 opacity-20 rounded-full blur-3xl"></div>

      <div class="relative z-10 flex items-center gap-3">
        <?php if ($loginLogo): ?>
          <img src="<?= $loginLogo ?>" alt="Logo" class="h-10 object-contain bg-white dark:bg-gray-800/10 p-1.5 rounded-lg border border-white/20">
        <?php else: ?>
          <div class="h-10 w-10 bg-blue-600 text-white rounded-lg flex items-center justify-center font-bold text-xl shadow-md"><i class="fas fa-boxes-stacked"></i></div>
        <?php endif; ?>
        <div>
          <span class="text-lg font-bold tracking-wider block leading-none"><?= htmlspecialchars($loginTitle) ?></span>
          <span class="text-[11px] text-blue-200 uppercase tracking-widest font-medium">Sistem Inventori</span>
        </div>
      </div>

      <div class="relative z-10 my-auto max-w-lg">
        <h1 class="text-4xl md:text-5xl font-extrabold mb-6 leading-tight tracking-tight">Selamat Datang di <br><span class="text-blue-400"><?= htmlspecialchars($appName) ?></span></h1>
        <p class="text-base text-blue-100/90 leading-relaxed mb-8">
          <?= htmlspecialchars($loginSubtitle) ?>
        </p>
        <div class="flex items-center gap-3 text-xs font-semibold text-blue-200 bg-white dark:bg-gray-800/10 backdrop-blur-md inline-flex px-4 py-2 rounded-full border border-white/10">
          <span class="relative flex h-2.5 w-2.5">
           <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
           <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
          </span>
          Sistem Beroperasi Normal
        </div>
      </div>

      <div class="relative z-10 text-xs text-blue-200/60">
        &copy; <?= date('Y') ?> GUDANG BIGNET &bull; Sistem Manajemen Inventori
      </div>
    </div>

    <!-- KANAN: Form Login -->
    <div class="w-full lg:w-1/2 flex items-center justify-center px-4 py-6 sm:px-8 lg:px-16 bg-gray-50 dark:bg-gray-900 relative">
      <div class="w-full max-w-md bg-white dark:bg-gray-800/95 p-6 sm:p-8 rounded-2xl shadow-xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-gray-700/80 fade-in">
        
        <!-- Branding Header (Logo + Tagline Centered) -->
        <div class="text-center mb-6 flex flex-col items-center">
          <div class="inline-flex items-center justify-center mb-3">
            <?php if ($loginLogo): ?>
              <img src="<?= $loginLogo ?>" alt="Logo BIGNET" class="h-16 sm:h-20 max-h-[80px] object-contain">
            <?php else: ?>
              <div class="h-16 w-16 bg-blue-600 text-white rounded-2xl flex items-center justify-center text-3xl shadow-lg shadow-blue-500/20">
                <i class="fas fa-boxes-stacked"></i>
              </div>
            <?php endif; ?>
          </div>
          <p class="text-xs font-semibold tracking-wider text-blue-600 dark:text-blue-400 uppercase mb-2">
            Sistem Inventori &bull; <?= htmlspecialchars($loginTitle) ?>
          </p>
          <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">
            Selamat Datang Kembali
          </h2>
          <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">
            Masuk untuk melanjutkan ke sistem inventori.
          </p>
        </div>

        <!-- Alert Messages -->
        <?php if(isset($_SESSION['error'])): ?>
          <div class="bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 p-3.5 rounded-lg mb-5 shadow-sm">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <i class="fas fa-exclamation-circle text-red-500 text-sm"></i>
              </div>
              <div class="ml-3">
                <p class="text-xs sm:text-sm text-red-700 dark:text-red-400 font-medium">
                  <?= htmlspecialchars($_SESSION['error']) ?>
                </p>
              </div>
            </div>
          </div>
          <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if(isset($_SESSION['success'])): ?>
          <div class="bg-green-50 dark:bg-green-900/30 border-l-4 border-green-500 p-3.5 rounded-lg mb-5 shadow-sm">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <i class="fas fa-check-circle text-green-500 text-sm"></i>
              </div>
              <div class="ml-3">
                <p class="text-xs sm:text-sm text-green-700 dark:text-green-400 font-medium">
                  <?= htmlspecialchars($_SESSION['success']) ?>
                </p>
              </div>
            </div>
          </div>
          <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <!-- Form Login -->
        <form action="<?php echo url('/login'); ?>" method="POST" class="space-y-4">
          <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
          
          <div>
            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5" for="email">
              Alamat Email
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400 dark:text-gray-500">
                <i class="fas fa-envelope text-sm"></i>
              </div>
              <input class="w-full h-11 pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-800/80 border border-gray-300 dark:border-gray-700 rounded-lg text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" 
                id="email" type="email" name="email" placeholder="nama@perusahaan.com" value="<?php echo htmlspecialchars($_SESSION['old_email'] ?? ''); ?>" required autofocus>
            </div>
            <?php unset($_SESSION['old_email']); ?>
          </div>

          <div>
            <div class="flex justify-between items-center mb-1.5">
              <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider" for="password">
                Kata Sandi
              </label>
              <a href="<?= url('/forgot-password') ?>" class="text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 transition-colors">
                Lupa Kata Sandi?
              </a>
            </div>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400 dark:text-gray-500">
                <i class="fas fa-lock text-sm"></i>
              </div>
              <input class="w-full h-11 pl-10 pr-10 py-2.5 bg-gray-50 dark:bg-gray-800/80 border border-gray-300 dark:border-gray-700 rounded-lg text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" 
                id="password" type="password" name="password" placeholder="••••••••" required>
              
              <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600 dark:text-gray-300 dark:hover:text-gray-300 focus:outline-none" aria-label="Tampilkan Kata Sandi">
                <i class="fas fa-eye text-sm" id="eyeIcon"></i>
              </button>
            </div>
          </div>

          <div class="pt-1">
            <button type="submit" class="w-full h-11 py-2.5 px-4 flex items-center justify-center gap-2 text-sm font-bold rounded-lg text-white bg-blue-600 hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-md shadow-blue-500/20 transition-all cursor-pointer">
              <i class="fas fa-right-to-bracket text-sm opacity-90"></i>
              <span>Masuk</span>
            </button>
          </div>

          <div class="mt-5 pt-4 border-t border-gray-100 dark:border-gray-800/80 text-center">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Belum punya akun?</p>
            <a href="<?= url('/register') ?>" class="w-full h-11 inline-flex items-center justify-center gap-2 border border-gray-300 dark:border-gray-700 rounded-lg text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-800 transition-colors">
              <i class="fas fa-user-plus text-xs text-gray-500 dark:text-gray-400"></i>
              <span>Buat Akun Baru</span>
            </a>
          </div>
        </form>

        <div class="mt-6 text-center text-[11px] text-gray-400 dark:text-gray-500 lg:hidden">
          &copy; <?= date('Y') ?> GUDANG BIGNET &bull; PT. BIG
        </div>
      </div>
    </div>
  </div>

  <script>
    function togglePassword() {
      const pwdInput = document.getElementById('password');
      const eyeIcon = document.getElementById('eyeIcon');
      if (pwdInput.type === 'password') {
        pwdInput.type = 'text';
        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
      } else {
        pwdInput.type = 'password';
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
      }
    }
  </script>
</body>
</html>
