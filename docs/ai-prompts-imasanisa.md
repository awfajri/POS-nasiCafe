# Log Prompt AI - Imas Anisa (Master Data)

## Prompt 1 - Migration categories

- Tanggal: 15 November 2026
- Branch: feature/master-schema
- Tool AI: Antigravity
- Bagian yang diminta: migration tabel categories

### Prompt yang dikirim

halo, aku lagi mengerjakan proyek kelompok "NasiCafe POS" (Laravel 13, PHP 8.5, MySQL). Aku bertugas di modul Master Data (kategori dan menu). Saat ini aku berada di branch feature/master-schema.

Tugasnya: Buatkan SATU file migration saja untuk tabel `categories`.

Aturan:

1. Nama file harus persis: database/migrations/2026_11_14_000002_create_categories_table.php (nama ini sudah dikunci oleh tim, jangan diubah).
2. Kolom:
    - id
    - name: string, unique
    - slug: string, unique
    - description: text, nullable
    - timestamps
3. Gunakan sintaks anonymous class (return new class extends Migration) sesuai standar Laravel terbaru.
4. Isi method down() dengan Schema::dropIfExists('categories').
5. Jangan membuat atau mengubah file lain (model, controller, seeder, dsb).
6. Tambahkan komentar singkat berbahasa Indonesia di setiap baris kolom agar mudah kupelajari.

### Hasil

- File dihasilkan: database/migrations/2026_11_14_000002_create_categories_table.php
- Commit mentah AI: chore: draft AI migration categories (belum direview)

### Hasil review dan perubahan oleh saya

yang aku ubah, komentar dari ai terlalu panjang, jadi aku persingkat.
terus name dan slug itu dibuat unique karena supaya gada kategori yg kembar, terus up () itu untuk bikin tabel dan down () itu unyuk hapus saat rollback.
