<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-[var(--text-primary)]">Tambah Properti Kos Baru</h1>
                <p class="mt-1 text-sm text-[var(--text-secondary)]">Kos baru akan masuk antrean moderasi Admin sebelum tayang di katalog mahasiswa.</p>
            </div>
            <a href="{{ route('mitra.kos.index') }}" class="lk-btn-secondary text-sm">Kembali</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('mitra.kos.store') }}" enctype="multipart/form-data" class="lk-card p-6 sm:p-8 space-y-6">
            @csrf
            @include('mitra.kos._form')

            <div class="pt-4 border-t border-[var(--border-subtle)] flex justify-end gap-3">
                <a href="{{ route('mitra.kos.index') }}" class="lk-btn-secondary text-sm">Batal</a>
                <button type="submit" class="lk-btn-primary text-sm">Simpan Kos & Lanjut Tambah Kamar</button>
            </div>
        </form>
    </div>
</x-app-layout>
