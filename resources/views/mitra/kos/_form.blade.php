@php
    $editing = isset($kos);
    $selectedFacilityIds = old('facilities', $editing ? $kos->facilities->pluck('id')->all() : []);
    $initialLat = old('latitude', $editing ? $kos->latitude : \App\Models\BoardingHouse::UMY_LATITUDE);
    $initialLng = old('longitude', $editing ? $kos->longitude : \App\Models\BoardingHouse::UMY_LONGITUDE);
@endphp

<div class="space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="name" value="Nama Properti Kos" />
            <x-text-input id="name" name="name" type="text" :value="old('name', $editing ? $kos->name : '')" required placeholder="Contoh: Kos Putri Melati Ngebel" />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="area_id" value="Area Sekitar UMY" />
            <select id="area_id" name="area_id" required class="lk-input text-sm">
                <option value="">Pilih area...</option>
                @foreach ($areas as $area)
                    <option value="{{ $area->id }}" @selected((string) old('area_id', $editing ? $kos->area_id : '') === (string) $area->id)>
                        {{ $area->name }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('area_id')" />
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center">
        <div>
            <x-input-label for="gender_type" value="Tipe Penghuni" />
            <select id="gender_type" name="gender_type" required class="lk-input text-sm">
                <option value="putra" @selected(old('gender_type', $editing ? $kos->gender_type : '') === 'putra')>Kos Putra</option>
                <option value="putri" @selected(old('gender_type', $editing ? $kos->gender_type : '') === 'putri')>Kos Putri</option>
                <option value="campur" @selected(old('gender_type', $editing ? $kos->gender_type : '') === 'campur')>Kos Campur</option>
            </select>
            <x-input-error :messages="$errors->get('gender_type')" />
        </div>

        <div class="pt-5">
            <label class="inline-flex items-center gap-2.5 cursor-pointer min-h-[44px]">
                <input type="checkbox" name="is_syariah" value="1" @checked(old('is_syariah', $editing ? $kos->is_syariah : false)) class="w-4 h-4 rounded border-[var(--border-control)] text-[var(--brand-gold-text)]">
                <span class="text-sm font-medium text-[var(--text-primary)]">Terapkan kategori Kos Syariah / Muslim</span>
            </label>
        </div>
    </div>

    <div>
        <x-input-label for="address" value="Alamat Lengkap" />
        <textarea id="address" name="address" rows="2" required class="lk-input text-sm" placeholder="Jalan, RT/RW, Dusun, Kalurahan, Kecamatan">{{ old('address', $editing ? $kos->address : '') }}</textarea>
        <x-input-error :messages="$errors->get('address')" />
    </div>

    <div>
        <x-input-label for="description" value="Deskripsi Lingkungan Kos" />
        <textarea id="description" name="description" rows="3" class="lk-input text-sm" placeholder="Jelaskan akses menuju kampus UMY, suasana sekitar, dan keunggulan kos">{{ old('description', $editing ? $kos->description : '') }}</textarea>
        <x-input-error :messages="$errors->get('description')" />
    </div>

    <div>
        <x-input-label for="nearby_landmarks" value="Penanda Lokasi Sekitar UMY (pisahkan per baris, opsional)" />
        <textarea id="nearby_landmarks" name="nearby_landmarks" rows="2" class="lk-input text-sm" placeholder="Contoh:&#10;350 m ke Gerbang Barat UMY&#10;150 m ke Masjid & Minimarket&#10;100 m ke Laundry & Warung Makan">{{ old('nearby_landmarks', $editing ? $kos->nearby_landmarks : '') }}</textarea>
        <x-input-error :messages="$errors->get('nearby_landmarks')" />
    </div>

    @if ($editing)
        @php $score = $kos->completenessScore(); @endphp
        <div class="p-4 rounded-[var(--radius-control)] bg-[var(--bg-tint)] border border-[var(--border-subtle)] space-y-2">
            <div class="flex items-center justify-between text-xs">
                <span class="font-semibold text-[var(--text-primary)]">Skor Kelengkapan Profil Kos</span>
                <span class="font-bold text-[var(--brand-gold-text)] tabular-nums">{{ $score['percent'] }}% ({{ $score['completed'] }}/{{ $score['total'] }})</span>
            </div>
            <div class="w-full h-2 rounded-full bg-[var(--border-subtle)] overflow-hidden">
                <div class="h-full bg-[var(--brand-gold)] rounded-full" style="width: {{ $score['percent'] }}%;"></div>
            </div>
            @if (! empty($score['missing']))
                <p class="text-[11px] text-[var(--text-secondary)]">Belum lengkap: {{ implode(', ', $score['missing']) }}.</p>
            @else
                <p class="text-[11px] text-[var(--brand-secondary-ink)] font-medium">Profil kos sudah lengkap 100%.</p>
            @endif
        </div>
    @endif

    <div>
        <x-input-label for="rules" value="Peraturan Penghuni (pisahkan per baris)" />
        <textarea id="rules" name="rules" rows="3" class="lk-input text-sm" placeholder="Contoh: Jam malam pukul 22.00 WIB">{{ old('rules', $editing ? $kos->rules : '') }}</textarea>
        <x-input-error :messages="$errors->get('rules')" />
    </div>

    <div class="pt-4 border-t border-[var(--border-subtle)] space-y-4">
        <div>
            <h2 class="text-sm font-semibold text-[var(--text-primary)]">Kontak Pengelola & Pesan WhatsApp Otomatis</h2>
            <p class="mt-0.5 text-xs text-[var(--text-secondary)]">
                Tambahkan nomor WhatsApp atau telepon khusus untuk kos ini. Jika dikosongkan, sistem otomatis menggunakan nomor telepon akun Mitra Anda ({{ auth()->user()->phone ?: 'belum diisi' }}).
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="contact_name" value="Nama Pengelola / Penjaga Kos (Opsional)" />
                <x-text-input id="contact_name" name="contact_name" type="text" :value="old('contact_name', $editing ? $kos->contact_name : '')" placeholder="Contoh: Ibu Rahma (Pengelola Melati)" />
                <x-input-error :messages="$errors->get('contact_name')" />
            </div>

            <div>
                <x-input-label for="contact_hours" value="Jam Respon & Jadwal Survei (Opsional)" />
                <x-text-input id="contact_hours" name="contact_hours" type="text" :value="old('contact_hours', $editing ? $kos->contact_hours : '')" placeholder="Contoh: Setiap hari 08.00 - 21.00 WIB" />
                <x-input-error :messages="$errors->get('contact_hours')" />
            </div>

            <div>
                <x-input-label for="contact_whatsapp" value="Nomor WhatsApp Khusus Kos" />
                <x-text-input id="contact_whatsapp" name="contact_whatsapp" type="text" :value="old('contact_whatsapp', $editing ? $kos->contact_whatsapp : auth()->user()->phone)" placeholder="Contoh: 0812-2701-1101" class="tabular-nums" />
                <x-input-error :messages="$errors->get('contact_whatsapp')" />
            </div>

            <div>
                <x-input-label for="contact_phone" value="Nomor Telepon / SMS Alternatif (Opsional)" />
                <x-text-input id="contact_phone" name="contact_phone" type="text" :value="old('contact_phone', $editing ? $kos->contact_phone : '')" placeholder="Contoh: 0274-4342101 atau nomor penjaga" class="tabular-nums" />
                <x-input-error :messages="$errors->get('contact_phone')" />
            </div>
        </div>

        <div>
            <x-input-label for="whatsapp_message_template" value="Pesan Otomatis WhatsApp Kustom (Opsional)" />
            <textarea
                id="whatsapp_message_template"
                name="whatsapp_message_template"
                rows="3"
                class="lk-input text-sm"
                placeholder="Kosongkan agar LokaKos otomatis membuatkan pesan sapaan yang unik dan menarik sesuai nama pengelola, area, dan tipe kamar."
            >{{ old('whatsapp_message_template', $editing ? $kos->whatsapp_message_template : '') }}</textarea>
            <p class="mt-1 text-xs text-[var(--text-secondary)]">
                Anda dapat memakai penanda otomatis: <code>{pengelola}</code>, <code>{nama_kos}</code>, <code>{area}</code>, <code>{jarak}</code>, <code>{tipe_kamar}</code>, <code>{harga}</code>.
            </p>
            <x-input-error :messages="$errors->get('whatsapp_message_template')" />
        </div>

        @if ($editing)
            <div class="p-4 rounded-[var(--radius-control)] bg-[var(--bg-tint)] border border-[var(--border-subtle)] space-y-1.5">
                <p class="text-xs font-semibold text-[var(--text-primary)]">Pratinjau Pesan WhatsApp Otomatis Saat Ini</p>
                <p class="text-xs text-[var(--text-secondary)] leading-relaxed italic">
                    "{{ $kos->buildWhatsappMessage() }}"
                </p>
            </div>
        @endif
    </div>

    <div class="pt-4 border-t border-[var(--border-subtle)] space-y-4">
        <div class="flex flex-wrap items-start justify-between gap-2">
            <div>
                <h2 class="text-sm font-semibold text-[var(--text-primary)]">Lokasi Peta & Impor Akurat dari Google Maps</h2>
                <p class="mt-0.5 text-xs text-[var(--text-secondary)]">
                    Tempel link Google Maps (termasuk link bagikan <code>maps.app.goo.gl</code>), koordinat, atau cari dari alamat untuk menempatkan pin secara presisi.
                </p>
            </div>
        </div>

        <div class="p-4 rounded-[var(--radius-control)] bg-[var(--bg-tint)] border border-[var(--border-subtle)] space-y-3">
            <div>
                <x-input-label for="google_maps_url" value="Link Google Maps / Koordinat / Alamat" />
                <div class="mt-1 flex flex-col sm:flex-row gap-2">
                    <x-text-input
                        id="google_maps_url"
                        name="google_maps_url"
                        type="text"
                        :value="old('google_maps_url', $editing ? $kos->google_maps_url : '')"
                        placeholder="Tempel link Google Maps (maps.app.goo.gl/... atau google.com/maps/...) atau -7.8138, 110.3198"
                        class="text-sm flex-1"
                    />
                    <button
                        type="button"
                        id="btn-resolve-gmaps"
                        class="lk-btn-primary text-xs min-h-[42px] px-4 shrink-0"
                    >
                        Terapkan ke Peta
                    </button>
                </div>
                <x-input-error :messages="$errors->get('google_maps_url')" />
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button type="button" id="btn-geocode-address" class="lk-btn-secondary text-xs min-h-[38px] px-3.5">
                    Cari dari Alamat Lengkap
                </button>
                <button type="button" id="btn-use-gps" class="lk-btn-secondary text-xs min-h-[38px] px-3.5">
                    Gunakan GPS Perangkat
                </button>
                <span id="gmaps-resolve-status" class="text-xs text-[var(--text-secondary)]"></span>
            </div>
        </div>

        <div
            id="mitra-map-picker"
            data-lat="{{ $initialLat }}"
            data-lng="{{ $initialLng }}"
            data-umy-lat="{{ \App\Models\BoardingHouse::UMY_LATITUDE }}"
            data-umy-lng="{{ \App\Models\BoardingHouse::UMY_LONGITUDE }}"
            data-resolve-url="{{ route('mitra.maps.resolve') }}"
            class="h-80 w-full rounded-[var(--radius-card)] border border-[var(--border-subtle)] bg-[var(--bg-tint)] z-0"
        ></div>

        <div id="map-distance-preview" class="text-xs text-[var(--text-secondary)] tabular-nums"></div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="latitude" value="Latitude" />
                <x-text-input id="latitude" name="latitude" type="text" :value="old('latitude', $editing ? $kos->latitude : '')" placeholder="-7.8138000" class="tabular-nums text-sm" />
                <x-input-error :messages="$errors->get('latitude')" />
            </div>
            <div>
                <x-input-label for="longitude" value="Longitude" />
                <x-text-input id="longitude" name="longitude" type="text" :value="old('longitude', $editing ? $kos->longitude : '')" placeholder="110.3198000" class="tabular-nums text-sm" />
                <x-input-error :messages="$errors->get('longitude')" />
            </div>
        </div>
    </div>

    @if ($facilities->isNotEmpty())
        <div class="pt-4 border-t border-[var(--border-subtle)]">
            <span class="block text-sm font-semibold text-[var(--text-primary)] mb-2.5">Fasilitas Umum Kos</span>
            <div class="flex flex-wrap gap-2">
                @foreach ($facilities as $facility)
                    <label class="cursor-pointer">
                        <input
                            type="checkbox"
                            name="facilities[]"
                            value="{{ $facility->id }}"
                            @checked(in_array($facility->id, $selectedFacilityIds))
                            class="sr-only peer"
                        >
                        <span class="inline-flex items-center min-h-[38px] px-3.5 py-1 rounded-full border border-[var(--border-control)] bg-[var(--bg-surface)] text-xs font-medium text-[var(--text-primary)] peer-checked:bg-[var(--brand-gold)] peer-checked:text-[var(--brand-gold-ink)] peer-checked:border-[var(--brand-gold)] transition-colors">
                            {{ $facility->name }}
                        </span>
                    </label>
                @endforeach
            </div>
        </div>
    @endif

    <div class="pt-4 border-t border-[var(--border-subtle)]">
        <x-input-label for="images" value="Unggah Foto Kos (Opsional, JPG/PNG/WEBP, maks 2 MB per foto)" />
        <input id="images" name="images[]" type="file" multiple accept=".jpg,.jpeg,.png,.webp" class="lk-input text-sm">
        <p class="mt-1 text-xs text-[var(--text-secondary)]">Foto yang diunggah otomatis menggantikan ilustrasi demo di katalog publik.</p>
        <x-input-error :messages="$errors->get('images')" />
        <x-input-error :messages="$errors->get('images.*')" />
    </div>
</div>

@push('head')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var el = document.getElementById('mitra-map-picker');
            if (!el || typeof L === 'undefined') return;

            var latInput = document.getElementById('latitude');
            var lngInput = document.getElementById('longitude');
            var gmapsInput = document.getElementById('google_maps_url');
            var addressInput = document.getElementById('address');
            var resolveBtn = document.getElementById('btn-resolve-gmaps');
            var geocodeAddressBtn = document.getElementById('btn-geocode-address');
            var gpsBtn = document.getElementById('btn-use-gps');
            var statusEl = document.getElementById('gmaps-resolve-status');
            var distanceEl = document.getElementById('map-distance-preview');
            var resolveUrl = el.dataset.resolveUrl;
            var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            var initLat = parseFloat(el.dataset.lat) || parseFloat(el.dataset.umyLat);
            var initLng = parseFloat(el.dataset.lng) || parseFloat(el.dataset.umyLng);
            var umyLat = parseFloat(el.dataset.umyLat);
            var umyLng = parseFloat(el.dataset.umyLng);

            var map = L.map(el).setView([initLat, initLng], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            L.circleMarker([umyLat, umyLng], {
                radius: 7,
                color: '#3D312C',
                fillColor: '#CAA05C',
                fillOpacity: 1
            }).addTo(map).bindPopup('Kampus Terpadu UMY');

            var marker = L.marker([initLat, initLng], { draggable: true }).addTo(map);

            function haversineKm(lat1, lon1, lat2, lon2) {
                var toRad = function (deg) { return deg * Math.PI / 180; };
                var dLat = toRad(lat2 - lat1);
                var dLon = toRad(lon2 - lon1);
                var a = Math.sin(dLat / 2) * Math.sin(dLat / 2)
                    + Math.cos(toRad(lat1)) * Math.cos(toRad(lat2))
                    * Math.sin(dLon / 2) * Math.sin(dLon / 2);
                return Math.round((6371 * 2 * Math.asin(Math.sqrt(a))) * 100) / 100;
            }

            function renderDistance(lat, lng) {
                if (!distanceEl || isNaN(lat) || isNaN(lng)) return;
                var km = haversineKm(lat, lng, umyLat, umyLng);
                var walk = Math.max(1, Math.round((km / 4.5) * 60));
                var motor = Math.max(1, Math.round((km / 24.0) * 60));
                var kmText = km.toFixed(2).replace('.', ',');
                var warning = km > 25 ? ' (Peringatan: titik lebih dari 25 km dari UMY, pastikan link/pin sudah tepat)' : '';
                distanceEl.textContent = 'Estimasi jarak ke Kampus Terpadu UMY: ' + kmText + ' km · ~' + walk + ' mnt jalan kaki · ~' + motor + ' mnt motor.' + warning;
            }

            function setStatus(message, isError) {
                if (!statusEl) return;
                statusEl.textContent = message;
                statusEl.className = isError
                    ? 'text-xs font-medium text-[var(--status-danger)]'
                    : 'text-xs font-medium text-[var(--brand-secondary-ink)]';
            }

            function applyPoint(lat, lng, zoom) {
                var numLat = Number(lat);
                var numLng = Number(lng);
                if (isNaN(numLat) || isNaN(numLng)) return;
                latInput.value = numLat.toFixed(7);
                lngInput.value = numLng.toFixed(7);
                marker.setLatLng([numLat, numLng]);
                if (zoom) {
                    map.setView([numLat, numLng], zoom);
                }
                renderDistance(numLat, numLng);
            }

            async function resolveLocation(rawValue) {
                var query = (rawValue || '').trim();
                if (!query) {
                    setStatus('Masukkan link Google Maps, koordinat, atau alamat terlebih dahulu.', true);
                    return;
                }

                setStatus('Membaca koordinat akurat dari Google Maps...', false);
                if (resolveBtn) resolveBtn.disabled = true;
                if (geocodeAddressBtn) geocodeAddressBtn.disabled = true;

                try {
                    var res = await fetch(resolveUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ input: query })
                    });
                    var data = await res.json();

                    if (!res.ok || !data.ok) {
                        setStatus(data.message || 'Gagal membaca lokasi. Coba salin ulang link dari Google Maps.', true);
                        return;
                    }

                    applyPoint(data.lat, data.lng, 17);
                    var label = data.display_name ? ' (' + data.display_name.slice(0, 70) + ')' : '';
                    setStatus('Pin peta dipindahkan akurat ke ' + Number(data.lat).toFixed(6) + ', ' + Number(data.lng).toFixed(6) + label, false);
                } catch (err) {
                    setStatus('Koneksi bermasalah saat membaca link Google Maps. Anda juga dapat menggeser pin secara manual.', true);
                } finally {
                    if (resolveBtn) resolveBtn.disabled = false;
                    if (geocodeAddressBtn) geocodeAddressBtn.disabled = false;
                }
            }

            marker.on('dragend', function () {
                var pos = marker.getLatLng();
                applyPoint(pos.lat, pos.lng, null);
                setStatus('Koordinat diperbarui dari posisi pin peta.', false);
            });

            map.on('click', function (e) {
                applyPoint(e.latlng.lat, e.latlng.lng, null);
                setStatus('Koordinat diperbarui dari titik klik peta.', false);
            });

            function syncFromInputs() {
                var lat = parseFloat(latInput.value);
                var lng = parseFloat(lngInput.value);
                if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
                    marker.setLatLng([lat, lng]);
                    map.setView([lat, lng], 16);
                    renderDistance(lat, lng);
                }
            }

            latInput.addEventListener('change', syncFromInputs);
            lngInput.addEventListener('change', syncFromInputs);

            if (resolveBtn && gmapsInput) {
                resolveBtn.addEventListener('click', function () {
                    resolveLocation(gmapsInput.value);
                });

                gmapsInput.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        resolveLocation(gmapsInput.value);
                    }
                });

                gmapsInput.addEventListener('paste', function () {
                    setTimeout(function () {
                        if (gmapsInput.value.trim().length > 6) {
                            resolveLocation(gmapsInput.value);
                        }
                    }, 80);
                });
            }

            if (geocodeAddressBtn && addressInput) {
                geocodeAddressBtn.addEventListener('click', function () {
                    if (!addressInput.value.trim()) {
                        setStatus('Isi kolom Alamat Lengkap terlebih dahulu untuk mencari titik peta.', true);
                        addressInput.focus();
                        return;
                    }
                    resolveLocation(addressInput.value);
                });
            }

            if (gpsBtn) {
                gpsBtn.addEventListener('click', function () {
                    if (!navigator.geolocation) {
                        setStatus('Browser perangkat ini tidak mendukung deteksi GPS otomatis.', true);
                        return;
                    }
                    setStatus('Mendeteksi lokasi GPS perangkat...', false);
                    navigator.geolocation.getCurrentPosition(
                        function (position) {
                            applyPoint(position.coords.latitude, position.coords.longitude, 17);
                            if (gmapsInput && !gmapsInput.value.trim()) {
                                gmapsInput.value = 'https://www.google.com/maps/search/?api=1&query='
                                    + position.coords.latitude.toFixed(7) + ',' + position.coords.longitude.toFixed(7);
                            }
                            setStatus('Pin peta disetel sesuai lokasi GPS perangkat Anda.', false);
                        },
                        function () {
                            setStatus('Izin GPS ditolak atau lokasi belum tersedia. Tempel link Google Maps sebagai alternatif.', true);
                        },
                        { enableHighAccuracy: true, timeout: 10000 }
                    );
                });
            }

            renderDistance(initLat, initLng);
        });
    </script>
@endpush
