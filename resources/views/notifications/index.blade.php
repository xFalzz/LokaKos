<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-[var(--text-primary)]">Pusat Notifikasi</h1>
                <p class="mt-1 text-sm text-[var(--text-secondary)]">Pembaruan status pesanan, survei, keluhan kamar, dan moderasi akun Anda.</p>
            </div>
            @if ($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="lk-btn-secondary text-xs min-h-[42px]">Tandai Semua Dibaca ({{ $unreadCount }})</button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
        @if ($notifications->isNotEmpty())
            <div class="lk-card divide-y divide-[var(--border-subtle)] overflow-hidden">
                @foreach ($notifications as $item)
                    <div class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 {{ $item->isUnread() ? 'bg-[var(--bg-tint)]' : '' }}">
                        <div class="space-y-1 min-w-0">
                            <div class="flex items-center gap-2">
                                @if ($item->isUnread())
                                    <span class="w-2 h-2 rounded-full bg-[var(--brand-gold)] shrink-0"></span>
                                @endif
                                <h2 class="text-sm font-semibold text-[var(--text-primary)]">{{ $item->title }}</h2>
                            </div>
                            <p class="text-xs text-[var(--text-secondary)] leading-relaxed">{{ $item->body }}</p>
                            <p class="text-[11px] text-[var(--text-secondary)] tabular-nums">{{ $item->created_at->diffForHumans() }} · {{ $item->created_at->format('d M Y H:i') }} WIB</p>
                        </div>

                        <div class="shrink-0">
                            <form method="POST" action="{{ route('notifications.read', $item) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="{{ $item->isUnread() ? 'lk-btn-primary' : 'lk-btn-secondary' }} text-xs min-h-[38px] px-4">
                                    {{ $item->action_url ? 'Buka Detail' : 'Tandai Dibaca' }}
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <div>{{ $notifications->links() }}</div>
        @else
            <x-lokakos.empty-state
                title="Belum ada notifikasi"
                description="Pembaruan penting mengenai pesanan, jadwal survei, atau properti kos akan muncul di sini."
            />
        @endif
    </div>
</x-app-layout>
