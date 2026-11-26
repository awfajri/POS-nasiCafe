# Log Audit Penggunaan AI — Fase 3 (QA Keamanan, Audit Statis, & Polesan)

## Informasi Umum
- **Proyek:** NasiCafe POS (Sistem Point of Sale & Self-Order Berbasis Web)
- **Anggota:** Muhamad Hafiz
- **Peran / Modul:** QA Keamanan, Audit Rute & Hak Akses, serta Verifikasi Lintas Peran
- **Branch Git:** `feature/auth-middleware` (Target PR: `develop`)
- **Fase Pengerjaan:** Fase 3 — Audit Statis, Automated Security Testing, dan QA Manual Browser

---

## 1. Ringkasan Artefak Kode yang Dihasilkan
1. **Penyelarasan Middleware Rute (`routes/auth.php`):**
   - Memperbarui middleware rute `POST /logout` dari `auth` menjadi `['auth', 'role:owner,cashier,customer']` agar seluruh grup rute mematuhi aturan kontrak bersama.
2. **Automated Security & Access Test Suites:**
   - `tests/Feature/RoleAccessTest.php`: Pengujian matriks hak akses 403 untuk setiap peran (`owner`, `cashier`, `customer`), pengalihan tamu ke halaman `/login`, dan pemutusan sesi otomatis bagi akun nonaktif.
   - `tests/Feature/RouteSecurityTest.php`: Audit otomatis seluruh daftar rute aplikasi untuk memastikan seluruh endpoint dilindungi middleware `guest` atau `auth` + `role:...`.
   - `tests/Feature/SecurityQaTest.php`: Pengujian ketahanan terhadap *mass assignment* (`role` dan `is_active`), percobaan injeksi parameter `role=owner` saat registrasi, escaping XSS (`<script>alert(1)</script>`) pada daftar pengguna, serta isolasi data pesanan antar-pelanggan.
   - `tests/Feature/ExampleTest.php`: Penyesuaian ekspektasi test bawaan Laravel pada rute `/` agar sesuai dengan perilaku aplikasi yang mengarahkan ke `/login`.

---

## 2. Log Interaksi Prompt, Debugging, & Verifikasi

### Langkah 1: Audit Statis Codebase
- **Ringkasan Prompt ke AI:**
  Meminta rangkaian perintah audit statis untuk memeriksa kepatuhan seluruh file proyek terhadap aturan keamanan kontrak (larangan `{!! !!}`, `$request->all()`, form `POST` tanpa `@csrf`, kolom terlarang pada `$fillable`, dan file sensitif di Git).
- **Hasil Audit Statis:**
  1. **Pencarian `{!!` di `resources/views`:** Kosong (0 temuan) — seluruh tampilan ter-escape dengan aman.
  2. **Pencarian `$request->all()` di `app/`:** Tidak ada pemanggilan pada kode (hanya ditemukan pada baris komentar dokumentasi di `app/Http/Controllers/CategoryController.php:35`).
  3. **Pencarian Form `POST` tanpa `@csrf`:** Kosong (0 temuan) — seluruh formulir terlindungi token CSRF.
  4. **Pemeriksaan `app/Models/User.php`:** Atribut `#[Fillable(['name', 'email', 'password'])]` hanya mengizinkan tiga kolom dasar; `role` dan `is_active` terkunci dari *mass assignment*.
  5. **Pemeriksaan `git ls-files`:** Tidak ada `.env`, `vendor/`, atau `node_modules/` yang terlacak di repositori (hanya `.env.example`).

### Langkah 2: Penyusunan & Debugging Test Keamanan Otomatis
- **Ringkasan Prompt ke AI:**
  Meminta pembuatan tiga test suite keamanan (`RoleAccessTest`, `RouteSecurityTest`, dan `SecurityQaTest`) serta membantu menganalisis hasil eksekusi ketika muncul kegagalan pada proses pengujian awal.
- **Temuan & Perbaikan Selama Pengujian:**
  1. **Rute Upload Bawaan Framework pada `RouteSecurityTest`:**
     - *Masalah:* Pengujian mendeteksi rute internal Laravel `PUT /storage/{path} (storage.local.upload)` sebagai rute tanpa middleware `auth` + `role:`.
     - *Perbaikan:* Memperbarui kondisi pengecualian rute framework menjadi `str_starts_with($name, 'storage.local')` agar rute internal Laravel dilewati, sementara seluruh rute aplikasi tetap diaudit ketat.
  2. **Persistensi Sesi Pengujian pada `RoleAccessTest`:**
     - *Masalah:* Pada iterasi loop pengujian matriks rute, status login dari `$this->actingAs($user)` terbawa ke iterasi rute berikutnya sehingga pengujian akses tamu (`$this->get($url)`) terbaca masih login dan menghasilkan status `403` alih-alih `302` ke `/login`. Selain itu, rute `owner.reports.index` belum tersedia di branch `develop` sehingga memicu status *incomplete*.
     - *Perbaikan:* Menambahkan `auth()->logout();` sebelum pengujian akses tamu pada setiap iterasi rute, serta mengomentari sementara entri `owner.reports.index` dengan catatan agar diaktifkan kembali setelah modul laporan selesai di-merge.
  3. **Penyesuaian `ExampleTest`:**
     - *Masalah:* Test bawaan Laravel mengharapkan status `200` pada `/`, sedangkan aplikasi mengarahkan rute root ke `/login`.
     - *Perbaikan:* Mengubah asersi menjadi `$response->assertRedirect(route('login'))`.
- **Hasil Akhir Eksekusi `php artisan test`:**
  Seluruh **38 test (100%) lulus (`PASS`)**, mencakup `AuthTest`, `PasswordTest`, `RoleAccessTest`, `RoleMiddlewareTest`, `RouteSecurityTest`, `SecurityQaTest`, `UserControllerTest`, dan `UserManagementTest`.

### Langkah 3: QA Manual di Browser
- **Ringkasan Prompt ke AI:**
  Meminta panduan urutan pengujian manual end-to-end di browser menggunakan database hasil `php artisan migrate:fresh --seed` untuk memverifikasi matriks 403 dan 12 skenario keamanan.
- **Hasil Pengujian Manual:**
  1. **Matriks Akses Lintas Peran (Tamu, Owner, Kasir, Pelanggan):** Seluruh endpoint yang sudah tersedia di `develop` mengembalikan halaman (`OK`) untuk peran yang berhak, mengalihkan tamu ke `/login`, dan mengembalikan `403` bagi peran yang tidak berhak.
  2. **Autentikasi & Akun Nonaktif (S1–S3):** Pesan kegagalan login konsisten (*"Email atau kata sandi salah, atau akun nonaktif"*), akun yang dinonaktifkan Owner tidak dapat login, dan sesi aktif yang sedang berjalan langsung dikeluarkan ke `/login` saat pengguna melakukan navigasi berikutnya.
  3. **Proteksi Registrasi & Isolasi Data (S4–S5):** Percobaan menyisipkan input `role=owner` dan `is_active=0` melalui DevTools saat registrasi tetap menghasilkan akun dengan peran `customer` dan status aktif. Pelanggan hanya dapat melihat riwayat serta pesanan aktif miliknya sendiri.
  4. **Keamanan HTTP & Rate Limiting (S6–S12):** Akses langsung `GET /logout` ditolak dengan `405 Method Not Allowed`; percobaan gagal login sebanyak 6 kali dalam 1 menit ditolak dengan `429 Too Many Requests`; validasi ubah kata sandi berjalan sesuai aturan; Owner tidak dapat mengubah peran atau menonaktifkan dirinya sendiri; penghapusan token CSRF menghasilkan `419 Page Expired`; dan navigasi setelah logout tetap diarahkan kembali ke halaman login.

---

## 3. Catatan Temuan pada Modul Anggota Lain (Untuk Koordinasi Tim)
Sesuai aturan kontrak kerja tim agar tidak mengubah file milik anggota lain secara langsung, temuan berikut didokumentasikan untuk dilaporkan ke pemilik file terkait:
1. **Duplikasi Komponen `<x-low-stock-banner />`:** Pada halaman `/owner/dashboard`, banner peringatan stok kritis tampil sebanyak 3 kali karena irisan pemanggilan antara master layout (`resources/views/layouts/app.blade.php`) dan halaman dashboard owner (`resources/views/owner/dashboard.blade.php`).
2. **Duplikasi Komponen `<x-alert />`:** Pesan flash alert tampil ganda pada beberapa halaman karena komponen `<x-alert />` sudah dimuat di master layout utama.
3. **Ketersediaan Rute Laporan:** Rute `owner.reports.index` belum tersedia di branch `develop` saat QA Fase 3 dilakukan dan telah dicatat pada `tests/Feature/RoleAccessTest.php`.
