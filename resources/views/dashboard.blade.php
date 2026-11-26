<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-semibold text-[var(--text-primary)]">
                    @if ($user->isAdmin())
                        Ringkasan Back-Office LokaKos
                    @elseif ($user->isMitra())
                        {{ $myKos->isEmpty() ? 'Selamat Datang, Mitra Pemilik Kos' : 'Dashboard Pengelolaan Kos' }}
                    @else
                        Ringkasan Sewa & Kos Incaran
                    @endif
                </h1>
                <p class="mt-1 text-sm text-[var(--text-secondary)]">
                    @if ($user->isAdmin())
                        Pantau antrean verifikasi Mitra, moderasi listing kos kawasan UMY, dan transaksi lintas pemilik kos.
                    @elseif ($user->isMitra())
                        {{ $user->mitraProfile?->business_name ?? $user->name }} · Akses terbatas hanya untuk properti dan pesanan milik Anda.
                    @else
                        Kelola pesanan kamar aktif, unggah bukti transfer, dan akses kos yang Anda simpan.
                    @endif
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                @if ($user->isAdmin())
                    <a href="{{ route('admin.mitra.index', ['status' => 'pending']) }}" class="lk-btn-primary text-sm">Verifikasi Mitra</a>
                    <a href="{{ route('admin.kos.index', ['status' => 'pending_review']) }}" class="lk-btn-secondary text-sm">Moderasi Kos</a>
                @elseif ($user->isMitra())
                    @if ($user->mitraProfile?->isApproved())
                        <a href="{{ route('mitra.kos.create') }}" class="lk-btn-primary text-sm">Tambah Properti Kos</a>
                    @else
                        <a href="{{ route('mitra.profile.edit') }}" class="lk-btn-primary text-sm">Lengkapi Profil Mitra</a>
                    @endif
                @else
                    <a href="{{ route('kos.index') }}" class="lk-btn-primary text-sm">Cari Kos UMY</a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        @if ($user->isAdmin())
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="lk-card p-5">
                    <p class="text-xs font-semibold text-[var(--text-secondary)]">Kos Tayang / Menunggu</p>
                    <p class="mt-2 font-display text-2xl sm:text-3xl font-semibold text-[var(--text-primary)] tabular-nums">
                        {{ $counts['published_kos'] }}
                        <span class="text-sm font-medium text-[var(--brand-gold-text)]">/ {{ $counts['pending_kos'] }} pending</span>
                    </p>
                </div>
                <div class="lk-card p-5">
                    <p class="text-xs font-semibold text-[var(--text-secondary)]">Mitra Aktif / Menunggu</p>
                    <p class="mt-2 font-display text-2xl sm:text-3xl font-semibold text-[var(--text-primary)] tabular-nums">
                        {{ $counts['approved_mitra'] }}
                        <span class="text-sm font-medium text-[var(--brand-gold-text)]">/ {{ $counts['pending_mitra'] }} pending</span>
                    </p>
                </div>
                <div class="lk-card p-5">
                    <p class="text-xs font-semibold text-[var(--text-secondary)]">Total Pengguna & Area</p>
                    <p class="mt-2 font-display text-2xl sm:text-3xl font-semibold text-[var(--text-primary)] tabular-nums">
                        {{ $counts['total_users'] }}
                        <span class="text-sm font-medium text-[var(--text-secondary)]">akun · {{ $counts['total_areas'] }} area</span>
                    </p>
                </div>
                <div class="lk-card p-5">
                    <p class="text-xs font-semibold text-[var(--text-secondary)]">Transaksi Terkonfirmasi</p>
                    <p class="mt-2 font-display text-xl sm:text-2xl font-semibold text-[var(--text-primary)] tabular-nums">
                        Rp {{ number_format($counts['confirmed_revenue'], 0, ',', '.') }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <section class="lk-card p-6">
                    <div class="flex items-center justify-between gap-2 pb-4 border-b border-[var(--border-subtle)]">
                        <div>
                            <h2 class="text-lg font-semibold text-[var(--text-primary)]">Antrean Verifikasi Mitra Baru</h2>
                            <p class="text-xs text-[var(--text-secondary)] tabular-nums">{{ $pendingMitra->count() }} pendaftaran menunggu</p>
                        </div>
                        <a href="{{ route('admin.mitra.index') }}" class="text-xs font-semibold text-[var(--brand-gold-text)] hover:underline">Kelola Semua</a>
                    </div>

                    @if ($pendingMitra->isNotEmpty())
                        <ul class="mt-3 divide-y divide-[var(--border-subtle)]">
                            @foreach ($pendingMitra as $profile)
                                <li class="py-3.5 flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-[var(--text-primary)]">{{ $profile->business_name }}</p>
                                        <p class="text-xs text-[var(--text-secondary)]">{{ $profile->user?->name }} · {{ $profile->bank_name }} ({{ $profile->bank_account_number }})</p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <form method="POST" action="{{ route('admin.mitra.approve', $profile) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="lk-btn-primary text-xs min-h-[38px] px-4">Setujui</button>
                                        </form>
                                        <a href="{{ route('admin.mitra.index', ['status' => 'pending']) }}" class="lk-btn-secondary text-xs min-h-[38px] px-3.5">Tinjau</a>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="mt-4 text-sm text-[var(--text-secondary)]">Seluruh pendaftaran Mitra sudah diverifikasi.</p>
                    @endif
                </section>

                <section class="lk-card p-6">
                    <div class="flex items-center justify-between gap-2 pb-4 border-b border-[var(--border-subtle)]">
                        <div>
                            <h2 class="text-lg font-semibold text-[var(--text-primary)]">Antrean Moderasi Listing Kos</h2>
                            <p class="text-xs text-[var(--text-secondary)] tabular-nums">{{ $pendingKos->count() }} properti menunggu</p>
                        </div>
                        <a href="{{ route('admin.kos.index') }}" class="text-xs font-semibold text-[var(--brand-gold-text)] hover:underline">Kelola Semua</a>
                    </div>

                    @if ($pendingKos->isNotEmpty())
                        <ul class="mt-3 divide-y divide-[var(--border-subtle)]">
                            @foreach ($pendingKos as $item)
                                <li class="py-3.5 flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-[var(--text-primary)]">{{ $item->name }}</p>
                                        <p class="text-xs text-[var(--text-secondary)]">{{ $item->area?->name }} · {{ $item->mitra?->name }} · {{ $item->roomTypes->count() }} tipe kamar</p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <form method="POST" action="{{ route('admin.kos.approve', $item) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="lk-btn-primary text-xs min-h-[38px] px-4">Tayangkan</button>
                                        </form>
                                        <a href="{{ route('admin.kos.index', ['status' => 'pending_review']) }}" class="lk-btn-secondary text-xs min-h-[38px] px-3.5">Detail</a>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="mt-4 text-sm text-[var(--text-secondary)]">Tidak ada listing kos yang menunggu moderasi.</p>
                    @endif
                </section>
            </div>

            <section class="lk-card p-6">
                <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-[var(--border-subtle)]">
                    <h2 class="text-lg font-semibold text-[var(--text-primary)]">Transaksi Pemesanan Terbaru</h2>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.monitoring.index') }}" class="lk-btn-secondary text-xs min-h-[38px]">Buka Monitoring</a>
                        <a href="{{ route('admin.report.pdf') }}" class="lk-btn-espresso text-xs min-h-[38px]">Unduh Laporan PDF</a>
                    </div>
                </div>

                @if ($recentBookings->isNotEmpty())
                    <div class="mt-3 overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="border-b border-[var(--border-subtle)] text-xs text-[var(--text-secondary)]">
                                <tr>
                                    <th class="py-2.5 pr-4">Kode</th>
                                    <th class="py-2.5 pr-4">Penyewa</th>
                                    <th class="py-2.5 pr-4">Kos & Mitra</th>
                                    <th class="py-2.5 pr-4">Total</th>
                                    <th class="py-2.5">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[var(--border-subtle)]">
                                @foreach ($recentBookings as $b)
                                    <tr>
                                        <td class="py-3 pr-4 font-semibold tabular-nums">{{ $b->booking_code }}</td>
                                        <td class="py-3 pr-4">{{ $b->user?->name }}</td>
                                        <td class="py-3 pr-4">{{ $b->roomType?->boardingHouse?->name }} ({{ $b->roomType?->boardingHouse?->mitra?->name }})</td>
                                        <td class="py-3 pr-4 font-semibold tabular-nums">Rp {{ number_format($b->total_price, 0, ',', '.') }}</td>
                                        <td class="py-3"><x-lokakos.status-badge :status="$b->status" /></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="mt-4 text-sm text-[var(--text-secondary)]">Belum ada transaksi pemesanan pada platform.</p>
                @endif
            </section>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <section class="lk-card p-6 space-y-3">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <h2 class="text-base font-semibold text-[var(--text-primary)]">Okupansi Kamar per Area Sekitar UMY</h2>
                            <p class="text-xs text-[var(--text-secondary)]">Perbandingan kamar terisi dan kamar tersedia di setiap dusun.</p>
                        </div>
                        <a href="{{ route('admin.report.csv') }}" class="text-xs font-semibold text-[var(--brand-gold-text)] hover:underline">Ekspor CSV</a>
                    </div>
                    <div class="h-60">
                        <canvas id="admin-area-chart"></canvas>
                    </div>
                </section>

                <section class="lk-card p-6 space-y-3">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <h2 class="text-base font-semibold text-[var(--text-primary)]">Audit Trail Aktivitas Terbaru</h2>
                            <p class="text-xs text-[var(--text-secondary)]">Laporan terbuka: <strong class="text-[var(--status-danger)] tabular-nums">{{ $counts['open_reports'] ?? 0 }}</strong></p>
                        </div>
                        <div class="flex gap-3 text-xs font-semibold">
                            <a href="{{ route('admin.reports.index') }}" class="text-[var(--brand-gold-text)] hover:underline">Laporan Listing</a>
                            <a href="{{ route('admin.audit.index') }}" class="text-[var(--brand-gold-text)] hover:underline">Semua Log</a>
                        </div>
                    </div>

                    @if (($recentLogs ?? collect())->isNotEmpty())
                        <ul class="divide-y divide-[var(--border-subtle)] text-xs">
                            @foreach ($recentLogs as $log)
                                <li class="py-2.5 flex items-start justify-between gap-3">
                                    <div>
                                        <span class="font-semibold text-[var(--text-primary)]">{{ $log->action }}</span>
                                        <p class="text-[var(--text-secondary)]">{{ $log->description }}</p>
                                    </div>
                                    <span class="text-[11px] text-[var(--text-secondary)] tabular-nums shrink-0">{{ $log->created_at?->format('d/m H:i') }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-xs text-[var(--text-secondary)] pt-2">Belum ada log aktivitas tercatat.</p>
                    @endif
                </section>
            </div>
        @elseif ($user->isMitra())
            @if ($myKos->isEmpty())
                <section class="rounded-[var(--radius-panel)] bg-[var(--bg-espresso)] text-[var(--espresso-ink)] p-7 sm:p-10 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                    <div class="lg:col-span-7">
                        @if ($user->mitraProfile?->isApproved())
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[var(--espresso-gold)]">Akun Mitra Terverifikasi</p>
                            <h2 class="mt-2 font-display text-2xl sm:text-4xl font-semibold leading-tight text-[var(--espresso-ink)]">
                                Tambahkan properti kos pertama Anda untuk mulai menerima pesanan
                            </h2>
                            <p class="mt-3 text-sm sm:text-base text-[var(--espresso-muted)] leading-relaxed">
                                Anda belum memiliki data properti kos. Mulai dengan mengisi profil kos, menaruh pin peta sekitar UMY, lalu menambahkan tipe kamar, tarif, listrik, dan stok kamar tersedia.
                            </p>
                            <div class="mt-6 flex flex-wrap gap-3">
                                <a href="{{ route('mitra.kos.create') }}" class="lk-btn-primary">
                                    Tambah Properti Kos Pertama
                                </a>
                                <a href="{{ route('mitra.profile.edit') }}" class="inline-flex items-center justify-center min-h-[46px] px-5 rounded-full border border-white/35 text-sm font-medium text-[var(--espresso-ink)] hover:bg-white/10 transition-colors">
                                    Cek Rekening Transfer
                                </a>
                            </div>
                        @else
                            <div class="flex flex-wrap items-center gap-2.5">
                                <span class="text-xs font-semibold uppercase tracking-[0.14em] text-[var(--espresso-gold)]">Tahap Verifikasi Akun</span>
                                @if ($user->mitraProfile)
                                    <x-lokakos.status-badge :status="$user->mitraProfile->verification_status" />
                                @endif
                            </div>
                            <h2 class="mt-3 font-display text-2xl sm:text-3xl font-semibold leading-tight text-[var(--espresso-ink)]">
                                Pendaftaran Mitra Anda sedang ditinjau oleh Admin
                            </h2>
                            <p class="mt-3 text-sm sm:text-base text-[var(--espresso-muted)] leading-relaxed">
                                Sambil menunggu persetujuan Admin, pastikan data nama usaha, nomor WhatsApp, rekening bank penerima, dan berkas KTP Anda sudah lengkap. Setelah disetujui, tombol tambah kos dan kamar akan aktif di sini.
                            </p>
                            @if ($user->mitraProfile?->rejection_note)
                                <p class="mt-3 p-3.5 rounded-[var(--radius-control)] bg-black/35 border border-white/15 text-sm text-[#F28E86]">
                                    Catatan Admin: {{ $user->mitraProfile->rejection_note }}
                                </p>
                            @endif
                            <div class="mt-6">
                                <a href="{{ route('mitra.profile.edit') }}" class="lk-btn-primary">
                                    Lengkapi Profil, Rekening & KTP
                                </a>
                            </div>
                        @endif
                    </div>

                    <div class="lg:col-span-5 rounded-[var(--radius-card)] bg-black/25 border border-white/15 p-6 space-y-3 text-sm">
                        <p class="font-semibold text-[var(--espresso-gold)]">Tahapan Mulai Berjualan di LokaKos</p>
                        <ol class="space-y-2.5 text-[var(--espresso-muted)] list-decimal list-inside">
                            <li>Verifikasi akun Mitra dan rekening penerima oleh Admin.</li>
                            <li>Tambahkan properti kos, foto asli, dan pin lokasi peta UMY.</li>
                            <li>Tambahkan tipe kamar, kapasitas, status listrik, deposit, dan jumlah stok.</li>
                            <li>Terima pesanan mahasiswa, cek bukti transfer, dan konfirmasi check-in.</li>
                        </ol>
                    </div>
                </section>
            @else
                @if ($kosWithoutRooms->isNotEmpty())
                    <div class="p-4 rounded-[var(--radius-card)] bg-[var(--accent-warm-bg)] text-[var(--accent-warm-ink)] border border-[var(--border-subtle)] flex flex-wrap items-center justify-between gap-3 text-sm">
                        <span>
                            Properti <strong>{{ $kosWithoutRooms->first()->name }}</strong> belum memiliki tipe kamar. Tambahkan tipe kamar agar mahasiswa dapat memesan.
                        </span>
                        <a href="{{ route('mitra.rooms.index', $kosWithoutRooms->first()) }}" class="lk-btn-primary text-xs min-h-[38px] px-4">
                            Tambah Tipe Kamar
                        </a>
                    </div>
                @endif

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="lk-card p-5">
                        <p class="text-xs font-semibold text-[var(--text-secondary)]">Properti Kos Saya</p>
                        <p class="mt-2 font-display text-2xl sm:text-3xl font-semibold text-[var(--text-primary)] tabular-nums">
                            {{ $mitraStats['total_kos'] }}
                            <span class="text-sm font-medium text-[var(--brand-secondary)]">({{ $mitraStats['published_kos'] }} tayang)</span>
                        </p>
                    </div>
                    <div class="lk-card p-5">
                        <p class="text-xs font-semibold text-[var(--text-secondary)]">Okupansi Kamar</p>
                        <p class="mt-2 font-display text-2xl sm:text-3xl font-semibold text-[var(--text-primary)] tabular-nums">
                            {{ $mitraStats['occupied_rooms'] }} / {{ $mitraStats['total_rooms'] }}
                            <span class="text-xs font-medium text-[var(--text-secondary)] block mt-0.5">{{ $mitraStats['available_rooms'] }} kamar tersedia</span>
                        </p>
                    </div>
                    <div class="lk-card p-5">
                        <p class="text-xs font-semibold text-[var(--text-secondary)]">Menunggu Verifikasi Bayar</p>
                        <p class="mt-2 font-display text-2xl sm:text-3xl font-semibold text-[var(--brand-gold-text)] tabular-nums">
                            {{ $mitraStats['waiting_verification'] }}
                            <span class="text-xs font-medium text-[var(--text-secondary)]">pesanan</span>
                        </p>
                    </div>
                    <div class="lk-card p-5">
                        <p class="text-xs font-semibold text-[var(--text-secondary)]">Pemasukan Terkonfirmasi</p>
                        <p class="mt-2 font-display text-xl sm:text-2xl font-semibold text-[var(--text-primary)] tabular-nums">
                            Rp {{ number_format($mitraStats['confirmed_income'], 0, ',', '.') }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                    <a href="{{ route('mitra.surveys.index') }}" class="lk-card p-4 hover:border-[var(--brand-gold)] transition-colors flex items-center justify-between gap-2">
                        <span class="text-[var(--text-secondary)] font-medium">Survei Menunggu</span>
                        <strong class="text-base text-[var(--text-primary)] tabular-nums">{{ $mitraStats['pending_surveys'] ?? 0 }}</strong>
                    </a>
                    <a href="{{ route('mitra.tickets.index') }}" class="lk-card p-4 hover:border-[var(--brand-gold)] transition-colors flex items-center justify-between gap-2">
                        <span class="text-[var(--text-secondary)] font-medium">Keluhan Terbuka</span>
                        <strong class="text-base text-[var(--text-primary)] tabular-nums">{{ $mitraStats['open_tickets'] ?? 0 }}</strong>
                    </a>
                    <a href="{{ route('mitra.announcements.index') }}#tanya-jawab" class="lk-card p-4 hover:border-[var(--brand-gold)] transition-colors flex items-center justify-between gap-2">
                        <span class="text-[var(--text-secondary)] font-medium">Q&A Belum Dijawab</span>
                        <strong class="text-base text-[var(--text-primary)] tabular-nums">{{ $mitraStats['unanswered_questions'] ?? 0 }}</strong>
                    </a>
                    <a href="{{ route('mitra.expenses.index') }}" class="lk-card p-4 hover:border-[var(--brand-gold)] transition-colors flex items-center justify-between gap-2">
                        <span class="text-[var(--text-secondary)] font-medium">Estimasi Laba Bersih</span>
                        <strong class="text-sm text-[var(--brand-secondary-ink)] tabular-nums">Rp {{ number_format($mitraStats['net_income'] ?? 0, 0, ',', '.') }}</strong>
                    </a>
                </div>

                <section class="lk-card p-6 space-y-3">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <h2 class="text-base font-semibold text-[var(--text-primary)]">Arus Kas 6 Bulan Terakhir (Pemasukan vs Pengeluaran)</h2>
                            <p class="text-xs text-[var(--text-secondary)]">Rekap sewa terkonfirmasi dibanding pengeluaran operasional kos Anda.</p>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('mitra.report.csv') }}" class="lk-btn-secondary text-xs min-h-[36px] px-3.5">CSV (Excel)</a>
                            <a href="{{ route('mitra.expenses.index') }}" class="lk-btn-secondary text-xs min-h-[36px] px-3.5">Kelola Buku Kas</a>
                        </div>
                    </div>
                    <div class="h-56">
                        <canvas id="mitra-cashflow-chart"></canvas>
                    </div>
                </section>

                @if ($pendingBookings->isNotEmpty())
                    <section class="lk-card p-6">
                        <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-[var(--border-subtle)]">
                            <div>
                                <h2 class="text-lg font-semibold text-[var(--text-primary)]">Perlu Tindakan: Verifikasi Bukti Transfer</h2>
                                <p class="text-xs text-[var(--text-secondary)]">Setujui setelah mutasi masuk ke rekening {{ $user->mitraProfile?->bank_name }} Anda.</p>
                            </div>
                            <a href="{{ route('mitra.bookings.index', ['status' => 'waiting_verification']) }}" class="text-xs font-semibold text-[var(--brand-gold-text)] hover:underline">Buka Halaman Pesanan</a>
                        </div>

                        <ul class="mt-3 divide-y divide-[var(--border-subtle)]">
                            @foreach ($pendingBookings as $booking)
                                <li class="py-4 flex flex-wrap items-center justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-semibold text-[var(--text-primary)]">{{ $booking->booking_code }} · {{ $booking->user?->name }}</p>
                                        <p class="text-xs text-[var(--text-secondary)] tabular-nums">
                                            {{ $booking->roomType?->boardingHouse?->name }} ({{ $booking->roomType?->name }}) · Rp {{ number_format($booking->total_price, 0, ',', '.') }}
                                        </p>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        @if ($booking->payment)
                                            <a href="{{ asset('storage/' . $booking->payment->proof_image_path) }}" target="_blank" class="lk-btn-secondary text-xs min-h-[38px] px-3.5">Lihat Bukti</a>
                                        @endif
                                        <form method="POST" action="{{ route('mitra.bookings.approve', $booking) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="lk-btn-primary text-xs min-h-[38px] px-4">Setujui</button>
                                        </form>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                <section class="space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 class="text-xl font-semibold text-[var(--text-primary)]">Properti Kos & Rincian Kamar Milik Anda</h2>
                            <p class="text-xs text-[var(--text-secondary)]">Sesuaikan stok kamar atau buka form edit kamar secara langsung.</p>
                        </div>
                        <a href="{{ route('mitra.kos.index') }}" class="lk-btn-secondary text-xs min-h-[40px]">Kelola Semua Properti</a>
                    </div>

                    <div class="space-y-5">
                        @foreach ($myKos as $kos)
                            <div class="lk-card overflow-hidden">
                                <div class="p-5 sm:p-6 bg-[var(--bg-tint)] border-b border-[var(--border-subtle)] flex flex-wrap items-center justify-between gap-4">
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2.5">
                                            <h3 class="text-lg font-semibold text-[var(--text-primary)]">{{ $kos->name }}</h3>
                                            <x-lokakos.status-badge :status="$kos->status" />
                                        </div>
                                        <p class="mt-1 text-xs text-[var(--text-secondary)]">
                                            {{ $kos->area?->name }} · Kos {{ ucfirst($kos->gender_type) }} {{ $kos->is_syariah ? '· Syariah' : '' }}
                                            @if ($kos->distance_to_umy_km !== null)
                                                · {{ number_format($kos->distance_to_umy_km, 2, ',', '.') }} km dari UMY
                                            @endif
                                        </p>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('mitra.rooms.index', $kos) }}" class="lk-btn-primary text-xs min-h-[38px] px-4">Tambah / Kelola Kamar</a>
                                        <a href="{{ route('mitra.kos.edit', $kos) }}" class="lk-btn-secondary text-xs min-h-[38px] px-4">Edit Kos</a>
                                        @if ($kos->status === \App\Models\BoardingHouse::STATUS_PUBLISHED)
                                            <a href="{{ route('mitra.kos.poster-qr', $kos) }}" class="lk-btn-secondary text-xs min-h-[38px] px-3.5">Poster QR</a>
                                        @endif
                                    </div>
                                </div>

                                <div class="p-5 sm:p-6">
                                    @if ($kos->roomTypes->isNotEmpty())
                                        <div class="divide-y divide-[var(--border-subtle)]">
                                            @foreach ($kos->roomTypes as $room)
                                                <div class="py-3.5 first:pt-0 last:pb-0 flex flex-col md:flex-row md:items-center justify-between gap-4">
                                                    <div>
                                                        <p class="text-sm font-semibold text-[var(--text-primary)]">{{ $room->name }}</p>
                                                        <p class="mt-0.5 text-xs text-[var(--text-secondary)] tabular-nums">
                                                            Rp {{ number_format($room->price_monthly, 0, ',', '.') }}/bln ·
                                                            Maks {{ $room->max_occupancy }} org ·
                                                            {{ $room->electricity_included ? 'Termasuk listrik' : 'Listrik token mandiri' }}
                                                            @if ($room->deposit_amount > 0)
                                                                · Deposit Rp {{ number_format($room->deposit_amount, 0, ',', '.') }}
                                                            @endif
                                                        </p>
                                                    </div>

                                                    <div class="flex flex-wrap items-center gap-3">
                                                        <div class="inline-flex items-center gap-1.5 rounded-full border border-[var(--border-control)] bg-[var(--bg-canvas)] px-2 py-1">
                                                            <form method="POST" action="{{ route('mitra.rooms.stock', [$kos, $room]) }}">
                                                                @csrf
                                                                @method('PATCH')
                                                                <input type="hidden" name="delta" value="-1">
                                                                <button
                                                                    type="submit"
                                                                    @disabled($room->available_rooms <= 0)
                                                                    aria-label="Kurangi stok {{ $room->name }}"
                                                                    class="w-7 h-7 rounded-full inline-flex items-center justify-center text-sm font-bold hover:bg-[var(--bg-tint)] disabled:opacity-40"
                                                                >-</button>
                                                            </form>
                                                            <span class="px-2 text-xs font-semibold tabular-nums text-[var(--text-primary)]">
                                                                Tersedia {{ $room->available_rooms }}/{{ $room->total_rooms }}
                                                            </span>
                                                            <form method="POST" action="{{ route('mitra.rooms.stock', [$kos, $room]) }}">
                                                                @csrf
                                                                @method('PATCH')
                                                                <input type="hidden" name="delta" value="1">
                                                                <button
                                                                    type="submit"
                                                                    @disabled($room->available_rooms >= $room->total_rooms)
                                                                    aria-label="Tambah stok {{ $room->name }}"
                                                                    class="w-7 h-7 rounded-full inline-flex items-center justify-center text-sm font-bold hover:bg-[var(--bg-tint)] disabled:opacity-40"
                                                                >+</button>
                                                            </form>
                                                        </div>

                                                        <a href="{{ route('mitra.rooms.edit', [$kos, $room]) }}" class="lk-btn-secondary text-xs min-h-[36px] px-3.5">
                                                            Edit Kamar
                                                        </a>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-sm text-[var(--text-secondary)]">Belum ada tipe kamar. Klik tombol Tambah / Kelola Kamar di atas.</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                @if ($activeTenants->isNotEmpty())
                    <section class="lk-card p-6">
                        <h2 class="text-lg font-semibold text-[var(--text-primary)] pb-3 border-b border-[var(--border-subtle)]">Penghuni Terkonfirmasi Terbaru</h2>
                        <ul class="mt-3 divide-y divide-[var(--border-subtle)] text-sm">
                            @foreach ($activeTenants as $tenantBooking)
                                <li class="py-3 flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <p class="font-semibold text-[var(--text-primary)]">{{ $tenantBooking->user?->name }}</p>
                                        <p class="text-xs text-[var(--text-secondary)] tabular-nums">
                                            {{ $tenantBooking->roomType?->boardingHouse?->name }} ({{ $tenantBooking->roomType?->name }}) · Masuk {{ $tenantBooking->check_in_date->format('d M Y') }}
                                        </p>
                                    </div>
                                    <x-lokakos.status-badge :status="$tenantBooking->status" />
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif
            @endif
        @else
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="lk-card p-5">
                    <p class="text-xs font-semibold text-[var(--text-secondary)]">Pesanan Aktif</p>
                    <p class="mt-2 font-display text-3xl font-semibold text-[var(--text-primary)] tabular-nums">{{ $penyewaStats['active_bookings'] }}</p>
                </div>
                <div class="lk-card p-5">
                    <p class="text-xs font-semibold text-[var(--text-secondary)]">Total Riwayat Pemesanan</p>
                    <p class="mt-2 font-display text-3xl font-semibold text-[var(--text-primary)] tabular-nums">{{ $penyewaStats['total_bookings'] }}</p>
                </div>
                <div class="lk-card p-5">
                    <p class="text-xs font-semibold text-[var(--text-secondary)]">Kos Tersimpan (Favorit)</p>
                    <p class="mt-2 font-display text-3xl font-semibold text-[var(--text-primary)] tabular-nums">{{ $penyewaStats['saved_count'] }}</p>
                </div>
            </div>

            @if ($activeBooking)
                @php
                    $stepOrder = [
                        'pending_payment' => 1,
                        'waiting_verification' => 2,
                        'confirmed' => 3,
                        'completed' => 4,
                    ];
                    $currentStep = $stepOrder[$activeBooking->status] ?? 1;
                @endphp
                <section class="lk-card p-6 sm:p-7 space-y-5">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <div class="flex flex-wrap items-center gap-2.5">
                                <span class="text-xs font-bold text-[var(--brand-gold-text)] tabular-nums">{{ $activeBooking->booking_code }}</span>
                                <x-lokakos.status-badge :status="$activeBooking->status" />
                            </div>
                            <h2 class="mt-2 text-xl font-semibold text-[var(--text-primary)]">
                                {{ $activeBooking->roomType?->boardingHouse?->name }} · {{ $activeBooking->roomType?->name }}
                            </h2>
                            <p class="mt-1 text-sm text-[var(--text-secondary)] tabular-nums">
                                Rencana masuk {{ $activeBooking->check_in_date->format('d M Y') }} · {{ $activeBooking->duration_months }} bulan · Rp {{ number_format($activeBooking->total_price, 0, ',', '.') }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            @if ($activeBooking->canDownloadInvoice())
                                <a href="{{ route('penyewa.bookings.invoice', $activeBooking) }}" class="lk-btn-secondary text-xs min-h-[40px]">Unduh Invoice PDF</a>
                            @endif
                            <a href="{{ route('penyewa.bookings.show', $activeBooking) }}" class="lk-btn-primary text-xs min-h-[40px]">
                                {{ $activeBooking->status === \App\Models\Booking::STATUS_PENDING_PAYMENT ? 'Unggah Bukti Transfer' : 'Lihat Detail Pesanan' }}
                            </a>
                        </div>
                    </div>

                    <ol class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-4 border-t border-[var(--border-subtle)] text-xs">
                        @foreach ([
                            1 => '1. Ajukan & Transfer',
                            2 => '2. Verifikasi Mitra',
                            3 => '3. Invoice Aktif',
                            4 => '4. Check-In & Ulasan',
                        ] as $idx => $label)
                            <li class="p-3 rounded-[var(--radius-control)] border {{ $currentStep >= $idx ? 'bg-[var(--bg-tint)] border-[var(--brand-gold)] font-semibold text-[var(--text-primary)]' : 'border-[var(--border-subtle)] text-[var(--text-secondary)]' }}">
                                {{ $label }}
                            </li>
                        @endforeach
                    </ol>
                </section>
            @else
                <x-lokakos.empty-state
                    title="Belum ada pemesanan kamar aktif"
                    description="Jelajahi katalog kos di kawasan UMY, simpan kos incaran Anda, lalu ajukan pemesanan langsung dari halaman detail kos."
                    action-label="Cari Kos Sekitar UMY"
                    :action-href="route('kos.index')"
                />
            @endif

            @if (($announcements ?? collect())->isNotEmpty())
                <section class="lk-card p-6 space-y-3">
                    <h2 class="text-lg font-semibold text-[var(--text-primary)]">Papan Pengumuman Kos Anda</h2>
                    <div class="divide-y divide-[var(--border-subtle)]">
                        @foreach ($announcements as $ann)
                            <div class="py-3 first:pt-0 last:pb-0">
                                <p class="text-xs font-semibold text-[var(--brand-gold-text)]">{{ $ann->boardingHouse->name }} · {{ $ann->created_at->format('d M Y') }}</p>
                                <h3 class="text-sm font-semibold text-[var(--text-primary)] mt-0.5">{{ $ann->title }}</h3>
                                <p class="text-xs text-[var(--text-secondary)] mt-1 leading-relaxed">{{ $ann->body }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            @if (($upcomingSurveys ?? collect())->isNotEmpty())
                <section class="lk-card p-6 space-y-3">
                    <div class="flex items-center justify-between gap-2">
                        <h2 class="text-lg font-semibold text-[var(--text-primary)]">Jadwal Survei Lokasi Anda</h2>
                        <a href="{{ route('penyewa.surveys.index') }}" class="text-xs font-semibold text-[var(--brand-gold-text)] hover:underline">Lihat Semua</a>
                    </div>
                    <ul class="divide-y divide-[var(--border-subtle)] text-sm">
                        @foreach ($upcomingSurveys as $sv)
                            <li class="py-3 flex flex-wrap items-center justify-between gap-2">
                                <div>
                                    <p class="font-semibold text-[var(--text-primary)]">{{ $sv->boardingHouse->name }}</p>
                                    <p class="text-xs text-[var(--text-secondary)] tabular-nums">{{ $sv->preferred_date->format('d M Y') }} · {{ $sv->preferred_time }}</p>
                                </div>
                                <x-lokakos.status-badge :status="$sv->status" />
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <section class="space-y-4">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-xl font-semibold text-[var(--text-primary)]">Kos Tersimpan (Favorit Anda)</h2>
                    <a href="{{ route('penyewa.saved.index') }}" class="text-xs font-semibold text-[var(--brand-gold-text)] hover:underline">Lihat Semua Favorit</a>
                </div>

                @if ($savedKos->isNotEmpty())
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach ($savedKos as $kos)
                            <x-lokakos.kos-card :kos="$kos" />
                        @endforeach
                    </div>
                @else
                    <div class="lk-card p-6 text-sm text-[var(--text-secondary)]">
                        Anda belum menyimpan kos favorit. Klik tombol <strong>Simpan Kos</strong> pada halaman detail kos untuk membandingkannya dengan mudah.
                    </div>
                @endif
            </section>
        @endif
    </div>

    @if ($user->isAdmin() || ($user->isMitra() && $myKos->isNotEmpty()))
        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js" crossorigin="anonymous"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    if (typeof Chart === 'undefined') return;
                    var isDark = document.documentElement.classList.contains('dark');
                    var gridColor = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(61,49,44,0.08)';
                    var textColor = isDark ? '#C7BDB3' : '#57534E';

                    var adminCanvas = document.getElementById('admin-area-chart');
                    if (adminCanvas) {
                        var areaData = @json($areaChart ?? []);
                        new Chart(adminCanvas, {
                            type: 'bar',
                            data: {
                                labels: areaData.map(function (d) { return d.area; }),
                                datasets: [
                                    { label: 'Terisi', data: areaData.map(function (d) { return d.occupied; }), backgroundColor: '#3D312C', borderRadius: 6 },
                                    { label: 'Tersedia', data: areaData.map(function (d) { return d.available; }), backgroundColor: '#CAA05C', borderRadius: 6 }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: { legend: { labels: { color: textColor, font: { size: 11 } } } },
                                scales: {
                                    x: { ticks: { color: textColor, font: { size: 11 } }, grid: { display: false } },
                                    y: { beginAtZero: true, ticks: { color: textColor, precision: 0 }, grid: { color: gridColor } }
                                }
                            }
                        });
                    }

                    var mitraCanvas = document.getElementById('mitra-cashflow-chart');
                    if (mitraCanvas) {
                        var cashData = @json($mitraMonthlyChart ?? []);
                        new Chart(mitraCanvas, {
                            type: 'bar',
                            data: {
                                labels: cashData.map(function (d) { return d.label; }),
                                datasets: [
                                    { label: 'Pemasukan Sewa', data: cashData.map(function (d) { return d.income; }), backgroundColor: '#CAA05C', borderRadius: 6 },
                                    { label: 'Pengeluaran Kos', data: cashData.map(function (d) { return d.expense; }), backgroundColor: '#9E2A25', borderRadius: 6 }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: { legend: { labels: { color: textColor, font: { size: 11 } } } },
                                scales: {
                                    x: { ticks: { color: textColor, font: { size: 11 } }, grid: { display: false } },
                                    y: { beginAtZero: true, ticks: { color: textColor }, grid: { color: gridColor } }
                                }
                            }
                        });
                    }
                });
            </script>
        @endpush
    @endif
</x-app-layout>
