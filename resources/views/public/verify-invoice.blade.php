<x-public-layout title="Verifikasi Keaslian Invoice">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
        <div class="lk-card p-6 sm:p-10 space-y-6">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[var(--text-secondary)]">Verifikasi Dokumen LokaKos UMY</p>
                <h1 class="mt-2 font-display text-2xl sm:text-3xl font-semibold text-[var(--text-primary)]">
                    Validasi Bukti Pemesanan Resmi
                </h1>
            </div>

            @if ($isValid && $booking)
                <div class="p-4 rounded-[var(--radius-control)] bg-[var(--brand-secondary-bg)] text-[var(--brand-secondary-ink)] border border-[var(--border-subtle)] text-sm">
                    <p class="font-semibold">Dokumen Valid & Terverifikasi Resmi</p>
                    <p class="mt-1">Pembayaran untuk kode booking <strong class="tabular-nums">{{ $booking->booking_code }}</strong> tercatat resmi dan sah di sistem LokaKos.</p>
                </div>

                <dl class="divide-y divide-[var(--border-subtle)] text-sm">
                    <div class="py-3 flex justify-between gap-4">
                        <dt class="text-[var(--text-secondary)]">Kode Booking</dt>
                        <dd class="font-semibold text-[var(--text-primary)] tabular-nums">{{ $booking->booking_code }}</dd>
                    </div>
                    <div class="py-3 flex justify-between gap-4">
                        <dt class="text-[var(--text-secondary)]">Status Pesanan</dt>
                        <dd><x-lokakos.status-badge :status="$booking->status" /></dd>
                    </div>
                    <div class="py-3 flex justify-between gap-4">
                        <dt class="text-[var(--text-secondary)]">Nama Penyewa</dt>
                        <dd class="font-semibold text-[var(--text-primary)]">{{ $booking->user?->name }}</dd>
                    </div>
                    <div class="py-3 flex justify-between gap-4">
                        <dt class="text-[var(--text-secondary)]">Properti & Tipe Kamar</dt>
                        <dd class="font-semibold text-[var(--text-primary)] text-right">{{ $booking->roomType?->boardingHouse?->name }} ({{ $booking->roomType?->name }})</dd>
                    </div>
                    <div class="py-3 flex justify-between gap-4">
                        <dt class="text-[var(--text-secondary)]">Masa Sewa</dt>
                        <dd class="font-medium text-[var(--text-primary)] tabular-nums">{{ $booking->check_in_date->format('d M Y') }} s.d. {{ $booking->leaseEndDate()->format('d M Y') }}</dd>
                    </div>
                    <div class="py-3 flex justify-between gap-4">
                        <dt class="text-[var(--text-secondary)]">Nominal Terkonfirmasi</dt>
                        <dd class="font-bold text-[var(--text-primary)] tabular-nums">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</dd>
                    </div>
                </dl>
            @else
                <div class="p-4 rounded-[var(--radius-control)] bg-[var(--status-danger-bg)] text-[var(--status-danger)] text-sm">
                    <p class="font-semibold">Dokumen Tidak Valid atau Belum Terkonfirmasi</p>
                    <p class="mt-1">Kode booking <strong>{{ $searchedCode }}</strong> tidak ditemukan atau status pembayarannya belum diverifikasi lunas oleh pengelola kos.</p>
                </div>
            @endif

            <div class="pt-4 border-t border-[var(--border-subtle)] flex justify-end">
                <a href="{{ route('home') }}" class="lk-btn-secondary text-sm">Kembali ke Beranda</a>
            </div>
        </div>
    </div>
</x-public-layout>
