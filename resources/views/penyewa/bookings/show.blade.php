<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="flex flex-wrap items-center gap-2.5">
                    <h1 class="text-2xl font-semibold text-[var(--text-primary)] tabular-nums">{{ $booking->booking_code }}</h1>
                    <x-lokakos.status-badge :status="$booking->status" />
                </div>
                <p class="mt-1 text-sm text-[var(--text-secondary)]">
                    {{ $booking->roomType?->boardingHouse?->name }} · {{ $booking->roomType?->name }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if ($booking->canDownloadInvoice())
                    <a href="{{ route('penyewa.bookings.invoice', $booking) }}" class="lk-btn-primary text-xs sm:text-sm min-h-[42px]">Unduh Invoice PDF</a>
                    <a href="{{ route('penyewa.bookings.contract', $booking) }}" class="lk-btn-secondary text-xs sm:text-sm min-h-[42px]">Surat Perjanjian PDF</a>
                @endif
                <a href="{{ route('penyewa.bookings.index') }}" class="lk-btn-secondary text-xs sm:text-sm min-h-[42px]">Kembali</a>
            </div>
        </div>
    </x-slot>

    @php
        $mitraProfile = $booking->roomType?->boardingHouse?->mitra?->mitraProfile;
    @endphp

    <div class="py-8 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        @if ($booking->status === \App\Models\Booking::STATUS_PENDING_PAYMENT && $booking->payment_due_at)
            <div class="p-4 rounded-[var(--radius-control)] bg-[var(--accent-warm-bg)] text-[var(--accent-warm-ink)] border border-[var(--border-subtle)] text-sm flex flex-wrap items-center justify-between gap-2">
                <div>
                    <p class="font-semibold">Batas Waktu Unggah Bukti Pembayaran (24 Jam)</p>
                    <p class="text-xs mt-0.5 tabular-nums">
                        Unggah bukti transfer sebelum <strong>{{ $booking->payment_due_at->format('d M Y, H:i') }} WIB</strong> agar pesanan tidak dibatalkan otomatis oleh sistem.
                    </p>
                </div>
                @if ($booking->isPaymentExpired())
                    <span class="px-2.5 py-1 rounded-full bg-[var(--status-danger-bg)] text-[var(--status-danger)] text-xs font-semibold">Melewati Batas Waktu</span>
                @endif
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <section class="lk-card p-6 space-y-3 text-sm">
                <h2 class="text-lg font-semibold text-[var(--text-primary)]">Rincian Pemesanan</h2>
                <div class="flex justify-between gap-2 pt-2 border-t border-[var(--border-subtle)]">
                    <span class="text-[var(--text-secondary)]">Properti Kos</span>
                    <span class="font-semibold text-right">{{ $booking->roomType?->boardingHouse?->name }}</span>
                </div>
                <div class="flex justify-between gap-2">
                    <span class="text-[var(--text-secondary)]">Tipe Kamar</span>
                    <span class="font-medium text-right">{{ $booking->roomType?->name }}</span>
                </div>
                <div class="flex justify-between gap-2">
                    <span class="text-[var(--text-secondary)]">Periode Sewa</span>
                    <span class="font-medium tabular-nums text-right">{{ $booking->check_in_date->format('d M Y') }} s.d. {{ $booking->leaseEndDate()->format('d M Y') }}</span>
                </div>
                <div class="flex justify-between gap-2">
                    <span class="text-[var(--text-secondary)]">Durasi Sewa</span>
                    <span class="font-medium tabular-nums">{{ $booking->duration_months }} Bulan</span>
                </div>
                @if ($booking->promo_code && $booking->discount_amount > 0)
                    <div class="flex justify-between gap-2 text-[var(--brand-secondary-ink)]">
                        <span>Promo ({{ $booking->promo_code }})</span>
                        <span class="font-semibold tabular-nums">- Rp {{ number_format($booking->discount_amount, 0, ',', '.') }}</span>
                    </div>
                @endif
                <div class="flex justify-between gap-2 pt-3 border-t border-[var(--border-subtle)]">
                    <span class="font-semibold text-[var(--text-primary)]">Total Tagihan</span>
                    <span class="text-lg font-bold text-[var(--text-primary)] tabular-nums">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</span>
                </div>
                @if ($booking->identity_card_path)
                    <div class="pt-2">
                        <a href="{{ asset('storage/' . $booking->identity_card_path) }}" target="_blank" class="text-xs font-semibold text-[var(--brand-gold-text)] hover:underline">
                            Lihat Berkas KTM / KTP Terunggah
                        </a>
                    </div>
                @endif
                @if ($booking->notes)
                    <div class="pt-2 text-xs text-[var(--text-secondary)]">Catatan: {{ $booking->notes }}</div>
                @endif
            </section>

            <section class="lk-card p-6 space-y-3 text-sm">
                <h2 class="text-lg font-semibold text-[var(--text-primary)]">Rekening Tujuan Transfer (Mitra)</h2>
                @if ($mitraProfile)
                    <div class="p-4 rounded-[var(--radius-control)] bg-[var(--bg-tint)] border border-[var(--border-subtle)] space-y-1.5">
                        <p class="text-xs text-[var(--text-secondary)]">Bank Tujuan</p>
                        <p class="text-base font-bold text-[var(--text-primary)]">{{ $mitraProfile->bank_name }}</p>
                        <p class="text-xs text-[var(--text-secondary)] pt-2">Nomor Rekening</p>
                        <p class="text-lg font-bold text-[var(--text-primary)] tabular-nums">{{ $mitraProfile->bank_account_number }}</p>
                        <p class="text-xs text-[var(--text-secondary)] pt-1">Atas Nama: <strong class="text-[var(--text-primary)]">{{ $mitraProfile->bank_account_name }}</strong></p>
                    </div>
                    <p class="text-xs text-[var(--text-secondary)]">
                        Transfer sesuai nominal tagihan lalu unggah bukti transfer di bawah agar Mitra dapat memverifikasi pesanan Anda.
                    </p>
                @else
                    <p class="text-sm text-[var(--text-secondary)]">Informasi rekening Mitra belum tersedia.</p>
                @endif
            </section>
        </div>

        @if ($booking->canRenew() || $booking->canReportMaintenance())
            <section class="lk-card p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-semibold text-[var(--text-primary)]">Layanan Penghuni Aktif</h2>
                    <p class="mt-0.5 text-xs text-[var(--text-secondary)]">
                        Masa sewa berakhir pada <strong class="text-[var(--text-primary)] tabular-nums">{{ $booking->leaseEndDate()->format('d M Y') }}</strong>. Ajukan perpanjangan sewa cepat atau laporkan kendala kamar.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <form method="POST" action="{{ route('penyewa.bookings.renew', $booking) }}" class="flex items-center gap-2">
                        @csrf
                        <select name="duration_months" aria-label="Durasi perpanjangan" class="lk-input text-xs min-h-[40px] w-32">
                            <option value="1">1 Bulan</option>
                            <option value="3">3 Bulan</option>
                            <option value="6">6 Bulan</option>
                            <option value="12">12 Bulan</option>
                        </select>
                        <button type="submit" class="lk-btn-primary text-xs min-h-[40px] px-4">Perpanjang Sewa</button>
                    </form>
                    <a href="{{ route('penyewa.maintenance.index') }}" class="lk-btn-secondary text-xs min-h-[40px] px-4">
                        Laporkan Kendala Kamar
                    </a>
                </div>
            </section>
        @endif

        @if ($booking->payment)
            <section class="lk-card p-6">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 class="text-lg font-semibold text-[var(--text-primary)]">Status Bukti Pembayaran</h2>
                    <x-lokakos.status-badge :status="$booking->payment->status" />
                </div>
                <p class="mt-2 text-sm text-[var(--text-secondary)]">
                    Pengirim: <strong class="text-[var(--text-primary)]">{{ $booking->payment->sender_name }}</strong> ({{ $booking->payment->sender_bank }})
                </p>
                @if ($booking->payment->rejection_note)
                    <p class="mt-2 text-sm text-[var(--status-danger)] font-medium">
                        Alasan Penolakan Mitra: {{ $booking->payment->rejection_note }}
                    </p>
                @endif
                <div class="mt-4 flex flex-wrap gap-2">
                    <a href="{{ asset('storage/' . $booking->payment->proof_image_path) }}" target="_blank" class="lk-btn-secondary text-xs min-h-[40px]">
                        Lihat File Bukti Transfer
                    </a>
                    @if ($booking->canDownloadInvoice())
                        <a href="{{ $booking->verificationUrl() }}" target="_blank" class="lk-btn-secondary text-xs min-h-[40px]">
                            Halaman Verifikasi QR Invoice
                        </a>
                    @endif
                </div>
            </section>
        @endif

        @if ($booking->status === \App\Models\Booking::STATUS_PENDING_PAYMENT)
            <section class="lk-card p-6">
                <h2 class="text-lg font-semibold text-[var(--text-primary)]">Unggah Bukti Transfer Manual</h2>
                <p class="mt-1 text-sm text-[var(--text-secondary)]">Format yang diterima: JPG, PNG, atau PDF (maksimal 2 MB).</p>

                <form method="POST" action="{{ route('penyewa.bookings.payment', $booking) }}" enctype="multipart/form-data" class="mt-5 space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="sender_bank" value="Bank Pengirim" />
                            <x-text-input id="sender_bank" name="sender_bank" type="text" :value="old('sender_bank')" required placeholder="Contoh: BRI / BCA / BSI / E-Wallet" />
                            <x-input-error :messages="$errors->get('sender_bank')" />
                        </div>
                        <div>
                            <x-input-label for="sender_name" value="Nama Pemilik Rekening Pengirim" />
                            <x-text-input id="sender_name" name="sender_name" type="text" :value="old('sender_name', auth()->user()->name)" required />
                            <x-input-error :messages="$errors->get('sender_name')" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="proof_image" value="File Bukti Transfer (JPG/PNG/PDF, maks 2 MB)" />
                        <input id="proof_image" name="proof_image" type="file" accept=".jpg,.jpeg,.png,.pdf" required class="lk-input text-sm">
                        <x-input-error :messages="$errors->get('proof_image')" />
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                        <button type="submit" class="lk-btn-primary text-sm">Kirim Bukti Pembayaran</button>
                    </div>
                </form>

                <form method="POST" action="{{ route('penyewa.bookings.cancel', $booking) }}" class="mt-6 pt-5 border-t border-[var(--border-subtle)]" onsubmit="return confirm('Batalkan pesanan ini?');">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="text-sm font-semibold text-[var(--status-danger)] hover:underline">
                        Batalkan Pesanan Ini
                    </button>
                </form>
            </section>
        @endif

        @if ($booking->canBeReviewed())
            <section class="lk-card p-6">
                <h2 class="text-lg font-semibold text-[var(--text-primary)]">Tulis Ulasan Penghuni</h2>
                <p class="mt-1 text-sm text-[var(--text-secondary)]">Bagikan pengalaman tinggal Anda untuk membantu mahasiswa UMY lainnya.</p>

                <form method="POST" action="{{ route('penyewa.bookings.review', $booking) }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="rating" value="Penilaian (1 sampai 5)" />
                        <select id="rating" name="rating" required class="lk-input text-sm max-w-xs">
                            <option value="5">5 - Sangat baik</option>
                            <option value="4">4 - Baik</option>
                            <option value="3">3 - Cukup</option>
                            <option value="2">2 - Kurang</option>
                            <option value="1">1 - Tidak memuaskan</option>
                        </select>
                        <x-input-error :messages="$errors->get('rating')" />
                    </div>
                    <div>
                        <x-input-label for="comment" value="Komentar Ulasan" />
                        <textarea id="comment" name="comment" rows="3" class="lk-input text-sm" placeholder="Ceritakan kebersihan, fasilitas, dan suasana lingkungan kos">{{ old('comment') }}</textarea>
                        <x-input-error :messages="$errors->get('comment')" />
                    </div>
                    <button type="submit" class="lk-btn-primary text-sm">Kirim Ulasan</button>
                </form>
            </section>
        @elseif ($booking->review)
            <section class="lk-card p-6">
                <h2 class="text-lg font-semibold text-[var(--text-primary)]">Ulasan Anda</h2>
                <p class="mt-2 text-sm font-semibold text-[var(--brand-gold-text)] tabular-nums">Rating: {{ $booking->review->rating }} / 5</p>
                <p class="mt-1 text-sm text-[var(--text-secondary)]">{{ $booking->review->comment }}</p>
            </section>
        @endif
    </div>
</x-app-layout>
