<button
    type="button"
    x-data
    @click="$store.theme.toggle()"
    :aria-pressed="$store.theme.dark.toString()"
    :aria-label="$store.theme.dark ? 'Beralih ke tema terang' : 'Beralih ke tema gelap'"
    {{ $attributes->merge(['class' => 'inline-flex items-center justify-center gap-2 min-h-[44px] px-3.5 rounded-full border border-[var(--border-control)] bg-[var(--bg-surface)] text-[var(--text-primary)] hover:bg-[var(--bg-tint)] text-sm font-medium transition-colors']) }}
>
    <svg x-show="!$store.theme.dark" class="w-4 h-4 shrink-0" viewBox="0 0 256 256" fill="none" stroke="currentColor" stroke-width="18" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M216.7 152.6A91.9 91.9 0 0 1 103.4 39.3a92 92 0 1 0 113.3 113.3Z" />
    </svg>
    <svg x-cloak x-show="$store.theme.dark" class="w-4 h-4 shrink-0" viewBox="0 0 256 256" fill="none" stroke="currentColor" stroke-width="18" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <circle cx="128" cy="128" r="56" />
        <line x1="128" y1="24" x2="128" y2="44" />
        <line x1="128" y1="212" x2="128" y2="232" />
        <line x1="24" y1="128" x2="44" y2="128" />
        <line x1="212" y1="128" x2="232" y2="128" />
        <line x1="54.5" y1="54.5" x2="68.6" y2="68.6" />
        <line x1="187.4" y1="187.4" x2="201.5" y2="201.5" />
        <line x1="54.5" y1="201.5" x2="68.6" y2="187.4" />
        <line x1="187.4" y1="68.6" x2="201.5" y2="54.5" />
    </svg>
    <span class="hidden sm:inline" x-text="$store.theme.dark ? 'Terang' : 'Gelap'">Gelap</span>
</button>
