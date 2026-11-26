@props(['status', 'label' => null])

@php
    $map = [
        'published' => ['bg' => 'bg-[var(--brand-secondary-bg)]', 'text' => 'text-[var(--brand-secondary-ink)]', 'icon' => 'check', 'default' => 'Tayang'],
        'approved' => ['bg' => 'bg-[var(--brand-secondary-bg)]', 'text' => 'text-[var(--brand-secondary-ink)]', 'icon' => 'check', 'default' => 'Disetujui'],
        'confirmed' => ['bg' => 'bg-[var(--brand-secondary-bg)]', 'text' => 'text-[var(--brand-secondary-ink)]', 'icon' => 'check', 'default' => 'Terkonfirmasi'],
        'completed' => ['bg' => 'bg-[var(--brand-secondary-bg)]', 'text' => 'text-[var(--brand-secondary-ink)]', 'icon' => 'check', 'default' => 'Selesai'],
        'verified' => ['bg' => 'bg-[var(--brand-secondary-bg)]', 'text' => 'text-[var(--brand-secondary-ink)]', 'icon' => 'check', 'default' => 'Terverifikasi'],
        'pending' => ['bg' => 'bg-[var(--accent-warm-bg)]', 'text' => 'text-[var(--accent-warm-ink)]', 'icon' => 'clock', 'default' => 'Menunggu Verifikasi'],
        'pending_review' => ['bg' => 'bg-[var(--accent-warm-bg)]', 'text' => 'text-[var(--accent-warm-ink)]', 'icon' => 'clock', 'default' => 'Menunggu Moderasi'],
        'pending_payment' => ['bg' => 'bg-[var(--accent-warm-bg)]', 'text' => 'text-[var(--accent-warm-ink)]', 'icon' => 'clock', 'default' => 'Menunggu Pembayaran'],
        'waiting_verification' => ['bg' => 'bg-[var(--accent-warm-bg)]', 'text' => 'text-[var(--accent-warm-ink)]', 'icon' => 'clock', 'default' => 'Menunggu Verifikasi Bayar'],
        'rejected' => ['bg' => 'bg-[var(--status-danger-bg)]', 'text' => 'text-[var(--status-danger)]', 'icon' => 'x', 'default' => 'Ditolak'],
        'cancelled' => ['bg' => 'bg-[var(--status-danger-bg)]', 'text' => 'text-[var(--status-danger)]', 'icon' => 'x', 'default' => 'Dibatalkan'],
        'rescheduled' => ['bg' => 'bg-[var(--accent-warm-bg)]', 'text' => 'text-[var(--accent-warm-ink)]', 'icon' => 'clock', 'default' => 'Dijadwal Ulang'],
        'open' => ['bg' => 'bg-[var(--status-danger-bg)]', 'text' => 'text-[var(--status-danger)]', 'icon' => 'clock', 'default' => 'Menunggu Penanganan'],
        'in_progress' => ['bg' => 'bg-[var(--accent-warm-bg)]', 'text' => 'text-[var(--accent-warm-ink)]', 'icon' => 'clock', 'default' => 'Sedang Diproses'],
        'resolved' => ['bg' => 'bg-[var(--brand-secondary-bg)]', 'text' => 'text-[var(--brand-secondary-ink)]', 'icon' => 'check', 'default' => 'Selesai'],
        'dismissed' => ['bg' => 'bg-[var(--bg-surface-alt)]', 'text' => 'text-[var(--text-secondary)]', 'icon' => 'x', 'default' => 'Diabaikan'],
        'available' => ['bg' => 'bg-[var(--brand-secondary-bg)]', 'text' => 'text-[var(--brand-secondary-ink)]', 'icon' => 'check', 'default' => 'Kosong'],
        'occupied' => ['bg' => 'bg-[var(--accent-warm-bg)]', 'text' => 'text-[var(--accent-warm-ink)]', 'icon' => 'clock', 'default' => 'Terisi'],
        'maintenance' => ['bg' => 'bg-[var(--status-danger-bg)]', 'text' => 'text-[var(--status-danger)]', 'icon' => 'clock', 'default' => 'Perbaikan'],
        'draft' => ['bg' => 'bg-[var(--bg-surface-alt)]', 'text' => 'text-[var(--text-secondary)]', 'icon' => 'clock', 'default' => 'Draf'],
    ];

    $style = $map[$status] ?? $map['draft'];
    $text = $label ?? $style['default'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[var(--radius-badge)] text-xs font-semibold {$style['bg']} {$style['text']}"]) }}>
    @if ($style['icon'] === 'check')
        <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 256 256" fill="none" stroke="currentColor" stroke-width="24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="40 144 96 200 224 72" /></svg>
    @elseif ($style['icon'] === 'x')
        <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 256 256" fill="none" stroke="currentColor" stroke-width="24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="200" y1="56" x2="56" y2="200" /><line x1="200" y1="200" x2="56" y2="56" /></svg>
    @else
        <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 256 256" fill="none" stroke="currentColor" stroke-width="22" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="128" cy="128" r="96" /><polyline points="128 72 128 128 168 152" /></svg>
    @endif
    <span>{{ $text }}</span>
</span>
