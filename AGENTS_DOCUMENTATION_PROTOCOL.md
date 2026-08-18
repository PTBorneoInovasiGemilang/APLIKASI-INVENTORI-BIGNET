# BIGNET — AGENT DOCUMENTATION PROTOCOL

Dokument ini adalah addendum untuk AGENTS.md.

## WAJIB

Setiap task yang mengubah project harus menjaga dokumentasi project.

Sebelum bekerja:
1. Baca `MASTER_BIGNET_PROJECT_V2.md`.
2. Baca `docs/PROJECT_STATE.md`.
3. Baca `docs/DECISIONS.md`.
4. Baca `docs/OPEN_DECISIONS.md`.
5. Baca `docs/BUG_TRACKER.md` bila task terkait bug.
6. Baca `docs/UAT_STATUS.md` bila task terkait fitur yang sudah diuji.

Setelah bekerja:
1. Update `docs/PROJECT_STATE.md`.
2. Tambahkan entry ke `docs/CHANGELOG.md`.
3. Update `docs/BUG_TRACKER.md` bila ada bug/gap.
4. Update `docs/UAT_STATUS.md` bila ada hasil testing.
5. Update `docs/DECISIONS.md` hanya jika ada keputusan Product Owner yang benar-benar baru.
6. Update `docs/OPEN_DECISIONS.md` bila ada keputusan yang dibutuhkan atau sudah diputuskan.

## DILARANG

- Mengklaim fitur selesai tanpa verification.
- Menghapus histori dokumentasi.
- Mengubah locked decision tanpa persetujuan Product Owner.
- Mengubah business logic karena implementation saat ini lebih mudah.
- Mengisi dokumentasi dengan tebakan.
- Menulis PASS hanya karena HTTP 200 atau page render.

## CHECKPOINT

Setiap task selesai harus meninggalkan checkpoint:

- task;
- tujuan;
- status;
- file/module berubah;
- database impact;
- business flow terdampak;
- verification;
- bug/gap;
- next action.

`PROJECT_STATE.md` harus selalu menggambarkan titik terakhir project secara akurat.
