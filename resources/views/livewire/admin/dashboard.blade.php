<div>
    <h1 class="mb-6 text-2xl font-bold text-ink dark:text-ink-dark">Dashboard</h1>

    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="rounded-2xl border border-line bg-surface p-4 dark:border-line-dark dark:bg-surface-dark">
            <p class="text-sm text-muted dark:text-muted-dark">Active drinkers</p>
            <p class="text-2xl font-bold tabular-nums text-ink dark:text-ink-dark">{{ $activeDrinkers }}</p>
        </div>
        <div class="rounded-2xl border border-line bg-surface p-4 dark:border-line-dark dark:bg-surface-dark">
            <p class="text-sm text-muted dark:text-muted-dark">Active drinks</p>
            <p class="text-2xl font-bold tabular-nums text-ink dark:text-ink-dark">{{ $activeDrinks }}</p>
        </div>
        <div class="rounded-2xl border border-line bg-surface p-4 dark:border-line-dark dark:bg-surface-dark">
            <p class="text-sm text-muted dark:text-muted-dark">Total balances</p>
            <p @class([
                'text-2xl font-bold tabular-nums',
                'text-ink dark:text-ink-dark' => $totalBalance >= 0,
                'text-negative dark:text-negative-dark' => $totalBalance < 0,
            ])>
                {{ number_format($totalBalance, 2) }} €
            </p>
        </div>
        <div class="rounded-2xl border border-line bg-surface p-4 dark:border-line-dark dark:bg-surface-dark">
            <p class="text-sm text-muted dark:text-muted-dark">Purchases today</p>
            <p class="text-2xl font-bold tabular-nums text-ink dark:text-ink-dark">{{ $todaysSales }}</p>
        </div>
    </div>

    <div class="mt-8 flex flex-wrap gap-3">
        <a href="{{ route('admin.drinks') }}" wire:navigate class="rounded-xl bg-accent px-4 py-2 text-sm font-semibold text-white hover:bg-accent/90 dark:bg-accent-dark dark:hover:bg-accent-dark/90">
            Manage drinks
        </a>
        <a href="{{ route('admin.drinkers') }}" wire:navigate class="rounded-xl bg-accent px-4 py-2 text-sm font-semibold text-white hover:bg-accent/90 dark:bg-accent-dark dark:hover:bg-accent-dark/90">
            Manage drinkers
        </a>
        <a href="{{ route('admin.transactions') }}" wire:navigate class="rounded-xl border border-line bg-surface px-4 py-2 text-sm font-semibold text-ink hover:bg-paper dark:border-line-dark dark:bg-surface-dark dark:text-ink-dark dark:hover:bg-paper-dark">
            View transactions
        </a>
        <a href="{{ route('kiosk.home') }}" wire:navigate class="rounded-xl border border-line bg-surface px-4 py-2 text-sm font-semibold text-ink hover:bg-paper dark:border-line-dark dark:bg-surface-dark dark:text-ink-dark dark:hover:bg-paper-dark">
            Open kiosk
        </a>
    </div>
</div>
