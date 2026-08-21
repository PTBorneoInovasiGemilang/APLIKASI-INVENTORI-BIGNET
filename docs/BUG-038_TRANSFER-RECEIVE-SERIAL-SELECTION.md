# BUG-038 — Transfer Receive Serial Selection

## Date
2026-08-21

## Symptom
Pada halaman **Konfirmasi Penerimaan Barang**, tombol **Pilih Nomor Seri** dan **Pilih Otomatis** tidak menemukan/memilih serial number yang sedang dalam pengiriman.

## Root Cause
`show-v2.php` membentuk `serialData` menggunakan `array_map()` terhadap `$items`. Array hasil tersebut memakai index baris (`0, 1, ...`), sementara fungsi JavaScript `listFor(id)` mengakses data menggunakan `transfer_item_id`. Akibatnya lookup `serialData[String(id)]` kosong walaupun `transfer_item_serials` berisi serial yang valid.

## Fix
- `serialData` sekarang dibentuk sebagai object/map dengan key `transfer_item_id`.
- `listFor(id)` tetap menggunakan `serialData[String(id)]` sehingga lookup tepat ke item transfer.
- Pemilihan manual tetap membatasi jumlah SN sesuai quantity penerimaan.
- Pemilihan otomatis mengambil SN `IN_TRANSIT` paling awal sesuai quantity.
- Escaping nomor seri pada handler checkbox diperketat agar serial yang mengandung apostrophe tidak merusak inline handler.
- Hidden `receive_sns[item_id]` tetap menjadi source POST yang dipakai `TransferController::receive()`.

## Verification
- Source fix committed to GitHub.
- `node --check` terhadap pola JavaScript yang diperbaiki: PASS.
- Backend receive workflow tidak diubah.
- Database/schema tidak berubah.
- Browser UAT belum dijalankan pada environment user, sehingga fitur belum dinyatakan browser-PASS.

## Commit
`b468f3f50519dfe3b28ceca3d9d071cea37d3f51`
