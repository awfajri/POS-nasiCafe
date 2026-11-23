## Fase 1 — Skema User, RoleMiddleware, & Autentikasi (Login, Register, Logout)

### 1. Ringkasan Prompt yang Digunakan

Meminta AI (Claude) menyusun langkah kerja dan kode untuk jobdesk Fase 1: migration kolom `role` dan `is_active`, model `User` beserta `UserFactory`, `RoleMiddleware`, `LoginRequest` dan `RegisterRequest`, `AuthController`, `routes/auth.php`, serta tampilan login dan register. Prompt disertai dokumen panduan tim (`Workspace_Projek_NasiCafe.pdf`), hasil kerja sebelumnya, dan screenshot error sebagai konteks. Hasil generate diaudit ulang terhadap kontrak tim sebelum di-commit. AI juga dipakai untuk memandu alur Git (branch, pull, push, Pull Request) sesuai panduan tim.

### 2. Hasil Review & Audit Mandiri

- **File yang Langsung Lolos Audit (Tanpa Perubahan dari Draft Awal):**
    - `app/Http/Requests/LoginRequest.php` dan `app/Http/Requests/RegisterRequest.php`: aturan validasi (`email`, `password`, `name` max:100, email unik, password min:8 confirmed) dan pesan error bahasa Indonesia sudah sesuai kontrak.
    - `app/Http/Controllers/AuthController.php`: `Auth::attempt` dengan `is_active => true`, pesan gagal generik, regenerate session setelah login, `role` diset eksplisit `customer` saat register (tidak dari request), serta logout dengan invalidate session dan regenerate token sudah sesuai kontrak.
    - `routes/auth.php`: grup `guest` dengan `throttle:5,1` pada POST login dan register, serta logout lewat POST dengan middleware `auth`, sudah sesuai kontrak.
- **Temuan Masalah pada Hasil Generate Awal:**
    - **Pelanggaran kontrak keamanan:** Draft awal `User.php` memasukkan `role` dan `is_active` ke `$fillable`, padahal kontrak tim melarangnya (risiko mass assignment). Draft awal juga menyarankan rute login di `routes/web.php` dan nama route `login.store`, yang tidak sesuai kontrak.
    - **Kepatuhan pada kepemilikan file & penamaan:** Draft awal mengubah `bootstrap/app.php` (file milik Auf, alias `role` sudah dipasang di Fase 0) dan membuat `RoleMiddleware.php` sendiri sehingga berpotensi konflik dengan stub Auf. Nama file migration dari Artisan (`2026_11_13_064659_...`) tidak sesuai urutan nama di kontrak. Method `dashboardUrl()`, `hasRole()`, dan `roleLabel()` tidak ada di kontrak (yang diminta: `isOwner()`, `isCashier()`, `isCustomer()`, `homeRoute()`, dan relasi `transactions()`).
    - **Bug halus:** Saat file `User.php` ditimpa, trait `HasFactory` dan `Notifiable` ikut hilang. Di `UserFactory`, model hasil factory memiliki `is_active = null` sehingga `RoleMiddleware` menolak user yang seharusnya aktif (3 dari 5 test awal gagal dengan status 302). Pada view, tag `<form>` tidak tertutup setelah penghapusan atribut `class`, sehingga `@csrf` ikut terbawa ke dalam tag dan berisiko menimbulkan error 419.
    - **Pesan & pembatasan login:** Draft awal `RoleMiddleware` mengirim pesan akun nonaktif lewat `withErrors`, padahal komponen `<x-alert />` hanya membaca flash key `success` dan `error`. Draft awal `LoginRequest` juga membuat rate limiter sendiri, sedangkan kontrak sudah menetapkan `throttle:5,1` di route.
    - **UI & UX:** Tampilan awal login dan register terkesan template: ada kartu ganda dan judul yang mengulang header `layouts.guest`, serta warna tombol yang tidak mengikuti tema aplikasi.

### 3. Tindakan Perbaikan yang Dilakukan

1. Mengubah `$fillable` pada `User` menjadi hanya `name`, `email`, dan `password`. `role` dan `is_active` diset eksplisit (saat register dan lewat state factory).
2. Rollback migration, lalu me-rename menjadi `2026_11_14_000001_add_role_and_is_active_to_users_table.php`. Kolom `role` diberi `index()` dan `down()` menghapus index sebelum menghapus kolom.
3. Menyesuaikan `User.php` dengan kontrak: konstanta peran, `isOwner()`/`isCashier()`/`isCustomer()`, `homeRoute()` (mengembalikan nama route), relasi `transactions()`, serta mengembalikan trait `HasFactory` dan `Notifiable`.
4. Membatalkan perubahan pada `bootstrap/app.php`, dan `RoleMiddleware.php` hanya mengisi stub milik Auf. Isinya mengikuti kontrak: belum login diarahkan ke `login`, akun nonaktif di-logout dengan invalidate session dan regenerate token, peran tidak cocok `abort(403, 'Akses ditolak')`.
5. Melengkapi `UserFactory` dengan state `owner()`, `cashier()`, `customer()`, `inactive()` dan nilai default `role = customer` serta `is_active = true`.
6. Mengirim pesan akun nonaktif lewat flash key `error` agar tampil di `<x-alert />`, dan memakai `throttle:5,1` di route sebagai pengganti rate limiter buatan sendiri.
7. Memindahkan semua rute ke `routes/auth.php` dengan nama `login`, `login.attempt`, `register`, `register.store`, dan `logout`, tanpa mengubah `routes/web.php`.
8. Menutup kembali tag `<form>` dan memastikan `@csrf` berada di dalam form pada `login.blade.php` dan `register.blade.php`.
9. Menyesuaikan tampilan login dan register dengan desain buatan sendiri, menggunakan font `Outfit` dan token warna dari `resources/css/app.css`.

### 4. Verifikasi Lokal

- `php artisan test --filter=RoleMiddlewareTest` lulus (5 test, 10 assertion): tamu diarahkan ke login, akun nonaktif dikeluarkan dengan pesan, peran salah mendapat 403, peran benar boleh masuk, dan beberapa peran dalam satu rute.
- Login, register, dan logout sudah diuji manual di browser dan berjalan sesuai kontrak.
- `ExampleTest` bawaan Laravel gagal (status 302, bukan 200) karena `/` kini melakukan redirect sesuai Fase 0. Kegagalan ini tidak berasal dari perubahan Fase 1.
- Migration dan model `User` sudah melalui review dan ter-merge ke `develop` bersama `UserFactory`.
