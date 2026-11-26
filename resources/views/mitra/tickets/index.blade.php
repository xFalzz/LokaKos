<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-semibold text-[var(--text-primary)]">Keluhan & Laporan Perbaikan Penghuni</h1>
            <p class="mt-1 text-sm text-[var(--text-secondary)]">Tindak lanjuti kendala fasilitas dari penghuni aktif properti kos Anda.</p>
        </div>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
        @if ($tickets->isNotEmpty())
            @foreach ($tickets as $ticket)
                <article class="lk-card p-6 space-y-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <x-lokakos.status-badge :status="$ticket->status" />
                                <span class="text-xs font-semibold uppercase tracking-wide text-[var(--brand-gold-text)]">{{ str_replace('_', ' ', $ticket->category) }}</span>
                                <span class="text-xs text-[var(--text-secondary)] tabular-nums">{{ $ticket->created_at->format('d M Y H:i') }} WIB</span>
                            </div>
                            <h2 class="text-lg font-semibold text-[var(--text-primary)]">{{ $ticket->title }}</h2>
                            <p class="text-xs text-[var(--text-secondary)]">
                                {{ $ticket->boardingHouse->name }} ({{ $ticket->booking?->roomType?->name }}) · Pelapor: <strong class="text-[var(--text-primary)]">{{ $ticket->user?->name }}</strong>
                            </p>
                            <p class="text-sm text-[var(--text-secondary)] leading-relaxed pt-1">{{ $ticket->description }}</p>
                            @if ($ticket->photo_path)
                                <a href="{{ asset('storage/' . $ticket->photo_path) }}" target="_blank" class="inline-block text-xs font-semibold text-[var(--brand-gold-text)] hover:underline">Lihat Foto Lampiran</a>
                            @endif
                        </div>
                    </div>

                    <form method="POST" action="{{ route('mitra.tickets.update', $ticket) }}" class="pt-4 border-t border-[var(--border-subtle)] grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                        @csrf
                        @method('PATCH')
                        <div class="md:col-span-3">
                            <label class="block text-xs font-semibold text-[var(--text-primary)] mb-1">Status Penanganan</label>
                            <select name="status" class="lk-input text-xs min-h-[40px]">
                                <option value="open" @selected($ticket->status === 'open')>Baru Dilaporkan</option>
                                <option value="in_progress" @selected($ticket->status === 'in_progress')>Sedang Dikerjakan</option>
                                <option value="resolved" @selected($ticket->status === 'resolved')>Selesai Diperbaiki</option>
                            </select>
                        </div>
                        <div class="md:col-span-7">
                            <label class="block text-xs font-semibold text-[var(--text-primary)] mb-1">Catatan Tindak Lanjut untuk Penghuni</label>
                            <input type="text" name="mitra_note" value="{{ $ticket->mitra_note }}" placeholder="Contoh: Teknisi AC dijadwalkan datang besok pukul 10.00 WIB" class="lk-input text-xs min-h-[40px]">
                        </div>
                        <div class="md:col-span-2">
                            <button type="submit" class="lk-btn-primary w-full text-xs min-h-[40px]">Perbarui</button>
                        </div>
                    </form>
                </article>
            @endforeach
            <div>{{ $tickets->links() }}</div>
        @else
            <x-lokakos.empty-state
                title="Belum ada keluhan masuk"
                description="Laporan kerusakan fasilitas dari penghuni kos Anda akan tampil di sini."
            />
        @endif
    </div>
</x-app-layout>
