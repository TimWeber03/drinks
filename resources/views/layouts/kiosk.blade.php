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
        <div class="min-h-screen bg-paper dark:bg-paper-dark">
            <header class="border-b border-line dark:border-line-dark">
                <div class="mx-auto flex max-w-3xl items-center justify-between px-4 py-4 sm:px-6">
                    <a href="{{ route('kiosk.home') }}" wire:navigate>
                        <x-application-logo class="text-lg" />
                    </a>

                    <div class="flex items-center gap-5 text-sm font-medium">
                        <a href="{{ route('kiosk.activity') }}" wire:navigate class="text-muted hover:text-ink dark:text-muted-dark dark:hover:text-ink-dark">
                            Activity
                        </a>

                        @auth
                            <a href="{{ route('dashboard') }}" wire:navigate class="text-muted hover:text-ink dark:text-muted-dark dark:hover:text-ink-dark">
                                Admin
                            </a>
                        @else
                            <a href="{{ route('login') }}" wire:navigate class="text-muted hover:text-ink dark:text-muted-dark dark:hover:text-ink-dark">
                                Admin login
                            </a>
                        @endauth
                    </div>
                </div>
            </header>

            <main class="mx-auto max-w-3xl px-4 py-8 sm:px-6">
                {{ $slot }}
            </main>
        </div>

        <x-theme-toggle />
    </body>
</html>
