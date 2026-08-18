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
    <title>Lupa Kata Sandi - <?= htmlspecialchars($loginTitle) ?></title>
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
        .fade-in { animation: fadeIn 0.4s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 flex items-center justify-center min-h-screen" <?= $bgImage ? 'style="background-image: url(\''.url('/file?path='.urlencode($bgImage)).'\'); background-size: cover; background-position: center;"' : '' ?>>

    <div class="bg-white dark:bg-gray-800 p-8 rounded-xl shadow-2xl w-full max-w-md fade-in <?= $bgImage ? 'bg-opacity-95 dark:bg-opacity-95 backdrop-blur-sm' : '' ?>">
        <div class="text-center mb-8">
            <?php if ($loginLogo): ?>
                <img src="<?= url('/file?path=' . urlencode($loginLogo)) ?>" alt="Logo" class="h-12 mx-auto mb-4 object-contain">
            <?php endif; ?>
            <div class="mx-auto h-14 w-14 bg-primary-100 dark:bg-primary-900/40 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-headset text-primary-600 dark:text-primary-400 text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Lupa Kata Sandi</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-2">Hubungi Superadmin untuk mereset kata sandi Anda.</p>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 p-4 rounded-md mb-6 shadow-sm">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                    <p class="text-sm text-red-700 dark:text-red-400 font-medium"><?= htmlspecialchars($_SESSION['error']) ?></p>
                </div>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg p-5 mb-6">
            <div class="flex items-start gap-3">
                <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
                <div class="text-sm text-blue-800 dark:text-blue-200">
                    <p class="font-semibold mb-1">Cara mereset kata sandi:</p>
                    <ol class="list-decimal list-inside space-y-1 text-blue-700 dark:text-blue-300">
                        <li>Hubungi Superadmin (Administrator sistem).</li>
                        <li>Superadmin mereset kata sandi Anda dari menu <strong>Pengguna</strong>.</li>
                        <li>Anda menerima kata sandi sementara — gunakan saat login.</li>
                        <li>Ganti kata sandi Anda dari halaman profil setelah masuk.</li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="text-center">
            <a href="<?= url('/login') ?>" class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white font-bold py-2.5 px-6 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 transition-colors">
                <i class="fas fa-arrow-left"></i> Kembali ke Login
            </a>
        </div>
    </div>

</body>
</html>
