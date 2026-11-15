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

---

## Prompt 2 - Migration menus

- Tanggal: 15 November 2026
- Branch: feature/master-schema
- Tool AI: Antigravity
- Bagian yang diminta: migration tabel menus
- Catatan: prompt disusun dengan bantuan Claude, lalu dikirim ke Antigravity.

### Prompt yang dikirim

haii, aku aku lagi ngerjain proyek kelompok "NasiCafe POS" (Laravel 13, PHP 8.5, MySQL), modul Master Data. Branch saat ini: feature/master-schema. Tabel categories udah dibuat lewat migration sebelumnya.

Tugasnya buatin SATU file migration aja untuk tabel `menus`.

Aturannya

1. Nama file harus persis: database/migrations/2026_11_14_000003_create_menus_table.php (nama sudah dikunci tim, jangan diubah).
2. Kolom:
    - id
    - category_id: foreignId, constrained ke tabel categories, restrictOnDelete
    - name: string
    - price: unsignedInteger
    - stock: unsignedInteger, default 0
    - min_stock: unsignedInteger, default 5
    - image_path: string, nullable
    - is_available: boolean, default true
    - timestamps
3. Gunakan anonymous class (return new class extends Migration).
4. Method down() berisi Schema::dropIfExists('menus').
5. Jangan membuat atau mengubah file lain.
6. Komentar singkat berbahasa Indonesia, cuma pada kolom yang butuh penjelasan.

### Hasil

- File dihasilkan: database/migrations/2026_11_14_000003_create_menus_table.php
- Commit mentah AI: chore: draft AI migration menus (belum direview)

### Hasil review dan perubahan oleh saya

aku hapus semua komentar karena kodenya uda cukup jelas buat dibaca. logika kolom juga ga aku ubah, karena uda bener.

category_id adalah foreign key ke tabel categories, restrictOnDelete itu mencegah kategori dihapus selama masi dipakai menu. price dan stock pakai unsignedInteger supaya ga negatif dan uang
disimpan sebagai Rupiah penuh. is_available beda dengan stock 0: stock 0 berarti abis sementara, is_available false berarti menu sengaja dinonaktifkan.
