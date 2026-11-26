<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-[var(--text-primary)]">Kelola Tipe Kamar & Stok</h1>
                <p class="mt-1 text-sm text-[var(--text-secondary)]">{{ $kos->name }} · {{ $kos->area?->name }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('mitra.kos.edit', $kos) }}" class="lk-btn-secondary text-sm">Edit Data Kos</a>
                <a href="{{ route('mitra.kos.index') }}" class="lk-btn-secondary text-sm">Daftar Kos</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <div class="lg:col-span-7 space-y-4">
            <h2 class="text-lg font-semibold text-[var(--text-primary)]">Tipe Kamar Terdaftar ({{ $kos->roomTypes->count() }})</h2>

            @if ($kos->roomTypes->isNotEmpty())
                @foreach ($kos->roomTypes as $room)
                    <div class="lk-card p-6 space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                            <div class="flex gap-4 items-start">
                                @if ($room->room_image_path)
                                    <img src="{{ asset('storage/' . $room->room_image_path) }}" alt="Foto {{ $room->name }}" class="w-20 h-16 rounded-[var(--radius-control)] object-cover border border-[var(--border-subtle)] shrink-0">
                                @endif
                                <div>
                                    <h3 class="text-lg font-semibold text-[var(--text-primary)]">{{ $room->name }}</h3>
                                    <p class="mt-0.5 text-xs text-[var(--text-secondary)]">
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
                            <x-lokakos.status-badge :status="$room->available_rooms > 0 ? 'published' : 'rejected'" :label="'Tersedia ' . $room->available_rooms . ' / ' . $room->total_rooms . ' kamar'" />
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs tabular-nums">
                            <div><span class="block text-[var(--text-secondary)]">1 Bulan</span><strong>Rp {{ number_format($room->price_monthly, 0, ',', '.') }}</strong></div>
                            <div><span class="block text-[var(--text-secondary)]">3 Bulan</span><strong>Rp {{ number_format($room->calculatePrice(3), 0, ',', '.') }}</strong></div>
                            <div><span class="block text-[var(--text-secondary)]">6 Bulan</span><strong>Rp {{ number_format($room->calculatePrice(6), 0, ',', '.') }}</strong></div>
                            <div><span class="block text-[var(--text-secondary)]">12 Bulan</span><strong>Rp {{ number_format($room->calculatePrice(12), 0, ',', '.') }}</strong></div>
                        </div>

                        @if ($room->facilities->isNotEmpty())
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($room->facilities as $f)
                                    <span class="px-2.5 py-0.5 rounded-full bg-[var(--bg-tint)] text-xs text-[var(--text-primary)]">{{ $f->name }}</span>
                                @endforeach
                            </div>
                        @endif

                        <div class="pt-3 border-t border-[var(--border-subtle)] flex flex-wrap items-center justify-between gap-3">
                            <div class="inline-flex items-center gap-1.5 rounded-full border border-[var(--border-control)] bg-[var(--bg-canvas)] px-2 py-1">
                                <form method="POST" action="{{ route('mitra.rooms.stock', [$kos, $room]) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="delta" value="-1">
                                    <button type="submit" @disabled($room->available_rooms <= 0) aria-label="Kurangi stok {{ $room->name }}" class="w-7 h-7 rounded-full inline-flex items-center justify-center text-sm font-bold hover:bg-[var(--bg-tint)] disabled:opacity-40">-</button>
                                </form>
                                <span class="px-2 text-xs font-semibold tabular-nums text-[var(--text-primary)]">Stok {{ $room->available_rooms }}/{{ $room->total_rooms }}</span>
                                <form method="POST" action="{{ route('mitra.rooms.stock', [$kos, $room]) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="delta" value="1">
                                    <button type="submit" @disabled($room->available_rooms >= $room->total_rooms) aria-label="Tambah stok {{ $room->name }}" class="w-7 h-7 rounded-full inline-flex items-center justify-center text-sm font-bold hover:bg-[var(--bg-tint)] disabled:opacity-40">+</button>
                                </form>
                            </div>

                            <div class="flex items-center gap-2">
                                <a href="{{ route('mitra.rooms.edit', [$kos, $room]) }}" class="lk-btn-primary text-xs min-h-[38px] px-4">Edit Kamar</a>
                                <form method="POST" action="{{ route('mitra.rooms.destroy', [$kos, $room]) }}" onsubmit="return confirm('Hapus tipe kamar ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold text-[var(--status-danger)] hover:underline px-2 py-2">Hapus</button>
                                </form>
                            </div>
                        </div>

                        <details class="pt-3 border-t border-[var(--border-subtle)]">
                            <summary class="cursor-pointer text-xs font-semibold text-[var(--text-primary)]">
                                Pemetaan Nomor Kamar Fisik ({{ $room->units->count() }} unit tercatat)
                            </summary>
                            <div class="mt-3 space-y-3">
                                @if ($room->units->isNotEmpty())
                                    <div class="space-y-2">
                                        @foreach ($room->units as $unit)
                                            <div class="p-3 rounded-[var(--radius-control)] bg-[var(--bg-tint)] border border-[var(--border-subtle)] flex flex-col gap-2">
                                                <div class="flex flex-wrap items-center justify-between gap-2 text-xs">
                                                    <div>
                                                        <strong class="text-[var(--text-primary)]">{{ $unit->unit_code }}</strong>
                                                        @if ($unit->floor_label)
                                                            <span class="text-[var(--text-secondary)]"> · {{ $unit->floor_label }}</span>
                                                        @endif
                                                        @if ($unit->tenant_name)
                                                            <span class="text-[var(--text-secondary)]"> · Penghuni: <strong class="text-[var(--text-primary)]">{{ $unit->tenant_name }}</strong></span>
                                                        @endif
                                                        @if ($unit->lease_ends_at)
                                                            <span class="text-[var(--text-secondary)] tabular-nums"> (s.d. {{ $unit->lease_ends_at->format('d/m/Y') }})</span>
                                                        @endif
                                                    </div>
                                                    <x-lokakos.status-badge :status="$unit->status" />
                                                </div>
                                                <form method="POST" action="{{ route('mitra.units.update', [$kos, $room, $unit]) }}" class="flex flex-wrap items-center gap-2">
                                                    @csrf
                                                    @method('PATCH')
                                                    <select name="status" class="lk-input text-xs min-h-[34px] py-1 w-28">
                                                        <option value="available" @selected($unit->status === 'available')>Kosong</option>
                                                        <option value="occupied" @selected($unit->status === 'occupied')>Terisi</option>
                                                        <option value="maintenance" @selected($unit->status === 'maintenance')>Perbaikan</option>
                                                    </select>
                                                    <input type="text" name="tenant_name" value="{{ $unit->tenant_name }}" placeholder="Nama penghuni" class="lk-input text-xs min-h-[34px] py-1 w-36">
                                                    <input type="date" name="lease_ends_at" value="{{ $unit->lease_ends_at?->toDateString() }}" class="lk-input text-xs min-h-[34px] py-1 w-36">
                                                    <button type="submit" class="lk-btn-secondary text-xs min-h-[34px] px-3">Simpan</button>
                                                    <button type="submit" form="del-unit-{{ $unit->id }}" class="text-xs font-semibold text-[var(--status-danger)] hover:underline ml-auto">Hapus</button>
                                                </form>
                                                <form id="del-unit-{{ $unit->id }}" method="POST" action="{{ route('mitra.units.destroy', [$kos, $room, $unit]) }}" class="hidden">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('mitra.units.store', [$kos, $room]) }}" class="grid grid-cols-2 sm:grid-cols-5 gap-2 items-end">
                                    @csrf
                                    <input type="text" name="unit_code" required placeholder="Kode (A-01)" class="lk-input text-xs min-h-[36px] py-1">
                                    <input type="text" name="floor_label" placeholder="Lantai 1" class="lk-input text-xs min-h-[36px] py-1">
                                    <select name="status" class="lk-input text-xs min-h-[36px] py-1">
                                        <option value="available">Kosong</option>
                                        <option value="occupied">Terisi</option>
                                        <option value="maintenance">Perbaikan</option>
                                    </select>
                                    <input type="text" name="tenant_name" placeholder="Penghuni (opsional)" class="lk-input text-xs min-h-[36px] py-1">
                                    <button type="submit" class="lk-btn-primary text-xs min-h-[36px] px-3">Tambah Unit</button>
                                </form>
                            </div>
                        </details>
                    </div>
                @endforeach
            @else
                <x-lokakos.empty-state
                    title="Belum ada tipe kamar"
                    description="Gunakan formulir di samping untuk menambahkan tipe kamar pertama beserta tarif, kapasitas, status listrik, dan stok kamar."
                />
            @endif
        </div>

        <div class="lg:col-span-5">
            <form method="POST" action="{{ route('mitra.rooms.store', $kos) }}" enctype="multipart/form-data" class="lk-card p-6 space-y-4">
                @csrf
                <h2 class="text-lg font-semibold text-[var(--text-primary)]">Tambah Tipe Kamar Baru</h2>

                <div>
                    <x-input-label for="name" value="Nama Tipe Kamar" />
                    <x-text-input id="name" name="name" type="text" :value="old('name')" required placeholder="Contoh: Tipe AC + KM Dalam" />
                    <x-input-error :messages="$errors->get('name')" />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <x-input-label for="size_m2" value="Ukuran Kamar" />
                        <x-text-input id="size_m2" name="size_m2" type="text" :value="old('size_m2')" placeholder="Contoh: 3 x 4 m" />
                    </div>
                    <div>
                        <x-input-label for="max_occupancy" value="Kapasitas (Orang)" />
                        <select id="max_occupancy" name="max_occupancy" required class="lk-input text-sm">
                            <option value="1" @selected(old('max_occupancy', 1) == 1)>Maks 1 orang</option>
                            <option value="2" @selected(old('max_occupancy') == 2)>Maks 2 orang</option>
                            <option value="3" @selected(old('max_occupancy') == 3)>Maks 3 orang</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <x-input-label for="price_monthly" value="Harga 1 Bulan (Rp)" />
                        <x-text-input id="price_monthly" name="price_monthly" type="number" min="100000" step="25000" :value="old('price_monthly')" required class="tabular-nums" />
                        <x-input-error :messages="$errors->get('price_monthly')" />
                    </div>
                    <div>
                        <x-input-label for="price_3_months" value="Harga 3 Bln (Opsional)" />
                        <x-text-input id="price_3_months" name="price_3_months" type="number" min="100000" step="25000" :value="old('price_3_months')" class="tabular-nums" />
                    </div>
                    <div>
                        <x-input-label for="price_6_months" value="Harga 6 Bln (Opsional)" />
                        <x-text-input id="price_6_months" name="price_6_months" type="number" min="100000" step="25000" :value="old('price_6_months')" class="tabular-nums" />
                    </div>
                    <div>
                        <x-input-label for="price_yearly" value="Harga 12 Bln (Opsional)" />
                        <x-text-input id="price_yearly" name="price_yearly" type="number" min="100000" step="25000" :value="old('price_yearly')" class="tabular-nums" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 items-center">
                    <div>
                        <x-input-label for="deposit_amount" value="Uang Deposit (Rp, opsional)" />
                        <x-text-input id="deposit_amount" name="deposit_amount" type="number" min="0" step="50000" :value="old('deposit_amount', 0)" class="tabular-nums" />
                    </div>
                    <div class="pt-5">
                        <label class="inline-flex items-center gap-2 cursor-pointer min-h-[44px]">
                            <input type="checkbox" name="electricity_included" value="1" @checked(old('electricity_included')) class="w-4 h-4 rounded border-[var(--border-control)]">
                            <span class="text-xs font-medium text-[var(--text-primary)]">Harga termasuk listrik</span>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <x-input-label for="total_rooms" value="Total Unit Kamar" />
                        <x-text-input id="total_rooms" name="total_rooms" type="number" min="1" :value="old('total_rooms', 4)" required class="tabular-nums" />
                        <x-input-error :messages="$errors->get('total_rooms')" />
                    </div>
                    <div>
                        <x-input-label for="available_rooms" value="Kamar Tersedia" />
                        <x-text-input id="available_rooms" name="available_rooms" type="number" min="0" :value="old('available_rooms', 4)" required class="tabular-nums" />
                        <x-input-error :messages="$errors->get('available_rooms')" />
                    </div>
                </div>

                <div>
                    <x-input-label for="room_image" value="Foto Khusus Tipe Kamar (Opsional, maks 2 MB)" />
                    <input id="room_image" name="room_image" type="file" accept=".jpg,.jpeg,.png,.webp" class="lk-input text-sm">
                    <x-input-error :messages="$errors->get('room_image')" />
                </div>

                <div>
                    <x-input-label for="description" value="Keterangan Kamar" />
                    <textarea id="description" name="description" rows="2" class="lk-input text-sm" placeholder="Contoh: Jendela menghadap taman, meja belajar luas">{{ old('description') }}</textarea>
                </div>

                @if ($facilities->isNotEmpty())
                    <div>
                        <span class="block text-xs font-semibold text-[var(--text-primary)] mb-2">Fasilitas Kamar</span>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($facilities as $facility)
                                <label class="cursor-pointer">
                                    <input type="checkbox" name="facilities[]" value="{{ $facility->id }}" class="sr-only peer">
                                    <span class="inline-flex items-center min-h-[34px] px-3 py-1 rounded-full border border-[var(--border-control)] bg-[var(--bg-surface)] text-xs font-medium text-[var(--text-primary)] peer-checked:bg-[var(--brand-gold)] peer-checked:text-[var(--brand-gold-ink)] peer-checked:border-[var(--brand-gold)]">
                                        {{ $facility->name }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <button type="submit" class="lk-btn-primary w-full text-sm">Tambahkan Tipe Kamar</button>
            </form>
        </div>
    </div>
</x-app-layout>
