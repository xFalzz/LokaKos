@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm font-medium text-[var(--text-primary)] mb-1.5']) }}>
    {{ $value ?? $slot }}
</label>
