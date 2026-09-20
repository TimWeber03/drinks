<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-surface dark:bg-surface-dark border border-line dark:border-line-dark rounded-lg font-semibold text-sm text-ink dark:text-ink-dark hover:bg-paper dark:hover:bg-paper-dark focus:outline-none focus:ring-2 focus:ring-accent focus:ring-offset-2 dark:focus:ring-offset-surface-dark disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
