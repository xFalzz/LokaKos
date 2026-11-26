<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-[var(--text-primary)]">Pesanan Masuk & Verifikasi Transfer</h1>
                <p class="mt-1 text-sm text-[var(--text-secondary)]">Periksa bukti transfer penyewa, setujui untuk mengurangi stok kamar, dan konfirmasi check-in.</p>
            </div>
            <a href="{{ route('mitra.report.pdf') }}" class="lk-btn-espresso text-sm">Unduh Laporan Pemasukan PDF</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="flex flex-wrap gap-2">
            @foreach ([
                '' => 'Semua',
                'waiting_verification' => 'Menunggu Verifikasi Bayar',
                'pending_payment' => 'Menunggu Pembayaran',
                'confirmed' => 'Terkonfirmasi',
                'completed' => 'Selesai (Check-in)',
                'rejected' => 'Ditolak',
            ] as $key => $label)
                <a
                    href="{{ route('mitra.bookings.index', $key ? ['status' => $key] : []) }}"
                    class="inline-flex items-center min-h-[40px] px-4 rounded-full text-xs font-semibold border {{ $statusFilter === $key ? 'bg-[var(--brand-gold)] text-[var(--brand-gold-ink)] border-[var(--brand-gold)]' : 'bg-[var(--bg-surface)] text-[var(--text-primary)] border-[var(--border-control)] hover:bg-[var(--bg-tint)]' }}"
                >
                    {{ $label }}
                </a>
            @endforeach
        </div>

        @if ($bookings->isNotEmpty())
            <div class="space-y-4">
                @foreach ($bookings as $booking)
                    <div class="lk-card p-6 space-y-4">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <div class="flex flex-wrap items-center gap-2.5">
                                    <span class="text-xs font-bold text-[var(--brand-gold-text)] tabular-nums">{{ $booking->booking_code }}</span>
                                    <x-lokakos.status-badge :status="$booking->status" />
                                </div>
                                <h2 class="mt-2 text-lg font-semibold text-[var(--text-primary)]">
                                    {{ $booking->roomType?->boardingHouse?->name }} · {{ $booking->roomType?->name }}
                                </h2>
                                <p class="mt-1 text-sm text-[var(--text-secondary)]">
                                    Penyewa: <strong class="text-[var(--text-primary)]">{{ $booking->user?->name }}</strong> ({{ $booking->user?->phone ?? $booking->user?->email }})
                                </p>
                                <p class="mt-1 text-xs text-[var(--text-secondary)] tabular-nums">
                                    Rencana masuk {{ $booking->check_in_date->format('d M Y') }} · Durasi {{ $booking->duration_months }} bulan · Total <strong class="text-[var(--text-primary)]">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</strong>
                                </p>
                            </div>

                            <div class="text-xs text-[var(--text-secondary)] tabular-nums">
                                Sisa stok tipe ini: <strong class="text-[var(--text-primary)]">{{ $booking->roomType?->available_rooms }} kamar</strong>
                            </div>
                        </div>

                        @if ($booking->payment)
                            <div class="p-4 rounded-[var(--radius-control)] bg-[var(--bg-tint)] border border-[var(--border-subtle)] flex flex-wrap items-center justify-between gap-3 text-sm">
                                <div>
                                    <p class="font-semibold text-[var(--text-primary)]">
                                        Bukti Transfer: {{ $booking->payment->sender_bank }} a.n. {{ $booking->payment->sender_name }}
                                    </p>
                                    @if ($booking->payment->rejection_note)
                                        <p class="text-xs text-[var(--status-danger)] mt-1">Alasan penolakan: {{ $booking->payment->rejection_note }}</p>
                                    @endif
                                </div>
                                <a href="{{ asset('storage/' . $booking->payment->proof_image_path) }}" target="_blank" class="lk-btn-secondary text-xs min-h-[38px] px-4">
                                    Buka Bukti Transfer
                                </a>
                            </div>
                        @endif

                        @if ($booking->status === \App\Models\Booking::STATUS_WAITING_VERIFICATION)
                            <div class="pt-3 border-t border-[var(--border-subtle)] flex flex-col md:flex-row md:items-end justify-between gap-4">
                                <form method="POST" action="{{ route('mitra.bookings.approve', $booking) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="lk-btn-primary text-xs min-h-[42px]">
                                        Setujui Pembayaran (Kurangi Stok 1)
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('mitra.bookings.reject', $booking) }}" class="flex flex-col sm:flex-row gap-2 flex-1 md:max-w-lg">
                                    @csrf
                                    @method('PATCH')
                                    <input type="text" name="rejection_note" required placeholder="Tulis alasan penolakan bukti bayar..." class="lk-input text-xs">
                                    <button type="submit" class="lk-btn-secondary text-xs text-[var(--status-danger)] border-[var(--status-danger)] shrink-0">
                                        Tolak Pembayaran
                                    </button>
                                </form>
                            </div>
                        @elseif ($booking->status === \App\Models\Booking::STATUS_CONFIRMED)
                            <div class="pt-3 border-t border-[var(--border-subtle)]">
                                <form method="POST" action="{{ route('mitra.bookings.complete', $booking) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="lk-btn-espresso text-xs min-h-[42px]">
                                        Konfirmasi Penyewa Sudah Check-In (Selesai)
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div>{{ $bookings->links() }}</div>
        @else
            <x-lokakos.empty-state
                title="Belum ada pesanan masuk"
                description="Pesanan yang diajukan mahasiswa pada properti kos Anda akan muncul di halaman ini."
            />
        @endif
    </div>
</x-app-layout>
