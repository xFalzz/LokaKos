<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-[var(--text-primary)]">Kos Tersimpan (Favorit)</h1>
                <p class="mt-1 text-sm text-[var(--text-secondary)]">Daftar kos sekitar UMY yang Anda simpan untuk dibandingkan sebelum memesan.</p>
            </div>
            <a href="{{ route('kos.index') }}" class="lk-btn-primary text-sm">Cari Kos Lainnya</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if ($savedKos->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                @foreach ($savedKos as $kos)
                    <div class="space-y-3">
                        <x-lokakos.kos-card :kos="$kos" />
                        <form method="POST" action="{{ route('penyewa.saved.toggle', $kos) }}">
                            @csrf
                            <button type="submit" class="text-xs font-semibold text-[var(--status-danger)] hover:underline">
                                Hapus dari Kos Tersimpan
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @else
            <x-lokakos.empty-state
                title="Belum ada kos tersimpan"
                description="Simpan kos favorit Anda dari halaman detail kos agar mudah dibandingkan kapan saja."
                action-label="Buka Katalog Kos"
                :action-href="route('kos.index')"
            />
        @endif
    </div>
</x-app-layout>
