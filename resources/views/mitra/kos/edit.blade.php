<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="flex flex-wrap items-center gap-2.5">
                    <h1 class="text-2xl font-semibold text-[var(--text-primary)]">Edit Properti Kos</h1>
                    <x-lokakos.status-badge :status="$kos->status" />
                </div>
                <p class="mt-1 text-sm text-[var(--text-secondary)]">{{ $kos->name }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('mitra.rooms.index', $kos) }}" class="lk-btn-primary text-sm">Kelola Tipe Kamar</a>
                <a href="{{ route('mitra.kos.index') }}" class="lk-btn-secondary text-sm">Kembali</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        @if ($kos->rejection_note)
            <div class="p-4 rounded-[var(--radius-control)] bg-[var(--status-danger-bg)] text-[var(--status-danger)] text-sm">
                <strong>Catatan Moderasi Admin:</strong> {{ $kos->rejection_note }}
            </div>
        @endif

        @if ($kos->images->isNotEmpty())
            <section class="lk-card p-6">
                <h2 class="text-base font-semibold text-[var(--text-primary)] mb-4">Foto Kos Terunggah</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    @foreach ($kos->images as $image)
                        <div class="rounded-[var(--radius-control)] overflow-hidden border border-[var(--border-subtle)] bg-[var(--bg-tint)]">
                            <img src="{{ asset('storage/' . $image->image_path) }}" alt="Foto kos" class="w-full h-32 object-cover">
                            <div class="p-2 flex items-center justify-between text-xs">
                                <span class="text-[var(--text-secondary)]">{{ $image->is_primary ? 'Foto utama' : 'Galeri' }}</span>
                                <form method="POST" action="{{ route('mitra.kos.images.destroy', [$kos, $image]) }}" onsubmit="return confirm('Hapus foto ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="font-semibold text-[var(--status-danger)] hover:underline">Hapus</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <form method="POST" action="{{ route('mitra.kos.update', $kos) }}" enctype="multipart/form-data" class="lk-card p-6 sm:p-8 space-y-6">
            @csrf
            @method('PUT')
            @include('mitra.kos._form')

            @if ($kos->status === \App\Models\BoardingHouse::STATUS_REJECTED)
                <label class="inline-flex items-center gap-2.5 cursor-pointer min-h-[44px]">
                    <input type="checkbox" name="resubmit" value="1" checked class="w-4 h-4 rounded border-[var(--border-control)]">
                    <span class="text-sm font-semibold text-[var(--text-primary)]">Ajukan kembali ke antrean moderasi Admin setelah disimpan</span>
                </label>
            @endif

            <div class="pt-4 border-t border-[var(--border-subtle)] flex justify-end gap-3">
                <a href="{{ route('mitra.kos.index') }}" class="lk-btn-secondary text-sm">Kembali</a>
                <button type="submit" class="lk-btn-primary text-sm">Simpan Perubahan Kos</button>
            </div>
        </form>
    </div>
</x-app-layout>
