<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-semibold text-[var(--text-primary)]">Laporan Perbaikan & Keluhan Kamar</h1>
            <p class="mt-1 text-sm text-[var(--text-secondary)]">Laporkan kendala fasilitas kamar aktif Anda agar segera ditangani pengelola kos.</p>
        </div>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <div class="lg:col-span-5">
            <section class="lk-card p-6 space-y-4">
                <h2 class="text-lg font-semibold text-[var(--text-primary)]">Kirim Laporan Perbaikan</h2>

                @if ($activeBookings->isNotEmpty())
                    <form method="POST" action="{{ route('penyewa.maintenance.store') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div>
                            <x-input-label for="booking_id" value="Pilih Kamar Aktif" />
                            <select id="booking_id" name="booking_id" required class="lk-input text-sm">
                                @foreach ($activeBookings as $b)
                                    <option value="{{ $b->id }}">
                                        {{ $b->roomType?->boardingHouse?->name }} · {{ $b->roomType?->name }} ({{ $b->booking_code }})
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('booking_id')" />
                        </div>

                        <div>
                            <x-input-label for="category" value="Kategori Kendala" />
                            <select id="category" name="category" required class="lk-input text-sm">
                                <option value="ac_kipas">AC / Kipas Angin</option>
                                <option value="listrik">Listrik & Lampu</option>
                                <option value="air_kamar_mandi">Air & Kamar Mandi</option>
                                <option value="wifi">Jaringan Wi-Fi</option>
                                <option value="perabot">Perabot (Kasur / Lemari / Meja)</option>
                                <option value="kebersihan">Kebersihan Lingkungan</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>

                        <div>
                            <x-input-label for="title" value="Ringkasan Kendala" />
                            <x-text-input id="title" name="title" type="text" :value="old('title')" required placeholder="Contoh: AC kamar kurang dingin dan menetes" />
                            <x-input-error :messages="$errors->get('title')" />
                        </div>

                        <div>
                            <x-input-label for="description" value="Detail Keluhan" />
                            <textarea id="description" name="description" rows="3" required class="lk-input text-sm" placeholder="Jelaskan lokasi dan kondisi kendala agar teknisi mudah mengecek">{{ old('description') }}</textarea>
                            <x-input-error :messages="$errors->get('description')" />
                        </div>

                        <div>
                            <x-input-label for="photo" value="Foto Pendukung (Opsional, maks 2 MB)" />
                            <input id="photo" name="photo" type="file" accept=".jpg,.jpeg,.png,.webp" class="lk-input text-sm">
                            <x-input-error :messages="$errors->get('photo')" />
                        </div>

                        <button type="submit" class="lk-btn-primary w-full text-sm">Kirim Laporan ke Pengelola</button>
                    </form>
                @else
                    <p class="text-sm text-[var(--text-secondary)]">
                        Form laporan perbaikan otomatis aktif setelah Anda memiliki pesanan kamar berstatus Terkonfirmasi atau Selesai (Check-in).
                    </p>
                @endif
            </section>
        </div>

        <div class="lg:col-span-7 space-y-4">
            <h2 class="text-lg font-semibold text-[var(--text-primary)]">Riwayat Laporan Anda</h2>
            @if ($tickets->isNotEmpty())
                @foreach ($tickets as $ticket)
                    <article class="lk-card p-5 space-y-2.5">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="text-xs font-semibold uppercase tracking-wide text-[var(--brand-gold-text)]">{{ str_replace('_', ' ', $ticket->category) }}</span>
                            <x-lokakos.status-badge :status="$ticket->status" />
                        </div>
                        <h3 class="text-base font-semibold text-[var(--text-primary)]">{{ $ticket->title }}</h3>
                        <p class="text-xs text-[var(--text-secondary)]">{{ $ticket->boardingHouse->name }} · {{ $ticket->created_at->format('d M Y H:i') }} WIB</p>
                        <p class="text-sm text-[var(--text-secondary)] leading-relaxed">{{ $ticket->description }}</p>
                        @if ($ticket->photo_path)
                            <a href="{{ asset('storage/' . $ticket->photo_path) }}" target="_blank" class="inline-block text-xs font-semibold text-[var(--brand-gold-text)] hover:underline">Lihat Foto Lampiran</a>
                        @endif
                        @if ($ticket->mitra_note)
                            <div class="p-3 rounded-[var(--radius-control)] bg-[var(--bg-tint)] border border-[var(--border-subtle)] text-xs text-[var(--text-primary)]">
                                <strong>Tanggapan Pengelola:</strong> {{ $ticket->mitra_note }}
                            </div>
                        @endif
                    </article>
                @endforeach
                <div>{{ $tickets->links() }}</div>
            @else
                <x-lokakos.empty-state
                    title="Belum ada laporan perbaikan"
                    description="Jika ada kendala di kamar kos yang sedang Anda huni, kirim laporan lewat formulir di samping."
                />
            @endif
        </div>
    </div>
</x-app-layout>
