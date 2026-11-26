<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-[var(--text-primary)]">Buku Kas & Pengeluaran Operasional</h1>
                <p class="mt-1 text-sm text-[var(--text-secondary)]">Catat biaya operasional bulanan kos (Wi-Fi, air, kebersihan, perbaikan) untuk memantau hasil bersih.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('mitra.report.csv') }}" class="lk-btn-secondary text-xs min-h-[40px]">Unduh CSV (Excel)</a>
                <a href="{{ route('mitra.report.pdf') }}" class="lk-btn-primary text-xs min-h-[40px]">Unduh Laporan PDF</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="lk-card p-5">
                <p class="text-xs font-semibold text-[var(--text-secondary)]">Pemasukan Sewa Terkonfirmasi</p>
                <p class="mt-2 font-display text-2xl font-semibold text-[var(--text-primary)] tabular-nums">
                    Rp {{ number_format($grossIncome, 0, ',', '.') }}
                </p>
            </div>
            <div class="lk-card p-5">
                <p class="text-xs font-semibold text-[var(--text-secondary)]">Total Pengeluaran Operasional</p>
                <p class="mt-2 font-display text-2xl font-semibold text-[var(--status-danger)] tabular-nums">
                    Rp {{ number_format($totalExpenses, 0, ',', '.') }}
                </p>
            </div>
            <div class="lk-card p-5 bg-[var(--bg-tint)]">
                <p class="text-xs font-semibold text-[var(--text-secondary)]">Estimasi Pemasukan Bersih</p>
                <p class="mt-2 font-display text-2xl font-semibold text-[var(--brand-secondary-ink)] tabular-nums">
                    Rp {{ number_format($netIncome, 0, ',', '.') }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <div class="lg:col-span-5">
                <section class="lk-card p-6 space-y-4">
                    <h2 class="text-lg font-semibold text-[var(--text-primary)]">Catat Pengeluaran Baru</h2>
                    <form method="POST" action="{{ route('mitra.expenses.store') }}" class="space-y-4">
                        @csrf
                        <div>
                            <x-input-label for="boarding_house_id" value="Properti Kos (Opsional)" />
                            <select id="boarding_house_id" name="boarding_house_id" class="lk-input text-sm">
                                <option value="">Operasional Umum Semua Kos</option>
                                @foreach ($myKos as $k)
                                    <option value="{{ $k->id }}">{{ $k->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <x-input-label for="category" value="Kategori" />
                                <select id="category" name="category" required class="lk-input text-sm">
                                    <option value="wifi">Internet / Wi-Fi</option>
                                    <option value="air_pdam">Air / PDAM</option>
                                    <option value="listrik_umum">Listrik Area Umum</option>
                                    <option value="kebersihan">Sampah & Kebersihan</option>
                                    <option value="perbaikan">Perbaikan Fasilitas</option>
                                    <option value="lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div>
                                <x-input-label for="expense_date" value="Tanggal" />
                                <x-text-input id="expense_date" name="expense_date" type="date" :value="old('expense_date', now()->toDateString())" required />
                            </div>
                        </div>
                        <div>
                            <x-input-label for="description" value="Keterangan" />
                            <x-text-input id="description" name="description" type="text" required placeholder="Contoh: Tagihan Wi-Fi Biznet bulan ini" />
                        </div>
                        <div>
                            <x-input-label for="amount" value="Nominal Pengeluaran (Rp)" />
                            <x-text-input id="amount" name="amount" type="number" min="1000" step="1000" required placeholder="350000" class="tabular-nums" />
                        </div>
                        <button type="submit" class="lk-btn-primary w-full text-sm">Simpan Catatan Pengeluaran</button>
                    </form>
                </section>
            </div>

            <div class="lg:col-span-7 space-y-4">
                <h2 class="text-lg font-semibold text-[var(--text-primary)]">Riwayat Pengeluaran</h2>
                @if ($expenses->isNotEmpty())
                    <div class="lk-card overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-[var(--bg-tint)] border-b border-[var(--border-subtle)] text-xs text-[var(--text-secondary)]">
                                <tr>
                                    <th class="p-3.5">Tanggal</th>
                                    <th class="p-3.5">Keterangan & Kos</th>
                                    <th class="p-3.5 text-right">Nominal</th>
                                    <th class="p-3.5 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[var(--border-subtle)]">
                                @foreach ($expenses as $exp)
                                    <tr>
                                        <td class="p-3.5 text-xs text-[var(--text-secondary)] tabular-nums whitespace-nowrap">{{ $exp->expense_date->format('d M Y') }}</td>
                                        <td class="p-3.5">
                                            <p class="font-medium text-[var(--text-primary)]">{{ $exp->description }}</p>
                                            <p class="text-xs text-[var(--text-secondary)]">{{ str_replace('_', ' ', ucfirst($exp->category)) }} · {{ $exp->boardingHouse?->name ?? 'Umum' }}</p>
                                        </td>
                                        <td class="p-3.5 text-right font-semibold text-[var(--text-primary)] tabular-nums whitespace-nowrap">
                                            Rp {{ number_format($exp->amount, 0, ',', '.') }}
                                        </td>
                                        <td class="p-3.5 text-right">
                                            <form method="POST" action="{{ route('mitra.expenses.destroy', $exp) }}" onsubmit="return confirm('Hapus catatan pengeluaran ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs font-semibold text-[var(--status-danger)] hover:underline">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div>{{ $expenses->links() }}</div>
                @else
                    <x-lokakos.empty-state
                        title="Belum ada catatan pengeluaran"
                        description="Catat biaya operasional kos untuk menghitung estimasi pemasukan bersih bulanan pada laporan PDF."
                    />
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
