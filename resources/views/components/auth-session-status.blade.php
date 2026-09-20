@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-positive dark:text-positive-dark']) }}>
        {{ $status }}
    </div>
@endif
