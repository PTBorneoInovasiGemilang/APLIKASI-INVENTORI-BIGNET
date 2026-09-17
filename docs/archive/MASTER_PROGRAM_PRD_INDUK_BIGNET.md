# MASTER PROGRAM / PRD INDUK — BIGNET
## Sistem Inventory, Gudang, Asset & Accountability untuk Operasional ISP

**Dokumen:** Master Product & System Blueprint  
**Status:** MASTER / SINGLE SOURCE OF TRUTH  
**Tujuan:** Menjadi fondasi utama bagi Product Owner, developer, QA, dan AI Agent untuk memahami, mengaudit, memperbaiki, dan melanjutkan sistem tanpa kehilangan konteks bisnis maupun teknis.

---

# 0. ATURAN UTAMA DOKUMEN

Dokumen ini adalah acuan utama sebelum melakukan perubahan pada sistem.

AI Agent WAJIB:

1. Membaca dokumen ini sebelum melakukan coding.
2. Memahami alur bisnis sebelum mengubah UI atau backend.
3. Menginspeksi source code, database, konfigurasi, dan environment aktual sebelum menyimpulkan kondisi sistem.
4. Membandingkan implementasi aktual dengan requirement di dokumen ini.
5. Tidak mengubah requirement yang sudah dikunci hanya karena implementasi saat ini berbeda.
6. Tidak membuat asumsi terhadap logika bisnis yang belum jelas.
7. Jika menemukan logika yang ambigu, bertentangan, atau berisiko mengubah proses bisnis, tandai sebagai **OPEN DECISION** dan minta keputusan Product Owner.
8. Tidak menganggap HTTP 200, halaman berhasil dibuka, atau API berhasil dipanggil sebagai bukti bahwa fitur sudah selesai.
9. Menguji alur secara nyata melalui browser/UI, API, database, permission, dan data.
10. Setiap perubahan harus menjaga konsistensi seluruh modul yang terdampak.

**Prinsip paling penting:**

> Sistem ini bukan sekadar aplikasi CRUD gudang. Sistem ini adalah sistem kendali inventory dan accountability yang harus mampu menjawab: barang apa, berada di mana, siapa yang bertanggung jawab, kapan berpindah, melalui transaksi apa, dan apa buktinya.

---

# 1. TUJUAN BESAR SISTEM

Masalah utama yang ingin diselesaikan:

- Barang di gudang sering hilang.
- Barang dapat berpindah ke teknisi tetapi sulit diketahui keberadaannya.
- Selisih stok sulit ditelusuri.
- Barang yang kurang atau hilang sulit diketahui kapan dan pada transaksi mana masalah terjadi.
- Tidak ada jejak tanggung jawab yang kuat.
- Riwayat perpindahan barang tidak selalu mudah dilacak.
- Evidence/foto tidak selalu tersedia.
- Data antar proses dapat tidak sinkron.
- Monitoring sering hanya berupa angka, bukan analisis aktivitas.

## Tujuan akhir

Sistem harus mampu memberikan jawaban yang dapat diverifikasi untuk setiap inventory/asset:

- Apa barangnya?
- Kategori apa?
- Serial number berapa, jika serialized?
- Berapa jumlahnya?
- Berada di gudang/lokasi mana?
- Siapa yang memegang/bertanggung jawab?
- Kapan masuk?
- Dari mana asalnya?
- Kapan dipindahkan?
- Siapa yang memindahkan?
- Kapan diminta?
- Siapa yang meminta?
- Siapa yang menyetujui?
- Kapan keluar?
- Digunakan untuk apa?
- Dipasang di mana, jika asset terpasang?
- Kapan dikembalikan?
- Kondisinya bagaimana?
- Apakah rusak/hilang?
- Apa evidence-nya?
- Apa seluruh timeline-nya?

---

# 2. PRINSIP PRODUK YANG DIKUNCI

## 2.1 Traceability adalah inti

Setiap pergerakan barang harus dapat ditelusuri.

Untuk serialized asset, traceability harus sampai tingkat serial number.

Untuk non-serialized stock, traceability minimal sampai tingkat item, transaksi, quantity, lokasi, tanggal, actor, dan evidence yang relevan.

## 2.2 Accountability

Setiap transaksi harus mengetahui actor yang melakukan.

Minimal informasi yang relevan:

- created_by
- approved_by
- processed_by
- received_by
- requested_by
- assigned_to
- responsible_person
- timestamp
- source location
- destination location

Tidak semua field harus ada di semua transaksi, tetapi transaksi yang relevan WAJIB memiliki identitas actor dan waktu.

## 2.3 Timestamp wajib

Tanggal/waktu sangat penting.

Semua transaksi operasional yang relevan harus memiliki timestamp yang jelas.

Contoh:

- barang masuk
- barang keluar
- request
- approval
- transfer
- peminjaman
- pengembalian
- instalasi
- kerusakan
- kehilangan
- stock opname
- adjustment
- perubahan status
- audit

## 2.4 Evidence

Evidence/foto merupakan bagian penting dari accountability.

Sistem harus mendukung evidence pada transaksi yang relevan.

Evidence harus:

- dapat di-upload;
- tersimpan dengan aman;
- dapat ditampilkan kembali;
- terkait dengan transaksi;
- memiliki metadata yang relevan;
- tidak hanya menjadi file yatim tanpa hubungan dengan record.

## 2.5 Timeline

Timeline adalah fitur inti.

Timeline harus memungkinkan user melihat sejarah item/serial secara berurutan.

Contoh:

`Incoming → Stock → Transfer → Request → Approval → Outgoing → Assigned → Installed → Return/Damage/Lost`

Timeline harus menjawab:

- kapan event terjadi;
- event apa;
- siapa actor;
- dari mana;
- ke mana;
- quantity;
- serial number;
- transaksi terkait;
- evidence terkait;
- status sebelum/sesudah jika relevan.

## 2.6 Tidak boleh ada dead menu

Semua menu yang ditampilkan harus benar-benar bekerja.

Tidak boleh ada:

- menu mati;
- link 404;
- tombol tidak melakukan apa-apa;
- modal kosong;
- action dummy;
- halaman placeholder yang dianggap selesai;
- API yang tidak tersambung;
- UI yang terlihat selesai tetapi backend tidak menyimpan data.

## 2.7 Tidak boleh menebak business logic

Jika AI Agent tidak memahami aturan bisnis:

**JANGAN MENEBak.**

Gunakan:

`OPEN DECISION`

Lalu jelaskan:

- bagian yang ambigu;
- asumsi yang berpotensi dibuat;
- dampak jika salah;
- pertanyaan yang perlu diputuskan Product Owner.

---

# 3. PEMISAHAN REQUIREMENT VS BUG

## 3.1 LOCKED PRODUCT & SYSTEM STANDARDS

Bagian ini adalah keputusan produk/sistem yang dianggap fondasi.

Bug tidak boleh mengubah bagian ini.

Contoh locked standards:

- tujuan accountability;
- traceability;
- timeline;
- evidence;
- timestamp;
- role separation;
- UI consistency;
- workflow approval;
- integritas stok;
- keamanan permission;
- audit trail;
- standar frontend/backend;
- prinsip tidak menebak.

## 3.2 CURRENT BUGS / GAPS

Bug adalah masalah implementasi saat ini.

Bug dapat:

- diperbaiki;
- ditutup;
- diprioritaskan;
- diuji ulang;
- dipindahkan menjadi resolved.

Bug tidak boleh otomatis dianggap sebagai perubahan requirement.

Format issue:

| ID | Modul | Masalah | Expected | Actual | Priority | Status |
|---|---|---|---|---|---|---|
| BUG-001 | ... | ... | ... | ... | P0/P1/P2 | Open |

---

# 4. ROLE & AUTHORIZATION

Role harus dipisahkan berdasarkan tanggung jawab bisnis.

## 4.1 Super Admin

Fungsi utama:

- mengelola akun;
- mengelola role;
- mengelola permission;
- mengelola konfigurasi sistem;
- monitoring global;
- audit;
- mengelola kebutuhan administratif sistem;
- memantau aktivitas;
- melakukan kontrol terhadap data master yang memang menjadi kewenangannya.

Super Admin bukan berarti semua user harus melakukan seluruh workflow operasional.

Hak istimewa teknis tidak boleh otomatis mengubah definisi actor bisnis.

## 4.2 Admin / Warehouse Admin

Fungsi utama:

- mengelola operasional inventory;
- master barang sesuai kewenangan;
- kategori;
- satuan;
- gudang;
- lokasi;
- penerimaan;
- outgoing;
- transfer;
- proses request;
- approval/processing sesuai workflow yang disepakati;
- stock opname;
- pengelolaan stok;
- monitoring inventory;
- laporan operasional.

Admin bukan requester utama barang lapangan.

## 4.3 Teknisi / Field User

Fungsi utama:

- mengajukan/request barang;
- melihat barang yang menjadi tanggung jawabnya;
- menggunakan barang untuk pekerjaan;
- menerima/konfirmasi barang jika workflow mengharuskan;
- melaporkan penggunaan;
- melakukan pengembalian;
- mengirim evidence;
- melaporkan kerusakan;
- melaporkan kehilangan;
- melihat histori/timeline barang yang menjadi tanggung jawabnya.

Teknisi adalah actor utama untuk request barang lapangan.

---

# 5. AUTHENTICATION & ACCOUNT

Login harus sederhana, stabil, dan dapat digunakan tanpa error.

Requirement:

- login;
- logout;
- session handling;
- role-based access;
- protected routes;
- validasi credential;
- pesan error yang jelas;
- tidak redirect loop;
- tidak blank screen;
- tidak error setelah login;
- akun dikelola oleh Super Admin;
- role tidak boleh hanya disembunyikan di frontend;
- backend harus tetap melakukan authorization.

## Account Management

Super Admin dapat:

- membuat user;
- mengubah user;
- mengaktifkan/nonaktifkan user;
- menentukan role;
- reset credential sesuai mekanisme keamanan;
- melihat status user;
- melihat aktivitas user bila diperlukan.

---

# 6. STANDAR UI GLOBAL

Semua halaman harus konsisten.

## Tabel standar

Untuk tabel yang relevan, gunakan standar:

- nomor/row number;
- search;
- filter;
- reset filter;
- pagination;
- sorting bila relevan;
- tanggal;
- status;
- action;
- empty state;
- loading state;
- error state;
- feedback sukses/gagal.

Jangan membuat satu tabel memiliki search/pagination sedangkan tabel lain tidak tanpa alasan requirement.

## Form standar

Form harus memiliki:

- label jelas;
- required indicator;
- validasi;
- pesan error;
- loading saat submit;
- disabled state saat proses;
- success feedback;
- failure feedback;
- confirmation untuk destructive action;
- format input yang sesuai.

## Button

Setiap tombol harus punya fungsi nyata.

Contoh:

- View
- Detail
- Create
- Edit
- Delete
- Approve
- Reject
- Submit
- Cancel
- Receive
- Transfer
- Return
- Print
- Export
- Upload Evidence
- Scan
- Assign

Tidak boleh ada tombol yang hanya tampak aktif.

---

# 7. STRUKTUR SIDEBAR / MENU

Struktur menu adalah peta navigasi produk.

Implementasi aktual wajib diaudit terhadap fungsi di bawah ini.

## 7.1 Overview

### Dashboard

Tujuan:

Memberikan gambaran kondisi inventory dan aktivitas bisnis.

Dashboard bukan hanya kumpulan KPI.

Harus mampu menjadi dashboard analitik.

Contoh informasi:

- total item;
- total stock;
- total serialized asset;
- incoming;
- outgoing;
- request;
- pending approval;
- item dipinjam;
- item terpasang;
- item rusak;
- item hilang;
- aktivitas transaksi;
- tren transaksi;
- aktivitas berdasarkan waktu;
- top requester;
- top activity;
- distribusi inventory;
- alert/anomaly.

### Analitik

Minimal arah analitik:

- pola incoming;
- pola outgoing;
- periode aktivitas tertinggi;
- requester paling aktif;
- item paling sering bergerak;
- lokasi dengan aktivitas tinggi;
- tren transaksi.

Jika chart belum terbukti bekerja pada browser, statusnya:

**P0 OPEN / NEEDS VERIFICATION**

---

# 8. MASTER DATA

## 8.1 Kategori / Jenis Barang

Fungsi:

- mengelompokkan item;
- mendukung filtering;
- mendukung laporan;
- menentukan karakteristik item bila dibutuhkan.

## 8.2 Satuan

Fungsi:

- unit quantity;
- konsistensi transaksi inventory.

## 8.3 Gudang

Fungsi:

- master warehouse;
- identitas lokasi inventory utama;
- menjadi source/destination transaksi.

## 8.4 Lokasi

Fungsi:

- lokasi lebih detail dalam gudang atau konteks asset;
- membantu mengetahui posisi barang.

Untuk serialized asset, lokasi dapat menjadi bagian penting dari lifecycle.

## 8.5 Master Barang

Fungsi:

- identitas produk/item;
- kode;
- nama;
- kategori;
- satuan;
- tipe serialized/non-serialized;
- status;
- informasi tambahan sesuai requirement.

---

# 9. INVENTORY

## 9.1 Stok

Menampilkan kondisi stok saat ini.

Harus dapat ditelusuri ke transaksi.

Tidak boleh ada stok yang muncul tanpa dasar transaksi/adjustment yang dapat dijelaskan.

## 9.2 Nomor Seri

Untuk item serialized.

Informasi penting:

- serial number;
- item;
- status;
- current location;
- current responsible person;
- lifecycle;
- histori;
- timeline;
- evidence;
- transaksi terkait.

## 9.3 Pergerakan Stok

Menampilkan movement ledger.

Minimal:

- tanggal;
- item;
- serial jika ada;
- quantity;
- source;
- destination;
- actor;
- reference transaction;
- movement type;
- evidence jika relevan.

## 9.4 Inventory Invariants

Sistem harus menjaga aturan:

`stok akhir = stok awal + incoming - outgoing ± transfer/adjustment sesuai aturan`

Untuk serialized item:

- satu serial tidak boleh berada di dua lokasi aktif secara bersamaan;
- serial tidak boleh keluar tanpa lifecycle yang valid;
- serial tidak boleh muncul sebagai available jika sedang assigned/installed/borrowed sesuai state machine;
- setiap perpindahan harus memiliki event.

---

# 10. OPERASIONAL INVENTORY

## 10.1 Pengajuan / Request

Requester utama:

**Teknisi / Field User**

Workflow dasar:

`Draft → Submitted → Pending Approval → Approved/Rejected → Processed → Completed`

Request harus memiliki:

- requester;
- tanggal;
- item;
- quantity;
- tujuan;
- kebutuhan/pekerjaan;
- status;
- catatan;
- evidence jika relevan.

Untuk item **STOCK**, request wajib memiliki **Gudang Pemenuhan** per item (`submission_items.warehouse_id`). Gudang ini menjadi acuan tunggal untuk: cek stok saat approval, tampilan stok tersedia, dan gudang asal saat Barang Keluar. Identity stock = `master_item_id + warehouse_id` — TIDAK boleh menggunakan SUM lintas gudang.

## 10.2 Approval

Approval harus:

- memiliki actor;
- timestamp;
- keputusan;
- alasan bila reject;
- tidak dapat mengubah histori secara diam-diam;
- untuk item STOCK: cek stok aktual **pada Gudang Pemenuhan item** (`master_item_id + warehouse_id`) — stok gudang lain TIDAK dihitung; item STOCK tanpa gudang (data lama) ditolak dengan pesan jelas, bukan ditebak;
- stok dicek ulang saat Barang Keluar (stok dapat berubah setelah approval); request > stok gudang → `Stok Tidak Mencukupi`, proses dibatalkan, stok tidak berubah.

## 10.3 Incoming

Fungsi:

Mencatat barang masuk.

Data minimal:

- tanggal;
- source/vendor/reference;
- item;
- quantity;
- serial bila serialized;
- kondisi;
- destination warehouse/location;
- actor;
- evidence;
- reference number.

Incoming harus menambah inventory secara benar.

## 10.4 Outgoing

Fungsi:

Mencatat barang keluar.

Harus memiliki:

- requester/recipient;
- tanggal;
- item;
- quantity;
- serial;
- source;
- destination/person;
- actor;
- reference request;
- evidence;
- status.

Outgoing harus mengurangi stock sesuai aturan.

Outgoing yang berasal dari Pengajuan WAJIB memakai **Gudang Pemenuhan pengajuan** (`submission_items.warehouse_id`): gudang di-preselect di form, dan server menolak bila gudang header berbeda dari gudang pemenuhan item (warehouse mismatch → rollback). Pengajuan lama tanpa gudang: Admin menentukan gudang secara eksplisit saat proses (data tidak diisi paksa). Deduction tetap tepat satu kali di `OutgoingController::store()` (source of truth) dengan `master_item_id + warehouse_id` yang sama, didahului stock sufficiency check `FOR UPDATE` → no negative stock, no double deduction.

## 10.5 Peminjaman

Fungsi:

Mencatat asset/barang yang dipinjamkan.

Harus diketahui:

- siapa peminjam;
- barang;
- serial;
- tanggal pinjam;
- expected return;
- status;
- evidence;
- histori.

## 10.6 Transfer

Fungsi:

Memindahkan inventory antar lokasi/gudang.

Workflow:

`Request/Initiate → Approve jika diperlukan → Dispatch → Receive → Completed`

Transfer tidak boleh hanya mengubah field location tanpa membuat movement history.

## 10.7 Pengembalian

Fungsi:

Mengembalikan barang.

Harus diketahui:

- siapa yang mengembalikan;
- tanggal;
- item/serial;
- kondisi;
- lokasi tujuan;
- evidence;
- actor penerima;
- reference transaction.

## 10.8 Terpasang / Installed

Untuk asset yang sudah digunakan/dipasang.

Harus dapat mengetahui:

- serial;
- lokasi pemasangan;
- pekerjaan/project/reference;
- tanggal instalasi;
- teknisi;
- status;
- evidence;
- timeline.

---

# 11. BARCODE / QR / LABEL

Fitur barcode/QR harus diperlakukan sebagai bagian dari traceability.

Requirement:

- generate barcode/QR;
- scan;
- mencari item/serial dari hasil scan;
- membuka detail asset;
- membuka timeline;
- label dapat dipreview;
- label dapat dicetak;
- batch print bila diperlukan;
- informasi kepemilikan/identitas organisasi;
- dukungan logo pada label/barcode sesuai desain;
- informasi item/serial yang jelas;
- output dapat digunakan secara nyata.

QR/barcode tidak boleh hanya menjadi gambar dekoratif.

Scan harus membawa user ke data bisnis yang benar dan tetap mengikuti authorization.

---

# 12. TIMELINE & TRACEABILITY

Setiap serialized asset idealnya memiliki halaman detail dengan:

## Identity

- item;
- code;
- serial;
- category;
- status.

## Current State

- current warehouse;
- current location;
- current responsible person;
- current lifecycle status.

## Timeline

Contoh:

1. Created
2. Incoming
3. Stored
4. Transferred
5. Requested
6. Approved
7. Outgoing
8. Assigned
9. Installed
10. Returned
11. Damaged
12. Lost
13. Repaired
14. Reassigned

Tidak semua event wajib terjadi pada setiap asset.

Timeline harus berdasarkan event nyata, bukan data dummy.

---

# 13. EVIDENCE

Evidence dapat berupa foto/dokumen sesuai kebutuhan.

Harus terkait dengan:

- incoming;
- outgoing;
- transfer;
- request;
- return;
- installation;
- damage;
- loss;
- stock opname;
- adjustment;
- event lain yang membutuhkan bukti.

UI harus memungkinkan:

- upload;
- preview;
- view;
- metadata;
- relasi ke transaksi.

---

# 14. STOCK OPNAME

Stock opname digunakan untuk membandingkan:

`System Stock vs Physical Stock`

Harus dapat mencatat:

- tanggal;
- warehouse;
- location;
- item;
- serial;
- expected quantity;
- actual quantity;
- variance;
- reason;
- actor;
- evidence;
- approval bila diperlukan;
- adjustment reference.

Variance tidak boleh langsung mengubah stok tanpa proses yang dapat diaudit.

---

# 15. DAMAGE / LOST ITEM

Kondisi rusak/hilang harus menjadi lifecycle yang dapat ditelusuri.

Untuk lost:

- siapa terakhir memegang;
- kapan terakhir terlihat;
- lokasi terakhir;
- transaksi terakhir;
- evidence;
- actor pelapor;
- status investigasi;
- keputusan akhir.

Untuk damage:

- kondisi;
- tanggal;
- actor;
- evidence;
- tindakan berikutnya;
- repair/retire/replacement jika ada.

---

# 16. LAPORAN

Laporan harus dapat digunakan untuk menjawab kebutuhan operasional.

Contoh:

- stock report;
- incoming report;
- outgoing report;
- transfer report;
- request report;
- borrowing report;
- return report;
- installed asset report;
- damaged report;
- lost report;
- movement report;
- stock opname report;
- accountability report;
- audit report.

Filter yang relevan:

- tanggal;
- warehouse;
- location;
- item;
- category;
- serial;
- user;
- role;
- status.

Export hanya boleh dianggap selesai jika hasil export benar-benar dapat digunakan.

---

# 17. MONITORING

Monitoring harus membantu menjawab:

- apa yang sedang terjadi;
- siapa yang aktif;
- inventory mana yang bergerak;
- request mana yang pending;
- item mana yang bermasalah;
- item mana yang hilang;
- asset mana yang terpasang;
- siapa yang memegang asset;
- transaksi mana yang perlu diperiksa.

---

# 18. AUDIT LOG

Audit log harus mencatat aktivitas penting.

Minimal:

- actor;
- action;
- entity;
- entity id;
- timestamp;
- before/after jika relevan;
- metadata;
- reference transaction.

Audit log tidak boleh mudah dimanipulasi oleh user biasa.

---

# 19. BACKEND REQUIREMENTS

Backend harus menjaga business rules, bukan hanya menyediakan endpoint.

Setiap operasi penting harus memiliki:

1. Authentication
2. Authorization
3. Validation
4. Business rule
5. Database transaction bila diperlukan
6. Audit/event creation
7. Consistent response
8. Error handling

## Server-side authorization

Role restriction harus ditegakkan di backend.

Menyembunyikan tombol di frontend saja tidak cukup.

## Transaction integrity

Operasi yang mengubah inventory harus atomic jika terdiri dari beberapa perubahan.

Contoh outgoing:

- validasi stock;
- validasi serial;
- create transaction;
- update inventory;
- update lifecycle;
- create movement;
- create audit;
- commit.

Jika gagal di tengah, sistem tidak boleh meninggalkan data setengah jadi.

---

# 20. FRONTEND REQUIREMENTS

Frontend harus:

- konsisten;
- responsif;
- mudah dipahami;
- role-aware;
- memiliki loading state;
- memiliki empty state;
- memiliki error state;
- memiliki success feedback;
- memiliki confirmation;
- tidak menampilkan data yang tidak boleh dilihat role;
- tetap mengandalkan backend authorization.

Setiap page harus memiliki:

- header;
- breadcrumb/page context bila diperlukan;
- search/filter jika tabel membutuhkan;
- action;
- table/list;
- pagination;
- status;
- detail;
- feedback.

---

# 21. MENU-TO-BUSINESS MAPPING

Setiap menu harus memiliki definisi:

| Menu | Tujuan Bisnis | Actor | Input | Proses | Output | Data Terdampak | Timeline |
|---|---|---|---|---|---|---|---|
| Dashboard | Monitoring | Super Admin/Admin | Filter | Analisis | KPI/Chart | Read-only | - |
| Master Barang | Master inventory | Admin | Item data | Validation | Item master | Item | Audit |
| Kategori | Klasifikasi | Admin | Category | CRUD | Category | Category | Audit |
| Gudang | Lokasi inventory | Admin | Warehouse | CRUD | Warehouse | Warehouse | Audit |
| Lokasi | Detail lokasi | Admin | Location | CRUD | Location | Location | Audit |
| Stok | Kondisi inventory | Admin | Filter | Query ledger | Stock | Inventory | Yes |
| Serial | Tracking asset | Admin/authorized user | Search | Query lifecycle | Asset detail | Serial | Yes |
| Pengajuan | Request barang | Teknisi | Request | Approval flow | Request | Request | Yes |
| Incoming | Barang masuk | Admin | Incoming | Receive | Stock increase | Inventory | Yes |
| Outgoing | Barang keluar | Admin | Fulfillment | Issue | Stock decrease | Inventory | Yes |
| Transfer | Pindah lokasi | Admin | Transfer | Move | New location | Inventory | Yes |
| Peminjaman | Custody | Admin/Teknisi | Loan | Assign | Custody | Serial | Yes |
| Pengembalian | Return | Teknisi/Admin | Return | Receive | Returned | Inventory | Yes |
| Terpasang | Asset deployed | Teknisi/Admin | Installation | Install | Installed | Serial | Yes |
| Laporan | Reporting | Authorized | Filter | Aggregate | Report | Read | Yes |
| Audit | Accountability | Super Admin | Filter | Audit query | Audit trail | Audit | Yes |
| Users | Account management | Super Admin | User data | CRUD | User | Users | Audit |

**Catatan:** Mapping aktual wajib diaudit terhadap source aktual. Jika ada menu tambahan atau menu berbeda, jangan langsung menghapus/menambah. Tandai sebagai `OPEN DECISION` jika business purpose tidak jelas.

---

# 22. DEFINITION OF DONE

Fitur tidak dianggap selesai hanya karena:

- route tersedia;
- HTTP 200;
- API merespons;
- halaman muncul;
- tombol terlihat;
- data dummy tampil.

Fitur dianggap selesai apabila:

- requirement terpenuhi;
- role benar;
- permission benar;
- UI benar;
- backend benar;
- database benar;
- business logic benar;
- inventory invariant terjaga;
- timeline tercatat;
- audit tercatat;
- evidence tersimpan bila diperlukan;
- error handling benar;
- browser flow berhasil;
- data aktual dapat dibuat/diubah/dilihat;
- tidak ada dead button;
- tidak ada broken route;
- tidak ada regression pada modul terkait.

---

# 23. QA / UAT

Pengujian harus dilakukan per role.

## Super Admin

- login;
- dashboard;
- users;
- role;
- monitoring;
- audit;
- logout.

## Admin

- login;
- master data;
- incoming;
- stock;
- request processing;
- outgoing;
- transfer;
- return;
- stock opname;
- reports;
- logout.

## Teknisi

- login;
- request;
- melihat status request;
- menerima/menggunakan asset;
- melihat asset tanggung jawab;
- evidence;
- return;
- damage/lost report;
- timeline;
- logout.

## Browser verification

Setiap menu harus benar-benar dibuka.

Checklist:

- [ ] Menu dapat diklik
- [ ] Route benar
- [ ] Page tidak error
- [ ] Data tampil
- [ ] Search bekerja
- [ ] Filter bekerja
- [ ] Reset bekerja
- [ ] Pagination bekerja
- [ ] Create bekerja
- [ ] Edit bekerja
- [ ] Delete bekerja bila diizinkan
- [ ] Action bekerja
- [ ] Permission benar
- [ ] Error handling benar
- [ ] Success feedback benar

---

# 24. AI AGENT WORKING PROTOCOL

Sebelum coding:

### Phase 1 — Understand

Baca MASTER PRD.

### Phase 2 — Inspect

Audit:

- source;
- routes;
- pages;
- components;
- API;
- controllers/services;
- models/schema;
- migrations;
- auth;
- permissions;
- database;
- seed;
- configuration.

### Phase 3 — Map

Buat mapping:

`Requirement → Menu → Frontend → API → Backend → Database → Timeline/Audit → UAT`

### Phase 4 — Gap Analysis

Kelompokkan:

- Missing
- Incorrect
- Partial
- Broken
- Inconsistent
- Security issue
- Business logic issue
- UI inconsistency

### Phase 5 — Prioritize

Urutan:

**P0 — Business Critical**

Contoh:

- login;
- authorization;
- inventory integrity;
- stock movement;
- request;
- outgoing;
- traceability;
- timeline;
- accountability.

**P1 — Important**

Contoh:

- reports;
- analytics;
- evidence;
- barcode/QR;
- stock opname.

**P2 — Enhancement**

Perbaikan yang tidak mengganggu core business.

### Phase 6 — Plan

Jangan langsung coding.

Tampilkan:

- apa yang ditemukan;
- apa yang salah;
- apa yang missing;
- rencana perubahan;
- risiko;
- dependency.

### Phase 7 — Implement

Kerjakan satu scope yang jelas.

### Phase 8 — Verify

Test:

- frontend;
- backend;
- database;
- role;
- business flow;
- browser;
- regression.

---

# 25. ATURAN ANTI-ASAL / ANTI-HALUSINASI

AI Agent dilarang:

- mengarang requirement;
- mengarang route;
- mengarang database field;
- menganggap fitur selesai tanpa test;
- menghapus menu karena dianggap tidak penting;
- mengubah role tanpa approval;
- mengubah workflow bisnis tanpa approval;
- membuat dummy implementation dan menganggap selesai;
- menutupi error;
- mengatakan PASS tanpa evidence;
- menganggap screenshot sebagai bukti backend;
- mengubah locked requirement untuk menyesuaikan source.

Jika tidak tahu:

**STOP → DOCUMENT → ASK**

Format:

```text
OPEN DECISION

Area:
Masalah:
Yang sudah diketahui:
Yang belum diketahui:
Pilihan yang memungkinkan:
Dampak masing-masing:
Keputusan yang dibutuhkan:
```

---

# 26. PERUBAHAN REQUIREMENT

Requirement baru dapat ditambahkan.

Format:

```text
REQ-XXXX
Nama:
Tujuan:
Actor:
Workflow:
Input:
Output:
Database impact:
UI impact:
Backend impact:
Audit impact:
Timeline impact:
Acceptance criteria:
Status:
```

Requirement baru tidak boleh merusak locked requirement tanpa keputusan eksplisit.

---

# 27. FITUR KOLABORATIF / SUGGESTION

Beberapa fitur dapat merupakan hasil penyempurnaan desain sistem dan harus diperlakukan sebagai candidate/approved requirement sesuai keputusan Product Owner.

Contoh:

- analytics dashboard;
- advanced asset timeline;
- evidence viewer;
- batch label printing;
- QR/barcode deep-link;
- anomaly monitoring;
- accountability report;
- advanced audit filtering;
- serialized asset lifecycle;
- location-aware tracking.

Jika status belum disepakati, gunakan:

`PROPOSED`

Jangan menganggap `PROPOSED` sebagai `LOCKED`.

---

# 28. SOURCE AKTUAL SEBAGAI IMPLEMENTATION BASELINE

Source aktual adalah kondisi implementasi yang harus diperiksa.

AI Agent WAJIB:

- membaca struktur project;
- membaca route;
- membaca menu/sidebar;
- membaca role;
- membaca backend;
- membaca database;
- membaca API;
- membaca komponen UI;
- membaca konfigurasi;
- menjalankan aplikasi;
- menguji flow.

Source aktual bukan sumber kebenaran requirement.

**PRD menentukan target. Source menunjukkan kondisi saat ini.**

---

# 29. AUDIT MENU

Untuk setiap sidebar/menu:

| Menu | Ada di Source | Route | Bisa Dibuka | Fungsi Sesuai | Role Sesuai | CRUD | Search | Filter | Pagination | Backend | DB | Timeline | Audit | Status |
|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|
| ... | ... | ... | ... | ... | ... | ... | ... | ... | ... | ... | ... | ... | ... | ... |

Tidak boleh menyatakan PASS sebelum benar-benar diuji.

---

# 30. AUDIT FRONTEND

Periksa:

- sidebar;
- header;
- dashboard;
- table;
- form;
- modal;
- detail;
- pagination;
- search;
- filter;
- buttons;
- notification;
- loading;
- empty state;
- error;
- responsive behavior;
- role visibility.

Target:

**UI harus konsisten dan predictable.**

---

# 31. AUDIT BACKEND

Periksa:

- auth;
- authorization;
- routes;
- controllers;
- services;
- validation;
- transaction;
- database;
- relations;
- stock calculation;
- serial lifecycle;
- audit;
- evidence;
- error response.

Pertanyaan utama:

> Apakah backend benar-benar menegakkan business logic yang dijelaskan PRD?

---

# 32. AUDIT DATABASE

Periksa:

- table;
- primary key;
- foreign key;
- indexes;
- timestamps;
- status;
- nullable rules;
- unique constraints;
- serial uniqueness;
- transaction relations;
- audit relations;
- evidence relations.

Data tidak boleh mudah menjadi orphan atau inconsistent.

---

# 33. BUSINESS FLOW UTAMA

## Flow A — Barang Masuk

`Source → Incoming → Validation → Receive → Inventory Update → Movement → Audit → Timeline`

## Flow B — Request Barang

`Teknisi → Request → Submit → Approval → Fulfillment → Outgoing → Assignment → Timeline`

## Flow C — Transfer

`Source Location → Transfer → Dispatch → Receive → Destination → Movement → Timeline`

## Flow D — Peminjaman

`Available → Borrow → Assign Person → Custody → Return → Inspection → Available/Other State`

## Flow E — Instalasi

`Available → Outgoing/Assignment → Technician → Installation → Installed Location → Timeline`

## Flow F — Pengembalian

`Technician → Return → Inspection → Evidence → Receive → Inventory/Lifecycle Update → Timeline`

## Flow G — Damage

`Report → Evidence → Review → Damage State → Repair/Retire/Replace`

## Flow H — Lost

`Report → Evidence → Last Known Custody → Investigation → Resolution → Audit`

## Flow I — Stock Opname

`Count → Compare → Variance → Review → Approved Adjustment → Inventory Update → Audit`

---

# 34. DATA LINEAGE

Untuk transaksi inventory, data harus dapat dilacak:

`User → Action → Transaction → Inventory → Item/Serial → Location → Timeline → Audit → Evidence`

Tidak boleh ada transaksi penting yang terputus dari lineage.

---

# 35. DASHBOARD ANALYTICS

Dashboard harus berkembang menjadi alat monitoring bisnis.

Minimum target analitik:

- aktivitas incoming per periode;
- aktivitas outgoing per periode;
- peak transaction period;
- top requester;
- item paling sering bergerak;
- movement trend;
- inventory distribution;
- pending request;
- outstanding return;
- installed asset;
- damaged/lost asset;
- stock anomaly.

Chart harus menggunakan data nyata.

Tidak boleh chart dummy.

---

# 36. BARCODE / QR ACCEPTANCE CRITERIA

Fitur dianggap selesai apabila:

- [ ] Generate berhasil
- [ ] Barcode/QR berisi identifier yang benar
- [ ] Logo dapat ditampilkan sesuai desain
- [ ] Informasi item jelas
- [ ] Serial jelas bila serialized
- [ ] Preview benar
- [ ] Print benar
- [ ] Batch print bila requirement aktif
- [ ] Scan dapat membuka asset/item
- [ ] Authorization tetap diterapkan
- [ ] Timeline dapat diakses
- [ ] Tidak ada data dummy

---

# 37. GLOBAL ACCEPTANCE CRITERIA

Aplikasi dianggap siap hanya jika:

1. Login stabil.
2. Account management stabil.
3. Role separation benar.
4. Sidebar sesuai role.
5. Semua menu dapat dibuka.
6. Tidak ada route error.
7. Semua action penting bekerja.
8. Inventory konsisten.
9. Serial lifecycle konsisten.
10. Request workflow bekerja.
11. Approval bekerja.
12. Incoming bekerja.
13. Outgoing bekerja.
14. Transfer bekerja.
15. Borrowing bekerja.
16. Return bekerja.
17. Installation bekerja.
18. Damage/lost workflow dapat ditelusuri.
19. Stock opname dapat diaudit.
20. Timeline tersedia.
21. Evidence tersedia.
22. Audit log tersedia.
23. Reports dapat digunakan.
24. Dashboard analytics menggunakan data nyata.
25. Barcode/QR dapat digunakan.
26. UI konsisten.
27. Search/filter/pagination tersedia sesuai kebutuhan.
28. Permission server-side benar.
29. Tidak ada dead button.
30. Tidak ada silent failure.
31. Tidak ada business logic yang dibuat berdasarkan tebakan.

---

# 38. PRIORITAS IMPLEMENTASI

Urutan kerja yang disarankan:

## P0 — Foundation

- Authentication
- Authorization
- Users/Roles
- Master Data
- Inventory model
- Stock integrity
- Serialized asset model
- Core transaction model
- Audit
- Timeline

## P1 — Core Operations

- Request
- Approval
- Incoming
- Outgoing
- Transfer
- Borrow
- Return
- Installation
- Damage/Lost
- Stock Opname

## P1 — Accountability

- Evidence
- Movement tracking
- Asset detail
- Responsibility tracking
- Reports

## P1/P2 — Visibility

- Dashboard
- Analytics
- Charts
- Monitoring

## P2 — Convenience

- Barcode
- QR
- Label
- Batch print
- Export enhancements

---

# 39. CURRENT IMPLEMENTATION GAP

Bagian ini harus diisi berdasarkan audit terhadap source aktual.

JANGAN mengisi berdasarkan asumsi.

Format:

| ID | Requirement | Kondisi Aktual | Gap | Dampak | Priority | Action | Verification |
|---|---|---|---|---|---|---|---|
| GAP-001 | ... | ... | ... | ... | P0 | ... | ... |

---

# 40. CHANGE CONTROL

Jika AI Agent ingin melakukan perubahan besar:

Harus menjelaskan:

- requirement yang terdampak;
- business flow yang terdampak;
- role yang terdampak;
- database yang terdampak;
- frontend yang terdampak;
- backend yang terdampak;
- audit/timeline yang terdampak;
- risiko;
- migration requirement;
- regression test.

Tidak boleh melakukan perubahan besar secara diam-diam.

---

# 41. FINAL PRINCIPLE

Seluruh sistem harus selalu kembali kepada pertanyaan utama:

> "Apakah aplikasi ini benar-benar membuat kita dapat mengetahui keberadaan, pergerakan, kondisi, dan penanggung jawab setiap barang?"

Jika jawabannya belum:

**belum selesai.**

Bukan hanya karena UI belum bagus.

Bukan hanya karena ada error.

Tetapi karena tujuan bisnis belum tercapai.

---

# 42. INSTRUKSI UNTUK AI AGENT

Sebelum mengerjakan task apa pun:

```text
1. READ MASTER PRD.
2. IDENTIFY THE BUSINESS FLOW.
3. IDENTIFY THE ROLE.
4. INSPECT CURRENT SOURCE.
5. MAP REQUIREMENT TO IMPLEMENTATION.
6. IDENTIFY GAP.
7. DO NOT GUESS.
8. IF AMBIGUOUS, CREATE OPEN DECISION.
9. PLAN BEFORE CODING.
10. IMPLEMENT.
11. TEST FRONTEND + BACKEND + DATABASE.
12. TEST ROLE PERMISSION.
13. TEST BUSINESS FLOW.
14. TEST BROWSER.
15. REPORT EXACT RESULT.
```

Format laporan setelah pekerjaan:

```text
TASK:
Scope:

REQUIREMENT:
...

CURRENT CONDITION:
...

CHANGES:
...

BUSINESS FLOW VERIFIED:
...

FRONTEND VERIFIED:
...

BACKEND VERIFIED:
...

DATABASE VERIFIED:
...

ROLE/PERMISSION VERIFIED:
...

TIMELINE VERIFIED:
...

AUDIT VERIFIED:
...

EVIDENCE VERIFIED:
...

UAT:
PASS / FAIL / BLOCKED

OPEN DECISIONS:
...

REMAINING GAPS:
...
```

---

# 43. PRODUCT OWNER DECISION RULE

Jika ada perbedaan antara:

- implementasi aktual;
- asumsi AI;
- requirement;

maka prioritasnya:

**LOCKED REQUIREMENT → PRODUCT OWNER DECISION → IMPLEMENTATION**

Bukan:

**IMPLEMENTATION → AI ASSUMPTION → REQUIREMENT**

---

# 44. PENUTUP

Dokumen ini merupakan blueprint utama sistem.

Semua pengembangan selanjutnya harus menjaga:

- tujuan bisnis;
- accountability;
- inventory integrity;
- traceability;
- timestamp;
- evidence;
- timeline;
- role separation;
- UI consistency;
- backend integrity;
- auditability;
- usability.

Jika suatu fitur terlihat bagus tetapi tidak membantu proses bisnis, evaluasi kembali.

Jika backend berjalan tetapi timeline/audit/accountability rusak, fitur belum selesai.

Jika UI terlihat lengkap tetapi tombol tidak bekerja, fitur belum selesai.

Jika data dapat berubah tetapi tidak dapat dijelaskan asal-usulnya, sistem belum memenuhi tujuan.

**Target akhir bukan sekadar website yang bisa dibuka.**

**Target akhir adalah sistem inventory ISP yang dapat dipercaya untuk menjawab keberadaan dan tanggung jawab setiap barang.**
