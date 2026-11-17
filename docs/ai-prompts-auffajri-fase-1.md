## Fase 1 — Master Layout, Komponen UI, & StoreCustomerOrderRequest

### 1. Ringkasan Prompt yang Digunakan

Meminta AI men-generate kerangka `layouts/app.blade.php` dengan navigasi dinamis per peran, komponen Blade bersama (`x-status-badge`, `x-payment-badge`, `x-stat-card`, `x-low-stock-banner`), serta `StoreCustomerOrderRequest` sesuai kontrak, dengan menyesuaikan gaya visual komponen dari referensi desain Figma.

### 2. Hasil Review & Audit Mandiri

- **File yang Langsung Lolos Audit (Tanpa Perubahan dari Draft Awal):**
    - `app/Http/Requests/StoreCustomerOrderRequest.php`: Aturan validasi (`table_number`, `items`, `menu_id`, `quantity`), pesan error bahasa Indonesia, dan `authorize()` khusus role `customer` sudah sesuai kontrak.
    - `app/View/Components/LowStockBanner.php`: Query `Menu::lowStock()`, pembatasan `shouldRender()` hanya untuk `owner` dan `cashier`, serta penanganan error sudah sesuai.
- **Temuan Masalah pada Hasil Generate Awal:**
    - **Keamanan (XSS):** Pada `stat-card.blade.php` versi awal terdapat directive `{!! $icon !!}` untuk rendering ikon kustom yang melanggar aturan larangan unescaped output di kontrak.
    - **UI & Aset Ikon:** AI menggunakan tag `<svg>` manual dengan path yang tidak rapi serta emoji (`⚠️`, `✕`) sehingga tidak konsisten dengan desain Figma. Selain itu, font `Outfit` belum terpasang di `<head>` dan CSS utama.
    - **Layout Navigasi & Edge Case:** Top navbar Owner yang berisi 6 menu menumpuk dan patah ke baris kedua karena digabung sejajar dengan logo dan profil. Tombol lonceng notifikasi kosong (tidak ada di kontrak), dan saat kondisi _guest_ (`auth()->guest()`), menu navigasi masih ikut ter-render.

### 3. Tindakan Perbaikan yang Dilakukan

1. Menginstal paket resmi `mallardduck/blade-lucide-icons` dan mengganti seluruh `<svg>` manual, emoji, serta `{!! $icon !!}` menjadi komponen ikon standar `<x-lucide-... />` di `app.blade.php`, `stat-card.blade.php`, `low-stock-banner.blade.php`, dan `alert.blade.php`.
2. Memuat Google Font `Outfit` di `layouts/app.blade.php` (serta mengatur `--font-sans` di `resources/css/app.css`), dan menyesuaikan bobot angka utama pada `x-stat-card` menjadi `font-semibold` agar lebih proporsional untuk nominal Rupiah panjang.
3. Memisahkan header navbar khusus role `owner` menjadi 2 baris (baris atas logo & profil, baris bawah 6 pill navigasi sejajar), menghapus tombol lonceng tanpa fungsi, mengamankan render navigasi saat _guest_, serta mengunci tampilan _bottom navigation bar_ untuk role `customer`.
4. Memperbarui `low-stock-banner.blade.php` dengan ringkasan teks 1 baris, tombol arah ke `owner.menus.index` khusus Owner, dan fitur tutup berbasis Alpine.js.

### 4. Verifikasi Lokal

- Seluruh komponen dan simulasi tampilan 3 role (`owner`, `cashier`, `customer`) beserta 4 state alert sudah diuji langsung di browser dan `php artisan route:list` / `php artisan view:cache` berjalan tanpa error.
