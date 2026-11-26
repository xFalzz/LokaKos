<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-[var(--text-primary)]">Properti Kos Saya</h1>
                <p class="mt-1 text-sm text-[var(--text-secondary)]">Kelola properti kos, foto, pin lokasi peta UMY, dan stok tipe kamar Anda.</p>
            </div>
            @if ($user->mitraProfile?->isApproved())
                <a href="{{ route('mitra.kos.create') }}" class="lk-btn-primary text-sm">Tambah Kos Baru</a>
            @else
                <a href="{{ route('mitra.profile.edit') }}" class="lk-btn-secondary text-sm">Lengkapi Profil Mitra</a>
            @endif
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        @if (! $user->mitraProfile?->isApproved())
            <div class="lk-card p-5 bg-[var(--accent-warm-bg)] text-[var(--accent-warm-ink)] text-sm">
                <p class="font-semibold">Akun Mitra Anda berstatus: {{ ucfirst($user->mitraProfile?->verification_status ?? 'pending') }}</p>
                <p class="mt-1">Anda dapat menambahkan properti kos setelah Admin menyetujui pendaftaran Mitra Anda.</p>
                @if ($user->mitraProfile?->rejection_note)
                    <p class="mt-1 font-medium text-[var(--status-danger)]">Catatan Admin: {{ $user->mitraProfile->rejection_note }}</p>
                @endif
            </div>
        @endif

        @if ($boardingHouses->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach ($boardingHouses as $kos)
                    <article class="lk-card p-6 flex flex-col justify-between gap-4">
                        <div>
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <x-lokakos.status-badge :status="$kos->status" />
                                @if ($kos->distance_to_umy_km !== null)
                                    <span class="text-xs font-semibold text-[var(--text-secondary)] tabular-nums">
                                        {{ number_format($kos->distance_to_umy_km, 2, ',', '.') }} km dari UMY
                                    </span>
                                @endif
                            </div>

                            <h2 class="mt-3 text-xl font-semibold text-[var(--text-primary)]">{{ $kos->name }}</h2>
                            <p class="mt-1 text-sm text-[var(--text-secondary)]">
                                {{ $kos->area?->name }} · Kos {{ ucfirst($kos->gender_type) }} {{ $kos->is_syariah ? '· Syariah' : '' }}
                            </p>
                            <p class="mt-1 text-xs text-[var(--text-secondary)]">{{ $kos->address }}</p>
                            @if ($kos->resolvedDisplayWhatsapp())
                                <p class="mt-1.5 text-xs text-[var(--text-secondary)] tabular-nums">
                                    Kontak WA: <strong class="text-[var(--text-primary)]">{{ $kos->resolvedContactName() }}</strong> ({{ $kos->resolvedDisplayWhatsapp() }})
                                    @if ($kos->contact_hours)
                                        · {{ $kos->contact_hours }}
                                    @endif
                                </p>
                            @endif

                            @php $score = $kos->completenessScore(); @endphp
                            <div class="mt-4 pt-3 border-t border-[var(--border-subtle)] flex flex-wrap gap-4 text-xs text-[var(--text-secondary)] tabular-nums">
                                <span>Tipe kamar: <strong class="text-[var(--text-primary)]">{{ $kos->roomTypes->count() }}</strong></span>
                                <span>Sisa stok: <strong class="text-[var(--text-primary)]">{{ $kos->roomTypes->sum('available_rooms') }} / {{ $kos->roomTypes->sum('total_rooms') }}</strong></span>
                                <span>Kelengkapan: <strong class="text-[var(--brand-gold-text)]">{{ $score['percent'] }}%</strong></span>
                            </div>

                            @if ($kos->rejection_note)
                                <p class="mt-3 p-3 rounded-[var(--radius-control)] bg-[var(--status-danger-bg)] text-[var(--status-danger)] text-xs">
                                    Catatan Moderasi Admin: {{ $kos->rejection_note }}
                                </p>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-2 pt-3 border-t border-[var(--border-subtle)]">
                            <a href="{{ route('mitra.rooms.index', $kos) }}" class="lk-btn-primary text-xs min-h-[40px] px-4">Kelola Kamar</a>
                            <a href="{{ route('mitra.kos.edit', $kos) }}" class="lk-btn-secondary text-xs min-h-[40px] px-4">Edit Kos</a>
                            @if ($kos->status === \App\Models\BoardingHouse::STATUS_PUBLISHED)
                                <a href="{{ route('kos.show', $kos) }}" target="_blank" class="lk-btn-secondary text-xs min-h-[40px] px-3.5">Pratinjau</a>
                                <a href="{{ route('mitra.kos.poster-qr', $kos) }}" class="lk-btn-secondary text-xs min-h-[40px] px-3.5">Poster QR PDF</a>
                            @endif
                            <form method="POST" action="{{ route('mitra.kos.destroy', $kos) }}" class="ml-auto" onsubmit="return confirm('Hapus properti kos ini beserta seluruh datanya?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-semibold text-[var(--status-danger)] hover:underline px-2 py-2">Hapus</button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <x-lokakos.empty-state
                title="Belum ada properti kos terdaftar"
                description="Tambahkan data kos milik Anda, tentukan lokasi pada peta sekitar UMY, lalu tambahkan tipe kamar beserta stoknya."
                :action-label="$user->mitraProfile?->isApproved() ? 'Tambah Kos Pertama' : null"
                :action-href="$user->mitraProfile?->isApproved() ? route('mitra.kos.create') : null"
            />
        @endif
    </div>
</x-app-layout>
