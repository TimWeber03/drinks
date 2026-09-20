<div>
    @if ($view === 'drinkers')
        <div class="mb-6">
            <input
                type="search"
                wire:model.live.debounce.200ms="search"
                placeholder="Find your name…"
                class="w-full rounded-xl border-line bg-surface text-lg text-ink placeholder:text-muted focus:border-accent focus:ring-accent dark:border-line-dark dark:bg-surface-dark dark:text-ink-dark"
            >
        </div>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
            @forelse ($drinkers as $drinker)
                <button
                    type="button"
                    wire:click="selectDrinker({{ $drinker->id }})"
                    class="flex flex-col items-center gap-3 rounded-2xl border border-line bg-surface p-5 text-center transition hover:border-accent hover:-translate-y-0.5 dark:border-line-dark dark:bg-surface-dark dark:hover:border-accent-dark"
                >
                    @if ($drinker->avatar_path)
                        <img src="{{ asset('storage/'.$drinker->avatar_path) }}" alt="{{ $drinker->name }}" class="h-16 w-16 rounded-full object-cover">
                    @else
                        <span class="flex h-16 w-16 items-center justify-center rounded-full bg-accent/10 text-2xl font-semibold text-accent dark:bg-accent-dark/10 dark:text-accent-dark">
                            {{ mb_strtoupper(mb_substr($drinker->name, 0, 1)) }}
                        </span>
                    @endif
                    <span class="font-semibold text-ink dark:text-ink-dark">{{ $drinker->name }}</span>
                    <span @class([
                        'text-sm tabular-nums',
                        'text-muted dark:text-muted-dark' => $drinker->balance >= 0,
                        'font-semibold text-negative dark:text-negative-dark' => $drinker->balance < 0,
                    ])>
                        {{ number_format($drinker->balance, 2) }} €
                    </span>
                </button>
            @empty
                <p class="col-span-full py-12 text-center text-muted dark:text-muted-dark">
                    {{ $search !== '' ? 'No drinkers match "'.$search.'".' : 'No drinkers yet — ask an admin to add one.' }}
                </p>
            @endforelse
        </div>
    @endif

    @if ($view === 'drinks' && $selectedDrinker)
        <div class="mb-6 flex items-start justify-between gap-4">
            <div>
                <button wire:click="backToDrinkers" type="button" class="mb-1 text-sm text-accent hover:underline dark:text-accent-dark">
                    &larr; Back
                </button>
                <h1 class="text-2xl font-bold text-ink dark:text-ink-dark">{{ $selectedDrinker->name }}</h1>
                <p @class([
                    'text-sm tabular-nums',
                    'text-muted dark:text-muted-dark' => $selectedDrinker->balance >= 0,
                    'font-semibold text-negative dark:text-negative-dark' => $selectedDrinker->balance < 0,
                ])>
                    Balance: {{ number_format($selectedDrinker->balance, 2) }} €
                </p>
            </div>

            <div class="flex items-center gap-4">
                <a
                    href="{{ route('kiosk.drinker-history', $selectedDrinker) }}"
                    wire:navigate
                    class="text-sm font-medium text-accent hover:underline dark:text-accent-dark"
                >
                    History
                </a>

                <button
                    wire:click="showDeposit"
                    type="button"
                    class="rounded-xl bg-positive px-4 py-2.5 text-sm font-semibold text-white hover:bg-positive/90 dark:bg-positive-dark dark:hover:bg-positive-dark/90"
                >
                    + Add money
                </button>
            </div>
        </div>

        @error('stock')
            <p class="mb-4 text-sm font-medium text-negative dark:text-negative-dark">{{ $message }}</p>
        @enderror

        <label class="mb-4 flex items-center gap-2 text-sm text-muted dark:text-muted-dark">
            <input type="checkbox" wire:model.live="showOutOfStock" class="rounded border-line text-accent focus:ring-accent dark:border-line-dark dark:bg-surface-dark">
            Show out-of-stock items
        </label>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
            @forelse ($drinks as $drink)
                @if ($drink->isOutOfStock())
                    <div class="flex cursor-not-allowed flex-col items-center gap-3 rounded-2xl border border-line bg-surface p-5 text-center opacity-50 dark:border-line-dark dark:bg-surface-dark">
                        @if ($drink->image_path)
                            <img src="{{ asset('storage/'.$drink->image_path) }}" alt="{{ $drink->name }}" class="h-16 w-16 rounded-full object-cover grayscale">
                        @else
                            <span class="flex h-16 w-16 items-center justify-center rounded-full bg-muted/10 text-2xl font-semibold text-muted dark:bg-muted-dark/10 dark:text-muted-dark">
                                {{ mb_strtoupper(mb_substr($drink->name, 0, 1)) }}
                            </span>
                        @endif
                        <span class="font-semibold text-ink dark:text-ink-dark">{{ $drink->name }}</span>
                        <span class="text-sm font-medium text-muted dark:text-muted-dark">Out of stock</span>
                    </div>
                @else
                    <button
                        type="button"
                        wire:click="buy({{ $drink->id }})"
                        class="flex flex-col items-center gap-3 rounded-2xl border border-line bg-surface p-5 text-center transition hover:border-accent hover:-translate-y-0.5 dark:border-line-dark dark:bg-surface-dark dark:hover:border-accent-dark"
                    >
                        @if ($drink->image_path)
                            <img src="{{ asset('storage/'.$drink->image_path) }}" alt="{{ $drink->name }}" class="h-16 w-16 rounded-full object-cover">
                        @else
                            <span class="flex h-16 w-16 items-center justify-center rounded-full bg-accent/10 text-2xl font-semibold text-accent dark:bg-accent-dark/10 dark:text-accent-dark">
                                {{ mb_strtoupper(mb_substr($drink->name, 0, 1)) }}
                            </span>
                        @endif
                        <span class="font-semibold text-ink dark:text-ink-dark">{{ $drink->name }}</span>
                        <span class="text-sm tabular-nums text-muted dark:text-muted-dark">{{ number_format($drink->price, 2) }} €</span>
                    </button>
                @endif
            @empty
                <p class="col-span-full py-12 text-center text-muted dark:text-muted-dark">No drinks available — ask an admin to add one.</p>
            @endforelse
        </div>
    @endif

    @if ($view === 'deposit' && $selectedDrinker)
        <div class="mx-auto max-w-sm">
            <button wire:click="backToDrinks" type="button" class="mb-4 text-sm text-accent hover:underline dark:text-accent-dark">
                &larr; Back
            </button>

            <h1 class="mb-6 text-2xl font-bold text-ink dark:text-ink-dark">Add money for {{ $selectedDrinker->name }}</h1>

            <div class="mb-4 grid grid-cols-3 gap-3">
                @foreach ([5, 10, 20] as $amount)
                    <button
                        type="button"
                        wire:click="depositQuickAmount({{ $amount }})"
                        class="rounded-xl bg-positive py-4 text-lg font-semibold text-white hover:bg-positive/90 dark:bg-positive-dark dark:hover:bg-positive-dark/90"
                    >
                        +{{ $amount }} €
                    </button>
                @endforeach
            </div>

            <form wire:submit="depositCustomAmount" class="flex gap-2">
                <input
                    type="number"
                    step="0.01"
                    min="0.01"
                    wire:model="depositAmount"
                    placeholder="Custom amount"
                    class="flex-1 rounded-xl border-line bg-surface text-lg text-ink placeholder:text-muted focus:border-accent focus:ring-accent dark:border-line-dark dark:bg-surface-dark dark:text-ink-dark"
                >
                <button type="submit" class="rounded-xl bg-accent px-5 py-2 font-semibold text-white hover:bg-accent/90 dark:bg-accent-dark dark:hover:bg-accent-dark/90">
                    Add
                </button>
            </form>
            @error('depositAmount')
                <p class="mt-2 text-sm text-negative dark:text-negative-dark">{{ $message }}</p>
            @enderror
        </div>
    @endif

    @if ($view === 'confirmation')
        <div
            x-data
            x-init="setTimeout(() => $wire.backToDrinkers(), 2500)"
            class="mx-auto flex max-w-sm flex-col items-center gap-4 py-16 text-center"
        >
            <span class="flex h-16 w-16 items-center justify-center rounded-full bg-positive/10 text-3xl text-positive dark:bg-positive-dark/10 dark:text-positive-dark">
                ✓
            </span>
            <p class="text-lg font-medium text-ink dark:text-ink-dark">{{ $confirmationMessage }}</p>
            <button wire:click="backToDrinkers" type="button" class="text-sm text-accent hover:underline dark:text-accent-dark">
                Back to start now
            </button>
        </div>
    @endif
</div>
