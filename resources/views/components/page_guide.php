<?php
/**
 * REUSABLE PAGE GUIDE COMPONENT
 *
 * Variabel yang didukung (set sebelum include):
 * - $guide (array, opsional): struktur terpusat
 *   [
 *    'title'  => 'Panduan Cepat',
 *    'sections' => [
 *      ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => ['...', '...']],
 *      ['icon' => 'fa-route',  'title' => 'Workflow',    'items' => ['...']],
 *    ],
 *   ]
 * - $guide_content (string, opsional): fallback legacy HTML (halaman lama).
 *
 * Mode: memprioritaskan $guide (structured), lalu $guide_content (legacy).
 * Dark mode: menggunakan Tailwind dark: class.
 */

$guideTitle = $guide['title'] ?? 'Panduan Cepat';
$sections = $guide['sections'] ?? [];
?>
<div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-100 dark:border-blue-800 p-5">
  <h3 class="text-blue-800 dark:text-blue-200 font-bold mb-3 flex items-center">
    <i class="fas fa-lightbulb text-yellow-500 mr-2 text-lg"></i> <?= htmlspecialchars($guideTitle) ?>
  </h3>

  <?php if (!empty($sections)): ?>
    <div class="space-y-4">
      <?php foreach ($sections as $section): ?>
        <?php if (empty($section['items'])) continue; ?>
        <div>
          <p class="text-xs font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wide flex items-center mb-1.5">
            <i class="fas <?= htmlspecialchars($section['icon'] ?? 'fa-circle-info') ?> mr-1.5"></i>
            <?= htmlspecialchars($section['title'] ?? 'Informasi') ?>
          </p>
          <ul class="list-disc pl-4 space-y-1 text-sm text-blue-900 dark:text-blue-300">
            <?php foreach ($section['items'] as $item): ?>
              <li><?= htmlspecialchars((string)$item) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endforeach; ?>
    </div>
  <?php elseif (!empty($guide_content)): ?>
    <div class="text-sm text-blue-900 dark:text-blue-300 space-y-3">
      <?= $guide_content ?>
    </div>
  <?php endif; ?>
</div>
