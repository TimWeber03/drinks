<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <script>
            (function () {
                function applyStoredTheme() {
                    const stored = localStorage.getItem('theme');
                    const dark = stored ? stored === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
                    document.documentElement.classList.toggle('dark', dark);
                }

                applyStoredTheme();
                document.addEventListener('livewire:navigated', applyStoredTheme);
            })();
        </script>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="flex min-h-screen flex-col items-center justify-center bg-paper px-4 dark:bg-paper-dark">
            <a href="/" wire:navigate>
                <x-application-logo class="text-3xl" />
            </a>

            <div class="mt-8 w-full sm:max-w-md rounded-2xl border border-line bg-surface px-6 py-8 dark:border-line-dark dark:bg-surface-dark">
                {{ $slot }}
            </div>
        </div>

        <x-theme-toggle />
    </body>
</html>
