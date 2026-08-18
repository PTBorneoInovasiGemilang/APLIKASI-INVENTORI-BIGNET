<?php

namespace App\Helpers;

/**
 * PageGuideHelper — Sumber tunggal konten "Panduan Cepat" untuk seluruh halaman.
 *
 * Pola: route -> [title, sections]. Dipakai otomatis oleh layout (layouts/app.php)
 * melalui komponen shared components/page_guide.php. Halaman tertentu dapat
 * meng-override dengan mengeset $guide (atau $guide_content legacy) sendiri.
 */
class PageGuideHelper {

    /**
     * Daftar panduan per route. Prefix match: /categories/edit/3 -> /categories.
     */
    public static function all() {
        return [
            '/dashboard' => [
                'title' => 'Panduan Dasbor',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Memantau kondisi stok dan pergerakan gudang secara real-time.',
                    ]],
                    ['icon' => 'fa-chart-line', 'title' => 'Fungsi Utama', 'items' => [
                        'KPI stok, trend 6 bulan, pergerakan stok, dan daftar anomaly (Perhatian).',
                        'Aksi Cepat menyesuaikan peran Anda (Superadmin/Admin/Teknisi/NOC).',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan Penting', 'items' => [
                        'Semua angka berasal dari data transaksi nyata — bukan placeholder.',
                        'Klik kartu/link untuk menindaklanjuti ke modul terkait.',
                    ]],
                    ['icon' => 'fa-user-shield', 'title' => 'Fokus Peran Anda', 'roles' => ['SUPERADMIN'], 'items' => [
                        'Anda fokus monitoring & sistem: Pengguna, Pengaturan, Audit, Laporan, Timeline.',
                        'Modul operasional bersifat baca-saja untuk Anda.',
                    ]],
                    ['icon' => 'fa-warehouse', 'title' => 'Fokus Peran Anda', 'roles' => ['ADMIN'], 'items' => [
                        'Anda menangani operasional gudang: incoming, outgoing, transfer, proses pengajuan, opname, insiden.',
                    ]],
                    ['icon' => 'fa-user-gear', 'title' => 'Fokus Peran Anda', 'roles' => ['TEKNISI_NOC'], 'items' => [
                        'Dashboard personal: pengajuan, pinjaman, instalasi, dan riwayat Anda.',
                    ]],
                ],
            ],

            '/search' => [
                'title' => 'Panduan Pencarian',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Mencari barang secara global: nomor seri, kode, atau nama.',
                    ]],
                    ['icon' => 'fa-search', 'title' => 'Fungsi Utama', 'items' => [
                        'Kombinasikan kolom pencarian dan filter untuk mempersempit hasil.',
                        'Tombol Reset mengembalikan tampilan ke kondisi awal.',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan Penting', 'items' => [
                        'Barang serialized ditelusuri sampai tingkat nomor seri (traceability).',
                        'Teknisi hanya melihat barang yang pernah menjadi tanggung jawabnya.',
                    ]],
                ],
            ],

            '/categories' => [
                'title' => 'Panduan Kategori Barang',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Mengelola klasifikasi barang inventaris.',
                    ]],
                    ['icon' => 'fa-cogs', 'title' => 'Fungsi Utama', 'items' => [
                        'Tambah, ubah, aktif/nonaktifkan kategori. Kode dibuat otomatis.',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan Penting', 'items' => [
                        'Kategori yang sudah dipakai item tidak bisa dihapus; gunakan nonaktif.',
                    ]],
                    ['icon' => 'fa-shield-halved', 'title' => 'Aturan Action & Lifecycle', 'items' => [
                        'Hapus hanya untuk kategori yang belum pernah digunakan; yang sudah dipakai otomatis dilewati.',
                        'Ubah diperbolehkan; kode kategori (KAT-xxxxxx) otomatis dan tidak berubah.',
                        'Hanya ADMIN yang menambah/mengubah/menghapus; SUPERADMIN read-only.',
                    ]],
                ],
            ],

            '/units' => [
                'title' => 'Panduan Satuan',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Mengelola satuan ukuran barang (pcs, meter, unit, dll).',
                    ]],
                    ['icon' => 'fa-cogs', 'title' => 'Fungsi Utama', 'items' => [
                        'Tambah dan ubah satuan beserta status aktifnya.',
                    ]],
                    ['icon' => 'fa-key', 'title' => 'Kode', 'items' => [
                        'Kode satuan dibuat otomatis oleh sistem (format UNT-xxxxxx) dan tidak dapat diubah.',
                    ]],
                    ['icon' => 'fa-shield-halved', 'title' => 'Aturan Action & Lifecycle', 'items' => [
                        'Hapus hanya untuk satuan yang belum pernah digunakan oleh barang; yang sudah dipakai dilewati otomatis.',
                        'Hanya ADMIN yang menambah/mengubah/menghapus; SUPERADMIN read-only.',
                    ]],
                ],
            ],

            '/warehouses' => [
                'title' => 'Panduan Gudang',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Mengelola master gudang tempat barang disimpan.',
                    ]],
                    ['icon' => 'fa-route', 'title' => 'Workflow', 'items' => [
                        'Gudang menjadi referensi untuk stok, incoming, outgoing, dan transfer.',
                    ]],
                    ['icon' => 'fa-key', 'title' => 'Kode', 'items' => [
                        'Kode gudang dibuat otomatis oleh sistem (format GDG-xxxxxx) dan tidak berubah saat nama diubah.',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan Penting', 'items' => [
                        'Transfer antar gudang dicatat sebagai perpindahan, bukan incoming/outgoing palsu.',
                    ]],
                    ['icon' => 'fa-shield-halved', 'title' => 'Aturan Action & Lifecycle', 'items' => [
                        'Hapus hanya untuk gudang yang belum pernah digunakan (stok/transaksi); yang sudah dipakai dilewati otomatis.',
                        'Kode gudang stabil saat nama diubah; status Nonaktif menghentikan pemakaian baru.',
                        'Hanya ADMIN yang menambah/mengubah/menghapus; SUPERADMIN read-only.',
                    ]],
                ],
            ],

            '/locations' => [
                'title' => 'Panduan Lokasi',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Mengelola detail lokasi/penempatan di dalam gudang.',
                    ]],
                    ['icon' => 'fa-map-marker-alt', 'title' => 'Fungsi Utama', 'items' => [
                        'Catat posisi spesifik barang (rak/area) untuk memudahkan pencarian fisik.',
                    ]],
                    ['icon' => 'fa-key', 'title' => 'Kode', 'items' => [
                        'Kode lokasi dibuat otomatis oleh sistem (format LOK-xxxxxx, unik global) dan tidak dapat diubah.',
                    ]],
                    ['icon' => 'fa-shield-halved', 'title' => 'Aturan Action & Lifecycle', 'items' => [
                        'Hapus hanya untuk lokasi yang belum pernah digunakan; yang sudah dipakai dilewati otomatis.',
                        'Hanya ADMIN yang menambah/mengubah/menghapus; SUPERADMIN read-only.',
                    ]],
                ],
            ],

            '/items' => [
                'title' => 'Panduan Master Barang',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Mengelola identitas barang: kode, nama, kategori, satuan, serialized/non.',
                    ]],
                    ['icon' => 'fa-toggle-on', 'title' => 'Status Barang', 'items' => [
                        'Aktif → barang dapat digunakan dalam transaksi baru sesuai aturan sistem.',
                        'Nonaktif → barang tidak dapat digunakan dalam transaksi baru, tetapi data dan histori tetap tersimpan.',
                    ]],
                    ['icon' => 'fa-list-check', 'title' => 'Cara Mengelola Barang', 'items' => [
                        'Buat Barang untuk membuat master barang baru (kode dibuat otomatis).',
                        'Ubah untuk memperbarui informasi master (tidak mengubah status).',
                        'Nonaktifkan jika barang tidak ingin digunakan untuk transaksi baru.',
                        'Barang tidak dapat dinonaktifkan jika masih memiliki stok fisik atau serial aktif.',
                        'Aktifkan untuk mengembalikan barang Nonaktif menjadi Aktif.',
                        'Hapus hanya untuk Master Barang yang belum pernah digunakan atau belum memiliki riwayat/relasi.',
                    ]],
                    ['icon' => 'fa-route', 'title' => 'Workflow', 'items' => [
                        'Barang serialized wajib dicatat nomor serinya saat pergerakan.',
                        'Barang non-serialized dikelola berdasarkan quantity.',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Perbedaan Penting', 'items' => [
                        'Nonaktifkan ≠ Hapus — Nonaktif menghentikan pemakaian baru, Hapus menghilangkan record.',
                        'Hapus ≠ Mengurangi Stok — mengurangi stok dilakukan melalui Barang Keluar.',
                        'Memindahkan stok dilakukan melalui Transfer Gudang.',
                        'Koreksi stok dilakukan melalui Penyesuaian Stok.',
                        'Stok minimum (stok_minimum) memicu peringatan stok rendah.',
                        'Nomor seri tidak boleh ter-assign ganda (double assignment).',
                    ]],
                ],
            ],

            '/stocks' => [
                'title' => 'Panduan Stok Barang',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Melihat kondisi stok terkini per barang dan gudang.',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan Penting', 'items' => [
                        'Stok Barang adalah halaman monitoring stok.',
                        'Stok Barang bukan tempat menghapus Master Barang.',
                        'Jangan menghapus Master Barang dari halaman Stok.',
                        'Status Master Barang dikelola melalui Master Data → Master Barang.',
                        'Stok berkurang melalui transaksi Barang Keluar, bukan dengan menghapus stok.',
                        'Perpindahan antar gudang menggunakan Transfer Gudang.',
                        'Koreksi selisih menggunakan Penyesuaian Stok.',
                        'Gunakan "Lihat" untuk membuka detail Inventory 360°.',
                        'Invariant: Ending = Beginning + In - Out ± Transfer.',
                    ]],
                ],
            ],

            '/serials' => [
                'title' => 'Panduan Nomor Seri',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Memantau nomor seri aset: status, lokasi, dan pemegang saat ini.',
                    ]],
                    ['icon' => 'fa-barcode', 'title' => 'Fungsi Utama', 'items' => [
                        'Cari serial, lihat riwayat (timeline), dan telusuri pergerakannya.',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan Penting', 'items' => [
                        'Satu serial tidak boleh berada di dua lokasi aktif sekaligus.',
                    ]],
                    ['icon' => 'fa-user-gear', 'title' => 'Untuk Teknisi', 'roles' => ['TEKNISI_NOC'], 'items' => [
                        'Hanya nomor seri yang pernah menjadi tanggung jawab Anda yang tampil.',
                    ]],
                    ['icon' => 'fa-lock', 'title' => 'Aturan Action & Lifecycle', 'items' => [
                        'Nomor Seri tidak memiliki Delete bebas: status berubah hanya melalui transaksi resmi (masuk, keluar, transfer, pinjam, kembali, instalasi, insiden, penyesuaian).',
                        'Riwayat pergerakan serial tetap utuh dan dapat ditelusuri lewat Timeline.',
                    ]],
                ],
            ],

            '/timeline' => [
                'title' => 'Panduan Timeline',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Melacak perjalanan barang dari hulu ke hilir (serial & bulk).',
                    ]],
                    ['icon' => 'fa-route', 'title' => 'Workflow', 'items' => [
                        'Ketik SN / kode / nama, lalu klik "Lihat Perjalanan" untuk detail level 2.',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan Penting', 'items' => [
                        'Timeline hanya memuat event nyata dari transaksi sumber.',
                        'Teknisi hanya melihat barang yang pernah ia pegang.',
                    ]],
                    ['icon' => 'fa-user-gear', 'title' => 'Untuk Teknisi', 'roles' => ['TEKNISI_NOC'], 'items' => [
                        'Riwayat yang tampil adalah perjalanan barang yang pernah Anda pegang.',
                    ]],
                ],
            ],

            '/submissions' => [
                'title' => 'Panduan Pengajuan',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Mengelola pengajuan kebutuhan barang oleh teknisi.',
                    ]],
                    ['icon' => 'fa-route', 'title' => 'Workflow & Status', 'items' => [
                        'Draft → Submit → Pending Approval → Approved/Rejected → Fulfilled.',
                        'Penolakan wajib menyimpan aktor, waktu, dan alasan.',
                    ]],
                    ['icon' => 'fa-user-gear', 'title' => 'Untuk Teknisi', 'roles' => ['TEKNISI_NOC'], 'items' => [
                        'Buat pengajuan Anda lalu pantau statusnya di sini.',
                    ]],
                    ['icon' => 'fa-check-double', 'title' => 'Untuk Admin', 'roles' => ['ADMIN', 'SUPERADMIN'], 'items' => [
                        'Tinjau pengajuan: setujui kuantitas atau tolak dengan alasan wajib.',
                    ]],
                    ['icon' => 'fa-shield-halved', 'title' => 'Aturan Action & Lifecycle', 'items' => [
                        'Teknisi hanya dapat mengelola pengajuan miliknya sendiri — tidak bisa mengubah milik teknisi lain.',
                        'Sebelum diputuskan Admin, pengajuan bersifat pending dan tidak memiliki tombol Edit/Delete (ubah = ajukan ulang).',
                        'Setelah APPROVED / PARTIAL_APPROVED / REJECTED, record terkunci: tidak ada Edit dan tidak ada Delete.',
                        'Histori keputusan (aktor, waktu, alasan penolakan) tidak dapat dihapus.',
                    ]],
                ],
            ],

            '/submissions/create' => [
                'title' => 'Panduan Buat Pengajuan',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan', 'items' => [
                        'Mengajukan kebutuhan material untuk pekerjaan.',
                    ]],
                    ['icon' => 'fa-list-check', 'title' => 'Cara', 'items' => [
                        'Pilih sumber barang: Dari Stok (Master Data) atau Manual (barang ad-hoc).',
                        'Untuk barang Dari Stok, wajib memilih Gudang Pemenuhan — stok akan dicek dari gudang tersebut saat persetujuan dan pemenuhan.',
                        'Pilih barang, masukkan jumlah, tambahkan catatan item jika perlu.',
                        'Isi Keperluan Pengajuan, lalu kirim pengajuan.',
                    ]],
                    ['icon' => 'fa-route', 'title' => 'Workflow', 'items' => [
                        'Pengajuan dikirim → status Menunggu Persetujuan (PENDING).',
                        'Admin melakukan persetujuan (setujui sebagian / tolak dengan alasan wajib).',
                        'Setelah disetujui, proses pemenuhan dilakukan oleh gudang.',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan', 'items' => [
                        'Pengajuan BUKAN Barang Keluar — tidak langsung mengurangi stok.',
                        'STOCK dan MANUAL bisa digabung dalam satu pengajuan.',
                        'Barang berseri wajib memilih nomor seri saat pemenuhan.',
                    ]],
                ],
            ],

            '/submissions/show' => [
                'title' => 'Panduan Detail Pengajuan',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Meninjau dan merespons pengajuan barang.',
                    ]],
                    ['icon' => 'fa-list-check', 'title' => 'Fungsi Utama (Admin)', 'roles' => ['ADMIN'], 'items' => [
                        'Tentukan Qty Disetujui per item: penuh (Setujui Semua), sebagian (kurangi angka), atau 0 (Tolak).',
                        'Qty disetujui tidak boleh melebihi qty diminta dan tidak boleh negatif.',
                        'Penolakan item (Qty 0) wajib mencantumkan alasan.',
                        'Sistem mengecek STOK AKTUAL di backend saat keputusan — dicek per Gudang Pemenuhan (master_item_id + warehouse_id), bukan gabungan semua gudang. Jika stok tidak cukup, proses ditolak dengan pesan “Stok Tidak Mencukupi”.',
                    ]],
                    ['icon' => 'fa-route', 'title' => 'Workflow & Status', 'items' => [
                        'Teknisi mengirim pengajuan → status Menunggu (PENDING) → Admin memberi keputusan.',
                        'Detail menampilkan Gudang Pemenuhan + stok tersedia di gudang tersebut per item.',
                        'Keputusan Admin: APPROVED / PARTIAL_APPROVED / REJECTED — tercatat aktor + waktu + alasan di riwayat.',
                        'Setelah APPROVED/PARTIAL_APPROVED, Admin melanjutkan ke Barang Keluar (tombol “Proses Barang Keluar”) — gudang otomatis mengikuti Gudang Pemenuhan pengajuan; gudang berbeda ditolak.',
                        'Barang Keluar → stok berkurang sesuai qty yang diproses, tepat satu kali (master_item_id + warehouse_id yang sama).',
                        'Jika stok tidak mencukupi saat proses Barang Keluar, transaksi dibatalkan dan stok tidak berubah.',
                    ]],
                    ['icon' => 'fa-bell', 'title' => 'Notifikasi', 'items' => [
                        'Teknisi mengirim pengajuan → Admin menerima notifikasi “Pengajuan Barang Baru”.',
                        'Keputusan Admin → Teknisi pemilik menerima notifikasi hasil (disetujui / sebagian / ditolak + alasan).',
                    ]],
                ],
            ],

            '/incoming' => [
                'title' => 'Panduan Barang Masuk',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Gunakan halaman ini untuk menambah stok melalui transaksi penerimaan barang.',
                    ]],
                    ['icon' => 'fa-route', 'title' => 'Workflow', 'items' => [
                        'Source/Vendor → Receive → Validate → Inventory Increase → Timeline.',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan Penting', 'items' => [
                        'Barang serialized wajib mencatat nomor seri pada penerimaan.',
                    ]],
                    ['icon' => 'fa-lock', 'title' => 'Aturan Action & Lifecycle', 'items' => [
                        'Barang Masuk yang sudah diproses memengaruhi stok & ledger → immutable: tidak ada Edit/Delete.',
                        'Koreksi salah input tidak dilakukan dengan menghapus histori; gunakan Penyesuaian Stok / workflow koreksi yang sah.',
                        'Hanya ADMIN yang dapat membuat Barang Masuk; SUPERADMIN read-only.',
                    ]],
                ],
            ],

            '/outgoing' => [
                'title' => 'Panduan Barang Keluar',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Gunakan halaman ini untuk mengurangi stok melalui transaksi pengeluaran barang.',
                        'Untuk workflow teknisi, gunakan Pengajuan Barang → persetujuan → Barang Keluar sesuai rule existing.',
                    ]],
                    ['icon' => 'fa-route', 'title' => 'Workflow', 'items' => [
                        'Approved Request → Validate Availability → Issue → Inventory Decrease → Custody.',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan Penting', 'items' => [
                        'Stok tidak pernah berkurang tanpa transaksi penjelas.',
                    ]],
                    ['icon' => 'fa-lock', 'title' => 'Aturan Action & Lifecycle', 'items' => [
                        'Barang Keluar yang sudah diproses mengurangi stok & mengubah kepemilikan/serial → immutable: tidak ada Edit/Delete.',
                        'Koreksi tidak dilakukan dengan menghapus transaksi; gunakan workflow koreksi/penyesuaian yang sah.',
                        'Hanya ADMIN yang dapat membuat Barang Keluar; SUPERADMIN read-only.',
                    ]],
                ],
            ],

            '/outgoing/create' => [
                'title' => 'Panduan Pemenuhan Barang',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Mengeluarkan barang sesuai pengajuan yang disetujui.',
                    ]],
                    ['icon' => 'fa-boxes-stacked', 'title' => 'Fungsi Utama', 'items' => [
                        'Pilih gudang asal dengan benar.',
                        'Barang berseri (SN) wajib memilih nomor seri spesifik yang diberikan.',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan Penting', 'items' => [
                        'Pengeluaran tidak boleh melebihi Sisa Qty yang belum terpenuhi.',
                        'Stok tidak berkurang tanpa transaksi penjelas.',
                    ]],
                ],
            ],

            '/transfers' => [
                'title' => 'Panduan Transfer Gudang',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Gunakan halaman ini untuk memindahkan stok dari satu gudang ke gudang lain.',
                    ]],
                    ['icon' => 'fa-route', 'title' => 'Workflow & Status', 'items' => [
                        'Initiate → Validate Source → Dispatch → In Transit → Receive at Destination.',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan Penting', 'items' => [
                        'Transfer adalah perpindahan, bukan incoming/outgoing palsu; ledger mencegah double counting.',
                    ]],
                    ['icon' => 'fa-shield-halved', 'title' => 'Aturan Action & Lifecycle', 'items' => [
                        'Status PENDING → IN_TRANSIT → COMPLETED.',
                        'Terima hanya tersedia saat IN_TRANSIT dan hanya untuk ADMIN; SUPERADMIN read-only.',
                        'Setelah COMPLETED, transfer immutable — tidak ada Edit/Delete karena stok sudah berpindah.',
                    ]],
                ],
            ],

            '/loans' => [
                'title' => 'Panduan Peminjaman',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Mengelola peminjaman barang kepada teknisi (custody).',
                    ]],
                    ['icon' => 'fa-route', 'title' => 'Workflow & Status', 'items' => [
                        'Available → Borrow/Assign → Custody → Return.',
                        'Status: Dipinjam / Kembali Sebagian / Dikembalikan.',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan Penting', 'items' => [
                        'Batas waktu (due date) memicu status terlambat bila belum dikembalikan.',
                    ]],
                    ['icon' => 'fa-user-gear', 'title' => 'Untuk Teknisi', 'roles' => ['TEKNISI_NOC'], 'items' => [
                        'Lihat barang yang sedang Anda pinjam beserta tenggat pengembalian.',
                    ]],
                    ['icon' => 'fa-shield-halved', 'title' => 'Aturan Action & Lifecycle', 'items' => [
                        'Peminjaman yang sudah diberikan memengaruhi status serial & holder → tidak ada Delete.',
                        'Perubahan hanya melalui workflow resmi: Pengembalian untuk mengembalikan aset.',
                        'Teknisi hanya melihat peminjaman miliknya; Admin/Admin menangani proses.',
                    ]],
                ],
            ],

            '/returns' => [
                'title' => 'Panduan Pengembalian',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Mencatat pengembalian barang pinjaman beserta kondisinya.',
                    ]],
                    ['icon' => 'fa-route', 'title' => 'Workflow', 'items' => [
                        'Custody → Return → Receive/Inspect → Condition Decision → Next Lifecycle.',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan Penting', 'items' => [
                        'Hasil inspeksi menentukan: kembali ke stok, repair, atau damage/lost.',
                    ]],
                    ['icon' => 'fa-lock', 'title' => 'Aturan Action & Lifecycle', 'items' => [
                        'Pengembalian yang sudah diproses Admin mengubah status serial/holder/stok → immutable: tidak ada Edit/Delete.',
                        'Teknisi membuat permintaan pengembalian; hanya ADMIN yang memproses (Terima/inspeksi).',
                        'Histori peminjaman, serial, dan pemegang tetap utuh.',
                    ]],
                ],
            ],

            '/returns/create' => [
                'title' => 'Panduan Pengembalian',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Mengembalikan inventaris yang tidak terpakai atau rusak ke gudang.',
                    ]],
                    ['icon' => 'fa-clipboard-check', 'title' => 'Fungsi Utama', 'items' => [
                        'Tentukan status akhir SN: AVAILABLE (stok normal), BROKEN (rusak), atau REPAIR.',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan Penting', 'items' => [
                        'Teknisi hanya dapat mengembalikan SN yang sedang dipegangnya.',
                        'Setelah disubmit, tanggung jawab aset Teknisi berkurang.',
                    ]],
                ],
            ],

            '/installations' => [
                'title' => 'Panduan Instalasi',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Mencatat pemasangan barang di lapangan oleh teknisi.',
                    ]],
                    ['icon' => 'fa-route', 'title' => 'Workflow', 'items' => [
                        'Issue/Assignment → Installation → Installed State → Evidence (foto).',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan Penting', 'items' => [
                        'Status barang berubah menjadi Terpasang dengan lokasi terpasang.',
                    ]],
                    ['icon' => 'fa-user-gear', 'title' => 'Untuk Teknisi', 'roles' => ['TEKNISI_NOC'], 'items' => [
                        'Catat instalasi & unggah foto sebagai bukti (evidence).',
                    ]],
                    ['icon' => 'fa-shield-halved', 'title' => 'Aturan Action & Lifecycle', 'items' => [
                        'Instalasi yang sudah tercatat mengubah status serial & lokasi terpasang → tidak ada Delete.',
                        'Teknisi hanya mencatat instalasi untuk data yang memang dipegangnya.',
                        'Koreksi pasca-instalasi melalui workflow resmi (pengembalian/insiden), bukan hapus histori.',
                    ]],
                ],
            ],

            '/incidents' => [
                'title' => 'Panduan Damage / Lost',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Melaporkan dan menindaklanjuti insiden kerusakan/kehilangan aset.',
                    ]],
                    ['icon' => 'fa-route', 'title' => 'Workflow', 'items' => [
                        'Report → Evidence → Review → Damage/Lost State → Resolution.',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan Penting', 'items' => [
                        'Wajib mencatat pelapor, bukti fisik, pemegang/lokasi terakhir, dan timeline.',
                    ]],
                    ['icon' => 'fa-shield-halved', 'title' => 'Aturan Action & Lifecycle', 'items' => [
                        'Insiden OPEN dapat diproses (INVESTIGATING → RESOLVED/REJECTED) oleh ADMIN.',
                        'Setelah RESOLVED/REJECTED, record terkunci — histori keputusan tidak dihapus.',
                        'Teknisi melapor, Admin memproses; tidak ada Delete transaksi insiden.',
                    ]],
                ],
            ],

            '/opnames' => [
                'title' => 'Panduan Stock Opname',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Membandingkan stok fisik vs sistem dan meninjau selisih (variance).',
                    ]],
                    ['icon' => 'fa-route', 'title' => 'Workflow', 'items' => [
                        'Count → Compare System vs Physical → Variance → Review → Approved Adjustment.',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan Penting', 'items' => [
                        'Stock Opname ≠ Adjustment: penyesuaian hanya setelah review/approval.',
                    ]],
                    ['icon' => 'fa-shield-halved', 'title' => 'Aturan Action & Lifecycle', 'items' => [
                        'ADMIN membuat opname; variance disetujui oleh SUPERADMIN sesuai rule existing.',
                        'Opname yang sudah disetujui menjadi dasar Stock Adjustment — jangan dihapus agar adjustment tidak orphan.',
                        'Draft dapat dibatalkan; setelah approval, record immutable.',
                    ]],
                ],
            ],

            '/adjustments' => [
                'title' => 'Panduan Penyesuaian Stok',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Gunakan halaman ini untuk koreksi stok berdasarkan proses stock opname/adjustment yang sah.',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan Penting', 'items' => [
                        'Setiap penyesuaian wajib memiliki reason/reference yang dapat diaudit.',
                    ]],
                    ['icon' => 'fa-lock', 'title' => 'Aturan Action & Lifecycle', 'items' => [
                        'Penyesuaian yang sudah diposting mengubah stok/ledger → immutable: tidak ada Edit/Delete.',
                        'Koreksi lanjutan dilakukan dengan penyesuaian baru yang sah, bukan menghapus histori.',
                        'Hanya ADMIN yang membuat penyesuaian; SUPERADMIN read-only.',
                    ]],
                ],
            ],

            '/reports' => [
                'title' => 'Panduan Laporan',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Menelusuri data operasional & audit dalam bentuk laporan.',
                    ]],
                    ['icon' => 'fa-filter', 'title' => 'Fungsi Utama', 'items' => [
                        'Gunakan filter tanggal/gudang/kategori/user, pencarian, dan export CSV.',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan Penting', 'items' => [
                        'Laporan menampilkan data nyata; export CSV menghormati filter yang dipilih.',
                    ]],
                ],
            ],

            '/users' => [
                'title' => 'Panduan Pengguna',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Mengelola akun, role, dan status user sistem.',
                    ]],
                    ['icon' => 'fa-route', 'title' => 'Workflow & Status', 'items' => [
                        'Created → Pending Approval → Approved/Rejected → Active/Inactive.',
                        'Klik "Tinjau" untuk approve/reject pendaftaran (alasan wajib saat menolak).',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan Penting', 'items' => [
                        'Hanya user APPROVED & aktif yang dapat login.',
                        'Superadmin tidak bisa dibuat melalui pendaftaran publik.',
                    ]],
                ],
            ],

            '/labels' => [
                'title' => 'Panduan Label Barcode',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Mencetak label identitas barang (Code 128 + QR) untuk pelabelan fisik.',
                    ]],
                    ['icon' => 'fa-route', 'title' => 'Fungsi Utama', 'items' => [
                        'Satu label: buka dari Nomor Seri / Master Barang (ikon barcode).',
                        'Batch: gunakan tombol \"Cetak Semua Label\" di halaman Nomor Seri.',
                        'Klik Cetak untuk print, QR dapat dipindai aplikasi QR apapun.',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan Penting', 'items' => [
                        'Identitas memakai kode barang & serial number existing — tidak ada identitas baru.',
                        'Scanner membaca kode ke halaman Scan untuk membuka detail.',
                    ]],
                ],
            ],

            '/labels/create' => [
                'title' => 'Panduan Cetak Label Massal',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Menyiapkan cetakan label massal (banyak sekaligus, bukan satu per satu).',
                    ]],
                    ['icon' => 'fa-route', 'title' => 'Fungsi Utama', 'items' => [
                        'Filter pencarian/kategori/gudang/status untuk mempersempit daftar.',
                        'SN berseri: centang baris → 1 SN = 1 label.',
                        'Item non-serialized: centang + tentukan jumlah label (1/5/10/custom).',
                        'Tombol Pilih Semua Tampil / Bersihkan untuk seleksi cepat.',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan Penting', 'items' => [
                        'Jumlah label ditampilkan sebelum dicetak — pastikan sudah sesuai.',
                        'Duplikat SN otomatis dibuang (tidak tercetak dua kali).',
                        'Setiap cetakan dicatat di Audit Log (aktor, waktu, jumlah).',
                    ]],
                ],
            ],

            '/scan' => [
                'title' => 'Panduan Scan Barcode',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Membuka detail barang/serial dari hasil scan atau input manual.',
                    ]],
                    ['icon' => 'fa-route', 'title' => 'Fungsi Utama', 'items' => [
                        'Scan kode barang / nomor seri lalu Enter — langsung ke detail.',
                        'Scanner genggam mengetik otomatis; di ponsel ketik manual.',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan Penting', 'items' => [
                        'Jika kode tidak ditemukan, sistem mengarahkan ke hasil pencarian.',
                    ]],
                ],
            ],

            '/notifications' => [
                'title' => 'Panduan Notifikasi',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Menampilkan pembaruan penting untuk akun dan pekerjaan Anda.',
                    ]],
                    ['icon' => 'fa-route', 'title' => 'Fungsi Utama', 'items' => [
                        'Notifikasi bersifat pribadi — hanya milik Anda.',
                        'Klik \"Buka\" untuk menuju halaman terkait, atau \"Tandai dibaca\".',
                        '\"Tandai Semua Dibaca\" membersihkan badge di lonceng header.',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan Penting', 'items' => [
                        'Login/logout tidak menghasilkan notifikasi bisnis.',
                        'Notifikasi tidak dapat dilihat/diubah user lain.',
                    ]],
                ],
            ],

            '/settings' => [
                'title' => 'Panduan Pengaturan Sistem',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Mengelola konfigurasi global aplikasi dan branding.',
                    ]],
                    ['icon' => 'fa-cogs', 'title' => 'Fungsi Utama', 'items' => [
                        'Umum (nama aplikasi), Branding (logo/warna/tema), Keamanan (sesi).',
                    ]],
                    ['icon' => 'fa-exclamation-circle', 'title' => 'Aturan Penting', 'items' => [
                        'Perubahan langsung diterapkan ke seluruh pengguna.',
                    ]],
                ],
            ],

            '/profile' => [
                'title' => 'Panduan Profil',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Mengelola data diri dan keamanan akun Anda.',
                    ]],
                    ['icon' => 'fa-key', 'title' => 'Ubah Password', 'items' => [
                        'Wajib mengisi password saat ini untuk mengganti password.',
                        'Password minimal 6 karakter dan konfirmasi harus cocok.',
                    ]],
                ],
            ],

            '/import-export' => [
                'title' => 'Panduan Import / Export',
                'sections' => [
                    ['icon' => 'fa-bullseye', 'title' => 'Tujuan Halaman', 'items' => [
                        'Mengimpor data massal dan mengekspor template/master data.',
                    ]],
                    ['icon' => 'fa-file-import', 'title' => 'Fungsi Utama', 'items' => [
                        'Gunakan template resmi; pastikan format kolom sesuai sebelum upload.',
                    ]],
                ],
            ],
        ];
    }

    /**
     * Ambil panduan untuk sebuah path. Exact match dulu, lalu prefix terpanjang.
     * Path form (/create, /edit) otomatis mendapat micro-guidance tambahan.
     */
    public static function forPath($path) {
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

            // Micro-guidance untuk halaman form
            if (str_contains($path, '/create') || str_contains($path, '/edit')) {
                $guide['sections'][] = [
                    'icon' => 'fa-lightbulb',
                    'title' => 'Micro-guidance',
                    'items' => [
                        'Isi formulir lalu tekan Simpan. Field bertanda * wajib diisi.',
                        'Gunakan tombol Batal/Kembali untuk membatalkan tanpa menyimpan.',
                    ],
                ];
            }
        }

        // Filter section sesuai role (roles = hanya untuk role tsb; roles_exclude = bukan untuk role tsb).
        $guide['sections'] = self::filterByRole($guide['sections'] ?? []);

        // Judul distandarkan agar seluruh modul memakai pola yang sama.
        $guide['title'] = 'Panduan Cepat';
        return $guide;
    }

    /**
     * Filter section panduan berdasarkan role sesi saat ini.
     * Section dapat mendeklarasikan 'roles' (hanya role ini) atau 'roles_exclude' (kecuali role ini).
     */
    private static function filterByRole(array $sections) {
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
