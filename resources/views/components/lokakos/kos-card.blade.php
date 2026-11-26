@props(['kos'])

@php
    $primaryImage = $kos->images->first();
    $demoPool = [
        'images/demo/room-boutique-ac.jpg',
        'images/demo/room-minimal-warm.jpg',
        'images/demo/room-studio-balcony.jpg',
    ];
    $demoPath = $demoPool[$kos->id % count($demoPool)];
    $hasDemoFile = file_exists(public_path($demoPath));

    $lowestPrice = $kos->min_monthly_price ?? $kos->roomTypes->min('price_monthly');
    $availableRooms = $kos->total_available_rooms ?? $kos->roomTypes->sum('available_rooms');
    $genderLabel = match ($kos->gender_type) {
        'putra' => 'Putra',
        'putri' => 'Putri',
        default => 'Campur',
    };
@endphp

<article class="group flex flex-col">
    <a href="{{ route('kos.show', $kos) }}" class="block">
        <div class="relative aspect-[16/10] rounded-[var(--radius-card)] bg-[var(--bg-tint)] overflow-hidden">
            @if ($primaryImage)
                <img
                    src="{{ asset('storage/' . $primaryImage->image_path) }}"
                    alt="Foto {{ $kos->name }}"
                    class="w-full h-full object-cover group-hover:scale-[1.03] transition-transform duration-300"
                    loading="lazy"
                >
            @elseif ($hasDemoFile)
                <img
                    src="{{ asset($demoPath) }}"
                    alt="Ilustrasi demo kamar untuk {{ $kos->name }}"
                    class="w-full h-full object-cover group-hover:scale-[1.03] transition-transform duration-300"
                    loading="lazy"
                >
                <span class="absolute bottom-3 right-3 px-2.5 py-1 rounded-[var(--radius-badge)] bg-black/70 text-[#F5F1EB] text-[11px] font-medium">
                    Ilustrasi demo
                </span>
            @else
                <div class="w-full h-full flex flex-col items-center justify-center px-5 text-center border border-[var(--border-subtle)] rounded-[var(--radius-card)]">
                    <span class="text-xs font-semibold text-[var(--text-secondary)]">[FOTO KOS - PLACEHOLDER]</span>
                </div>
            @endif

            <div class="absolute top-3.5 left-3.5 flex flex-wrap items-center gap-2">
                @if ($kos->is_syariah)
                    <span class="px-3 py-1 rounded-full bg-[var(--brand-secondary-bg)] text-[var(--brand-secondary-ink)] text-xs font-semibold">
                        Syariah
                    </span>
                @endif
                @if ($availableRooms === 0)
                    <span class="px-3 py-1 rounded-full bg-[var(--status-danger-bg)] text-[var(--status-danger)] text-xs font-semibold">
                        Penuh
                    </span>
                @endif
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center justify-between gap-2 text-sm text-[var(--text-secondary)]">
            <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
                <span class="inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 256 256" fill="none" stroke="currentColor" stroke-width="18" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="128" cy="96" r="44" />
                        <path d="M52 216a76 76 0 0 1 152 0" />
                    </svg>
                    Kos {{ $genderLabel }}
                </span>

                @if ($kos->distance_to_umy_km !== null)
                    <span class="inline-flex items-center gap-1.5 tabular-nums">
                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 256 256" fill="none" stroke="currentColor" stroke-width="18" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M128 24a80 80 0 0 0-80 80c0 72 80 128 80 128s80-56 80-128a80 80 0 0 0-80-80Z" />
                            <circle cx="128" cy="104" r="24" />
                        </svg>
                        {{ number_format($kos->distance_to_umy_km, 2, ',', '.') }} km UMY
                    </span>
                @endif

                @if ($availableRooms > 0)
                    <span class="tabular-nums font-medium text-[var(--brand-secondary)]">
                        {{ $availableRooms }} kamar tersedia
                    </span>
                @endif
            </div>

            <div class="text-right tabular-nums">
                @if ($lowestPrice)
                    <span class="text-base sm:text-lg font-bold text-[var(--text-primary)]">
                        Rp {{ number_format($lowestPrice, 0, ',', '.') }}
                    </span>
                    <span class="text-xs text-[var(--text-secondary)]">/bulan</span>
                @else
                    <span class="text-xs text-[var(--text-secondary)]">Belum ada tarif</span>
                @endif
            </div>
        </div>

        <h3 class="mt-2 text-xl sm:text-2xl font-semibold text-[var(--text-primary)] group-hover:text-[var(--brand-gold-text)] transition-colors line-clamp-1">
            {{ $kos->name }}
        </h3>

        <p class="mt-1 text-sm text-[var(--text-secondary)] line-clamp-1">
            {{ $kos->area?->name }} · {{ $kos->address }}
        </p>
    </a>
</article>
