<div
    x-data="{ dark: document.documentElement.classList.contains('dark') }"
    x-init="
        $watch('dark', (value) => {
            localStorage.setItem('theme', value ? 'dark' : 'light');
            document.documentElement.classList.toggle('dark', value);
        });
        document.addEventListener('livewire:navigated', () => {
            dark = document.documentElement.classList.contains('dark');
        });
    "
    class="fixed bottom-4 right-4 z-50"
>
    <button
        type="button"
        @click="dark = !dark"
        aria-label="Toggle dark mode"
        class="flex items-center justify-center w-11 h-11 rounded-full border border-line bg-surface text-muted transition hover:text-ink dark:border-line-dark dark:bg-surface-dark dark:text-muted-dark dark:hover:text-ink-dark"
    >
        <svg x-show="!dark" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1.5m0 15V21m8.485-8.485h-1.5m-15 0H3m14.485 6.485l-1.06-1.06M6.575 6.575l-1.06-1.06m12.97 0l-1.06 1.06M6.575 17.425l-1.06 1.06M16.5 12a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" />
        </svg>
        <svg x-show="dark" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
        </svg>
    </button>
</div>
