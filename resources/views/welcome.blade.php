<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Sistem POS') }}</title>

        <!-- Google Fonts: Outfit -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-surface-light text-ink-primary">
        <div class="min-h-screen flex flex-col items-center justify-center p-6">
            <div class="bg-surface-card p-8 rounded-2xl shadow-sm border border-surface-border max-w-md w-full text-center">
                
                <!-- Badge Status Sistem (Menggunakan warna brand-50, brand-600 & status-success) -->
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-50 text-brand-600 text-xs font-medium mb-5">
                    <span class="w-2 h-2 rounded-full bg-status-success"></span>
                    Sistem Kasir Aktif
                </div>

                <!-- Judul & Subjudul -->
                <h1 class="text-3xl font-bold text-ink-primary mb-2">Aplikasi POS</h1>
                <p class="text-ink-muted mb-6">
                    Kelola transaksi penjualan, stok barang, dan laporan harian dalam satu layar.
                </p>

                <!-- Tombol Aksi Utama (Menggunakan brand-600 dan hover brand-700) -->
                <a href="#" class="inline-block w-full bg-brand-600 hover:bg-brand-700 text-white font-medium py-3 px-4 rounded-xl transition shadow-sm">
                    Masuk ke Kasir
                </a>

                <!-- Info Shift / Status Tambahan -->
                <div class="mt-6 pt-5 border-t border-surface-border flex items-center justify-between text-xs text-ink-muted">
                    <span>Status Server: <strong class="text-status-success font-medium">Online</strong></span>
                    <span>Shift Pagi</span>
                </div>
            </div>
        </div>
    </body>
</html>
