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

## Prompt 3 - Model Category dan Menu

- Tanggal: 15 November 2026
- Branch: feature/master-schema
- Tool AI: Antigravity
- Bagian yang diminta: model Category dan Menu

### Prompt yang dikirim

halo, akulagi ngerjain proyek kelompok "NasiCafe POS" (Laravel 13, PHP 8.5, MySQL), modul Master Data. Branch: feature/master-schema. Tabel categories dan menus sudah dibuat lewat migration.

tugasnya, buatin DUA file model aja: app/Models/Category.php dan app/Models/Menu.php.

Aturan Categorynya

1. $fillable: name, slug, description.
2. Relasi menus(): hasMany(Menu::class).

Aturan Menunya

1. $fillable lengkap: category_id, name, price, stock, min_stock, image_path, is_available.
2. $casts: is_available => boolean.
3. Relasi category(): belongsTo(Category::class).
4. Relasi transactionDetails(): hasMany(TransactionDetail::class, 'menu_id'). Jangan membuat model TransactionDetail, itu milik anggota tim lain.
5. scopeAvailable: where is_available = true.
6. scopeLowStock: whereColumn('stock', '<=', 'min_stock').
7. Accessor getImageUrlAttribute(): jika image_path ada, kembalikan Storage::url($this->image_path); jika tidak, asset('images/menu-placeholder.png').

### Hasil

- File dihasilkan: app/Models/Category.php dan app/Models/Menu.php
- Commit mentah AI: chore: draft AI model Category dan Menu (belum direview)

### Hasil review dan perubahan oleh saya

pas aku review, aku nemu 2 hal yang aku perbaiki di menu.php

1. accessor image_url ga nyebut disk. foto nanti disimpan di disk public,
   jadi akuganti ke Storage::disk('public')->url() supaya URL-nya konsisten.
2. price, stock, dan min_stock aku tambah cast integer
   oh yaa aku juga nambahin komentar singkat di scope dan accessor.

    untuk review di category.php, aku cocokin dengan dokumen, fillable (name, slug, description) dan relasi menus() hasMany udah sesuai. logika nya ga aku ubah, cuma nambahin komentar singkat.

## Prompt 4 - CategoryFactory dan MenuFactory

- Tanggal: 15 November 2026
- Branch: feature/master-schema
- Tool AI: Antigravity
- Bagian yang diminta: CategoryFactory dan MenuFactory

### Prompt yang dikirim

okee lanjut ya, aku lagi ngerjain proyek kelompok "NasiCafe POS" (Laravel 13, PHP 8.5, MySQL), modul Master Data. Branch: feature/master-schema. Model Category dan Menu sudah ada (sudah memakai HasFactory).

tugasnya buatin DUA file factory aja: database/factories/CategoryFactory.php dan database/factories/MenuFactory.php.

aturan CategoryFactory nya

1. name: unik (pakai unique()), nama kategori makanan/minuman berbahasa Indonesia.
2. slug: dibuat dari name memakai Str::slug().
3. description: teks pendek, nullable boleh.

aturan MenuFactory nya

1. category_id: Category::factory().
2. name: nama menu makanan/minuman berbahasa Indonesia.
3. price: integer kelipatan 500, antara 5000 sampai 50000.
4. stock: integer antara 0 sampai 50.
5. min_stock: 5.
6. image_path: null.
7. is_available: true.
8. Tambahkan state lowStock() (stock di bawah atau sama dengan min_stock) dan state outOfStock() (stock 0).

### Hasil

- File dihasilkan: database/factories/CategoryFactory.php dan database/factories/MenuFactory.php
- Commit mentah AI: chore: draft AI CategoryFactory dan MenuFactory (belum direview)

### Hasil review dan perubahan oleh saya

waktu review aku nemuin bug di CategoryFactory, slug dibuat dari variabel $name yg dipilih acak diawal, jadi kalo name di override (misalnya create(['name' => 'Minuman])), slug nya ga cocok dengan name. terus yauda aku perbaiki dengan ngejadiin name dan slug sebagai closure, jadi slug selalu dihitung dari name yg bener-bener dipakai. terus yg baris $name itu jadi mubazir, dan aku hapus, dan name acak ga lagi ngabisisn jatah unique() kalo di override. teruss unique() cuma punya 50 nama kategori, jadi seeder harus memberi category_id eksplisit kalo membuat banyak menu. yg Parameter ?int $stock di lowStock() adalah tambahan dari AI yg ga
aku minta, tapi yauda aku biarin karena berguna untuk menu stok kritis.

---

## Prompt 5 - CategoryRequest

- Tanggal: 17 November 2026
- Branch: feature/master-request
- Tool AI: Antigravity
- Bagian yang diminta: Form Request untuk kategori

### Prompt yang dikirim

haloo, sekarang kita lanjut yaa, aku masi ngerjain proyek kelompok "NasiCafe POS" (Laravel 13, PHP 8.5, MySQL), modul Master Data. Branch: feature/master-request. Model Category dan Menu udah ada.

tugasnya nanti kamu buatinn SATU file Form Request aja: app/Http/Requests/CategoryRequest.php.

terus aturannya

1. authorize() mengembalikan true (pembatasan role sudah ditangani middleware route).
2. rules():
    - name: required, string, max:100, unique di tabel categories kolom name. Saat update, abaikan id kategori yang sedang diedit (ambil dari route parameter 'category').
    - description: nullable, string.
3. Tambahkan messages() berbahasa Indonesia untuk name (required, unique, max).
4. Jangan membuat atau mengubah file lain. Komentar singkat berbahasa Indonesia seperlunya aja yaa

### Hasil

- File dihasilkan: app/Http/Requests/CategoryRequest.php
- Commit mentah AI: chore: draft AI CategoryRequest (belum direview)

### Hasil review dan perubahan oleh saya

tadi pas review sempet nemu celah, name unik tapi slug dubuat dari name... jadi "Nasi Spesial" dan "Nasi-Spesial" lolos validasi nama tp bentrok di slug dab bikin error ke database. nah aku nambahin pengecekan slug gitu sih di rules name lewat closure, tapi mengabaikan kategori yg lagi diedit..

---

## Prompt 6 - MenuRequest

- Tanggal: 17 November 2026
- Branch: feature/master-request
- Tool AI: Antigravity
- Bagian yang diminta: Form Request untuk menu

### Prompt yang dikirim

Kkkey lanjut yaa, aku masi ngerjain proyek kelompok "NasiCafe POS" (Laravel 13, PHP 8.5, MySQL), modul Master Data. Branch: feature/master-request. Model Category dan Menu sudah ada.

tugasnya buatin SATU file Form Request aja: app/Http/Requests/MenuRequest.php.

aturanya kayak gini

1. authorize() mengembalikan true (pembatasan role sudah ditangani middleware route).
2. rules():
    - category_id: required, exists:categories,id
    - name: required, string, max:150
    - price: required, integer, min:0
    - stock: required, integer, min:0
    - min_stock: required, integer, min:0
    - is_available: boolean
    - image: nullable, image, mimes:jpg,jpeg,png,webp, max:2048
3. tambahinn messages() berbahasa Indonesia untuk semua field, termasuk pesan khusus untuk image (tipe file salah dan ukuran melebihi 2MB).
4. Jangan membuat atau mengubah file lain. Komentar singkat berbahasa Indonesia seperlunya aja

### Hasil

- File dihasilkan: app/Http/Requests/MenuRequest.php
- Commit mentah AI: chore: draft AI MenuRequest (belum direview)

### Hasil review dan perubahan oleh saya

disini aku nemu 3 hal si yg diperbaiki

1. Checkbox is_available yg ga dicentang ga dikirim browser, jadi aku tambahin prepareForValidation() supaya nilainya jadi true/false.
2. price, stock, min_stock belum ada batas atas, padahal kolom unsignedInteger punya batas. sku tambahin max
3. aku tambahin jg pesan image.uploaded dan pesan untuk batas max baru. terus aku jg uda ngecek upload_max_filesize di php.ini supaya file 2MB ga ditolak PHP lebih dulu.
