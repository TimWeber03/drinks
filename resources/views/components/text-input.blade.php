@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-line dark:border-line-dark dark:bg-surface-dark text-ink dark:text-ink-dark focus:border-accent dark:focus:border-accent-dark focus:ring-accent dark:focus:ring-accent-dark rounded-lg']) }}>
