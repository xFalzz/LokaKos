<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-[var(--text-primary)]">Verifikasi Akun Mitra Pemilik Kos</h1>
                <p class="mt-1 text-sm text-[var(--text-secondary)]">Tinjau data pemilik kos, rekening penerima, dan berkas identitas sebelum menyetujui akses penayangan kos.</p>
            </div>
            <a href="{{ route('dashboard') }}" class="lk-btn-secondary text-sm">Dashboard Admin</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="flex flex-wrap gap-2">
            @foreach (['' => 'Semua', 'pending' => 'Menunggu Verifikasi', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'] as $key => $label)
                <a
                    href="{{ route('admin.mitra.index', $key ? ['status' => $key] : []) }}"
                    class="inline-flex items-center min-h-[40px] px-4 rounded-full text-xs font-semibold border {{ $statusFilter === $key ? 'bg-[var(--brand-gold)] text-[var(--brand-gold-ink)] border-[var(--brand-gold)]' : 'bg-[var(--bg-surface)] text-[var(--text-primary)] border-[var(--border-control)] hover:bg-[var(--bg-tint)]' }}"
                >
                    {{ $label }}
                </a>
            @endforeach
        </div>

        @if ($profiles->isNotEmpty())
            <div class="space-y-4">
                @foreach ($profiles as $profile)
                    <div class="lk-card p-6 space-y-4">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <div class="flex flex-wrap items-center gap-2.5">
                                    <h2 class="text-lg font-semibold text-[var(--text-primary)]">{{ $profile->business_name }}</h2>
                                    <x-lokakos.status-badge :status="$profile->verification_status" />
                                </div>
                                <p class="mt-1 text-sm text-[var(--text-secondary)]">
                                    Pengelola: <strong class="text-[var(--text-primary)]">{{ $profile->user?->name }}</strong> · {{ $profile->user?->email }} · {{ $profile->user?->phone ?? '-' }}
                                </p>
                                <p class="mt-1 text-xs text-[var(--text-secondary)] tabular-nums">
                                    Rekening: {{ $profile->bank_name }} {{ $profile->bank_account_number }} (a.n. {{ $profile->bank_account_name }})
                                </p>
                                @if ($profile->rejection_note)
                                    <p class="mt-2 text-xs text-[var(--status-danger)]">Catatan penolakan: {{ $profile->rejection_note }}</p>
                                @endif
                            </div>

                            @if ($profile->ktp_path)
                                <a href="{{ asset('storage/' . $profile->ktp_path) }}" target="_blank" class="lk-btn-secondary text-xs min-h-[38px] px-4">
                                    Lihat Berkas KTP
                                </a>
                            @endif
                        </div>

                        <div class="pt-3 border-t border-[var(--border-subtle)] flex flex-col md:flex-row md:items-end justify-between gap-4">
                            @if ($profile->verification_status !== \App\Models\MitraProfile::STATUS_APPROVED)
                                <form method="POST" action="{{ route('admin.mitra.approve', $profile) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="lk-btn-primary text-xs min-h-[42px]">Setujui Akun Mitra</button>
                                </form>
                            @endif

                            <form method="POST" action="{{ route('admin.mitra.reject', $profile) }}" class="flex flex-col sm:flex-row gap-2 flex-1 md:max-w-lg">
                                @csrf
                                @method('PATCH')
                                <input type="text" name="rejection_note" required placeholder="Alasan penolakan / revisi data..." class="lk-input text-xs">
                                <button type="submit" class="lk-btn-secondary text-xs text-[var(--status-danger)] border-[var(--status-danger)] shrink-0">
                                    Tolak Mitra
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <div>{{ $profiles->links() }}</div>
        @else
            <x-lokakos.empty-state
                title="Tidak ada data Mitra pada filter ini"
                description="Semua pendaftaran Mitra sesuai filter sudah selesai ditinjau."
            />
        @endif
    </div>
</x-app-layout>
