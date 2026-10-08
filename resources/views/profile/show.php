<?php
$pageTitle = 'Profil Saya';
ob_start();
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$activeTab = $_GET['tab'] ?? 'general';
if (!in_array($activeTab, ['general', 'password', 'activity'])) {
  $activeTab = 'general';
}

$roleKey = $profile['role'] ?? 'GUEST';
$roleMeta = [
  'SUPERADMIN' => [
    'label' => 'Super Administrator',
    'badge' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300 border-purple-200 dark:border-purple-800',
    'desc' => 'Akses penuh monitoring sistem, manajemen pengguna, audit log, dan pengaturan global.',
    'icon' => 'fa-crown',
  ],
  'ADMIN' => [
    'label' => 'Admin Gudang',
    'badge' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 border-blue-200 dark:border-blue-800',
    'desc' => 'Mengelola operasional keluar-masuk barang, persetujuan pengajuan, transfer, dan stok gudang.',
    'icon' => 'fa-warehouse',
  ],
  'TEKNISI_NOC' => [
    'label' => 'Teknisi / NOC',
    'badge' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
    'desc' => 'Mengajukan kebutuhan barang, meminjam alat/aset, mencatat instalasi, dan melapor insiden.',
    'icon' => 'fa-tools',
  ],
];
$currentRole = $roleMeta[$roleKey] ?? [
  'label' => $roleKey,
  'badge' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border-gray-200',
  'desc' => 'Pengguna sistem inventori.',
  'icon' => 'fa-user',
];
?>

<div class="max-w-4xl mx-auto space-y-6">

  <!-- Flash Messages -->
  <?php if (isset($_SESSION['success'])): ?>
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 dark:border-emerald-900/50 dark:bg-emerald-950/30 p-4 text-sm text-emerald-800 dark:text-emerald-200 flex items-center justify-between shadow-sm">
      <div class="flex items-center gap-3">
        <i class="fas fa-check-circle text-emerald-500 text-base shrink-0"></i>
        <span><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></span>
      </div>
    </div>
  <?php endif; ?>
  <?php if (isset($_SESSION['error'])): ?>
    <div class="rounded-xl border border-red-200 bg-red-50 dark:border-red-900/50 dark:bg-red-950/30 p-4 text-sm text-red-800 dark:text-red-200 flex items-center justify-between shadow-sm">
      <div class="flex items-center gap-3">
        <i class="fas fa-exclamation-circle text-red-500 text-base shrink-0"></i>
        <span><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
      </div>
    </div>
  <?php endif; ?>

  <!-- Profile Hero Header Card (Disafa Modern SaaS Style) -->
  <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-800 p-5 sm:p-7 relative overflow-hidden">
    <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5">
      <!-- Photo Avatar with Upload Overlay -->
      <div class="relative group shrink-0">
        <?php if (!empty($profile['photo_path'])): ?>
          <img id="avatarPreview" src="<?= url('/file?path=' . urlencode($profile['photo_path'])) ?>" alt="Foto Profil" class="w-24 h-24 sm:w-28 sm:h-28 rounded-full object-cover ring-4 ring-slate-100 dark:ring-slate-800 shadow-md">
        <?php else: ?>
          <div id="avatarFallback" class="w-24 h-24 sm:w-28 sm:h-28 rounded-full bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center font-extrabold text-white text-3xl sm:text-4xl shadow-md ring-4 ring-slate-100 dark:ring-slate-800">
            <?= strtoupper(substr($profile['name'] ?? 'U', 0, 1)) ?>
          </div>
        <?php endif; ?>

        <!-- Quick Upload Button Trigger -->
        <label for="profilePhotoInput" class="absolute bottom-0 right-0 w-8 h-8 rounded-full bg-blue-600 hover:bg-blue-700 text-white flex items-center justify-center shadow-lg ring-2 ring-white dark:ring-slate-900 cursor-pointer transition-transform hover:scale-110" title="Ganti Foto">
          <i class="fas fa-camera text-xs"></i>
        </label>
      </div>

      <!-- Hidden Form for Instant Photo Upload -->
      <form id="photoUploadForm" action="<?= url('/profile/photo') ?>" method="POST" enctype="multipart/form-data" class="hidden">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <input type="file" id="profilePhotoInput" name="photo" accept="image/jpeg,image/png,image/webp" onchange="submitPhotoUpload(this)">
      </form>

      <!-- User Identification -->
      <div class="text-center sm:text-left flex-1 min-w-0">
        <div class="flex flex-col sm:flex-row sm:items-center gap-2 mb-1.5">
          <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-slate-100 truncate"><?= htmlspecialchars($profile['name']) ?></h1>
          <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border <?= $currentRole['badge'] ?> w-fit mx-auto sm:mx-0">
            <i class="fas <?= $currentRole['icon'] ?> text-[10px]"></i>
            <?= htmlspecialchars($currentRole['label']) ?>
          </span>
        </div>

        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 flex items-center justify-center sm:justify-start gap-1.5 mb-3">
          <i class="fas fa-envelope text-slate-400 text-xs"></i>
          <span><?= htmlspecialchars($profile['email']) ?></span>
          <?php if (!empty($profile['phone'])): ?>
            <span class="mx-1 text-slate-300 dark:text-slate-600">·</span>
            <i class="fas fa-phone text-slate-400 text-xs"></i>
            <span><?= htmlspecialchars($profile['phone']) ?></span>
          <?php endif; ?>
        </p>

        <!-- Account Badges Info -->
        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 text-xs">
          <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 font-medium border border-emerald-200/60 dark:border-emerald-800">
            <i class="fas fa-check-circle text-[10px]"></i> Akun Aktif
          </span>
          <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300 font-medium">
            <i class="fas fa-calendar-alt text-[10px] text-slate-400"></i> Terdaftar: <?= date('d M Y', strtotime($profile['created_at'])) ?>
          </span>
        </div>
      </div>
    </div>
  </div>

  <!-- Segmented Tab Navigation -->
  <div class="flex p-1 bg-slate-100 dark:bg-slate-800/80 rounded-2xl gap-1">
    <a href="?tab=general" class="flex-1 text-center py-2 px-2 sm:py-2.5 sm:px-3 rounded-xl text-xs sm:text-sm font-bold transition-all flex items-center justify-center gap-1.5 <?= $activeTab === 'general' ? 'bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' ?>">
      <i class="fas fa-user-gear text-xs sm:text-sm"></i>
      <span class="whitespace-nowrap">Data Diri</span>
    </a>
    <a href="?tab=password" class="flex-1 text-center py-2 px-2 sm:py-2.5 sm:px-3 rounded-xl text-xs sm:text-sm font-bold transition-all flex items-center justify-center gap-1.5 <?= $activeTab === 'password' ? 'bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' ?>">
      <i class="fas fa-key text-xs sm:text-sm"></i>
      <span class="whitespace-nowrap">Password</span>
    </a>
    <a href="?tab=activity" class="flex-1 text-center py-2 px-2 sm:py-2.5 sm:px-3 rounded-xl text-xs sm:text-sm font-bold transition-all flex items-center justify-center gap-1.5 <?= $activeTab === 'activity' ? 'bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' ?>">
      <i class="fas fa-clock-rotate-left text-xs sm:text-sm"></i>
      <span class="whitespace-nowrap">Aktivitas</span>
    </a>
  </div>

  <!-- Tab Content -->
  <?php if ($activeTab === 'general'): ?>
    <!-- TAB 1: INFORMASI DATA DIRI -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-800 p-5 sm:p-7">
      <div class="mb-5 pb-4 border-b border-slate-100 dark:border-slate-800">
        <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">Informasi Pribadi</h2>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Perbarui nama dan kontak akun yang tampil di sistem inventori.</p>
      </div>

      <form action="<?= url('/profile/update') ?>" method="POST" class="space-y-5">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <input type="hidden" name="tab" value="general">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <!-- Nama Lengkap -->
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1.5">
              Nama Lengkap <span class="text-red-500">*</span>
            </label>
            <input type="text" name="name" value="<?= htmlspecialchars($profile['name']) ?>" required autocomplete="name" class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" placeholder="Nama lengkap Anda">
          </div>

          <!-- Email (Read-Only) -->
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1.5 flex items-center justify-between">
              <span>Alamat Email</span>
              <span class="text-[10px] text-slate-400 font-normal lowercase">(tidak dapat diubah)</span>
            </label>
            <input type="email" value="<?= htmlspecialchars($profile['email']) ?>" disabled class="w-full px-3.5 py-2.5 bg-slate-100 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-500 dark:text-slate-400 cursor-not-allowed">
          </div>

          <!-- Nomor Telepon -->
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1.5">
              Nomor WhatsApp / Telepon
            </label>
            <input type="text" name="phone" value="<?= htmlspecialchars($profile['phone'] ?? '') ?>" autocomplete="tel" class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" placeholder="Contoh: 081234567890">
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Digunakan untuk notifikasi dan kontak tim di lapangan.</p>
          </div>

          <!-- Peran & Hak Akses (Read-Only Info) -->
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1.5">
              Peran Pengguna (Role)
            </label>
            <div class="flex items-center gap-3 p-2.5 bg-slate-50 dark:bg-slate-800/40 rounded-xl border border-slate-200/80 dark:border-slate-800">
              <div class="w-9 h-9 rounded-lg bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                <i class="fas <?= $currentRole['icon'] ?> text-sm"></i>
              </div>
              <div class="min-w-0">
                <p class="text-xs font-bold text-slate-800 dark:text-slate-200"><?= htmlspecialchars($currentRole['label']) ?></p>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 truncate"><?= htmlspecialchars($currentRole['desc']) ?></p>
              </div>
            </div>
          </div>
        </div>

        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end">
          <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-xl shadow-sm text-sm transition-all">
            <i class="fas fa-check text-xs"></i> Simpan Perubahan
          </button>
        </div>
      </form>
    </div>

  <?php elseif ($activeTab === 'password'): ?>
    <!-- TAB 2: KEAMANAN & UBAH PASSWORD -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-800 p-5 sm:p-7">
      <div class="mb-5 pb-4 border-b border-slate-100 dark:border-slate-800">
        <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">Ubah Kata Sandi</h2>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Pastikan akun Anda menggunakan kata sandi yang aman dan tidak digunakan di situs lain.</p>
      </div>

      <form action="<?= url('/profile/update') ?>" method="POST" class="space-y-5">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <input type="hidden" name="tab" value="password">
        <!-- Preserve required name -->
        <input type="hidden" name="name" value="<?= htmlspecialchars($profile['name']) ?>">
        <input type="hidden" name="phone" value="<?= htmlspecialchars($profile['phone'] ?? '') ?>">

        <div class="space-y-4 max-w-lg">
          <!-- Password Saat Ini -->
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1.5">
              Kata Sandi Saat Ini <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <input type="password" id="currPassInput" name="current_password" required autocomplete="current-password" class="w-full pl-3.5 pr-10 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" placeholder="Masukkan password lama Anda">
              <button type="button" onclick="togglePasswordVisibility('currPassInput', this)" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                <i class="fas fa-eye text-xs"></i>
              </button>
            </div>
          </div>

          <!-- Password Baru -->
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1.5">
              Kata Sandi Baru <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <input type="password" id="newPassInput" name="password" minlength="6" required autocomplete="new-password" class="w-full pl-3.5 pr-10 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" placeholder="Minimal 6 karakter">
              <button type="button" onclick="togglePasswordVisibility('newPassInput', this)" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                <i class="fas fa-eye text-xs"></i>
              </button>
            </div>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Gunakan kombinasi huruf, angka, dan simbol untuk keamanan maksimal.</p>
          </div>

          <!-- Konfirmasi Password Baru -->
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1.5">
              Ulangi Kata Sandi Baru <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <input type="password" id="confPassInput" name="password_confirmation" minlength="6" required autocomplete="new-password" class="w-full pl-3.5 pr-10 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" placeholder="Ulangi kata sandi baru">
              <button type="button" onclick="togglePasswordVisibility('confPassInput', this)" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                <i class="fas fa-eye text-xs"></i>
              </button>
            </div>
          </div>
        </div>

        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end">
          <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-xl shadow-sm text-sm transition-all">
            <i class="fas fa-shield-alt text-xs"></i> Perbarui Kata Sandi
          </button>
        </div>
      </form>
    </div>

        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end">
          <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-xl shadow-sm text-sm transition-all">
            <i class="fas fa-shield-alt text-xs"></i> Perbarui Kata Sandi
          </button>
        </div>
      </form>
    </div>

  <?php elseif ($activeTab === 'activity'): ?>
    <!-- TAB 3: RIWAYAT AKTIVITAS TERAKHIR -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-800 p-5 sm:p-7">
      <div class="mb-5 pb-4 border-b border-slate-100 dark:border-slate-800">
        <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">Log Aktivitas Terbaru</h2>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Catatan 5 aksi atau transaksi terakhir yang Anda lakukan pada sistem.</p>
      </div>

      <?php if (!empty($recentActivities)): ?>
        <div class="divide-y divide-slate-100 dark:divide-slate-800">
          <?php foreach ($recentActivities as $act): ?>
            <div class="py-3 flex items-start gap-3">
              <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 shrink-0 mt-0.5">
                <i class="fas fa-clock text-xs"></i>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-xs sm:text-sm font-semibold text-slate-800 dark:text-slate-200">
                  <span class="inline-block px-2 py-0.5 text-[10px] font-bold rounded-md bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 mr-1.5"><?= htmlspecialchars($act['action']) ?></span>
                  Modul <?= htmlspecialchars($act['entity']) ?> #<?= (int)$act['entity_id'] ?>
                </p>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">
                  <?= date('d M Y, H:i:s', strtotime($act['created_at'])) ?>
                  <?php if (!empty($act['ip_address'])): ?>
                    · IP: <?= htmlspecialchars($act['ip_address']) ?>
                  <?php endif; ?>
                </p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="py-8 text-center text-slate-400 dark:text-slate-500 text-sm">
          <i class="fas fa-history text-2xl mb-2"></i>
          <p>Belum ada rekaman riwayat aktivitas.</p>
        </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

</div>

<script>
function submitPhotoUpload(input) {
  if (!input.files || !input.files[0]) return;
  const file = input.files[0];
  if (file.size > 2 * 1024 * 1024) {
    if (window.AppToast) {
      AppToast.error('Ukuran foto maksimal 2MB.');
    } else {
      alert('Ukuran foto maksimal 2MB.');
    }
    input.value = '';
    return;
  }
  document.getElementById('photoUploadForm').submit();
}

function togglePasswordVisibility(inputId, btn) {
  const input = document.getElementById(inputId);
  if (!input) return;
  const icon = btn.querySelector('i');
  if (input.type === 'password') {
    input.type = 'text';
    if (icon) {
      icon.classList.remove('fa-eye');
      icon.classList.add('fa-eye-slash');
    }
  } else {
    input.type = 'password';
    if (icon) {
      icon.classList.remove('fa-eye-slash');
      icon.classList.add('fa-eye');
    }
  }
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
