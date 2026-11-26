<x-public-layout title="Cari Kos Sekitar Kampus Terpadu UMY">
    @php
        $heroHasImage = file_exists(public_path('images/demo/hero-kos-umy.jpg'));
        $splitHasImage = file_exists(public_path('images/demo/room-studio-balcony.jpg'));
    @endphp

    <section class="pt-3 sm:pt-4 px-3 sm:px-5">
        <div class="relative max-w-[1440px] mx-auto rounded-[var(--radius-panel)] overflow-hidden bg-[var(--bg-espresso)] min-h-[480px] sm:min-h-[560px] lg:min-h-[620px] flex items-end">
            @if ($heroHasImage)
                <img
                    src="{{ asset('images/demo/hero-kos-umy.jpg') }}"
                    alt="Ilustrasi demo arsitektur hunian kos hangat di kawasan UMY"
                    class="absolute inset-0 w-full h-full object-cover"
                >
                <div class="absolute inset-0 bg-gradient-to-t from-[#191411]/90 via-[#191411]/55 to-[#191411]/30"></div>
                <div class="absolute inset-0 bg-gradient-to-r from-[#191411]/75 via-[#191411]/30 to-transparent"></div>
            @endif

            <div class="relative z-10 w-full max-w-[1260px] mx-auto px-6 sm:px-10 lg:px-12 pt-16 pb-20 sm:pb-24 lg:pb-28 grid grid-cols-1 lg:grid-cols-12 gap-8 items-end">
                <div class="lg:col-span-8">
                    <h1 class="font-display font-semibold text-[#FAF7F2] leading-[1.06]" style="font-size: clamp(2.15rem, 5vw, 4.25rem);">
                        Tinggal lebih tenang di sekitar Kampus Terpadu UMY
                    </h1>
                    <p class="mt-5 text-base sm:text-lg text-[#E8E0D7] max-w-xl leading-relaxed">
                        Telusuri pilihan kos Putra, Putri, Campur, dan Syariah di Ngebel, Geblagan, Brajan, hingga Tamantirto dengan jarak kampus terhitung dan ketersediaan kamar aktual.
                    </p>
                    <div class="mt-7 flex flex-wrap items-center gap-3">
                        <a href="{{ route('kos.index') }}" class="lk-btn-primary">
                            Lihat Semua Kos
                        </a>
                        <a href="{{ route('mitra.info') }}" class="inline-flex items-center justify-center min-h-[46px] px-6 rounded-full border border-[#FAF7F2]/60 bg-black/25 text-[#FAF7F2] hover:bg-black/45 text-sm font-semibold transition-colors">
                            Gabung Mitra Kos
                        </a>
                    </div>
                </div>

                <div class="lg:col-span-4 lg:text-right">
                    <p class="text-xs text-[#E8E0D7]/90 tabular-nums">
                        Titik acuan jarak: Kampus Terpadu UMY (-7.8113, 110.3216)
                    </p>
                    @if ($heroHasImage)
                        <p class="mt-1 text-[11px] text-[#E8E0D7]/80">
                            Visual latar merupakan ilustrasi demo hunian
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <div class="relative z-20 max-w-[1140px] mx-auto -mt-10 sm:-mt-14 px-2 sm:px-4">
            <form
                method="GET"
                action="{{ route('kos.index') }}"
                class="rounded-[22px] lg:rounded-full bg-[var(--bg-surface)] border border-[var(--border-subtle)] lk-elevated p-4 sm:p-5 lg:px-7 lg:py-4"
            >
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4 items-end">
                    <div class="lg:col-span-3">
                        <label for="home-area" class="block text-xs font-semibold text-[var(--text-secondary)] mb-1">
                            Area sekitar UMY
                        </label>
                        <select id="home-area" name="area" class="lk-input text-sm">
                            <option value="">Semua area sekitar UMY</option>
                            @foreach ($areas as $area)
                                <option value="{{ $area->slug }}">{{ $area->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="lg:col-span-3">
                        <label for="home-gender" class="block text-xs font-semibold text-[var(--text-secondary)] mb-1">
                            Tipe penghuni
                        </label>
                        <select id="home-gender" name="gender" class="lk-input text-sm">
                            <option value="">Semua tipe</option>
                            <option value="putra">Kos Putra</option>
                            <option value="putri">Kos Putri</option>
                            <option value="campur">Kos Campur</option>
                        </select>
                    </div>

                    <div class="lg:col-span-3">
                        <label for="home-max-price" class="block text-xs font-semibold text-[var(--text-secondary)] mb-1">
                            Harga maksimal / bulan
                        </label>
                        <select id="home-max-price" name="max_price" class="lk-input text-sm">
                            <option value="">Semua kisaran harga</option>
                            <option value="750000">Di bawah Rp 750.000</option>
                            <option value="1000000">Di bawah Rp 1.000.000</option>
                            <option value="1250000">Di bawah Rp 1.250.000</option>
                            <option value="1500000">Di bawah Rp 1.500.000</option>
                        </select>
                    </div>

                    <div class="lg:col-span-3">
                        <button type="submit" class="lk-btn-primary w-full">
                            Cari Kos
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <section class="max-w-[1260px] mx-auto px-4 sm:px-6 lg:px-8 pt-16 sm:pt-24 pb-12 sm:pb-16">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div class="max-w-2xl">
                <p class="text-sm font-semibold text-[var(--brand-gold-text)]">Hunian Terverifikasi</p>
                <h2 class="mt-2 font-display font-semibold text-[var(--text-primary)] leading-[1.15]" style="font-size: clamp(1.85rem, 3.2vw, 2.85rem);">
                    Pilihan kos dekat Kampus Terpadu UMY
                </h2>
                <p class="mt-3 text-base text-[var(--text-secondary)] leading-relaxed">
                    Diurutkan dari jarak terdekat menuju kampus. Setiap kos telah melewati pemeriksaan admin dan terhubung langsung dengan pemiliknya.
                </p>
            </div>
            <a href="{{ route('kos.index') }}" class="lk-btn-secondary shrink-0 self-start md:self-auto">
                Lihat Semua Kos
            </a>
        </div>

        @if ($featuredKos->isNotEmpty())
            <div class="mt-10 sm:mt-12 grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-12">
                @foreach ($featuredKos as $kos)
                    <x-lokakos.kos-card :kos="$kos" />
                @endforeach
            </div>
        @else
            <div class="mt-10">
                <x-lokakos.empty-state
                    title="Belum ada kos yang tayang"
                    description="Belum ada properti kos berstatus published di database. Daftarkan kos melalui akun Mitra lalu setujui moderasi dari Admin."
                    action-label="Gabung Jadi Mitra Kos"
                    :action-href="route('mitra.info')"
                />
            </div>
        @endif
    </section>

    <section id="area-umy" class="mx-3 sm:mx-5 my-8 rounded-[var(--radius-panel)] bg-[var(--bg-tint)] border border-[var(--border-subtle)]">
        <div class="max-w-[1260px] mx-auto px-5 sm:px-10 py-14 sm:py-20">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 pb-8 border-b border-[var(--border-subtle)]">
                <div>
                    <p class="text-sm font-semibold text-[var(--brand-gold-text)]">Peta Wilayah Kampus</p>
                    <h2 class="mt-2 font-display font-semibold text-[var(--text-primary)] leading-[1.15]" style="font-size: clamp(1.75rem, 3vw, 2.65rem);">
                        Kawasan sekitar Universitas Muhammadiyah Yogyakarta
                    </h2>
                </div>
                <p class="text-sm text-[var(--text-secondary)] max-w-sm">
                    Pilih dusun atau koridor jalan untuk langsung menyaring kos sesuai jarak tempuh harian Anda.
                </p>
            </div>

            @if ($areas->isNotEmpty())
                <div class="divide-y divide-[var(--border-subtle)]">
                    @foreach ($areas as $area)
                        <a
                            href="{{ route('kos.index', ['area' => $area->slug]) }}"
                            class="group py-5 sm:py-6 grid grid-cols-1 md:grid-cols-12 gap-3 md:gap-6 items-center hover:bg-[var(--bg-surface)]/70 -mx-3 px-3 rounded-[var(--radius-control)] transition-colors"
                        >
                            <div class="md:col-span-4">
                                <span class="font-display text-xl sm:text-2xl font-semibold text-[var(--text-primary)] group-hover:text-[var(--brand-gold-text)] transition-colors">
                                    {{ $area->name }}
                                </span>
                            </div>

                            <div class="md:col-span-5">
                                <p class="text-sm text-[var(--text-secondary)] line-clamp-2">
                                    {{ $area->description }}
                                </p>
                            </div>

                            <div class="md:col-span-3 flex items-center justify-between md:justify-end gap-4">
                                <span class="text-sm font-semibold text-[var(--text-primary)] tabular-nums">
                                    {{ $area->published_count }} kos tayang
                                </span>
                                <span class="inline-flex items-center justify-center w-10 h-10 rounded-full border border-[var(--border-control)] text-[var(--text-primary)] group-hover:bg-[var(--brand-gold)] group-hover:text-[var(--brand-gold-ink)] group-hover:border-[var(--brand-gold)] transition-colors" aria-hidden="true">
                                    <svg class="w-4 h-4" viewBox="0 0 256 256" fill="none" stroke="currentColor" stroke-width="20" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="64" y1="192" x2="192" y2="64" />
                                        <polyline points="88 64 192 64 192 168" />
                                    </svg>
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="max-w-[1260px] mx-auto px-4 sm:px-6 lg:px-8 pt-12 sm:pt-16 pb-4">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
            <div class="lg:col-span-5 rounded-[var(--radius-panel)] bg-[var(--bg-espresso)] text-[var(--espresso-ink)] p-7 sm:p-9 flex flex-col justify-between overflow-hidden">
                <div>
                    <p class="text-sm font-semibold text-[var(--espresso-gold)]">Untuk Pemilik Kos Lokal</p>
                    <h2 class="mt-2 font-display text-2xl sm:text-3xl font-semibold leading-tight text-[var(--espresso-ink)]">
                        Kelola kamar dan bukti transfer penyewa tanpa tercecer
                    </h2>
                    <p class="mt-4 text-sm sm:text-base text-[var(--espresso-muted)] leading-relaxed">
                        Daftarkan properti kos Anda di Tamantirto, Ngebel, Geblagan, Brajan, atau Kasihan. Mahasiswa mentransfer langsung ke rekening Anda, sementara LokaKos mencatat pesanan dan stok kamar.
                    </p>
                </div>

                @if ($splitHasImage)
                    <div class="mt-6 rounded-[var(--radius-card)] overflow-hidden aspect-[16/9] relative">
                        <img
                            src="{{ asset('images/demo/room-studio-balcony.jpg') }}"
                            alt="Ilustrasi demo interior kamar kos studio hangat"
                            class="w-full h-full object-cover"
                            loading="lazy"
                        >
                        <span class="absolute bottom-2.5 right-2.5 px-2 py-0.5 rounded bg-black/70 text-[#F5F1EB] text-[11px]">
                            Ilustrasi demo
                        </span>
                    </div>
                @endif

                <div class="mt-7 flex flex-wrap gap-3">
                    <a href="{{ route('register.mitra') }}" class="lk-btn-primary">
                        Daftar Jadi Mitra Kos
                    </a>
                    <a href="{{ route('mitra.info') }}" class="inline-flex items-center justify-center min-h-[46px] px-5 rounded-full border border-white/35 text-sm font-medium text-[var(--espresso-ink)] hover:bg-white/10 transition-colors">
                        Alur Verifikasi
                    </a>
                </div>
            </div>

            <div class="lg:col-span-7 rounded-[var(--radius-panel)] bg-[var(--bg-surface)] border border-[var(--border-subtle)] overflow-hidden grid grid-cols-1 sm:grid-cols-2">
                <div class="p-7 sm:p-8 border-b sm:border-r border-[var(--border-subtle)] flex flex-col justify-between">
                    <span class="text-xs font-semibold text-[var(--brand-gold-text)] tabular-nums">01</span>
                    <div class="mt-6">
                        <h3 class="text-xl font-semibold text-[var(--text-primary)]">Moderasi Mitra & Listing</h3>
                        <p class="mt-2.5 text-sm text-[var(--text-secondary)] leading-relaxed">
                            Pendaftaran pemilik kos dan data properti ditinjau terlebih dahulu oleh Admin sebelum muncul di katalog mahasiswa.
                        </p>
                    </div>
                </div>

                <div class="p-7 sm:p-8 border-b border-[var(--border-subtle)] flex flex-col justify-between">
                    <span class="text-xs font-semibold text-[var(--brand-gold-text)] tabular-nums">02</span>
                    <div class="mt-6">
                        <h3 class="text-xl font-semibold text-[var(--text-primary)]">Hitung Jarak ke UMY</h3>
                        <p class="mt-2.5 text-sm text-[var(--text-secondary)] leading-relaxed">
                            Titik koordinat peta dihitung otomatis terhadap Kampus Terpadu UMY sehingga mahasiswa dapat mengurutkan kos terdekat.
                        </p>
                    </div>
                </div>

                <div class="p-7 sm:p-8 border-b sm:border-b-0 sm:border-r border-[var(--border-subtle)] flex flex-col justify-between">
                    <span class="text-xs font-semibold text-[var(--brand-gold-text)] tabular-nums">03</span>
                    <div class="mt-6">
                        <h3 class="text-xl font-semibold text-[var(--text-primary)]">Stok Terkunci Transaksional</h3>
                        <p class="mt-2.5 text-sm text-[var(--text-secondary)] leading-relaxed">
                            Kamar tersedia hanya berkurang otomatis ketika Mitra menyetujui bukti transfer valid, mencegah pemesanan ganda.
                        </p>
                    </div>
                </div>

                <div class="p-7 sm:p-8 flex flex-col justify-between">
                    <span class="text-xs font-semibold text-[var(--brand-gold-text)] tabular-nums">04</span>
                    <div class="mt-6">
                        <h3 class="text-xl font-semibold text-[var(--text-primary)]">Bukti Pemesanan PDF</h3>
                        <p class="mt-2.5 text-sm text-[var(--text-secondary)] leading-relaxed">
                            Setelah pembayaran dikonfirmasi Mitra, penyewa memperoleh invoice PDF resmi dengan kode booking untuk ditunjukkan saat check-in.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-public-layout>
