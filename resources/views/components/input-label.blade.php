@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-ink dark:text-ink-dark']) }}>
    {{ $value ?? $slot }}
</label>
