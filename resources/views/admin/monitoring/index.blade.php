<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-[var(--text-primary)]">Monitoring Transaksi, Pengguna, & Ulasan</h1>
                <p class="mt-1 text-sm text-[var(--text-secondary)]">Pantau transaksi lintas Mitra, kelola status aktif akun, dan moderasi ulasan penghuni.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.report.csv') }}" class="lk-btn-secondary text-sm">Ekspor CSV (Excel)</a>
                <a href="{{ route('admin.report.pdf') }}" class="lk-btn-espresso text-sm">Unduh Laporan Platform PDF</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        <section class="lk-card p-6">
            <h2 class="text-lg font-semibold text-[var(--text-primary)] mb-4">Transaksi Pemesanan Lintas Mitra</h2>
            @if ($bookings->isNotEmpty())
                <div class="overflow-x-auto">
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
                            @foreach ($bookings as $booking)
                                <tr>
                                    <td class="py-3 pr-4 font-semibold tabular-nums">{{ $booking->booking_code }}</td>
                                    <td class="py-3 pr-4">{{ $booking->user?->name }}</td>
                                    <td class="py-3 pr-4">
                                        <div>{{ $booking->roomType?->boardingHouse?->name }}</div>
                                        <div class="text-xs text-[var(--text-secondary)]">{{ $booking->roomType?->boardingHouse?->mitra?->name }}</div>
                                    </td>
                                    <td class="py-3 pr-4 font-semibold tabular-nums">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</td>
                                    <td class="py-3"><x-lokakos.status-badge :status="$booking->status" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $bookings->links() }}</div>
            @else
                <p class="text-sm text-[var(--text-secondary)]">Belum ada transaksi pemesanan yang tercatat.</p>
            @endif
        </section>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <section class="lk-card p-6">
                <h2 class="text-lg font-semibold text-[var(--text-primary)] mb-4">Daftar Pengguna ({{ $users->count() }})</h2>
                <ul class="divide-y divide-[var(--border-subtle)] text-sm">
                    @foreach ($users as $account)
                        <li class="py-3 flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p class="font-semibold text-[var(--text-primary)]">
                                    {{ $account->name }}
                                    <span class="ml-1.5 text-xs font-normal text-[var(--text-secondary)]">({{ ucfirst($account->role) }})</span>
                                </p>
                                <p class="text-xs text-[var(--text-secondary)]">{{ $account->email }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-lokakos.status-badge :status="$account->is_active ? 'published' : 'rejected'" :label="$account->is_active ? 'Aktif' : 'Nonaktif'" />
                                @if ($account->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.toggle', $account) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="lk-btn-secondary text-xs min-h-[36px] px-3">
                                            {{ $account->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>

            <section class="lk-card p-6">
                <h2 class="text-lg font-semibold text-[var(--text-primary)] mb-4">Moderasi Ulasan Penghuni</h2>
                @if ($reviews->isNotEmpty())
                    <ul class="divide-y divide-[var(--border-subtle)] text-sm">
                        @foreach ($reviews as $review)
                            <li class="py-3 flex flex-wrap items-start justify-between gap-3">
                                <div class="max-w-sm">
                                    <p class="font-semibold text-[var(--text-primary)]">{{ $review->boardingHouse?->name }} ({{ $review->rating }}/5)</p>
                                    <p class="text-xs text-[var(--text-secondary)]">Oleh {{ $review->user?->name }}</p>
                                    <p class="mt-1 text-xs text-[var(--text-secondary)]">{{ $review->comment }}</p>
                                </div>
                                <form method="POST" action="{{ route('admin.reviews.toggle', $review) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="lk-btn-secondary text-xs min-h-[36px] px-3">
                                        {{ $review->is_visible ? 'Sembunyikan' : 'Tampilkan' }}
                                    </button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-sm text-[var(--text-secondary)]">Belum ada ulasan penghuni yang masuk.</p>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
