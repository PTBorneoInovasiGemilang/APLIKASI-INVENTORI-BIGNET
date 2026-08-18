<?php

namespace App\Helpers;

class UIHelper {
    /**
     * Render a consistent semantic badge
     */
    /**
     * Terjemahan status enum database ke label Indonesia untuk UI.
     * VALUE tetap enum (untuk filter/badge logic); label user-facing memakai fungsi ini.
     */
    public static function statusLabel($status) {
        $map = [
            'PENDING' => 'Menunggu',
            'PENDING_APPROVAL' => 'Menunggu Persetujuan',
            'APPROVED' => 'Disetujui',
            'PARTIAL_APPROVED' => 'Disetujui Sebagian',
            'REJECTED' => 'Ditolak',
            'COMPLETED' => 'Selesai',
            'SUBMITTED' => 'Dikirim',
            'DRAFT' => 'Draf',
            'FULFILLED' => 'Terpenuhi',
            'AVAILABLE' => 'Tersedia',
            'BORROWED' => 'Dipinjam',
            'INSTALLED' => 'Terpasang',
            'IN_TRANSIT' => 'Dalam Perjalanan',
            'IN_USE' => 'Dipakai',
            'REPAIR' => 'Perbaikan',
            'BROKEN' => 'Rusak',
            'LOST' => 'Hilang',
            'DAMAGED' => 'Rusak',
            'RETURNED' => 'Dikembalikan',
            'PARTIAL_RETURNED' => 'Kembali Sebagian',
            'ISSUED' => 'Dikeluarkan',
            'IN_PROGRESS' => 'Dalam Proses',
            'SUSPENDED' => 'Ditangguhkan',
            'ACTIVE' => 'Aktif',
            'MANUAL' => 'Manual',
            'INVENTORY' => 'Stok Gudang',
            'PARTIAL_RECEIVED' => 'Diterima Sebagian',
            'REVIEW' => 'Dalam Tinjauan',
            'RESOLVED' => 'Selesai',
            'CLOSED' => 'Ditutup',
            'PROCESSING' => 'Diproses',
            'COUNTED' => 'Dihitung',
            'DAMAGE' => 'Kerusakan',
            'LOST' => 'Kehilangan',
            'GOOD' => 'Baik',
            'BAIK' => 'Baik',
            'RUSAK_RINGAN' => 'Rusak Ringan',
            'RUSAK_BERAT' => 'Rusak Berat',
            'HILANG' => 'Hilang',
            'PERLU_PERBAIKAN' => 'Perlu Perbaikan',
            'TIDAK_LENGKAP' => 'Tidak Lengkap',
            'IN' => 'Masuk',
            'OUT' => 'Keluar',
            'INCOMING' => 'Barang Masuk',
            'OUTGOING' => 'Barang Keluar',
            'TRANSFER_IN' => 'Transfer Masuk',
            'TRANSFER_OUT' => 'Transfer Keluar',
            'ADJUSTMENT_IN' => 'Penyesuaian Masuk',
            'ADJUSTMENT_OUT' => 'Penyesuaian Keluar',
            'INITIAL' => 'Saldo Awal',
        ];
        return $map[$status] ?? $status;
    }

    /**
     * Label display role untuk user-facing.
     * VALUE internal (SUPERADMIN/ADMIN/TEKNISI_NOC) TIDAK berubah untuk auth/
     * business logic; fungsi ini HANYA untuk presentation.
     * STANDARD: TEKNISI_NOC -> Teknisi/NOC; ADMIN -> Admin; SUPERADMIN -> Superadmin.
     */
    public static function roleLabel($role) {
        $map = [
            'SUPERADMIN' => 'Superadmin',
            'ADMIN' => 'Admin',
            'TEKNISI_NOC' => 'Teknisi/NOC',
        ];
        return $map[$role] ?? $role;
    }

    public static function badge($text, $type = 'NEUTRAL', $customClasses = '') {
        // Terjemahkan enum status yang dikenal ke label Indonesia (raw enum tidak bocor ke UI).
        $text = self::statusLabel($text);
        $base = 'px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full ';
        
        switch (strtoupper($type)) {
            case 'SUCCESS':
                $color = 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300';
                break;
            case 'WARNING':
                $color = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300';
                break;
            case 'DANGER':
                $color = 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300';
                break;
            case 'INFO':
                $color = 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300';
                break;
            case 'PURPLE':
                $color = 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300';
                break;
            case 'NEUTRAL':
            default:
                $color = 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
                break;
        }

        return sprintf('<span class="%s %s %s">%s</span>', $base, $color, $customClasses, htmlspecialchars($text));
    }

    /**
     * Global Status Badge — icon Font Awesome + label Indonesia + warna semantik.
     * Pemetaan berdasarkan status ACTUAL di source (enum backend TIDAK diubah).
     * Default: NEUTRAL gray bila status tidak dikenal.
     */
    public static function statusBadge($status, $customClasses = '') {
        $map = [
            // Pengajuan / approval
            'PENDING'          => ['fa-clock', 'WARNING'],
            'PENDING_APPROVAL' => ['fa-clock', 'WARNING'],
            'SUBMITTED'        => ['fa-paper-plane', 'INFO'],
            'DRAFT'            => ['fa-pen', 'NEUTRAL'],
            'APPROVED'         => ['fa-check-circle', 'SUCCESS'],
            'PARTIAL_APPROVED' => ['fa-adjust', 'PURPLE'],
            'REJECTED'         => ['fa-times-circle', 'DANGER'],
            'FULFILLED'        => ['fa-box-open', 'SUCCESS'],
            'COMPLETED'        => ['fa-check-double', 'SUCCESS'],
            // Serial / inventori
            'AVAILABLE'        => ['fa-check-circle', 'SUCCESS'],
            'BORROWED'         => ['fa-hand-holding', 'INFO'],
            'INSTALLED'        => ['fa-plug', 'SUCCESS'],
            'ISSUED'           => ['fa-sign-out-alt', 'INFO'],
            'IN_TRANSIT'       => ['fa-truck', 'INFO'],
            'IN_USE'           => ['fa-wrench', 'INFO'],
            'RETURNED'         => ['fa-undo', 'SUCCESS'],
            'PARTIAL_RETURNED' => ['fa-rotate-left', 'PURPLE'],
            'BROKEN'           => ['fa-exclamation-triangle', 'DANGER'],
            'DAMAGED'          => ['fa-exclamation-triangle', 'DANGER'],
            'LOST'             => ['fa-question-circle', 'DANGER'],
            'REPAIR'           => ['fa-tools', 'WARNING'],
            // Proses / workflow
            'PROCESSING'       => ['fa-spinner', 'INFO'],
            'IN_PROGRESS'      => ['fa-spinner', 'INFO'],
            'REVIEW'           => ['fa-search', 'INFO'],
            'INVESTIGATING'    => ['fa-search', 'INFO'],
            'RESOLVED'         => ['fa-check-circle', 'SUCCESS'],
            'CLOSED'           => ['fa-check-circle', 'SUCCESS'],
            'COUNTED'          => ['fa-clipboard-check', 'SUCCESS'],
            'ACTIVE'           => ['fa-circle-check', 'SUCCESS'],
        ];
        $entry = $map[$status] ?? ['fa-circle', 'NEUTRAL'];
        $icon = $entry[0];
        $type = $entry[1];
        $label = self::statusLabel($status);

        // Bangun badge secara manual agar icon Font Awesome ada DI DALAM badge.
        $base = 'px-2.5 py-0.5 inline-flex items-center gap-1.5 text-xs leading-5 font-semibold rounded-full ';
        switch (strtoupper($type)) {
            case 'SUCCESS': $color = 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'; break;
            case 'WARNING': $color = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300'; break;
            case 'DANGER':  $color = 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300'; break;
            case 'INFO':    $color = 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300'; break;
            case 'PURPLE':  $color = 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300'; break;
            default:        $color = 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'; break;
        }

        return sprintf('<span class="%s %s %s"><i class="fas %s"></i>%s</span>', $base, $color, $customClasses, $icon, htmlspecialchars($label));
    }

    /**
     * Label manusiawi untuk transaction_type pada stock_ledgers / mutation history.
     * Mapping HANYA untuk tipe yang benar-benar dipakai oleh service (StockService dkk).
     */
    public static function mutationTypeLabel($type) {
        $map = [
            'INCOMING'      => 'Barang Masuk',
            'OUTGOING'      => 'Barang Keluar',
            'LOAN'          => 'Peminjaman',
            'RETURN'        => 'Pengembalian',
            'TRANSFER_IN'   => 'Transfer Masuk',
            'TRANSFER_OUT'  => 'Transfer Keluar',
            'ADJUSTMENT_IN' => 'Penyesuaian (+)',
            'ADJUSTMENT_OUT'=> 'Penyesuaian (-)',
            'INITIAL'       => 'Saldo Awal',
            'IN'            => 'Barang Masuk',
            'OUT'           => 'Barang Keluar',
        ];
        return $map[$type] ?? $type;
    }

    /**
     * Render a consistent action button (icon only)
     */
    public static function actionBtn($url, $icon, $type = 'info', $tooltip = '', $attributes = '') {
        $base = 'p-1.5 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-800 inline-flex items-center justify-center';
        
        switch (strtolower($type)) {
            case 'primary':
            case 'info':
                $color = 'text-blue-600 hover:text-blue-900 hover:bg-blue-50 dark:text-blue-400 dark:hover:text-blue-300 dark:hover:bg-blue-900/30 focus:ring-blue-500';
                break;
            case 'warning':
                $color = 'text-yellow-600 hover:text-yellow-900 hover:bg-yellow-50 dark:text-yellow-400 dark:hover:text-yellow-300 dark:hover:bg-yellow-900/30 focus:ring-yellow-500';
                break;
            case 'danger':
                $color = 'text-red-600 hover:text-red-900 hover:bg-red-50 dark:text-red-400 dark:hover:text-red-300 dark:hover:bg-red-900/30 focus:ring-red-500';
                break;
            case 'success':
                $color = 'text-green-600 hover:text-green-900 hover:bg-green-50 dark:text-green-400 dark:hover:text-green-300 dark:hover:bg-green-900/30 focus:ring-green-500';
                break;
            default:
                $color = 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:bg-gray-700/50 focus:ring-gray-500';
                break;
        }

        $titleAttr = $tooltip ? 'title="' . htmlspecialchars($tooltip) . '"' : '';
        
        // If it's not a real link but a button
        if ($url === '#') {
            return sprintf(
                '<button type="button" class="%s %s" %s %s><i class="%s fa-fw"></i></button>',
                $base, $color, $titleAttr, $attributes, $icon
            );
        }

        return sprintf(
            '<a href="%s" class="%s %s" %s %s><i class="%s fa-fw"></i></a>',
            $url, $base, $color, $titleAttr, $attributes, $icon
        );
    }

    /**
     * Render an empty table state
     */
    public static function emptyState($colSpan, $message = 'Tidak ada data yang ditemukan.') {
        return sprintf(
            '<tr>
                <td colspan="%d" class="px-6 py-12 text-center border-b border-gray-200 dark:border-gray-700">
                    <div class="flex flex-col items-center justify-center">
                        <i class="fas fa-inbox text-gray-300 dark:text-gray-600 text-4xl mb-3"></i>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">%s</p>
                    </div>
                </td>
            </tr>',
            $colSpan,
            htmlspecialchars($message)
        );
    }
}
