## Fase 2 — Layar POS Kasir & Self-Order Pelanggan

### Prompt 1 — Rute, Controller, dan View POS Kasir & Self-Order

- Tanggal: 23 November 2026
- Branch: feature/pos-self-order-ui
- Tool AI: Antigravity
- Bagian yang diminta: Routing (`routes/pos.php`), Controller (`PosController` & `CustomerOrderController`), dan View (`pos/index.blade.php` & `customer/order.blade.php`)

#### Prompt yang dikirim

Halo Antigravity, bantu kerjakan jobdesk **Fase 2** saya (**Auf Fajri**) pada proyek **NasiCafe POS** (Laravel 12 + Tailwind CSS + Alpine.js).

=== 1. BATASAN KEPEMILIKAN FILE (STRICT SCOPE) ===
Kerjakan HANYA modul milik saya di Fase 2. DILARANG keras membuat, mengubah, atau menyentuh file milik anggota tim lain (Hafiz, Imas, Gading, Martin, Dimas) maupun file Fase 0/1 yang sudah dikunci (`routes/web.php`, `resources/views/layouts/app.blade.php`, `app/Services/*`, `app/Models/*`, `database/migrations/*`).

File yang BOLEH kamu buat/edit HANYA 5 file berikut:

1. `routes/pos.php`
2. `app/Http/Controllers/PosController.php`
3. `app/Http/Controllers/CustomerOrderController.php`
4. `resources/views/pos/index.blade.php`
5. `resources/views/customer/order.blade.php`

Sebelum menulis kode, **BACA terlebih dahulu** file-file berikut yang sudah ada di repository agar 100% sinkron dengan struktur, komponen, dan kontrak yang berlaku:

- `resources/views/layouts/app.blade.php` (ikuti palet warna, tipografi, slot/section, dan shell layout yang sudah dibangun)
- `app/Http/Requests/StoreCustomerOrderRequest.php`
- `app/Services/TransactionService.php`
- `app/Models/Menu.php` dan `app/Models/Category.php`
- `app/helpers.php`
- `routes/transactions.php` (cek route `pos.store` milik Gading) dan `routes/auth.php` (cek route `customer.dashboard` milik Hafiz)

=== 2. KONTRAK BERSAMA & ATURAN BISNIS WAJIB ===

1. **Routes (`routes/pos.php`):**
    - Grup `middleware(['auth', 'role:cashier'])`:
        - `GET /pos` -> `[PosController::class, 'index']` dengan nama `pos.index`
    - Grup `middleware(['auth', 'role:customer'])`:
        - `GET /customer/order` -> `[CustomerOrderController::class, 'index']` dengan nama `customer.order`
        - `POST /customer/order` -> `[CustomerOrderController::class, 'store']` dengan nama `customer.order.store`
    - DILARANG membuat route `customer.dashboard` (itu milik Hafiz di `routes/auth.php`).

2. **Perilaku Menu (`is_available` vs `stock = 0`) — SANGAT PENTING:**
    - Pada `PosController@index()` dan `CustomerOrderController@index()`, ambil semua `Category` beserta relasi `menus` yang **`is_available = true`** (gunakan scope/query yang hanya memfilter `is_available = true`, JANGAN menyembunyikan menu yang `stock = 0`).
    - Jika menu memiliki `stock <= 0` (sementara `is_available = true`), kartu menu **TETAP DITAMPILKAN** di layar POS Kasir maupun Self-Order Pelanggan, namun diberi tanda/badge **"Habis"**, tampilan visual redup/disabled, dan **tidak bisa diklik / tidak bisa ditambahkan ke keranjang**.
    - Jika `stock > 0` dan `stock <= min_stock`, tampilkan badge sisa stok menipis (misalnya `"Sisa 3"`). Jika stok aman (`stock > min_stock`), tetap tampilkan info sisa stok secara rapi.

3. **Controllers (`PosController` & `CustomerOrderController`):**
    - `PosController@index()`: kirim data kategori & menu aktif ke view `pos.index`.
    - `CustomerOrderController@index()`: kirim data kategori & menu aktif ke view `customer.order`.
    - `CustomerOrderController@store(StoreCustomerOrderRequest $request)`:
        - Gunakan `$validated = $request->validated();` (dilarang `$request->all()` atau `$request->input()` mentah).
        - CUKUP memanggil service Gading dengan signature persis:
            ```php
            $transaction = app(TransactionService::class)->createOrder($validated['items'],
                $request->user()->name,$validated['table_number'] ?? null,
                'self_order',
                $request->user()
            );
            ```
        - **DILARANG** menulis ulang logika hitung total, generate kode nota, atau decrement/cek stok di controller (semua error bisnis dari `TransactionService` otomatis melempar `ValidationException` dan ditangani Laravel).
        - Setelah sukses, redirect ke `route('customer.dashboard')` dengan flash message key `'success'` berisi kode nota, contoh:
          `->with('success', "Pesanan berhasil dibuat! Kode nota: {$transaction->invoice_code}")`

4. **Kontrak Form Submission POS Kasir (`resources/views/pos/index.blade.php`):**
    - Layout **split-screen** pada desktop (panel kiri katalog menu dengan scroll independen, panel kanan keranjang & checkout dengan scroll independen) dan tetap rapi di tablet/mobile.
    - Panel Kiri: search bar cepat (opsional, client-side Alpine), tab/pill filter kategori ("Semua" + daftar kategori), grid kartu menu (`$menu->image_url`, nama menu, `rupiah($menu->price)`, badge sisa stok / badge `"Habis"` non-interaktif).
    - Panel Kanan (Alpine.js):
        - Daftar item keranjang dengan stepper `-` / `+` dan tombol hapus. **Wajib kunci tombol `+` (`disabled`) jika `item.quantity >= item.stock`** agar tidak melebihi stok tersedia.
        - Input `customer_name` (`name="customer_name"`, wajib diisi / `required`) dan `table_number` (`name="table_number"`, opsional).
        - Form wajib pakai `method="POST"`, `action="{{ route('pos.store') }}"`, dan `@csrf`.
        - Hidden input item keranjang WAJIB menggunakan indeks berurutan (`0, 1, 2, ...` via `x-for="(item, index) in cart"`) agar tidak ada indeks bolong saat item dihapus:
            - `:name="`items[${index}][menu_id]`"` -> `:value="item.id"`
            - `:name="`items[${index}][quantity]`"` -> `:value="item.quantity"`
        - **DILARANG** mengirim field `price`, `subtotal`, atau `total` ke server (aturan keamanan kontrak: harga selalu dihitung dari database).
        - Dua tombol submit:
            - **"Simpan Pesanan"** -> mengirim `redirect_to_payment` bernilai `"0"`.
            - **"Simpan & Bayar"** -> mengirim `redirect_to_payment` bernilai `"1"`.
        - Jika ada validasi gagal dari server (`@if ($errors->any())`), tampilkan pesan error dengan jelas (atau gunakan `<x-alert />` jika belum dirender otomatis oleh `layouts.app`).

5. **Kontrak Halaman Self-Order Pelanggan (`resources/views/customer/order.blade.php`):**
    - Desain **mobile-first** yang nyaman di layar HP sekaligus tetap proporsional di desktop.
    - Header sapaan singkat + filter kategori horizontal scroll/pill + pencarian menu + katalog menu dengan `$menu->image_url`, nama, harga (`rupiah($menu->price)`), badge stok menipis, dan state disabled + badge `"Habis"` jika `stock <= 0`.
    - Beri _bottom padding_ (`pb-28` / `pb-32`) pada daftar menu agar kartu paling bawah tidak tertutup oleh floating cart bar.
    - **Floating Cart Bar** fixed di bagian bawah layar (muncul saat keranjang berisi >= 1 item, menampilkan total item & estimasi total harga) yang saat diklik membuka **bottom-sheet / drawer / modal ringkasan keranjang**:
        - Stepper `-` / `+` per item (terkunci maksimal `item.stock`) & hapus item.
        - Input opsional nomor meja (`name="table_number"`, maksimal 10 karakter sesuai `StoreCustomerOrderRequest`).
        - Form `POST` ke `route('customer.order.store')` dengan `@csrf`, hidden inputs `items[index][menu_id]` dan `items[index][quantity]` (indeks berurutan, tanpa mengirim `price`), dan tombol submit **"Pesan Sekarang"** (disertai state loading/disabled saat submit agar tidak double-submit).

6. **Standar Kualitas Kode, Keamanan & Estetika UI:**
    - Kedua view wajib memakai `@extends('layouts.app')`, `@section('title', ...)`, dan `@section('content')`.
    - Gunakan helper PHP `rupiah($nominal)` untuk rendering server-side, accessor `$menu->image_url` untuk gambar menu (jangan hardcode path storage manual), dan `Intl.NumberFormat('id-ID')` di Alpine.js untuk kalkulasi dinamis.
    - Keamanan: wajib `@csrf` di setiap form, gunakan `{{ ... }}` untuk semua output (dilarang `{!! !!}`).
    - Estetika UI: selaraskan 100% dengan design language yang sudah ada di `resources/views/layouts/app.blade.php` (hindari tampilan template polos/generik; gunakan hierarchy tipografi yang tegas, `tabular-nums` untuk harga & stok, micro-interaction halus pada tombol/stepper, kontras status stok yang jelas, dan feedback visual saat menu ditambahkan ke keranjang).

Setelah selesai mengimplementasikan ke-5 file tersebut, jalankan pengecekan sintaks/route (`php artisan route:list --path=pos` dan `php artisan route:list --path=customer`) untuk memastikan tidak ada error.

#### Hasil

- File dihasilkan:
    - `routes/pos.php`
    - `app/Http/Controllers/PosController.php`
    - `app/Http/Controllers/CustomerOrderController.php`
    - `resources/views/pos/index.blade.php`
    - `resources/views/customer/order.blade.php`
- Commit mentah AI: `feat: implementasi rute, controller, dan view dasar pos & self-order`

#### Hasil review dan perubahan oleh saya

Setelah mengecek seluruh file hasil generate AI terhadap kontrak tim dan keamanan kode:

- **Yang sudah sesuai kontrak dan tidak diubah:**
    - `routes/pos.php` sudah menerapkan `auth` + `role:cashier` dan `role:customer` dengan nama route `pos.index`, `customer.order`, dan `customer.order.store` tanpa menyentuh `customer.dashboard` milik Hafiz.
    - `CustomerOrderController@store()` sudah memakai `$request->validated()`, memanggil `TransactionService::createOrder()` dengan signature eksak, serta redirect dengan flash message `success` berisi `invoice_code`.
    - Kedua form tidak mengirimkan field harga mentah (`price`/`subtotal`/`total`), memakai `@csrf`, indeks `items[index]` selalu berurutan saat item dihapus, tombol `+` terkunci saat `quantity >= stock`, dan menu `stock <= 0` tetap tampil berbadge `"Habis"` serta non-interaktif.

- **3 hal yang saya temukan saat review dan saya perbaiki pada commit lanjutan:**
    1. **Query redundan di `PosController` dan `CustomerOrderController`:** AI menjalankan dua query terpisah untuk data menu aktif (`Category::with('menus')` dan `Menu::with('category')`). Saya ubah query `Category` menjadi `withCount(['menus' => ...])` dan memakai `$category->menus_count` di kedua view agar lebih hemat query dan memori.
    2. **State keranjang hilang saat validasi server gagal (`ValidationException`):** Pada `pos/index.blade.php` dan `customer/order.blade.php`, `cart` diinisialisasi kosong (`[]`). Jika stok habis saat submit dan server me-redirect kembali dengan `old('items')`, keranjang ikut ter-reset. Saya tambahkan method `init()` pada Alpine (`posApp()` dan `customerOrderApp()`) untuk memulihkan item dari `old('items', [])` (dibatasi maksimal stok terkini) serta membuka drawer keranjang otomatis pada halaman Self-Order bila `$errors->any()` bernilai `true`.
    3. **Konsistensi serialisasi JSON di Alpine.js:** Perbandingan ID kategori pada pill filter dan inisialisasi `old('customer_name')` / `old('table_number')` saya ganti ke `@json(...)` agar aman terhadap karakter kutip dan konsisten dengan tipe data string di Alpine.
