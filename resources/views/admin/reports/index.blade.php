<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-[var(--text-primary)]">Laporan Ketidaksesuaian Listing</h1>
                <p class="mt-1 text-sm text-[var(--text-secondary)]">Tindak lanjuti laporan mahasiswa jika data harga, stok, atau foto kos tidak sesuai kondisi lapangan.</p>
            </div>
            <div class="flex gap-2 text-xs">
                <a href="{{ route('admin.reports.index') }}" class="{{ ! $currentStatus ? 'lk-btn-primary' : 'lk-btn-secondary' }} min-h-[38px] px-4">Semua</a>
                <a href="{{ route('admin.reports.index', ['status' => 'open']) }}" class="{{ $currentStatus === 'open' ? 'lk-btn-primary' : 'lk-btn-secondary' }} min-h-[38px] px-4">Belum Ditangani</a>
                <a href="{{ route('admin.reports.index', ['status' => 'resolved']) }}" class="{{ $currentStatus === 'resolved' ? 'lk-btn-primary' : 'lk-btn-secondary' }} min-h-[38px] px-4">Selesai</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
        @if ($reports->isNotEmpty())
            @foreach ($reports as $report)
                <article class="lk-card p-6 space-y-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <x-lokakos.status-badge :status="$report->status" />
                                <span class="text-xs font-semibold uppercase tracking-wide text-[var(--status-danger)]">{{ str_replace('_', ' ', $report->reason) }}</span>
                                <span class="text-xs text-[var(--text-secondary)] tabular-nums">{{ $report->created_at->format('d M Y H:i') }} WIB</span>
                            </div>
                            <h2 class="text-lg font-semibold text-[var(--text-primary)]">{{ $report->boardingHouse?->name }}</h2>
                            <p class="text-xs text-[var(--text-secondary)]">
                                Pelapor: <strong class="text-[var(--text-primary)]">{{ $report->reporter_name }}</strong> ·
                                Pemilik Mitra: {{ $report->boardingHouse?->mitra?->name }}
                            </p>
                            <p class="text-sm text-[var(--text-secondary)] pt-1 leading-relaxed">{{ $report->details }}</p>
                        </div>
                    </div>

                    @if ($report->status === 'open')
                        <form method="POST" action="{{ route('admin.reports.handle', $report) }}" class="pt-4 border-t border-[var(--border-subtle)] space-y-3">
                            @csrf
                            @method('PATCH')
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                                <div class="md:col-span-3">
                                    <label class="block text-xs font-semibold text-[var(--text-primary)] mb-1">Keputusan Admin</label>
                                    <select name="status" class="lk-input text-xs min-h-[40px]">
                                        <option value="resolved">Selesaikan Laporan</option>
                                        <option value="dismissed">Abaikan (Tidak Terbukti)</option>
                                    </select>
                                </div>
                                <div class="md:col-span-7">
                                    <label class="block text-xs font-semibold text-[var(--text-primary)] mb-1">Catatan Tindakan Admin</label>
                                    <input type="text" name="admin_note" placeholder="Contoh: Mitra diminta memperbarui tarif terbaru" class="lk-input text-xs min-h-[40px]">
                                </div>
                                <div class="md:col-span-2">
                                    <button type="submit" class="lk-btn-primary w-full text-xs min-h-[40px]">Simpan</button>
                                </div>
                            </div>
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="unpublish_kos" value="1" class="w-4 h-4 rounded border-[var(--border-control)]">
                                <span class="text-xs font-semibold text-[var(--status-danger)]">Sekaligus tarik sementara kos ini ke status Draf (sembunyikan dari publik)</span>
                            </label>
                        </form>
                    @else
                        <p class="text-xs text-[var(--text-secondary)] pt-2 border-t border-[var(--border-subtle)]">
                            Ditangani oleh <strong class="text-[var(--text-primary)]">{{ $report->handler?->name ?? 'Admin' }}</strong>
                            pada {{ $report->handled_at?->format('d M Y H:i') }} WIB.
                            @if ($report->admin_note)
                                Catatan: {{ $report->admin_note }}
                            @endif
                        </p>
                    @endif
                </article>
            @endforeach
            <div>{{ $reports->links() }}</div>
        @else
            <x-lokakos.empty-state
                title="Tidak ada laporan listing"
                description="Semua listing kos tayang saat ini tidak memiliki laporan ketidaksesuaian yang terbuka."
            />
        @endif
    </div>
</x-app-layout>
