<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-negative dark:bg-negative-dark border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-negative/90 dark:hover:bg-negative-dark/90 focus:outline-none focus:ring-2 focus:ring-negative focus:ring-offset-2 dark:focus:ring-offset-surface-dark transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
