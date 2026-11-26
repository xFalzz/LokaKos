<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'LokaKos') }} | Panel</title>

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
    <body
        x-data="{ sidebarOpen: false }"
        @keydown.escape.window="sidebarOpen = false"
        class="font-sans antialiased min-h-screen bg-[var(--bg-canvas)] text-[var(--text-primary)]"
    >
        <div class="min-h-screen flex">
            @include('layouts.navigation')

            <div class="flex-1 flex flex-col min-w-0 lg:pl-64">
                <header class="sticky top-0 z-30 h-16 bg-[var(--bg-surface)] border-b border-[var(--border-subtle)] px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3 min-w-0">
                        <button
                            type="button"
                            @click="sidebarOpen = true"
                            class="lg:hidden inline-flex items-center justify-center min-h-[44px] px-3.5 rounded-full border border-[var(--border-control)] bg-[var(--bg-surface)] text-sm font-medium text-[var(--text-primary)]"
                        >
                            Menu
                        </button>
                        <a href="{{ route('home') }}" class="lg:hidden font-display text-lg font-semibold text-[var(--text-primary)]">
                            LokaKos
                        </a>
                        <span class="hidden lg:inline text-xs font-semibold uppercase tracking-[0.12em] text-[var(--text-secondary)]">
                            @if (Auth::user()->isAdmin())
                                Back-Office Administrator
                            @elseif (Auth::user()->isMitra())
                                Workspace Mitra Pemilik Kos
                            @else
                                Akun Penyewa LokaKos
                            @endif
                        </span>
                    </div>

                    <div class="flex items-center gap-2.5">
                        @php $unreadNotif = Auth::user()->unreadNotificationsCount(); @endphp
                        <a
                            href="{{ route('notifications.index') }}"
                            aria-label="Notifikasi"
                            class="relative inline-flex items-center gap-1.5 min-h-[40px] px-3.5 rounded-full border border-[var(--border-control)] bg-[var(--bg-surface)] text-xs font-medium text-[var(--text-primary)] hover:bg-[var(--bg-tint)] transition-colors"
                        >
                            <span>Notifikasi</span>
                            @if ($unreadNotif > 0)
                                <span class="px-1.5 py-0.5 rounded-full bg-[var(--brand-gold)] text-[var(--brand-gold-ink)] text-[11px] font-bold tabular-nums leading-none">
                                    {{ $unreadNotif }}
                                </span>
                            @endif
                        </a>
                        <a href="{{ route('home') }}" class="hidden sm:inline-flex items-center min-h-[40px] px-3.5 rounded-full text-xs font-medium text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:bg-[var(--bg-tint)] transition-colors">
                            Situs Publik
                        </a>
                        <x-lokakos.theme-toggle />
                    </div>
                </header>

                @if (isset($header))
                    <div class="bg-[var(--bg-tint)] border-b border-[var(--border-subtle)]">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </div>
                @endif

                <main class="flex-1">
                    @if (session('status') || session('error'))
                        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
                            @if (session('status'))
                                <div class="p-4 rounded-[var(--radius-control)] bg-[var(--brand-secondary-bg)] text-[var(--brand-secondary-ink)] text-sm font-medium border border-[var(--border-subtle)]">
                                    {{ session('status') }}
                                </div>
                            @endif
                            @if (session('error'))
                                <div class="p-4 rounded-[var(--radius-control)] bg-[var(--status-danger-bg)] text-[var(--status-danger)] text-sm font-medium border border-[var(--border-subtle)]">
                                    {{ session('error') }}
                                </div>
                            @endif
                        </div>
                    @endif

                    {{ $slot }}
                </main>
            </div>
        </div>

        @stack('scripts')
    </body>
</html>
