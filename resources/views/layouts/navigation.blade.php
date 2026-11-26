@php
    $user = Auth::user();
    $adminPendingMitra = $user->isAdmin()
        ? \App\Models\MitraProfile::where('verification_status', \App\Models\MitraProfile::STATUS_PENDING)->count()
        : 0;
    $adminPendingKos = $user->isAdmin()
        ? \App\Models\BoardingHouse::where('status', \App\Models\BoardingHouse::STATUS_PENDING_REVIEW)->count()
        : 0;
    $adminOpenReports = $user->isAdmin()
        ? \App\Models\ListingReport::where('status', \App\Models\ListingReport::STATUS_OPEN)->count()
        : 0;

    $mitraHasKos = $user->isMitra()
        ? \App\Models\BoardingHouse::where('mitra_id', $user->id)->exists()
        : false;
    $mitraWaitingPay = ($user->isMitra() && $mitraHasKos)
        ? \App\Models\Booking::whereHas('roomType.boardingHouse', fn ($q) => $q->where('mitra_id', $user->id))
            ->where('status', \App\Models\Booking::STATUS_WAITING_VERIFICATION)
            ->count()
        : 0;
    $mitraPendingSurveys = ($user->isMitra() && $mitraHasKos)
        ? \App\Models\SurveyBooking::whereHas('boardingHouse', fn ($q) => $q->where('mitra_id', $user->id))
            ->where('status', \App\Models\SurveyBooking::STATUS_PENDING)
            ->count()
        : 0;
    $mitraOpenTickets = ($user->isMitra() && $mitraHasKos)
        ? \App\Models\MaintenanceTicket::whereHas('boardingHouse', fn ($q) => $q->where('mitra_id', $user->id))
            ->whereIn('status', [\App\Models\MaintenanceTicket::STATUS_OPEN, \App\Models\MaintenanceTicket::STATUS_IN_PROGRESS])
            ->count()
        : 0;
@endphp

<div
    x-cloak
    x-show="sidebarOpen"
    x-transition.opacity.duration.180ms
    class="fixed inset-0 z-40 bg-black/55 lg:hidden"
    @click="sidebarOpen = false"
></div>

<aside
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    class="fixed inset-y-0 left-0 z-50 w-64 bg-[var(--bg-espresso)] text-[var(--espresso-ink)] flex flex-col border-r border-white/10 transition-transform duration-200 ease-out"
>
    <div class="h-16 px-5 flex items-center justify-between border-b border-white/10">
        <a href="{{ route('home') }}" class="inline-flex items-baseline gap-2">
            <span class="font-display text-xl font-semibold tracking-tight text-[var(--espresso-ink)]">LokaKos</span>
            <span class="text-[11px] font-medium text-[var(--espresso-gold)]">UMY</span>
        </a>
        <button
            type="button"
            @click="sidebarOpen = false"
            class="lg:hidden inline-flex items-center justify-center min-h-[40px] px-3 rounded-full border border-white/25 text-xs text-[var(--espresso-ink)]"
        >
            Tutup
        </button>
    </div>

    <nav aria-label="Navigasi panel" class="flex-1 overflow-y-auto px-3 py-5 space-y-1 text-sm">
        <a
            href="{{ route('dashboard') }}"
            class="flex items-center justify-between min-h-[42px] px-3.5 rounded-[var(--radius-control)] font-medium transition-colors {{ request()->routeIs('dashboard') ? 'bg-[var(--brand-gold)] text-[var(--brand-gold-ink)] font-semibold' : 'text-[var(--espresso-muted)] hover:bg-white/10 hover:text-[var(--espresso-ink)]' }}"
        >
            <span>Ringkasan Dashboard</span>
        </a>

        @if ($user->isAdmin())
            <p class="px-3.5 pt-4 pb-1 text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--espresso-gold)]">
                Moderasi & Laporan
            </p>
            <a
                href="{{ route('admin.mitra.index') }}"
                class="flex items-center justify-between min-h-[42px] px-3.5 rounded-[var(--radius-control)] font-medium transition-colors {{ request()->routeIs('admin.mitra.*') ? 'bg-[var(--brand-gold)] text-[var(--brand-gold-ink)] font-semibold' : 'text-[var(--espresso-muted)] hover:bg-white/10 hover:text-[var(--espresso-ink)]' }}"
            >
                <span>Verifikasi Mitra</span>
                @if ($adminPendingMitra > 0)
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold tabular-nums {{ request()->routeIs('admin.mitra.*') ? 'bg-black/15 text-[var(--brand-gold-ink)]' : 'bg-[var(--brand-gold)] text-[var(--brand-gold-ink)]' }}">
                        {{ $adminPendingMitra }}
                    </span>
                @endif
            </a>
            <a
                href="{{ route('admin.kos.index') }}"
                class="flex items-center justify-between min-h-[42px] px-3.5 rounded-[var(--radius-control)] font-medium transition-colors {{ request()->routeIs('admin.kos.*') ? 'bg-[var(--brand-gold)] text-[var(--brand-gold-ink)] font-semibold' : 'text-[var(--espresso-muted)] hover:bg-white/10 hover:text-[var(--espresso-ink)]' }}"
            >
                <span>Moderasi Listing Kos</span>
                @if ($adminPendingKos > 0)
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold tabular-nums {{ request()->routeIs('admin.kos.*') ? 'bg-black/15 text-[var(--brand-gold-ink)]' : 'bg-[var(--brand-gold)] text-[var(--brand-gold-ink)]' }}">
                        {{ $adminPendingKos }}
                    </span>
                @endif
            </a>
            <a
                href="{{ route('admin.reports.index') }}"
                class="flex items-center justify-between min-h-[42px] px-3.5 rounded-[var(--radius-control)] font-medium transition-colors {{ request()->routeIs('admin.reports.*') ? 'bg-[var(--brand-gold)] text-[var(--brand-gold-ink)] font-semibold' : 'text-[var(--espresso-muted)] hover:bg-white/10 hover:text-[var(--espresso-ink)]' }}"
            >
                <span>Laporan Listing</span>
                @if ($adminOpenReports > 0)
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold tabular-nums {{ request()->routeIs('admin.reports.*') ? 'bg-black/15 text-[var(--brand-gold-ink)]' : 'bg-[var(--brand-gold)] text-[var(--brand-gold-ink)]' }}">
                        {{ $adminOpenReports }}
                    </span>
                @endif
            </a>

            <p class="px-3.5 pt-4 pb-1 text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--espresso-gold)]">
                Back-Office & Tata Kelola
            </p>
            <a
                href="{{ route('admin.master.index') }}"
                class="flex items-center justify-between min-h-[42px] px-3.5 rounded-[var(--radius-control)] font-medium transition-colors {{ request()->routeIs('admin.master.*') ? 'bg-[var(--brand-gold)] text-[var(--brand-gold-ink)] font-semibold' : 'text-[var(--espresso-muted)] hover:bg-white/10 hover:text-[var(--espresso-ink)]' }}"
            >
                <span>Master Area & Fasilitas</span>
            </a>
            <a
                href="{{ route('admin.monitoring.index') }}"
                class="flex items-center justify-between min-h-[42px] px-3.5 rounded-[var(--radius-control)] font-medium transition-colors {{ request()->routeIs('admin.monitoring.*') ? 'bg-[var(--brand-gold)] text-[var(--brand-gold-ink)] font-semibold' : 'text-[var(--espresso-muted)] hover:bg-white/10 hover:text-[var(--espresso-ink)]' }}"
            >
                <span>Transaksi, Akun, Ulasan</span>
            </a>
            <a
                href="{{ route('admin.audit.index') }}"
                class="flex items-center justify-between min-h-[42px] px-3.5 rounded-[var(--radius-control)] font-medium transition-colors {{ request()->routeIs('admin.audit.*') ? 'bg-[var(--brand-gold)] text-[var(--brand-gold-ink)] font-semibold' : 'text-[var(--espresso-muted)] hover:bg-white/10 hover:text-[var(--espresso-ink)]' }}"
            >
                <span>Audit Log Aktivitas</span>
            </a>
            <a
                href="{{ route('admin.report.pdf') }}"
                class="flex items-center justify-between min-h-[42px] px-3.5 rounded-[var(--radius-control)] font-medium text-[var(--espresso-muted)] hover:bg-white/10 hover:text-[var(--espresso-ink)] transition-colors"
            >
                <span>Unduh Laporan PDF</span>
            </a>
            <a
                href="{{ route('admin.report.csv') }}"
                class="flex items-center justify-between min-h-[42px] px-3.5 rounded-[var(--radius-control)] font-medium text-[var(--espresso-muted)] hover:bg-white/10 hover:text-[var(--espresso-ink)] transition-colors"
            >
                <span>Ekspor CSV (Excel)</span>
            </a>
        @elseif ($user->isMitra())
            <p class="px-3.5 pt-4 pb-1 text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--espresso-gold)]">
                Kelola Properti
            </p>
            <a
                href="{{ route('mitra.kos.index') }}"
                class="flex items-center justify-between min-h-[42px] px-3.5 rounded-[var(--radius-control)] font-medium transition-colors {{ request()->routeIs('mitra.kos.*') || request()->routeIs('mitra.rooms.*') ? 'bg-[var(--brand-gold)] text-[var(--brand-gold-ink)] font-semibold' : 'text-[var(--espresso-muted)] hover:bg-white/10 hover:text-[var(--espresso-ink)]' }}"
            >
                <span>{{ $mitraHasKos ? 'Kos & Kamar Saya' : 'Tambah Kos Pertama' }}</span>
            </a>
            @if ($mitraHasKos)
                <a
                    href="{{ route('mitra.bookings.index') }}"
                    class="flex items-center justify-between min-h-[42px] px-3.5 rounded-[var(--radius-control)] font-medium transition-colors {{ request()->routeIs('mitra.bookings.*') ? 'bg-[var(--brand-gold)] text-[var(--brand-gold-ink)] font-semibold' : 'text-[var(--espresso-muted)] hover:bg-white/10 hover:text-[var(--espresso-ink)]' }}"
                >
                    <span>Pesanan & Verifikasi</span>
                    @if ($mitraWaitingPay > 0)
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold tabular-nums {{ request()->routeIs('mitra.bookings.*') ? 'bg-black/15 text-[var(--brand-gold-ink)]' : 'bg-[var(--brand-gold)] text-[var(--brand-gold-ink)]' }}">
                            {{ $mitraWaitingPay }}
                        </span>
                    @endif
                </a>
                <a
                    href="{{ route('mitra.surveys.index') }}"
                    class="flex items-center justify-between min-h-[42px] px-3.5 rounded-[var(--radius-control)] font-medium transition-colors {{ request()->routeIs('mitra.surveys.*') ? 'bg-[var(--brand-gold)] text-[var(--brand-gold-ink)] font-semibold' : 'text-[var(--espresso-muted)] hover:bg-white/10 hover:text-[var(--espresso-ink)]' }}"
                >
                    <span>Jadwal Survei</span>
                    @if ($mitraPendingSurveys > 0)
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold tabular-nums {{ request()->routeIs('mitra.surveys.*') ? 'bg-black/15 text-[var(--brand-gold-ink)]' : 'bg-[var(--brand-gold)] text-[var(--brand-gold-ink)]' }}">
                            {{ $mitraPendingSurveys }}
                        </span>
                    @endif
                </a>
                <a
                    href="{{ route('mitra.tickets.index') }}"
                    class="flex items-center justify-between min-h-[42px] px-3.5 rounded-[var(--radius-control)] font-medium transition-colors {{ request()->routeIs('mitra.tickets.*') ? 'bg-[var(--brand-gold)] text-[var(--brand-gold-ink)] font-semibold' : 'text-[var(--espresso-muted)] hover:bg-white/10 hover:text-[var(--espresso-ink)]' }}"
                >
                    <span>Keluhan Penghuni</span>
                    @if ($mitraOpenTickets > 0)
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold tabular-nums {{ request()->routeIs('mitra.tickets.*') ? 'bg-black/15 text-[var(--brand-gold-ink)]' : 'bg-[var(--brand-gold)] text-[var(--brand-gold-ink)]' }}">
                            {{ $mitraOpenTickets }}
                        </span>
                    @endif
                </a>

                <p class="px-3.5 pt-4 pb-1 text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--espresso-gold)]">
                    Operasional & Keuangan
                </p>
                <a
                    href="{{ route('mitra.promos.index') }}"
                    class="flex items-center justify-between min-h-[42px] px-3.5 rounded-[var(--radius-control)] font-medium transition-colors {{ request()->routeIs('mitra.promos.*') ? 'bg-[var(--brand-gold)] text-[var(--brand-gold-ink)] font-semibold' : 'text-[var(--espresso-muted)] hover:bg-white/10 hover:text-[var(--espresso-ink)]' }}"
                >
                    <span>Promo & Voucher</span>
                </a>
                <a
                    href="{{ route('mitra.announcements.index') }}"
                    class="flex items-center justify-between min-h-[42px] px-3.5 rounded-[var(--radius-control)] font-medium transition-colors {{ request()->routeIs('mitra.announcements.*') ? 'bg-[var(--brand-gold)] text-[var(--brand-gold-ink)] font-semibold' : 'text-[var(--espresso-muted)] hover:bg-white/10 hover:text-[var(--espresso-ink)]' }}"
                >
                    <span>Pengumuman & Q&A</span>
                </a>
                <a
                    href="{{ route('mitra.expenses.index') }}"
                    class="flex items-center justify-between min-h-[42px] px-3.5 rounded-[var(--radius-control)] font-medium transition-colors {{ request()->routeIs('mitra.expenses.*') ? 'bg-[var(--brand-gold)] text-[var(--brand-gold-ink)] font-semibold' : 'text-[var(--espresso-muted)] hover:bg-white/10 hover:text-[var(--espresso-ink)]' }}"
                >
                    <span>Buku Kas & Laporan</span>
                </a>
            @endif
            <p class="px-3.5 pt-4 pb-1 text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--espresso-gold)]">
                Usaha & Rekening
            </p>
            <a
                href="{{ route('mitra.profile.edit') }}"
                class="flex items-center justify-between min-h-[42px] px-3.5 rounded-[var(--radius-control)] font-medium transition-colors {{ request()->routeIs('mitra.profile.*') ? 'bg-[var(--brand-gold)] text-[var(--brand-gold-ink)] font-semibold' : 'text-[var(--espresso-muted)] hover:bg-white/10 hover:text-[var(--espresso-ink)]' }}"
            >
                <span>Profil & Rekening Bank</span>
            </a>
        @else
            <p class="px-3.5 pt-4 pb-1 text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--espresso-gold)]">
                Aktivitas Sewa
            </p>
            <a
                href="{{ route('penyewa.bookings.index') }}"
                class="flex items-center justify-between min-h-[42px] px-3.5 rounded-[var(--radius-control)] font-medium transition-colors {{ request()->routeIs('penyewa.bookings.*') ? 'bg-[var(--brand-gold)] text-[var(--brand-gold-ink)] font-semibold' : 'text-[var(--espresso-muted)] hover:bg-white/10 hover:text-[var(--espresso-ink)]' }}"
            >
                <span>Pesanan Saya</span>
            </a>
            <a
                href="{{ route('penyewa.surveys.index') }}"
                class="flex items-center justify-between min-h-[42px] px-3.5 rounded-[var(--radius-control)] font-medium transition-colors {{ request()->routeIs('penyewa.surveys.*') ? 'bg-[var(--brand-gold)] text-[var(--brand-gold-ink)] font-semibold' : 'text-[var(--espresso-muted)] hover:bg-white/10 hover:text-[var(--espresso-ink)]' }}"
            >
                <span>Jadwal Survei</span>
            </a>
            <a
                href="{{ route('penyewa.maintenance.index') }}"
                class="flex items-center justify-between min-h-[42px] px-3.5 rounded-[var(--radius-control)] font-medium transition-colors {{ request()->routeIs('penyewa.maintenance.*') ? 'bg-[var(--brand-gold)] text-[var(--brand-gold-ink)] font-semibold' : 'text-[var(--espresso-muted)] hover:bg-white/10 hover:text-[var(--espresso-ink)]' }}"
            >
                <span>Keluhan Kamar</span>
            </a>
            <a
                href="{{ route('penyewa.saved.index') }}"
                class="flex items-center justify-between min-h-[42px] px-3.5 rounded-[var(--radius-control)] font-medium transition-colors {{ request()->routeIs('penyewa.saved.*') ? 'bg-[var(--brand-gold)] text-[var(--brand-gold-ink)] font-semibold' : 'text-[var(--espresso-muted)] hover:bg-white/10 hover:text-[var(--espresso-ink)]' }}"
            >
                <span>Kos Tersimpan</span>
            </a>
            <a
                href="{{ route('kos.compare') }}"
                class="flex items-center justify-between min-h-[42px] px-3.5 rounded-[var(--radius-control)] font-medium text-[var(--espresso-muted)] hover:bg-white/10 hover:text-[var(--espresso-ink)] transition-colors"
            >
                <span>Bandingkan Kos</span>
            </a>
            <a
                href="{{ route('kos.index') }}"
                class="flex items-center justify-between min-h-[42px] px-3.5 rounded-[var(--radius-control)] font-medium text-[var(--espresso-muted)] hover:bg-white/10 hover:text-[var(--espresso-ink)] transition-colors"
            >
                <span>Cari Kos UMY</span>
            </a>
        @endif

        <p class="px-3.5 pt-4 pb-1 text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--espresso-gold)]">
            Akun
        </p>
        <a
            href="{{ route('profile.edit') }}"
            class="flex items-center justify-between min-h-[42px] px-3.5 rounded-[var(--radius-control)] font-medium transition-colors {{ request()->routeIs('profile.*') ? 'bg-[var(--brand-gold)] text-[var(--brand-gold-ink)] font-semibold' : 'text-[var(--espresso-muted)] hover:bg-white/10 hover:text-[var(--espresso-ink)]' }}"
        >
            <span>Pengaturan Akun</span>
        </a>
    </nav>

    <div class="p-4 border-t border-white/10 space-y-3">
        <div class="px-1">
            <p class="text-sm font-semibold text-[var(--espresso-ink)] truncate">{{ $user->name }}</p>
            <p class="text-xs text-[var(--espresso-muted)] truncate">{{ $user->email }}</p>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full min-h-[42px] rounded-full border border-white/30 text-xs font-semibold text-[var(--espresso-ink)] hover:bg-white/10 transition-colors">
                Keluar
            </button>
        </form>
    </div>
</aside>
