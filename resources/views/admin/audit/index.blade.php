<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-[var(--text-primary)]">Audit Trail & Log Aktivitas Sistem</h1>
                <p class="mt-1 text-sm text-[var(--text-secondary)]">Rekam jejak keputusan verifikasi Mitra, moderasi kos, pembayaran, dan tindakan tata kelola platform.</p>
            </div>
            <form method="GET" action="{{ route('admin.audit.index') }}" class="flex gap-2">
                <input type="text" name="action" value="{{ $actionFilter }}" placeholder="Filter aksi (mis. kos, payment)" class="lk-input text-xs min-h-[40px] w-56">
                <button type="submit" class="lk-btn-primary text-xs min-h-[40px] px-4">Filter</button>
            </form>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
        @if ($logs->isNotEmpty())
            <div class="lk-card overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-[var(--bg-tint)] border-b border-[var(--border-subtle)] text-xs text-[var(--text-secondary)]">
                        <tr>
                            <th class="p-3.5">Waktu</th>
                            <th class="p-3.5">Pelaku</th>
                            <th class="p-3.5">Kode Aksi</th>
                            <th class="p-3.5">Deskripsi Aktivitas</th>
                            <th class="p-3.5">IP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border-subtle)]">
                        @foreach ($logs as $log)
                            <tr>
                                <td class="p-3.5 text-xs text-[var(--text-secondary)] tabular-nums whitespace-nowrap">{{ $log->created_at?->format('d M Y H:i:s') }}</td>
                                <td class="p-3.5 text-xs">
                                    <strong class="text-[var(--text-primary)]">{{ $log->user?->name ?? 'Sistem Otomatis' }}</strong>
                                    @if ($log->user)
                                        <span class="block text-[var(--text-secondary)]">{{ ucfirst($log->user->role) }}</span>
                                    @endif
                                </td>
                                <td class="p-3.5">
                                    <span class="px-2.5 py-0.5 rounded-full bg-[var(--bg-tint)] border border-[var(--border-subtle)] text-xs font-semibold text-[var(--text-primary)]">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td class="p-3.5 text-xs text-[var(--text-primary)]">{{ $log->description }}</td>
                                <td class="p-3.5 text-xs text-[var(--text-secondary)] tabular-nums">{{ $log->ip_address ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div>{{ $logs->links() }}</div>
        @else
            <x-lokakos.empty-state
                title="Belum ada log aktivitas"
                description="Setiap tindakan verifikasi Mitra, moderasi kos, pembayaran, dan penanganan laporan akan tercatat otomatis di sini."
            />
        @endif
    </div>
</x-app-layout>
