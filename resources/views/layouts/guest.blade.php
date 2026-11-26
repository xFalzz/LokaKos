<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title . ' | LokaKos' : 'Masuk & Daftar | LokaKos' }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Golos+Text:wght@400;500;600;700&family=Instrument+Sans:wght@500;600;700&display=swap" rel="stylesheet">

        <script>
            (function () {
                try {
                    var saved = localStorage.getItem('lokakos-theme');
                    var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    if (saved === 'dark' || (!saved && prefersDark)) {
                        document.documentElement.classList.add('dark');
                    }
                } catch (e) {}
            })();
        </script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[var(--bg-canvas)] text-[var(--text-primary)] antialiased flex flex-col">
        <header class="border-b border-[var(--border-subtle)] bg-[var(--bg-canvas)]/95">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 md:h-20 flex items-center justify-between gap-4">
                <a href="{{ route('home') }}" class="inline-flex items-baseline gap-2.5 py-2">
                    <x-application-logo class="text-2xl" />
                    <span class="hidden sm:inline text-xs font-medium text-[var(--text-secondary)]">Kawasan UMY</span>
                </a>
                <div class="flex items-center gap-2.5">
                    <a href="{{ route('kos.index') }}" class="lk-btn-secondary text-sm">
                        Lihat Katalog
                    </a>
                    <x-lokakos.theme-toggle />
                </div>
            </div>
        </header>

        <main class="flex-1 flex items-center justify-center px-4 py-10 sm:py-14">
            <div class="w-full max-w-5xl grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
                <div class="hidden lg:flex lg:col-span-5 rounded-[var(--radius-panel)] bg-[var(--bg-espresso)] text-[var(--espresso-ink)] p-8 flex-col justify-between overflow-hidden relative">
                    @if (file_exists(public_path('images/demo/hero-kos-umy.jpg')))
                        <img
                            src="{{ asset('images/demo/hero-kos-umy.jpg') }}"
                            alt="Ilustrasi demo arsitektur kos kawasan UMY"
                            class="absolute inset-0 w-full h-full object-cover opacity-35"
                        >
                        <div class="absolute inset-0 bg-gradient-to-t from-[#191411]/95 via-[#2C221D]/70 to-[#2C221D]/55"></div>
                    @endif

                    <div class="relative z-10">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[var(--espresso-gold)]">LokaKos Kawasan UMY</p>
                        <h1 class="mt-3 font-display text-3xl font-semibold leading-tight text-[#FAF7F2]">
                            Hunian terverifikasi di sekitar Kampus Terpadu UMY
                        </h1>
                        <p class="mt-4 text-sm text-[#E5DDD5] leading-relaxed">
                            Bandingkan area Ngebel, Geblagan, Brajan, hingga Tamantirto berdasarkan jarak kampus, tipe penghuni, dan ketersediaan kamar aktual.
                        </p>
                    </div>

                    <div class="relative z-10 mt-8 p-4 rounded-[var(--radius-control)] bg-black/35 border border-white/15 space-y-1.5 text-xs text-[#E5DDD5]">
                        <p class="font-semibold text-[#FAF7F2]">Akses terpisah sesuai peran</p>
                        <p class="leading-relaxed">
                            Mahasiswa memesan kamar dan mengunggah bukti transfer manual; Mitra mengelola properti kos dan memverifikasi pembayaran.
                        </p>
                    </div>
                </div>

                <div class="lg:col-span-7 rounded-[var(--radius-panel)] bg-[var(--bg-surface)] border border-[var(--border-subtle)] p-6 sm:p-10 lk-elevated">
                    {{ $slot }}
                </div>
            </div>
        </main>
    </body>
</html>
