<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-accent dark:bg-accent-dark border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-accent/90 dark:hover:bg-accent-dark/90 focus:outline-none focus:ring-2 focus:ring-accent focus:ring-offset-2 dark:focus:ring-offset-surface-dark transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
