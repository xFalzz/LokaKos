<x-public-layout title="Bandingkan Kos Kawasan UMY">
    <div class="max-w-[1260px] mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 space-y-8">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[var(--text-secondary)]">Perbandingan Berdampingan</p>
                <h1 class="mt-1 font-display text-3xl sm:text-4xl font-semibold text-[var(--text-primary)]">Bandingkan Pilihan Kos UMY</h1>
                <p class="mt-2 text-sm text-[var(--text-secondary)]">Pilih hingga 3 kos untuk membandingkan harga bulanan, jarak ke UMY, fasilitas, listrik, dan uang jaminan.</p>
            </div>
            <a href="{{ route('kos.index') }}" class="lk-btn-secondary text-sm">Kembali ke Katalog</a>
        </div>

        <form method="GET" action="{{ route('kos.compare') }}" class="lk-card p-5 sm:p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @for ($slot = 0; $slot < 3; $slot++)
                    <div>
                        <label for="compare-slot-{{ $slot }}" class="block text-xs font-semibold text-[var(--text-primary)] mb-1.5">
                            Pilihan Kos {{ $slot + 1 }}
                        </label>
                        <select id="compare-slot-{{ $slot }}" name="ids[]" class="lk-input text-sm">
                            <option value="">-- Pilih kos --</option>
                            @foreach ($availableKos as $opt)
                                <option value="{{ $opt->id }}" @selected(($selectedIds[$slot] ?? null) === $opt->id)>
                                    {{ $opt->name }} ({{ $opt->area?->name }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endfor
            </div>
            <div class="flex flex-wrap justify-end gap-2">
                <a href="{{ route('kos.compare') }}" class="lk-btn-secondary text-xs min-h-[40px]">Reset Pilihan</a>
                <button type="submit" class="lk-btn-primary text-xs min-h-[40px]">Tampilkan Perbandingan</button>
            </div>
        </form>

        @if ($selectedKos->isNotEmpty())
            <div class="lk-card overflow-x-auto">
                <table class="w-full text-sm text-left min-w-[680px]">
                    <thead class="bg-[var(--bg-tint)] border-b border-[var(--border-subtle)]">
                        <tr>
                            <th class="p-4 font-semibold text-[var(--text-secondary)] w-44">Aspek Perbandingan</th>
                            @foreach ($selectedKos as $kos)
                                <th class="p-4 align-top">
                                    <a href="{{ route('kos.show', $kos) }}" class="text-base font-semibold text-[var(--text-primary)] hover:text-[var(--brand-gold-text)]">
                                        {{ $kos->name }}
                                    </a>
                                    <p class="mt-0.5 text-xs font-normal text-[var(--text-secondary)]">{{ $kos->area?->name }}</p>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border-subtle)]">
                        <tr>
                            <td class="p-4 font-medium text-[var(--text-secondary)]">Tipe & Kategori</td>
                            @foreach ($selectedKos as $kos)
                                <td class="p-4 text-[var(--text-primary)]">
                                    Kos {{ ucfirst($kos->gender_type) }}
                                    @if ($kos->is_syariah)
                                        · <span class="font-semibold text-[var(--brand-secondary-ink)]">Syariah</span>
                                    @endif
                                    @if ($kos->is_field_verified)
                                        <span class="block mt-1 text-xs font-semibold text-[var(--brand-secondary-ink)]">Terverifikasi Lapangan</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="p-4 font-medium text-[var(--text-secondary)]">Tarif Mulai / Bulan</td>
                            @foreach ($selectedKos as $kos)
                                @php $minPrice = $kos->roomTypes->min('price_monthly'); @endphp
                                <td class="p-4 font-bold text-[var(--text-primary)] tabular-nums">
                                    {{ $minPrice ? 'Rp ' . number_format($minPrice, 0, ',', '.') : '-' }}
                                </td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="p-4 font-medium text-[var(--text-secondary)]">Jarak & Waktu ke UMY</td>
                            @foreach ($selectedKos as $kos)
                                <td class="p-4 text-[var(--text-primary)] tabular-nums">
                                    @if ($kos->distance_to_umy_km !== null)
                                        <strong>{{ number_format($kos->distance_to_umy_km, 2, ',', '.') }} km</strong>
                                        <span class="block text-xs text-[var(--text-secondary)]">
                                            ~{{ $kos->estimatedWalkingMinutes() }} mnt jalan · ~{{ $kos->estimatedMotorMinutes() }} mnt motor
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="p-4 font-medium text-[var(--text-secondary)]">Sisa Kamar</td>
                            @foreach ($selectedKos as $kos)
                                <td class="p-4 tabular-nums text-[var(--text-primary)]">
                                    {{ $kos->roomTypes->sum('available_rooms') }} dari {{ $kos->roomTypes->sum('total_rooms') }} kamar
                                </td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="p-4 font-medium text-[var(--text-secondary)]">Listrik & Deposit</td>
                            @foreach ($selectedKos as $kos)
                                @php
                                    $anyIncludeElectric = $kos->roomTypes->contains('electricity_included', true);
                                    $minDeposit = $kos->roomTypes->min('deposit_amount') ?? 0;
                                @endphp
                                <td class="p-4 text-xs text-[var(--text-primary)] space-y-1">
                                    <p>{{ $anyIncludeElectric ? 'Ada tipe termasuk listrik' : 'Listrik token mandiri' }}</p>
                                    <p class="tabular-nums">Deposit mulai: {{ $minDeposit > 0 ? 'Rp ' . number_format($minDeposit, 0, ',', '.') : 'Tanpa deposit' }}</p>
                                </td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="p-4 font-medium text-[var(--text-secondary)]">Fasilitas Umum</td>
                            @foreach ($selectedKos as $kos)
                                <td class="p-4">
                                    <div class="flex flex-wrap gap-1.5">
                                        @forelse ($kos->facilities as $f)
                                            <span class="px-2.5 py-0.5 rounded-full bg-[var(--bg-tint)] border border-[var(--border-subtle)] text-xs text-[var(--text-primary)]">{{ $f->name }}</span>
                                        @empty
                                            <span class="text-xs text-[var(--text-secondary)]">-</span>
                                        @endforelse
                                    </div>
                                </td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="p-4 font-medium text-[var(--text-secondary)]">Aksi Cepat</td>
                            @foreach ($selectedKos as $kos)
                                <td class="p-4">
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('kos.show', $kos) }}" class="lk-btn-primary text-xs min-h-[38px] px-4">Lihat & Pesan</a>
                                        @if ($wa = $kos->whatsappUrl(null, auth()->user()))
                                            <a href="{{ $wa }}" target="_blank" rel="noopener noreferrer" class="lk-btn-secondary text-xs min-h-[38px] px-3.5">WhatsApp</a>
                                        @endif
                                    </div>
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        @else
            <x-lokakos.empty-state
                title="Pilih kos yang ingin dibandingkan"
                description="Pilih minimal 2 kos pada formulir di atas atau tandai kos dari halaman katalog untuk melihat tabel perbandingannya."
                action-label="Buka Katalog Kos"
                :action-href="route('kos.index')"
            />
        @endif
    </div>
</x-public-layout>
