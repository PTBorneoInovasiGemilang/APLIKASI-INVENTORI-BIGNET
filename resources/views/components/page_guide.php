<?php
/**
 * REUSABLE PAGE GUIDE COMPONENT (Disafa Modern SaaS Style)
 *
 * Variabel yang didukung:
 * - $guide (array, opsional): struktur terpusat dari PageGuideHelper
 * - $guide_content (string, opsional): fallback legacy HTML
 *
 * Tampilan selaras dengan grid 4px, tema Disafa, dan dukungan penuh Dark Mode.
 */

$guideTitle = $guide['title'] ?? 'Panduan Cepat';
$sections = $guide['sections'] ?? [];
?>
<div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-4 sm:p-5 transition-all">
  <div class="flex items-center justify-between gap-3 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
    <div class="flex items-center gap-2.5 min-w-0">
      <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-300 flex items-center justify-center shrink-0 text-sm shadow-sm">
        <i class="fas fa-book-open"></i>
      </div>
      <div class="min-w-0">
        <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100 leading-tight truncate">
          <?= htmlspecialchars($guideTitle) ?>
        </h3>
        <p class="text-[10px] text-slate-400 dark:text-slate-500 font-medium mt-0.5">Petunjuk & Bantuan Modul</p>
      </div>
    </div>
    <button type="button" onclick="togglePageGuide()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors text-xs shrink-0" title="Tutup Panduan (Buka kembali via icon buku di atas)">
      <i class="fas fa-times"></i>
    </button>
  </div>

  <?php if (!empty($sections)): ?>
    <div class="space-y-3">
      <?php foreach ($sections as $section): ?>
        <?php if (empty($section['items'])) continue; ?>
        <div class="p-3.5 rounded-xl bg-slate-50/70 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800/80">
          <p class="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2 mb-2">
            <i class="fas <?= htmlspecialchars($section['icon'] ?? 'fa-circle-info') ?> text-blue-500 text-xs shrink-0"></i>
            <span><?= htmlspecialchars($section['title'] ?? 'Informasi') ?></span>
          </p>
          <ul class="space-y-2 text-xs leading-relaxed text-slate-600 dark:text-slate-300">
            <?php foreach ($section['items'] as $item): ?>
              <li class="flex items-start gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-blue-500/80 dark:bg-blue-400 shrink-0 mt-1.5"></span>
                <span class="flex-1"><?= htmlspecialchars((string)$item) ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endforeach; ?>
    </div>
  <?php elseif (!empty($guide_content)): ?>
    <div class="p-3.5 rounded-xl bg-slate-50/70 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800/80 text-xs leading-relaxed text-slate-600 dark:text-slate-300 space-y-2.5">
      <?= $guide_content ?>
    </div>
  <?php endif; ?>
</div>
