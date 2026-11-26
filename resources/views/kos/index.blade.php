<x-public-layout title="Katalog Kos Sekitar UMY">
    @push('head')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    @endpush

    <div
        x-data='{
            filterOpen: false,
            showMap: false,
            compareIds: [],
            toggleCompare(id) {
                if (this.compareIds.includes(id)) {
                    this.compareIds = this.compareIds.filter(i => i !== id);
                } else if (this.compareIds.length < 3) {
                    this.compareIds.push(id);
                }
            },
            openMap() {
                this.showMap = !this.showMap;
                if (this.showMap) {
                    this.$nextTick(() => window.initLokaKosCatalogMap && window.initLokaKosCatalogMap());
                }
            }
        }'
        @keydown.escape.window="filterOpen = false"
        class="max-w-[1320px] mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12"
    >
        <div class="rounded-[var(--radius-panel)] bg-[var(--bg-tint)] border border-[var(--border-subtle)] px-6 sm:px-10 py-8 sm:py-10 mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-[var(--brand-gold-text)]">Direktori Kawasan UMY</p>
                <h1 class="mt-1.5 font-display text-3xl sm:text-4xl font-semibold text-[var(--text-primary)]">
                    Katalog Kos Terverifikasi
                </h1>
                <p class="mt-2 text-sm text-[var(--text-secondary)] tabular-nums">
                    Menampilkan {{ $boardingHouses->total() }} kos tayang sesuai filter yang dipilih.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    @click="openMap()"
                    class="lk-btn-espresso text-xs sm:text-sm min-h-[42px]"
                >
                    <span x-text="showMap ? 'Sembunyikan Peta' : 'Lihat di Peta UMY'">Lihat di Peta UMY</span>
                </button>
                <a href="{{ route('kos.compare') }}" class="lk-btn-secondary text-xs sm:text-sm min-h-[42px]">
                    Bandingkan Kos
                </a>
                <button type="button" @click="filterOpen = true" class="lg:hidden lk-btn-secondary text-xs sm:text-sm min-h-[42px]">
                    Filter Kos
                </button>
            </div>
        </div>

        <div x-cloak x-show="showMap" x-transition class="mb-8 lk-card p-5 space-y-3">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 class="text-base font-semibold text-[var(--text-primary)]">Peta Sebaran Kos Sekitar Kampus Terpadu UMY</h2>
                    <p class="text-xs text-[var(--text-secondary)]">Klik penanda untuk melihat tarif mulai, sisa kamar, dan tautan detail kos.</p>
                </div>
                <span class="text-xs font-semibold text-[var(--text-secondary)] tabular-nums">{{ $mapPins->count() }} titik kos terpetakan</span>
            </div>
            <div
                id="catalog-umy-map"
                data-umy-lat="{{ \App\Models\BoardingHouse::UMY_LATITUDE }}"
                data-umy-lng="{{ \App\Models\BoardingHouse::UMY_LONGITUDE }}"
                class="h-80 sm:h-96 w-full rounded-[var(--radius-card)] border border-[var(--border-subtle)] bg-[var(--bg-tint)] z-0"
            ></div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <aside class="hidden lg:block lg:col-span-3 sticky top-24">
                <form method="GET" action="{{ route('kos.index') }}" class="lk-card p-6 space-y-5">
                    @if ($filters['q'] !== '')
                        <input type="hidden" name="q" value="{{ $filters['q'] }}">
                    @endif

                    <div class="flex items-center justify-between pb-3 border-b border-[var(--border-subtle)]">
                        <h2 class="font-display text-lg font-semibold text-[var(--text-primary)]">Filter</h2>
                        <a href="{{ route('kos.index') }}" class="text-xs font-semibold text-[var(--brand-gold-text)] hover:underline">
                            Reset
                        </a>
                    </div>

                    <div>
                        <span class="block text-xs font-semibold text-[var(--text-primary)] mb-2">Tipe Penghuni</span>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach (['' => 'Semua', 'putra' => 'Putra', 'putri' => 'Putri', 'campur' => 'Campur'] as $value => $label)
                                <label class="cursor-pointer">
                                    <input type="radio" name="gender" value="{{ $value }}" @checked($filters['gender'] === $value) class="sr-only peer">
                                    <span class="flex items-center justify-center min-h-[40px] px-2.5 rounded-full border border-[var(--border-control)] bg-[var(--bg-surface)] text-xs font-medium text-[var(--text-primary)] peer-checked:bg-[var(--brand-gold)] peer-checked:text-[var(--brand-gold-ink)] peer-checked:border-[var(--brand-gold)] transition-colors">
                                        {{ $label }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="flex items-center gap-2.5 cursor-pointer min-h-[38px]">
                            <input type="checkbox" name="syariah" value="1" @checked($filters['syariah']) class="w-4 h-4 rounded border-[var(--border-control)] text-[var(--brand-gold-text)]">
                            <span class="text-sm font-medium text-[var(--text-primary)]">Hanya kos Syariah</span>
                        </label>
                        <label class="flex items-center gap-2.5 cursor-pointer min-h-[38px]">
                            <input type="checkbox" name="verified" value="1" @checked($filters['verified']) class="w-4 h-4 rounded border-[var(--border-control)] text-[var(--brand-gold-text)]">
                            <span class="text-sm font-medium text-[var(--text-primary)]">Terverifikasi Lapangan</span>
                        </label>
                        <label class="flex items-center gap-2.5 cursor-pointer min-h-[38px]">
                            <input type="checkbox" name="electricity_included" value="1" @checked($filters['electricity_included']) class="w-4 h-4 rounded border-[var(--border-control)] text-[var(--brand-gold-text)]">
                            <span class="text-sm font-medium text-[var(--text-primary)]">Termasuk listrik</span>
                        </label>
                    </div>

                    <div>
                        <span class="block text-xs font-semibold text-[var(--text-primary)] mb-2">Rentang Harga / Bulan (Rp)</span>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="number" name="min_price" value="{{ $filters['min_price'] }}" placeholder="Min" min="0" step="50000" aria-label="Harga minimal per bulan" class="lk-input text-sm tabular-nums">
                            <input type="number" name="max_price" value="{{ $filters['max_price'] }}" placeholder="Max" min="0" step="50000" aria-label="Harga maksimal per bulan" class="lk-input text-sm tabular-nums">
                        </div>
                    </div>

                    <div>
                        <label for="filter-area" class="block text-xs font-semibold text-[var(--text-primary)] mb-1.5">Area Sekitar UMY</label>
                        <select id="filter-area" name="area" class="lk-input text-sm">
                            <option value="">Semua area</option>
                            @foreach ($areas as $area)
                                <option value="{{ $area->slug }}" @selected($filters['area'] === $area->slug)>{{ $area->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="filter-sort" class="block text-xs font-semibold text-[var(--text-primary)] mb-1.5">Urutkan</label>
                        <select id="filter-sort" name="sort" class="lk-input text-sm">
                            <option value="nearest" @selected($filters['sort'] === 'nearest')>Terdekat ke UMY</option>
                            <option value="cheapest" @selected($filters['sort'] === 'cheapest')>Harga terendah</option>
                            <option value="latest" @selected($filters['sort'] === 'latest')>Terbaru tayang</option>
                        </select>
                    </div>

                    @if ($facilities->isNotEmpty())
                        <div>
                            <span class="block text-xs font-semibold text-[var(--text-primary)] mb-2">Fasilitas</span>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($facilities as $facility)
                                    <label class="cursor-pointer">
                                        <input type="checkbox" name="facilities[]" value="{{ $facility->id }}" @checked(in_array($facility->id, $selectedFacilities, true)) class="sr-only peer">
                                        <span class="inline-flex items-center min-h-[36px] px-3 py-1 rounded-full border border-[var(--border-control)] bg-[var(--bg-surface)] text-xs font-medium text-[var(--text-primary)] peer-checked:bg-[var(--brand-gold)] peer-checked:text-[var(--brand-gold-ink)] peer-checked:border-[var(--brand-gold)] transition-colors">
                                            {{ $facility->name }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <button type="submit" class="lk-btn-primary w-full text-sm">
                        Terapkan Filter
                    </button>
                </form>
            </aside>

            <div class="lg:col-span-9 min-w-0">
                <form method="GET" action="{{ route('kos.index') }}" class="mb-8 flex flex-col sm:flex-row gap-3">
                    @foreach (['area', 'gender', 'min_price', 'max_price', 'sort'] as $keep)
                        @if ($filters[$keep] !== '')
                            <input type="hidden" name="{{ $keep }}" value="{{ $filters[$keep] }}">
                        @endif
                    @endforeach
                    @foreach (['syariah', 'verified', 'electricity_included'] as $flag)
                        @if ($filters[$flag])
                            <input type="hidden" name="{{ $flag }}" value="1">
                        @endif
                    @endforeach
                    @foreach ($selectedFacilities as $facilityId)
                        <input type="hidden" name="facilities[]" value="{{ $facilityId }}">
                    @endforeach

                    <div class="flex-1">
                        <label for="catalog-search" class="sr-only">Cari nama kos, jalan, atau area</label>
                        <input
                            id="catalog-search"
                            type="search"
                            name="q"
                            value="{{ $filters['q'] }}"
                            placeholder="Cari nama kos, jalan, atau dusun (contoh: Ngebel, Melati)"
                            class="lk-input"
                        >
                    </div>
                    <button type="submit" class="lk-btn-primary shrink-0">
                        Cari Kos
                    </button>
                </form>

                @if ($boardingHouses->isNotEmpty())
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-7 gap-y-10">
                        @foreach ($boardingHouses as $kos)
                            <div class="space-y-2">
                                <x-lokakos.kos-card :kos="$kos" />
                                <div class="flex items-center justify-between gap-2 text-xs px-1">
                                    <label class="inline-flex items-center gap-2 cursor-pointer text-[var(--text-secondary)] hover:text-[var(--text-primary)]">
                                        <input
                                            type="checkbox"
                                            :checked="compareIds.includes({{ $kos->id }})"
                                            :disabled="!compareIds.includes({{ $kos->id }}) && compareIds.length >= 3"
                                            @change="toggleCompare({{ $kos->id }})"
                                            class="w-4 h-4 rounded border-[var(--border-control)]"
                                        >
                                        <span>Bandingkan kos ini</span>
                                    </label>
                                    @if ($kos->is_field_verified)
                                        <span class="px-2 py-0.5 rounded-full bg-[var(--brand-secondary-bg)] text-[var(--brand-secondary-ink)] font-semibold text-[11px]">
                                            Terverifikasi Lapangan
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if ($boardingHouses->hasPages())
                        <div class="mt-10">
                            {{ $boardingHouses->links() }}
                        </div>
                    @endif
                @else
                    <x-lokakos.empty-state
                        title="Tidak ada kos yang cocok dengan filter ini"
                        description="Coba ubah rentang harga, ganti area sekitar UMY, atau hapus sebagian fasilitas agar kos yang tersedia muncul kembali."
                        action-label="Reset Filter"
                        :action-href="route('kos.index')"
                    />
                @endif
            </div>
        </div>

        <div
            x-cloak
            x-show="compareIds.length > 0"
            x-transition
            class="fixed bottom-4 left-1/2 -translate-x-1/2 z-40 rounded-full bg-[var(--bg-espresso)] text-[var(--espresso-ink)] px-5 py-3 shadow-lg border border-white/15 flex items-center gap-4 text-xs sm:text-sm"
        >
            <span class="tabular-nums font-medium"><strong x-text="compareIds.length"></strong> / 3 kos dipilih untuk dibandingkan</span>
            <a
                :href="'{{ route('kos.compare') }}?ids=' + compareIds.join(',')"
                class="lk-btn-primary text-xs min-h-[36px] px-4"
            >
                Bandingkan Sekarang
            </a>
            <button type="button" @click="compareIds = []" class="text-xs text-[var(--espresso-muted)] hover:text-[var(--espresso-ink)]">
                Bersihkan
            </button>
        </div>

        <div
            x-cloak
            x-show="filterOpen"
            x-transition.opacity.duration.180ms
            class="fixed inset-0 z-50 lg:hidden flex justify-end bg-black/50"
            role="dialog"
            aria-modal="true"
            aria-label="Filter Katalog Kos"
        >
            <div class="fixed inset-0" @click="filterOpen = false"></div>
            <div class="relative z-10 w-full max-w-sm bg-[var(--bg-surface)] h-full overflow-y-auto p-6 border-l border-[var(--border-subtle)]">
                <div class="flex items-center justify-between pb-4 border-b border-[var(--border-subtle)]">
                    <h2 class="font-display text-lg font-semibold text-[var(--text-primary)]">Filter Kos</h2>
                    <button type="button" @click="filterOpen = false" class="lk-btn-secondary text-xs min-h-[44px] px-4">
                        Tutup
                    </button>
                </div>

                <form method="GET" action="{{ route('kos.index') }}" class="mt-5 space-y-5">
                    @if ($filters['q'] !== '')
                        <input type="hidden" name="q" value="{{ $filters['q'] }}">
                    @endif

                    <div>
                        <span class="block text-xs font-semibold text-[var(--text-primary)] mb-2">Tipe Penghuni</span>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach (['' => 'Semua', 'putra' => 'Putra', 'putri' => 'Putri', 'campur' => 'Campur'] as $value => $label)
                                <label class="cursor-pointer">
                                    <input type="radio" name="gender" value="{{ $value }}" @checked($filters['gender'] === $value) class="sr-only peer">
                                    <span class="flex items-center justify-center min-h-[44px] px-2.5 rounded-full border border-[var(--border-control)] bg-[var(--bg-surface)] text-xs font-medium text-[var(--text-primary)] peer-checked:bg-[var(--brand-gold)] peer-checked:text-[var(--brand-gold-ink)] peer-checked:border-[var(--brand-gold)]">
                                        {{ $label }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="flex items-center gap-2.5 cursor-pointer min-h-[40px]">
                            <input type="checkbox" name="syariah" value="1" @checked($filters['syariah']) class="w-4 h-4 rounded border-[var(--border-control)]">
                            <span class="text-sm font-medium text-[var(--text-primary)]">Hanya kos Syariah</span>
                        </label>
                        <label class="flex items-center gap-2.5 cursor-pointer min-h-[40px]">
                            <input type="checkbox" name="verified" value="1" @checked($filters['verified']) class="w-4 h-4 rounded border-[var(--border-control)]">
                            <span class="text-sm font-medium text-[var(--text-primary)]">Terverifikasi Lapangan</span>
                        </label>
                        <label class="flex items-center gap-2.5 cursor-pointer min-h-[40px]">
                            <input type="checkbox" name="electricity_included" value="1" @checked($filters['electricity_included']) class="w-4 h-4 rounded border-[var(--border-control)]">
                            <span class="text-sm font-medium text-[var(--text-primary)]">Termasuk listrik</span>
                        </label>
                    </div>

                    <div>
                        <span class="block text-xs font-semibold text-[var(--text-primary)] mb-2">Harga / Bulan (Rp)</span>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="number" name="min_price" value="{{ $filters['min_price'] }}" placeholder="Min" min="0" step="50000" class="lk-input text-sm tabular-nums">
                            <input type="number" name="max_price" value="{{ $filters['max_price'] }}" placeholder="Max" min="0" step="50000" class="lk-input text-sm tabular-nums">
                        </div>
                    </div>

                    <div>
                        <label for="mobile-filter-area" class="block text-xs font-semibold text-[var(--text-primary)] mb-1.5">Area Sekitar UMY</label>
                        <select id="mobile-filter-area" name="area" class="lk-input text-sm">
                            <option value="">Semua area</option>
                            @foreach ($areas as $area)
                                <option value="{{ $area->slug }}" @selected($filters['area'] === $area->slug)>{{ $area->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="mobile-filter-sort" class="block text-xs font-semibold text-[var(--text-primary)] mb-1.5">Urutkan</label>
                        <select id="mobile-filter-sort" name="sort" class="lk-input text-sm">
                            <option value="nearest" @selected($filters['sort'] === 'nearest')>Terdekat ke UMY</option>
                            <option value="cheapest" @selected($filters['sort'] === 'cheapest')>Harga terendah</option>
                            <option value="latest" @selected($filters['sort'] === 'latest')>Terbaru tayang</option>
                        </select>
                    </div>

                    @if ($facilities->isNotEmpty())
                        <div>
                            <span class="block text-xs font-semibold text-[var(--text-primary)] mb-2">Fasilitas</span>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($facilities as $facility)
                                    <label class="cursor-pointer">
                                        <input type="checkbox" name="facilities[]" value="{{ $facility->id }}" @checked(in_array($facility->id, $selectedFacilities, true)) class="sr-only peer">
                                        <span class="inline-flex items-center min-h-[40px] px-3 py-1 rounded-full border border-[var(--border-control)] bg-[var(--bg-surface)] text-xs font-medium text-[var(--text-primary)] peer-checked:bg-[var(--brand-gold)] peer-checked:text-[var(--brand-gold-ink)] peer-checked:border-[var(--brand-gold)]">
                                            {{ $facility->name }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="pt-3 flex gap-2">
                        <a href="{{ route('kos.index') }}" class="lk-btn-secondary flex-1 text-sm">Reset</a>
                        <button type="submit" class="lk-btn-primary flex-1 text-sm">Terapkan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
        <script>
            (function () {
                var catalogMapInstance = null;
                var pins = @json($mapPins);

                window.initLokaKosCatalogMap = function () {
                    var el = document.getElementById('catalog-umy-map');
                    if (!el || typeof L === 'undefined') return;

                    if (catalogMapInstance) {
                        catalogMapInstance.invalidateSize();
                        return;
                    }

                    var umyLat = parseFloat(el.dataset.umyLat);
                    var umyLng = parseFloat(el.dataset.umyLng);

                    catalogMapInstance = L.map(el).setView([umyLat, umyLng], 14);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(catalogMapInstance);

                    var bounds = [[umyLat, umyLng]];
                    L.circleMarker([umyLat, umyLng], {
                        radius: 9,
                        color: '#3D312C',
                        fillColor: '#CAA05C',
                        fillOpacity: 1
                    }).addTo(catalogMapInstance).bindPopup('<strong>Kampus Terpadu UMY</strong><br>Tamantirto, Kasihan, Bantul');

                    pins.forEach(function (pin) {
                        bounds.push([pin.lat, pin.lng]);
                        var priceText = pin.price > 0 ? 'Rp ' + Number(pin.price).toLocaleString('id-ID') + '/bln' : 'Cek tarif';
                        var popup = '<div style="min-width:170px;font-family:sans-serif;font-size:12px;">'
                            + '<strong>' + pin.name + '</strong><br>'
                            + '<span>' + pin.area + ' · Kos ' + pin.gender + '</span><br>'
                            + '<strong style="color:#7A5820;">' + priceText + '</strong> · Sisa ' + pin.available + ' kamar<br>'
                            + '<a href="' + pin.url + '" style="display:inline-block;margin-top:6px;font-weight:600;color:#3D312C;text-decoration:underline;">Lihat Detail Kos</a>'
                            + '</div>';
                        L.marker([pin.lat, pin.lng]).addTo(catalogMapInstance).bindPopup(popup);
                    });

                    if (bounds.length > 1) {
                        catalogMapInstance.fitBounds(bounds, { padding: [32, 32] });
                    }
                };
            })();
        </script>
    @endpush
</x-public-layout>
