<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-[var(--text-primary)]">Jadwal Survei Lokasi</h1>
                <p class="mt-1 text-sm text-[var(--text-secondary)]">Pantau persetujuan atau jadwal ulang kunjungan survei kamar kos dari pengelola.</p>
            </div>
            <a href="{{ route('kos.index') }}" class="lk-btn-primary text-sm">Cari Kos untuk Survei</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">
        @if ($surveys->isNotEmpty())
            <div class="space-y-4">
                @foreach ($surveys as $survey)
                    <article class="lk-card p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="space-y-1.5">
                            <div class="flex flex-wrap items-center gap-2">
                                <x-lokakos.status-badge :status="$survey->status" />
                                <span class="text-xs font-semibold text-[var(--text-secondary)] tabular-nums">
                                    {{ $survey->preferred_date->format('d M Y') }} · {{ $survey->preferred_time }}
                                </span>
                            </div>
                            <h2 class="text-lg font-semibold text-[var(--text-primary)]">
                                <a href="{{ route('kos.show', $survey->boardingHouse) }}" class="hover:text-[var(--brand-gold-text)]">
                                    {{ $survey->boardingHouse->name }}
                                </a>
                            </h2>
                            <p class="text-xs text-[var(--text-secondary)]">
                                {{ $survey->boardingHouse->area?->name }}
                                @if ($survey->roomType)
                                    · Minat kamar: {{ $survey->roomType->name }}
                                @endif
                            </p>
                            @if ($survey->notes)
                                <p class="text-xs text-[var(--text-secondary)]">Catatan Anda: {{ $survey->notes }}</p>
                            @endif
                            @if ($survey->mitra_response)
                                <p class="text-xs font-medium text-[var(--text-primary)] bg-[var(--bg-tint)] px-3 py-2 rounded-[var(--radius-control)] border border-[var(--border-subtle)]">
                                    Balasan Pengelola: {{ $survey->mitra_response }}
                                </p>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-2 shrink-0">
                            <a href="{{ route('kos.show', $survey->boardingHouse) }}" class="lk-btn-secondary text-xs min-h-[38px] px-4">Lihat Kos</a>
                            @if (in_array($survey->status, ['pending', 'approved', 'rescheduled'], true))
                                <form method="POST" action="{{ route('penyewa.surveys.cancel', $survey) }}" onsubmit="return confirm('Batalkan jadwal survei ini?');">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-xs font-semibold text-[var(--status-danger)] hover:underline px-2 py-2">Batalkan</button>
                                </form>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            <div>{{ $surveys->links() }}</div>
        @else
            <x-lokakos.empty-state
                title="Belum ada pengajuan jadwal survei"
                description="Buka halaman detail kos yang Anda minati lalu ajukan jadwal kunjungan survei sebelum memesan kamar."
                action-label="Jelajahi Kos UMY"
                :action-href="route('kos.index')"
            />
        @endif
    </div>
</x-app-layout>
