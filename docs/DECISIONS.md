# BIGNET LOCKED DECISIONS

> Hanya masukkan keputusan yang benar-benar sudah diputuskan Product Owner.

## D-001 — Core Purpose
Aplikasi adalah inventory/accountability system untuk mencegah barang hilang/sulit dilacak.

## D-002 — Traceability
Setiap pergerakan relevan harus dapat ditelusuri.

## D-003 — Timestamp
Tanggal/waktu adalah bagian penting dari transaksi dan event.

## D-004 — Evidence
Evidence/foto adalah bagian dari accountability pada workflow yang relevan.

## D-005 — Timeline
Timeline adalah fitur inti, terutama untuk serialized asset.

## D-006 — Role Separation
Super Admin, Admin/Warehouse, dan Teknisi memiliki tanggung jawab berbeda.

## D-007 — Technician as Requester
Teknisi adalah requester utama kebutuhan barang lapangan.

## D-008 — Admin as Warehouse Operator
Admin menangani operasi inventory/gudang dan proses yang menjadi kewenangannya.

## D-009 — UI Consistency
Tabel/form/action harus konsisten dan tidak boleh ada dead menu/button.

## D-010 — No Guessing
AI Agent tidak boleh menebak business logic.

## D-011 — PRD Authority
Requirement locked lebih tinggi daripada implementation.

## D-012 — Done Means Verified
Fitur tidak dianggap selesai hanya karena route/HTTP 200/page render.

## D-013 — Damage & Lost Accountability
Damage/Lost ditetapkan sebagai *capability accountability* wajib.
Setiap insiden harus memiliki laporan (report), bukti (evidence), pemegang/lokasi terakhir (holder/location), timeline, dan jejak audit.

## D-014 — Stock Opname Workflow
Stock Opname adalah fitur wajib yang membandingkan fisik vs sistem (*physical vs system stock*).
Harus ada mekanisme review *variance* (selisih).
*Adjustment* (penyesuaian stok) hanya dilakukan setelah proses review/approval selesai. Stock Opname bukan sekadar sinonim dari menu *Adjustment* biasa.

## D-015 — Superadmin-Created User Langsung Aktif
User yang dibuat oleh SUPERADMIN langsung berstatus **APPROVED + ACTIVE** dan dapat langsung login — Superadmin sudah melakukan verifikasi saat membuat, sehingga tidak ada loop persetujuan ulang.
Registrasi publik (public register) TETAP mengikuti alur Pending Approval → review Superadmin → Approve/Reject.
Jika memakai kata sandi sementara, kata sandi ditampilkan sekali dan user wajib mengganti saat login pertama.

## D-016 — Internal Password Recovery
Recovery kata sandi dikelola **Superadmin** (Users → Reset Password → kata sandi sementara sekali-tampil → audit).
TIDAK ada alur email reset / mail server / public reset-token. Alur forgot-password lama (`password_reset_tokens`, token SHA-256, kedaluwarsa 1 jam) dihapus sebagai dead code (tabel di-drop).
Halaman Lupa Kata Sandi hanya mengarahkan user untuk menghubungi Superadmin.

## D-017 — Bulk Barcode / Label Printing
Cetak label/barcode wajib mendukung seleksi massal: pilih per SN, **Pilih Semua Tampil**, **Bersihkan**, filter kategori/gudang/status/pencarian, jumlah label untuk item non-serialized (1–100), preview jumlah, lembar cetak A4 multi-label, proteksi duplikat (SN duplikat dibuang, qty digabung), audit cetak (`LABEL_PRINT`: aktor, waktu, jumlah, ringkasan), dan otorisasi peran (SUPERADMIN/ADMIN). Tujuan: gudang tidak perlu mencetak barcode satu per satu.
