<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-[var(--text-primary)]">Moderasi Listing Properti Kos</h1>
                <p class="mt-1 text-sm text-[var(--text-secondary)]">Hanya kos berstatus Tayang yang muncul pada halaman pencarian mahasiswa UMY.</p>
            </div>
            <a href="{{ route('dashboard') }}" class="lk-btn-secondary text-sm">Dashboard Admin</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="flex flex-wrap gap-2">
            @foreach (['' => 'Semua', 'pending_review' => 'Menunggu Moderasi', 'published' => 'Tayang', 'rejected' => 'Ditolak'] as $key => $label)
                <a
                    href="{{ route('admin.kos.index', $key ? ['status' => $key] : []) }}"
                    class="inline-flex items-center min-h-[40px] px-4 rounded-full text-xs font-semibold border {{ $statusFilter === $key ? 'bg-[var(--brand-gold)] text-[var(--brand-gold-ink)] border-[var(--brand-gold)]' : 'bg-[var(--bg-surface)] text-[var(--text-primary)] border-[var(--border-control)] hover:bg-[var(--bg-tint)]' }}"
                >
                    {{ $label }}
                </a>
            @endforeach
        </div>

        @if ($boardingHouses->isNotEmpty())
            <div class="space-y-4">
                @foreach ($boardingHouses as $kos)
                    <div class="lk-card p-6 space-y-4">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <div class="flex flex-wrap items-center gap-2.5">
                                    <h2 class="text-lg font-semibold text-[var(--text-primary)]">{{ $kos->name }}</h2>
                                    <x-lokakos.status-badge :status="$kos->status" />
                                    @if ($kos->is_field_verified)
                                        <span class="px-2.5 py-0.5 rounded-full bg-[var(--brand-secondary-bg)] text-[var(--brand-secondary-ink)] text-xs font-semibold">
                                            Terverifikasi Lapangan
                                        </span>
                                    @endif
                                </div>
                                <p class="mt-1 text-sm text-[var(--text-secondary)]">
                                    {{ $kos->area?->name }} · Kos {{ ucfirst($kos->gender_type) }} {{ $kos->is_syariah ? '· Syariah' : '' }} · Mitra: <strong class="text-[var(--text-primary)]">{{ $kos->mitra?->name }}</strong>
                                </p>
                                <p class="mt-1 text-xs text-[var(--text-secondary)]">{{ $kos->address }}</p>
                                <p class="mt-1 text-xs text-[var(--text-secondary)] tabular-nums">
                                    Tipe kamar: {{ $kos->roomTypes->count() }} · Total stok tersedia: {{ $kos->roomTypes->sum('available_rooms') }} kamar
                                    @if ($kos->distance_to_umy_km !== null)
                                        · Jarak ke UMY: {{ number_format($kos->distance_to_umy_km, 2, ',', '.') }} km
                                    @endif
                                </p>
                                @if ($kos->rejection_note)
                                    <p class="mt-2 text-xs text-[var(--status-danger)]">Catatan penolakan: {{ $kos->rejection_note }}</p>
                                @endif
                            </div>

                            @if ($kos->status === \App\Models\BoardingHouse::STATUS_PUBLISHED)
                                <a href="{{ route('kos.show', $kos) }}" target="_blank" class="lk-btn-secondary text-xs min-h-[38px] px-4">Lihat Halaman Publik</a>
                            @endif
                        </div>

                        <div class="pt-3 border-t border-[var(--border-subtle)] flex flex-col md:flex-row md:items-end justify-between gap-4">
                            <div class="flex flex-wrap gap-2">
                                @if ($kos->status !== \App\Models\BoardingHouse::STATUS_PUBLISHED)
                                    <form method="POST" action="{{ route('admin.kos.approve', $kos) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="lk-btn-primary text-xs min-h-[42px]">Setujui & Tayangkan</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.kos.unpublish', $kos) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="lk-btn-secondary text-xs min-h-[42px]">Tarik Sementara (Draf)</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.kos.verify-field', $kos) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="{{ $kos->is_field_verified ? 'lk-btn-espresso' : 'lk-btn-secondary' }} text-xs min-h-[42px]">
                                            {{ $kos->is_field_verified ? 'Cabut Verifikasi Lapangan' : 'Tandai Terverifikasi Lapangan' }}
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <form method="POST" action="{{ route('admin.kos.reject', $kos) }}" class="flex flex-col sm:flex-row gap-2 flex-1 md:max-w-lg">
                                @csrf
                                @method('PATCH')
                                <input type="text" name="rejection_note" required placeholder="Alasan penolakan atau perbaikan yang diminta..." class="lk-input text-xs">
                                <button type="submit" class="lk-btn-secondary text-xs text-[var(--status-danger)] border-[var(--status-danger)] shrink-0">
                                    Tolak Listing
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <div>{{ $boardingHouses->links() }}</div>
        @else
            <x-lokakos.empty-state
                title="Tidak ada listing kos pada filter ini"
                description="Semua listing properti kos pada kategori ini sudah selesai dimoderasi."
            />
        @endif
    </div>
</x-app-layout>
