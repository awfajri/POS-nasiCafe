## Deskripsi Perubahan (PR Description)
Ringkasan perubahan atau fitur yang dikerjakan pada pull request ini:
- 

## Tipe Perubahan
- [ ] Fondasi / Setup Proyek (Skeleton, Config, Tooling)
- [ ] Fitur Baru (Feature)
- [ ] Perbaikan Bug (Bug Fix)
- [ ] Refactor Kode
- [ ] Dokumentasi

## Checklist Definition of Done (DoD)
Harap pastikan semua kriteria berikut terpenuhi sebelum meminta review:
- [ ] **Scope Pengerjaan**: Hanya mengerjakan tugas/fitur yang sesuai dengan hak akses / jobdesk tiket.
- [ ] **Standar Kode**: Kode rapi dan mengikuti konvensi Laravel / PSR-12.
- [ ] **Routing**: `php artisan route:list` berjalan lancar tanpa error route definition.
- [ ] **Asset Build**: `npm run build` berhasil tanpa error kompilasi Tailwind CSS / Vite.
- [ ] **Database & Migrasi**: Migrasi dan seeder berjalan tanpa error (`php artisan migrate --seed`).
- [ ] **Akses & Keamanan**: Middleware dan hak akses role (`owner`, `cashier`, `customer`) terpasang sesuai kontrak.
- [ ] **Blade & Tampilan**: Tampilan responsif dan menggunakan layout master (`layouts/app.blade.php` / `layouts/guest.blade.php`) serta komponen bersama.
- [ ] **Kebersihan Kode**: Tidak ada file sensitif (.env, credentials) atau baris debug (`dd()`, `dump()`, `console.log`).
- [ ] **Pengujian Manual**: Sudah diuji dan diverifikasi secara lokal di browser.

## Catatan Tambahan untuk Reviewer
- 
