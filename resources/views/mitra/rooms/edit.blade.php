<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-[var(--text-primary)]">Edit Tipe Kamar</h1>
                <p class="mt-1 text-sm text-[var(--text-secondary)]">{{ $kos->name }} · {{ $room->name }}</p>
            </div>
            <a href="{{ route('mitra.rooms.index', $kos) }}" class="lk-btn-secondary text-sm">Kembali</a>
        </div>
    </x-slot>

    @php
        $selectedIds = old('facilities', $room->facilities->pluck('id')->all());
    @endphp

    <div class="py-8 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('mitra.rooms.update', [$kos, $room]) }}" enctype="multipart/form-data" class="lk-card p-6 sm:p-8 space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-1">
                    <x-input-label for="name" value="Nama Tipe Kamar" />
                    <x-text-input id="name" name="name" type="text" :value="old('name', $room->name)" required />
                    <x-input-error :messages="$errors->get('name')" />
                </div>
                <div>
                    <x-input-label for="size_m2" value="Ukuran Kamar" />
                    <x-text-input id="size_m2" name="size_m2" type="text" :value="old('size_m2', $room->size_m2)" />
                </div>
                <div>
                    <x-input-label for="max_occupancy" value="Kapasitas (Orang)" />
                    <select id="max_occupancy" name="max_occupancy" required class="lk-input text-sm">
                        <option value="1" @selected(old('max_occupancy', $room->max_occupancy) == 1)>Maks 1 orang</option>
                        <option value="2" @selected(old('max_occupancy', $room->max_occupancy) == 2)>Maks 2 orang</option>
                        <option value="3" @selected(old('max_occupancy', $room->max_occupancy) == 3)>Maks 3 orang</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div>
                    <x-input-label for="price_monthly" value="1 Bulan (Rp)" />
                    <x-text-input id="price_monthly" name="price_monthly" type="number" min="100000" :value="old('price_monthly', $room->price_monthly)" required class="tabular-nums" />
                    <x-input-error :messages="$errors->get('price_monthly')" />
                </div>
                <div>
                    <x-input-label for="price_3_months" value="3 Bulan" />
                    <x-text-input id="price_3_months" name="price_3_months" type="number" min="100000" :value="old('price_3_months', $room->price_3_months)" class="tabular-nums" />
                </div>
                <div>
                    <x-input-label for="price_6_months" value="6 Bulan" />
                    <x-text-input id="price_6_months" name="price_6_months" type="number" min="100000" :value="old('price_6_months', $room->price_6_months)" class="tabular-nums" />
                </div>
                <div>
                    <x-input-label for="price_yearly" value="12 Bulan" />
                    <x-text-input id="price_yearly" name="price_yearly" type="number" min="100000" :value="old('price_yearly', $room->price_yearly)" class="tabular-nums" />
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center">
                <div>
                    <x-input-label for="deposit_amount" value="Uang Deposit / Jaminan (Rp)" />
                    <x-text-input id="deposit_amount" name="deposit_amount" type="number" min="0" step="50000" :value="old('deposit_amount', $room->deposit_amount)" class="tabular-nums" />
                </div>
                <div class="pt-5">
                    <label class="inline-flex items-center gap-2.5 cursor-pointer min-h-[44px]">
                        <input type="checkbox" name="electricity_included" value="1" @checked(old('electricity_included', $room->electricity_included)) class="w-4 h-4 rounded border-[var(--border-control)]">
                        <span class="text-sm font-medium text-[var(--text-primary)]">Harga sudah termasuk listrik</span>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="total_rooms" value="Total Unit Kamar" />
                    <x-text-input id="total_rooms" name="total_rooms" type="number" min="1" :value="old('total_rooms', $room->total_rooms)" required class="tabular-nums" />
                    <x-input-error :messages="$errors->get('total_rooms')" />
                </div>
                <div>
                    <x-input-label for="available_rooms" value="Kamar Tersedia" />
                    <x-text-input id="available_rooms" name="available_rooms" type="number" min="0" :value="old('available_rooms', $room->available_rooms)" required class="tabular-nums" />
                    <x-input-error :messages="$errors->get('available_rooms')" />
                </div>
            </div>

            <div>
                <x-input-label for="room_image" value="Ganti Foto Tipe Kamar (Opsional, JPG/PNG/WEBP maks 2 MB)" />
                @if ($room->room_image_path)
                    <img src="{{ asset('storage/' . $room->room_image_path) }}" alt="Foto {{ $room->name }}" class="mb-3 h-32 rounded-[var(--radius-control)] object-cover border border-[var(--border-subtle)]">
                @endif
                <input id="room_image" name="room_image" type="file" accept=".jpg,.jpeg,.png,.webp" class="lk-input text-sm">
                <x-input-error :messages="$errors->get('room_image')" />
            </div>

            <div>
                <x-input-label for="description" value="Keterangan Kamar" />
                <textarea id="description" name="description" rows="3" class="lk-input text-sm">{{ old('description', $room->description) }}</textarea>
            </div>

            <div>
                <span class="block text-xs font-semibold text-[var(--text-primary)] mb-2">Fasilitas Kamar</span>
                <div class="flex flex-wrap gap-2">
                    @foreach ($facilities as $facility)
                        <label class="cursor-pointer">
                            <input type="checkbox" name="facilities[]" value="{{ $facility->id }}" @checked(in_array($facility->id, $selectedIds)) class="sr-only peer">
                            <span class="inline-flex items-center min-h-[36px] px-3 py-1 rounded-full border border-[var(--border-control)] bg-[var(--bg-surface)] text-xs font-medium text-[var(--text-primary)] peer-checked:bg-[var(--brand-gold)] peer-checked:text-[var(--brand-gold-ink)] peer-checked:border-[var(--brand-gold)]">
                                {{ $facility->name }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-3 border-t border-[var(--border-subtle)]">
                <a href="{{ route('mitra.rooms.index', $kos) }}" class="lk-btn-secondary text-sm">Batal</a>
                <button type="submit" class="lk-btn-primary text-sm">Simpan Perubahan Kamar</button>
            </div>
        </form>
    </div>
</x-app-layout>
