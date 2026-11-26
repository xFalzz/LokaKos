<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-[var(--text-primary)]">Master Data Kawasan UMY & Fasilitas</h1>
                <p class="mt-1 text-sm text-[var(--text-secondary)]">Perubahan data master langsung tersedia pada formulir Mitra dan filter katalog publik.</p>
            </div>
            <a href="{{ route('dashboard') }}" class="lk-btn-secondary text-sm">Dashboard Admin</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
        <section class="lk-card p-6 space-y-5">
            <h2 class="text-lg font-semibold text-[var(--text-primary)]">Area Sekitar UMY ({{ $areas->count() }})</h2>

            <form method="POST" action="{{ route('admin.areas.store') }}" class="space-y-3 p-4 rounded-[var(--radius-control)] bg-[var(--bg-tint)] border border-[var(--border-subtle)]">
                @csrf
                <div>
                    <x-input-label for="area_name" value="Tambah Area Baru" />
                    <x-text-input id="area_name" name="name" type="text" required placeholder="Contoh: Kasongan / Tirtonirmolo" />
                    <x-input-error :messages="$errors->get('name')" />
                </div>
                <div>
                    <x-input-label for="area_desc" value="Deskripsi Singkat (Opsional)" />
                    <x-text-input id="area_desc" name="description" type="text" placeholder="Gambaran lokasi terhadap Kampus Terpadu UMY" />
                </div>
                <button type="submit" class="lk-btn-primary text-xs min-h-[40px]">Simpan Area</button>
            </form>

            <ul class="divide-y divide-[var(--border-subtle)] text-sm">
                @foreach ($areas as $area)
                    <li x-data="{ editing: false }" class="py-3 space-y-2.5">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="font-semibold text-[var(--text-primary)]">{{ $area->name }}</p>
                                <p class="text-xs text-[var(--text-secondary)] tabular-nums">{{ $area->boarding_houses_count }} properti kos</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <button type="button" @click="editing = !editing" class="text-xs font-semibold text-[var(--brand-gold-text)] hover:underline">
                                    <span x-text="editing ? 'Batal' : 'Edit'">Edit</span>
                                </button>
                                <form method="POST" action="{{ route('admin.areas.destroy', $area) }}" onsubmit="return confirm('Hapus area ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold text-[var(--status-danger)] hover:underline">Hapus</button>
                                </form>
                            </div>
                        </div>

                        <form x-cloak x-show="editing" method="POST" action="{{ route('admin.areas.update', $area) }}" class="p-3 rounded-[var(--radius-control)] bg-[var(--bg-tint)] border border-[var(--border-subtle)] space-y-2">
                            @csrf
                            @method('PUT')
                            <input type="text" name="name" value="{{ $area->name }}" required class="lk-input text-xs">
                            <input type="text" name="description" value="{{ $area->description }}" placeholder="Deskripsi singkat area" class="lk-input text-xs">
                            <button type="submit" class="lk-btn-primary text-xs min-h-[36px] px-4">Simpan Perubahan</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        </section>

        <section class="lk-card p-6 space-y-5">
            <h2 class="text-lg font-semibold text-[var(--text-primary)]">Master Fasilitas ({{ $facilities->count() }})</h2>

            <form method="POST" action="{{ route('admin.facilities.store') }}" class="space-y-3 p-4 rounded-[var(--radius-control)] bg-[var(--bg-tint)] border border-[var(--border-subtle)]">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <x-input-label for="facility_name" value="Nama Fasilitas" />
                        <x-text-input id="facility_name" name="name" type="text" required placeholder="Contoh: Kulkas Bersama" />
                    </div>
                    <div>
                        <x-input-label for="facility_category" value="Kategori" />
                        <select id="facility_category" name="category" required class="lk-input text-sm">
                            <option value="kos">Fasilitas Umum Kos</option>
                            <option value="kamar">Fasilitas Kamar</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="lk-btn-primary text-xs min-h-[40px]">Simpan Fasilitas</button>
            </form>

            <ul class="divide-y divide-[var(--border-subtle)] text-sm">
                @foreach ($facilities as $facility)
                    <li class="py-2.5 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <span class="font-medium text-[var(--text-primary)]">{{ $facility->name }}</span>
                            <span class="text-[11px] px-2 py-0.5 rounded-full bg-[var(--bg-tint)] text-[var(--text-secondary)] uppercase">{{ $facility->category }}</span>
                        </div>
                        <form method="POST" action="{{ route('admin.facilities.destroy', $facility) }}" onsubmit="return confirm('Hapus fasilitas ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs font-semibold text-[var(--status-danger)] hover:underline">Hapus</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        </section>
    </div>
</x-app-layout>
