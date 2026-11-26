<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-[var(--text-primary)]">Pesanan Kamar Saya</h1>
                <p class="mt-1 text-sm text-[var(--text-secondary)]">Pantau status pemesanan, unggah bukti transfer, unduh invoice PDF, dan kirim ulasan.</p>
            </div>
            <a href="{{ route('kos.index') }}" class="lk-btn-primary text-sm">Cari Kos Lainnya</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if ($bookings->isNotEmpty())
            <div class="space-y-4">
                @foreach ($bookings as $booking)
                    <div class="lk-card p-5 sm:p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <div class="flex flex-wrap items-center gap-2.5">
                                <span class="text-xs font-bold text-[var(--brand-gold-text)] tabular-nums">{{ $booking->booking_code }}</span>
                                <x-lokakos.status-badge :status="$booking->status" />
                            </div>
                            <h2 class="mt-2 text-lg font-semibold text-[var(--text-primary)]">
                                {{ $booking->roomType?->boardingHouse?->name }} · {{ $booking->roomType?->name }}
                            </h2>
                            <p class="mt-1 text-sm text-[var(--text-secondary)] tabular-nums">
                                {{ $booking->roomType?->boardingHouse?->area?->name }} · Masuk {{ $booking->check_in_date->format('d M Y') }} ({{ $booking->duration_months }} bulan) · Rp {{ number_format($booking->total_price, 0, ',', '.') }}
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            @if ($booking->canDownloadInvoice())
                                <a href="{{ route('penyewa.bookings.invoice', $booking) }}" class="lk-btn-secondary text-xs min-h-[40px]">Unduh Invoice PDF</a>
                            @endif
                            <a href="{{ route('penyewa.bookings.show', $booking) }}" class="lk-btn-primary text-xs min-h-[40px]">Detail & Pembayaran</a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">{{ $bookings->links() }}</div>
        @else
            <x-lokakos.empty-state
                title="Belum ada pesanan kamar"
                description="Pilih properti kos di kawasan UMY lalu ajukan pemesanan kamar dari halaman detail kos."
                action-label="Buka Katalog Kos"
                :action-href="route('kos.index')"
            />
        @endif
    </div>
</x-app-layout>
