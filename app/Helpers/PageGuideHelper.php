<?php

namespace App\Helpers;

/**
 * Sumber tunggal konten "Panduan Cepat" untuk seluruh halaman.
 *
 * Panduan harus:
 * - menggunakan bahasa Indonesia yang mudah dipahami;
 * - mengikuti alur bisnis inventory terbaru;
 * - tidak menampilkan istilah internal/kode database;
 * - menjelaskan titik perubahan stok secara jelas.
 */
class PageGuideHelper
{
    public static function all(): array
    {
        return [
            '/dashboard' => [
                'title' => 'Panduan Dasbor',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Memantau kondisi stok, barang yang sedang dipinjam, dan aktivitas gudang.',
                    ]],
                    ['icon' => 'fa-chart-line', 'title' => 'Fungsi Utama', 'items' => [
                        'Melihat ringkasan stok, barang masuk, barang keluar, peminjaman, pengajuan, dan peringatan.',
                        'Tombol Aksi Cepat mengarahkan ke proses yang memang perlu ditindaklanjuti.',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan Penting', 'items' => [
                        'Angka dasbor berasal dari data transaksi yang tersimpan di sistem.',
                        'Peminjaman tidak mengurangi stok fisik; barang yang dipinjam ditampilkan sebagai barang yang sedang dipinjam.',
                    ]],
                    ['icon' => 'fa-user-gear', 'title' => 'Fokus Peran Anda', 'roles' => ['SUPERADMIN'], 'items' => [
                        'Fokus pada pengaturan sistem, pengguna, audit, laporan, dan pemantauan.',
                    ]],
                    ['icon' => 'fa-warehouse', 'title' => 'Fokus Peran Anda', 'roles' => ['ADMIN'], 'items' => [
                        'Menangani operasional gudang: penerimaan, pengeluaran, pengajuan, peminjaman, transfer, pengembalian, opname, dan insiden.',
                    ]],
                    ['icon' => 'fa-user-gear', 'title' => 'Fokus Peran Anda', 'roles' => ['TEKNISI_NOC'], 'items' => [
                        'Mengajukan kebutuhan, meminjam barang, mengembalikan barang, melakukan instalasi, dan melihat riwayat barang yang menjadi tanggung jawab Anda.',
                    ]],
                ],
            ],

            '/search' => [
                'title' => 'Panduan Pencarian',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Mencari barang dan nomor seri dari seluruh data yang boleh Anda lihat.',
                    ]],
                    ['icon' => 'fa-search', 'title' => 'Fungsi Utama', 'items' => [
                        'Gunakan pencarian dan filter secara bersamaan untuk mempersempit hasil.',
                        'Gunakan tombol Reset untuk kembali ke kondisi awal.',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan Penting', 'items' => [
                        'Barang dengan nomor seri dapat ditelusuri sampai nomor serinya.',
                        'Teknisi hanya melihat barang yang memang pernah menjadi tanggung jawabnya.',
                    ]],
                ],
            ],

            '/categories' => [
                'title' => 'Panduan Kategori Barang',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Mengelola kelompok atau klasifikasi barang inventaris.',
                    ]],
                    ['icon' => 'fa-cogs', 'title' => 'Fungsi Utama', 'items' => [
                        'Menambah, mengubah, mengaktifkan, atau menonaktifkan kategori.',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan Penting', 'items' => [
                        'Kategori yang sudah digunakan barang tidak boleh dihapus; gunakan Nonaktifkan.',
                    ]],
                    ['icon' => 'fa-shield-halved', 'title' => 'Aturan Tindakan & Siklus Data', 'items' => [
                        'Hapus hanya tersedia untuk kategori yang belum pernah digunakan.',
                        'Kategori yang sudah dipakai tetap disimpan agar riwayat tidak rusak.',
                        'Hanya Admin yang mengelola kategori; Superadmin bersifat baca saja.',
                    ]],
                ],
            ],

            '/units' => [
                'title' => 'Panduan Satuan',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Mengelola satuan yang digunakan untuk jumlah barang, seperti pcs, unit, atau meter.',
                    ]],
                    ['icon' => 'fa-cogs', 'title' => 'Fungsi Utama', 'items' => [
                        'Menambah dan mengubah satuan beserta status aktifnya.',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan Penting', 'items' => [
                        'Satuan yang sudah dipakai barang tidak boleh dihapus; gunakan Nonaktifkan.',
                    ]],
                    ['icon' => 'fa-shield-halved', 'title' => 'Aturan Tindakan & Siklus Data', 'items' => [
                        'Hanya Admin yang mengelola satuan; Superadmin bersifat baca saja.',
                    ]],
                ],
            ],

            '/warehouses' => [
                'title' => 'Panduan Gudang',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Mengelola gudang tempat barang disimpan dan diproses.',
                    ]],
                    ['icon' => 'fa-route', 'title' => 'Alur', 'items' => [
                        'Gudang menjadi dasar untuk stok, barang masuk, barang keluar, peminjaman, dan transfer.',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan Penting', 'items' => [
                        'Perpindahan antar gudang dilakukan melalui Transfer Gudang, bukan dengan membuat barang masuk dan barang keluar palsu.',
                    ]],
                    ['icon' => 'fa-shield-halved', 'title' => 'Aturan Tindakan & Siklus Data', 'items' => [
                        'Gudang yang sudah dipakai transaksi tidak boleh dihapus.',
                        'Gunakan Nonaktifkan untuk menghentikan pemakaian baru tanpa menghapus riwayat.',
                        'Hanya Admin yang mengelola gudang; Superadmin bersifat baca saja.',
                    ]],
                ],
            ],

            '/locations' => [
                'title' => 'Panduan Lokasi',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Mengelola lokasi fisik atau area penempatan barang di dalam gudang.',
                    ]],
                    ['icon' => 'fa-map-marker-alt', 'title' => 'Fungsi Utama', 'items' => [
                        'Mencatat rak, area, atau posisi barang agar mudah ditemukan secara fisik.',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan Penting', 'items' => [
                        'Lokasi yang sudah digunakan tidak boleh dihapus karena masih terkait dengan riwayat barang.',
                    ]],
                    ['icon' => 'fa-shield-halved', 'title' => 'Aturan Tindakan & Siklus Data', 'items' => [
                        'Hanya Admin yang mengelola lokasi; Superadmin bersifat baca saja.',
                    ]],
                ],
            ],

            '/items' => [
                'title' => 'Panduan Master Barang',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Mengelola identitas barang yang menjadi dasar seluruh transaksi inventory.',
                    ]],
                    ['icon' => 'fa-list-check', 'title' => 'Cara Mengelola Barang', 'items' => [
                        'Buat barang untuk menambah identitas barang baru.',
                        'Ubah untuk memperbarui informasi master tanpa menghapus riwayat.',
                        'Nonaktifkan untuk menghentikan penggunaan barang dalam transaksi baru.',
                        'Aktifkan untuk mengembalikan barang yang sebelumnya dinonaktifkan.',
                    ]],
                    ['icon' => 'fa-tags', 'title' => 'Pelacakan Barang', 'items' => [
                        'Pakai Nomor Seri: setiap unit dilacak satu per satu melalui nomor seri.',
                        'Tanpa Nomor Seri: barang dikelola berdasarkan jumlah.',
                    ]],
                    ['icon' => 'fa-route', 'title' => 'Alur Inventory', 'items' => [
                        'Barang Masuk menambah stok.',
                        'Barang Keluar menjadi titik eksekusi pengeluaran barang dan mengurangi stok fisik untuk pengeluaran biasa.',
                        'Peminjaman melalui Barang Keluar tidak mengurangi stok fisik, tetapi mengubah barang menjadi Barang Dipinjam.',
                        'Transfer Gudang memindahkan stok antar gudang.',
                        'Penyesuaian Stok digunakan untuk koreksi resmi dan dapat diaudit.',
                        'Instalasi hanya boleh memakai barang yang memang sudah dikeluarkan atau sedang menjadi tanggung jawab teknisi.',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan Penting', 'items' => [
                        'Master Barang bukan tempat mengubah jumlah stok.',
                        'Jangan menghapus master barang untuk mengurangi stok; gunakan Barang Keluar atau Penyesuaian Stok sesuai kebutuhan.',
                        'Master Barang yang sudah memiliki riwayat atau stok aktif tidak dapat dihapus sembarangan.',
                    ]],
                ],
            ],

            '/stocks' => [
                'title' => 'Panduan Stok Barang',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Melihat kondisi stok per barang dan gudang.',
                    ]],
                    ['icon' => 'fa-chart-column', 'title' => 'Cara Membaca Stok', 'items' => [
                        'Stok Fisik adalah jumlah aset yang benar-benar dimiliki gudang.',
                        'Tersedia adalah jumlah yang dapat digunakan saat ini.',
                        'Dipinjam adalah jumlah barang yang sedang berada pada teknisi atau peminjam.',
                        'Untuk barang dengan nomor seri, status setiap nomor seri menjadi sumber pemeriksaan paling rinci.',
                    ]],
                    ['icon' => 'fa-search', 'title' => 'Pencarian', 'items' => [
                        'Gunakan pencarian untuk mencari nama barang.',
                        'Buka Detail Barang untuk melihat daftar nomor seri dan mencari nomor seri tertentu.',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan Penting', 'items' => [
                        'Stok Barang adalah halaman monitoring, bukan tempat menghapus stok.',
                        'Barang Keluar mengurangi stok fisik untuk pengeluaran biasa.',
                        'Peminjaman tidak mengurangi stok fisik; jumlahnya tampil sebagai Dipinjam dan mengurangi jumlah Tersedia.',
                        'Transfer Gudang digunakan untuk memindahkan stok antar gudang.',
                        'Penyesuaian Stok digunakan untuk koreksi selisih yang sah.',
                    ]],
                ],
            ],

            '/serials' => [
                'title' => 'Panduan Nomor Seri',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Memantau barang yang menggunakan nomor seri, termasuk status, lokasi, dan pemegangnya.',
                    ]],
                    ['icon' => 'fa-search', 'title' => 'Fungsi Utama', 'items' => [
                        'Cari nomor seri atau nama barang.',
                        'Buka perjalanan untuk melihat riwayat pergerakan nomor seri.',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan Penting', 'items' => [
                        'Satu nomor seri tidak boleh berada pada dua lokasi aktif sekaligus.',
                        'Status nomor seri berubah melalui transaksi resmi: masuk, keluar, pinjam, kembali, transfer, instalasi, insiden, atau penyesuaian.',
                    ]],
                    ['icon' => 'fa-user-gear', 'title' => 'Untuk Teknisi', 'roles' => ['TEKNISI_NOC'], 'items' => [
                        'Yang tampil hanya nomor seri yang memang pernah menjadi tanggung jawab Anda.',
                    ]],
                ],
            ],

            '/timeline' => [
                'title' => 'Panduan Timeline',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Melacak perjalanan barang dari barang masuk sampai kondisi terakhirnya.',
                    ]],
                    ['icon' => 'fa-route', 'title' => 'Cara Menggunakan', 'items' => [
                        'Cari berdasarkan nomor seri atau nama barang, lalu buka perjalanan untuk melihat detail.',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan Penting', 'items' => [
                        'Timeline hanya menampilkan kejadian yang benar-benar berasal dari transaksi.',
                        'Riwayat tidak dihapus ketika barang berpindah, dipinjam, dipasang, dikembalikan, rusak, atau hilang.',
                    ]],
                    ['icon' => 'fa-user-gear', 'title' => 'Untuk Teknisi', 'roles' => ['TEKNISI_NOC'], 'items' => [
                        'Riwayat yang Anda lihat dibatasi pada barang yang pernah menjadi tanggung jawab Anda.',
                    ]],
                ],
            ],

            '/submissions' => [
                'title' => 'Panduan Pengajuan',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Mengelola permintaan kebutuhan barang sebelum barang benar-benar dikeluarkan.',
                    ]],
                    ['icon' => 'fa-route', 'title' => 'Alur & Status', 'items' => [
                        'Teknisi membuat pengajuan → Menunggu Persetujuan → Admin meninjau → Disetujui/Disetujui Sebagian/Ditolak.',
                        'Setelah disetujui, pemenuhan dilakukan melalui Barang Keluar.',
                    ]],
                    ['icon' => 'fa-user-gear', 'title' => 'Untuk Teknisi', 'roles' => ['TEKNISI_NOC'], 'items' => [
                        'Buat pengajuan sesuai kebutuhan dan pantau hasil keputusan Admin.',
                    ]],
                    ['icon' => 'fa-check-double', 'title' => 'Untuk Admin', 'roles' => ['ADMIN', 'SUPERADMIN'], 'items' => [
                        'Periksa jumlah yang diminta, gudang pemenuhan, dan ketersediaan barang sebelum menentukan jumlah yang diberikan.',
                        'Jika pengajuan sudah disetujui, lanjutkan ke Barang Keluar untuk benar-benar mengeluarkan barang.',
                    ]],
                    ['icon' => 'fa-shield-halved', 'title' => 'Aturan Tindakan & Siklus Data', 'items' => [
                        'Pengajuan tidak mengurangi stok.',
                        'Pengajuan yang sudah diputuskan tidak diubah diam-diam; koreksi dilakukan melalui proses baru yang sah.',
                        'Riwayat keputusan tetap tersimpan.',
                    ]],
                ],
            ],

            '/submissions/create' => [
                'title' => 'Panduan Buat Pengajuan',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan', 'items' => [
                        'Mengajukan kebutuhan barang untuk pekerjaan tertentu.',
                    ]],
                    ['icon' => 'fa-list-check', 'title' => 'Cara', 'items' => [
                        'Pilih barang dari stok atau tulis barang manual/custom jika barang yang dibutuhkan belum dipetakan.',
                        'Untuk barang dari stok, pilih gudang pemenuhan agar Admin memeriksa stok di gudang yang benar.',
                        'Isi jumlah dan catatan bila diperlukan, lalu kirim pengajuan.',
                    ]],
                    ['icon' => 'fa-route', 'title' => 'Alur', 'items' => [
                        'Pengajuan dikirim → Menunggu Persetujuan → Admin memutuskan → Pemenuhan melalui Barang Keluar.',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan', 'items' => [
                        'Pengajuan bukan Barang Keluar dan tidak langsung mengurangi stok.',
                        'Barang manual/custom harus dipetakan ke Master Barang oleh Admin sebelum dikeluarkan dari stok.',
                        'Barang yang menggunakan nomor seri akan dipilih nomor serinya saat pemenuhan.',
                    ]],
                ],
            ],

            '/submissions/show' => [
                'title' => 'Panduan Detail Pengajuan',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Meninjau permintaan dan menentukan apa yang benar-benar akan diberikan.',
                    ]],
                    ['icon' => 'fa-list-check', 'title' => 'Untuk Admin', 'roles' => ['ADMIN', 'SUPERADMIN'], 'items' => [
                        'Periksa stok pada gudang pemenuhan yang dipilih.',
                        'Tentukan jumlah yang disetujui; boleh penuh, sebagian, atau nol.',
                        'Barang manual/custom wajib dipetakan ke Master Barang sebelum dipenuhi.',
                        'Jika barang menggunakan nomor seri, nomor seri yang benar-benar diberikan dipilih saat proses Barang Keluar.',
                    ]],
                    ['icon' => 'fa-route', 'title' => 'Alur Setelah Disetujui', 'items' => [
                        'Pengajuan disetujui → Admin memilih barang dan jumlah yang akan dikeluarkan → Proses Barang Keluar → stok berubah satu kali.',
                        'Gudang pemenuhan tetap menjadi acuan; pengeluaran dari gudang lain tidak diperbolehkan.',
                    ]],
                    ['icon' => 'fa-bell', 'title' => 'Notifikasi', 'items' => [
                        'Teknisi menerima pemberitahuan ketika keputusan atas pengajuannya berubah.',
                    ]],
                ],
            ],

            '/incoming' => [
                'title' => 'Panduan Barang Masuk',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Mencatat penerimaan barang agar stok resmi bertambah.',
                    ]],
                    ['icon' => 'fa-route', 'title' => 'Alur', 'items' => [
                        'Barang diterima → diperiksa → dicatat → stok bertambah → riwayat tersimpan.',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan Penting', 'items' => [
                        'Barang yang menggunakan nomor seri wajib dicatat nomor serinya saat diterima.',
                        'Transaksi yang sudah diproses tidak diedit atau dihapus untuk membetulkan histori.',
                    ]],
                    ['icon' => 'fa-shield-halved', 'title' => 'Aturan Tindakan & Siklus Data', 'items' => [
                        'Koreksi kesalahan dilakukan melalui Penyesuaian Stok atau proses koreksi yang sah.',
                        'Hanya Admin yang memproses Barang Masuk; Superadmin bersifat baca saja.',
                    ]],
                ],
            ],

            '/outgoing' => [
                'title' => 'Panduan Barang Keluar',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Menjadi titik eksekusi saat barang benar-benar dikeluarkan dari gudang.',
                    ]],
                    ['icon' => 'fa-route', 'title' => 'Alur', 'items' => [
                        'Pengajuan/Peminjaman disetujui → Admin memeriksa stok → pilih barang/jumlah/nomor seri → Barang Keluar dibuat → status barang diperbarui.',
                    ]],
                    ['icon' => 'fa-box-open', 'title' => 'Dua Dampak Utama', 'items' => [
                        'Pengeluaran biasa: stok fisik berkurang.',
                        'Peminjaman: stok fisik tetap, tetapi barang menjadi Barang Dipinjam dan jumlah Tersedia berkurang.',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan Penting', 'items' => [
                        'Stok tidak boleh berkurang tanpa transaksi Barang Keluar atau Penyesuaian Stok yang sah.',
                        'Barang dengan nomor seri wajib memilih nomor seri yang benar-benar dikeluarkan.',
                    ]],
                ],
            ],

            '/outgoing/create' => [
                'title' => 'Panduan Proses Barang Keluar',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Menjalankan pengeluaran barang yang sudah disetujui.',
                    ]],
                    ['icon' => 'fa-list-check', 'title' => 'Yang Wajib Diperiksa', 'items' => [
                        'Pastikan gudang asal sesuai dengan gudang pemenuhan.',
                        'Pastikan jumlah tidak melebihi jumlah yang sudah disetujui dan stok yang tersedia.',
                        'Untuk barang dengan nomor seri, pilih nomor seri yang benar-benar dikeluarkan.',
                    ]],
                    ['icon' => 'fa-route', 'title' => 'Hasil Proses', 'items' => [
                        'Pengeluaran biasa → stok fisik berkurang.',
                        'Peminjaman → stok fisik tetap dan barang menjadi Barang Dipinjam.',
                    ]],
                ],
            ],

            '/transfers' => [
                'title' => 'Panduan Transfer Gudang',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Memindahkan barang dari satu gudang ke gudang lain.',
                    ]],
                    ['icon' => 'fa-route', 'title' => 'Alur', 'items' => [
                        'Buat transfer → periksa stok gudang asal → kirim → barang dalam perjalanan → terima di gudang tujuan.',
                    ]],
                    ['icon' => 'fa-camera', 'title' => 'Bukti Transfer', 'items' => [
                        'Foto pengiriman dan foto penerimaan wajib disimpan sebagai bukti.',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan Penting', 'items' => [
                        'Barang yang bisa dipilih hanya barang yang tersedia di gudang asal.',
                        'Gudang asal dan gudang tujuan harus berbeda.',
                        'Transfer bukan barang masuk atau barang keluar biasa karena stok berpindah lokasi.',
                    ]],
                    ['icon' => 'fa-shield-halved', 'title' => 'Aturan Tindakan & Siklus Data', 'items' => [
                        'Setelah selesai diterima di gudang tujuan, transfer tidak boleh diedit atau dihapus.',
                    ]],
                ],
            ],

            '/loans' => [
                'title' => 'Panduan Peminjaman',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Mengelola proses barang yang dipinjam teknisi mulai dari permintaan sampai pengembalian.',
                    ]],
                    ['icon' => 'fa-route', 'title' => 'Alur Peminjaman', 'items' => [
                        'Teknisi mengajukan → Admin memeriksa stok → Admin menentukan barang dan jumlah → Barang Keluar untuk Peminjaman → Barang Dipinjam → Pengembalian.',
                    ]],
                    ['icon' => 'fa-box', 'title' => 'Aturan Stok', 'items' => [
                        'Peminjaman tidak mengurangi Stok Fisik.',
                        'Jumlah Tersedia berkurang selama barang masih dipinjam.',
                        'Barang kembali menjadi Tersedia setelah pengembalian diterima dan dinyatakan baik.',
                    ]],
                    ['icon' => 'fa-user-gear', 'title' => 'Untuk Teknisi', 'roles' => ['TEKNISI_NOC'], 'items' => [
                        'Ajukan barang yang dibutuhkan dan ajukan pengembalian ketika barang sudah selesai digunakan.',
                    ]],
                    ['icon' => 'fa-user-check', 'title' => 'Untuk Admin', 'roles' => ['ADMIN', 'SUPERADMIN'], 'items' => [
                        'Admin wajib memeriksa stok sebelum memberikan barang.',
                        'Barang manual/custom wajib dipetakan ke Master Barang.',
                        'Jika barang memakai nomor seri, Admin wajib memilih nomor seri yang benar-benar diberikan.',
                        'Setelah keputusan selesai, proses dilanjutkan melalui Barang Keluar.',
                    ]],
                    ['icon' => 'fa-shield-halved', 'title' => 'Aturan Tindakan & Siklus Data', 'items' => [
                        'Peminjaman yang sudah diserahkan tidak dihapus; koreksi dilakukan melalui Pengembalian atau proses resmi lainnya.',
                        'Riwayat siapa yang meminjam dan kapan barang diberikan tetap tersimpan.',
                    ]],
                ],
            ],

            '/returns' => [
                'title' => 'Panduan Pengembalian',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Mencatat barang yang dikembalikan oleh teknisi dan menentukan kondisi akhirnya.',
                    ]],
                    ['icon' => 'fa-route', 'title' => 'Alur', 'items' => [
                        'Teknisi mengajukan pengembalian → Admin menerima dan memeriksa → kondisi ditentukan → barang kembali tersedia atau masuk proses perbaikan/insiden.',
                    ]],
                    ['icon' => 'fa-check-circle', 'title' => 'Kondisi Barang', 'items' => [
                        'Baik → kembali menjadi Tersedia.',
                        'Rusak → masuk proses kerusakan/perbaikan sesuai kondisi.',
                        'Hilang → diproses melalui Insiden Kerusakan/Kehilangan.',
                    ]],
                    ['icon' => 'fa-lock', 'title' => 'Aturan Penting', 'items' => [
                        'Pengembalian yang sudah diproses tidak dihapus karena menjadi bagian dari riwayat barang.',
                        'Teknisi hanya dapat mengembalikan barang yang memang sedang menjadi tanggung jawabnya.',
                    ]],
                ],
            ],

            '/returns/create' => [
                'title' => 'Panduan Pengajuan Pengembalian',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Mengajukan barang yang sedang dipinjam untuk dikembalikan ke gudang.',
                    ]],
                    ['icon' => 'fa-list-check', 'title' => 'Cara', 'items' => [
                        'Pilih barang yang sedang Anda pegang.',
                        'Untuk barang dengan nomor seri, pilih nomor seri yang benar-benar dikembalikan.',
                        'Isi jumlah pengembalian dan catatan bila diperlukan.',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan Penting', 'items' => [
                        'Teknisi hanya dapat mengembalikan barang yang memang sedang dipinjam oleh dirinya.',
                        'Pemeriksaan kondisi akhir dilakukan oleh Admin.',
                    ]],
                ],
            ],

            '/installations' => [
                'title' => 'Panduan Instalasi',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Mencatat pemasangan barang yang sudah menjadi tanggung jawab teknisi di lokasi pelanggan.',
                    ]],
                    ['icon' => 'fa-route', 'title' => 'Alur', 'items' => [
                        'Barang sudah dikeluarkan kepada teknisi → pilih barang yang sedang dipegang → pasang → catat lokasi → unggah foto bukti.',
                    ]],
                    ['icon' => 'fa-list-check', 'title' => 'Aturan Penting', 'items' => [
                        'Teknisi hanya boleh memasang barang yang memang tersedia dalam tanggung jawabnya.',
                        'Satu formulir instalasi hanya untuk satu pekerjaan pemasangan.',
                        'Untuk barang dengan nomor seri, pilih nomor seri dari barang yang sedang dipegang; tidak mengetik manual.',
                        'Foto bukti instalasi wajib diunggah.',
                    ]],
                    ['icon' => 'fa-shield-halved', 'title' => 'Aturan Tindakan & Siklus Data', 'items' => [
                        'Instalasi yang sudah tercatat tidak dihapus untuk membetulkan histori.',
                        'Koreksi dilakukan melalui proses resmi seperti pengembalian atau insiden sesuai kondisi barang.',
                    ]],
                ],
            ],

            '/incidents' => [
                'title' => 'Panduan Kerusakan / Kehilangan',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Melaporkan dan menindaklanjuti barang yang rusak atau hilang.',
                    ]],
                    ['icon' => 'fa-route', 'title' => 'Alur', 'items' => [
                        'Lapor → bukti → pemeriksaan Admin → keputusan → status barang diperbarui.',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan Penting', 'items' => [
                        'Catat pelapor, lokasi atau pemegang terakhir, bukti, dan kronologi yang jelas.',
                        'Barang rusak tidak otomatis dianggap hilang; kondisi harus diputuskan melalui proses pemeriksaan.',
                    ]],
                    ['icon' => 'fa-shield-halved', 'title' => 'Aturan Tindakan & Siklus Data', 'items' => [
                        'Teknisi melapor, Admin memproses dan menyelesaikan keputusan.',
                        'Riwayat insiden tidak dihapus setelah diproses.',
                    ]],
                ],
            ],

            '/opnames' => [
                'title' => 'Panduan Stock Opname',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Membandingkan jumlah fisik di lapangan dengan jumlah yang tercatat di sistem.',
                    ]],
                    ['icon' => 'fa-route', 'title' => 'Alur', 'items' => [
                        'Hitung fisik → bandingkan dengan sistem → tinjau selisih → setujui hasil → buat Penyesuaian Stok bila diperlukan.',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan Penting', 'items' => [
                        'Stock Opname adalah pemeriksaan; perubahan stok dilakukan melalui Penyesuaian Stok setelah proses yang sah.',
                    ]],
                    ['icon' => 'fa-shield-halved', 'title' => 'Aturan Tindakan & Siklus Data', 'items' => [
                        'Catatan opname yang sudah disetujui menjadi dasar koreksi dan tidak boleh dihapus sembarangan.',
                    ]],
                ],
            ],

            '/adjustments' => [
                'title' => 'Panduan Penyesuaian Stok',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Melakukan koreksi stok secara resmi dan dapat diaudit.',
                    ]],
                    ['icon' => 'fa-list-check', 'title' => 'Sebelum Menyesuaikan', 'items' => [
                        'Pastikan alasan dan sumber selisih jelas.',
                        'Periksa gudang, barang, jumlah, dan bila perlu nomor seri yang terdampak.',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan Penting', 'items' => [
                        'Penyesuaian bukan cara untuk menghapus transaksi lama.',
                        'Setiap perubahan harus mempunyai alasan yang dapat ditelusuri.',
                    ]],
                ],
            ],

            '/reports' => [
                'title' => 'Panduan Laporan',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Membaca dan mencetak laporan operasional serta riwayat perubahan stok.',
                    ]],
                    ['icon' => 'fa-filter', 'title' => 'Fungsi Utama', 'items' => [
                        'Gunakan filter tanggal, gudang, kategori, pengguna, dan pencarian sesuai kebutuhan.',
                        'Gunakan ekspor atau cetak untuk mengambil hasil laporan.',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan Penting', 'items' => [
                        'Laporan bersumber dari data transaksi dan riwayat yang tersimpan di sistem.',
                        'Filter pada laporan harus ikut diterapkan pada hasil ekspor atau cetak.',
                    ]],
                ],
            ],

            '/users' => [
                'title' => 'Panduan Pengguna',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Mengelola akun, peran, dan status pengguna sistem.',
                    ]],
                    ['icon' => 'fa-route', 'title' => 'Alur Pendaftaran', 'items' => [
                        'Pendaftaran → pemeriksaan Admin → disetujui atau ditolak → akun aktif atau tidak aktif.',
                        'Penolakan wajib mempunyai alasan yang jelas.',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan Penting', 'items' => [
                        'Hanya pengguna yang disetujui dan aktif yang dapat masuk ke sistem.',
                        'Superadmin tidak dibuat melalui pendaftaran publik.',
                    ]],
                ],
            ],

            '/labels' => [
                'title' => 'Panduan Label Barang',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Mencetak label identitas fisik barang.',
                    ]],
                    ['icon' => 'fa-route', 'title' => 'Fungsi Utama', 'items' => [
                        'Buka label dari Detail Barang atau Nomor Seri sesuai jenis barang.',
                        'Gunakan Cetak Semua Label untuk mencetak beberapa label sekaligus.',
                        'Label dapat dipindai untuk membuka detail barang atau nomor seri.',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan Penting', 'items' => [
                        'Label hanya menampilkan identitas yang sudah dimiliki barang; tidak membuat identitas baru.',
                    ]],
                ],
            ],

            '/labels/create' => [
                'title' => 'Panduan Cetak Label Massal',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Menyiapkan banyak label sekaligus.',
                    ]],
                    ['icon' => 'fa-list-check', 'title' => 'Fungsi Utama', 'items' => [
                        'Gunakan pencarian dan filter untuk memilih barang yang akan dicetak.',
                        'Barang dengan nomor seri: satu nomor seri menghasilkan satu label.',
                        'Barang tanpa nomor seri: tentukan jumlah label yang dibutuhkan.',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan Penting', 'items' => [
                        'Periksa jumlah label sebelum mencetak agar tidak berlebihan.',
                        'Nomor seri yang sama tidak dicetak dua kali dalam satu proses.',
                    ]],
                ],
            ],

            '/scan' => [
                'title' => 'Panduan Scan Barcode',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Membuka detail barang atau nomor seri dari hasil pemindaian.',
                    ]],
                    ['icon' => 'fa-route', 'title' => 'Fungsi Utama', 'items' => [
                        'Pindai barcode atau QR, atau masukkan hasil scan secara manual lalu tekan Enter.',
                        'Jika data tidak ditemukan, sistem mengarahkan ke pencarian.',
                    ]],
                ],
            ],

            '/notifications' => [
                'title' => 'Panduan Notifikasi',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Menampilkan pembaruan penting yang berkaitan dengan pekerjaan dan akun Anda.',
                    ]],
                    ['icon' => 'fa-route', 'title' => 'Fungsi Utama', 'items' => [
                        'Buka notifikasi untuk menuju transaksi terkait.',
                        'Gunakan Tandai Dibaca atau Tandai Semua Dibaca untuk membersihkan pemberitahuan yang sudah dibaca.',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan Penting', 'items' => [
                        'Notifikasi bersifat pribadi untuk pengguna yang menerimanya.',
                    ]],
                ],
            ],

            '/settings' => [
                'title' => 'Panduan Pengaturan Sistem',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Mengelola konfigurasi umum dan tampilan aplikasi.',
                    ]],
                    ['icon' => 'fa-cogs', 'title' => 'Fungsi Utama', 'items' => [
                        'Mengatur nama aplikasi, logo, warna, tema, dan pengaturan keamanan yang tersedia.',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan Penting', 'items' => [
                        'Perubahan pengaturan dapat langsung berlaku untuk seluruh pengguna.',
                    ]],
                ],
            ],

            '/profile' => [
                'title' => 'Panduan Profil',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Mengelola data diri dan keamanan akun Anda.',
                    ]],
                    ['icon' => 'fa-key', 'title' => 'Keamanan Akun', 'items' => [
                        'Masukkan kata sandi saat ini sebelum menggantinya.',
                        'Kata sandi baru harus memenuhi aturan keamanan aplikasi dan konfirmasi harus sama.',
                    ]],
                ],
            ],

            '/import-export' => [
                'title' => 'Panduan Impor / Ekspor',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Mengelola pemasukan dan pengeluaran data secara massal sesuai format sistem.',
                    ]],
                    ['icon' => 'fa-file-import', 'title' => 'Fungsi Utama', 'items' => [
                        'Gunakan format/template resmi dari sistem.',
                        'Periksa data sebelum mengunggah agar tidak membuat data ganda atau salah.',
                    ]],
                ],
            ],
        ];
    }

    public static function forPath($path)
    {
        $path = '/' . ltrim((string)$path, '/');
        $guides = self::all();

        if (isset($guides[$path])) {
            $guide = $guides[$path];
        } else {
            $matched = null;
            $matchedKey = '';
            foreach ($guides as $key => $g) {
                $prefix = rtrim($key, '/') . '/';
                if (str_starts_with($path, $prefix) && strlen($prefix) > strlen($matchedKey)) {
                    $matched = $g;
                    $matchedKey = $prefix;
                }
            }
            if ($matched === null) {
                return null;
            }
            $guide = $matched;

            if (str_contains($path, '/create') || str_contains($path, '/edit')) {
                $guide['sections'][] = [
                    'icon' => 'fa-lightbulb',
                    'title' => 'Petunjuk Singkat',
                    'items' => [
                        'Isi data yang diperlukan lalu tekan Simpan.',
                        'Gunakan Batal atau Kembali untuk keluar tanpa menyimpan.',
                    ],
                ];
            }
        }

        $guide['sections'] = self::filterByRole($guide['sections'] ?? []);
        $guide['title'] = 'Panduan Cepat';
        return $guide;
    }

    private static function filterByRole(array $sections): array
    {
        $role = $_SESSION['role'] ?? 'GUEST';
        $kept = [];
        foreach ($sections as $section) {
            if (isset($section['roles']) && !in_array($role, (array)$section['roles'], true)) {
                continue;
            }
            if (isset($section['roles_exclude']) && in_array($role, (array)$section['roles_exclude'], true)) {
                continue;
            }
            $kept[] = $section;
        }
        return $kept;
    }
}
