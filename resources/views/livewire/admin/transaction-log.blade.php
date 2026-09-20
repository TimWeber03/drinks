<div>
    <h1 class="mb-6 text-2xl font-bold text-ink dark:text-ink-dark">Transactions</h1>

    <div class="mb-6 grid grid-cols-1 gap-4 rounded-2xl border border-line bg-surface p-4 dark:border-line-dark dark:bg-surface-dark sm:grid-cols-5">
        <div class="min-w-0">
            <x-input-label for="drinkerId" value="Drinker" />
            <select id="drinkerId" wire:model.live="drinkerId" class="mt-1 block w-full rounded-lg border-line bg-surface text-sm text-ink focus:border-accent focus:ring-accent dark:border-line-dark dark:bg-surface-dark dark:text-ink-dark">
                <option value="">All</option>
                @foreach ($drinkers as $drinker)
                    <option value="{{ $drinker->id }}">{{ $drinker->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="min-w-0">
            <x-input-label for="drinkId" value="Drink" />
            <select id="drinkId" wire:model.live="drinkId" class="mt-1 block w-full rounded-lg border-line bg-surface text-sm text-ink focus:border-accent focus:ring-accent dark:border-line-dark dark:bg-surface-dark dark:text-ink-dark">
                <option value="">All</option>
                @foreach ($drinks as $drink)
                    <option value="{{ $drink->id }}">{{ $drink->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="min-w-0">
            <x-input-label for="type" value="Type" />
            <select id="type" wire:model.live="type" class="mt-1 block w-full rounded-lg border-line bg-surface text-sm text-ink focus:border-accent focus:ring-accent dark:border-line-dark dark:bg-surface-dark dark:text-ink-dark">
                <option value="">All</option>
                @foreach ($types as $t)
                    <option value="{{ $t->value }}">{{ $t->label() }}</option>
                @endforeach
            </select>
        </div>

        <div class="min-w-0">
            <x-input-label for="from" value="From" />
            <input id="from" type="date" wire:model.live="from" class="mt-1 block w-full rounded-lg border-line bg-surface text-sm text-ink focus:border-accent focus:ring-accent dark:border-line-dark dark:bg-surface-dark dark:text-ink-dark">
        </div>

        <div class="min-w-0">
            <x-input-label for="to" value="To" />
            <input id="to" type="date" wire:model.live="to" class="mt-1 block w-full rounded-lg border-line bg-surface text-sm text-ink focus:border-accent focus:ring-accent dark:border-line-dark dark:bg-surface-dark dark:text-ink-dark">
        </div>

        <div class="sm:col-span-5">
            <button wire:click="resetFilters" type="button" class="text-sm text-accent hover:underline dark:text-accent-dark">
                Clear filters
            </button>
        </div>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-line bg-surface dark:border-line-dark dark:bg-surface-dark">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-line text-muted dark:border-line-dark dark:text-muted-dark">
                <tr>
                    <th class="px-4 py-3 font-medium">When</th>
                    <th class="px-4 py-3 font-medium">Drinker</th>
                    <th class="px-4 py-3 font-medium">Type</th>
                    <th class="px-4 py-3 font-medium">Detail</th>
                    <th class="px-4 py-3 text-right font-medium">Amount</th>
                    <th class="px-4 py-3 text-right font-medium">Balance after</th>
                    <th class="px-4 py-3 font-medium">By</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line dark:divide-line-dark">
                @forelse ($transactions as $transaction)
                    <tr>
                        <td class="px-4 py-3 text-muted dark:text-muted-dark">{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-3 text-ink dark:text-ink-dark">{{ $transaction->drinker->name }}</td>
                        <td class="px-4 py-3 text-ink dark:text-ink-dark">{{ $transaction->type->label() }}</td>
                        <td class="px-4 py-3 text-ink dark:text-ink-dark">{{ $transaction->drink?->name ?? '—' }}</td>
                        <td @class([
                            'px-4 py-3 text-right font-medium tabular-nums',
                            'text-positive dark:text-positive-dark' => $transaction->amount > 0,
                            'text-negative dark:text-negative-dark' => $transaction->amount < 0,
                        ])>
                            {{ number_format($transaction->amount, 2) }} €
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums text-ink dark:text-ink-dark">{{ number_format($transaction->balance_after, 2) }} €</td>
                        <td class="px-4 py-3 text-muted dark:text-muted-dark">{{ $transaction->createdBy?->name ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-muted dark:text-muted-dark">No transactions match these filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $transactions->links() }}
    </div>
</div>
