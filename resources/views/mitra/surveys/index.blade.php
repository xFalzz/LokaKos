<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-semibold text-[var(--text-primary)]">Permintaan Jadwal Survei Lokasi</h1>
            <p class="mt-1 text-sm text-[var(--text-secondary)]">Konfirmasi, jadwalkan ulang, atau tandai selesai kunjungan calon penghuni ke kos Anda.</p>
        </div>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
        @if ($surveys->isNotEmpty())
            @foreach ($surveys as $survey)
                <article class="lk-card p-6 space-y-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <x-lokakos.status-badge :status="$survey->status" />
                                <span class="text-xs font-semibold text-[var(--text-primary)] tabular-nums">
                                    {{ $survey->preferred_date->format('d M Y') }} · {{ $survey->preferred_time }}
                                </span>
                            </div>
                            <h2 class="mt-2 text-lg font-semibold text-[var(--text-primary)]">{{ $survey->boardingHouse->name }}</h2>
                            <p class="text-xs text-[var(--text-secondary)]">
                                Pemohon: <strong class="text-[var(--text-primary)]">{{ $survey->user?->name }}</strong>
                                ({{ $survey->user?->phone ?: $survey->user?->email }})
                                @if ($survey->roomType)
                                    · Minat: {{ $survey->roomType->name }}
                                @endif
                            </p>
                            @if ($survey->notes)
                                <p class="mt-1 text-xs text-[var(--text-secondary)]">Catatan pemohon: {{ $survey->notes }}</p>
                            @endif
                        </div>
                    </div>

                    @if ($survey->status !== 'cancelled')
                        <form method="POST" action="{{ route('mitra.surveys.update', $survey) }}" class="pt-4 border-t border-[var(--border-subtle)] grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                            @csrf
                            @method('PATCH')
                            <div class="md:col-span-3">
                                <label class="block text-xs font-semibold text-[var(--text-primary)] mb-1">Tindakan Status</label>
                                <select name="status" class="lk-input text-xs min-h-[40px]">
                                    <option value="approved" @selected($survey->status === 'approved')>Setujui Jadwal</option>
                                    <option value="rescheduled" @selected($survey->status === 'rescheduled')>Usulkan Jadwal Ulang</option>
                                    <option value="completed" @selected($survey->status === 'completed')>Selesai Survei</option>
                                    <option value="rejected" @selected($survey->status === 'rejected')>Tolak Pengajuan</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold text-[var(--text-primary)] mb-1">Tanggal Baru</label>
                                <input type="date" name="preferred_date" value="{{ $survey->preferred_date->toDateString() }}" class="lk-input text-xs min-h-[40px]">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold text-[var(--text-primary)] mb-1">Jam Baru</label>
                                <input type="text" name="preferred_time" value="{{ $survey->preferred_time }}" class="lk-input text-xs min-h-[40px]">
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-xs font-semibold text-[var(--text-primary)] mb-1">Catatan Pengelola</label>
                                <input type="text" name="mitra_response" value="{{ $survey->mitra_response }}" placeholder="Misal: Temui penjaga di gerbang depan" class="lk-input text-xs min-h-[40px]">
                            </div>
                            <div class="md:col-span-2">
                                <button type="submit" class="lk-btn-primary w-full text-xs min-h-[40px]">Simpan</button>
                            </div>
                        </form>
                    @endif
                </article>
            @endforeach
            <div>{{ $surveys->links() }}</div>
        @else
            <x-lokakos.empty-state
                title="Belum ada permintaan survei"
                description="Permintaan kunjungan survei lokasi dari mahasiswa untuk kos milik Anda akan muncul di sini."
            />
        @endif
    </div>
</x-app-layout>
