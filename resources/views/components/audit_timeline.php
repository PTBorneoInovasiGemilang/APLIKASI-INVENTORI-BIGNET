<div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mt-6 border-t-4 border-blue-500">
    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-4"><i class="fas fa-history mr-2 text-blue-500"></i> Riwayat Status & Audit</h3>
    
    <?php if (empty($timeline)): ?>
        <?php
        $emptyBare = true;
        $emptyMessage = 'Belum ada riwayat tercatat';
        $emptySubMessage = 'Riwayat status dan audit akan muncul di sini setelah ada aktivitas.';
        $icon = 'fa-history';
        require __DIR__ . '/table/empty_state.php';
        unset($emptyBare, $emptyMessage, $emptySubMessage, $icon);
        ?>
    <?php else: ?>
        <div class="relative border-l-2 border-gray-200 dark:border-gray-700 ml-3">
            <?php foreach ($timeline as $t): ?>
            <div class="mb-6 ml-6 relative">
                <!-- Icon -->
                <span class="absolute flex items-center justify-center w-8 h-8 bg-white dark:bg-gray-800 rounded-full -left-10 top-0 border-2 <?php 
                    if ($t['action'] === 'CREATE') echo 'border-green-500 text-green-500';
                    elseif ($t['action'] === 'UPDATE') echo 'border-blue-500 text-blue-500';
                    elseif ($t['action'] === 'DELETE') echo 'border-red-500 text-red-500';
                    elseif ($t['action'] === 'APPROVE') echo 'border-indigo-500 text-indigo-500';
                    elseif ($t['action'] === 'REJECT') echo 'border-orange-500 text-orange-500';
                    else echo 'border-gray-500 text-gray-500';
                ?>"> 
                    <i class="fas <?php 
                        if ($t['action'] === 'CREATE') echo 'fa-plus';
                        elseif ($t['action'] === 'UPDATE') echo 'fa-edit';
                        elseif ($t['action'] === 'DELETE') echo 'fa-trash';
                        elseif ($t['action'] === 'APPROVE') echo 'fa-check';
                        elseif ($t['action'] === 'REJECT') echo 'fa-times';
                        else echo 'fa-circle text-xs';
                    ?> text-sm"></i>
                </span>
                
                <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg border border-gray-100 dark:border-gray-700 shadow-sm">
                    <div class="flex justify-between items-center mb-1">
                        <h4 class="font-bold text-gray-800 dark:text-gray-100 text-sm">
                            <?php echo htmlspecialchars($t['action']); ?>
                        </h4>
                        <time class="text-xs font-semibold text-gray-500 dark:text-gray-400">
                            <i class="far fa-clock mr-1"></i><?php echo date('d M Y, H:i', strtotime($t['created_at'])); ?>
                        </time>
                    </div>
                    
                    <div class="text-sm text-gray-600 dark:text-gray-300 mt-2 flex items-center">
                        <div class="w-6 h-6 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center text-gray-600 dark:text-gray-300 text-xs font-bold mr-2">
                            <?php echo strtoupper(substr($t['user_name'] ?? 'S', 0, 1)); ?>
                        </div>
                        Oleh: <span class="font-medium ml-1"><?php echo htmlspecialchars($t['user_name'] ?? 'Sistem'); ?></span>
                    </div>
                    
                    <?php if (!empty($t['notes'])): ?>
                        <div class="mt-3 text-sm text-gray-600 dark:text-gray-300 border-l-2 border-gray-300 dark:border-gray-600 pl-3 italic">
                            <?php echo htmlspecialchars($t['notes']); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
