<?php
$pageTitle = 'Tambah Pengguna';
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
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Tambah Pengguna Baru</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">Buat akun pengguna baru untuk sistem.</p>
        </div>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            <span class="block sm:inline"><?= htmlspecialchars($_SESSION['error']) ?></span>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <!-- D-015: Superadmin-created user langsung aktif -->
    <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-6">
        <div class="flex items-start gap-3">
            <i class="fas fa-check-circle text-green-500 mt-0.5"></i>
            <div class="text-sm text-green-800 dark:text-green-200">
                <p class="font-semibold">Akun langsung AKTIF</p>
                <p class="mt-1 text-green-700 dark:text-green-300">User yang dibuat oleh Superadmin langsung berstatus APPROVED & aktif — dapat langsung login. Tidak perlu persetujuan ulang.</p>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700">
        <form action="<?= url('/users/store') ?>" method="POST" enctype="multipart/form-data" class="p-6">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <div class="md:col-span-2 border-b dark:border-gray-700 pb-4 mb-2">
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Foto Profil</h3>
                    <div class="flex items-center space-x-4">
                        <div class="h-20 w-20 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center overflow-hidden" id="photoPreviewContainer">
                            <i class="fas fa-user text-gray-400 text-3xl" id="photoPlaceholder"></i>
                            <img id="photoPreview" class="h-full w-full object-cover hidden" alt="Preview">
                        </div>
                        <div>
                            <input type="file" name="photo" id="photo" accept="image/jpeg,image/png,image/webp" class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900/30 dark:file:text-blue-400" onchange="previewImage(this)">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Format: JPG, PNG, WEBP. Maks: 2MB.</p>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email Address <span class="text-red-500">*</span></label>
                    <input type="email" name="email" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone Number</label>
                    <input type="text" name="phone" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role <span class="text-red-500">*</span></label>
                    <select name="role" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="TEKNISI_NOC"><?= \App\Helpers\UIHelper::roleLabel('TEKNISI_NOC') ?></option>
                        <option value="ADMIN"><?= \App\Helpers\UIHelper::roleLabel('ADMIN') ?></option>
                        <option value="SUPERADMIN"><?= \App\Helpers\UIHelper::roleLabel('SUPERADMIN') ?></option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>

            <div class="mt-8 border-t dark:border-gray-700 pt-4 flex justify-end gap-3">
                <a href="<?= url('/users') ?>" class="bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 px-4 py-2 rounded-md font-medium text-sm transition-colors">Batal</a>
                <button type="submit" class="bg-blue-600 text-white hover:bg-blue-700 px-4 py-2 rounded-md font-medium text-sm transition-colors">Simpan Pengguna</button>
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
    } else {
        preview.src = '';
        preview.classList.add('hidden');
        placeholder.classList.remove('hidden');
    }
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
