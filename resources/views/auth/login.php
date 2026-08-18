<?php
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Branding Login mengikuti Pengaturan Sistem (Superadmin):
// Nama Perusahaan = Pengaturan Sistem -> Umum (company_name)
// Logo Utama      = Pengaturan Sistem -> Branding & Tampilan (logo_path)
// Logo/favicon dilayani via endpoint publik /logo & /favicon (StaticController) karena
// /file memerlukan login sehingga tidak bisa dipakai di halaman publik (login/register).
$loginTitle = setting('company_name', 'PT. BIG');

// Headline "Welcome to" pada halaman Login mengikuti Nama Aplikasi (Pengaturan Sistem -> Umum, app_name).
// Bukan company_name: company_name tetap dipakai untuk branding nama perusahaan di samping logo (desktop/mobile) & <title>.
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
        .fade-in { animation: fadeIn 0.5s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen text-gray-800 dark:text-gray-200">
    <div class="flex min-h-screen w-full">
        
        <!-- KIRI: Branding & Informasi -->
        <div class="hidden lg:flex lg:w-1/2 flex-col justify-between p-12 bg-primary-900 text-white relative overflow-hidden" 
            <?= $bgImage ? 'style="background-image: linear-gradient(to right, rgba(15, 23, 42, 0.9), rgba(15, 23, 42, 0.7)), url(\''.url('/file?path='.urlencode($bgImage)).'\'); background-size: cover; background-position: center;"' : 'style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);"' ?>>
            
            <!-- Ornamen Dekoratif -->
            <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-white opacity-10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-[-10%] right-[-10%] w-96 h-96 bg-blue-400 opacity-20 rounded-full blur-3xl"></div>

            <div class="relative z-10 flex items-center gap-3">
                <?php if ($loginLogo): ?>
                    <img src="<?= $loginLogo ?>" alt="Logo" class="h-10 object-contain bg-white p-1 rounded">
                <?php else: ?>
                    <div class="h-10 w-10 bg-white text-blue-800 rounded flex items-center justify-center font-bold text-xl"><i class="fas fa-boxes"></i></div>
                <?php endif; ?>
                <span class="text-xl font-bold tracking-wider"><?= htmlspecialchars($loginTitle) ?></span>
            </div>

            <div class="relative z-10 my-auto max-w-lg">
                <h1 class="text-4xl md:text-5xl font-extrabold mb-6 leading-tight">Welcome to <br><span class="text-blue-300"><?= htmlspecialchars($appName) ?></span></h1>
                <p class="text-lg text-blue-100 opacity-90 leading-relaxed mb-8">
                    <?= htmlspecialchars($loginSubtitle) ?>
                </p>
                <div class="flex items-center gap-3 text-sm font-medium text-blue-200 bg-black bg-opacity-20 inline-flex px-4 py-2 rounded-full border border-blue-500 border-opacity-30">
                    <span class="relative flex h-3 w-3">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                    </span>
                    Semua Sistem Beroperasi Normal
                </div>
            </div>

            <div class="relative z-10 text-sm text-blue-200 opacity-70">
                &copy; <?= date('Y') ?> GUDANG BIGNET - Create by Tommy Ryan Dwiputra.
            </div>
        </div>

        <!-- KANAN: Form Login -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 sm:p-12 lg:p-24 bg-white dark:bg-gray-900 relative">
            <div class="w-full max-w-md fade-in">
                
                <!-- Tampil logo di mobile -->
                <div class="lg:hidden text-center mb-8 flex flex-col items-center">
                    <?php if ($loginLogo): ?>
                        <img src="<?= $loginLogo ?>" alt="Logo" class="h-12 mb-4 object-contain">
                    <?php endif; ?>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100"><?= htmlspecialchars($loginTitle) ?></h2>
                </div>

                <div class="mb-10">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2">Masuk ke Akun</h2>
                    <p class="text-gray-500 dark:text-gray-400">Silakan masukkan kredensial Anda untuk melanjutkan.</p>
                </div>

                <?php if(isset($_SESSION['error'])): ?>
                    <div class="bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 p-4 rounded-md mb-6 shadow-sm">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-circle text-red-500"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700 dark:text-red-400 font-medium">
                                    <?= htmlspecialchars($_SESSION['error']) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <?php if(isset($_SESSION['success'])): ?>
                    <div class="bg-green-50 dark:bg-green-900/30 border-l-4 border-green-500 p-4 rounded-md mb-6 shadow-sm">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle text-green-500"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-green-700 dark:text-green-400 font-medium">
                                    <?= htmlspecialchars($_SESSION['success']) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <form action="<?php echo url('/login'); ?>" method="POST" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2" for="email">Alamat Email</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <input class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all" 
                                id="email" type="email" name="email" placeholder="nama@perusahaan.com" value="<?php echo htmlspecialchars($_SESSION['old_email'] ?? ''); ?>" required autofocus>
                        </div>
                        <?php unset($_SESSION['old_email']); ?>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300" for="password">Kata Sandi</label>
                            <a href="<?= url('/forgot-password') ?>" class="text-sm font-medium text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300 transition-colors">Lupa Kata Sandi?</a>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-lock"></i>
                            </div>
                            <input class="w-full pl-10 pr-10 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all" 
                                id="password" type="password" name="password" placeholder="••••••••" required>
                            
                            <!-- Toggle Password Visibility -->
                            <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none">
                                <i class="fas fa-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-bold rounded-lg text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 shadow-md transition-all">
                            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                <i class="fas fa-sign-in-alt text-primary-500 group-hover:text-primary-400"></i>
                            </span>
                            Masuk
                        </button>
                    </div>

                    <div class="mt-4 text-center">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Belum punya akun?</p>
                        <a href="<?= url('/register') ?>" class="inline-block w-full text-center py-2.5 border border-gray-300 dark:border-gray-700 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            <i class="fas fa-user-plus mr-1"></i> Buat Akun Baru
                        </a>
                    </div>
                </form>

                <div class="mt-8 text-center text-sm text-gray-500 dark:text-gray-400 lg:hidden">
                    &copy; <?= date('Y') ?> GUDANG BIGNET
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
