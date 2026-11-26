@props([
    'title',
    'description',
    'actionLabel' => null,
    'actionHref' => null,
])

<div {{ $attributes->merge(['class' => 'lk-card p-8 text-center']) }}>
    <h3 class="text-lg font-semibold text-[var(--text-primary)]">{{ $title }}</h3>
    <p class="mt-2 text-sm text-[var(--text-secondary)] max-w-md mx-auto leading-relaxed">{{ $description }}</p>
    @if ($actionLabel && $actionHref)
        <div class="mt-5">
            <a href="{{ $actionHref }}" class="lk-btn-secondary">
                {{ $actionLabel }}
            </a>
        </div>
    @endif
</div>
