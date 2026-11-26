@php
    $genderLabel = match ($kos->gender_type) {
        'putra' => 'Putra',
        'putri' => 'Putri',
        default => 'Campur',
    };
    $demoPool = [
        'images/demo/room-boutique-ac.jpg',
        'images/demo/room-minimal-warm.jpg',
        'images/demo/room-studio-balcony.jpg',
    ];
    $demoPath = $demoPool[$kos->id % count($demoPool)];
    $hasDemoFile = file_exists(public_path($demoPath));

    $roomsPayload = $kos->roomTypes->map(fn ($r) => [
        'id' => $r->id,
        'name' => $r->name,
        'available' => $r->available_rooms,
        'deposit' => (int) ($r->deposit_amount ?? 0),
        'max_occupancy' => (int) ($r->max_occupancy ?? 1),
        'electricity_included' => (bool) $r->electricity_included,
        'prices' => [
            1 => $r->calculatePrice(1),
            3 => $r->calculatePrice(3),
            6 => $r->calculatePrice(6),
            12 => $r->calculatePrice(12),
        ],
    ])->values();
    $firstAvailableRoom = $kos->roomTypes->firstWhere('available_rooms', '>', 0) ?? $kos->roomTypes->first();
    $landmarks = $kos->landmarkList();
@endphp

<x-public-layout :title="$kos->name">
    @push('head')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    @endpush

    <div class="max-w-[1260px] mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
        <nav aria-label="Breadcrumb" class="text-xs sm:text-sm text-[var(--text-secondary)] flex flex-wrap items-center gap-2">
            <a href="{{ route('home') }}" class="hover:text-[var(--brand-gold-text)]">Beranda</a>
            <span aria-hidden="true">/</span>
            <a href="{{ route('kos.index') }}" class="hover:text-[var(--brand-gold-text)]">Cari Kos</a>
            <span aria-hidden="true">/</span>
            <span class="text-[var(--text-primary)] font-medium">{{ $kos->name }}</span>
        </nav>

        <div class="mt-5 flex flex-wrap items-center gap-2 text-xs">
            <span class="px-3 py-1 rounded-full bg-[var(--bg-tint)] text-[var(--text-primary)] font-medium border border-[var(--border-subtle)]">
                Kos {{ $genderLabel }}
            </span>
            @if ($kos->is_syariah)
                <span class="px-3 py-1 rounded-full bg-[var(--brand-secondary-bg)] text-[var(--brand-secondary-ink)] font-semibold">
                    Syariah
                </span>
            @endif
            @if ($kos->is_field_verified)
                <span class="px-3 py-1 rounded-full bg-[var(--brand-secondary-bg)] text-[var(--brand-secondary-ink)] font-semibold">
                    Terverifikasi Lapangan
                </span>
            @endif
            @if ($kos->distance_to_umy_km !== null)
                <span class="px-3 py-1 rounded-full bg-[var(--bg-surface)] text-[var(--text-primary)] border border-[var(--border-subtle)] font-semibold tabular-nums">
                    {{ number_format($kos->distance_to_umy_km, 2, ',', '.') }} km dari UMY · ~{{ $kos->estimatedWalkingMinutes() }} mnt jalan · ~{{ $kos->estimatedMotorMinutes() }} mnt motor
                </span>
            @endif
        </div>

        <div class="mt-3 flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <h1 class="font-display text-3xl sm:text-5xl font-semibold text-[var(--text-primary)] leading-[1.1]">
                    {{ $kos->name }}
                </h1>
                <p class="mt-2.5 text-sm sm:text-base text-[var(--text-secondary)]">
                    {{ $kos->area?->name }} · {{ $kos->address }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                @auth
                    @if (auth()->user()->isPenyewa())
                        <form method="POST" action="{{ route('penyewa.saved.toggle', $kos) }}">
                            @csrf
                            <button type="submit" class="{{ ($isSaved ?? false) ? 'lk-btn-espresso' : 'lk-btn-secondary' }} text-xs min-h-[42px]">
                                {{ ($isSaved ?? false) ? 'Tersimpan di Favorit' : 'Simpan Kos' }}
                            </button>
                        </form>
                    @endif
                @endauth

                <a href="{{ route('kos.compare', ['ids' => $kos->id]) }}" class="lk-btn-secondary text-xs min-h-[42px]">
                    Bandingkan
                </a>

                @if (! empty($whatsappUrl))
                    <a
                        href="{{ $whatsappUrl }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="lk-btn-secondary text-xs min-h-[42px]"
                    >
                        Tanya via WhatsApp
                    </a>
                @endif
            </div>
        </div>

        @if (session('status'))
            <div class="mt-4 p-3.5 rounded-[var(--radius-control)] bg-[var(--brand-secondary-bg)] text-[var(--brand-secondary-ink)] text-sm font-medium border border-[var(--border-subtle)]">
                {{ session('status') }}
            </div>
        @endif

        @if (($activePromos ?? collect())->isNotEmpty())
            <div class="mt-5 p-4 rounded-[var(--radius-control)] bg-[var(--bg-tint)] border border-[var(--border-subtle)] flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-[var(--brand-gold-text)]">Penawaran Promo Aktif</p>
                    <div class="mt-1 flex flex-wrap gap-2">
                        @foreach ($activePromos as $promo)
                            <span class="px-3 py-1 rounded-full bg-[var(--brand-gold)] text-[var(--brand-gold-ink)] text-xs font-semibold">
                                {{ $promo->badgeLabel() }} · Min {{ $promo->min_duration_months }} bln
                            </span>
                        @endforeach
                    </div>
                </div>
                <span class="text-xs text-[var(--text-secondary)]">Masukkan kode voucher pada form pemesanan di samping.</span>
            </div>
        @endif

        <div class="mt-8 grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-start">
            <div class="lg:col-span-8 space-y-8 min-w-0">
                <div class="relative rounded-[var(--radius-panel)] overflow-hidden border border-[var(--border-subtle)] bg-[var(--bg-tint)] aspect-[16/10]">
                    @if ($kos->images->isNotEmpty())
                        <img
                            src="{{ asset('storage/' . $kos->images->first()->image_path) }}"
                            alt="Foto {{ $kos->name }}"
                            class="w-full h-full object-cover"
                        >
                    @elseif ($hasDemoFile)
                        <img
                            src="{{ asset($demoPath) }}"
                            alt="Ilustrasi demo kamar untuk {{ $kos->name }}"
                            class="w-full h-full object-cover"
                        >
                        <span class="absolute bottom-4 right-4 px-3 py-1 rounded-[var(--radius-badge)] bg-black/75 text-[#F5F1EB] text-xs font-medium">
                            Ilustrasi demo kamar (diganti saat Mitra mengunggah foto asli)
                        </span>
                    @else
                        <div class="w-full h-full flex items-center justify-center p-6 text-center">
                            <span class="text-xs font-semibold text-[var(--text-secondary)]">[FOTO KOS - PLACEHOLDER]</span>
                        </div>
                    @endif
                </div>

                <section class="lk-card p-6 sm:p-8">
                    <h2 class="text-2xl font-semibold text-[var(--text-primary)]">Tentang Kos & Peraturan</h2>
                    @if ($kos->description)
                        <p class="mt-3 text-sm sm:text-base text-[var(--text-secondary)] leading-relaxed">
                            {{ $kos->description }}
                        </p>
                    @endif

                    @if (! empty($landmarks))
                        <div class="mt-6 pt-6 border-t border-[var(--border-subtle)]">
                            <h3 class="text-base font-semibold text-[var(--text-primary)]">Penanda Lokasi Sekitar Kampus UMY</h3>
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach ($landmarks as $lm)
                                    <span class="px-3.5 py-1.5 rounded-full border border-[var(--border-subtle)] bg-[var(--bg-surface)] text-xs font-medium text-[var(--text-primary)]">
                                        {{ $lm }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($kos->rules)
                        <div class="mt-6 pt-6 border-t border-[var(--border-subtle)]">
                            <h3 class="text-base font-semibold text-[var(--text-primary)]">Peraturan Penghuni</h3>
                            <ul class="mt-3 space-y-2.5 text-sm text-[var(--text-secondary)]">
                                @foreach (preg_split('/\r\n|\r|\n/', trim($kos->rules)) as $rule)
                                    @if (trim($rule) !== '')
                                        <li class="flex items-start gap-2.5">
                                            <span class="mt-2 w-1.5 h-1.5 rounded-full bg-[var(--brand-gold)] shrink-0"></span>
                                            <span>{{ trim($rule) }}</span>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if ($kos->facilities->isNotEmpty())
                        <div class="mt-6 pt-6 border-t border-[var(--border-subtle)]">
                            <h3 class="text-base font-semibold text-[var(--text-primary)]">Fasilitas Umum Kos</h3>
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach ($kos->facilities as $facility)
                                    <span class="px-3.5 py-1.5 rounded-full border border-[var(--border-subtle)] bg-[var(--bg-tint)] text-xs font-medium text-[var(--text-primary)]">
                                        {{ $facility->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </section>

                <section class="space-y-4">
                    <h2 class="text-2xl font-semibold text-[var(--text-primary)]">Pilihan Tipe Kamar</h2>

                    @if ($kos->roomTypes->isNotEmpty())
                        @foreach ($kos->roomTypes as $room)
                            <div class="lk-card p-6 sm:p-7">
                                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                                    <div class="flex gap-4 items-start">
                                        @if ($room->room_image_path)
                                            <img src="{{ asset('storage/' . $room->room_image_path) }}" alt="Foto {{ $room->name }}" class="w-24 h-20 rounded-[var(--radius-control)] object-cover border border-[var(--border-subtle)] shrink-0">
                                        @endif
                                        <div>
                                            <h3 class="text-xl font-semibold text-[var(--text-primary)]">{{ $room->name }}</h3>
                                            <p class="mt-1 text-xs text-[var(--text-secondary)]">
                                                @if ($room->size_m2)
                                                    Ukuran {{ $room->size_m2 }} ·
                                                @endif
                                                Maks {{ $room->max_occupancy }} orang ·
                                                {{ $room->electricity_included ? 'Termasuk listrik' : 'Listrik token mandiri' }}
                                                @if ($room->deposit_amount > 0)
                                                    · Deposit Rp {{ number_format($room->deposit_amount, 0, ',', '.') }}
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    @if ($room->available_rooms > 0)
                                        <x-lokakos.status-badge status="published" :label="'Tersedia ' . $room->available_rooms . ' dari ' . $room->total_rooms . ' kamar'" />
                                    @else
                                        <x-lokakos.status-badge status="rejected" label="Kamar penuh" />
                                    @endif
                                </div>

                                @if ($room->description)
                                    <p class="mt-3 text-sm text-[var(--text-secondary)] leading-relaxed">{{ $room->description }}</p>
                                @endif

                                @if ($room->facilities->isNotEmpty())
                                    <div class="mt-4 flex flex-wrap gap-2">
                                        @foreach ($room->facilities as $facility)
                                            <span class="px-3 py-1 rounded-full bg-[var(--bg-tint)] text-xs text-[var(--text-primary)]">
                                                {{ $facility->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="mt-5 pt-5 border-t border-[var(--border-subtle)] grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                                    <div>
                                        <span class="block text-xs text-[var(--text-secondary)]">1 Bulan</span>
                                        <span class="font-bold text-[var(--text-primary)] tabular-nums">Rp {{ number_format($room->calculatePrice(1), 0, ',', '.') }}</span>
                                    </div>
                                    <div>
                                        <span class="block text-xs text-[var(--text-secondary)]">3 Bulan</span>
                                        <span class="font-semibold text-[var(--text-primary)] tabular-nums">Rp {{ number_format($room->calculatePrice(3), 0, ',', '.') }}</span>
                                    </div>
                                    <div>
                                        <span class="block text-xs text-[var(--text-secondary)]">6 Bulan</span>
                                        <span class="font-semibold text-[var(--text-primary)] tabular-nums">Rp {{ number_format($room->calculatePrice(6), 0, ',', '.') }}</span>
                                    </div>
                                    <div>
                                        <span class="block text-xs text-[var(--text-secondary)]">12 Bulan</span>
                                        <span class="font-semibold text-[var(--text-primary)] tabular-nums">Rp {{ number_format($room->calculatePrice(12), 0, ',', '.') }}</span>
                                    </div>
                                </div>

                                <div class="mt-4 pt-4 border-t border-[var(--border-subtle)] flex flex-wrap items-center justify-between gap-2">
                                    @if ($room->available_rooms === 0)
                                        @auth
                                            @if (auth()->user()->isPenyewa())
                                                @php $isWaiting = in_array($room->id, $waitlistedRoomIds ?? [], true); @endphp
                                                <form method="POST" action="{{ route('penyewa.waitlist.toggle', $room) }}">
                                                    @csrf
                                                    <button type="submit" class="{{ $isWaiting ? 'lk-btn-espresso' : 'lk-btn-primary' }} text-xs min-h-[38px] px-4">
                                                        {{ $isWaiting ? 'Terdaftar di Daftar Tunggu' : 'Kabari Jika Tersedia' }}
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-xs text-[var(--text-secondary)]">Unit kamar tipe ini sedang penuh.</span>
                                            @endif
                                        @else
                                            <a href="{{ route('login') }}" class="text-xs font-semibold text-[var(--brand-gold-text)] hover:underline">
                                                Masuk untuk mendaftar tunggu kamar ini
                                            </a>
                                        @endauth
                                    @else
                                        <span class="text-xs text-[var(--text-secondary)]">Ingin tanya detail atau jadwal survei tipe kamar ini?</span>
                                    @endif

                                    @if ($roomWaUrl = $kos->whatsappUrl($room, auth()->user()))
                                        <a
                                            href="{{ $roomWaUrl }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="lk-btn-secondary text-xs min-h-[38px] px-4"
                                        >
                                            Tanya Tipe Ini via WA
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <x-lokakos.empty-state
                            title="Belum ada tipe kamar"
                            description="Pengelola belum menambahkan rincian tipe kamar untuk kos ini."
                        />
                    @endif
                </section>

                <section class="lk-card p-6 sm:p-8">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 class="text-2xl font-semibold text-[var(--text-primary)]">Lokasi Relatif ke Kampus Terpadu UMY</h2>
                            @if ($kos->distance_to_umy_km !== null)
                                <p class="mt-1 text-xs font-semibold text-[var(--text-secondary)] tabular-nums">
                                    {{ number_format($kos->distance_to_umy_km, 2, ',', '.') }} km · ~{{ $kos->estimatedWalkingMinutes() }} mnt jalan · ~{{ $kos->estimatedMotorMinutes() }} mnt motor
                                </p>
                            @endif
                        </div>
                        @if ($gmapsNav = $kos->googleMapsDirectionsUrl())
                            <a
                                href="{{ $gmapsNav }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="lk-btn-secondary text-xs min-h-[40px] px-4"
                            >
                                Buka di Google Maps
                            </a>
                        @endif
                    </div>

                    @if ($kos->latitude !== null && $kos->longitude !== null)
                        <div id="kos-map-status" class="mt-3 text-xs text-[var(--text-secondary)]">
                            Memuat peta lokasi OpenStreetMap...
                        </div>
                        <div
                            id="kos-map"
                            data-lat="{{ $kos->latitude }}"
                            data-lng="{{ $kos->longitude }}"
                            data-name="{{ $kos->name }}"
                            data-umy-lat="{{ \App\Models\BoardingHouse::UMY_LATITUDE }}"
                            data-umy-lng="{{ \App\Models\BoardingHouse::UMY_LONGITUDE }}"
                            class="mt-3 h-72 sm:h-80 w-full rounded-[var(--radius-card)] border border-[var(--border-subtle)] bg-[var(--bg-tint)] z-0"
                        ></div>
                    @else
                        <p class="mt-3 text-sm text-[var(--text-secondary)]">Koordinat lokasi belum ditentukan oleh Mitra.</p>
                    @endif
                </section>

                <section id="tanya-jawab" class="lk-card p-6 sm:p-8 space-y-5">
                    <div>
                        <h2 class="text-2xl font-semibold text-[var(--text-primary)]">Tanya Jawab Publik (Q&A)</h2>
                        <p class="mt-1 text-xs text-[var(--text-secondary)]">Ajukan pertanyaan seputar parkir, sinyal internet, atau fasilitas agar dijawab terbuka oleh pengelola.</p>
                    </div>

                    @auth
                        <form method="POST" action="{{ route('kos.questions.store', $kos) }}" class="flex flex-col sm:flex-row gap-2.5">
                            @csrf
                            <input
                                type="text"
                                name="question"
                                required
                                minlength="8"
                                placeholder="Contoh: Apakah area parkir muat motor besar dan sinyal internet lancar?"
                                class="lk-input text-sm flex-1"
                            >
                            <button type="submit" class="lk-btn-primary text-xs min-h-[42px] px-5 shrink-0">Kirim Pertanyaan</button>
                        </form>
                    @else
                        <p class="text-xs text-[var(--text-secondary)]">
                            <a href="{{ route('login') }}" class="font-semibold text-[var(--brand-gold-text)] hover:underline">Masuk</a> untuk mengajukan pertanyaan ke pengelola kos.
                        </p>
                    @endauth

                    @if ($kos->visibleQuestions->isNotEmpty())
                        <div class="divide-y divide-[var(--border-subtle)]">
                            @foreach ($kos->visibleQuestions as $q)
                                <div class="py-4 first:pt-0 last:pb-0 space-y-2">
                                    <p class="text-sm font-semibold text-[var(--text-primary)]">
                                        Q: {{ $q->question }}
                                    </p>
                                    <p class="text-xs text-[var(--text-secondary)]">
                                        Ditanyakan oleh {{ $q->user?->name ?? 'Pengguna' }} · {{ $q->created_at->format('d M Y') }}
                                    </p>
                                    @if ($q->answer)
                                        <div class="p-3 rounded-[var(--radius-control)] bg-[var(--bg-tint)] border border-[var(--border-subtle)] text-sm text-[var(--text-primary)]">
                                            <span class="font-semibold text-[var(--brand-gold-text)]">Jawaban Pengelola:</span> {{ $q->answer }}
                                        </div>
                                    @else
                                        <p class="text-xs italic text-[var(--text-secondary)]">Menunggu jawaban dari pengelola kos.</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-[var(--text-secondary)]">Belum ada pertanyaan publik untuk kos ini.</p>
                    @endif
                </section>

                <section class="lk-card p-6 sm:p-8">
                    <h2 class="text-2xl font-semibold text-[var(--text-primary)]">Ulasan Penghuni Terverifikasi</h2>
                    @if ($kos->visibleReviews->isNotEmpty())
                        <div class="mt-4 divide-y divide-[var(--border-subtle)]">
                            @foreach ($kos->visibleReviews as $review)
                                <div class="py-4 first:pt-0 last:pb-0">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-sm font-semibold text-[var(--text-primary)]">{{ $review->user?->name ?? 'Penghuni' }}</span>
                                        <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-[var(--accent-warm-bg)] text-[var(--accent-warm-ink)] tabular-nums">
                                            {{ $review->rating }} / 5
                                        </span>
                                    </div>
                                    @if ($review->comment)
                                        <p class="mt-2 text-sm text-[var(--text-secondary)] leading-relaxed">{{ $review->comment }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="mt-3 text-sm text-[var(--text-secondary)]">
                            Belum ada ulasan penghuni untuk kos ini. Ulasan hanya dapat ditulis oleh penyewa setelah check-in terkonfirmasi.
                        </p>
                    @endif
                </section>

                <details class="lk-card p-5">
                    <summary class="cursor-pointer text-xs font-semibold text-[var(--text-secondary)] hover:text-[var(--status-danger)]">
                        Menemukan harga, stok, atau foto yang tidak sesuai di lapangan? Laporkan listing ini ke Admin
                    </summary>
                    <form method="POST" action="{{ route('kos.report', $kos) }}" class="mt-4 pt-4 border-t border-[var(--border-subtle)] space-y-3">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-[var(--text-primary)] mb-1">Alasan Laporan</label>
                                <select name="reason" required class="lk-input text-xs">
                                    <option value="harga_tidak_sesuai">Harga di lapangan berbeda</option>
                                    <option value="stok_tidak_sesuai">Stok kamar tidak sesuai</option>
                                    <option value="foto_tidak_sesuai">Foto tidak menggambarkan kondisi asli</option>
                                    <option value="kontak_tidak_aktif">Nomor WhatsApp / telepon tidak aktif</option>
                                    <option value="lainnya">Alasan lainnya</option>
                                </select>
                            </div>
                            @guest
                                <div>
                                    <label class="block text-xs font-semibold text-[var(--text-primary)] mb-1">Nama Anda (Opsional)</label>
                                    <input type="text" name="reporter_name" placeholder="Nama pelapor" class="lk-input text-xs">
                                </div>
                            @endguest
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[var(--text-primary)] mb-1">Detail Ketidaksesuaian</label>
                            <textarea name="details" rows="2" required minlength="10" placeholder="Jelaskan perbedaan data yang Anda temukan saat survei atau menghubungi pengelola" class="lk-input text-xs"></textarea>
                        </div>
                        <button type="submit" class="lk-btn-secondary text-xs min-h-[38px] px-4">Kirim Laporan ke Admin</button>
                    </form>
                </details>
            </div>

            <aside class="lg:col-span-4 sticky top-24 space-y-5">
                <div
                    x-data='{
                        rooms: @json($roomsPayload),
                        selectedRoomId: {{ $firstAvailableRoom?->id ?? 'null' }},
                        duration: 1,
                        get currentRoom() {
                            return this.rooms.find(r => r.id === Number(this.selectedRoomId)) || null;
                        },
                        get estimatedPrice() {
                            if (!this.currentRoom) return 0;
                            return this.currentRoom.prices[Number(this.duration)] || 0;
                        },
                        get depositAmount() {
                            return this.currentRoom ? Number(this.currentRoom.deposit || 0) : 0;
                        },
                        get totalInitialCost() {
                            return this.estimatedPrice + this.depositAmount;
                        },
                        get perPersonSplit() {
                            if (!this.currentRoom || this.currentRoom.max_occupancy < 2) return null;
                            return Math.round(this.totalInitialCost / this.currentRoom.max_occupancy);
                        },
                        formatRp(val) {
                            return "Rp " + Number(val).toLocaleString("id-ID");
                        }
                    }'
                    class="rounded-[var(--radius-panel)] bg-[var(--bg-surface)] border border-[var(--border-subtle)] p-6 sm:p-7 lk-elevated"
                >
                    <h2 class="text-xl font-semibold text-[var(--text-primary)]">Simulasi Biaya & Pemesanan</h2>
                    <p class="mt-1.5 text-xs text-[var(--text-secondary)]">
                        Pembayaran transfer manual ke rekening Mitra (batas unggah bukti 24 jam setelah pesan).
                    </p>

                    @if ($kos->roomTypes->isNotEmpty())
                        @if (session('error'))
                            <div class="mt-4 p-3 rounded-[var(--radius-control)] bg-[var(--status-danger-bg)] text-[var(--status-danger)] text-xs font-medium">
                                {{ session('error') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="mt-4 p-3 rounded-[var(--radius-control)] bg-[var(--status-danger-bg)] text-[var(--status-danger)] text-xs space-y-1">
                                @foreach ($errors->all() as $error)
                                    <p>{{ $error }}</p>
                                @endforeach
                            </div>
                        @endif

                        <form method="POST" action="{{ route('penyewa.bookings.store') }}" enctype="multipart/form-data" class="mt-5 space-y-4">
                            @csrf
                            <div>
                                <label for="booking-room" class="block text-xs font-semibold text-[var(--text-primary)] mb-1.5">
                                    Pilih Tipe Kamar
                                </label>
                                <select id="booking-room" name="room_type_id" x-model="selectedRoomId" class="lk-input text-sm">
                                    @foreach ($kos->roomTypes as $room)
                                        <option value="{{ $room->id }}" @disabled($room->available_rooms === 0)>
                                            {{ $room->name }} ({{ $room->available_rooms > 0 ? 'Sisa ' . $room->available_rooms : 'Penuh' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label for="booking-date" class="block text-xs font-semibold text-[var(--text-primary)] mb-1.5">
                                        Tanggal Masuk
                                    </label>
                                    <input
                                        id="booking-date"
                                        type="date"
                                        name="check_in_date"
                                        value="{{ old('check_in_date', now()->addDays(1)->toDateString()) }}"
                                        min="{{ now()->toDateString() }}"
                                        required
                                        class="lk-input text-xs"
                                    >
                                </div>
                                <div>
                                    <label for="booking-duration" class="block text-xs font-semibold text-[var(--text-primary)] mb-1.5">
                                        Durasi Sewa
                                    </label>
                                    <select id="booking-duration" name="duration_months" x-model="duration" class="lk-input text-xs">
                                        <option value="1">1 Bulan</option>
                                        <option value="3">3 Bulan</option>
                                        <option value="6">6 Bulan</option>
                                        <option value="12">12 Bulan</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label for="booking-promo" class="block text-xs font-semibold text-[var(--text-primary)] mb-1.5">
                                    Kode Promo / Voucher (Opsional)
                                </label>
                                <input id="booking-promo" type="text" name="promo_code" value="{{ old('promo_code') }}" placeholder="Contoh: MABAUMY26" class="lk-input text-xs uppercase">
                            </div>

                            <div>
                                <label for="booking-ktm" class="block text-xs font-semibold text-[var(--text-primary)] mb-1.5">
                                    Unggah KTM UMY / KTP (Opsional, JPG/PNG/PDF maks 2 MB)
                                </label>
                                <input id="booking-ktm" type="file" name="identity_card" accept=".jpg,.jpeg,.png,.pdf" class="lk-input text-xs">
                            </div>

                            <div>
                                <label for="booking-notes" class="block text-xs font-semibold text-[var(--text-primary)] mb-1.5">
                                    Catatan untuk Pengelola (Opsional)
                                </label>
                                <textarea id="booking-notes" name="notes" rows="2" placeholder="Contoh: Rencana masuk sore hari" class="lk-input text-xs">{{ old('notes') }}</textarea>
                            </div>

                            <div class="p-4 rounded-[var(--radius-control)] bg-[var(--bg-tint)] border border-[var(--border-subtle)] space-y-1.5 text-xs">
                                <div class="flex justify-between">
                                    <span class="text-[var(--text-secondary)]">Biaya sewa</span>
                                    <span class="font-semibold text-[var(--text-primary)] tabular-nums" x-text="formatRp(estimatedPrice)">
                                        Rp {{ number_format($firstAvailableRoom?->calculatePrice(1) ?? 0, 0, ',', '.') }}
                                    </span>
                                </div>
                                <div class="flex justify-between" x-show="depositAmount > 0">
                                    <span class="text-[var(--text-secondary)]">Uang jaminan (deposit)</span>
                                    <span class="font-semibold text-[var(--text-primary)] tabular-nums" x-text="formatRp(depositAmount)"></span>
                                </div>
                                <div class="pt-2 border-t border-[var(--border-subtle)] flex items-baseline justify-between gap-2">
                                    <span class="font-semibold text-[var(--text-primary)]">Estimasi biaya awal</span>
                                    <span class="text-lg font-bold text-[var(--text-primary)] tabular-nums" x-text="formatRp(totalInitialCost)">
                                        Rp {{ number_format(($firstAvailableRoom?->calculatePrice(1) ?? 0) + ($firstAvailableRoom?->deposit_amount ?? 0), 0, ',', '.') }}
                                    </span>
                                </div>
                                <p x-show="perPersonSplit" class="text-[11px] text-[var(--brand-secondary-ink)] font-medium tabular-nums">
                                    Bisa berdua: estimasi <span x-text="formatRp(perPersonSplit)"></span> / orang.
                                </p>
                            </div>

                            @auth
                                @if (auth()->user()->isPenyewa())
                                    <button
                                        type="submit"
                                        :disabled="!currentRoom || currentRoom.available <= 0"
                                        class="lk-btn-primary w-full text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        Ajukan Pemesanan Kamar
                                    </button>
                                @else
                                    <p class="text-xs text-[var(--text-secondary)] p-3 rounded-[var(--radius-control)] bg-[var(--bg-tint)] border border-[var(--border-subtle)]">
                                        Anda sedang masuk sebagai {{ ucfirst(auth()->user()->role) }}. Pemesanan kamar hanya dapat diajukan menggunakan akun Penyewa.
                                    </p>
                                @endif
                            @else
                                <a href="{{ route('login') }}" class="lk-btn-primary w-full text-sm">
                                    Masuk untuk Ajukan Pemesanan
                                </a>
                            @endauth
                        </form>
                    @endif

                    @if ($kos->mitra?->mitraProfile || ! empty($whatsappUrl) || ! empty($displayPhone))
                        <div class="mt-5 pt-5 border-t border-[var(--border-subtle)] text-xs text-[var(--text-secondary)] space-y-2.5">
                            <div>
                                <p class="font-semibold text-[var(--text-primary)]">Kontak Pengelola & Survei</p>
                                <p class="mt-0.5 text-sm font-semibold text-[var(--text-primary)]">{{ $contactName ?? $kos->resolvedContactName() }}</p>
                                @if ($kos->mitra?->mitraProfile)
                                    <p>{{ $kos->mitra->mitraProfile->business_name }} · Bank {{ $kos->mitra->mitraProfile->bank_name }}</p>
                                @endif
                            </div>

                            <div class="space-y-1 tabular-nums">
                                @if (! empty($displayWhatsapp))
                                    <p>WhatsApp: <strong class="text-[var(--text-primary)]">{{ $displayWhatsapp }}</strong></p>
                                @endif
                                @if (! empty($displayPhone) && $displayPhone !== ($displayWhatsapp ?? null))
                                    <p>
                                        Telepon:
                                        <a href="{{ $phoneHref }}" class="font-semibold text-[var(--brand-gold-text)] hover:underline">{{ $displayPhone }}</a>
                                    </p>
                                @elseif (! empty($phoneHref) && ! empty($displayPhone))
                                    <p>
                                        Telepon langsung:
                                        <a href="{{ $phoneHref }}" class="font-semibold text-[var(--brand-gold-text)] hover:underline">{{ $displayPhone }}</a>
                                    </p>
                                @endif
                                @if ($kos->contact_hours)
                                    <p>Jam layanan: <span class="text-[var(--text-primary)]">{{ $kos->contact_hours }}</span></p>
                                @endif
                            </div>

                            @if (! empty($whatsappUrl))
                                <a
                                    href="{{ $whatsappUrl }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="lk-btn-secondary w-full text-xs min-h-[42px]"
                                >
                                    Chat Pengelola via WhatsApp
                                </a>

                                @if (! empty($whatsappMessagePreview))
                                    <details class="rounded-[var(--radius-control)] bg-[var(--bg-tint)] border border-[var(--border-subtle)] p-3">
                                        <summary class="cursor-pointer font-semibold text-[var(--text-primary)]">Lihat pesan otomatis WhatsApp</summary>
                                        <p class="mt-2 leading-relaxed text-[var(--text-secondary)] italic">
                                            "{{ $whatsappMessagePreview }}"
                                        </p>
                                    </details>
                                @endif
                            @endif
                        </div>
                    @endif
                </div>

                <div class="rounded-[var(--radius-panel)] bg-[var(--bg-surface)] border border-[var(--border-subtle)] p-6 space-y-4">
                    <div>
                        <h2 class="text-base font-semibold text-[var(--text-primary)]">Ajukan Jadwal Survei Lokasi</h2>
                        <p class="mt-1 text-xs text-[var(--text-secondary)]">Ingin melihat kamar langsung sebelum membayar? Pilih rencana kunjungan Anda.</p>
                    </div>
                    @auth
                        @if (auth()->user()->isPenyewa())
                            <form method="POST" action="{{ route('penyewa.surveys.store', $kos) }}" class="space-y-3">
                                @csrf
                                <div class="grid grid-cols-2 gap-2.5">
                                    <div>
                                        <label for="survey-date" class="block text-xs font-semibold text-[var(--text-primary)] mb-1">Tanggal</label>
                                        <input id="survey-date" type="date" name="preferred_date" min="{{ now()->toDateString() }}" value="{{ now()->addDay()->toDateString() }}" required class="lk-input text-xs">
                                    </div>
                                    <div>
                                        <label for="survey-time" class="block text-xs font-semibold text-[var(--text-primary)] mb-1">Jam Kunjungan</label>
                                        <select id="survey-time" name="preferred_time" required class="lk-input text-xs">
                                            <option value="09.00 - 11.00 WIB">09.00 - 11.00 WIB</option>
                                            <option value="13.00 - 15.00 WIB">13.00 - 15.00 WIB</option>
                                            <option value="16.00 - 18.00 WIB">16.00 - 18.00 WIB</option>
                                            <option value="19.00 - 20.30 WIB">19.00 - 20.30 WIB</option>
                                        </select>
                                    </div>
                                </div>
                                @if ($kos->roomTypes->isNotEmpty())
                                    <div>
                                        <label for="survey-room" class="block text-xs font-semibold text-[var(--text-primary)] mb-1">Tipe Kamar Diminati (Opsional)</label>
                                        <select id="survey-room" name="room_type_id" class="lk-input text-xs">
                                            <option value="">Lihat semua kamar tersedia</option>
                                            @foreach ($kos->roomTypes as $room)
                                                <option value="{{ $room->id }}">{{ $room->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                                <div>
                                    <label for="survey-notes" class="block text-xs font-semibold text-[var(--text-primary)] mb-1">Catatan (Opsional)</label>
                                    <input id="survey-notes" type="text" name="notes" placeholder="Misal: Datang bersama orang tua" class="lk-input text-xs">
                                </div>
                                <button type="submit" class="lk-btn-espresso w-full text-xs min-h-[42px]">Ajukan Jadwal Survei</button>
                            </form>
                        @else
                            <p class="text-xs text-[var(--text-secondary)]">Masuk dengan akun Penyewa untuk menjadwalkan survei lokasi.</p>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="lk-btn-secondary w-full text-xs min-h-[42px]">Masuk untuk Ajukan Survei</a>
                    @endauth
                </div>
            </aside>
        </div>
    </div>

    @if ($kos->latitude !== null && $kos->longitude !== null)
        @push('scripts')
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    var el = document.getElementById('kos-map');
                    var statusEl = document.getElementById('kos-map-status');
                    if (!el || typeof L === 'undefined') {
                        if (statusEl) {
                            statusEl.textContent = 'Gagal memuat peta lokasi. Periksa koneksi internet lalu muat ulang halaman.';
                        }
                        return;
                    }

                    var kosLat = parseFloat(el.dataset.lat);
                    var kosLng = parseFloat(el.dataset.lng);
                    var umyLat = parseFloat(el.dataset.umyLat);
                    var umyLng = parseFloat(el.dataset.umyLng);

                    var map = L.map(el).setView([kosLat, kosLng], 15);
                    var tiles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap contributors'
                    });

                    tiles.on('load', function () {
                        if (statusEl) statusEl.textContent = 'Menampilkan titik kos dan titik referensi Kampus Terpadu UMY.';
                    });

                    tiles.on('tileerror', function () {
                        if (statusEl) statusEl.textContent = 'Sebagian peta gagal dimuat. Coba muat ulang halaman.';
                    });

                    tiles.addTo(map);
                    L.marker([kosLat, kosLng]).addTo(map).bindPopup(el.dataset.name).openPopup();
                    L.marker([umyLat, umyLng]).addTo(map).bindPopup('Kampus Terpadu UMY (Tamantirto)');

                    var bounds = L.latLngBounds([[kosLat, kosLng], [umyLat, umyLng]]);
                    map.fitBounds(bounds, { padding: [36, 36] });
                });
            </script>
        @endpush
    @endif
</x-public-layout>
