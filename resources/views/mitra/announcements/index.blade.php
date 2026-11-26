<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-semibold text-[var(--text-primary)]">Papan Pengumuman & Tanya Jawab Kos</h1>
            <p class="mt-1 text-sm text-[var(--text-secondary)]">Bagikan informasi penting ke penghuni aktif dan jawab pertanyaan calon penyewa di halaman kos.</p>
        </div>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
        <section class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <div class="lg:col-span-5">
                <div class="lk-card p-6 space-y-4">
                    <h2 class="text-lg font-semibold text-[var(--text-primary)]">Buat Pengumuman Penghuni</h2>
                    <form method="POST" action="{{ route('mitra.announcements.store') }}" class="space-y-4">
                        @csrf
                        <div>
                            <x-input-label for="boarding_house_id" value="Pilih Properti Kos" />
                            <select id="boarding_house_id" name="boarding_house_id" required class="lk-input text-sm">
                                @foreach ($myKos as $k)
                                    <option value="{{ $k->id }}">{{ $k->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="title" value="Judul Pengumuman" />
                            <x-text-input id="title" name="title" type="text" required placeholder="Contoh: Jadwal Perawatan Wi-Fi & Kebersihan" />
                        </div>
                        <div>
                            <x-input-label for="body" value="Isi Pengumuman" />
                            <textarea id="body" name="body" rows="3" required class="lk-input text-sm" placeholder="Tulis info jadwal atau himbauan penting untuk penghuni kos"></textarea>
                        </div>
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_pinned" value="1" class="w-4 h-4 rounded border-[var(--border-control)]">
                            <span class="text-xs font-medium text-[var(--text-primary)]">Sematkan di bagian paling atas</span>
                        </label>
                        <button type="submit" class="lk-btn-primary w-full text-sm">Terbitkan Pengumuman</button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-7 space-y-3">
                <h2 class="text-lg font-semibold text-[var(--text-primary)]">Pengumuman Terbit</h2>
                @if ($announcements->isNotEmpty())
                    @foreach ($announcements as $ann)
                        <article class="lk-card p-5 space-y-2">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <span class="text-xs font-semibold text-[var(--brand-gold-text)]">{{ $ann->boardingHouse->name }}</span>
                                    @if ($ann->is_pinned)
                                        <span class="ml-2 px-2 py-0.5 rounded-full bg-[var(--bg-tint)] border border-[var(--border-subtle)] text-[11px] font-semibold text-[var(--text-primary)]">Disematkan</span>
                                    @endif
                                    <h3 class="mt-1 text-base font-semibold text-[var(--text-primary)]">{{ $ann->title }}</h3>
                                </div>
                                <form method="POST" action="{{ route('mitra.announcements.destroy', $ann) }}" onsubmit="return confirm('Hapus pengumuman ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold text-[var(--status-danger)] hover:underline">Hapus</button>
                                </form>
                            </div>
                            <p class="text-sm text-[var(--text-secondary)] leading-relaxed">{{ $ann->body }}</p>
                            <p class="text-[11px] text-[var(--text-secondary)] tabular-nums">{{ $ann->created_at->format('d M Y H:i') }} WIB</p>
                        </article>
                    @endforeach
                @else
                    <x-lokakos.empty-state
                        title="Belum ada pengumuman"
                        description="Pengumuman yang Anda terbitkan langsung tampil di dashboard penghuni aktif properti tersebut."
                    />
                @endif
            </div>
        </section>

        <section id="tanya-jawab" class="space-y-4 pt-6 border-t border-[var(--border-subtle)]">
            <h2 class="text-xl font-semibold text-[var(--text-primary)]">Pertanyaan Publik Calon Penyewa (Q&A)</h2>
            @if ($questions->isNotEmpty())
                <div class="space-y-4">
                    @foreach ($questions as $q)
                        <article class="lk-card p-5 space-y-3">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <span class="text-xs font-semibold text-[var(--brand-gold-text)]">{{ $q->boardingHouse->name }}</span>
                                <span class="text-xs text-[var(--text-secondary)]">
                                    Ditanyakan oleh <strong class="text-[var(--text-primary)]">{{ $q->user?->name }}</strong> · {{ $q->created_at->format('d M Y') }}
                                </span>
                            </div>
                            <p class="text-sm font-medium text-[var(--text-primary)]">"{{ $q->question }}"</p>

                            <form method="POST" action="{{ route('mitra.questions.answer', $q) }}" class="flex flex-col sm:flex-row gap-2">
                                @csrf
                                @method('PATCH')
                                <input
                                    type="text"
                                    name="answer"
                                    value="{{ $q->answer }}"
                                    required
                                    placeholder="Tulis jawaban terbuka agar mahasiswa lain juga terbantu..."
                                    class="lk-input text-xs flex-1"
                                >
                                <button type="submit" class="lk-btn-primary text-xs min-h-[40px] px-4 shrink-0">
                                    {{ $q->answer ? 'Perbarui Jawaban' : 'Kirim Jawaban' }}
                                </button>
                            </form>
                        </article>
                    @endforeach
                </div>
            @else
                <x-lokakos.empty-state
                    title="Belum ada pertanyaan publik"
                    description="Pertanyaan yang diajukan mahasiswa di halaman detail kos Anda akan muncul di sini untuk dijawab."
                />
            @endif
        </section>
    </div>
</x-app-layout>
