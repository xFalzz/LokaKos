<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title . ' | LokaKos' : 'LokaKos | Pemesanan Kos Multi-Mitra Kawasan UMY' }}</title>

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

        @stack('head')
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen flex flex-col bg-[var(--bg-canvas)] text-[var(--text-primary)] antialiased">
        <a href="#konten-utama" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 lk-btn-primary">
            Lompat ke konten utama
        </a>

        <header
            x-data="{ mobileMenu: false }"
            @keydown.escape.window="mobileMenu = false"
            class="sticky top-0 z-40 bg-[var(--bg-canvas)]/95 backdrop-blur-sm border-b border-[var(--border-subtle)]"
        >
            <div class="max-w-[1320px] mx-auto px-4 sm:px-6 lg:px-8 h-16 md:h-20 flex items-center justify-between gap-4">
                <a href="{{ route('home') }}" class="inline-flex items-baseline gap-2.5 py-2">
                    <x-application-logo class="text-2xl tracking-tight" />
                    <span class="hidden sm:inline text-xs font-medium text-[var(--text-secondary)]">Kawasan UMY</span>
                </a>

                <nav aria-label="Navigasi utama" class="hidden md:flex items-center gap-2">
                    <a href="{{ route('home') }}" class="inline-flex items-center min-h-[44px] px-4 rounded-full text-sm font-medium {{ request()->routeIs('home') ? 'text-[var(--brand-gold-text)] bg-[var(--bg-tint)]' : 'text-[var(--text-primary)] hover:bg-[var(--bg-tint)]' }} transition-colors">
                        Beranda
                    </a>
                    <a href="{{ route('kos.index') }}" class="inline-flex items-center min-h-[44px] px-4 rounded-full text-sm font-medium {{ request()->routeIs('kos.*') ? 'text-[var(--brand-gold-text)] bg-[var(--bg-tint)]' : 'text-[var(--text-primary)] hover:bg-[var(--bg-tint)]' }} transition-colors">
                        Cari Kos
                    </a>
                    <a href="{{ route('home') }}#area-umy" class="inline-flex items-center min-h-[44px] px-4 rounded-full text-sm font-medium text-[var(--text-primary)] hover:bg-[var(--bg-tint)] transition-colors">
                        Area UMY
                    </a>
                    <a href="{{ route('mitra.info') }}" class="inline-flex items-center min-h-[44px] px-4 rounded-full text-sm font-medium {{ request()->routeIs('mitra.info') ? 'text-[var(--brand-gold-text)] bg-[var(--bg-tint)]' : 'text-[var(--text-primary)] hover:bg-[var(--bg-tint)]' }} transition-colors">
                        Gabung Mitra
                    </a>
                </nav>

                <div class="flex items-center gap-2 sm:gap-2.5">
                    <x-lokakos.theme-toggle />

                    @auth
                        <a href="{{ route('dashboard') }}" class="hidden md:inline-flex lk-btn-primary text-sm">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="hidden md:inline-flex lk-btn-secondary text-sm">
                            Masuk
                        </a>
                        <a href="{{ route('register') }}" class="hidden md:inline-flex lk-btn-primary text-sm">
                            Daftar
                        </a>
                    @endauth

                    <button
                        type="button"
                        @click="mobileMenu = !mobileMenu"
                        :aria-expanded="mobileMenu.toString()"
                        aria-controls="nav-mobile-drawer"
                        class="md:hidden inline-flex items-center justify-center gap-1.5 min-h-[44px] px-4 rounded-full border border-[var(--border-control)] bg-[var(--bg-surface)] text-sm font-medium text-[var(--text-primary)]"
                    >
                        <span x-text="mobileMenu ? 'Tutup' : 'Menu'">Menu</span>
                    </button>
                </div>
            </div>

            <div
                id="nav-mobile-drawer"
                x-cloak
                x-show="mobileMenu"
                x-transition.opacity.duration.180ms
                class="md:hidden border-t border-[var(--border-subtle)] bg-[var(--bg-surface)] px-4 py-4 space-y-2"
            >
                <a href="{{ route('home') }}" @click="mobileMenu = false" class="flex items-center min-h-[44px] px-3.5 rounded-[var(--radius-control)] text-sm font-medium text-[var(--text-primary)] hover:bg-[var(--bg-tint)]">
                    Beranda
                </a>
                <a href="{{ route('kos.index') }}" @click="mobileMenu = false" class="flex items-center min-h-[44px] px-3.5 rounded-[var(--radius-control)] text-sm font-medium text-[var(--text-primary)] hover:bg-[var(--bg-tint)]">
                    Cari Kos
                </a>
                <a href="{{ route('home') }}#area-umy" @click="mobileMenu = false" class="flex items-center min-h-[44px] px-3.5 rounded-[var(--radius-control)] text-sm font-medium text-[var(--text-primary)] hover:bg-[var(--bg-tint)]">
                    Area Sekitar UMY
                </a>
                <a href="{{ route('mitra.info') }}" @click="mobileMenu = false" class="flex items-center min-h-[44px] px-3.5 rounded-[var(--radius-control)] text-sm font-medium text-[var(--text-primary)] hover:bg-[var(--bg-tint)]">
                    Gabung Mitra Kos
                </a>

                <div class="pt-3 mt-2 border-t border-[var(--border-subtle)] flex flex-col gap-2">
                    @auth
                        <a href="{{ route('dashboard') }}" class="lk-btn-primary w-full">Buka Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="lk-btn-secondary w-full">Masuk</a>
                        <a href="{{ route('register') }}" class="lk-btn-primary w-full">Daftar Akun Penyewa</a>
                    @endauth
                </div>
            </div>
        </header>

        <main id="konten-utama" class="flex-1">
            {{ $slot }}
        </main>

        <footer class="mt-20 mx-3 sm:mx-5 mb-4 rounded-[var(--radius-panel)] bg-[var(--bg-espresso)] text-[var(--espresso-ink)] overflow-hidden">
            <div class="max-w-[1260px] mx-auto px-6 sm:px-10 py-12 sm:py-16 grid grid-cols-1 md:grid-cols-3 gap-10 items-start">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[var(--espresso-gold)]">Kawasan Studi Kasus</p>
                    <p class="mt-3 text-sm text-[var(--espresso-muted)] leading-relaxed">
                        Sekitar Kampus Terpadu Universitas Muhammadiyah Yogyakarta, Jl. Brawijaya, Tamantirto, Kec. Kasihan, Kab. Bantul, DIY.
                    </p>
                </div>

                <div class="md:text-center">
                    <span class="font-display text-3xl font-semibold tracking-tight text-[var(--espresso-ink)]">LokaKos</span>
                    <p class="mt-3 text-sm text-[var(--espresso-muted)] max-w-xs md:mx-auto leading-relaxed">
                        Sistem informasi pemesanan kos berbasis web dengan multi-mitra pemilik kos terverifikasi.
                    </p>
                </div>

                <div class="md:text-right">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[var(--espresso-gold)]">Akses Cepat</p>
                    <ul class="mt-3 space-y-2 text-sm text-[var(--espresso-muted)]">
                        <li><a href="{{ route('kos.index') }}" class="hover:text-[var(--espresso-gold)] transition-colors">Katalog Kos UMY</a></li>
                        <li><a href="{{ route('mitra.info') }}" class="hover:text-[var(--espresso-gold)] transition-colors">Gabung Mitra Pemilik Kos</a></li>
                        @auth
                            <li><a href="{{ route('dashboard') }}" class="hover:text-[var(--espresso-gold)] transition-colors">Dashboard Akun</a></li>
                        @else
                            <li><a href="{{ route('login') }}" class="hover:text-[var(--espresso-gold)] transition-colors">Masuk</a></li>
                            <li><a href="{{ route('register.mitra') }}" class="hover:text-[var(--espresso-gold)] transition-colors">Daftar Mitra Kos</a></li>
                        @endauth
                    </ul>
                </div>
            </div>

            <div class="border-t border-white/15 px-6 sm:px-10 py-5 text-xs text-[var(--espresso-muted)] flex flex-col sm:flex-row items-center justify-between gap-2 max-w-[1260px] mx-auto">
                <span>LokaKos · Multi-Mitra Pemesanan Kos Kawasan UMY</span>
                <span>Metode Waterfall · Laravel 10</span>
            </div>
        </footer>

        @stack('scripts')
    </body>
</html>
