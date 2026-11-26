<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-semibold text-[var(--text-primary)]">Promo & Kode Voucher Kos</h1>
            <p class="mt-1 text-sm text-[var(--text-secondary)]">Buat diskon khusus awal semester atau paket sewa 6/12 bulan untuk menarik mahasiswa UMY.</p>
        </div>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <div class="lg:col-span-5">
            <section class="lk-card p-6 space-y-4">
                <h2 class="text-lg font-semibold text-[var(--text-primary)]">Buat Promo Baru</h2>
                <form method="POST" action="{{ route('mitra.promos.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="boarding_house_id" value="Berlaku untuk Properti Kos" />
                        <select id="boarding_house_id" name="boarding_house_id" class="lk-input text-sm">
                            <option value="">Semua properti kos milik saya</option>
                            @foreach ($myKos as $k)
                                <option value="{{ $k->id }}" @selected((string) old('boarding_house_id') === (string) $k->id)>{{ $k->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <x-input-label for="code" value="Kode Voucher" />
                            <x-text-input id="code" name="code" type="text" :value="old('code')" required placeholder="MABAUMY26" class="uppercase" />
                            <x-input-error :messages="$errors->get('code')" />
                        </div>
                        <div>
                            <x-input-label for="min_duration_months" value="Minimal Durasi" />
                            <select id="min_duration_months" name="min_duration_months" class="lk-input text-sm">
                                <option value="1">1 Bulan</option>
                                <option value="3">3 Bulan</option>
                                <option value="6" selected>6 Bulan</option>
                                <option value="12">12 Bulan</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <x-input-label for="title" value="Judul Promo" />
                        <x-text-input id="title" name="title" type="text" :value="old('title')" required placeholder="Potongan Mahasiswa Baru Semester Ganjil" />
                        <x-input-error :messages="$errors->get('title')" />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <x-input-label for="discount_type" value="Jenis Potongan" />
                            <select id="discount_type" name="discount_type" class="lk-input text-sm">
                                <option value="fixed">Nominal (Rp)</option>
                                <option value="percent">Persentase (%)</option>
                            </select>
                        </div>
                        <div>
                            <x-input-label for="discount_value" value="Nilai Potongan" />
                            <x-text-input id="discount_value" name="discount_value" type="number" min="1" :value="old('discount_value', 150000)" required class="tabular-nums" />
                            <x-input-error :messages="$errors->get('discount_value')" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <x-input-label for="valid_from" value="Mulai Berlaku" />
                            <x-text-input id="valid_from" name="valid_from" type="date" :value="old('valid_from', now()->toDateString())" required />
                        </div>
                        <div>
                            <x-input-label for="valid_until" value="Berakhir Pada" />
                            <x-text-input id="valid_until" name="valid_until" type="date" :value="old('valid_until', now()->addMonths(2)->toDateString())" required />
                            <x-input-error :messages="$errors->get('valid_until')" />
                        </div>
                    </div>

                    <button type="submit" class="lk-btn-primary w-full text-sm">Simpan & Aktifkan Promo</button>
                </form>
            </section>
        </div>

        <div class="lg:col-span-7 space-y-4">
            <h2 class="text-lg font-semibold text-[var(--text-primary)]">Daftar Promo Anda</h2>
            @if ($promos->isNotEmpty())
                @foreach ($promos as $promo)
                    <article class="lk-card p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-[var(--radius-badge)] bg-[var(--brand-gold)] text-[var(--brand-gold-ink)] text-xs font-bold tracking-wide">
                                    {{ $promo->code }}
                                </span>
                                <x-lokakos.status-badge :status="$promo->is_active ? 'published' : 'draft'" :label="$promo->is_active ? 'Aktif' : 'Nonaktif'" />
                            </div>
                            <h3 class="text-base font-semibold text-[var(--text-primary)]">{{ $promo->title }}</h3>
                            <p class="text-xs text-[var(--text-secondary)]">
                                {{ $promo->boardingHouse?->name ?? 'Semua Kos Saya' }} ·
                                {{ $promo->discount_type === 'percent' ? 'Diskon ' . $promo->discount_value . '%' : 'Potongan Rp ' . number_format($promo->discount_value, 0, ',', '.') }} ·
                                Min {{ $promo->min_duration_months }} bulan
                            </p>
                            <p class="text-xs text-[var(--text-secondary)] tabular-nums">
                                Berlaku {{ $promo->valid_from->format('d M Y') }} s.d. {{ $promo->valid_until->format('d M Y') }}
                            </p>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <form method="POST" action="{{ route('mitra.promos.toggle', $promo) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="lk-btn-secondary text-xs min-h-[38px] px-3.5">
                                    {{ $promo->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('mitra.promos.destroy', $promo) }}" onsubmit="return confirm('Hapus promo ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-semibold text-[var(--status-danger)] hover:underline px-2 py-2">Hapus</button>
                            </form>
                        </div>
                    </article>
                @endforeach
            @else
                <x-lokakos.empty-state
                    title="Belum ada promo aktif"
                    description="Tambahkan kode voucher diskon agar tampil sebagai penawaran menarik pada halaman detail kos Anda."
                />
            @endif
        </div>
    </div>
</x-app-layout>
